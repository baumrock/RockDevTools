<?php

namespace RockDevTools;

use MatthiasMullie\Minify\Exceptions\IOException;
use ProcessWire\FilenameArray as ProcessWireFilenameArray;
use ProcessWire\WireException;
use ProcessWire\WireFilesException;
use ProcessWire\WirePermissionException;

use function ProcessWire\rockdevtools;
use function ProcessWire\wire;

class FilenameArray extends ProcessWireFilenameArray
{
  public function add($filename)
  {
    $filename = rockdevtools()->toPath($filename);
    return parent::add($filename);
  }

  public function append($filename)
  {
    $filename = rockdevtools()->toPath($filename);
    return parent::append($filename);
  }

  /**
   * Did the list of files in the array change? (file added or removed)
   * @param string $dstFile
   * @return bool
   * @throws WireException
   * @throws WirePermissionException
   */
  public function filesChanged(string $dstFile): bool
  {
    if (rockdevtools()->debug) return true;
    $dstFile = rockdevtools()->toPath($dstFile);
    $oldListHash = wire()->cache->get('rockdevtools-filenames-' . md5($dstFile));
    if (!$oldListHash) return true;
    return $oldListHash !== $this->filesListHash();
  }

  /**
   * Get an md5 hash of the list of filenames.
   * @return string
   */
  public function filesListHash(): string
  {
    return md5(implode(',', array_keys($this->data)));
  }

  /**
   * Does the current list of files has any changes? This includes both
   * changed files or a changed list of files (added/removed files).
   *
   * @param string $dstFile
   * @return bool
   * @throws WireException
   * @throws WirePermissionException
   */
  public function hasChanges(string $dstFile): bool
  {
    if (rockdevtools()->debug) return true;
    $dstFile = rockdevtools()->toPath($dstFile);

    // if dst file does not exist, return true
    if (!is_file($dstFile)) return true;

    // did the list of files change?
    // if yes, return true
    if ($this->filesChanged($dstFile)) return true;

    // if any of the files in the array is newer than the dst file, return true
    foreach ($this as $filename) {
      if (@filemtime($filename) > filemtime($dstFile)) return true;
    }

    // otherwise return false
    return false;
  }

  public function prepend($filename)
  {
    $filename = rockdevtools()->toPath($filename);
    return parent::prepend($filename);
  }

  /**
   * Generic save method that all asset types use. It will save a reference of
   * the filelist to cache to keep track of added/removed files.
   *
   * @param string $to
   * @param bool $onlyIfChanged
   * @return FilenameArray
   * @throws WireException
   * @throws WirePermissionException
   * @throws WireFilesException
   * @throws IOException
   */
  public function save(
    string $to,
    bool $onlyIfChanged = true,
  ): self {
    $dst = rockdevtools()->toPath($to);

    // early exit if no changes
    if ($onlyIfChanged && !$this->hasChanges($dst)) return $this;

    // make sure the folder exists
    wire()->files->mkdir(dirname($dst), true);

    if ($this instanceof LessArray) $this->saveLESS($dst);
    if ($this instanceof CssArray) $this->saveCSS($dst);
    if ($this instanceof JsArray) $this->saveJS($dst);

    $this->updateFilesListHash($dst);

    return $this;
  }

  public function toArray(): array
  {
    return (array)$this->data;
  }

  public function updateFilesListHash(string $dstFile): void
  {
    $dstFile = rockdevtools()->toPath($dstFile);
    wire()->cache->save(
      'rockdevtools-filenames-' . md5($dstFile),
      $this->filesListHash()
    );
  }
}

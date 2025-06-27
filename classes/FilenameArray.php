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
  public function add($filename, int $levels = 3)
  {
    if (str_contains($filename, '*')) return $this->addAll($filename, $levels);
    $filename = rockdevtools()->toPath($filename);
    return parent::add($filename);
  }

  /**
   * Add all files matching the glob to the array.
   *
   * Supports ** for recursive globbing!
   *
   * Usage:
   * rockdevtools()->less()->addAll('/site/templates/src/*.less')
   * rockdevtools()->less()->addAll('/site/templates/RockPageBuilder/**\/*.less')
   *
   * @param string $glob
   * @return LessArray
   * @throws WireException
   * @throws WirePermissionException
   */
  public function addAll(string $glob, int $levels = 3): self
  {
    foreach ($this->glob($glob, $levels) as $file) $this->add($file);
    return $this;
  }

  public function append($filename)
  {
    $filename = rockdevtools()->toPath($filename);
    return parent::append($filename);
  }

  /**
   * Log current list of added files to the Tracy Debug Bar
   */
  public function bd(): self
  {
    try {
      bd($this->data);
    } catch (\Throwable $th) {
    }
    return $this;
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
   * Get list of files by glob pattern
   *
   * This also supports ** for recursive globbing!
   *
   * @param string $pattern
   * @param int $levels
   * @return array
   */
  public function glob(string $pattern, int $levels = 3): array
  {
    $pattern = rockdevtools()->toPath($pattern);

    // if path contains ** we use brace expansion to find recursively
    $glob = '';
    if (strpos($pattern, '**') !== false) {
      $glob = '{';
      // build a pattern like this:
      // /var/www/html/site/templates/RockPageBuilder/{,*,*/*,*/*/*}/*.less
      for ($i = 1; $i <= $levels; $i++) {
        $glob .= rtrim(str_repeat('*/', $i), '/');
        $glob .= ',';
      }
      $glob = rtrim($glob, ',');
      $glob .= '}';
      $pattern = str_replace('**', $glob, $pattern);
    }
    return glob($pattern, GLOB_BRACE);
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
   * Remove file or files (glob pattern) from the list
   * @param string $file
   */
  public function remove($file): self
  {
    if (str_contains($file, '*')) {
      $file = $this->glob($file);
      foreach ($file as $f) $this->remove($f);
      return $this;
    }
    parent::remove($file);
    return $this;
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
    bool $sourceMap = false,
  ): self {
    $dst = rockdevtools()->toPath($to);

    // early exit if no changes
    if ($onlyIfChanged && !$this->hasChanges($dst)) return $this;

    // make sure the folder exists
    wire()->files->mkdir(dirname($dst), true);

    if ($this instanceof LessArray) $this->saveLESS($dst, sourceMap: $sourceMap);
    if ($this instanceof ScssArray) $this->saveSCSS($dst, sourceMap: $sourceMap);
    if ($this instanceof CssArray) $this->saveCSS($dst);
    if ($this instanceof JsArray) $this->saveJS($dst);

    $this->updateFilesListHash($dst);

    return $this;
  }

  public function toArray(): array
  {
    return $this->data;
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

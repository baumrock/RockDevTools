<?php

namespace RockDevTools;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use ProcessWire\Wire;
use ProcessWire\WireException;

use function ProcessWire\rockdevtools;
use function ProcessWire\wire;

class Assets extends Wire
{

  public function css(): CssArray
  {
    return new CssArray();
  }

  /**
   * Given a source file it returns the destination file for minification
   * @param string $src
   * @param string $dst
   * @return string
   */
  private function getDstFile(
    string $src,
    string $dst
  ): string {
    // if $dst is a folder, use basename of $src
    if ($this->isDir($dst)) $dst = $dst . '/' . basename($src);

    // change extension
    if (str_ends_with($src, '.less')) $dst = substr($dst, 0, -5) . '.min.css';
    elseif (str_ends_with($src, '.scss')) $dst = substr($dst, 0, -5) . '.min.css';
    elseif (str_ends_with($src, '.js')) $dst = substr($dst, 0, -3) . '.min.js';
    elseif (str_ends_with($src, '.css')) $dst = substr($dst, 0, -4) . '.min.css';

    return $dst;
  }

  /**
   * Is the given path a directory?
   *
   * Other than PHP's is_dir() this does NOT check if the path exists!
   *
   * @param mixed $path
   * @return bool
   */
  public function isDir(string $path): bool
  {
    return !array_key_exists('extension', pathinfo($path));
  }

  /**
   * Is the source file newer than the destination file?
   * @param string $srcFile
   * @param string $dstFile
   * @return bool
   */
  public function isNewer(
    string $srcFile,
    string $dstFile,
  ): bool {
    return @filemtime($srcFile) > @filemtime($dstFile);
  }

  public function js(): JsArray
  {
    return new JsArray();
  }

  public function less(): LessArray
  {
    if (!wire()->modules->get('Less')) {
      throw new WireException('Less module not found');
    }
    return new LessArray();
  }

  public function scss(): ScssArray
  {
    if (!wire()->modules->get('Scss')) {
      throw new WireException('Scss module not found');
    }
    return new ScssArray();
  }

  /**
   * Parse and minify JS/LESS/CSS files and write them to $dst
   *
   * Can either take a single file or a folder
   *
   * NOTE: This is intentionally NOT recursive! So you can, for example,
   * put some includes in a nested /src/includes folder and they will be
   * untouched.
   *
   * @param string $src
   * @param string $dst
   * @return void
   */
  public function minify(
    string $src,
    string $dst,
  ): self {
    $src = rockdevtools()->toPath($src);
    $dst = rockdevtools()->toPath($dst);

    // if $src is a folder minify all files in it
    if (is_dir($src)) {
      foreach (glob($src . '/*.{js,less,scss,css}', GLOB_BRACE) as $file) {
        $this->minify($file, $dst);
      }
      return $this;
    }

    // single file
    // get destination filepath
    $dstFile = $this->getDstFile($src, $dst);

    // check if we need to minify it
    if (!$this->minifyNeeded($src, $dstFile)) return $this;

    // minify file
    $this->minifyFile($src, $dstFile);

    return $this;
  }

  private function minifyCSS(
    string $srcFile,
    string $dstFile,
  ): void {
    $min = new CSS();
    $min->add($srcFile);
    $min->minify($dstFile);
  }

  private function minifyJS(
    string $srcFile,
    string $dstFile,
  ): void {
    $min = new JS();
    $min->add($srcFile);
    $min->minify($dstFile);
  }

  private function minifyLess(
    string $srcFile,
    string $dstFile,
  ): void {
    if (!wire()->modules->get('Less')) throw new WireException('Less module not found');
    /** @var Less $less */
    $less = wire()->modules->get('Less');
    $less->setOption('compress', true); // minify
    $less->addFile($srcFile);
    $less->saveCss($dstFile);
  }

  private function minifyScss(
    string $srcFile,
    string $dstFile,
  ): void {
    if (!wire()->modules->get('Scss')) throw new WireException('Scss module not found');
    /** @var Scss $scss */
    $scss = wire()->modules->get('Scss');
    $scss->setOption('compress', true); // minify
    $scss->addFile($srcFile);
    $scss->saveCss($dstFile);
  }

  public function minifyFile(
    string $srcFile,
    string $dstFile,
  ): void {
    // make sure the folder exists
    wire()->files->mkdir(dirname($dstFile));
    // bd("$srcFile -> $dstFile", 'minify');

    // create minified file based on extension
    if (str_ends_with($srcFile, '.less')) $this->minifyLess($srcFile, $dstFile);
    elseif (str_ends_with($srcFile, '.scss')) $this->minifyScss($srcFile, $dstFile);
    elseif (str_ends_with($srcFile, '.js')) $this->minifyJS($srcFile, $dstFile);
    elseif (str_ends_with($srcFile, '.css')) $this->minifyCSS($srcFile, $dstFile);
  }

  private function minifyNeeded(
    string $srcFile,
    string $dstFile,
  ): bool {
    // check if file exists
    // if not, return false or throw error when debug is on
    if (!is_file($srcFile)) {
      if (wire()->config->debug) throw new WireException("File $srcFile not found");
      return false;
    }

    // otherwise minify if src file is newer (has changed)
    if ($this->isNewer($srcFile, $dstFile)) return true;
    else return false;
  }
}

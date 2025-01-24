<?php

namespace ProcessWire;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use RockDevTools\Assets;

function rockdevtools(): RockDevTools
{
  return wire()->modules->get('RockDevTools');
}

/**
 * @author Bernhard Baumrock, 14.01.2025
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
require_once __DIR__ . '/vendor/autoload.php';
class RockDevTools extends WireData implements Module
{
  public function init()
  {
    // early exit if not enabled to keep the footprint as low as possible
    if (!wire()->config->rockdevtools) return;

    wire()->classLoader->addNamespace('RockDevTools', __DIR__ . '/classes');

    // hooks
    wire()->addHookAfter('Modules::refresh', $this, 'resetCache');
  }

  public function assets(): Assets
  {
    return new Assets();
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

  public function isNewer(
    string $srcFile,
    string $dstFile,
  ): bool {
    return @filemtime($srcFile) > @filemtime($dstFile);
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
    $src = rtrim(Paths::normalizeSeparators($src), '/');
    $dst = rtrim(Paths::normalizeSeparators($dst), '/');

    // if $src is a folder minify all files in it
    if (is_dir($src)) {
      foreach (glob($src . '/*.{js,less,css}', GLOB_BRACE) as $file) {
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

  public function minifyFile(
    string $srcFile,
    string $dstFile,
  ): void {
    // make sure the folder exists
    wire()->files->mkdir(dirname($dstFile));
    // bd("$srcFile -> $dstFile", 'minify');

    // create minified file based on extension
    if (str_ends_with($srcFile, '.less')) $this->minifyLess($srcFile, $dstFile);
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

  /**
   * Reset cache and recreate all minified files
   * @param HookEvent $event
   * @return void
   * @throws WireException
   */
  public function resetCache(HookEvent $event): void
  {
    wire()->cache->delete('rockdevtools-filenames-*');
  }

  /**
   * Ensures that given path is a path within the PW root.
   *
   * Usage:
   * $rockdevtools->toPath("/site/templates/foo.css");
   * $rockdevtools->toPath("/var/www/html/site/templates/foo.css");
   * @param string $path
   * @return string
   */
  public function toPath(string $path): string
  {
    $path = Paths::normalizeSeparators($path);
    $root = wire()->config->paths->root;
    if (str_starts_with($path, $root)) return $path;
    return $root . ltrim($path, '/');
  }
}

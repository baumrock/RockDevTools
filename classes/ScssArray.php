<?php

namespace RockDevTools;

use ProcessWire\Scss;
use function ProcessWire\wire;
use function ProcessWire\rockdevtools;

/**
 * Array of SCSS filenames with helper to compile into a single CSS output.
 */
class ScssArray extends FilenameArray
{
  /**
   * Compile the added SCSS files into a single CSS file.
   *
   * - Uses a temporary master .scss when multiple files were added.
   * - Sets SCSSPHP importPaths to all source folders + a computed common root.
   * - Produces root-relative @imports for deterministic resolution.
   *
   * @param string $dst Destination CSS filepath
   * @param string $style Output style ('compressed', 'expanded', ...)
   * @param bool $sourceMap Inline sourcemap
   * @return void
   */
  public function saveSCSS(
    string $dst,
    string $style = 'compressed',
    bool $sourceMap = false,
  ): void {
    /** @var Scss $scss */
    $scss = wire()->modules->get('Scss');
    $compiler = $scss->compiler();
    $compiler->setOutputStyle($style);
    if ($sourceMap) $compiler->setSourceMap($compiler::SOURCE_MAP_INLINE);

    // Gather all unique import paths from the added files (folder of each file).
    $importPaths = [];
    foreach ($this as $file) {
      $importPaths[] = $this->normalizePath(dirname($file));
    }
    $importPaths = array_values(array_unique($importPaths));

    // Sort longest path first so the most specific path matches first.
    usort($importPaths, fn($a, $b) => strlen($b) <=> strlen($a));

    // Add a common root so we can reference everything root-relative.
    $commonRoot = $this->commonRoot($importPaths);
    if ($commonRoot) {
      $importPaths[] = $commonRoot;
      // Keep unique, preserve order (specific first, then root).
      $importPaths = array_values(array_unique($importPaths));
    }

    $compiler->setImportPaths($importPaths);

    // Get all added files as an array.
    $files = iterator_to_array($this);
    if (empty($files)) return;

    // Build source: master content if multiple files, otherwise single file.
    if (count($files) > 1) {
      $masterContent = $this->buildMasterContent($files, $commonRoot ?: null);
      $tmpFile = tempnam(sys_get_temp_dir(), 'scss_master_') . '.scss';
      wire()->files->filePutContents($tmpFile, $masterContent);
      $source = $tmpFile;
    } else {
      $source = array_values($files)[0];
    }

    // Read the content of the source file (master or single file).
    $scssContent = wire()->files->fileGetContents($source);

    // Compile the SCSS content. Passing the source path helps resolve relative imports.
    $css = $compiler->compileString($scssContent, $source)->getCss();

    // Optionally post-process the compiled CSS with RockCSS features.
    $css = rockdevtools()->rockcss()->compile($css);

    // Write the final CSS output to the destination file.
    wire()->files->filePutContents($dst, $css);

    // Clean up the temporary master file if one was created.
    if (isset($tmpFile)) {
      @unlink($tmpFile);
    }
  }

  /**
   * Build the master @import content from a list of absolute file paths.
   * Imports are made root-relative to $root if given; basename underscore is stripped.
   *
   * @param array<int,string> $files
   * @param string|null $root
   * @return string
   */
  protected function buildMasterContent(array $files, ?string $root): string
  {
    $lines = [];
    foreach ($files as $file) {
      $file = $this->normalizePath($file);
      $import = $root
        ? $this->relativePath($file, $root)
        : basename($file);

      // Remove .scss extension and leading underscore on basename only.
      $import = $this->stripScssExt($import);
      $import = $this->stripPartialUnderscoreOnBasename($import);

      // Always use forward slashes in imports.
      $import = str_replace('\\', '/', $import);

      $lines[] = '@import "' . $import . '";';
    }
    return implode("\n", $lines) . "\n";
  }

  /**
   * Computes a relative import path for a given file based on available import paths.
   *
   * This robust variant prefers the common root (if available), otherwise
   * falls back to the most specific import path hit.
   *
   * @param string $file Absolute file path
   * @param array<int,string> $importPaths Normalized import paths (longest-first)
   * @param string|null $root Common root path if known
   * @return string Root-relative (preferred) or importPath-relative path without .scss and underscore on basename
   */
  protected function getRelativeImportPath(string $file, array $importPaths, ?string $root = null): string
  {
    $file = $this->normalizePath($file);

    // Prefer root-relative if we have a common root.
    if ($root) {
      $rel = $this->relativePath($file, $root);
      $rel = $this->stripScssExt($rel);
      $rel = $this->stripPartialUnderscoreOnBasename($rel);
      return $rel;
    }

    // Otherwise, find the most specific matching importPath (array is longest-first).
    foreach ($importPaths as $importPath) {
      if (strpos($file, $importPath . '/') === 0 || $file === $importPath) {
        $relative = ltrim(substr($file, strlen($importPath)), '/\\');
        $relative = $this->stripScssExt($relative);
        $relative = $this->stripPartialUnderscoreOnBasename($relative);
        return $relative;
      }
    }

    // Fallback: basename only.
    $base = $this->stripScssExt(basename($file));
    return ltrim($this->stripLeadingUnderscore($base), '/');
  }

  /**
   * Compute the common root directory for a set of directories.
   * Returns normalized path or empty string if none.
   *
   * @param array<int,string> $dirs
   * @return string
   */
  protected function commonRoot(array $dirs): string
  {
    $dirs = array_values(array_filter(array_map([$this, 'normalizePath'], $dirs)));
    if (count($dirs) <= 1) {
      return $dirs[0] ?? '';
    }

    $first = $dirs[0];
    $prefix = $first;

    foreach ($dirs as $dir) {
      $i = 0;
      $max = min(strlen($prefix), strlen($dir));
      while ($i < $max && $prefix[$i] === $dir[$i]) $i++;
      $prefix = rtrim(substr($prefix, 0, $i), '/');
      if ($prefix === '') break;
    }

    // Trim to directory boundary
    $pos = strrpos($prefix, '/');
    if ($pos !== false) {
      $prefix = substr($prefix, 0, $pos);
    }
    return $prefix ?: '';
  }

  /**
   * Normalize a filesystem path to forward slashes and without trailing slash.
   *
   * @param string $path
   * @return string
   */
  protected function normalizePath(string $path): string
  {
    $path = str_replace('\\', '/', $path);
    // Collapse duplicate slashes
    $path = preg_replace('~/+~', '/', $path);
    // Trim trailing slash except for root "/"
    if ($path !== '/' && substr($path, -1) === '/') {
      $path = substr($path, 0, -1);
    }
    return $path;
  }

  /**
   * Compute $path relative to $base (both absolute, normalized).
   *
   * @param string $path
   * @param string $base
   * @return string
   */
  protected function relativePath(string $path, string $base): string
  {
    $path = $this->normalizePath($path);
    $base = $this->normalizePath($base);

    // If path starts with base, strip it.
    if (strpos($path, $base . '/') === 0) {
      return ltrim(substr($path, strlen($base)), '/');
    }
    if ($path === $base) return '';

    // Fallback: manual relative computation
    $pSeg = explode('/', $path);
    $bSeg = explode('/', $base);

    // Drop common prefix
    $i = 0;
    $len = min(count($pSeg), count($bSeg));
    while ($i < $len && $pSeg[$i] === $bSeg[$i]) $i++;
    $pRest = array_slice($pSeg, $i);
    $bRest = array_slice($bSeg, $i);

    $ups = array_fill(0, max(count($bRest), 0), '..');
    $rel = array_merge($ups, $pRest);
    return implode('/', array_filter($rel, fn($s) => $s !== ''));
  }

  /**
   * Strip ".scss" extension if present.
   *
   * @param string $p
   * @return string
   */
  protected function stripScssExt(string $p): string
  {
    return (substr($p, -5) === '.scss') ? substr($p, 0, -5) : $p;
  }

  /**
   * Strip a leading underscore if present.
   *
   * @param string $name
   * @return string
   */
  protected function stripLeadingUnderscore(string $name): string
  {
    return (isset($name[0]) && $name[0] === '_') ? substr($name, 1) : $name;
  }

  /**
   * Strip a leading underscore from the basename while preserving subfolders.
   * E.g. "components/Image/_TextImage" -> "components/Image/TextImage"
   *
   * @param string $rel
   * @return string
   */
  protected function stripPartialUnderscoreOnBasename(string $rel): string
  {
    $rel = $this->normalizePath($rel);
    $parts = explode('/', $rel);
    $last  = array_pop($parts) ?: '';
    $last  = $this->stripLeadingUnderscore($last);
    return ($parts ? implode('/', $parts) . '/' : '') . $last;
  }
}

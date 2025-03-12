<?php

namespace RockDevTools;

use ProcessWire\Scss;
use function ProcessWire\wire;
use function ProcessWire\rockdevtools;

class ScssArray extends FilenameArray
{
  public function saveSCSS(string $dst, string $style = 'compressed'): void
  {
    /** @var Scss $scss */
    $scss = wire()->modules->get('Scss');
    $compiler = $scss->compiler();
    $compiler->setOutputStyle($style);

    // Gather all unique import paths from the added files
    $importPaths = [];
    foreach ($this as $file) {
      $importPaths[] = dirname($file);
    }
    $importPaths = array_unique($importPaths);
    $compiler->setImportPaths($importPaths);

    // Get all added files as an array.
    $files = iterator_to_array($this);
    if (empty($files)) return;

    // If more than one file was added, create a temporary master file with import statements.
    if (count($files) > 1) {
      $masterContent = "";
      foreach ($files as $file) {
        // Compute a relative path for the import based on our import paths.
        $relative = $this->getRelativeImportPath($file, $importPaths);
        $masterContent .= '@import "' . $relative . '";' . "\n";
      }
      // Write the master file to a temporary file.
      $tmpFile = tempnam(sys_get_temp_dir(), 'scss_master_') . '.scss';
      wire()->files->filePutContents($tmpFile, $masterContent);
      $source = $tmpFile;
    } else {
      // Only one file: use it as the source.
      $source = $files[0];
    }

    // Read the content of the source file (master or single file)
    $scssContent = wire()->files->fileGetContents($source);

    // Compile the SCSS content. Passing the source path helps resolve relative imports.
    $css = $compiler->compileString($scssContent, $source)->getCss();

    // Optionally post-process the compiled CSS with RockCSS features.
    $css = rockdevtools()->rockcss()->compile($css);

    // Write the final CSS output to the destination file.
    wire()->files->filePutContents($dst, $css);

    // Clean up the temporary master file if one was created.
    if (isset($tmpFile)) {
      unlink($tmpFile);
    }
  }

  /**
   * Computes a relative import path for a given file based on available import paths.
   *
   * This method tries to remove one of the import path prefixes from the absolute file path,
   * then strips the .scss extension and a leading underscore (if it’s a partial).
   *
   * @param string $file
   * @param array $importPaths
   * @return string
   */
  protected function getRelativeImportPath(string $file, array $importPaths): string
  {
    foreach ($importPaths as $importPath) {
      if (strpos($file, $importPath) === 0) {
        $relative = ltrim(substr($file, strlen($importPath)), '/\\');
        // Remove the .scss extension.
        if (substr($relative, -5) === '.scss') {
          $relative = substr($relative, 0, -5);
        }
        // Remove a leading underscore for partials.
        if (substr($relative, 0, 1) === '_') {
          $relative = substr($relative, 1);
        }
        return $relative;
      }
    }
    // Fallback: return the basename without extension.
    return basename($file, '.scss');
  }
}

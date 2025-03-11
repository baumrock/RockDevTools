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

    // Use the first valid file as your dedicated main SCSS file.
    $files = iterator_to_array($this);
    if(empty($files)) return;
    $mainFile = null;
    foreach ($files as $file) {
      if(is_string($file) && !empty($file)) {
        $mainFile = $file;
        break;
      }
    }
    if(!$mainFile) return;

    // Read the content of the main SCSS file.
    $scssContent = wire()->files->fileGetContents($mainFile);

    // Compile the SCSS content using the compiler.
    $css = $compiler->compileString($scssContent)->getCss();

    // Optionally post-process the compiled CSS with RockCSS features.
    $css = rockdevtools()->rockcss()->compile($css);

    // Write the final CSS output to the destination file.
    wire()->files->filePutContents($dst, $css);
  }
}

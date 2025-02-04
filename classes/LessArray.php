<?php

namespace RockDevTools;

use ProcessWire\Less;

use function ProcessWire\rockdevtools;
use function ProcessWire\wire;

class LessArray extends FilenameArray
{
  public function saveLESS(string $dst): void
  {
    /** @var Less $less */
    $less = wire()->modules->get('Less');
    foreach ($this as $file) $less->addFile($file);
    $css = $less->getCss();
    $css = rockdevtools()->rockcss()->compile($css);
    wire()->files->filePutContents($dst, $css);
  }
}

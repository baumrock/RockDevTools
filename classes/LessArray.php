<?php

namespace RockDevTools;

use function ProcessWire\wire;

class LessArray extends FilenameArray
{
  public function saveLESS(string $dst): void
  {
    /** @var Less $less */
    $less = wire()->modules->get('Less');
    foreach ($this as $file) $less->addFile($file);
    $less->saveCss($dst);
  }
}

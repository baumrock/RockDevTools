<?php

namespace RockDevTools;

use MatthiasMullie\Minify\CSS;
use ProcessWire\Less;

use function ProcessWire\rockdevtools;
use function ProcessWire\wire;

class LessArray extends FilenameArray
{
  public function saveLESS(
    string $dst,
    bool $sourceMap = false,
    ?bool $minify = null,
  ): void {
    /** @var Less $less */
    $less = wire()->modules->get('Less');
    $less->setOption('sourceMap', $sourceMap);
    foreach ($this as $file) $less->addFile($file);
    $css = $less->getCss();
    $css = rockdevtools()->rockcss()->compile($css);

    // minify?
    if ($minify === null) $minify = str_ends_with($dst, '.min.css');

    // then write resulting css back to file
    if ($minify) {
      $minifier = new CSS();
      $minifier->add($css);
      $minifier->minify($dst);
    } else {
      wire()->files->filePutContents($dst, $css);
    }
  }
}

<?php

namespace RockDevTools;

use MatthiasMullie\Minify\CSS;

use function ProcessWire\rockdevtools;
use function ProcessWire\wire;

class CssArray extends FilenameArray
{
  public function saveCSS(string $to): void
  {
    // first we merge all the css
    $css = '';
    foreach ($this as $file) $css .= @wire()->files->fileGetContents($file);

    // then we compile rockcss features (grow/shrink/pxrem)
    $css = rockdevtools()->rockcss()->compile($css);

    // then write resulting css back to file
    if (str_ends_with($to, '.min.css')) {
      // minified
      $minifier = new CSS();
      $minifier->add($css);
      $minifier->minify($to);
    } else {
      // unminified
      wire()->files->filePutContents($to, $css);
    }
  }
}

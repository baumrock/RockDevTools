<?php

namespace RockDevTools;

use MatthiasMullie\Minify\CSS;

use function ProcessWire\wire;

class CssArray extends FilenameArray
{
  public function saveCSS(string $to): void
  {
    if (str_ends_with($to, '.min.css')) {
      // minify content
      $minifier = new CSS();
      foreach ($this as $file) $minifier->add($file);
      $minifier->minify($to);
    } else {
      // merge content
      $css = '';
      foreach ($this as $file) $css .= @wire()->files->fileGetContents($file);
      wire()->files->filePutContents($to, $css);
    }
  }
}

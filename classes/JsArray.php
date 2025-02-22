<?php

namespace RockDevTools;

use MatthiasMullie\Minify\JS;

use function ProcessWire\wire;

class JsArray extends FilenameArray
{
  public function saveJS(string $to): void
  {
    if (str_ends_with($to, '.min.js')) {
      // minify content
      $minifier = new JS();
      foreach ($this as $file) $minifier->add($file);
      $minifier->minify($to);
    } else {
      // merge content
      $js = '';
      foreach ($this as $file) $js .= @wire()->files->fileGetContents($file);
      wire()->files->filePutContents($to, $js);
    }
  }
}

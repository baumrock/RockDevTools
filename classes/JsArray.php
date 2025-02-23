<?php

namespace RockDevTools;

use MatthiasMullie\Minify\JS;

use function ProcessWire\wire;

class JsArray extends FilenameArray
{
  public function saveJS(string $to): void
  {
    $js = '';
    if (str_ends_with($to, '.min.js')) {
      // minify content
      foreach ($this as $file) {
        $js .= ';'; // fix concatenating issues
        if (str_ends_with(strtolower($file), '.min.js')) {
          // add file as is
          $js .= @wire()->files->fileGetContents($file);
        } else {
          $minifier = new JS();
          $minifier->add($file);
          $js .= $minifier->minify();
        }
      }
    } else {
      // merge content
      foreach ($this as $file) $js .= @wire()->files->fileGetContents($file);
    }
    wire()->files->filePutContents($to, $js);
  }
}

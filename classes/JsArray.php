<?php

namespace RockDevTools;

use MatthiasMullie\Minify\JS;

use function ProcessWire\wire;

class JsArray extends FilenameArray
{
  public function saveJS(string $to, $minify = null): void
  {
    $js = '';

    // if minify is not set we auto-detect it from the filename
    if ($minify === null) $minify = str_ends_with($to, '.min.js');

    // merge all files
    if ($minify) {
      // minify is enabled
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
      // minify is disabled
      // only merge content
      foreach ($this as $file) $js .= @wire()->files->fileGetContents($file);
    }

    // write to file
    wire()->files->filePutContents($to, $js);
  }
}

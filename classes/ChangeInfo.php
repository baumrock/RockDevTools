<?php

namespace RockDevTools;

use ProcessWire\WireData;

class ChangeInfo extends WireData
{
  public function __construct(
    public string $message,
    public string $src,
    public string $dst,
  ) {
    $this->setArray([
      'message' => $message,
      'srcFile' => $src,
      'srcM' => date('Y-m-d H:i:s', @filemtime($src)),
      'dstFile' => $dst,
      'dstM' => date('Y-m-d H:i:s', @filemtime($dst)),
    ]);
  }
}

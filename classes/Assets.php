<?php

namespace RockDevTools;

use ProcessWire\Wire;
use ProcessWire\WireException;

use function ProcessWire\wire;

class Assets extends Wire
{

  public function css(): CssArray
  {
    return new CssArray();
  }

  public function js(): JsArray
  {
    return new JsArray();
  }

  public function less(): LessArray
  {
    if (!wire()->modules->get('Less')) {
      throw new WireException('Less module not found');
    }
    return new LessArray();
  }
}

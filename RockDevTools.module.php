<?php

namespace ProcessWire;

function rockdevtools(): RockDevTools
{
  return wire()->modules->get('RockDevTools');
}

// info / infoc
class RockDevTools extends WireData implements Module, ConfigurableModule
{
  public function init() {}

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    return $inputfields;
  }
}

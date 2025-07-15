<?php

namespace ProcessWire;

use RockDevTools\Assets;
use RockDevTools\LiveReload;
use RockDevTools\RockCSS;

function rockdevtools(): RockDevTools
{
  return wire()->modules->get('RockDevTools');
}

/**
 * @author Bernhard Baumrock, 14.01.2025
 * @license Licensed under MIT
 * @link https://www.baumrock.com
 */
require_once __DIR__ . '/vendor/autoload.php';
class RockDevTools extends WireData implements Module, ConfigurableModule
{
  public $debug = false;
  public $livereload;

  private $rockcss = false;

  public function __construct()
  {
    // early exit if not enabled to keep the footprint as low as possible
    if (!wire()->config->rockdevtools) return;

    // add classloader and load livereload
    wire()->classLoader->addNamespace('RockDevTools', __DIR__ . '/classes');
    $this->livereload = new LiveReload();
  }

  public function __debugInfo()
  {
    return [
      'livereload' => $this->livereload->filesToWatch(),
    ];
  }

  public function init()
  {
    // early exit if not enabled to keep the footprint as low as possible
    if (!wire()->config->rockdevtools) return;

    // minify assets
    $this->assets()->minify(__DIR__ . '/src', __DIR__ . '/dst');

    // add panel to support livereload on tracy blue screen
    $this->livereload->addBlueScreenPanel();

    // hooks
    wire()->addHookAfter('Modules::refresh', $this, 'resetCache');
    wire()->addHookAfter('Page::render', $this->livereload, 'addLiveReloadMarkup');
  }

  public function assets(?string $root = null): Assets
  {
    return new Assets($root);
  }

  public function getModuleConfigInputfields(InputfieldWrapper $inputfields)
  {
    $inputfields->add([
      'type' => 'markup',
      'label' => 'LiveReload Files List',
      'value' => wire()->files->render(__DIR__ . '/markup/livereloadinfo.php'),
      'icon' => 'magic',
    ]);
    return $inputfields;
  }

  /**
   * Reset cache and recreate all minified files
   * @param HookEvent $event
   * @return void
   * @throws WireException
   */
  public function resetCache(HookEvent $event): void
  {
    wire()->cache->delete('rockdevtools-filenames-*');
  }

  /**
   * @return RockCSS
   */
  public function rockcss()
  {
    if (!$this->rockcss) $this->rockcss = new RockCSS();
    return $this->rockcss;
  }
}

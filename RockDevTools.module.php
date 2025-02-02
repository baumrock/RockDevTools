<?php

namespace ProcessWire;

use RockDevTools\Assets;
use RockDevTools\LiveReload;

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
class RockDevTools extends WireData implements Module
{
  public $livereload;

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

  public function assets(): Assets
  {
    return new Assets();
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
   * Ensures that given path is a path within the PW root.
   *
   * Usage:
   * $rockdevtools->toPath("/site/templates/foo.css");
   * $rockdevtools->toPath("/var/www/html/site/templates/foo.css");
   * @param string $path
   * @return string
   */
  public function toPath(string $path): string
  {
    $path = Paths::normalizeSeparators($path);
    $root = wire()->config->paths->root;
    if (str_starts_with($path, $root)) return $path;
    return $root . ltrim($path, '/');
  }
}

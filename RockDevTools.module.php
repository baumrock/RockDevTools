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
  public $debugAssetTools = false;
  public $autoLogin = false;
  /** @var string Redirect path after auto-login. Empty = default (page id 2). */
  public $autoLoginRedirect = '';
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
      'livereload' => $this->livereload ? $this->livereload->filesToWatch() : null,
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

    $this->addAutoLoginHook();
  }

  /**
   * Add auto-login URL hook only when debug + DDEV + autoLogin enabled.
   */
  protected function addAutoLoginHook(): void
  {
    if (!wire()->config->debug) return;
    if (!getenv('DDEV_HOSTNAME')) return;
    if (!$this->autoLogin) return;

    wire()->addHook('/auto-login', $this, 'autoLogin');
  }

  /**
   * Force login as superuser and redirect (called by /auto-login hook).
   * Redirect target: autoLoginRedirect if set, otherwise page id 2.
   */
  public function autoLogin(HookEvent $event): void
  {
    $user = wire()->users->get('roles=superuser');
    if (!$user->id) return;
    wire()->session->forceLogin($user);
    $url = trim((string) $this->autoLoginRedirect) !== ''
      ? wire()->config->urls->root . ltrim($this->autoLoginRedirect, '/')
      : wire()->pages->get(2)->url;
    wire()->session->redirect($url);
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
      'value' => $this->livereload
        ? wire()->files->render(__DIR__ . '/markup/livereloadinfo.php')
        : '<p>RockDevTools is disabled (<code>$config->rockdevtools</code> is not set to <code>true</code>).</p>',
      'icon' => 'magic',
    ]);

    $inputfields->add([
      'type' => 'checkbox',
      'label' => 'Debug Asset Tools',
      'name' => 'debugAssetTools',
      'checked' => $this->debugAssetTools,
      'notes' => 'If enabled, the asset tools will log debug information to the Tracy debug bar.',
    ]);

    $inputfields->add([
      'type' => 'checkbox',
      'label' => 'Auto Login',
      'name' => 'autoLogin',
      'checked' => $this->autoLogin,
      'notes' => 'When enabled (and only when debug mode + DDEV), visiting /auto-login will log in as superuser and redirect. Useful for manual login or browser automation.',
    ]);

    $inputfields->add([
      'type' => 'text',
      'label' => 'Auto Login Redirect',
      'name' => 'autoLoginRedirect',
      'value' => $this->autoLoginRedirect,
      'placeholder' => '',
      'notes' => 'Path to redirect to after auto-login (e.g. /tool). Empty = default (page id 2).',
      'showIf' => 'autoLogin=1',
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

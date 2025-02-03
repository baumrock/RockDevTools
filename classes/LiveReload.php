<?php

namespace RockDevTools;

use Nette\Utils\FileInfo;
use ProcessWire\HookEvent;
use ProcessWire\Wire;
use Tracy\Debugger;

use function ProcessWire\rockdevtools;
use function ProcessWire\wire;

class LiveReload extends Wire
{
  public function __construct()
  {
    wire()->addHookBefore('Session::init', $this, 'addSSE');
  }

  public function addBlueScreenPanel(): void
  {
    if (!wire()->modules->isInstalled('TracyDebugger')) return;
    $blueScreen = Debugger::getBlueScreen();
    $blueScreen->addPanel(function () {
      return [
        'tab' => 'LiveReload',
        'panel' => 'RockDevTools LiveReload is active' . $this->scriptTag(),
      ];
    });
  }

  protected function addLiveReloadMarkup(HookEvent $event): void
  {
    if (wire()->config->ajax) return;
    if (wire()->config->external) return;
    $event->return .= $this->scriptTag();
  }

  protected function addSSE(HookEvent $event): void
  {
    if (!str_ends_with((string)@$_GET['it'], 'rockdevtools-livereload')) return;

    // disable tracy for the SSE stream
    wire()->config->tracy = ['enabled' => false];

    // start the loop for sse stream
    $this->loop();
  }

  public function filesToWatch(): array
  {
    // note: do not cache files to watch
    // to make sure newly created files trigger a reload
    require dirname(__DIR__) . '/src/livereload.php';
    $configfile = wire()->config->paths->site . 'config-livereload.php';
    if (is_file($configfile)) require $configfile;
    $arr = [];
    foreach ($files->collect() as $file) {
      /** @var FileInfo $file */
      $arr[] = (string)$file;
    }
    return $arr;
  }

  public function findModifiedFile(int $since): string|false
  {
    // go through all files
    foreach ($this->filesToWatch() as $file) {
      if (@filemtime($file) > $since) return $file;
    }
    return false;
  }

  public function loop(): void
  {
    // we dont want warnings in the stream
    // for debugging you can uncomment this line
    error_reporting(E_ALL & ~E_WARNING);

    header('Cache-Control: no-cache');
    header('Content-Type: text/event-stream');

    // reset log
    wire()->log->prune('livereload', 1);

    // get list of files to watch
    $files = $this->filesToWatch();

    // start loop
    $start = time();
    $executed = false;
    while (true) {
      $file = $this->findModifiedFile($start);

      // file changed
      if (!$executed && $file) {
        wire()->log->save('livereload', "File changed: $file", [
          'showURL' => false,
          'showUser' => false,
        ]);
        $actionFile = wire()->config->paths->site . 'livereload.php';
        if (is_file($actionFile)) {
          wire()->log->save('livereload', "Loading actionfile $actionFile");
          include $actionFile;
        } else {
          wire()->log->save('livereload', "No actionfile $actionFile");
        }
        $executed = true;
      }

      // send message to frontend
      $this->sse($file);

      // add note to log
      if ($file) @ob_end_flush();
      while (ob_get_level() > 0) @ob_end_flush();

      // stop loop when connection is aborted
      if (connection_aborted()) break;

      // sleep until next try
      $sleepSeconds = (float)wire()->config->livereload ?: 1.0;
      usleep($sleepSeconds * 1000000);
    }
  }

  private function scriptTag(): string
  {
    $src = wire()->config->urls(rockdevtools()) . 'dst/livereload.min.js';
    $src = wire()->config->versionUrl($src);
    $url = wire()->config->urls->root . 'rockdevtools-livereload';
    $force = (int)wire()->config->livereloadForce;
    return "<script
      src='$src'
      data-url='$url'
      data-force='$force'
      ></script>";
  }

  /**
   * Send SSE message to client
   * @return void
   */
  public function sse($msg)
  {
    echo "data: $msg\n\n";
    echo str_pad('', 8186) . "\n";
    flush();
  }

  public function watch(): void
  {
    if (!wire()->config->livereload) return;
    if (!str_ends_with((string)@$_GET['it'], 'rockdevtools-livereload')) return;
  }
}

<?php

namespace RockDevTools;

use Nette\Utils\Finder;

use function ProcessWire\wire;

/**
 * List of files to watch for changes
 * use bd(rockdevtools()->livereload->filesToWatch()) to inspect
 */

$files = Finder::findFiles([
  '*.php',
  '*.module',
  '*.js',
  '*.css',
  '*.latte',
  '*.twig',
  '*.less',
])
  ->from(wire()->config->paths->site)
  ->exclude('assets/backups/*')
  ->exclude('assets/cache/*')
  ->exclude('assets/files/*')
  ->exclude('assets/logs/*')
  ->exclude('*/lib/*')
  ->exclude('*/dist/*')
  ->exclude('*/dst/*')
  ->exclude('*/build/*')
  ->exclude('*/uikit/src/*')
  ->exclude('*/TracyDebugger/tracy-*')
  ->exclude('*/TracyDebugger/scripts/*')
  ->exclude('*/vendor/*');

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
  ->from(wire()->config->paths->root)
  ->exclude('wire/*')
  ->exclude('.*/*')
  ->exclude('node_modules/*')
  ->exclude('site/assets/backups/*')
  ->exclude('site/assets/cache/*')
  ->exclude('site/assets/files/*')
  ->exclude('site/assets/logs/*')
  ->exclude('*/lib/*')
  ->exclude('*/dist/*')
  ->exclude('*/dst/*')
  ->exclude('*/build/*')
  ->exclude('*/uikit/src/*')
  ->exclude('*/TracyDebugger/tracy-*')
  ->exclude('*/TracyDebugger/scripts/*')
  ->exclude('*/vendor/*');

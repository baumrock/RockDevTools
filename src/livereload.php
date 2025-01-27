<?php

namespace RockDevTools;

use Nette\Utils\Finder;

use function ProcessWire\wire;

/**
 * List of files to watch for changes
 * use bd(rockdevtools()->livereload->filesToWatch()) to inspect
 */

$files = Finder::findFiles(['*.php', '*.js', '*.css', '*.latte', '*.less'])
  ->from(wire()->config->paths->site)
  ->exclude('*/cache/*')
  ->exclude('*/lib/*')
  ->exclude('*/dist/*')
  ->exclude('*/dst/*')
  ->exclude('*/build/*')
  ->exclude('*/uikit/src/*')
  ->exclude('*/TracyDebugger/tracy-*')
  ->exclude('*/TracyDebugger/scripts/*')
  ->exclude('*/vendor/*');

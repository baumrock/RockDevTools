<?php

namespace RockDevTools;

/**
 * List of files to watch for changes
 * use bd(rockdevtools()->livereload->filesToWatch()) to inspect
 */

$files = (new FilenameArray())
  // watch files in root folder
  ->add('*.*')
  // watch files in /site folder
  ->add('/site/*.php')
  // watch custom page classes
  ->add('/site/classes/*.php')
  // watch migrations
  ->add('/site/RockMigrations/**.*')
  // watch files in /site/templates folder recursively
  ->add('/site/templates/**.{css,js,less,php}', 4)
  // watch latte files in /site/templates folder recursively (6 levels)
  ->add('/site/templates/**.latte', 6)
  // watch all assets in /site/modules
  ->add('/site/modules/**.{css,js,less,php,latte}', 4)
  // do not watch uikit files
  ->remove('/site/templates/uikit/**.*');

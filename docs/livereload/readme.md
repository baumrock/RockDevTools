# LiveReload

The LiveReload module provides automatic browser refresh functionality when files in your ProcessWire project are modified. It uses Server-Sent Events (SSE) to efficiently detect and respond to file changes.

## Configuration

### Default Configuration

The default configuration watches several key directories and file types:

```php
$files = (new FilenameArray())
  // Root folder files
  ->add('*.*')
  // PHP files in /site folder
  ->add('/site/*.php')
  // Template files (recursive, 4 levels deep)
  ->add('/site/templates/**.{css,js,less,php}', 4)
  // Latte template files (recursive, 6 levels deep)
  ->add('/site/templates/**.latte', 6)
  // Module assets
  ->add('/site/modules/**.{css,js,less,php,latte}', 4)
  // Exclude uikit files
  ->remove('/site/templates/uikit/**.*');
```

### Custom Configuration

You can create a custom configuration file at `site/config-livereload.php`. This file will be loaded after the default configuration, allowing you to:
- Add additional files/patterns to watch
- Remove files/patterns from being watched
- Override the default configuration entirely

Example:

```php
<?php
$files->add('path/to/file.php');
$files->remove('/site/foo/bar/**.*');
```

## Methods

### add()

Add files or patterns to watch:

```php
// Add a single file
$files->add('path/to/file.php');

// Add using glob pattern (recursive)
$files->add('/site/templates/**.css', 4); // Watch 4 levels deep
$files->add('/site/modules/**.{js,php}', 6); // Watch multiple extensions
```

### remove()

Remove files or patterns from being watched:

```php
// Remove a single file
$files->remove('path/to/file.php');

// Remove using glob pattern
$files->remove('/site/templates/uikit/**.*'); // Remove all uikit files
```

## Advanced Usage

### Custom Actions

You can create a `site/livereload.php` file that will be executed when file changes are detected. This allows you to run custom actions before the browser refresh occurs.

Here's an example of a `site/livereload.php` file:

```php
<?php

/**
 * This file will be loaded by RockDevTools whenever a watched file changed.
 */
exec('npm run build');
```

The file is executed automatically when changes are detected, before the browser refresh occurs. This ensures your compiled assets are up to date when the page reloads.


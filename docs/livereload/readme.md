# LiveReload

The LiveReload module provides automatic browser refresh functionality when files in your ProcessWire project are modified. It uses Server-Sent Events (SSE) to efficiently detect and respond to file changes.

## Setup

To use the livereload feature you need to enable the RockDevTools module from your config:

```php
$config->rockdevtools = true;
```

Note: Enable RockDevTools only on development! RockDevTools is designed to never ever run on production.

## Configuration

### Watched Files

RockDevTools uses Nette's File Finder to find files to watch. The default configuration at the moment of writing this documentation watches the following files:

```php
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
```

### Custom Configuration

You can create a custom configuration file at `site/config-livereload.php`. This file will be loaded after the default configuration, allowing you to:
- Add additional files/patterns to watch
- Remove files/patterns from being watched
- Override the default configuration entirely

The `$finder` object from above will be available as `$finder` in the custom configuration file. See the nette docs how to use the finder: https://doc.nette.org/en/utils/finder

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


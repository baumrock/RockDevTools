# LiveReload

The LiveReload module provides automatic browser refresh functionality when files in your ProcessWire project are modified. It uses Server-Sent Events (SSE) to efficiently detect and respond to file changes, eliminating the need to manually refresh your browser during development.

## Features

- **Automatic Browser Refresh**: Pages automatically reload when watched files change
- **Smart Change Detection**: Only reloads when necessary, avoiding unnecessary refreshes
- **Unsaved Changes Protection**: Warns before reloading if you have unsaved form changes
- **Custom Actions**: Execute custom scripts before reload (e.g., build processes)
- **Flexible Configuration**: Customize which files to watch and exclude
- **Development Only**: Designed to never run in production environments

## Setup

### Basic Setup

To use the LiveReload feature, enable the RockDevTools module in your configuration:

```php
// /site/config-local.php
$config->rockdevtools = true;
```

**Important**: Enable RockDevTools only in development! RockDevTools is designed to never run in production environments.

### Disabling LiveReload

By default, LiveReload is enabled when RockDevTools is enabled. You can disable LiveReload while keeping other RockDevTools features:

```php
// /site/config-local.php
$config->rockdevtools = true;
$config->livereload = false;
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `$config->rockdevtools` | bool | false | Enable RockDevTools module |
| `$config->livereload` | bool | true | Enable/disable LiveReload specifically |
| `$config->livereloadForce` | int | 0 | Force reload even with unsaved changes |

## Configuration

### Disabling LiveReload for Specific Pages

You can disable LiveReload for specific pages or templates using hooks:

```php
// /site/ready.php
wire()->addHookAfter(
  'LiveReload::addLiveReload',
  function (HookEvent $event) {
    $page = $event->arguments(0);

    // Disable for admin pages
    if ($page->template == 'admin') $event->return = false;

    // Disable for specific pages
    if ($page->id == 123) $event->return = false;
  }
);
```

### Watched Files

RockDevTools uses Nette's File Finder to monitor file changes. The default configuration watches these file types:

```php
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
```

### Custom Configuration

Create a custom configuration file at `site/config-livereload.php` to customize which files are watched:

```php
// site/config-livereload.php
<?php

namespace ProcessWire;

// Exclude additional folders from watching
$files->exclude('site/templates/old/*');
$files->exclude('site/modules/legacy/*');

// Add additional file types to watch
$files->append()
  ->files(['*.scss', '*.sass', '*.md'])
  ->from(wire()->config->paths->root);

// Watch files from a specific directory
$files->append()
  ->files('*.json')
  ->from(wire()->config->paths->site . 'config/');
```

### Debugging

To see which files are currently being watched, add this to your `site/ready.php`:

```php
// Debug watched files in TracyDebugger
bd(rockdevtools()->livereload->filesToWatch());
```

## Advanced Usage

### Custom Actions

Create a `site/livereload.php` file to execute custom actions before browser refresh:

```php
// site/livereload.php
<?php

/**
 * This file is executed when watched files change, before browser refresh.
 * Use this for build processes, cache clearing, or other pre-reload tasks.
 */

// Example: Run npm build
exec('npm run build');
```

### Force Reload with Unsaved Changes

By default, LiveReload won't reload if you have unsaved form changes. To force reload regardless:

```php
// /site/config.php
$config->livereloadForce = 1;
```

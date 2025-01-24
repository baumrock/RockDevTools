# RockDevTools

A collection of helpful tools for ProcessWire development.

## Background

This module is intended to be used by developers only and should never be used on a production site. The idea is that you use the module, for example, for asset minification during development. Then you push the already minified files to production and therefore don't need the module to do any work.

## WHY

You might wonder why I started developing a module that has similar feature as other of my modules (like RockFrontend or RockMigrations). There are several reasons for that:

- I am not 100% happy with the implementation of some of the tools in my existing modules and don't want to break backwards compatibility by changing them. This way I can add deprecation notices to the old modules and start over with a new one making the transition for everybody as smooth as possible.
- Some features were added to RockFrontend or RockMigrations as a matter of convenience, even though they didn't really belong there logically. Now that I have so many modules, I've realized that splitting them up makes the code cleaner and easier to maintain. This also makes the existing modules less bloated and more lightweight.
- Hooking into page render was convenient to reduce the amount of necessary setup but also added complexity and made debugging and explaining things harder. Additionally, it had one major drawback that I only realized years later: This concept does not work with template cache! What a bummer. There is no way around it, so I thought it'd be best to move on and develop a better concept.

## Setup

To enable the module you have to add the following line to your `site/config-local.php`:

```php
$config->rockdevtools = true;
```

## Minify

A common need during development is to minify JS, LESS and CSS files. The module provides a simple way to do that:

```php
if($config->rockdevtools) {
  // minify all JS, LESS and CSS files in the /src folder
  // and write them to the /dst folder
  rockdevtools()->minify(__DIR__ . '/src', __DIR__ . '/dst');
}
```

RockDevTools will only minify files that are newer than the destination file.

## Debug

When working on JS/CSS assets it can sometimes be useful to recreate the minified files even if they are not newer than the destination file. To do that you can set the `debug` config option to `true`:

```php
rockdevtools()->debug = true;
```

This will force RockDevTools to recreate all asset files even if no changes have been made.

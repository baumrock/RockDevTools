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

First, install the module like any other ProcessWire module.

Then, to enable the module, you have to add the following line to your `site/config-local.php`:

```php
$config->rockdevtools = true;
```

NOTE: If that config setting is not set, the module will not do anything! This is by design to keep the footprint of the module as small as possible. On the production site it is intended to be disabled!

# Assets

The module can be used to easily minify or merge JS, LESS and CSS assets of a site or a module.

## Site Assets

To minify/merge site assets you can put something likethis in your '/site/templates/_init.php' file:

```php
<?php
// make sure you have set the ProcessWire namespace at the top of the file!
namespace ProcessWire;

if ($config->rockdevtools) {
  $devtools = rockdevtools();

  // force recreating of minified files on every request
  // $devtools->debug = true;

  // parse all less files to css
  $devtools->assets()
    ->less()
    ->add('/site/templates/uikit/src/less/uikit.theme.less')
    ->add('/site/templates/src/*.less')
    ->save('/site/templates/src/.styles.css');

  // merge and minify css files
  $devtools->assets()
    ->css()
    ->add('/site/templates/src/.styles.css')
    ->add('/site/templates/src/.tailwind.css')
    ->save('/site/templates/dst/styles.min.css');

  // merge and minify JS files
  $devtools->assets()
    ->js()
    ->add('/site/templates/uikit/dist/js/uikit.min.js')
    ->add('/site/templates/scripts/main.js')
    ->save('/site/templates/dst/scripts.min.js');
}
```

In your main markup file you can include those minified files like this:

```latte
{rockfrontend()->styleTag('/site/templates/dst/styles.min.css')|noescape}
{rockfrontend()->scriptTag('/site/templates/dst/scripts.min.js', 'defer')|noescape}
```

Which will output something like this:

```html
<link rel="stylesheet" href="/site/templates/dst/styles.min.css?akd84" />
<script src="/site/templates/dst/scripts.min.js?vur4s" defer></script>
```

Note that we are automatically adding a cache busting string to the URL to make sure that the browser always fetches the latest version of the file and does not use a cached version, which can be a problem when working on development machines as you might see an outdated version of the file.

## add()

Add a file to the FilenameArray. This method is versatile and supports both individual files and glob patterns:

```php
// Add a single file
$array->add('/site/templates/styles.css');

// Add multiple files using a glob pattern
// This will add all files in the /site/templates/ directory (not recursively)
$array->add('/site/templates/*.css');

// Add files recursively using **
// This will add all files in the /site/templates/ directory and its subdirectories (default depth 3)
$array->add(
  // pattern
  '/site/templates/**.css',
  // depth
  5,
);
```

### Features

- Supports individual files and glob patterns
- Automatically handles recursive file matching with `**` pattern
- Ensures paths are properly formatted within the ProcessWire root
- Returns `$this` for method chaining
- If a glob pattern is detected (contains `*`), it automatically uses `addAll()` internally

## Minify Folder

When developing ProcessWire modules I like to write my CSS as LESS, because it's very easy to namespace my classes:

```LESS
.my-module {
  .foo {
    border: 1px solid red;
  }
}
```

This ensures that elements with the class `.foo` will only have a red border if they are inside a `.my-module` wrapper. So if any other module also used the `.foo` class it would not get a red border.

Often a module needs more than one CSS/JS file, so RockDevTools provides a single method to minify all files of a source folder and write them to a destination folder:

```php
if($config->rockdevtools) {
  // minify all JS, LESS and CSS files in the /src folder
  // and write them to the /dst folder
  rockdevtools()->assets()->minify(
    __DIR__ . '/src',
    __DIR__ . '/dst',
  );
}
```

RockDevTools will only minify files that are newer than the destination file.

Note that this will NOT merge files to a single file as this feature is intended for backend development.

## Debugging

When working on JS/CSS assets it can sometimes be useful to recreate the minified files even if they are not newer than the destination file. To do that you can set the `debug` config option to `true`:

```php
rockdevtools()->debug = true;
```

This will force RockDevTools to recreate all asset files even if no changes have been made.

Another helpful feature is to dump the list of added files to the TracyDebugger bar:

```php
$devtools->assets()
  ->less()
  ->add('/site/templates/uikit/src/less/uikit.theme.less')
  ->add('/site/templates/src/*.less')
  ->add('/site/templates/RockPageBuilder/**/*.less')

  // dump the list of added files to the TracyDebugger bar
  ->bd()

  ->save('/site/templates/src/.styles.css');
```

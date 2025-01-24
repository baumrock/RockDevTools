## Minify/Merge Site or Module Assets

The module can be used to easily minify or merge JS, LESS and CSS assets of a site or a module.

## Site Assets

To minify/merge site assets you can put something likethis in your '/site/templates/_init.php' file:

```php
if ($config->rockdevtools) {
  $devtools = rockdevtools();

  // force recreating of minified files on every request
  // $devtools->debug = true;

  // parse all less files to css
  $devtools->assets()
    ->less()
    ->add('/site/templates/uikit/src/less/uikit.theme.less')
    ->addAll('/site/templates/src/*.less') // glob example
    ->save('/site/templates/src/uikit.css');

  // merge and minify css files
  $devtools->assets()
    ->css()
    ->add('/site/templates/src/uikit.css')
    ->add('/site/templates/src/tailwind.css')
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
<link rel="stylesheet" href="{wire()->config->versionUrl('/site/templates/dst/styles.min.css')}" />
<script defer src="{wire()->config->versionUrl('/site/templates/dst/scripts.min.js')}"></script>
```

Note that we are using the `versionUrl` function to add a cache busting string to the URL to make sure that the browser always fetches the latest version of the file and does not use a cached version, which can be a problem when working on development machines as you might see an outdated version of the file.

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

## Debug

When working on JS/CSS assets it can sometimes be useful to recreate the minified files even if they are not newer than the destination file. To do that you can set the `debug` config option to `true`:

```php
rockdevtools()->debug = true;
```

This will force RockDevTools to recreate all asset files even if no changes have been made.

# SourceMaps

You might want to generate sourcemaps for easier development. All you need to do is to set the `sourceMap` parameter to true:

```php
$devtools->assets()
  ->less()
  ->add('/site/templates/uikit/src/less/uikit.theme.less')
  ->add('/site/templates/src/*.less')
  ->save(
    '/site/templates/src/.styles.css',
    sourceMap: true,
  );
```

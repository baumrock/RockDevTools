<?php

namespace ProcessWire;

use Nette\Utils\FileInfo;

$files = rockdevtools()->livereload->filesToWatch(
  returnUrls: true,
);
$root = rtrim(wire()->config->paths->root, '/');
$ms = Debug::timer();
$files = array_map(function ($file) use ($root) {
  return [
    'filemtime' => date('Y-m-d H:i:s', filemtime($root . $file)),
    'url' => $file,
  ];
}, $files);
$ms = Debug::timer($ms) * 1000;

$livereload = $config->livereload;
if ($livereload === false) $livereload = 'disabled (false)';
else $livereload = 'enabled (as long as not set to false)';
?>

<table class='uk-table uk-table-striped uk-table-small'>
  <tr>
    <td class='uk-text-nowrap'>$config->rockdevtools</td>
    <td class='uk-width-expand'><?= $config->rockdevtools ?></td>
  </tr>
  <tr>
    <td class='uk-text-nowrap'>$config->livereload</td>
    <td class='uk-width-expand'><?= $livereload ?></td>
  </tr>
  <tr>
    <td class='uk-text-nowrap'>$config->livereloadForce</td>
    <td class='uk-width-expand'><?= $config->livereloadForce ?></td>
  </tr>
  <tr>
    <td class='uk-text-nowrap'>Watched Files</td>
    <td class='uk-width-expand'><?= count($files) ?></td>
  </tr>
  <tr>
    <td class='uk-text-nowrap'>filemtime Performance</td>
    <td class='uk-width-expand'><?= $ms ?>ms</td>
  </tr>
</table>

<link href="https://unpkg.com/tabulator-tables@6.3.1/dist/css/tabulator.min.css" rel="stylesheet">
<script type="text/javascript" src="https://unpkg.com/tabulator-tables@6.3.1/dist/js/tabulator.min.js"></script>
<input type="text" id="livereload-filter" placeholder="Filter files ..." class='uk-input uk-margin-small-bottom'>
<div id="livereload-table"></div>
<script>
  var tabledata = <?= json_encode($files) ?>;
  var table = new Tabulator("#livereload-table", {
    data: tabledata,
    layout: "fitColumns",
    autoColumns: true,
    pagination: "local",
    paginationSize: 20,
    paginationCounter: "rows",
    initialSort: [{
      column: "filemtime",
      dir: "desc"
    }],
    autoColumnsDefinitions: function(defs) {
      defs.forEach((col) => {
        if (col.field === 'filemtime') col.width = 200;
      });
      return defs;
    },
  });

  // save and restore filter
  const filter = document.querySelector('#livereload-filter');
  filter.addEventListener('input', function() {
    localStorage.setItem('livereload-filter', filter.value);
    table.setFilter('url', 'like', filter.value);
  });
  table.on('tableBuilt', function() {
    filter.value = localStorage.getItem('livereload-filter') || '';
    if (filter.value) table.setFilter('url', 'like', filter.value);
  });
</script>
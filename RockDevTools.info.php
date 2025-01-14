<?php

namespace ProcessWire;

$info = [
  'title' => 'RockDevTools',
  'version' => json_decode(file_get_contents(__DIR__ . "/package.json"))->version,
  'summary' => '',
  'autoload' => true,
  'singular' => true,
  'icon' => 'check',
  'requires' => [
    'PHP>=8.1',
  ],
];

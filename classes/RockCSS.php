<?php

namespace RockDevTools;

use ProcessWire\Wire;

class RockCSS extends Wire
{
  private $remBase = 16;
  private $min = 375;
  private $max = 1440;

  public function __debugInfo(): array
  {
    return [
      'max' => $this->max,
      'min' => $this->min,
      'remBase' => $this->remBase,
    ];
  }

  /**
   * Compile given css to css with applied RockCSS rules
   * @param string $css
   * @return string
   */
  public function compile(string $css): string
  {
    $css = $this->compileGrow($css);
    $css = $this->compileShrink($css);
    $css = $this->pxREM($css); // after grow + shrink!
    return $css;
  }

  /**
   * Compile grow() function
   * @param string $css
   * @return string
   */
  public function compileGrow(string $css): string
  {
    if (!str_contains($css, 'grow(')) return $css;
    return preg_replace_callback(
      "/grow\((.*?),(.*?)(,(.*?))?(,(.*?))?\)/",
      function ($match) {
        if (count($match) < 3) return false;
        $match = array_map('trim', $match);
        $from = $match[1];
        $to = $match[2];
        $breakpointMin = (int)($match[4] ?? $this->min);
        $breakpointMax = (int)($match[6] ?? $this->max);
        $diff = (int)$to - (int)$from;

        $percent = "((100vw - {$breakpointMin}px) / ($breakpointMax - $breakpointMin))";
        $result = "clamp($from, $from + $diff * $percent, $to)";
        return $result;
      },
      $css
    );
  }

  /**
   * Compile shrink() function
   * @param string $css
   * @return string
   */
  public function compileShrink(string $css): string
  {
    if (!str_contains($css, 'shrink(')) return $css;
    return preg_replace_callback(
      "/shrink\((.*?),(.*?)(,(.*?))?(,(.*?))?\)/",
      function ($match) {
        if (count($match) < 3) return false;
        $match = array_map('trim', $match);
        $to = $match[1];
        $from = $match[2];
        $breakpointMin = (int)($match[4] ?? $this->min);
        $breakpointMax = (int)($match[6] ?? $this->max);
        $diff = (int)$to - (int)$from;

        $percent = "((100vw - {$breakpointMin}px) / ($breakpointMax - $breakpointMin))";
        $result = "clamp($from, $to - $diff * $percent, $to)";
        return $result;
      },
      $css
    );
  }

  public function pxrem(string $css): string
  {
    if (!str_contains($css, 'pxrem')) return $css;
    return preg_replace_callback("/([0-9\.]+)(pxrem)/", function ($matches) {
      $px = $matches[1];
      $rem = round($px / $this->remBase, 3);
      return $rem . "rem";
    }, $css);
  }

  /**
   * Set remBase, min and max
   * @param array $values
   * @return void
   */
  public function setup(array $values): void
  {
    foreach ($values as $k => $v) $this->$k = $v;
  }
}

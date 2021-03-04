<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;

final class FormatBytesExtension extends AbstractExtension
{
  public function getFunctions(): array
  {
    return [
      new \Twig_Function(
        'format_bytes',
        [$this, 'renderBlock'],
        ['is_safe' => ['html']]
      ),
    ];
  }

  public function renderBlock(string $bytes, int $precision = 2): string
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = (float) max($bytes, 0);
    $pow = min(
      floor(($bytes ? log($bytes) : 0) / log(1024)),
      count($units) - 1
    );

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }
}

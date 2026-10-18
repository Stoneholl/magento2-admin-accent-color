<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

/** Local sRGB contrast calculations for normalized opaque HEX colors. */
class ColorContrast
{
    /**
     * Prefer white when it meets the normal-text threshold; otherwise use black.
     *
     * @param string $background
     * @return string
     */
    public function readableText(string $background): string
    {
        return $this->ratio('#FFFFFF', $background) >= 4.5 ? '#FFFFFF' : '#000000';
    }

    /**
     * Calculate the WCAG relative-luminance contrast ratio without rounding.
     *
     * @param string $foreground
     * @param string $background
     * @return float
     */
    public function ratio(string $foreground, string $background): float
    {
        $first = $this->luminance($foreground);
        $second = $this->luminance($background);
        return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
    }

    /**
     * Convert an sRGB HEX value to relative luminance.
     *
     * @param string $color
     * @return float
     */
    private function luminance(string $color): float
    {
        $channels = [];
        foreach ([1, 3, 5] as $offset) {
            $value = hexdec(substr($color, $offset, 2)) / 255;
            $channels[] = $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }
        return $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;
    }
}

<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Framework\Exception\LocalizedException;

class ColorParser
{
    /**
     * Normalize a supported color to uppercase six-digit HEX.
     *
     * @param mixed $value
     * @return string|null
     * @throws LocalizedException
     */
    public function normalize($value): ?string
    {
        if ($value !== null && !is_string($value)) {
            throw new LocalizedException(__('Enter a color as a HEX or RGB text value.'));
        }
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^#?([0-9a-f]{6})$/i', $value, $matches) === 1) {
            return '#' . strtoupper($matches[1]);
        }

        if (preg_match(
            '/^rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)$/i',
            $value,
            $matches
        ) === 1) {
            $channels = [(int)$matches[1], (int)$matches[2], (int)$matches[3]];
            foreach ($channels as $channel) {
                if ($channel > 255) {
                    throw new LocalizedException(__('RGB channels must be between 0 and 255.'));
                }
            }

            return sprintf('#%02X%02X%02X', $channels[0], $channels[1], $channels[2]);
        }

        throw new LocalizedException(
            __('Enter a six-digit HEX color or an RGB color such as rgb(235, 82, 2).')
        );
    }
}

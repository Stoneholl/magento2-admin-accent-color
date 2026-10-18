<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Framework\Exception\LocalizedException;

/** Normalize a complete local palette without accepting arbitrary CSS or configuration. */
class PaletteSnapshot
{
    /** @var ColorParser */
    private $colorParser;

    /**
     * @param ColorParser $colorParser
     */
    public function __construct(ColorParser $colorParser)
    {
        $this->colorParser = $colorParser;
    }

    /**
     * Require the exact versioned shape and normalize all values before any persistence.
     *
     * @param mixed $data
     * @return array
     * @throws LocalizedException
     */
    public function normalize($data): array
    {
        if (!is_array($data) || !$this->hasKeys($data, ['schema_version', 'colors', 'areas'])
            || $data['schema_version'] !== 1 || !is_array($data['colors']) || !is_array($data['areas'])
            || !$this->hasKeys($data['colors'], PaletteDefinition::COLOR_FIELDS)
            || !$this->hasKeys($data['areas'], array_keys(PaletteDefinition::AREAS))
        ) {
            throw new LocalizedException(__('The preset must contain a complete supported palette.'));
        }
        $colors = [];
        foreach (PaletteDefinition::COLOR_FIELDS as $field) {
            if (is_string($data['colors'][$field]) && strlen($data['colors'][$field]) > 64) {
                throw new LocalizedException(__('The preset contains an invalid color.'));
            }
            $colors[$field] = $this->colorParser->normalize($data['colors'][$field]);
        }
        $areas = [];
        foreach (PaletteDefinition::AREAS as $area => $definition) {
            if (!is_bool($data['areas'][$area])) {
                throw new LocalizedException(__('Preset area switches must be boolean values.'));
            }
            $areas[$area] = $data['areas'][$area];
        }
        return ['schema_version' => 1, 'colors' => $colors, 'areas' => $areas];
    }

    /**
     * Validate bounded plain-text names before they enter an index or a response.
     *
     * @param mixed $name
     * @return string
     * @throws LocalizedException
     */
    public function normalizeName($name): string
    {
        if (!is_string($name) || strlen($name) > 256) {
            throw new LocalizedException(__('Enter a preset name of 1 to 64 letters or numbers.'));
        }
        $name = trim($name);
        if (preg_match('/^[\p{L}\p{N} _().-]{1,64}$/u', $name) !== 1) {
            throw new LocalizedException(__('Use 1 to 64 letters, numbers, spaces, dots, hyphens or parentheses.'));
        }
        return $name;
    }

    /**
     * Reject missing and extra keys rather than partially merging a preset.
     *
     * @param array $data
     * @param array $keys
     * @return bool
     */
    private function hasKeys(array $data, array $keys): bool
    {
        $actual = array_keys($data);
        sort($actual);
        sort($keys);
        return $actual === $keys;
    }
}

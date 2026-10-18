<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

/** Immutable effective palette; only normalized colors can reach generated CSS. */
class Settings
{
    /** @var array */
    private $values;

    /**
     * Keep existing positional settings compatible and append optional extended fields.
     *
     * @param bool $tabsEnabled
     * @param string|null $tabsColor
     * @param string|null $tabsTextColor
     * @param bool $buttonsEnabled
     * @param string|null $buttonsColor
     * @param string|null $buttonsTextColor
     * @param bool $menuEnabled
     * @param string|null $menuColor
     * @param string|null $menuTextColor
     * @param array $extended
     */
    public function __construct(
        bool $tabsEnabled,
        ?string $tabsColor,
        ?string $tabsTextColor,
        bool $buttonsEnabled,
        ?string $buttonsColor,
        ?string $buttonsTextColor,
        bool $menuEnabled,
        ?string $menuColor,
        ?string $menuTextColor,
        array $extended = []
    ) {
        $this->values = array_merge($extended, [
            'tabs_enabled' => $tabsEnabled, 'tabs_color' => $tabsColor, 'tabs_text_color' => $tabsTextColor,
            'buttons_enabled' => $buttonsEnabled, 'buttons_color' => $buttonsColor,
            'buttons_text_color' => $buttonsTextColor,
            'menu_enabled' => $menuEnabled, 'menu_color' => $menuColor, 'menu_text_color' => $menuTextColor
        ]);
    }

    /**
     * Read a closed-list area switch; new areas preserve native styling until colored.
     *
     * @param string $area
     * @return bool
     */
    public function isAreaEnabled(string $area): bool
    {
        return isset(PaletteDefinition::AREAS[$area]) && (bool)($this->values[$area . '_enabled'] ?? true);
    }

    /**
     * Reject malformed persisted data at the CSS boundary without emitting it.
     *
     * @param string $field
     * @return string|null
     */
    public function getColor(string $field): ?string
    {
        $value = $this->values[$field] ?? null;
        return in_array($field, PaletteDefinition::COLOR_FIELDS, true)
            && is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) === 1
            ? strtoupper($value) : null;
    }

    /**
     * Detect configured colors in enabled areas only.
     *
     * @return bool
     */
    public function hasOverrides(): bool
    {
        foreach (PaletteDefinition::AREAS as $area => $definition) {
            if (!$this->isAreaEnabled($area)) {
                continue;
            }
            foreach ($definition['fields'] as $field => $label) {
                if ($this->getColor($field) !== null) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Read the tabs area switch.
     *
     * @return bool
     */
    public function areTabsEnabled(): bool
    {
        return $this->isAreaEnabled('tabs');
    }

    /**
     * Read the buttons area switch.
     *
     * @return bool
     */
    public function areButtonsEnabled(): bool
    {
        return $this->isAreaEnabled('buttons');
    }

    /**
     * Read the menu area switch.
     *
     * @return bool
     */
    public function isMenuEnabled(): bool
    {
        return $this->isAreaEnabled('menu');
    }

    /**
     * Read the normalized tabs color.
     *
     * @return string|null
     */
    public function getTabsColor(): ?string
    {
        return $this->getColor('tabs_color');
    }

    /**
     * Read the normalized tabs text color.
     *
     * @return string|null
     */
    public function getTabsTextColor(): ?string
    {
        return $this->getColor('tabs_text_color');
    }

    /**
     * Read the normalized buttons color.
     *
     * @return string|null
     */
    public function getButtonsColor(): ?string
    {
        return $this->getColor('buttons_color');
    }

    /**
     * Read the normalized buttons text color.
     *
     * @return string|null
     */
    public function getButtonsTextColor(): ?string
    {
        return $this->getColor('buttons_text_color');
    }

    /**
     * Read the normalized menu color.
     *
     * @return string|null
     */
    public function getMenuColor(): ?string
    {
        return $this->getColor('menu_color');
    }

    /**
     * Read the normalized menu text color.
     *
     * @return string|null
     */
    public function getMenuTextColor(): ?string
    {
        return $this->getColor('menu_text_color');
    }
}

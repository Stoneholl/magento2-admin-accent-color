<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Stoneholl\AdminAccentColor\Model\Source\PersonalizationMode;

class Config
{
    public const XML_PATH_ENABLED = 'stoneholl_admin_accent_color/general/enabled';
    public const XML_PATH_PERSONALIZATION_MODE = 'stoneholl_admin_accent_color/general/personalization_mode';
    public const XML_PATH_TABS_ENABLED = 'stoneholl_admin_accent_color/areas/tabs_enabled';
    public const XML_PATH_TABS_COLOR = 'stoneholl_admin_accent_color/areas/tabs_color';
    public const XML_PATH_TABS_TEXT_COLOR = 'stoneholl_admin_accent_color/areas/tabs_text_color';
    public const XML_PATH_BUTTONS_ENABLED = 'stoneholl_admin_accent_color/areas/buttons_enabled';
    public const XML_PATH_BUTTONS_COLOR = 'stoneholl_admin_accent_color/areas/buttons_color';
    public const XML_PATH_BUTTONS_TEXT_COLOR = 'stoneholl_admin_accent_color/areas/buttons_text_color';
    public const XML_PATH_MENU_ENABLED = 'stoneholl_admin_accent_color/areas/menu_enabled';
    public const XML_PATH_MENU_COLOR = 'stoneholl_admin_accent_color/areas/menu_color';
    public const XML_PATH_MENU_TEXT_COLOR = 'stoneholl_admin_accent_color/areas/menu_text_color';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * Initialize explicit Config dependencies.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Read the global Default-scope enable switch.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Read the closed Default-scope personalization mode.
     *
     * @return bool
     */
    public function allowsPersonalOverrides(): bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PERSONALIZATION_MODE)
            === PersonalizationMode::PERSONAL_MODE;
    }

    /**
     * Collect the allowlisted Default-scope palette and area switches.
     *
     * @return Settings
     */
    public function getGlobalSettings(): Settings
    {
        $extended = [];
        foreach (PaletteDefinition::COLOR_FIELDS as $field) {
            $extended[$field] = $this->nullableValue('stoneholl_admin_accent_color/areas/' . $field);
        }
        foreach (['sidebar', 'links'] as $area) {
            $extended[$area . '_enabled'] = $this->scopeConfig->isSetFlag(
                'stoneholl_admin_accent_color/areas/' . $area . '_enabled'
            );
        }
        return new Settings(
            $this->scopeConfig->isSetFlag(self::XML_PATH_TABS_ENABLED),
            $this->nullableValue(self::XML_PATH_TABS_COLOR),
            $this->nullableValue(self::XML_PATH_TABS_TEXT_COLOR),
            $this->scopeConfig->isSetFlag(self::XML_PATH_BUTTONS_ENABLED),
            $this->nullableValue(self::XML_PATH_BUTTONS_COLOR),
            $this->nullableValue(self::XML_PATH_BUTTONS_TEXT_COLOR),
            $this->scopeConfig->isSetFlag(self::XML_PATH_MENU_ENABLED),
            $this->nullableValue(self::XML_PATH_MENU_COLOR),
            $this->nullableValue(self::XML_PATH_MENU_TEXT_COLOR),
            $extended
        );
    }

    /**
     * Read an optional Default-scope color value.
     *
     * @param string $path
     * @return string|null
     */
    private function nullableValue(string $path): ?string
    {
        $value = trim((string)$this->scopeConfig->getValue($path));
        return $value === '' ? null : $value;
    }
}

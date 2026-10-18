<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Framework\Model\AbstractModel;

class UserPreference extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\UserPreference::class);
    }

    /**
     * Convert the stored personal palette into normalized effective settings.
     *
     * @return Settings
     */
    public function toSettings(): Settings
    {
        return new Settings(
            (bool)$this->getData('tabs_enabled'),
            $this->nullableColor('tabs_color'),
            $this->nullableColor('tabs_text_color'),
            (bool)$this->getData('buttons_enabled'),
            $this->nullableColor('buttons_color'),
            $this->nullableColor('buttons_text_color'),
            (bool)$this->getData('menu_enabled'),
            $this->nullableColor('menu_color'),
            $this->nullableColor('menu_text_color'),
            $this->getData()
        );
    }

    /**
     * Read an optional stored personal color.
     *
     * @param string $key
     * @return string|null
     */
    private function nullableColor(string $key): ?string
    {
        $value = trim((string)$this->getData($key));
        return $value === '' ? null : $value;
    }
}

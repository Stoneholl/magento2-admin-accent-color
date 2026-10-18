<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PersonalizationMode implements OptionSourceInterface
{
    public const GLOBAL_MODE = 'global';
    public const PERSONAL_MODE = 'personal';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::GLOBAL_MODE, 'label' => __('Global for All Administrators')],
            ['value' => self::PERSONAL_MODE, 'label' => __('Allow Personal Overrides')]
        ];
    }
}

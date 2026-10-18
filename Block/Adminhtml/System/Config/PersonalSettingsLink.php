<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class PersonalSettingsLink extends Field
{
    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return sprintf(
            '<a class="action-default" href="%s"><span>%s</span></a>',
            $this->escapeUrl($this->getUrl('adminaccentcolor/preferences/index')),
            $this->escapeHtml(__('Open My Accent Colors'))
        );
    }
}

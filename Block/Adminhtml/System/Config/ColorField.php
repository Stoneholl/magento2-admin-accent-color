<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ColorField extends Field
{
    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->addClass('stoneholl-accent-color-text');
        // Keep native values and validation intact; the shared editor replaces these rows after initialization.
        return $element->getElementHtml();
    }
}

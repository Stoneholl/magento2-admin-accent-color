<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml\System\Config;

use Stoneholl\AdminAccentColor\Block\Adminhtml\PalettePreview;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Preview extends Field
{
    /**
     * Give the preview the configuration group's available width.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return '<tr id="row_' . $this->escapeHtmlAttr($element->getHtmlId()) . '"><td colspan="4">'
            . $this->_getElementHtml($element) . '</td></tr>';
    }

    /**
     * Render the shared local-only palette preview inside System Configuration.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->getLayout()->createBlock(PalettePreview::class)
            ->setTemplate('Stoneholl_AdminAccentColor::preview.phtml')->toHtml();
    }
}

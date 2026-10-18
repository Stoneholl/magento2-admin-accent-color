<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Stoneholl\AdminAccentColor\Model\ThemeSupport;

class ThemeStatus extends Field
{
    /** @var ThemeSupport */
    private $themeSupport;

    /**
     * Initialize explicit ThemeStatus dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ThemeSupport $themeSupport
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ThemeSupport $themeSupport,
        array $data = []
    ) {
        $this->themeSupport = $themeSupport;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $themeCode = $this->themeSupport->getActiveThemeCode();
        if ($this->themeSupport->isSupported()) {
            return '<div class="message message-success">'
                . $this->escapeHtml(__('Supported Admin theme detected: %1.', $themeCode))
                . '</div>';
        }

        return '<div class="message message-warning">'
            . $this->escapeHtml(
                __('Admin theme %1 is not supported. Accent styles will not be applied.', $themeCode)
            )
            . '</div>';
    }
}

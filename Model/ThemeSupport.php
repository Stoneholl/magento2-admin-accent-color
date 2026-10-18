<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model;

use Magento\Framework\View\DesignInterface;

class ThemeSupport
{
    public const SUPPORTED_THEME = 'Magento/backend';

    /** @var DesignInterface */
    private $design;

    /**
     * Initialize explicit ThemeSupport dependencies.
     *
     * @param DesignInterface $design
     */
    public function __construct(DesignInterface $design)
    {
        $this->design = $design;
    }

    /**
     * Return the active Admin theme identifier.
     *
     * @return string
     */
    public function getActiveThemeCode(): string
    {
        return (string)$this->design->getDesignTheme()->getCode();
    }

    /**
     * Permit styling only for the unmodified standard Admin theme.
     *
     * @return bool
     */
    public function isSupported(): bool
    {
        return $this->getActiveThemeCode() === self::SUPPORTED_THEME;
    }
}

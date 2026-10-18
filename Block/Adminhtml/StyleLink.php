<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use Stoneholl\AdminAccentColor\Model\Config;
use Stoneholl\AdminAccentColor\Model\EffectiveSettingsResolver;
use Stoneholl\AdminAccentColor\Model\ThemeSupport;

class StyleLink extends Template
{
    /** @var Session */
    private $authSession;
    /** @var Config */
    private $config;
    /** @var ThemeSupport */
    private $themeSupport;
    /** @var EffectiveSettingsResolver */
    private $settingsResolver;

    /**
     * Initialize explicit StyleLink dependencies.
     *
     * @param Template\Context $context
     * @param Session $authSession
     * @param Config $config
     * @param ThemeSupport $themeSupport
     * @param EffectiveSettingsResolver $settingsResolver
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $authSession,
        Config $config,
        ThemeSupport $themeSupport,
        EffectiveSettingsResolver $settingsResolver,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->config = $config;
        $this->themeSupport = $themeSupport;
        $this->settingsResolver = $settingsResolver;
        parent::__construct($context, $data);
    }

    /**
     * Link private CSS only for an authenticated user with effective overrides.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        $user = $this->authSession->getUser();
        return $this->config->isEnabled()
            && $this->themeSupport->isSupported()
            && $user
            && (int)$user->getId()
            && $this->settingsResolver->resolve((int)$user->getId())->hasOverrides();
    }

    /**
     * Build the current administrator's same-origin stylesheet route.
     *
     * @return string
     */
    public function getStylesheetUrl(): string
    {
        return $this->getUrl('adminaccentcolor/css/index');
    }
}

<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Stoneholl\AdminAccentColor\Model\Config;

class AccountLink extends Template
{
    /** @var Config */
    private $config;

    /**
     * Initialize explicit AccountLink dependencies.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(Template\Context $context, Config $config, array $data = [])
    {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Show the personal-settings link only when personalization is enabled.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->config->isEnabled() && $this->config->allowsPersonalOverrides();
    }

    /**
     * Build the personal accent settings route.
     *
     * @return string
     */
    public function getPreferencesUrl(): string
    {
        return $this->getUrl('adminaccentcolor/preferences/index');
    }
}

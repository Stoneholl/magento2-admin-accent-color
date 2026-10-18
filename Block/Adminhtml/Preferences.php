<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use Stoneholl\AdminAccentColor\Model\Config;
use Stoneholl\AdminAccentColor\Model\Settings;
use Stoneholl\AdminAccentColor\Model\ThemeSupport;
use Stoneholl\AdminAccentColor\Model\UserPreference;
use Stoneholl\AdminAccentColor\Model\UserPreferenceRepository;

class Preferences extends Template
{
    /** @var Session */
    private $authSession;
    /** @var Config */
    private $config;
    /** @var ThemeSupport */
    private $themeSupport;
    /** @var UserPreferenceRepository */
    private $preferenceRepository;
    /** @var UserPreference|null|false */
    private $preference = false;

    /**
     * Initialize explicit Preferences dependencies.
     *
     * @param Template\Context $context
     * @param Session $authSession
     * @param Config $config
     * @param ThemeSupport $themeSupport
     * @param UserPreferenceRepository $preferenceRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $authSession,
        Config $config,
        ThemeSupport $themeSupport,
        UserPreferenceRepository $preferenceRepository,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->config = $config;
        $this->themeSupport = $themeSupport;
        $this->preferenceRepository = $preferenceRepository;
        parent::__construct($context, $data);
    }

    /**
     * Describe the same closed palette fields used by personal-save validation.
     *
     * @return array
     */
    public function getAreas(): array
    {
        return \Stoneholl\AdminAccentColor\Model\PaletteDefinition::AREAS;
    }

    /**
     * Require the global switch and personal-override mode before editing.
     *
     * @return bool
     */
    public function canCustomize(): bool
    {
        return $this->config->isEnabled() && $this->config->allowsPersonalOverrides();
    }

    /**
     * Report whether the standard Admin theme is active.
     *
     * @return bool
     */
    public function isThemeSupported(): bool
    {
        return $this->themeSupport->isSupported();
    }

    /**
     * Return the active Admin theme identifier.
     *
     * @return string
     */
    public function getActiveThemeCode(): string
    {
        return $this->themeSupport->getActiveThemeCode();
    }

    /**
     * Report whether the current administrator has no saved override.
     *
     * @return bool
     */
    public function isUsingGlobal(): bool
    {
        return $this->getPreference() === null;
    }

    /**
     * Load personal settings or the global fallback for the form.
     *
     * @return Settings
     */
    public function getSettings(): Settings
    {
        $preference = $this->getPreference();
        return $preference === null ? $this->config->getGlobalSettings() : $preference->toSettings();
    }

    /**
     * Build the native form-key protected personal-save route.
     *
     * @return string
     */
    public function getSaveUrl(): string
    {
        return $this->getUrl('adminaccentcolor/preferences/save');
    }

    /**
     * Build the native My Account route.
     *
     * @return string
     */
    public function getAccountUrl(): string
    {
        return $this->getUrl('adminhtml/system_account/index');
    }

    /**
     * Memoize only the current authenticated administrator's preference.
     *
     * @return UserPreference|null
     */
    private function getPreference(): ?UserPreference
    {
        if ($this->preference === false) {
            $user = $this->authSession->getUser();
            $this->preference = $user && (int)$user->getId()
                ? $this->preferenceRepository->getByUserId((int)$user->getId())
                : null;
        }

        return $this->preference ?: null;
    }
}

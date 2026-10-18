<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml\Css;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Stoneholl\AdminAccentColor\Model\Config;
use Stoneholl\AdminAccentColor\Model\CssGenerator;
use Stoneholl\AdminAccentColor\Model\EffectiveSettingsResolver;
use Stoneholl\AdminAccentColor\Model\ThemeSupport;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /** @var Session */
    private $authSession;
    /** @var Config */
    private $config;
    /** @var ThemeSupport */
    private $themeSupport;
    /** @var EffectiveSettingsResolver */
    private $settingsResolver;
    /** @var CssGenerator */
    private $cssGenerator;

    /**
     * Initialize explicit Index dependencies.
     *
     * @param Action\Context $context
     * @param Session $authSession
     * @param Config $config
     * @param ThemeSupport $themeSupport
     * @param EffectiveSettingsResolver $settingsResolver
     * @param CssGenerator $cssGenerator
     */
    public function __construct(
        Action\Context $context,
        Session $authSession,
        Config $config,
        ThemeSupport $themeSupport,
        EffectiveSettingsResolver $settingsResolver,
        CssGenerator $cssGenerator
    ) {
        $this->authSession = $authSession;
        $this->config = $config;
        $this->themeSupport = $themeSupport;
        $this->settingsResolver = $settingsResolver;
        $this->cssGenerator = $cssGenerator;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $css = '';
        $user = $this->authSession->getUser();
        if ($this->config->isEnabled() && $this->themeSupport->isSupported() && $user && (int)$user->getId()) {
            $css = $this->cssGenerator->generate($this->settingsResolver->resolve((int)$user->getId()));
        }

        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setHeader('Content-Type', 'text/css; charset=UTF-8', true);
        $resultRaw->setHeader('Cache-Control', 'private, no-store, max-age=0', true);
        $resultRaw->setHeader('X-Content-Type-Options', 'nosniff', true);
        return $resultRaw->setContents($css);
    }

    /**
     * Serve the current user's palette to any authenticated administrator.
     *
     * Personal editing still requires My Account; global styling does not.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_auth->isLoggedIn();
    }
}

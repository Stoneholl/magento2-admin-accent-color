<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml\Preferences;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Stoneholl\AdminAccentColor\Model\ColorParser;
use Stoneholl\AdminAccentColor\Model\Config;
use Stoneholl\AdminAccentColor\Model\PaletteDefinition;
use Stoneholl\AdminAccentColor\Model\UserPreferenceRepository;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Backend::myaccount';

    /** @var Session */
    private $authSession;
    /** @var Config */
    private $config;
    /** @var ColorParser */
    private $colorParser;
    /** @var UserPreferenceRepository */
    private $preferenceRepository;
    /** @var LoggerInterface */
    private $logger;

    /**
     * Initialize explicit Save dependencies.
     *
     * @param Action\Context $context
     * @param Session $authSession
     * @param Config $config
     * @param ColorParser $colorParser
     * @param UserPreferenceRepository $preferenceRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        Session $authSession,
        Config $config,
        ColorParser $colorParser,
        UserPreferenceRepository $preferenceRepository,
        LoggerInterface $logger
    ) {
        $this->authSession = $authSession;
        $this->config = $config;
        $this->colorParser = $colorParser;
        $this->preferenceRepository = $preferenceRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $user = $this->authSession->getUser();

        if (!$this->config->isEnabled() || !$this->config->allowsPersonalOverrides()) {
            $this->messageManager->addErrorMessage(
                __('Personal accent colors are not currently enabled by the global configuration.')
            );
            return $resultRedirect->setPath('*/*/index');
        }

        if (!$user || !(int)$user->getId()) {
            $this->messageManager->addErrorMessage(__('The current administrator could not be identified.'));
            return $resultRedirect->setPath('*/*/index');
        }

        try {
            if ($this->flag('use_global') === 1) {
                $this->preferenceRepository->deleteForUser((int)$user->getId());
                $this->messageManager->addSuccessMessage(__('Global accent color settings are now in use.'));
                return $resultRedirect->setPath('*/*/index');
            }

            $data = [];
            foreach (PaletteDefinition::COLOR_FIELDS as $field) {
                $data[$field] = $this->colorParser->normalize($this->getRequest()->getParam($field));
            }
            foreach (PaletteDefinition::AREAS as $area => $definition) {
                $data[$area . '_enabled'] = $this->flag($area . '_enabled');
            }
            $this->preferenceRepository->saveForUser((int)$user->getId(), $data);
            $this->messageManager->addSuccessMessage(__('You saved your accent colors.'));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error($exception);
            $this->messageManager->addErrorMessage(__('An error occurred while saving your accent colors.'));
        }

        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * Read a personal area switch from the submitted form.
     *
     * @param string $name
     * @return int
     */
    private function flag(string $name): int
    {
        $value = $this->getRequest()->getParam($name, false);
        if (!in_array($value, ['0', '1', 0, 1, false, true, null], true)) {
            throw new LocalizedException(__('Palette switches must be Yes or No.'));
        }
        return in_array($value, ['1', 1, true], true) ? 1 : 0;
    }
}

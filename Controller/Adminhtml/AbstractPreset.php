<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Stoneholl\AdminAccentColor\Model\PresetAccess;
use Stoneholl\AdminAccentColor\Model\PresetManagement;

abstract class AbstractPreset extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /** @var PresetManagement */
    protected $presets;
    /** @var PresetAccess */
    private $access;
    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Action\Context $context
     * @param PresetManagement $presets
     * @param PresetAccess $access
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        PresetManagement $presets,
        PresetAccess $access,
        LoggerInterface $logger
    ) {
        $this->presets = $presets;
        $this->access = $access;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Require an authenticated caller with at least one preset permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        try {
            $this->access->actorId();
            return $this->access->canPersonal() || $this->access->canShared();
        } catch (AuthorizationException $error) {
            return false;
        }
    }

    /**
     * Return safe JSON without caching local palettes or leaking database diagnostics.
     *
     * @param callable $operation
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function respond(callable $operation)
    {
        $status = 200;
        try {
            $data = ['success' => true, 'data' => $operation()];
        } catch (AuthorizationException $error) {
            $status = 403;
            $data = ['success' => false, 'message' => (string)__('Preset access is not allowed.')];
        } catch (LocalizedException $error) {
            $status = 422;
            $data = ['success' => false, 'message' => $error->getMessage()];
        } catch (\Throwable $error) {
            $this->logger->error('Admin Accent Color preset operation failed.', ['exception' => $error]);
            $status = 500;
            $data = ['success' => false, 'message' => (string)__('The preset operation failed. Please try again.')];
        }
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setHttpResponseCode($status);
        $result->setHeader('Cache-Control', 'private, no-store, max-age=0', true);
        $result->setHeader('X-Content-Type-Options', 'nosniff', true);
        return $result->setData($data);
    }
}

<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml\Preferences;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Magento_Backend::myaccount';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('My Accent Colors'));
        return $resultPage;
    }
}

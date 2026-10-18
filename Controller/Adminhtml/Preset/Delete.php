<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml\Preset;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Stoneholl\AdminAccentColor\Controller\Adminhtml\AbstractPreset;

class Delete extends AbstractPreset implements HttpPostActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->respond(function () {
            $this->presets->delete($this->getRequest()->getParam('preset_id'), $this->getRequest()->getParam('scope'));
            return [];
        });
    }
}

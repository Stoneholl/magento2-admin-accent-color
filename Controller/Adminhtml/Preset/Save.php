<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml\Preset;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Stoneholl\AdminAccentColor\Controller\Adminhtml\AbstractPreset;

class Save extends AbstractPreset implements HttpPostActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->respond(function () {
            return ['id' => $this->presets->save(
                $this->getRequest()->getParam('scope'),
                $this->getRequest()->getParam('name'),
                $this->getRequest()->getParam('palette')
            )];
        });
    }
}

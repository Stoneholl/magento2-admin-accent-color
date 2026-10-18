<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Controller\Adminhtml\Preset;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Stoneholl\AdminAccentColor\Controller\Adminhtml\AbstractPreset;

class Index extends AbstractPreset implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->respond(function () {
            return $this->presets->available();
        });
    }
}

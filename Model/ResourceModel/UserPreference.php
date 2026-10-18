<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class UserPreference extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init('stoneholl_admin_accent_user', 'preference_id');
    }
}

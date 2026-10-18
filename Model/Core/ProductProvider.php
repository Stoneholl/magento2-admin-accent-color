<?php

declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model\Core;

use Stoneholl\Core\Api\ProductProviderInterface;
use Stoneholl\Core\Model\Data\ProductDescriptor;

class ProductProvider implements ProductProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getProducts(): array
    {
        return [new ProductDescriptor(
            'admin-accent-color',
            'Stoneholl Admin Accent Color',
            ['Stoneholl_AdminAccentColor'],
            ['stoneholl/module-admin-accent-color']
        )];
    }
}

<?php
declare(strict_types=1);

namespace Stoneholl\AdminAccentColor\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Stoneholl\AdminAccentColor\Model\ColorParser;

class Color extends Value
{
    /** @var ColorParser */
    private $colorParser;

    /**
     * Initialize explicit Color dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param ColorParser $colorParser
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        ColorParser $colorParser,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->colorParser = $colorParser;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $this->setValue($this->colorParser->normalize($this->getValue()) ?: '');
        return parent::beforeSave();
    }
}

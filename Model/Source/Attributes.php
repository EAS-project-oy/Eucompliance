<?php

declare(strict_types=1);

namespace Easproject\Eucompliance\Model\Source;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Attributes implements OptionSourceInterface
{

    /**
     * @var AttributeCollectionFactory
     */
    private AttributeCollectionFactory $collectionFactory;

    /**
     * Attributes constructor.
     * @param AttributeCollectionFactory $collectionFactory
     */
    public function __construct(
        AttributeCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = [];
        $collection = $this->collectionFactory->create()->setEntityTypeFilter(Configuration::PRODUCT_ENTITY_TYPE);
        foreach ($collection->getData() as $attribute) {
            $attributes[$attribute[Configuration::ATTRIBUTE_CODE]] = $attribute[Configuration::ATTRIBUTE_CODE];
        }

        return $attributes;
    }
}

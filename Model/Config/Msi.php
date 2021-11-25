<?php

namespace Eas\Eucompliance\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Module\Manager;
use Magento\Inventory\Model\ResourceModel\Source\Collection;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Msi implements OptionSourceInterface
{

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Manager $moduleManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Manager           $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->collectionFactory = $collectionFactory;
    }


    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled(Configuration::INVENTORY_MODULE)) {
            return [];
        }
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('enabled', ['eq' => 1])
            ->addFieldToSelect(['name', 'country_id']);

        $sources = [
            ['value'=> 0,'label'=>' ']
        ];
        foreach ($collection->getItems() as $item) {
            $sources[] = [
                'value' => $item->getCountryId(),
                'label' => $item->getName()
            ];
        }
        return $sources;
    }
}
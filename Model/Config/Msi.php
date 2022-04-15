<?php

namespace Easproject\Eucompliance\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Module\Manager;
use Magento\Inventory\Model\ResourceModel\Source\Collection;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\InventorySourceSelectionApi\Model\GetSourceSelectionAlgorithmList;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Msi implements OptionSourceInterface
{


    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @var GetSourceSelectionAlgorithmList
     */
    private GetSourceSelectionAlgorithmList $getSourceSelectionAlgorithmList;

    /**
     * @param GetSourceSelectionAlgorithmList $getSourceSelectionAlgorithmList
     * @param Manager $moduleManager
     */
    public function __construct(
        GetSourceSelectionAlgorithmList $getSourceSelectionAlgorithmList,
        Manager           $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->getSourceSelectionAlgorithmList = $getSourceSelectionAlgorithmList;
    }


    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled(Configuration::INVENTORY_MODULE)) {
            return [];
        }
        $list = $this->getSourceSelectionAlgorithmList->execute();

        $algorithms = [];
        foreach ($list as $item) {
            $algorithms[] = [
                'value' => $item->getCode(),
                'label' => $item->getTitle()
            ];
        }
        return $algorithms;
    }
}

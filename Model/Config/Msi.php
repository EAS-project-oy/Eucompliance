<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 8
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */

namespace Easproject\Eucompliance\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Module\Manager;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\InventorySourceSelectionApi\Model\GetSourceSelectionAlgorithmList;

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
     * @param Manager                         $moduleManager
     */
    public function __construct(
        GetSourceSelectionAlgorithmList $getSourceSelectionAlgorithmList,
        Manager           $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->getSourceSelectionAlgorithmList = $getSourceSelectionAlgorithmList;
    }

    /**
     * Options data
     *
     * @return array
     */
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

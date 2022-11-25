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

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderManagement
{

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param Configuration $configuration
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Configuration $configuration
    ) {
        $this->orderRepository = $orderRepository;
        $this->configuration = $configuration;
    }

    /**
     * After Place Plugin
     *
     * @param  OrderManagementInterface $subject
     * @param  OrderInterface           $result
     * @param  OrderInterface           $order
     * @return OrderInterface
     * @throws \Exception
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {

        if (!$this->configuration->isEnabled()) {
            return $result;
        }

        foreach ($result->getItems() as $item) {
            if ($item->getEasWarehouseCode()) {
                $result->addCommentToStatusHistory(
                    'Eas confirmation: product with sku ' .
                    $item->getSku() . ' should be shipped from ' . $item->getEasWarehouseCode(),
                    true,
                    true
                );
            }
        }
        $this->orderRepository->save($result);
        return $result;
    }
}

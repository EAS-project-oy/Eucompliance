<?php

namespace Eas\Eucompliance\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class OrderManagement
{

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws \Exception
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $result, OrderInterface $order): OrderInterface
    {
        foreach ($result->getItems() as $item) {
            if ($item->getEasWarehouseCode()) {
                $result->addCommentToStatusHistory(
                    'Eas confirmation: product with sku ' .
                    $item->getSku() . ' should be shipped from ' . $item->getEasWarehouseCode() . ' stock',
                    true,
                    true
                );
            }
        }
        $this->orderRepository->save($result);
        return $result;
    }
}

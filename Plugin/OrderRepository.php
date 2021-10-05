<?php

namespace Eas\Eucompliance\Plugin;

use Eas\Eucompliance\Service\Calculate;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class OrderRepository
{
    const PENDING = 'pending';
    /**
     * @var Calculate
     */
    private Calculate $calculate;

    /**
     * @param Calculate $calculate
     */
    public function __construct(Calculate $calculate) {
        $this->calculate = $calculate;
    }

    /**
     * @param OrderRepository $subject
     * @param OrderInterface $result
     * @param OrderInterface $entity
     * @return OrderInterface
     */
    public function afterSave(
        \Magento\Sales\Model\OrderRepository $subject,
        OrderInterface $result,
        OrderInterface $entity
    ):  OrderInterface {

            if ($result->getStatus() == 'processing' || $result->getStatus() == 'complete') {
                $this->calculate->confirmOrder($result);
            }

        return $result;
    }

    /**
     * @param \Magento\Sales\Model\OrderRepository $subject
     * @param OrderInterface $entity
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function beforeSave(
        \Magento\Sales\Model\OrderRepository $subject,
        OrderInterface $entity):  array {

        if (!$entity->getEntityId() && $entity->getPayment()->getMethod() !== Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            if ($entity->getStatus() == self::PENDING) {
                $this->calculate->confirmOrder($entity);
            }
        }

        return [$entity];
    }
}
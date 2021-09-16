<?php

namespace Eas\Eucompliance\Plugin;

use Eas\Eucompliance\Service\Calculate;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Sales\Api\Data\OrderInterface;

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
     * @param OrderRepository $subject
     * @param OrderInterface $result
     * @param OrderInterface $entity
     * @return array
     */
    public function beforeSave(
        \Magento\Sales\Model\OrderRepository $subject,
        OrderInterface $entity
    ):  OrderInterface {

        if (!$entity->getEntityId() && $entity->getPayment()->getMethod() !== Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            if ($entity->getStatus() == self::PENDING) {
                $this->calculate->confirmOrder($entity);
            }
        }

        return [$entity];
    }
}
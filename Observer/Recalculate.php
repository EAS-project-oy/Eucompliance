<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Recalculate implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getData('quote');
        $additionalCosts = $quote->getEas();
        if ($additionalCosts) {
            $total = $observer->getData('total');
            $total->setData(
                'tax_amount',
                $total->getData('tax_amount') + $additionalCosts
            );
            $total->setData(
                'base_tax_amount',
                $total->getData('base_tax_amount') + $additionalCosts
            );
        }
    }
}

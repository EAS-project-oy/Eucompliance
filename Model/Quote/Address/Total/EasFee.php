<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Model\Quote\Address\Total;

use Eas\Eucompliance\Model\Config\Configuration;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class EasFee extends AbstractTotal
{

    /**
     * EasFee constructor.
     */
    public function __construct()
    {
        $this->setCode(Configuration::EAS_FEE);
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return EasFee
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    ): EasFee {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        $amount = $quote->getEas();

        if ($amount) {
            $total->setGrandTotal($total->getGrandTotal() + $amount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $amount);
        }
        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        return $this->getEasFeeTotal((float)$quote->getEas());
    }

    /**
     * @param float $easFee
     * @return array
     */
    private function getEasFeeTotal(float $easFee): array
    {
        return [
            'code' => $this->getCode(),
            'title' => Configuration::EAS_FEE,
            'value' => $easFee
        ];
    }
}

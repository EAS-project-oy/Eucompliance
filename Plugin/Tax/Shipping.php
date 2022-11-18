<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

namespace Easproject\Eucompliance\Plugin\Tax;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class Shipping extends CommonTaxCollector
{
    /**
     *  Plugin After Collect
     *
     * @param \Magento\Tax\Model\Sales\Total\Quote\Shipping $subject
     * @param \Magento\Tax\Model\Sales\Total\Quote\Shipping $result
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return mixed
     */
    public function afterCollect(
        \Magento\Tax\Model\Sales\Total\Quote\Shipping $subject,
        $result,
        Quote                                         $quote,
        ShippingAssignmentInterface                   $shippingAssignment,
        Total                                         $total
    ) {
        $storeId = $quote->getStoreId();
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, false);
        $baseShippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, true);

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $storeId);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $storeId);
        if (array_key_exists(self::ITEM_CODE_SHIPPING, $taxDetails->getItems())) {
            $taxDetailsItems = $taxDetails->getItems()[self::ITEM_CODE_SHIPPING];
            $baseTaxDetailsItems = $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING];

            if ($quote->getData(Configuration::EAS_SHIPPING_COST)) {
                $taxDetails->setSubtotal($quote->getData(Configuration::EAS_SHIPPING_COST));
                $baseTaxDetails->setSubtotal($quote->getData(Configuration::EAS_SHIPPING_COST));
                $total->setData(
                    'shipping_tax_calculation_amount',
                    $quote->getData(Configuration::EAS_SHIPPING_COST)
                );
                $total->setData(
                    'base_shipping_tax_calculation_amount',
                    $quote->getData(Configuration::EAS_SHIPPING_COST)
                );
            }
            $this->processShippingTaxInfo(
                $shippingAssignment,
                $total,
                $taxDetailsItems,
                $baseTaxDetailsItems
            );
        }

        return $result;
    }
}

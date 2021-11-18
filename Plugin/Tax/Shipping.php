<?php

namespace Eas\Eucompliance\Plugin\Tax;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;

class Shipping extends \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector
{

    /**
     * @param \Magento\Tax\Model\Sales\Total\Quote\Shipping $subject
     * @param $result
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     */
    public function afterCollect(\Magento\Tax\Model\Sales\Total\Quote\Shipping $subject, $result, Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        $storeId = $quote->getStoreId();
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, false);
        $baseShippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, true);

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $storeId);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $storeId);
        if (array_key_exists(self::ITEM_CODE_SHIPPING,$taxDetails->getItems())) {
            $taxDetailsItems = $taxDetails->getItems()[self::ITEM_CODE_SHIPPING];
            $baseTaxDetailsItems = $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING];

            if ($quote->getData('eas_shipping_cost')) {
                $quote->setData('eas_shipping_cost',$quote->getData('eas_shipping_cost') * 4 );
                $taxDetails->setSubtotal($quote->getData('eas_shipping_cost'));
                $baseTaxDetails->setSubtotal($quote->getData('eas_shipping_cost'));
                $total->setData('shipping_tax_calculation_amount', $quote->getData('eas_shipping_cost'));
                $total->setData('base_shipping_tax_calculation_amount', $quote->getData('eas_shipping_cost'));
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

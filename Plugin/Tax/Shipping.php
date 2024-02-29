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

namespace Easproject\Eucompliance\Plugin\Tax;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterfaceFactory;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Framework\App\ObjectManager;

class Shipping extends CommonTaxCollector
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @return Configuration
     */
    private function getConfiguration()
    {
        if(isset($this->configuration)) {
            return $this->configuration;
        }
        $this->configuration = ObjectManager::getInstance()->get(Configuration::class);
        return $this->configuration;
    }

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
        if (!$this->getConfiguration()->isEnabled() || $this->getConfiguration()->isStandardSolution()) {
            return $result;
        }

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

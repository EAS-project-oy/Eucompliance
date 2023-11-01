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

class Shipping extends CommonTaxCollector
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @param Configuration $configuration
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param CustomerAddressRegionFactory $customerAddressRegionFactory
     * @param TaxHelper|null $taxHelper
     * @param QuoteDetailsItemExtensionInterfaceFactory|null $quoteDetailsItemExtensionInterfaceFactory
     * @param CustomerAccountManagement|null $customerAccountManagement
     */
    public function __construct(
        Configuration $configuration,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        TaxHelper $taxHelper = null,
        QuoteDetailsItemExtensionInterfaceFactory $quoteDetailsItemExtensionInterfaceFactory = null,
        ?CustomerAccountManagement $customerAccountManagement = null
    ) {
        $this->configuration = $configuration;
        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory,
            $taxHelper,
            $quoteDetailsItemExtensionInterfaceFactory,
            $customerAccountManagement
        );
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
        if (!$this->configuration->isEnabled() || $this->configuration->isStandardSolution()) {
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

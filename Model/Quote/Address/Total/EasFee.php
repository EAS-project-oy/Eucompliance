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

declare(strict_types=1);

namespace Easproject\Eucompliance\Model\Quote\Address\Total;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item\Repository;

class EasFee extends AbstractTotal
{
    /**
     * @var Repository
     */
    private Repository $repository;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * EasFee constructor.
     *
     * @param Repository $repository
     * @param Session $checkoutSession
     */
    public function __construct(
        Repository $repository,
        Session $checkoutSession
    ) {
        $this->repository = $repository;
        $this->setCode(Configuration::EAS_FEE);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Collect
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @throws \Exception
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
        $easTaxAmount = $quote->getData(Configuration::EAS_TOTAL_TAX);
        $easTotalAmount = $quote->getData(Configuration::EAS_TOTAL_AMOUNT);

        foreach ($quote->getAllItems() as $item) {
            if ($item->getExtensionAttributes()) {
                $extAttributes = $item->getExtensionAttributes();
                if ($extAttributes->getEasTaxPercent()) {
                    $item->setTaxPercent($extAttributes->getEasTaxPercent());
                }
                if ($extAttributes->getEasTaxAmount()) {
                    $item->setTaxAmount($extAttributes->getEasTaxAmount());
                }
                if ($extAttributes->getEasRowTotal()) {
                    $item->setRowTotal($extAttributes->getEasRowTotal());
                }

                if ($extAttributes->getEasRowTotalInclTax()) {
                    $item->setRowTotalInclTax($extAttributes->getEasRowTotalInclTax());
                }
            }
        }

        if ($easTaxAmount) {
            $total->setData('tax_amount', $easTaxAmount);
            $total->setData('base_tax_amount', $easTaxAmount);
        }

        if ($easTotalAmount) {
            $total->setGrandTotal($easTotalAmount);
            $total->setBaseGrandTotal($easTotalAmount);
        }
        if ($this->checkoutSession->getData('custom_price_price')) {
            $total->setData('subtotal', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('base_subtotal_total_incl_tax', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('subtotal_incl_tax', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('base_subtotal_incl_tax', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('base_subtotal', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('subtotal_with_discount', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('base_subtotal_with_discount', $this->checkoutSession->getData('custom_price_price'));
            $total->setData('base_shipping_amount', $this->checkoutSession->getData('custom_shipping_price'));
            $total->setData('shipping_amount', $this->checkoutSession->getData('custom_shipping_price'));
            $total->setData(
                'shipping_tax_calculation_amount',
                $this->checkoutSession->getData('custom_shipping_price')
            );
            $total->setData(
                'base_shipping_tax_calculation_amount',
                $this->checkoutSession->getData('custom_shipping_price')
            );
            $total->setData('shipping_incl_tax', $this->checkoutSession->getData('custom_shipping_price'));
            $total->setData('base_shipping_incl_tax', $this->checkoutSession->getData('custom_shipping_price'));
            $quote->save();
            $this->checkoutSession->getData('custom_data_eas', true);
        }

        return $this;
    }

    /**
     * Fetch fee total
     *
     * @param  Quote $quote
     * @param  Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        return $this->getEasFeeTotal((float)$quote->getEas());
    }

    /**
     * Get eas fee total
     *
     * @param  float $easFee
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

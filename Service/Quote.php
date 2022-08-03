<?php

namespace Easproject\Eucompliance\Service;

use Easproject\Eucompliance\Model\Config\Configuration;
use Firebase\JWT\JWT;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Quote
{
    /**
     * @var JWT
     */
    private JWT $jwt;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var Calculate
     */
    private Calculate $calculate;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @param \Firebase\JWT\JWT                          $jwt
     * @param \Easproject\Eucompliance\Service\Calculate $calculate
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Checkout\Model\Session            $session
     */
    public function __construct(
        JWT                     $jwt,
        Calculate               $calculate,
        CartRepositoryInterface $quoteRepository,
        Session                 $session
    ) {
        $this->jwt = $jwt;
        $this->calculate = $calculate;
        $this->quoteRepository = $quoteRepository;
        $this->session = $session;
    }

    /**
     * @param  $tokenData
     * @param  bool $coupon
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveQuoteData($tokenData, bool $coupon = false): bool
    {
        if (array_key_exists(Configuration::EAS_CHECKOUT_TOKEN, $tokenData)) {
            $token = $tokenData[Configuration::EAS_CHECKOUT_TOKEN];
            $data = $this->jwt->decode(
                $token,
                json_decode($this->calculate->getPublicKey(), true),
                ['RS256']
            );
            $data = json_decode(json_encode($data), true);
            $quote = $this->session->getQuote();

            $quote->setData(Configuration::EAS_SHIPPING_COST, $data['delivery_charge_vat_excl']);
            $quote->setData(Configuration::EAS_TOTAL_VAT, $data['delivery_charge_vat'] + $data['merchandise_vat']);
            $quote->setData(
                Configuration::EAS_TOTAL_TAX,
                $data['delivery_charge_vat'] + $data['merchandise_vat'] +
                $data['eas_fee_vat'] + $data['total_customs_duties'] +
                $data['eas_fee']
            );

            $quote->setData(Configuration::EAS_TOTAL_AMOUNT, $data['total_order_amount']);
            $quote->setData(Configuration::EAS_TOKEN, $tokenData[Configuration::EAS_CHECKOUT_TOKEN]);
            $quote->setGrandTotal($data['total_order_amount']);
            $quote->setBaseGrandTotal($data['total_order_amount']);

            foreach ($data['items'] as $item) {
                $items = $quote->getAllItems();
                foreach ($items as $quoteItem) {
                    if ($item['item_id'] == $quoteItem->getProductId()) {
                        $this->clear($quoteItem);
                       // $quoteItem->setCustomPrice($item['unit_cost_excl_vat']);
                        //$quoteItem->setOriginalCustomPrice($item['unit_cost_excl_vat']);
                        $extAttributes = $quoteItem->getExtensionAttributes();
                        $extAttributes->setEasTaxAmount(
                            $item['item_duties_and_taxes'] - $item['item_customs_duties']
                            - $item['item_eas_fee'] - $item['item_eas_fee_vat'] - $item['item_delivery_charge_vat']
                        );
                        $extAttributes->setEasRowTotal($item['unit_cost_excl_vat'] * $quoteItem->getQty());

                        $extAttributes->setEasRowTotalInclTax(
                            $item['unit_cost_excl_vat'] * $quoteItem->getQty() +
                            $extAttributes->getEasTaxAmount() + $item['item_customs_duties'] +
                            $item['item_eas_fee'] + $item['item_eas_fee_vat']
                        );

                        $extAttributes->setEasTaxPercent($item['vat_rate']);
                        $extAttributes->setEasFee($item['item_eas_fee']);
                        $extAttributes->setVatOnEasFee($item['item_eas_fee_vat']);
                        $extAttributes->setEasCustomDuties($item['item_customs_duties']);
                    }
                }
                $quote->setItems($items);
            }

            if ($coupon) {
                if ($data['merchandise_cost_vat_excl'] < $data['merchandise_cost']) {

                    $countProduct = count($quote->getAllItems());
                    $discountSubtotal = $quote->getData('base_subtotal_with_discount');
                    $quote->getData('subtotal_with_discount');
                    $discountPer = 100 - ($discountSubtotal * 100 / $quote->getData('base_subtotal'));
                    $discountPrice = $discountSubtotal * $discountPer / 100;
                    $totalOrder = $discountPrice + $discountSubtotal;
                    $quote->setGrandTotal($totalOrder);
                    $quote->setBaseGrandTotal($totalOrder);
                    $quote->setBaseGrandTotal($totalOrder);
                    $quote->setData('base_subtotal_with_discount', $totalOrder);
                    $quote->setData('subtotal_with_discount', $totalOrder);
                    $quote->setData('base_subtotal', $totalOrder);
                    $discountPerByProduct = $discountPrice / $countProduct;

                    foreach ($quote->getAllItems() as $productItem) {
                        $productItem->setOriginalCustomPrice($productItem->getPrice() + $discountPerByProduct);
                    }
                }
            }
            $this->quoteRepository->save($quote);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $this->quoteRepository->save($quote);
            return true;
        }
        return false;
    }

    /**
     * @param  \Magento\Quote\Model\Quote\Item $item
     * @return void
     */
    private function clear(Item $item)
    {
        $item->setEasTaxAmount(0);
        $item->setEasRowTotal(0);
        $item->setEasRowTotalInclTax(0);
        $item->setEasTaxPercent(0);
        $item->setEasFee(0);
        $item->setVatOnEasFee(0);
        $item->setEasCustomDuties(0);
    }
}

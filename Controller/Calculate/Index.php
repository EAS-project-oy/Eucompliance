<?php

namespace Eas\Eucompliance\Controller\Calculate;

use Eas\Eucompliance\Service\Calculate;
use Eas\Eucompliance\Model\Config\Configuration;
use Firebase\JWT\JWT;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteManagement;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Index implements ActionInterface
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * @var JWT
     */
    private JWT $jwt;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

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
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var QuoteManagement
     */
    private QuoteManagement $quoteManagement;

    /**
     * Index constructor.
     * @param Request $request
     * @param ResponseInterface $response
     * @param QuoteManagement $quoteManagement
     * @param JWT $jwt
     * @param UrlInterface $url
     * @param Calculate $calculate
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $session
     * @param Configuration $configuration
     */
    public function __construct(
        Request                 $request,
        ResponseInterface       $response,
        QuoteManagement         $quoteManagement,
        JWT                     $jwt,
        UrlInterface            $url,
        Calculate               $calculate,
        CartRepositoryInterface $quoteRepository,
        Session                 $session,
        Configuration           $configuration
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->jwt = $jwt;
        $this->url = $url;
        $this->calculate = $calculate;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->session = $session;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        if (!$this->configuration->isEnabled()) {
            return $this->response->setRedirect($this->url->getUrl('checkout/cart'));
        }

        $params = $this->request->getParams();
        if (array_key_exists(Configuration::EAS_CHECKOUT_TOKEN, $params)) {
            $token = $this->request->getParams()[Configuration::EAS_CHECKOUT_TOKEN];
            $data = $this->jwt->decode(
                $token,
                json_decode($this->calculate->getPublicKey(), true),
                ['RS256']
            );
            $data = json_decode(json_encode($data), true);
            $quote = $this->session->getQuote();

            $quote->setData(Configuration::EAS_SHIPPING_COST, $data['delivery_charge_vat_excl']);
            $quote->setData(Configuration::EAS_TOTAL_VAT, $data['delivery_charge_vat'] + $data['merchandise_vat']);
            $quote->setData(Configuration::EAS_TOKEN, $params[Configuration::EAS_CHECKOUT_TOKEN]);

            foreach ($data['items'] as $item) {
                $items = $quote->getAllItems();
                foreach ($items as $quoteItem) {
                    if ($item['item_id'] == $quoteItem->getProductId()) {
                        $this->clear($quoteItem);
                        $quoteItem->setCustomPrice($item['unit_cost_excl_vat']);
                        $quoteItem->setOriginalCustomPrice($item['unit_cost_excl_vat']);
                        $extAttributes = $quoteItem->getExtensionAttributes();
                        $extAttributes->setEasTaxAmount($item['item_duties_and_taxes'] - $item['item_customs_duties']
                            - $item['item_eas_fee'] - $item['item_eas_fee_vat'] - $item['item_delivery_charge_vat']);
                        $extAttributes->setEasRowTotal($item['unit_cost_excl_vat'] * $quoteItem->getQty());

                        $extAttributes->setEasRowTotalInclTax($item['unit_cost_excl_vat'] * $quoteItem->getQty() +
                            $extAttributes->getEasTaxAmount() + $item['item_customs_duties'] +
                            $item['item_eas_fee'] + $item['item_eas_fee']);

                        $extAttributes->setEasTaxPercent($item['vat_rate']);
                        $extAttributes->setEasFee($item['item_eas_fee']);
                        $extAttributes->setVatOnEasFee($item['item_eas_fee_vat']);
                        $extAttributes->setEasCustomDuties($item['item_customs_duties']);
                    }
                }
                $quote->setItems($items);
            }

            $this->quoteRepository->save($quote);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            return $this->response->setRedirect($this->url->getUrl('checkout/') . '#payment');
        } else {
            return $this->response->setRedirect($this->url->getUrl('checkout/cart'));
        }
    }

    private function clear (Item $item) {
        $item->setEasTaxAmount(0);
        $item->setEasRowTotal(0);
        $item->setEasRowTotalInclTax(0);
        $item->setEasTaxPercent(0);
        $item->setEasFee(0);
        $item->setVatOnEasFee(0);
        $item->setEasCustomDuties(0);
    }
}

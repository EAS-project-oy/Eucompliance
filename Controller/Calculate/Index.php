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
     * Index constructor.
     * @param Request $request
     * @param ResponseInterface $response
     * @param JWT $jwt
     * @param UrlInterface $url
     * @param Calculate $calculate
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $session
     * @param Configuration $configuration
     */
    public function __construct(
        Request $request,
        ResponseInterface $response,
        JWT $jwt,
        UrlInterface $url,
        Calculate $calculate,
        CartRepositoryInterface $quoteRepository,
        Session $session,
        Configuration $configuration
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->jwt = $jwt;
        $this->url = $url;
        $this->calculate = $calculate;
        $this->quoteRepository = $quoteRepository;
        $this->session = $session;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        if ($this->configuration->isEnabled()) {
            $this->response->setRedirect($this->url->getUrl('checkout/cart'));
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
            $quote->setData('eas', $data[Configuration::EAS_FEE] + $data['taxes_and_duties']);

            foreach ($data['items'] as $item) {
                $items = $quote->getAllItems();
                foreach ($items as $quoteItem) {
                    if ($item['item_id'] == $quoteItem->getProductId()) {
                        $quoteItem->setPrice($item['item_duties_and_taxes'] + $quoteItem->getPrice());
                        $quoteItem->setBasePrice($item['item_duties_and_taxes'] + $quoteItem->getBasePrice());
                        $quoteItem->setTaxAmount($item['item_duties_and_taxes'] + $quoteItem->getTaxAmount());
                        $quoteItem->setBaseTaxAmount($item['item_duties_and_taxes'] + $quoteItem->getBaseTaxAmount());
                    }
                }
                $quote->setItems($items);
            }

            $this->quoteRepository->save($quote);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $this->response->setRedirect($this->url->getUrl('checkout/') . '#payment');
        } else {
            $this->response->setRedirect($this->url->getUrl('checkout/cart'));
        }
    }
}

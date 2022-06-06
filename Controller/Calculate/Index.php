<?php

namespace Easproject\Eucompliance\Controller\Calculate;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\Quote;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;

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
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var \Easproject\Eucompliance\Service\Quote
     */
    private Quote $serviceQuote;

    /**
     * Index constructor.
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\UrlInterface $url
     * @param \Easproject\Eucompliance\Model\Config\Configuration $configuration
     * @param \Easproject\Eucompliance\Service\Quote $serviceQuote
     */
    public function __construct(
        Request                 $request,
        ResponseInterface       $response,
        UrlInterface            $url,
        Configuration           $configuration,
        Quote $serviceQuote
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->url = $url;
        $this->configuration = $configuration;
        $this->serviceQuote = $serviceQuote;
    }

    public function execute()
    {
        if (!$this->configuration->isEnabled()) {
            return $this->response->setRedirect($this->url->getUrl('checkout/cart'));
        }

        $params = $this->request->getParams();
        if ($this->serviceQuote->saveQuoteData($params)) {
            return $this->response->setRedirect($this->url->getUrl('checkout/') . '#payment');
        } else {
            return $this->response->setRedirect($this->url->getUrl('checkout/cart'));
        }
    }
}

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

namespace Easproject\Eucompliance\Controller\Calculate;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\Quote;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;

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
     *
     * @param \Magento\Framework\Webapi\Rest\Request              $request
     * @param \Magento\Framework\App\ResponseInterface            $response
     * @param \Magento\Framework\UrlInterface                     $url
     * @param \Easproject\Eucompliance\Model\Config\Configuration $configuration
     * @param \Easproject\Eucompliance\Service\Quote              $serviceQuote
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

    /**
     * Calculate
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->configuration->isEnabled() || $this->configuration->isStandardSolution()) {
            return $this->response->setRedirect($this->url->getUrl('checkout/cart'));
        }

        $params = $this->request->getParams();
        if ($this->serviceQuote->saveQuoteData($params)) {
            return $this->response->setRedirect($this->url->getUrl('checkout/') . '#payment');
        }
        return $this->response->setRedirect($this->url->getUrl('checkout/cart'));
    }
}

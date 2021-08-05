<?php

namespace Eas\Eucompliance\Controller\Index;

use Eas\Eucompliance\Service\Calculate;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend_Http_Client_Exception;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Index implements ActionInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Calculate
     */
    private $calculate;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * Index constructor.
     * @param JsonFactory $jsonResultFactory
     * @param Session $session
     * @param Calculate $calculate
     */
    public function __construct(
        JsonFactory $jsonResultFactory,
        Session $session,
        Calculate $calculate
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->session = $session;
        $this->calculate = $calculate;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Zend_Http_Client_Exception
     */
    public function execute()
    {
        $calculateUrl = $this->calculate->calculate($this->session->getQuote());
        $result = $this->jsonResultFactory->create();
        $result->setData($calculateUrl);
        return $result;
    }
}

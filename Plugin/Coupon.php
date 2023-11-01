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

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\Calculate;
use Easproject\Eucompliance\Service\Quote;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\CouponManagement;

class Coupon
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var Calculate
     */
    private Calculate $calculate;

    /**
     * @var \Easproject\Eucompliance\Service\Quote
     */
    private Quote $serviceQuote;

    /**
     * @param Session $session
     * @param Calculate $calculate
     * @param Quote $serviceQuote
     * @param Configuration $configuration
     */
    public function __construct(
        Session   $session,
        Calculate $calculate,
        Quote     $serviceQuote,
        Configuration $configuration
    ) {
        $this->session = $session;
        $this->calculate = $calculate;
        $this->serviceQuote = $serviceQuote;
        $this->configuration = $configuration;
    }

    /**
     * After set plugin
     *
     * @param CouponManagement $subject
     * @param bool $result
     * @param int $cartId
     * @param string $couponCode
     * @return bool
     */
    public function afterSet(CouponManagement $subject, bool $result, $cartId, $couponCode): bool
    {
        if (!$this->configuration->isEnabled() || $this->configuration->isStandardSolution()) {
            return $result;
        }

        try {
            $quote = $this->session->getQuote();
            list($data, $response) = $this->calculate->sendRequest($quote);
            $separator = Configuration::EAS_CHECKOUT_TOKEN . '=';
            $response = explode($separator, $response);
            if (count($response) === 2) {
                $tempResponse = $response[1];
                $response = [];
                $response[Configuration::EAS_CHECKOUT_TOKEN] = $tempResponse;
                $this->serviceQuote->saveQuoteData($response, true);
            }
        } catch (\Exception $exception) {
            return $result;
        }

        return $result;
    }
}

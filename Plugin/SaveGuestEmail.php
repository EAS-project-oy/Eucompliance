<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class SaveGuestEmail
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * SaveGuestEmail constructor.
     * @param Session $session
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Session $session,
        QuoteRepository $quoteRepository
    ) {
        $this->session = $session;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param AccountManagement $subject
     * @param $customerEmail
     * @param null $websiteId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeIsEmailAvailable(AccountManagement $subject, $customerEmail, $websiteId = null): array
    {
        $this->session->getQuote()->setCustomerEmail($customerEmail);
        $this->quoteRepository->save($this->session->getQuote());
        return [$customerEmail, $websiteId];
    }
}

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

namespace Easproject\Eucompliance\Plugin;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;

class SaveGuestEmail
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
     * @var QuoteRepository
     */
    private QuoteRepository $quoteRepository;

    /**
     * SaveGuestEmail constructor.
     *
     * @param Session $session
     * @param QuoteRepository $quoteRepository
     * @param Configuration $configuration
     */
    public function __construct(
        Session $session,
        QuoteRepository $quoteRepository,
        Configuration $configuration
    ) {
        $this->session = $session;
        $this->quoteRepository = $quoteRepository;
        $this->configuration = $configuration;
    }

    /**
     * Plugin Before Is Email Available
     *
     * @param AccountManagement $subject
     * @param string $customerEmail
     * @param int|null $websiteId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeIsEmailAvailable(
        AccountManagement $subject,
        string $customerEmail,
        int $websiteId = null
    ): array {
        if ($this->configuration->isEnabled() && !$this->configuration->isStandardSolution()) {
            $this->session->getQuote()->setCustomerEmail($customerEmail);
            $this->quoteRepository->save($this->session->getQuote());
        }
        return [$customerEmail, $websiteId];
    }
}

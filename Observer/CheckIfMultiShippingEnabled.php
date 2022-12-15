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

namespace Easproject\Eucompliance\Observer;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CheckIfMultiShippingEnabled implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    public ManagerInterface $messageManager;

    /**
     * @var Configuration
     */
    public Configuration $configuration;

    /**
     * Constructor CheckIfMultiShippingEnabled
     *
     * @param ManagerInterface $messageManager
     * @param Configuration $configuration
     */
    public function __construct(
        ManagerInterface $messageManager,
        Configuration $configuration
    ) {
        $this->messageManager = $messageManager;
        $this->configuration = $configuration;
    }

    /**
     * Check if multi shipping is enabled and show message
     *
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if ($this->configuration->isMultiShippingEnabled()) {
            $this->messageManager->addWarningMessage(
                __($this->configuration->getWarningMessage())
            );
        }
        return $this;
    }
}

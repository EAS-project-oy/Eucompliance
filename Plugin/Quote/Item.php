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

namespace Easproject\Eucompliance\Plugin\Quote;

use Easproject\Eucompliance\Model\Config\Configuration;
use Easproject\Eucompliance\Service\CartItem;
use Magento\Framework\Model\AbstractModel;

class Item
{
    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var CartItem
     */
    private CartItem $cartItemService;

    /**
     * @param CartItem $cartItemService
     * @param Configuration $configuration
     */
    public function __construct(
        CartItem $cartItemService,
        Configuration $configuration
    ) {
        $this->cartItemService = $cartItemService;
        $this->configuration = $configuration;
    }

    /**
     * Before Save Plugin
     *
     * @param  \Magento\Quote\Model\ResourceModel\Quote\Item $subject
     * @param  AbstractModel                                 $cartItem
     * @return array
     */
    public function beforeSave(\Magento\Quote\Model\ResourceModel\Quote\Item $subject, AbstractModel $cartItem): array
    {
        if ($this->configuration->isEnabled() && !$this->configuration->isStandardSolution()) {
            $this->cartItemService->handleAttributes($cartItem);
        }
        return [$cartItem];
    }
}

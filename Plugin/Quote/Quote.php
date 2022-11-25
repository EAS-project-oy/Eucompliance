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
use Magento\Quote\Model\Quote\Item;

class Quote
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
     * Plugin After Get All Visible Items
     *
     * @param  \Magento\Quote\Model\Quote $subject
     * @param  Item[]                     $result
     * @return Item[]
     */
    public function afterGetAllVisibleItems(\Magento\Quote\Model\Quote $subject, array $result): array
    {
        if ($this->configuration->isEnabled()) {
            foreach ($result as $item) {
                $this->cartItemService->handleAttributes($item, CartItem::SET);
            }
        }
        return $result;
    }
}

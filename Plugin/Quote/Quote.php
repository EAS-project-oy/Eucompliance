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

use Easproject\Eucompliance\Service\CartItem;
use Magento\Quote\Model\Quote\Item;

class Quote
{
    /**
     * @var CartItem
     */
    private CartItem $cartItemService;

    /**
     * @param CartItem $cartItemService
     */
    public function __construct(CartItem $cartItemService)
    {
        $this->cartItemService = $cartItemService;
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
        foreach ($result as $item) {
            $this->cartItemService->handleAttributes($item, CartItem::SET);
        }
        return $result;
    }
}

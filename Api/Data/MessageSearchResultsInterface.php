<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Api\Data;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
interface MessageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get message list.
     * @return \Eas\Eucompliance\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * Set error_type list.
     * @param \Eas\Eucompliance\Api\Data\MessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

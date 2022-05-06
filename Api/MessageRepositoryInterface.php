<?php

declare(strict_types=1);

namespace Eas\Eucompliance\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
interface MessageRepositoryInterface
{

    /**
     * Save message
     * @param \Eas\Eucompliance\Api\Data\MessageInterface $message
     * @return \Eas\Eucompliance\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Eas\Eucompliance\Api\Data\MessageInterface $message
    );

    /**
     * Retrieve message
     * @param string $messageId
     * @return \Eas\Eucompliance\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($messageId);

    /**
     * Retrieve message matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Eas\Eucompliance\Api\Data\MessageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete message
     * @param \Eas\Eucompliance\Api\Data\MessageInterface $message
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Eas\Eucompliance\Api\Data\MessageInterface $message
    );

    /**
     * Delete message by ID
     * @param string $messageId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($messageId);
}

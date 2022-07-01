<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 * PHP version 7
 *
 * @category Module
 * @package  Easproject_Eucompliance
 * @author   EAS Project <magento@easproject.org>
 * @license  https://github.com/EAS-project-oy/eascompliance/ General License
 * @link     https://github.com/EAS-project-oy/eascompliance
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Api;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 *
 * @author  EAS Project <magento@easproject.org>
 * @license https://github.com/EAS-project-oy/eascompliance/ General License
 * @link    https://github.com/EAS-project-oy/eascompliance
 */
interface MessageRepositoryInterface
{

    /**
     * Save message
     *
     * @param \Easproject\Eucompliance\Api\Data\MessageInterface $message message
     *
     * @return \Easproject\Eucompliance\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Easproject\Eucompliance\Api\Data\MessageInterface $message
    ): Data\MessageInterface;

    /**
     * Retrieve message
     *
     * @param string $messageId message id
     *
     * @return \Easproject\Eucompliance\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get(string $messageId): Data\MessageInterface;

    /**
     * Retrieve message matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria criteria
     *
     * @return \Easproject\Eucompliance\Api\Data\MessageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): Data\MessageSearchResultsInterface;

    /**
     * Delete message
     *
     * @param \Easproject\Eucompliance\Api\Data\MessageInterface $message message
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Easproject\Eucompliance\Api\Data\MessageInterface $message
    ): bool;

    /**
     * Delete message by ID
     *
     * @param string $messageId message id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(string $messageId): bool;
}

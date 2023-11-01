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

namespace Easproject\Eucompliance\Api;

use Easproject\Eucompliance\Api\Data\JobInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 *
 * @author  EAS Project <magento@easproject.org>
 * @license https://github.com/EAS-project-oy/eascompliance/ General License
 * @link    https://github.com/EAS-project-oy/eascompliance
 */
interface JobRepositoryInterface
{

    /**
     * Save job
     *
     * @param JobInterface $job
     * @return JobInterface
     * @throws LocalizedException
     */
    public function save(
        JobInterface $job
    ): Data\JobInterface;

    /**
     * Retrieve job
     *
     * @param int $jobId
     * @return JobInterface
     * @throws LocalizedException
     */
    public function get(int $jobId): Data\JobInterface;

    /**
     * Retrieve job matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria criteria
     * @return Data\JobSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): Data\JobSearchResultsInterface;

    /**
     * Delete job
     *
     * @param JobInterface $job
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        JobInterface $job
    ): bool;

    /**
     * Delete job by ID
     *
     * @param int $jobId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $jobId): bool;
}

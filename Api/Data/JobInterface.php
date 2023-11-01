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

namespace Easproject\Eucompliance\Api\Data;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 *
 * @author  EAS Project <magento@easproject.org>
 * @license https://github.com/EAS-project-oy/eascompliance/ General License
 * @link    https://github.com/EAS-project-oy/eascompliance
 */
interface JobInterface
{

    public const JOB_ID = 'job_id';
    public const ERROR = 'error';
    public const STATUS = 'status';
    public const SYNCED = 'synced';

    /**
     * @return int|null
     */
    public function getJobId(): ?int;

    /**
     * @param int $jobId
     * @return $this
     */
    public function setJobId(int $jobId): JobInterface;

    /**
     * @return string|null
     */
    public function getError(): ?string;

    /**
     * @param string $error
     * @return $this
     */
    public function setError(string $error): JobInterface;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): JobInterface;

    /**
     * @return int
     */
    public function getSynced(): int;

    /**
     * @param int $synced
     * @return JobInterface
     */
    public function setSynced(int $synced): JobInterface;
}

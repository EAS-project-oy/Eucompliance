<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Easproject\Eucompliance\Model;

use Easproject\Eucompliance\Api\Data\JobInterface;
use Magento\Framework\Model\AbstractModel;

class Job extends AbstractModel implements JobInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Message::class);
    }

    /**
     * @inheritDoc
     */
    public function getJobId(): ?int
    {
        return (int)$this->getData(self::JOB_ID);
    }

    /**
     * @inheritDoc
     */
    public function setJobId(int $jobId): JobInterface
    {
        return $this->setData(self::JOB_ID, $jobId);
    }

    /**
     * @inheritDoc
     */
    public function getError(): ?string
    {
        return $this->getData(self::ERROR);
    }

    /**
     * @inheritDoc
     */
    public function setError(string $error): JobInterface
    {
        return $this->setData(self::ERROR, $error);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ?string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): JobInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getSynced(): int
    {
        return (int)$this->getData(self::SYNCED);
    }

    /**
     * @inheritDoc
     */
    public function setSynced(int $synced): JobInterface
    {
        return $this->setData(self::SYNCED, (int)(bool)$synced);
    }
}

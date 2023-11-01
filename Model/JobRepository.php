<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Easproject\Eucompliance\Model;

use Easproject\Eucompliance\Api\Data\JobInterface;
use Easproject\Eucompliance\Api\Data\JobInterfaceFactory;
use Easproject\Eucompliance\Api\Data\JobSearchResultsInterface;
use Easproject\Eucompliance\Api\Data\JobSearchResultsInterfaceFactory;
use Easproject\Eucompliance\Api\Data\MessageInterfaceFactory;
use Easproject\Eucompliance\Api\Data\MessageSearchResultsInterfaceFactory;
use Easproject\Eucompliance\Api\JobRepositoryInterface;
use Easproject\Eucompliance\Model\ResourceModel\Job\CollectionFactory;
use Easproject\Eucompliance\Model\ResourceModel\Job as ResourceJob;
use Easproject\Eucompliance\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class JobRepository implements JobRepositoryInterface
{

    /**
     * @var JobInterfaceFactory
     */
    protected JobInterfaceFactory $jobFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $jobCollectionFactory;

    /**
     * @var JobSearchResultsInterfaceFactory
     */
    protected JobSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected CollectionProcessorInterface $collectionProcessor;

    /**
     * @var ResourceJob
     */
    protected ResourceJob $resource;

    /**
     * @param ResourceJob $resource
     * @param JobInterfaceFactory $jobFactory
     * @param CollectionFactory $jobCollectionFactory
     * @param JobSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceJob $resource,
        JobInterfaceFactory $jobFactory,
        CollectionFactory $jobCollectionFactory,
        JobSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->jobFactory = $jobFactory;
        $this->jobCollectionFactory = $jobCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(JobInterface $job): JobInterface
    {
        try {
            $this->resource->save($job);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the job: %1',
                    $exception->getMessage()
                )
            );
        }
        return $job;
    }

    /**
     * @inheritDoc
     */
    public function get(int $jobId): JobInterface
    {
        $job = $this->jobFactory->create();
        $this->resource->load($job, $jobId);
        if (!$job->getId()) {
            throw new NoSuchEntityException(__('job with id "%1" does not exist.', $jobId));
        }
        return $job;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ): JobSearchResultsInterface {
        $collection = $this->jobCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(JobInterface $job): bool
    {
        try {
            $jobModel = $this->jobFactory->create();
            $this->resource->load($jobModel, $job->getJobId());
            $this->resource->delete($jobModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the job: %1',
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $jobId): bool
    {
        return $this->delete($this->get($jobId));
    }
}

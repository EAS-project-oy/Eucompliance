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

namespace Easproject\Eucompliance\Model\ResourceModel\Job;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'job_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Easproject\Eucompliance\Model\Job::class,
            \Easproject\Eucompliance\Model\ResourceModel\Job::class
        );
    }
}

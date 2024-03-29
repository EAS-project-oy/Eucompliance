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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 *
 * @author  EAS Project <magento@easproject.org>
 * @license https://github.com/EAS-project-oy/eascompliance/ General License
 * @link    https://github.com/EAS-project-oy/eascompliance
 */
interface JobSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get message list.
     *
     * @return JobInterface[]
     */
    public function getItems();

    /**
     * Set error_type list.
     *
     * @param JobInterface[] $items items
     *
     * @return $this
     */
    public function setItems(array $items);
}

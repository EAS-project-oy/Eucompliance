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

namespace Easproject\Eucompliance\Plugin\Sales\Order;

use Easproject\Eucompliance\Model\Config\Configuration;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class Grid
{
    public const EAS_TOKEN_IS_NOT_NULL = '`eas_token` IS NOT NULL';
    public const EAS_TOKEN_IS_NULL = '`eas_token` IS NULL';

    public const EAS_CONDITION_YES = "`eas_token` LIKE '%Yes%'";
    public const EAS_CONDITION_NO = "`eas_token` LIKE '%No%'";

    /**
     * @var string
     */
    public static string $table = 'sales_order_grid';

    /**
     * @var string
     */
    public static string $leftJoinTable = 'quote';

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * Plugin constructor
     *
     * @param Configuration $configuration
     */
    public function __construct(
        Configuration $configuration,
    ) {
        $this->configuration = $configuration;
    }

    /**
     * Add eas_token to collection
     *
     * @param Reporting $subject
     * @param Collection $result
     * @param SearchCriteriaInterface $collection
     * @return mixed
     */
    public function afterSearch(Reporting $subject, $result, $collection)
    {
        if (!$this->configuration->isEnabled()) {
            return $result;
        }

        if ($result->getMainTable() === $result->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $result->getConnection()->getTableName(self::$leftJoinTable);

            $result
                ->getSelect()
                ->joinLeft(
                    ['co' => $leftJoinTableName],
                    "co.reserved_order_id = main_table.increment_id",
                    [
                        'eas_token' => 'co.eas_token'
                    ]
                );

            $where = $result->getSelect()->getPart(Select::WHERE);

            foreach ($where as $key => $condition) {
                if (strripos($condition, self::EAS_CONDITION_YES)) {
                    $newCondition = str_ireplace(
                        self::EAS_CONDITION_YES,
                        self::EAS_TOKEN_IS_NOT_NULL,
                        $condition
                    );
                } elseif (strripos($condition, self::EAS_CONDITION_NO)) {
                    $newCondition = str_ireplace(
                        self::EAS_CONDITION_NO,
                        self::EAS_TOKEN_IS_NULL,
                        $condition
                    );
                } else {
                    $newCondition = str_ireplace(
                        '%',
                        '',
                        $condition
                    );

                }
                $where[$key] = $newCondition;
            }

            $result->getSelect()->setPart(Select::WHERE, $where);

        }
        return $result;
    }
}

<?php
/**
 * Copyright © EAS Project Oy. All rights reserved.
 */

declare(strict_types=1);

namespace Easproject\Eucompliance\Plugin\Sales\Order;

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
     * Add eas_token to collection
     *
     * @param Reporting $subject
     * @param Collection $collection
     * @return Collection
     * @throws \Zend_Db_Select_Exception
     */
    public function afterSearch(Reporting $subject, Collection $collection): Collection
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection
                ->getSelect()
                ->joinLeft(
                    ['co' => $leftJoinTableName],
                    "co.reserved_order_id = main_table.increment_id",
                    [
                        'eas_token' => 'co.eas_token'
                    ]
                );

            $where = $collection->getSelect()->getPart(Select::WHERE);

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

            $collection->getSelect()->setPart(Select::WHERE, $where);

        }
        return $collection;
    }
}

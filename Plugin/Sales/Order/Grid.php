<?php

namespace Easproject\Eucompliance\Plugin\Sales\Order;

use Zend_Db_Select;

/**
 * Copyright © EAS Project Oy. All rights reserved.
 */
class Grid
{
    const EAS_TOKEN_IS_NOT_NULL = '`eas_token` IS NOT NULL';
    const EAS_TOKEN_IS_NULL = '`eas_token` IS NULL';

    const EAS_CONDITION_YES = "`eas_token` LIKE '%Yes%'";
    const EAS_CONDITION_NO = "`eas_token` LIKE '%No%'";

    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'quote';

    /**
     * @param $intercepter
     * @param $collection
     * @return mixed
     */
    public function afterSearch($intercepter, $collection)
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

            $where = $collection->getSelect()->getPart(Zend_Db_Select::WHERE);

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

            $collection->getSelect()->setPart(Zend_Db_Select::WHERE, $where);

        }
        return $collection;
    }
}

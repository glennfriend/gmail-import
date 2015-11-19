<?php
/**
 *  該程式於 model 呼叫使用
 *  代入特定的 white-list array 
 *  將會處理裡面的參數
 *
 *  白名單格式 $whiteList example
 *      array (
 *          'field' => [
 *              'id'            => 'order_item.id',
 *              'orderId'       => 'order_item.order_id',
 *              'status'        => 'order_item.status',
 *              'sku'           => 'order_item.sku',
 *              'orderId'       => 'order.order_number',
 *              'orderNumber'   => 'order.order_number',
 *          ],
 *          'option' => [
 *              '_order',
 *              '_page',
 *              '_itemsPerPage',
 *          ]
 *      );
 *
 */
class ZendModelWhiteListHelper
{

    /**
     *  如果代入的欄位不在白名單, 會 trigger error
     *
     *  @param array $options, 標準欄位格式
     *  @param array $allowFields, 予許的欄位白名單
     */
    public static function validateFields( Array $option, Array $whiteList )
    {
        foreach ( $option as $field => $value) {
            if ( !in_array($field, array_keys($whiteList['fields'])) &&
                 !in_array($field, $whiteList['option'] )
            ) {
                $field = preg_replace("/[^a-zA-Z0-9_]+/", '', $field );
                trigger_error("Custom Model Error: field not found '{$field}'", E_USER_ERROR);
            }
        }
    }

    /**
     *  將陣列資料的 null 轉換為 空字串
     *
     *  @param array $options, 標準欄位格式
     *  @return array
     */
    public static function fieldValueNullToEmpty( Array & $options )
    {
        foreach ( $options as $field => $value) {
            if ( is_null($options[$field]) ) {
                $options[$field] = '';
            }
        }
    }

    /**
     *  filter order-by string
     *  如果欄位名稱沒包含在白名單內
     *  則 unset() 這筆資料
     *
     *  該程式 會 區分大寫小
     *
     *  example:
     *      array(
     *          "_order" => "order_number desc"
     *      )
     *      , array(
     *          "order_id",
     *          "order_number",
     *      )
     *
     *  @return array
     */
    public static function filterOrder( Array & $option, Array $whiteList, $name='_order' )
    {
        // 如果欄位不存在, 則不處理
        if ( !isset($option[$name]) ) {
            return $option;
        }
        $orderBy = explode(',', trim( $option[$name] ) );


        // field
        $field = $orderBy[0];
        if ( !in_array( $field, array_keys($whiteList['fields']) ) ) {
            unset( $option[$name] );
            return $option;
        }

        // order by
        $order = isset($orderBy[1]) ? $orderBy[1] : 'asc';
        if ( 'desc' != strtolower($order) ) {
            $order = 'asc';
        }

        $option[$name] = $whiteList['fields'][$field].' '.$order;
    }

    /**
     *  包裏多數資料 (陣列格式) 的 Zend Db 語法
     *
     *  由於 Zend Db 在包裏 nest, unnset 之間的條件必須在一個式子裡面
     *  所以這裡用比較醜的方式來重新包裏 ( values[0] and values[1] and ... )
     *
     *  example:
     *
     *      $select
     *          ->where
     *          ->and
     *          ->nest
     *              ->like( $field, '%'. $values[0] .'%' )
     *              ->or
     *              ->like( $field, '%'. $values[1] .'%' )
     *              ->or
     *              ->like( $field, '%'. $values[2] .'%' )
     *          ->unnest
     *      ;
     *
     */
    public static function nestLikeOr(Zend\Db\Sql\Select $select, $field, Array $values)
    {
        if (1 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                ->unnest
            ;
        }
        elseif (2 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                ->unnest
            ;
        }
        elseif (3 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                    ->or
                    ->like( $field, '%'. $values[2] .'%' )
                ->unnest
            ;
        }
        elseif (4 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                    ->or
                    ->like( $field, '%'. $values[2] .'%' )
                    ->or
                    ->like( $field, '%'. $values[3] .'%' )
                ->unnest
            ;
        }
        elseif (5 == count($values)) {
            $select
                ->where
                ->and
                ->nest
                    ->like( $field, '%'. $values[0] .'%' )
                    ->or
                    ->like( $field, '%'. $values[1] .'%' )
                    ->or
                    ->like( $field, '%'. $values[2] .'%' )
                    ->or
                    ->like( $field, '%'. $values[3] .'%' )
                    ->or
                    ->like( $field, '%'. $values[4] .'%' )
                ->unnest
            ;
        }
        else {
            throw new Exception('f324093j4806384506k8320495k830495k83405934');
        }

    }

}

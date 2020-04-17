<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\extensions\ProductDesigner\models as ProductDesignerORM;

class QuoteOrdersProductsAttributes extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'quote_orders_products_attributes';
    }

    /*
     * one-to-one
     * @return object
     */
    public function getOrder()
    {
        return $this->hasOne(QuoteOrders::className(), ['orders_id' => 'orders_id']);
    }
}

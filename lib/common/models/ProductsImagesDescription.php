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

class ProductsImagesDescription extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'products_images_description';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getImage()
    {
        return $this->hasOne(ProductsImages::className(), ['products_images_id' => 'products_images_id']);
    }
}
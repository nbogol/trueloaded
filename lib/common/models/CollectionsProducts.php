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

class CollectionsProducts extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'collections_to_products';
    }    
    
    public function getCollections(){
        return $this->hasOne(Collections::className(), ['collections_id' => 'collections_id']);
    }
}
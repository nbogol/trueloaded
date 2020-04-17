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

use yii\db\ActiveRecord;

class RegularOffers extends ActiveRecord
{
    /**
     * @property int $platform_id
     */
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'regular_offers';
    }

}
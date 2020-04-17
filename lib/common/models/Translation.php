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


/**
 * This is the model class for table "translation".
 */
class Translation extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'translation';
    }

    /**
     * @param $language_id
     * @param $translation_key
     * @param $translation_entity
     * @param $translation_value
     * @return object
     */
    public static function create($language_id, $translation_key, $translation_entity, $translation_value)
    {
    	$model = new static();
    	$model->language_id = $language_id;
    	$model->translation_key = $translation_key;
    	$model->translation_entity = $translation_entity;
    	$model->translation_value = $translation_value;
    	$model->translated = 1;
    	return $model;
    }
}

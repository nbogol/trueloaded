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

use common\models\queries\LanguagesQuery;
use Yii;
use yii\helpers\Html;
/**
 * This is the model class for table "languages".
 *
 * @property integer $languages_id
 * @property string $name
 * @property string $code
 * @property string $image
 * @property string $image_svg
 * @property string $directory
 * @property integer $sort_order
 * @property integer $languages_status
 * @property string $locale
 * @property integer $shown_language
 * @property integer $searchable_language
 *
 * @property string $logo
 * @property string $svgSrc
 *
 * @property OrdersStatus[] $ordersStatuses
 * @property OrdersStatusGroups[] $ordersStatusGroups
 */
class Languages extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    public $logo = '';
    public $svgSrc = '';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'languages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['sort_order', 'languages_status', 'shown_language', 'searchable_language'], 'integer'],
            [['name', 'directory'], 'string', 'max' => 32],
            [['code'], 'string', 'max' => 2],
            [['image', 'image_svg'], 'string', 'max' => 64],
            [['locale'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'languages_id' => 'Languages ID',
            'name' => 'Name',
            'code' => 'Code',
            'image' => 'Image',
            'image_svg' => 'Image Svg',
            'directory' => 'Directory',
            'sort_order' => 'Sort Order',
            'languages_status' => 'Languages Status',
            'locale' => 'Locale',
            'shown_language' => 'Shown Language',
            'searchable_language' => 'Searchable Language',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersStatus()
    {
        return $this->hasOne(OrdersStatus::class, ['language_id' => 'languages_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersStatusGroup()
    {
        return $this->hasOne(OrdersStatusGroups::class, ['language_id' => 'languages_id']);
    }

    public function getLanguageData()
    {
        return $this->hasOne(LanguagesData::class, ['language_code' => 'code']);
    }
    public function afterFind()
    {
        parent::afterFind(); // TODO: Change the autogenerated stub
        $this->logo = (empty($this->image) || empty($this->name))? '' : Html::img(DIR_WS_CATALOG . DIR_WS_ICONS . $this->image,['alt'=>$this->name,'title'=>$this->name,'width'=>24,'height'=>16,'class'=>"language-icon"]);
        $this->svgSrc =(empty($this->image_svg) || empty($this->name))? '' : Html::img(DIR_WS_CATALOG . DIR_WS_ICONS . $this->image_svg,['alt'=>$this->name,'title'=>$this->name,'width'=>24,'height'=>16]);
    }

    /**
     * @inheritdoc
     * @return LanguagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LanguagesQuery(static::class);
    }
}

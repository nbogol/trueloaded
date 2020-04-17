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
 * This is the model class for table "groups".
 *
 * @property int $groups_id
 * @property string $groups_name
 * @property string $groups_discount
 * @property int $groups_is_tax_applicable
 * @property int $groups_is_reseller
 * @property int $groups_disable_checkout
 * @property string $date_added
 * @property string $last_modified
 * @property int $groups_is_show_price
 * @property int $new_approve
 * @property string $image_active
 * @property string $image_inactive
 * @property int $apply_groups_discount_to_specials
 * @property int $disable_watermark
 * @property int $groups_use_more_discount
 * @property string $superdiscount_summ
 * @property float $bonus_points_currency_rate
 *
 * @property GroupsCategories[] $groupsCategories
 * @property Categories[] $categories
 * @property GroupsProducts[] $groupsProducts
 * @property Products[] $products
 */
class Groups extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['groups_discount', 'superdiscount_summ', 'bonus_points_currency_rate'], 'number'],
            [['groups_is_tax_applicable', 'groups_is_reseller', 'groups_disable_checkout', 'groups_is_show_price', 'new_approve', 'apply_groups_discount_to_specials', 'disable_watermark', 'groups_use_more_discount'], 'integer'],
            [['date_added', 'last_modified'], 'safe'],
            [['groups_name', 'image_active', 'image_inactive'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'groups_id' => 'Groups ID',
            'groups_name' => 'Groups Name',
            'groups_discount' => 'Groups Discount',
            'groups_is_tax_applicable' => 'Groups Is Tax Applicable',
            'groups_is_reseller' => 'Groups Is Reseller',
            'groups_disable_checkout' => 'Groups Disable Checkout',
            'date_added' => 'Date Added',
            'last_modified' => 'Last Modified',
            'groups_is_show_price' => 'Groups Is Show Price',
            'new_approve' => 'New Approve',
            'image_active' => 'Image Active',
            'image_inactive' => 'Image Inactive',
            'apply_groups_discount_to_specials' => 'Apply Groups Discount To Specials',
            'disable_watermark' => 'Disable Watermark',
            'groups_use_more_discount' => 'Groups Use More Discount',
            'superdiscount_summ' => 'Superdiscount Summ',
            'bonus_points_currency_rate' => 'Bonus Points/Currency - Rate',
        ];
    }

    /**
     * one-to-many
     * @return object
     */
    public function getAdditionalDiscounts()
    {
        return $this->hasMany(GroupsDiscounts::class, ['groups_id' => 'groups_id'])->orderBy('groups_discounts_amount');
    }
    
    public function getAdditionalDiscountsCS()
    {
        return $this->hasMany(GroupsDiscounts::class, ['groups_id' => 'groups_id'])->where('check_supersum = 1')->orderBy('groups_discounts_amount');
    }
    
    public function getAdditionalDiscountsNCS()
    {
        return $this->hasMany(GroupsDiscounts::class, ['groups_id' => 'groups_id'])->where('check_supersum = 0')->orderBy('groups_discounts_amount');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupsCategories()
    {
        return $this->hasMany(GroupsCategories::class, ['groups_id' => 'groups_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Categories::class, ['categories_id' => 'categories_id'])->viaTable('groups_categories', ['groups_id' => 'groups_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupsProducts()
    {
        return $this->hasMany(GroupsProducts::class, ['groups_id' => 'groups_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::class, ['products_id' => 'products_id'])->viaTable('groups_products', ['groups_id' => 'groups_id']);
    }
}

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

use common\models\queries\CustomersQuery;
use Yii;
use yii\db\ActiveRecord;
use common\models\PersonalCatalog;
use common\models\Orders;
use yii\db\ColumnSchema;
use yii\db\Query;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "customers".
 *
 * @property int $customers_id
 * @property string $customers_gender
 * @property string $customers_firstname
 * @property string $customers_lastname
 * @property string $customers_dob
 * @property string $customers_email_address
 * @property int $platform_id
 * @property int $customers_default_address_id
 * @property string $customers_telephone
 * @property string $customers_landline
 * @property string $customers_fax
 * @property string $customers_password
 * @property string $customers_newsletter
 * @property string $customers_selected_template
 * @property int $admin_id
 * @property string $customers_alt_email_address
 * @property string $customers_alt_telephone
 * @property string $customers_cell
 * @property int $customers_owc_member
 * @property int $customers_type_id
 * @property int $customers_bonus_points
 * @property string $customers_credit_avail
 * @property int $affiliate_id
 * @property int $groups_id
 * @property int $customers_status
 * @property string $last_xml_import
 * @property string $last_xml_export
 * @property int $opc_temp_account
 * @property string $customers_company
 * @property string $customers_company_vat
 * @property float $credit_amount
 * @property int $sap_servers_id
 * @property int $customers_currency_id
 * @property string $customers_cardcode
 * @property int $currency_switcher
 * @property int $erp_customer_id
 * @property string $erp_customer_code
 * @property int $trustpilot_disabled
 * @property bool $dob_flag [tinyint(1)]
 * @property int $departments_id [int(11)]
 * @property string $pin [varchar(8)]
 * @property int $_api_time_modified [timestamp]
 * @property string $payerreference [varchar(255)]
 * @property int $language_id
 */
class Customers extends ActiveRecord 
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    
    public $multi_customer_id = 0;
    
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'customers';
    }
    
    public static function findIdentity($id){
        return static::findOne(['customers_id' => $id]);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
       
 
    public function getAuthKey()
    {
        return $this->authKey;
    }
 
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
    public function findIdentityByEmail($email)
    {
        return static::find()
                ->where(['or', ['customers_email_address' => $email], ['erp_customer_code' => $email] ])
                ->andWhere(['customers_status' => 1, 'opc_temp_account' => 0])
                ->limit(1)->one();
    }

    public function getProducts(){
        return $this->hasMany(\common\models\Products::className(), ['products_id' => 'products_id'])
                    ->viaTable('personal_catalog', ['customers_id' => 'customers_id']);
    }

    public function getPersonalCatalog()
    {
        return $this->hasMany(PersonalCatalog::className(), ['customers_id' => 'customers_id']);
    }


    public function getAddressBooks(){
        return $this->hasMany(AddressBook::className(), ['customers_id' => 'customers_id'])->joinWith('country');
    }
    
    public function hasAddressBooks(){
        return count($this->getAddressBooks());
    }

    public function init() {
        if($this->isNewRecord){
            $this->platform_id = \common\classes\platform::currentId();
        }
        parent::init();
    }

    public function getCustomersEmails(){
        return $this->hasMany(CustomersEmails::className(), ['customers_id' => 'customers_id']);
    }

    public function getCustomersPhones(){
        return $this->hasMany(CustomersPhones::className(), ['customers_id' => 'customers_id']);
    }

    /*
    public function getAddressBook(){
        return $this->hasMany(AddressBook::className(), ['customers_id' => 'customers_id']);
    }*/
    public function getAddressBook($id){
        return $this->hasOne(AddressBook::className(), ['customers_id' => 'customers_id'])
                ->onCondition(['address_book_id' => $id])->joinWith('country');
    }

    /**
     * @param $email
     *
     * @return Customers
     */
    public static function findByEmail($email) {

        $customerModel = static::find()
            ->where([ 'customers_email_address' => $email ])
            ->limit(1)
            ->one();
        if ( !$customerModel ) {
            $customerModel = static::find()
                ->joinWith('customersEmails')
                ->where([ CustomersEmails::tableName() . '.customers_email' => $email ])
                ->limit(1)
                ->one();
        }
        return $customerModel;
        /*
        return static::find()
            ->joinWith('customersEmails')
            ->where([ CustomersEmails::tableName() . '.customers_email' => $email ])
            ->orWhere([static::tableName() . '.customers_email_address' => $email ])
            ->limit(1)
            ->one();
        */
    }
    
    /**
     * @param $email
     *
     * @return Customers
     */
    public static function findByMultiEmail($email) {
      if(\common\extensions\CustomersMultiEmails\CustomersMultiEmails::allowed()) {
        //2do same agent (email) of several customers
        $multi = \common\extensions\CustomersMultiEmails\models\CustomersMultiEmails::find()
                ->where(['customers_email' => $email])
                ->limit(1)->one();
        if (is_object($multi)) {
            $customer = static::find()
                ->where(['customers_id' => $multi->customers_id])
                ->andWhere(['customers_status' => 1, 'opc_temp_account' => 0])
                ->limit(1)->one();
            if (is_object($customer)) {
                $customer->customers_email_address = $multi->customers_email;
                $customer->customers_password = $multi->customers_password;
                $customer->customers_firstname = $multi->customers_firstname;
                $customer->customers_lastname = $multi->customers_lastname;
                
                $customer->multi_customer_id = $multi->id;

                return $customer;
            }
        }
      }
      return NULL;
    }

    /**
     * @param $email
     *
     * @return Customers
     */
    public static function findByPhone($email) {
        return static::find()
            ->joinWith('customersPhones')
            ->where([ CustomersPhones::tableName() . '.customers_phone' => $email ])
            ->orWhere([static::tableName() . '.customers_telephone' => $email ])
            ->limit(1)
            ->one();
    }

    public function addPhone($phone){
        if(!($customer = static::findByPhone($phone))){
            if(!trim($this->customers_telephone)){
                $this->customers_telephone = $phone;
                $this->save(false);
                (new CustomersPhones(['customers_phone' => $phone]))->link('customer', $this);
            }
        }
        return $this;
    }

    public function addEmail($email){
        if(!($customer = static::findByPhone($email))){
            if(!trim($this->customers_email_address)) {
                $this->customers_email_address = $email;
                $this->save(false);
                (new CustomersEmails([ 'customers_email' => $email ]))->link( 'customer', $this );
            }
        }
        return $this;
    }
    
    public function getOrders()
    {
        return $this->hasMany(Orders::className(), ['customers_id' => 'customers_id']);
    }
    
    public function getOrdersTotals(){
        return $this->hasMany(OrdersTotal::className(), ['orders_id' => 'orders_id'])->viaTable(Orders::tableName(), ['customers_id' => 'customers_id']);
    }

    /**
    * @param $withTax boolean, $from, $to - datetime db format or null
    * @return ordered total amount
    **/
    public function fetchOrderTotalAmount($withTax = false, $from = null, $to = null){
        $amount = 0;
        $query = $this->getOrdersTotals()->onCondition('class="ot_total"')->innerJoinWith([
            'order' => function (\yii\db\ActiveQuery $query) use ($from, $to){
                if (!is_null($from)){
                    $query->andOnCondition(['>=','date_purchased', $from]);
                }
                if (!is_null($to)){
                    $query->andOnCondition(['<=','date_purchased', $to]);
                }
                if (defined('ORDER_COMPLETE_STATUSES')){
                    $completedStatuses = array_map("intval", explode(",", ORDER_COMPLETE_STATUSES));
                    if ($completedStatuses) $query->andOnCondition(['orders_status' => $completedStatuses]);
                }
            }
        ]);
        
        $list = $query->asArray()->all();
        if ($list){
            if ($withTax){
                $amount = array_sum(ArrayHelper::getColumn($list, 'value_inc_tax'));
            } else {
                $amount = array_sum(ArrayHelper::getColumn($list, 'value_exc_vat'));
            }
        }
        return $amount;
    }

    public function getDefaultAddress(){
    	return $this->hasOne(AddressBook::className(), ['address_book_id' => 'customers_default_address_id'])
                ->joinWith('country');
    }

    public function getInfo(){
    	return $this->hasOne(CustomersInfo::class, ['customers_info_id' => 'customers_id']);
    }

    public function getGroup(){
    	return $this->hasOne(Groups::class, ['groups_id' => 'groups_id']);
    }

    public function editCustomersPassword($customersPassword): void
    {
        $this->customers_password = $customersPassword;
        $this->save();
    }

    public function editCustomersNewsletter($customersNewsletter): void
    {
        $this->customers_newsletter = $customersNewsletter;
        $this->save();
    }

    public function editCustomerDetails(array $customerDetails): void
    {
        $this->customers_email_address = $customerDetails['customers_email_address'];
        $this->customers_gender = $customerDetails['customers_gender'];
        $this->customers_firstname = $customerDetails['customers_firstname'];
        $this->customers_lastname = $customerDetails['customers_lastname'];
        $this->customers_dob = date('Y-m-d H:i:s');
        $this->customers_telephone = $customerDetails['customers_telephone'];
        $this->customers_landline = $customerDetails['customers_landline'];
        $this->customers_company = $customerDetails['customers_company'];
        //$this->customers_company_vat = $customerDetails['customers_company_vat'];
        $this->save();
    }

    /**
     * {@inheritdoc}
     * @return CustomersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomersQuery(get_called_class());
    }
    
    public function beforeDelete() {
        AddressBook::deleteAll(['customers_id' => $this->customers_id]);
        CustomersBasket::deleteAll(['customers_id' => $this->customers_id]);
        CustomersBasketAttributes::deleteAll(['customers_id' => $this->customers_id]);
        CustomersCreditHistory::deleteAll(['customers_id' => $this->customers_id]);
        CustomersEmails::deleteAll(['customers_id' => $this->customers_id]);
        CustomersInfo::deleteAll(['customers_info_id' => $this->customers_id]);
        CustomersPhones::deleteAll(['customers_id' => $this->customers_id]);
        CustomersWishlist::deleteAll(['customers_id' => $this->customers_id]);
        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ( $insert ) {
            foreach ($this->getTableSchema()->columns as $column) {
                /**
                 * @var $column ColumnSchema
                 */
                if (!$column->allowNull && ($this->getAttribute($column->name) === null || $column->dbTypecast($this->getAttribute($column->name))===null) ) {
                    $defValue = $column->defaultValue;
                    if ( $column->dbTypecast($defValue)===null ) {
                        $defTypeValue = [
                            'boolean' => 0,
                            'float' => 0.0,
                            'decimal' => 0.0,
                        ];
                        if ( stripos($column->type,'int')!==false ) {
                            $defValue = 0;
                        }else{
                            $defValue = isset($defTypeValue[$column->type])?$defTypeValue[$column->type]:'';
                        }
                    }
                    $this->setAttribute($column->name, $defValue);
                }
            }
        }

        return true;
    }
}

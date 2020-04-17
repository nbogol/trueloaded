<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\forms\contact;
 
use Yii;
use yii\base\Model;
use common\components\Customer;
use common\classes\ReCaptcha;
use frontend\design\Info;

class Contact extends \frontend\forms\registration\CustomerRegistration {
    
    public $customer_id;
    public $captcha = null;
    public $captha_enabled = false;    
    public $captcha_response;    
    public $captcha_widget;
    
    private $shortName = 'Contact';
    
    public function __construct($config = array()) {
        $this->customer_id = Yii::$app->user->getId();
        if (isset($config['captcha'])){
            $this->captha_enabled = (bool)$config['captcha'] && !$this->customer_id;
            if ($this->captha_enabled ){
                $this->captcha = new ReCaptcha();            
                $this->captha_enabled = $this->captcha->isEnabled();
            }
        }
        
        if ($this->captha_enabled && !$this->customer_id){
            $this->captcha_widget = \frontend\design\boxes\ReCaptchaWidget::widget();
        }        
    }
    
    public function formName(){
        return $this->shortName;
    }

    public function beforeValidate() {        
        $this->captcha_response = Yii::$app->request->post('g-recaptcha-response', null);
        return parent::beforeValidate();
    }

    public function rules() {        
        $_rules = [
            ['email_address', 'email', 'message' => ENTRY_EMAIL_ADDRESS_CHECK_ERROR],
            ['email_address', 'emailUnique'],
            [['email_address', 'content', 'name'], 'required', 'skipOnEmpty' => false],
            [['email_address', 'content', 'name'], 'string', 'skipOnEmpty' => false],
            [['dob', 'gdrp'], 'requiredOnRegister', 'skipOnEmpty' => false],            
            ['terms', 'requiredTrems', 'skipOnEmpty' => false],
            ['captcha_response', 'validateCaptcha', 'skipOnEmpty' => false]
        ];        
        return $_rules;
    }
    
    public function requiredTrems($attribute, $params){        
        if (!$this->$attribute){
            $this->addError($attribute, 'Please Read terms & conditions');
        }
    }
    
    public function validateCaptcha($attribute, $params){        
        if ($this->captha_enabled){
            if (!$this->captcha->checkVerification($this->captcha_response)){
                $this->addError($attribute, UNSUCCESSFULL_ROBOT_VERIFICATION);
            }
        }
    }

    public function scenarios() {        
        return [
            'default' => $this->collectFields(null)
        ];
    }
    
    public function collectFields($type) {
        
        $fields = ['email_address'];

        if (!$this->customer_id){
            if (in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])){
                $fields[] = 'dobTmp';
                $fields[] = 'dob';
                $fields[] = 'gdpr';
            }
            $fields[] = 'terms';
        }

        $fields[] = 'name';
        $fields[] = 'content';
        if ($this->captha_enabled){
            $fields[] = 'captcha_response';
        }

        return $fields;
            
    }
    
    public function preloadCustomersData($customer = null){
        if ($customer instanceof Customer){
            $this->email_address = $customer->customers_email_address;
            $this->name = $customer->customers_firstname;
        }
    }


    public function sendMessage(){
        $platform_config = Yii::$app->get('platform')->config();
        $to_name = $platform_config->const_value('STORE_OWNER');
        $to_email = $platform_config->contactUsEmail();
        if ( strpos($to_email,',')!==false ) {
            $to_name = '';
        }
        \common\helpers\Mail::send(
            $to_name, $to_email,
            EMAIL_SUBJECT, $this->content,
            $this->name, $this->email_address,
            array(), 'Reply-To: "' . $this->name . '" <' . $this->email_address . '>'
        );
        return true;
    }
    
}
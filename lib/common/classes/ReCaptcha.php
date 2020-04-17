<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

use Yii;

class ReCaptcha {

    private $public_key;
    private $secret_key;
    private $version;
    private $url = 'https://www.google.com/recaptcha/api/siteverify';
    private $enabled;

    public function __construct() {
        $this->enabled = true;
        
        $provider = \common\components\GoogleTools::instance()->getCaptchaProvider();
        
        $this->public_key = $provider->getPublickey();
        
        $this->secret_key = $provider->getPrivateKey();
        
        $this->version = $provider->getVersion();
        
        if (empty($this->public_key) || empty($this->secret_key))
            $this->enabled = false;
    }
    
    public function isEnabled(){
        return $this->enabled;
    }
    
    public function getPublicKey(){
        return $this->public_key;
    }
    
    public function getVersion(){
        return $this->version;
    }

    public function checkVerification($user_value) {
        if (empty($user_value) || !$this->enabled)
            return false;
        $ch = curl_init($this->url);
        if ($ch) {
            $data = array('secret' => $this->secret_key, 'response' => $user_value, 'remoteip' => $_SERVER['REMOTE_ADDR']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            if ($result === false) {
                return false;
            }
            curl_close($ch);
            $result = json_decode($result);
            return $result->success;
        }
        return false;
    }

}

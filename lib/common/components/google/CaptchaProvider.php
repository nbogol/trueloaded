<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\components\google;

use Yii;
use yii\helpers\Inflector;
use common\models\repositories\GoogleSettingsRepository;

class CaptchaProvider extends Providers implements GoogleProviderInterface {

    private $gsRepository;

    private $code = 'recaptcha';
    
    public function getName(){
        return 'reCaptcha Keys';
    }
    
    public function getCode(){
        return $this->code;
    }

    public function getDescription(){
        return 'That pair of keys is used to provide google reCaptcha verification. They can be obtained at <a href="https://www.google.com/recaptcha/intro/v3.html" target="_blank">Google reCAPTCHA Console</a>';
    }
    
    public function __construct(GoogleSettingsRepository $gsRepository){
        $this->gsRepository = $gsRepository;
    }
    
    public function getConfig(){
        static $setting = null;
        if (is_null($setting)){
            $setting = $this->getSetting();
        }
        if ($setting){
            $value = $setting->getValue();
            return $value ? $value : false;
        } else {
            $setting = false;
        }        
        return false;
    }
    
    public function getSetting(){
        return $this->gsRepository->getSetting($this->code, 0, 1);
    }
    
    private function prepareConfig($data){
        if (is_array($data) && isset($data['publicKey']) && isset($data['privateKey'])){
            try{
                return \GuzzleHttp\json_encode([
                    'publicKey' => (string)$data['publicKey'],
                    'privateKey' => (string)$data['privateKey'],
                    'version' => (string)$data['version'],
                ]);
            } catch (\Exception $ex) {
            }
        }
        return false;
    }
    
    public function updateSetting($setting, $data){
        if ($config = $this->prepareConfig($data)){
            return $this->gsRepository->updateSetting($setting, [ $this->gsRepository->getConfigHolder() => $config ]);
        }
        return false;
    }
    
    public function createSetting($data){
        if ($config = $this->prepareConfig($data)){
            return $this->gsRepository->createSetting($this->getCode(), $this->getName(), $config, 0, 1);
        }
        return false;
    }
    
    private function _decode($config){
        try{
            return \GuzzleHttp\json_decode($config, true);
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    public function getPublicKey(){
        $config = $this->getConfig();
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['publicKey'];
            }
        }
        return false;
    }
    
    public function getPrivateKey(){
        $config = $this->getConfig();
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['privateKey'];
            }
        }
        return false;
    }
    
    public function getVersion(){
        $config = $this->getConfig();
        if ($config){
            $values = $this->_decode($config);
            if ($values){
                return $values['version'] ?? 'v2';
            }
        }
        return false;
    }

    public function drawConfigTemplate(){
        return widgets\CaptchaWidget::widget(['publicKey' => $this->getPublicKey(), 'privateKey' => $this->getPrivateKey(), 'version' => $this->getVersion(), 'owner' => $this->getClassName(), 'description' => $this->getDescription()]);
    }
}

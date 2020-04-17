<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\modules\email;


class Transport {
    
    private function __construct() {
        
    }
    
    public static function getTransport(){
        
        if (EMAIL_TRANSPORT == 'smtp') {
            try{
                if (defined('SMTP_MAILER') && !empty(SMTP_MAILER)){
                    $className = "\\common\\modules\\email\\" . SMTP_MAILER;
                    if (class_exists($className)){
                        $mailer = new $className();
                        if ($mailer->ready()){
                            return $mailer;
                        }
                    }
                }
            } catch (\Exception $ex) {
                \Yii::warning($ex->getMessage());
            }
        }
        //by defualt return common Mailer
        return self::commonMailer();
    }
    
    private static function commonMailer(){
        return new \common\classes\email(array('X-Mailer: True Loaded Mailer'));
    }
}
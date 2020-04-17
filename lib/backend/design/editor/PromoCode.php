<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\editor;


use Yii;
use yii\base\Widget;


/* used to different codes incoming (promo/coupon/gift) */
class PromoCode extends Widget {
    
    public $manager;
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        $hasOt = false;
        
        $ot_data = $this->manager->getCreditModules();
        if (is_array($ot_data)){
            $hasOt = count(preg_grep ("/^ot_/", array_keys($ot_data))) > 0;
        }
        
        return $this->render('promo-code', [
           'hasOt'  => $hasOt
        ]);
    }
    
}

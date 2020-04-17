<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\orders;


use Yii;
use yii\base\Widget;

class Downloads extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        
        $dQuery = \common\models\OrdersProductsDownload::find()->where(['orders_id' => $this->order->order_id]);
        if ($dQuery->exists()){
            return $this->render('downloads', [
                'data' => $dQuery->all()
            ]);
        }
    }
}

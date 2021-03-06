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

class Paying extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        $ot_paid_exist = false;
        $paid = $this->manager->getTotalCollection()->get('ot_paid');
        if ($paid){
            $totals = \yii\helpers\ArrayHelper::map($this->order->totals, 'class', 'value_inc_tax');
            $ot_paid_exist = number_format($totals['ot_total'], 2) > number_format($totals['ot_paid'], 2);
        }
        if ($ot_paid_exist){
            return $this->render('paying', [
        
            ]);
        }
    }
}

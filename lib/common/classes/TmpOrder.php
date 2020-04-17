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

class TmpOrder extends \common\classes\extended\OrderAbstract {

    public $table_prefix = 'tmp_';

    public function createOrder() {
        global $cart;
        
        $tModelQuery = $this->getARModel()->where(['orders_id' => $this->order_id]);
        if (!$tModelQuery->exists()){
            return false;
        }
        $tModel = $tModelQuery->one();
        if ($tModel->child_id > 0){
            return false;
        }
        
        if (!is_object($cart)){
            $cart = new \common\classes\shopping_cart;
        }
        
        if (!$this->manager->hasCart()){
            $this->manager->loadCart($cart);
        }        
        
        if ($this->manager->isInstance()){
            $order = $this->manager->getOrderInstance();
        } else {
            $order = $this->manager->createOrderInstance('\common\classes\Order');
        }
        
        $order->info = $this->info;
        $order->totals = $this->totals;
        $order->products = $this->products;
        $order->customer = $this->customer;
        $order->delivery = $this->delivery;
        $order->billing = $this->billing;
        $order->content_type = $this->content_type;
        $order->tax_address = $this->tax_address;
        
        $order->update_piad_information();
        if ($order->content_type != 'virtual'){
            $order->withDelivery = true;
        }
        
        $insert_id = $order->save_order();
        $order->save_details();
        $order->save_products();
        
        $tModel->child_id = $insert_id;
        $tModel->save(false);
        
        $this->setParent($insert_id);
        
        return $insert_id;
    }
    
    public static function getARModel($new = false){
        if($new){
            return parent::getARModelNew(new \common\models\TmpOrders());
        } else {
            return \common\models\TmpOrders::find();
        }
    }
    
    public function getProductsARModel(){
        return \common\models\TmpOrdersProducts::find();
    }
    
    public function getStatusHistoryARModel(){
        
    }
    
    public function getHistoryARModel(){
        return \common\models\TmpOrdersHistory::find();
    }
    
}

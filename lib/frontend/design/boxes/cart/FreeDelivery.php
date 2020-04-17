<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class FreeDelivery extends Widget {

    public $file;
    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public function run() {
        global $cart;
        $manager = $this->params['manager'];
        $currencies = \Yii::$container->get('currencies');

        $cart_total = $cart->show_total();
        $free_shipping_over = $manager->getShippingCollection()->getFreeShippingOver();
        if ($cart_total > 0 && $free_shipping_over > 0 && $cart_total < $free_shipping_over) {
           return IncludeTpl::widget(['file' => 'boxes/cart/free-delivery.tpl', 'params' => ['amount_left' => $currencies->format($free_shipping_over - $cart_total)]]);
        }
    }

}

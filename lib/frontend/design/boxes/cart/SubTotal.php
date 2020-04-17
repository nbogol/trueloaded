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

class SubTotal extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $cart;
    $currencies = \Yii::$container->get('currencies');
    $gift_wrap = '';
    if ( $cart->have_gift_wrap_products() ) {
      $cart_gift_wrap_amount = $cart->get_gift_wrap_amount();
      $gift_wrap = $currencies->display_price($cart_gift_wrap_amount, \common\helpers\Tax::get_tax_rate(defined('MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS')?MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS:0));
    }
    
    return IncludeTpl::widget(['file' => 'boxes/cart/sub-total.tpl', 'params' => [
      'sub_total' => $currencies->format($cart->show_total()),
      'gift_wrap' => $gift_wrap,
    ]]);
  }
}
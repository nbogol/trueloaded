<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes;

use common\extensions\MultiCart\MultiCart;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class Cart extends Widget {

	public $params;
	public $settings;

	public function init() {
		parent::init();
	}

	public function run() {
		if( GROUPS_DISABLE_CHECKOUT ) {
			return '';
		}

		global $cart;
		$currencies = \Yii::$container->get('currencies');
		if( ! is_object( $cart ) || ! is_object( $currencies ) ) {
			return '';
		}
        
        if( $ext = \common\helpers\Acl::checkExtension( 'MultiCart', 'allowed' ) ) {
            if( MultiCart::allowed() && MultiCart::getCartsAmount(false) > 0) {
                return MultiCart::cartsBlock([
                    'settings' => $this->settings,
                    'id' => $this->id
                ]);
            }
        }

		//$products = $cart->get_products();
        
    $cartDecorator = new \frontend\design\CartDecorator($cart);
    $products = $cartDecorator->getProducts();

		foreach( $products as $key => $item ) {
			$products[ $key ]['price'] = $products[ $key ]['final_price'];
/*			$products[ $key ]['price'] = $currencies->display_price( $item['final_price'], \common\helpers\Tax::get_tax_rate( $item['tax_class_id'] ), $item['quantity'] );
			$products[ $key ]['image'] = Images::getImageUrl( $item['id'], 'Small' );
			$products[ $key ]['link']  = tep_href_link( 'catalog/product', 'products_id=' . $item['id'] . '&platform_id=' . $item['platform_id']);*/
		}

		if (!Yii::$app->user->isGuest){
		  $checkout_link = tep_href_link('checkout', '', 'SSL');
		} else {
		  $checkout_link = tep_href_link('checkout/login', '', 'SSL');
		}
		
		$params = [
			'total'          => $currencies->format( $cart->show_total() ),
			'count_contents' => $cart->count_contents(),
			'settings'       => $this->settings,
			'products'       => $products,
			'is_multi_cart'  => false,
			'currencies'     => $currencies,
			'checkout_link'  => $checkout_link
		];

		return IncludeTpl::widget( [
			'file'   => 'boxes/cart.tpl',
			'params' => $params
		] );
	}
}
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
use frontend\design\CartDecorator;

class Products extends Widget
{

    public $type;
    public $settings;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $cart;
        $groupId = (int) \Yii::$app->storage->get('customer_groups_id');
        if ($this->params['sender'] == 'worker') {
            if (\frontend\design\Info::widgetSettings('cart\Products', 'editable_products', 'checkout')){
                $this->settings[0]['editable_products'] = true;
            }
        }
        
        if ((Yii::$app->controller->id == 'checkout' && !$this->settings[0]['editable_products'])
            || Yii::$app->controller->id == 'sample-checkout'
            || Yii::$app->controller->id == 'quote-checkout' ) {
            $this->type = 2;
        }

        $cartDecorator = new CartDecorator($cart);
        
        $multiCart = ['enabled' => false,];
        if($ext = \common\helpers\Acl::checkExtension('MultiCart', 'allowed')){
            if ($ext::allowed()){
                $multiCart['enabled'] = !$ext::isEmpty();
            }
        }

        if($ext = \common\helpers\Acl::checkExtension('MultiCart', 'allowed')){
            if ($ext::allowed()){
                $multiCart['script'] = $ext::actionScript();
            }
        }

        \frontend\design\Info::addBlockToWidgetsList('cart-listing');
        
        $bounded = false;
        if ($cartDecorator->bound_quantity_ordered){
            $bounded = true;
            $boundMessage = TEXT_INSTOCK_BOUND_MESSAGE;
        } else if ($cart->hasBlockedProducts()){
            $bounded = true;
            $boundMessage = TEXT_INFO_CART_HAS_LIMITED_PRODUCTS;
        }
        
        if ($cart->count_contents() > 0) {
            return IncludeTpl::widget(['file' => 'boxes/cart/products' . ($this->type ? '-' . $this->type : '') . '.tpl', 'params' => [
              'products' => $cartDecorator->getProducts(),
              'allow_checkout' => !($cartDecorator->oos_product_incart || $bounded),
              'oos_product_incart' => $cartDecorator->oos_product_incart,
              'bound_quantity_ordered' => $bounded,
              'bonus_points' => (is_object($this->params['manager'])? $this->params['manager']->getBonusesDetails(): null),
              'promoMessage' => \common\models\promotions\PromotionService::getMessage(),
              'boundMessage' => $boundMessage,
              'popupMode' => ($_GET['popup'] == 1),
              'multiCart' => $multiCart,
              'settings' => $this->settings,
              'groupId' => $groupId,
            ]]);
        } else {
            return '<div class="empty">' . CART_EMPTY . '</div>';
        }
    }
}

<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;
use common\helpers\Date as DateHelper;

class OrderReorderButton extends Widget
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
        $order_id = (int)Yii::$app->request->get('order_id');

        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtension('UpdateAndPay', 'payLink')) {
            $pay_link = $ext::payLink($order_id);
        }
        if ($pay_link) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/account/order-reorder-button.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'reorder_link' => Yii::$app->urlManager->createUrl(['checkout/reorder', 'order_id' => $order_id]),
            'reorder_confirm' => ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : ''),
        ]]);
    }
}
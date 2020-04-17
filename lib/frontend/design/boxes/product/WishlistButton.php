<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class WishlistButton extends Widget
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
        global $wish_list;
        $params = Yii::$app->request->get();

        if ($params['products_id'] && !GROUPS_DISABLE_CHECKOUT && !Yii::$app->user->isGuest) {

            return IncludeTpl::widget(['file' => 'boxes/product/wishlist-button.tpl', 'params' => [
                'id' => $this->id,
                'in_wish_list' => $wish_list->in_wish_list($params['products_id'])
            ]]);
        } else {
            return '';
        }
    }
}
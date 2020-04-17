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
use common\helpers\Tax;
use common\helpers\Product;

class KlarnaFinance extends Widget {

    public $file;
    public $params;
    public $settings;
    public $frameUrl;

    public function init() {
        parent::init();
        $this->frameUrl = 'https://www.finance-calculator.co.uk/fcalculator.php?imegaid=1360&orderamount=';
    }

    public function run() {
        $params = Yii::$app->request->get();
        if ($params['products_id']) {
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
            $currencies = \Yii::$container->get('currencies');
            $products = Yii::$container->get('products');
            $product = $products->getProduct($params['products_id']);
            if ($product) {
                if ($ext = \common\helpers\Acl::checkExtension('PackUnits', 'checkPackPrice')) {
                    $return_price = $ext::checkPackPrice($params['products_id']);
                } else {
                    $return_price = true;
                }
                if (!$product->checkAttachedDetails($products::TYPE_STOCK)) {
                    $product_qty = Product::get_products_stock($params['products_id']);
                    $stock_info = \common\classes\StockIndication::product_info(array(
                                'products_id' => $params['products_id'],
                                'products_quantity' => $product_qty,
                    ));
                    $product = $products->attachDetails($params['products_id'], [$products::TYPE_STOCK => $stock_info])->getProduct($params['products_id']);
                } else {
                    $stock_info = $product[$products::TYPE_STOCK];
                }

                /**
                 * $stock_indicator_public['display_price_options']
                 * 0 - display
                 * 1 - hide
                 * 2 - hide if zero
                 */
                if (($stock_info['flags']['request_for_quote'] && SHOW_PRICE_FOR_QUOTE_PRODUCT != 'True' /* && $stock_info['flags']['display_price_options'] != 0 */) ||
                        ($stock_info['flags']['display_price_options'] == 1) ||
                        (abs($product['products_price']) < 0.01 && $stock_info['flags']['display_price_options'] == 2)) {
                    $return_price = false;
                }

                if (!$return_price) {
                    return '';
                }
                $actualPrice = 0;
                if ($product['is_bundle']) {
                    $details = \common\helpers\Bundles::getDetails(['products_id' => $product['products_id']]);
                    $actualPrice = $details['actual_bundle_price_clear'];
                } else {
                    if (isset($product['special_price']) && $product['special_price'] !== false) {
                        $actualPrice = $currencies->display_price($product['special_price'], $product['tax_rate'], 1);
                    } else {
                        $actualPrice = $currencies->display_price($product['products_price'], $product['tax_rate'], 1);
                    }
                }

                if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'changeShowPrice')) {
                    if ($ext::changeShowPrice($customer_groups_id)) {
                        return;
                    }
                }

                return IncludeTpl::widget(['file' => 'boxes/product/klarna.tpl', 'params' => [
                                'url' => $this->frameUrl,
                                'price' => strip_tags($actualPrice)
                ]]);
            }
        }
    }

}

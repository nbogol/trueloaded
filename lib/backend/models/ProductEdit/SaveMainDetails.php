<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\ProductEdit;

use yii;
use common\models\Products;

class SaveMainDetails
{

    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function prepareSave()
    {
        $sql_data_array = [];

        if (Yii::$app->request->post('without_inventory', false)!==false){
            $sql_data_array['without_inventory'] = (int) Yii::$app->request->post('without_inventory',0);
        }

        if (Yii::$app->request->post('listing_switch_present')){
            $sql_data_array['is_listing_product'] = (int) Yii::$app->request->post('is_listing_product',0);
        }
        $sql_data_array['products_status'] = (int) Yii::$app->request->post('products_status');
        //$sql_data_array['products_status_bundle'] = (int) Yii::$app->request->post('products_status_bundle');
        if ($ext = \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'onProductSave')) {
            $_sql_data = $ext::onProductSave();
            if ( is_array($_sql_data) ) $sql_data_array = array_merge($sql_data_array,$_sql_data);
        }
        //TODO: ???? separate prepare - ACL collision
        // Moved to SeoRedirectsNamed
        // $sql_data_array['products_old_seo_page_name'] = tep_db_prepare_input($_POST['pDescription'][\common\classes\platform::defaultId()]['products_old_seo_page_name']);

        $sql_data_array['manufacturers_id'] = (int) Yii::$app->request->post('manufacturers_id');
        $brandName = tep_db_prepare_input(Yii::$app->request->post('brand'));
        if (empty($brandName)) {
            $sql_data_array['manufacturers_id'] = 0;
        } else {
            $brands_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_name = '" . tep_db_input($brandName) . "'");
            $brands = tep_db_fetch_array($brands_query);
            if (isset($brands['manufacturers_id'])) {
                $sql_data_array['manufacturers_id'] = (int) $brands['manufacturers_id'];
            }
        }
        $sql_data_array['stock_indication_id'] = (int)Yii::$app->request->post('stock_indication_id', 0);

        $sql_data_array['stock_delivery_terms_id'] = (int)Yii::$app->request->post('stock_delivery_terms_id',0);

        $sql_data_array['products_model'] = Yii::$app->request->post('products_model');
        $sql_data_array['products_ean'] = Yii::$app->request->post('products_ean');
        $sql_data_array['products_upc'] = Yii::$app->request->post('products_upc');
        $sql_data_array['products_asin'] = Yii::$app->request->post('products_asin');
        $sql_data_array['products_isbn'] = Yii::$app->request->post('products_isbn');

        $sql_data_array['source'] = Yii::$app->request->post('source');

        $sql_data_array['subscription'] = (int) Yii::$app->request->post('subscription');
        $sql_data_array['subscription_code'] = Yii::$app->request->post('subscription_code');

        if ($extClass = \common\helpers\Acl::checkExtensionAllowed('UploadCustomerId', 'allowed')) {
            $extClass::productSave($this->product);
        }

        // save product behavior buttons
        $sql_data_array['request_quote'] = Yii::$app->request->post('request_quote', 0);
        $sql_data_array['request_quote_out_stock'] = Yii::$app->request->post('request_quote_out_stock', 0);
        $sql_data_array['ask_sample'] = Yii::$app->request->post('ask_sample', 0);
        $sql_data_array['allow_backorder'] = Yii::$app->request->post('allow_backorder', 0);
        $sql_data_array['cart_button'] = Yii::$app->request->post('cart_button');

        $sql_data_array['reorder_auto'] = Yii::$app->request->post('reorder_auto', 0);

        $sql_data_array['manual_stock_unlimited'] = (int)Yii::$app->request->post('manual_stock_unlimited', 0);

        if ($ext = \common\helpers\Acl::checkExtension('MinimumOrderQty', 'saveProduct')) {
            $sql_data_array = array_replace($sql_data_array, $ext::saveProduct());
        }
        if ($ext = \common\helpers\Acl::checkExtension('MaxOrderQty', 'saveProduct')) {
            $sql_data_array = array_replace($sql_data_array, $ext::saveProduct());
        }
        if ($ext = \common\helpers\Acl::checkExtension('OrderQuantityStep', 'saveProduct')) {
            $sql_data_array = array_replace($sql_data_array, $ext::saveProduct());
        }

        $products_date_available = Yii::$app->request->post('products_date_available');
        if (!empty($products_date_available)) {
            $sql_data_array['products_date_available'] = \common\helpers\Date::prepareInputDate($products_date_available);
        } else {
            $sql_data_array['products_date_available'] = '';
        }

        if ($ext = \common\helpers\Acl::checkExtension('Rma', 'setProductReturnTime')) {
            $ext::setProductReturnTime($this->product->products_id, Yii::$app->request->post('productReturnTime'));
        }

        $sql_data_array['products_pctemplates_id'] = Yii::$app->request->post('products_pctemplates_id');

        // product designer template
        $sql_data_array['product_designer_template_id'] = Yii::$app->request->post('product_designer_template_id');

        $stock_control = (int)Yii::$app->request->post('stock_control');
        if (($sql_data_array['manual_stock_unlimited'] > 0) OR ((int)Yii::$app->request->post('is_bundle', 0) > 0)) {
            $stock_control = 0;
        }
        $sql_data_array['stock_control'] = $stock_control;
        //--- Stock control Start
        switch ($stock_control) {
            case 0:
                break;
            case 1:
                $platformStock = \common\models\Platforms::find()->where(['status' => 1])->orderBy("sort_order")->all();
                foreach($platformStock as $platform){
                    $current_quantity = (int) Yii::$app->request->post('platform_to_qty_' . (int)$platform->platform_id);
                    $object = \common\models\PlatformStockControl::findOne(['products_id' => $products_id, 'platform_id' => $platform->platform_id]);
                    if (is_object($object)) {
                        if ($current_quantity != $object->current_quantity) {
                            $object->current_quantity = $current_quantity;
                            $object->manual_quantity = $current_quantity;
                            $object->save();
                        }
                    } else {
                        $object = new \common\models\PlatformStockControl();
                        $object->products_id = (int) $products_id;
                        $object->platform_id = (int) $platform->platform_id;
                        $object->current_quantity = $current_quantity;
                        $object->manual_quantity = $current_quantity;
                        $object->save();
                    }
                }
                break;
            case 2:
                \common\models\WarehouseStockControl::deleteAll(['products_id' => $products_id]);
                $platformStock = \common\models\Platforms::find()->where(['status' => 1])->orderBy("sort_order")->all();
                foreach($platformStock as $platform){
                    $object = new \common\models\WarehouseStockControl();
                    $object->products_id = (int) $products_id;
                    $object->platform_id = (int) $platform->platform_id;
                    $object->warehouse_id = (int) Yii::$app->request->post('platform_to_warehouse_' . (int)$platform->platform_id);
                    $object->save();
                }
                break;
            default:
                break;
        }
        //--- Stock control End
        $sql_data_array['stock_reorder_level'] = (int)Yii::$app->request->post('stock_reorder_level', -1);
        $sql_data_array['stock_reorder_quantity'] = (int)Yii::$app->request->post('stock_reorder_quantity', -1);


        if ($ext = \common\helpers\Acl::checkExtension('TypicalOperatingTemp', 'saveProduct')) {
            $ext::saveProduct($this->product);
        }

        $this->product->setAttributes($sql_data_array, false);
    }

}
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
use common\helpers\OrderProduct;
use common\classes\Images;

class Product extends Widget {

    public $product;
    public $manager;
    public $opsArray;
    public $handlers_array = [];
    public $iter;
    public $order;
    public $currency;
    public $currency_value;
    public $warehouseList;
    public $locationBlockList;

    public function init(){
        parent::init();
        if (!$this->currency)
            $this->currency = $this->order->info['currency'];
        if (!$this->currency_value)
            $this->currency_value = $this->order->info['currency_value'];
    }

    public function run(){
        global $languages_id;

        $isTemporary = false;
        foreach (\common\helpers\OrderProduct::getAllocatedArray($this->product['orders_products_id'], true) as $opaRecord) {
            if ((int)$opaRecord['is_temporary'] > 0 AND (int)$opaRecord['allocate_received'] > (int)$opaRecord['allocate_dispatched']) {
                $isTemporary = true;
                break;
            }
        }
        unset($opaRecord);

        $opsmArray = [];
        if (isset($this->opsArray[$this->product['status']])) {
            $opsmArray = $this->opsArray[$this->product['status']]->getMatrixArray();
        }
        if (defined('EXTENSION_HANDLERS_ENABLED') && EXTENSION_HANDLERS_ENABLED == 'True') {
            $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM handlers_products WHERE products_id='" . (int) $this->product['id'] . "' AND handlers_id IN (" . implode(", ", $this->handlers_array) . ") "));
            if ($_check['c'] == 0) {
                return;
            }
        }

        $location = '';
        foreach (\common\helpers\OrderProduct::getAllocatedArray($this->product['orders_products_id']) as $orderProductAllocateRecord) {
            $locationName = trim(\common\helpers\Warehouses::getLocationPath($orderProductAllocateRecord['location_id'], $orderProductAllocateRecord['warehouse_id'], $this->locationBlockList));
            $location .= '<div>'
                . (isset($this->warehouseList[$orderProductAllocateRecord['warehouse_id']]) ? $this->warehouseList[$orderProductAllocateRecord['warehouse_id']] : 'N/A')
                . ', ' . ($locationName != '' ? $locationName : 'N/A') . ': '
                . $orderProductAllocateRecord['allocate_received']
                . '</div>';
            unset($locationName);
        }
        unset($orderProductAllocateRecord);

        $gv_state_label = '';
        if ($this->product['gv_state'] != 'none') {
            $_inner_gv_state_label = (defined('TEXT_ORDERED_GV_STATE_' . strtoupper($this->product['gv_state'])) ? constant('TEXT_ORDERED_GV_STATE_' . strtoupper($this->product['gv_state'])) : $this->product['gv_state']);
            if ($this->product['gv_state'] == 'pending' || $this->product['gv_state'] == 'canceled') {
                $_inner_gv_state_label = '<a class="js_gv_state_popup" href="' . Yii::$app->urlManager->createUrl(['orders/gv-change-state', 'opID' => $this->product['orders_products_id']]) . '">' . $_inner_gv_state_label . '</a>';
            }
            $gv_state_label = '<span class="ordered_gv_state ordered_gv_state-' . $this->product['gv_state'] . '">' . $_inner_gv_state_label . '</span>';
        }

        $asset = null;
        if ($this->product['promo_id']){
            $asset = \common\models\promotions\PromotionService::getAsset($this->product['promo_id'], $this->product['id']);
        }

        $opsArray = array();
        foreach (\common\models\OrdersProductsStatus::findAll(['language_id' => (int)$languages_id]) as $opsRecord) {
            $opsArray[$opsRecord->orders_products_status_id] = $opsRecord;
        }
        unset($opsRecord);

        $suppliersPricesArray = array();
        foreach (\common\models\OrdersProductsAllocate::findAll(['orders_products_id' => (int)$this->product['orders_products_id']]) as $opaRecord) {
            if ($opaRecord->suppliers_price > 0) {
                $suppliersPricesArray[$opaRecord->suppliers_id] = $opaRecord;
            }
        }

        return $this->render('product',[
            'manager' => $this->manager,
            'opsmArray' => $opsmArray,
            'product' => $this->product,
            'image' => Images::getImage($this->product['id'], 'Small'),
            'image_url' => Images::getImageUrl($this->product['id'], 'Large'),
            'iter' => $this->iter,
            'currency' => $this->currency,
            'currency_value' => $this->currency_value,
            'location' => $location,
            'gv_state_label' => $gv_state_label,
            'asset' => $asset,
            'color' => (isset($this->opsArray[$this->product['status']]) ? $this->opsArray[$this->product['status']]->getColour() : '#000000'),
            'status' => (isset($this->opsArray[$this->product['status']]) ? $this->opsArray[$this->product['status']]->orders_products_status_name : ''),
            'isTemporary' => $isTemporary,
            'headers' => [
                'cancel'     => $opsArray[OrderProduct::OPS_CANCELLED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_CANCELLED,
                'ordered'    => $opsArray[OrderProduct::OPS_STOCK_ORDERED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_STOCK_ORDERED,
                'received'   => $opsArray[OrderProduct::OPS_RECEIVED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_RECEIVED,
                'dispatched' => $opsArray[OrderProduct::OPS_DISPATCHED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_DISPATCHED,
                'delivered'  => $opsArray[OrderProduct::OPS_DELIVERED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_DELIVERED
            ],
            'suppliersPricesArray' => $suppliersPricesArray,
        ]);
    }
}

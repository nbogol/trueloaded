<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use backend\models\ProductNameDecorator;
use backend\models\ProductEdit\ViewPriceData;
use backend\models\ProductEdit\PostArrayHelper;
use Yii;
use common\helpers\Html;

class SpecialsController extends Sceleton {

  public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_CATALOG_SPECIALS'];
  private static $dateOptions = ['active_on', 'start_between', 'end_between'];
  private static $by = [
    [
      'name' => 'TEXT_ANY',
      'value' => '',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_ID',
      'value' => 'specials.products_id',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_MODEL',
      'value' => 'products_model',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_NAME',
      'value' => 'products_name',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_UPC',
      'value' => 'products_upc',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_EAN',
      'value' => 'products_ean',
      'selected' => '',
    ],
    [
      'name' => 'PRODUCTS_ISBN',
      'value' => 'products_isbn',
      'selected' => '',
    ],
  ];
  private static $filterFields = ['search' => '', 'date' => '',
    'inactive' => 'intval',
    'pfrom' => 'floatval', 'pto' => 'floatval',
    'dfrom' => ['list' => ['\common\helpers\Date', 'prepareInputDate']],
    'dto' => ['list' => ['\common\helpers\Date', 'prepareInputDate']]
  ];

  public function actionIndex() {

    $this->selectedMenu = array('marketing', 'specials');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('specials/index'), 'title' => HEADING_TITLE);
    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['specials/specialedit']) . '" class="create_item" >' . IMAGE_INSERT . '</a>';
    $this->view->headingTitle = HEADING_TITLE;
    $this->view->specialsTable = array(
      array(
        'title' => Html::checkbox('select_all', false, ['id' => 'select_all']),
        'not_important' => 2
      ),
      array(
        'title' => DATE_CREATED,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS_PRICE_OLD,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_PRODUCTS_PRICE,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_START_DATE,
        'not_important' => 0
      ),
      array(
        'title' => TEXT_END_DATE,
        'not_important' => 0
      ),
      array(
        'title' => TABLE_HEADING_STATUS,
        'not_important' => 1
      ),
    );
    $this->view->sortColumns = '1,2,3,4,5,6,7';

    $this->view->filters = new \stdClass();
    $this->view->filters->row = (int) Yii::$app->request->get('row', 0);
    $gets = Yii::$app->request->get();

    $by = self::$by;
    foreach ($by as $key => $value) {
      $by[$key]['name'] = defined($by[$key]['name']) ? constant($by[$key]['name']) : strtolower(str_replace('_', ' ', $by[$key]['name']));
      if (isset($gets['by']) && $value['value'] == $gets['by']) {
        $by[$key]['selected'] = 'selected';
      }
    }
    $this->view->filters->by = $by;
    foreach (self::$dateOptions as $opt) {
      $this->view->filters->dateOptions[$opt] = defined('TEXT_' . strtoupper($opt)) ? constant('TEXT_' . strtoupper($opt)) : strtoupper($opt);
    }

    foreach (self::$filterFields as $v => $f) {
      if (!empty($gets[$v])) {
        if (is_callable($f)) {
          $this->view->filters->{$v} = call_user_func($f, $gets[$v]);
        } elseif (is_array($f) && !empty($f['filter']) && is_callable($f['filter'])) {
          $this->view->filters->{$v} = call_user_func($f['filter'], $gets[$v]);
        } else {
          $this->view->filters->{$v} = $gets[$v];
        }
      } else {
        $this->view->filters->{$v} = '';
      }
    }
    return $this->render('index');
  }

  public function actionList() {
    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);

    $currencies = Yii::$container->get('currencies');

    $responseList = array();
    if ($length == -1) {
      $length = 10000;
    }
    $query_numrows = 0;

    $formFilter = Yii::$app->request->get('filter');
    $gets = [];
    parse_str($formFilter, $gets);

    if (isset($gets['date']) && in_array($gets['date'], self::$dateOptions)) {
      $date = $gets['date'];
    } else {
      $date = 'active_on';
    }
    if (isset($gets['by']) && in_array($gets['by'], \yii\helpers\ArrayHelper::getColumn(self::$by, 'value'))) {
      $by = $gets['by'];
    } else {
      $by = '';
    }

    $listQuery = \common\models\Specials::find()->joinWith(['backendProductDescription'])->select(\common\models\Specials::tableName() . '.*');
    $inactive = false;

    foreach (self::$filterFields as $v => $f) {
      if (!empty($gets[$v])) {
        if (is_callable($f)) {
          if (is_array($gets[$v])) {
            foreach ($gets[$v] as $k => $vv) {
              $gets[$v][$k] = call_user_func($f, $vv);
            }
            $val = $gets[$v];
          } else {
            $val = call_user_func($f, $gets[$v]);
          }
        } elseif (is_array($f) && !empty($f['list']) && is_callable($f['list'])) {
          $val = call_user_func($f['list'], $gets[$v]);
        } else {
          $val = $gets[$v];
        }

        switch ($v) {
          case 'inactive':
            $inactive = true;
            break;
          case 'pfrom':
            $listQuery->joinWith('prices');
            $listQuery->andWhere(['>=', 'specials_new_products_price', $val]);
            $listQuery->distinct();
            break;
          case 'pto':
            $listQuery->joinWith('prices');
            $listQuery->andWhere(['<=', 'specials_new_products_price', $val]);
            $listQuery->andWhere(['>', 'specials_new_products_price', -0.0001]);
            $listQuery->distinct();
            break;
          case 'dfrom':
            if (in_array($date, ['start_between'])) {
              $listQuery->startAfter($val);
            } elseif (in_array($date, ['active_on'])) {
              $listQuery->endAfter($val);
            } else { //end between
              $listQuery->endAfter($val);
            }
            break;
          case 'dto':
            if (in_array($date, ['start_between'])) {
              $listQuery->startBefore($val);
            } elseif (in_array($date, ['active_on'])) {
              $listQuery->startBefore($val);
            } else { //end between
              $listQuery->endBefore($val);
            }
            break;
          case 'search':
            if ($by == '') { //all
              $tmp = [];
              foreach (\yii\helpers\ArrayHelper::getColumn(self::$by, 'value') as $field) {
                if (!empty($field) && is_string($field)) {
                  $tmp[] = ['like', $field, $val];
                }
              }
              if (!empty($tmp)) {
                $listQuery->andWhere(array_merge(['or'], $tmp));
              }
            } else {
              $listQuery->andWhere(['like', $by, $val]);
            }
            break;
        }
      }
    }
    if (!$inactive) {
      $listQuery->andWhere('status=1');
    }

    $gets = Yii::$app->request->get();
    if (!empty($gets['search']['value'])) {
      $val = $gets['search']['value'];
      $tmp = [];
      foreach (\yii\helpers\ArrayHelper::getColumn(self::$by, 'value') as $field) {
        if (!empty($field) && is_string($field)) {
          $tmp[] = ['like', $field, $val];
        }
      }
      if (!empty($tmp)) {
        $listQuery->andWhere(array_merge(['or'], $tmp));
      }
    }

    if (!empty($gets['order']) && is_array($gets['order'])) {
      foreach ($gets['order'] as $sort) {
          $dir = 'asc';
          if (!empty($sort['dir']) && $sort['dir'] == 'desc') {
            $dir = 'desc';
          }
          switch ($sort['column']) {
            case 1:
              $listQuery->addOrderBy(" specials_date_added " . $dir);
              break;
            case 2:
              $listQuery->addOrderBy(" products_name " . $dir);
              break;
            case 3:
              $listQuery->addOrderBy(" products_price " . $dir);
              break;
            case 4:
              $listQuery->addOrderBy(" specials_new_products_price " . $dir);
              break;
            case 5:
              $listQuery->addOrderBy(" start_date " . $dir);
              break;
            case 6:
              $listQuery->addOrderBy(" expires_date " . $dir);
              break;
            case 7:
              $listQuery->addOrderBy(" status " . $dir);
              break;
            default:
              $listQuery->addOrderBy(" specials_date_added desc ");
              break;
          }
      }
      $listQuery->addOrderBy(" products_name ");
    } else {
      $listQuery->addOrderBy(" specials_date_added desc ");
    }

    $responseList = array();
    $current_page_number = ( $start / $length ) + 1;
    $query_numrows = $listQuery->count();
    if ($query_numrows < $start) {
      $start = 0;
    }
//echo $listQuery->createCommand()->rawSql; die;
    $listQuery->offset($start)->limit($length);
    $listQuery->addSelect('products_name, products_price');

    $specials = $listQuery->asArray()->all();


    foreach ($specials as $special) {
      $row = [];
      $row[] = Html::checkbox('bulkProcess[]', false, ['value' => $special['specials_id']])
          . Html::hiddenInput('coupons_' . $special['specials_id'], $special['specials_id'], ['class' => "cell_identify"])
          . (!$special['status'] ? Html::hiddenInput('coupons_st' . $special['specials_id'], 'dis_module', ['class' => "tr-status-class"]) : '')
      ;

      if ($special['specials_date_added'] > '1980-01-01') {
        $row[] = \common\helpers\Date::date_short($special['specials_date_added']);
      } else {
        $row[] = '';
      }
      $name = $special['backendProductDescription']['products_name'] ?? '';
      foreach (['products_model', 'products_upc', 'products_ean', 'products_isbn'] as $value) {
        if (!empty($special['product'][$value])) {
          $name .= '<br>' . $special['product'][$value];
        }
      }

      $row[] = $name;
      $row[] = $currencies->format($special['products_price']);
      $row[] = $currencies->format($special['specials_new_products_price']);

      if ($special['start_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::datetime_short(($special['start_date']));
      } else {
        $row[] = '';
      }
      if ($special['expires_date'] > '1980-01-01') {
        $row[] = \common\helpers\Date::datetime_short($special['expires_date']);
      } else {
        $row[] = '';
      }

      $row[] = Html::checkbox('specials_status' . $special['specials_id'], $special['status'], ['value' => $special['specials_id'], 'class' => ($length < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' )]);

      $responseList[] = $row;
    }

    $response = array(
      'draw' => $draw,
      'recordsTotal' => $query_numrows,
      'recordsFiltered' => $query_numrows,
      'data' => $responseList
    );
    echo json_encode($response);
  }


  public function actionItempreedit() {
    \common\helpers\Translation::init('admin/specials');
    /** @var \common\classes\Currencies $currencies */
    $currencies = Yii::$container->get('currencies');
    $groups = \common\helpers\Group::get_customer_groups();
    $this->layout = false;
    $item_id = (int) Yii::$app->request->post('item_id', 0);
    $sInfo = \common\models\Specials::find()->andWhere(['specials_id' => $item_id])->with(['prices', 'backendProductDescription'])->one();
    if (!empty($sInfo->specials_id)) {
    ?>
    <div class="or_box_head or_box_head_no_margin"><?php echo $sInfo->backendProductDescription->products_name; ?></div>
    <div class="row_or_wrapp">
    <?php
    echo '<div class="row_or"><div>' . TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($sInfo->specials_date_added) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_INFO_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($sInfo->specials_last_modified) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_INFO_STATUS_CHANGE . '</div><div class="date-time-smaller">' . \common\helpers\Date::datetime_short($sInfo->date_status_change) . '</div></div><hr>';
    
    if (is_array($sInfo->prices)) {
      $prices = [];
      $defCurrencyId = \common\helpers\Currencies::getCurrencyId(DEFAULT_CURRENCY);
      foreach ($sInfo->prices as $priceInfo) {

        if ($priceInfo->specials_new_products_price>0 || $priceInfo->specials_new_products_price==-2) {
          if ($priceInfo->currencies_id == $defCurrencyId) {
            $cCode = DEFAULT_CURRENCY;
          } else {
            $cCode = \common\helpers\Currencies::getCurrencyCode($priceInfo->currencies_id);
          }
          $price = false;
          if ($priceInfo->specials_new_products_price==-2) {

            if (is_array($groups[$priceInfo->groups_id]) && $prices[DEFAULT_CURRENCY][0]['value']>0 && is_numeric($groups[$priceInfo->groups_id]['groups_discount'])) {
              if ($groups[$priceInfo->groups_id]['apply_groups_discount_to_specials']) {
                $price = $prices[DEFAULT_CURRENCY][0]['value'] * (1-$groups[$priceInfo->groups_id]['groups_discount']/100);
              } else {
                $price = $prices[DEFAULT_CURRENCY][0]['value'];
              }
            }
          } else {
            $price = $priceInfo->specials_new_products_price;
          }
          if ($price) {
            $prices[DEFAULT_CURRENCY][$priceInfo->groups_id] = ['value' => $price, 'text' => $currencies->format($price, 1, $cCode)];
          }
        }
      }
      if (!empty($prices)) {
        echo '<div class="row_or"><div>' . TEXT_INFO_NEW_PRICE . '</div></div>';
        foreach ($prices as $value) {
          foreach ($value as $gid => $price) {
            echo '<div class="row_or"><div class="group-name">' . ($gid==0?TEXT_MAIN:$groups[$gid]['groups_name']) . '</div> <div class="currency">' . $price['text'] . '</div></div>';
          }
        }
      }

    }
    echo '<div class="row_or">&nbsp;</div><div class="row_or"><div>' . TEXT_START_DATE . '</div><div  class="date-time-smaller">' . \common\helpers\Date::datetime_short($sInfo->start_date) . '</div></div>';
    echo '<div class="row_or"><div>' . TEXT_INFO_EXPIRES_DATE . '</div><div  class="date-time-smaller">' . \common\helpers\Date::datetime_short($sInfo->expires_date) . '</div></div>';
    ?>
    </div>
    <div class="btn-toolbar btn-toolbar-order">
      <a class="btn btn-edit btn-no-margin" href="<?php echo Yii::$app->urlManager->createUrl(['specials/specialedit', 'id' => $sInfo->specials_id]); ?>"><?php echo IMAGE_EDIT ?></a><button class="btn btn-delete" onclick="return deleteItemConfirm(<?php echo $item_id; ?>)"><?php echo IMAGE_DELETE; ?></button>
    </div>
    <?php
    }
  }

/**
 * @deprecated
 */
  function actionItemedit() {
    $this->layout = FALSE;

    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/specials');

    $item_id = (int) Yii::$app->request->post('item_id');

    $currencies = Yii::$container->get('currencies');

    //$_params = Yii::app()->getParams();
    //if( !isset( $_params->currencies ) ) Yii::app()->setParams( array( 'currencies' => $currencies ) );

    $header = '';
    $script = '';
    $delete_btn = '';
    $form_html = '';

    $fields = array();

    $languages = \common\helpers\Language::get_languages();

    if ($item_id === 0) {
      // Insert
      $header = 'Insert';

      $sInfo = new \objectInfo(array());

      $specials_array = array();
      $specials_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id");
      while ($specials = tep_db_fetch_array($specials_query)) {
        $specials_array[] = $specials['products_id'];
      }

      $special_product_html = \common\helpers\Product::draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array);


      $fields[] = array('type' => 'field', 'title' => TEXT_SPECIALS_PRODUCT, 'value' => $special_product_html);

      $fields[] = array('name' => 'products_price', 'type' => 'hidden', 'value' => '');


      if (USE_MARKET_PRICES == 'True') {

        foreach ($currencies->currencies as $key => $value) {

          $specials_products_price_html = tep_draw_input_field(
              'specials_new_products_price[' . $currencies->currencies[$key]['id'] . ']',
              \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id']), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $currencies->currencies[$key]['title'], 'value' => $specials_products_price_html);
        }

        $data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
        while ($data = tep_db_fetch_array($data_query)) {
          $data_html = tep_draw_input_field('specials_new_products_price_' . $data['groups_id'] . '[' . $currencies->currencies[$key]['id'] . ']', \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'], $data['groups_id'], '-2'), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $data['groups_name'], 'value' => $data_html);
        }
      } else {
        $fields[] = array('name' => 'specials_price', 'title' => TEXT_SPECIALS_SPECIAL_PRICE, 'value' => '');
      }

      $fields[] = array('name' => 'expires_date', 'title' => TEXT_SPECIALS_EXPIRES_DATE, 'class' => 'datepicker', 'value' => '');
    } else {
      // Update
      $header = 'Edit';

      $product_query = tep_db_query("select p.products_id, s.specials_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name, p.products_price, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'");
      $product = tep_db_fetch_array($product_query);

      $sInfo = new \objectInfo($product);

      $specials_array = array();
      $specials_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s where s.products_id = p.products_id");
      while ($specials = tep_db_fetch_array($specials_query)) {
        $specials_array[] = $specials['products_id'];
      }

      if (isset($sInfo->products_name)) {
        $special_product_html = $sInfo->products_name . ' <small>(' . $currencies->format(\common\helpers\Product::get_products_price($sInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])) . ')</small>';
      } else {
        $special_product_html = \common\helpers\Product::draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array);
      }

      $fields[] = array('type' => 'field', 'title' => TEXT_SPECIALS_PRODUCT, 'value' => $special_product_html);


      $status_checked_disabled = FALSE;
      $status_checked_active = FALSE;

      if ((int) $sInfo->status > 0) {
        $status_checked_active = TRUE;
      } else {
        $status_checked_disabled = TRUE;
      }

      $status_html = tep_draw_checkbox_field("status", '1', $status_checked_active, '', 'class="check_on_off"');
      /*                $status_html .= "Active " . tep_draw_radio_field( 'status', 1, $status_checked_active );
        $status_html .= '<br>';
        $status_html .= "Inactive " . tep_draw_radio_field( 'status', '0', $status_checked_disabled ); */



      $fields[] = array('type' => 'field', 'title' => TABLE_HEADING_STATUS, 'value' => $status_html);

      if (USE_MARKET_PRICES == 'True') {

        $specials_products_price_html = '';
        foreach ($currencies->currencies as $key => $value) {
          $specials_products_price_html = tep_draw_input_field('specials_new_products_price[' . $currencies->currencies[$key]['id'] . ']', (($specials_new_products_price[$currencies->currencies[$key]['id']]) ? stripslashes($specials_new_products_price[$currencies->currencies[$key]['id']]) : \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'])), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $currencies->currencies[$key]['title'], 'value' => $specials_products_price_html);
        }

        $data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
        while ($data = tep_db_fetch_array($data_query)) {
          $group_html = tep_draw_input_field('specials_new_products_price_' . $data['groups_id'] . '[' . $currencies->currencies[$key]['id'] . ']', \common\helpers\Product::get_specials_price($sInfo->specials_id, $currencies->currencies[$key]['id'], $data['groups_id'], '-2'), 'size="20"');
          $fields[] = array('type' => 'field', 'title' => $data['groups_name'], 'value' => $group_html);
        }
      } else {
        $fields[] = array('name' => 'specials_price', 'title' => TEXT_SPECIALS_SPECIAL_PRICE, 'value' => \common\helpers\Product::get_specials_price($sInfo->specials_id));

        $fields[] = array('name' => 'products_price', 'type' => 'hidden', 'value' => ( isset($sInfo->products_price) ? $sInfo->products_price : '' ));
      }

      if ($sInfo->expires_date == '0000-00-00 00:00:00') {
        $expires_date = '';
      } else {
        $expires_date = explode("-", $sInfo->expires_date);
        @$Y = $expires_date[0];
        @$M = $expires_date[1];
        @$d = $expires_date[2];
        @$D = explode(" ", $d);
        $expires_date = $M . "/" . $D[0] . "/" . $Y;
      }

      if ($expires_date == "//")
        $expires_date = '';

      $fields[] = array('name' => 'expires_date', 'title' => TEXT_SPECIALS_EXPIRES_DATE, 'class' => 'datepicker', 'value' => \common\helpers\Date::date_short($sInfo->expires_date));

      $fields[] = array('type' => 'field', 'title' => '', 'value' => TEXT_SPECIALS_PRICE_TIP);
    }

    echo tep_draw_form(
        'save_item_form',
        'specials/submit',
        \common\helpers\Output::get_all_get_params(array('action')),
        'post',
        'id="save_item_form" onSubmit="return saveItem();"') .
    tep_draw_hidden_field('item_id', $item_id);
    ?>
    <div class="or_box_head"><?php echo $header; ?></div>

    <?php
    foreach ($fields as $field) {
      if (isset($field['title']))
        $field_title = $field['title'];
      else
        $field_title = '';
      if (isset($field['name']))
        $field_name = $field['name'];
      else
        $field_name = '';
      if (isset($field['value']))
        $field_value = $field['value'];
      else
        $field_value = '';
      if (isset($field['type']))
        $field_type = $field['type'];
      else
        $field_type = 'text';
      if (isset($field['class']))
        $field_class = $field['class'];
      else
        $field_class = '';
      if (isset($field['required']))
        $field_required = '<span class="fieldRequired">* Required</span>';
      else
        $field_required = '';
      if (isset($field['maxlength']))
        $field_maxlength = 'maxlength="' . $field['maxlength'] . '"';
      else
        $field_maxlength = '';
      if (isset($field['size']))
        $field_size = 'size="' . $field['size'] . '"';
      else
        $field_size = '';
      if (isset($field['post_html']))
        $field_post_html = $field['post_html'];
      else
        $field_post_html = '';
      if (isset($field['pre_html']))
        $field_pre_html = $field['pre_html'];
      else
        $field_pre_html = '';
      if (isset($field['cols']))
        $field_cols = $field['cols'];
      else
        $field_cols = '70';
      if (isset($field['rows']))
        $field_rows = $field['rows'];
      else
        $field_rows = '15';

      if ($field_type == 'hidden') {
        $form_html .= tep_draw_hidden_field($field_name, $field_value);
      } elseif ($field_type == 'field') {
        echo ' <div class="main_row">';
        echo '      <div class="main_title">' . $field_title . '</div>';
        echo '       <div class="main_value">       ';
        echo "        $field_value";
        echo '       </div>       ';
        echo ' </div>';
      } elseif ($field_type == 'textarea') {

        $field_html = tep_draw_textarea_field($field_name, 'soft', $field_cols, $field_rows, $field_value);

        echo ' <div class="main_row">';
        echo '      <div class="main_title">' . $field_title . '</div>       ';
        echo '       <div class="main_value">       ';
        echo "        $field_pre_html $field_html  $field_required $field_post_html";
        echo '       </div>       ';
        echo ' </div>';
      } else {
        echo ' <div class="main_row">';
        echo '      <div class="main_title">' . $field_title . '</div>       ';
        echo '       <div class="main_value">       ';
        echo "        $field_pre_html <input type='$field_type' name='$field_name' value='$field_value' $field_maxlength $field_size class='$field_class'> $field_post_html $field_required";
        echo '       </div>       ';
        echo ' </div>';
      }
    }
    ?>
    <div class="btn-toolbar btn-toolbar-order">
      <input class="btn btn-no-margin" type="submit" value="<?php echo IMAGE_SAVE; ?>"><?php echo $delete_btn; ?><input class="btn btn-cancel" type="button" onclick="return resetStatement()" value="<?php echo IMAGE_CANCEL; ?>">
    </div>

    <?php echo $form_html; ?>
    </form>
    <script>
      $(document).ready(function () {
        $(".widget-content .check_on_off").bootstrapSwitch(
        {
          onText: "<?= SW_ON ?>",
          offText: "<?= SW_OFF ?>",
          handleWidth: '20px',
          labelWidth: '24px'
        }
        );

        $(".datepicker").datepicker({
          changeMonth: true,
          changeYear: true,
          showOtherMonths: true,
          autoSize: false,
          minDate: '1',
          dateFormat: '<?= DATE_FORMAT_DATEPICKER ?>',

        });
      })
    </script>
    <?php
  }

  function actionValidate() {

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $post = \Yii::$app->request->post();
    //echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($post , 1) ."</PRE>"; die;
    $currencies = Yii::$container->get('currencies');
    $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];

    $specials_expires_date =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'expires_date', 'dbdef' => '', 'post' => 'special_expires_date'], $_def_curr_id, 0);
    $specials_expires_date = \common\helpers\Date::prepareInputDate($specials_expires_date, true);
    $specials_start_date =  \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays(['db' => 'start_date', 'dbdef' => 'NULL', 'post' => 'special_start_date'], $_def_curr_id, 0);
    $specials_start_date = \common\helpers\Date::prepareInputDate($specials_start_date, true);
    if (empty($specials_expires_date)) {
      $specials_expires_date = '9999-01-01';
    }
    if (empty($specials_start_date)) {
      $specials_start_date = '0000-00-00';
    }

    $listQuery = \common\models\Specials::find()->alias('s')->joinWith(['prices'])->select('s.*');
    $listQuery->andWhere(['products_id' => (int)$post['products_id']]);
    if (intval($post['specials_id'])>0) {
      $listQuery->andWhere(['<>', 's.specials_id', intval($post['specials_id'])]);
    }
    $listQuery->datesInRange($specials_start_date, $specials_expires_date);
//echo $listQuery->createCommand()->rawSql;
    $q = $listQuery->asArray()->all();
    if (empty($q )) {
      $ret = ['valid' => 1];
    } else {
      $ret = ['list' => TEXT_OVERLAPPED_DATE_RANGE ." \$specials_start_date $specials_start_date \$specials_expires_date $specials_expires_date ", 'valid' => 0];
      foreach ($q as $price) {
        $ret['list'] .= '<br>';
        $ret['list'] .= $listQuery->createCommand()->rawSql .' <br>';
        $ret['list'] .= ' <span class="date start-date" title="' . $price['specials_id'] . '">' . TEXT_START_DATE . ' ' . \common\helpers\Date::datetime_short($price['start_date']) . '</span>';
        $ret['list'] .= ' <span class="date start-date">' . TEXT_SPECIALS_EXPIRES_DATE . ' ' . \common\helpers\Date::datetime_short($price['expires_date']) . '</span>';
        $ret['list'] .= ' <span class="group">' . TEXT_MAIN . ' <span class="price group-price0">' . $currencies->format($price['specials_new_products_price']) . '</span></span>';
      }
    }
    return $ret;
  }


  function actionSubmit() {
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $ret = ['result' => 0 ];
    \common\helpers\Translation::init('admin/specials');

    $products_id = (int) Yii::$app->request->post('products_id');
    $res = \common\helpers\Specials::saveFromPost($products_id, 0);
    if ($res === true) {
      $ret = ['result' => 1 ];
    } else {
      if (is_string($res)) {
        $ret['message'] = $res;
      } else {
        $ret['message'] = TEXT_MESSAGE_ERROR;
      }
    }

    return $ret;
  }


  function actionConfirmitemdelete() {
    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/specials');
    $this->layout = FALSE;

    $item_id = (int) Yii::$app->request->post('item_id');

    $message = $name = $title = '';
    $parent_id = 0;

    $specials_query = tep_db_query("select p.products_id, s.specials_id, " . ProductNameDecorator::instance()->listingQueryExpression('pd', '') . " AS products_name, p.products_price, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and p.products_id = s.products_id and s.specials_id = '" . (int) $item_id . "'");
    $specials = tep_db_fetch_array($specials_query);

    $sInfo = new \objectInfo($specials);

    echo '<div class="or_box_head top_spec">' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</div>';
    echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
    echo '<div class="col_desc"><strong>' . $sInfo->products_name . '</strong></div>';
    echo tep_draw_form('item_delete', FILENAME_SPECIALS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
    ?>
    <div class="btn-toolbar btn-toolbar-order">
    <?php
    echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
    echo '<button class="btn btn-cancel" onclick="return resetStatement()">' . IMAGE_CANCEL . '</button>';

    echo tep_draw_hidden_field('item_id', $item_id);
    ?>
    </div>
    </form>
    <?php
  }

  function actionItemdelete() {
    $this->layout = FALSE;

    $specials_id = (int) Yii::$app->request->post('item_id');

    $messageType = 'success';
    $message = TEXT_INFO_DELETED;

    tep_db_query("delete from " . TABLE_SPECIALS . " where specials_id = '" . (int) $specials_id . "'");

    if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True') {
      tep_db_query("delete from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . tep_db_input($specials_id) . "'");
    }
    ?>
    <div class="popup-box-wrap pop-mess">
      <div class="around-pop-up"></div>
      <div class="popup-box">
        <div class="pop-up-close pop-up-close-alert"></div>
        <div class="pop-up-content">
          <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
          <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
    <?php echo $message; ?>
          </div>
        </div>
        <div class="noti-btn">
          <div></div>
          <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
        </div>
      </div>
      <script>
        $('body').scrollTop(0);
        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
          $(this).parents('.pop-mess').remove();
        });
      </script>
    </div>


    <p class="btn-toolbar">
    <?php
    echo '<input type="button" class="btn btn-primary" value="' . IMAGE_BACK . '" onClick="return resetStatement()">';
    ?>
    </p>
    <?php
  }

  public function actionSpecialedit() {

    $specialsId = (int) Yii::$app->request->get('id');
    $productsId = (int) Yii::$app->request->get('products_id');
    $bp = Yii::$app->request->get('bp', []);

    $this->view->headingTitle = HEADING_TITLE;
    $this->selectedMenu = array('marketing', 'specials');
    \common\helpers\Translation::init('admin/specials');
    \common\helpers\Translation::init('admin/categories');

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('specials/index'), 'title' => HEADING_TITLE);

    $params = [];
    if (!empty($specialsId) || !empty($productsId)) {
      $template = 'specialedit';
      $currencies = Yii::$container->get('currencies');
      
      if (!empty($specialsId) ) {
        $sInfo = \common\models\Specials::find()->andWhere(['specials_id' => $specialsId])->with(['prices', 'backendProductDescription'])->one();
        if (!empty($sInfo->specials_id)) {
          $pInfo = $sInfo->product;
          unset($sInfo->product);
        }
      }
      if (empty($sInfo->specials_id) && !empty($productsId)) {
        $pInfo = \common\models\Products::find()->andWhere(['products_id' => $productsId])->with(['backendDescription'])->one();
      }

      if (!empty($pInfo)) {
        //fill in tabs details
        $params['currencies'] =  $currencies;
        $_tax =  \common\helpers\Tax::get_tax_rate_value($pInfo->products_tax_class_id)/100;
        $_roundTo = $currencies->get_decimal_places(DEFAULT_CURRENCY);
        $params['sInfo'] =  (object)\yii\helpers\ArrayHelper::toArray($sInfo);
        $params['pInfo'] = (object)\yii\helpers\ArrayHelper::toArray($pInfo);
        if (!empty($sInfo->specials_id)) {
          $params['pInfo']->specials_id = $sInfo->specials_id;
        }

        $params['price'] = $currencies->format($pInfo->products_price);
        $params['priceGross'] = $currencies->format($pInfo->products_price+ round($pInfo->products_price*$_tax, 6), $_roundTo);
        $params['backendProductDescription'] = \yii\helpers\ArrayHelper::toArray($pInfo->backendDescription, ['products_name']);
        $params['default_currency'] = $currencies->currencies[DEFAULT_CURRENCY];
        //$this->view->defaultCurrency = $currencies->currencies[DEFAULT_CURRENCY]['id'];
        $this->view->defaultSaleId = (empty($sInfo->specials_id)?0:$sInfo->specials_id);

        $priceViewObj = new ViewPriceData($pInfo);
        $priceViewObj->populateView($this->view, $currencies);
        $this->view->tax_classes = [0 => TEXT_NONE];
        $tmp = \common\models\TaxClass::find()->select('tax_class_id, tax_class_title')->orderBy('tax_class_title')->asArray()->indexBy('tax_class_id')->all();
        if (!empty($tmp)) {
          $this->view->tax_classes += \yii\helpers\ArrayHelper::getColumn($tmp, 'tax_class_title');
        }


       /* $tmp = [
                'groups_id' => 0,
                'currencies_id' => $currencies->currencies[DEFAULT_CURRENCY]['id'],
                'products_group_price' => $prod['products_price'],
                'products_group_price_gross' => round($prod['products_price'] + round($prod['products_price']*$_tax, 6), $_roundTo),
                'products_group_special_price' => (isset($_def_sale['status']) ? $_def_sale['specials_new_products_price']:0),
                'products_group_special_price_gross' => (isset($_def_sale['status']) ? round($_def_sale['specials_new_products_price'] + round($_def_sale['specials_new_products_price']*$_tax, 6), $_roundTo):0),
                'expires_date' => !empty($_def_sale['expires_date']) ? $_def_sale['expires_date']:'',
                'start_date' => !empty($_def_sale['start_date']) ? $_def_sale['start_date']:'',
                'tax_rate' => (double)$_tax,
                'round_to' => (int)$_roundTo,
            ];
            if (CUSTOMERS_GROUPS_ENABLE != 'True') {
                $price_tabs_data = $tmp;
            } else {
                $price_tabs_data[0] = $tmp;
            }
*/

/// re-arrange data arrays for design templates
// init price tabs
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
        if ($this->view->useMarketPrices) {
          $this->view->currenciesTabs = [];
          foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesTabs[] = $value;
          }
          $this->view->price_tabs[] = $this->view->currenciesTabs;
          $this->view->price_tabparams[] =  [
              'cssClass' => 'tabs-currencies',
              'tabs_type' => 'hTab',
              //'include' => 'test/test.tpl',
          ];
        }

    //// groups tabs and params
        if (CUSTOMERS_GROUPS_ENABLE == 'True' ) {
          $this->view->groups = [];
          /** @var \common\extensions\UserGroups\UserGroups $ext */
          if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroups')) {
              $ext::getGroups();
          }

          $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $this->view->groups);
          $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            unset($value['groups_id']);
            $tmp[] = $value;
          }
          $this->view->price_tabs[] = $tmp;
          unset($tmp);
          $this->view->price_tabparams[] = [
              'cssClass' => 'tabs-groups', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'lTab',
          ];
        }
        
      } else {
        $template = 'choose_product';
      }
    } else {
      
      $template = 'choose_product';
    }

    return $this->render($template, $params);
  }

  public function actionSwitchStatus() {
    $id = Yii::$app->request->post('id');
    $status = Yii::$app->request->post('status');
    tep_db_query("update " . TABLE_SPECIALS . " set status = '" . ($status == 'true' ? 1 : 0) . "' where specials_id = '" . (int) $id . "'");
  }


  public function actionDeleteSelected() {
    $this->layout = FALSE;

    $spIds = Yii::$app->request->post('bulkProcess', []);
    if (is_array($spIds) && !empty($spIds)) {
      $spIds = array_map('intval', $spIds);
      \common\models\Specials::deleteAll(['specials_id' => $spIds]);
      \common\models\SpecialsPrices::deleteAll(['specials_id' => $spIds]);
    }
  }



}

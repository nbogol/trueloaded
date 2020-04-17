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

use common\classes\platform;
use common\helpers\Acl;
use Yii;
use \yii\helpers\Html;
use common\models\Platforms;
use backend\models\Report;
use common\models\repositories\CountriesRepositiry;
use common\models\repositories\ZoneCountriesRepository;
use common\services\ZonesService;
use common\services\CountriesService;
use common\services\CurrenciesMarginService;
use common\models\PlatformsSettings;

class PlatformsController extends Sceleton {

    public $acl = ['BOX_HEADING_FRONENDS'];
    private $serviceZone;
    private $serviceCountry;
    private $currenciesMarginService;

    public function __construct(
        $id,
        $module = null,
        CurrenciesMarginService $currenciesMarginService,
        ZonesService $serviceZone,
        CountriesService $serviceCountry)
    {
        $this->currenciesMarginService = $currenciesMarginService;
        $this->serviceZone = $serviceZone;
        $this->serviceCountry = $serviceCountry;
        parent::__construct($id, $module);
    }

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        $this->selectedMenu = array('fronends', 'platforms');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('platforms/index'), 'title' => HEADING_TITLE);
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'index')) {
            $ext::index();
        }
        //$this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('platforms/edit').'" class="create_item addprbtn"><i class="icon-tag"></i>'.TEXT_CREATE_NEW_PLATFORM.'</a>';
        $this->view->headingTitle = HEADING_TITLE;
        $this->view->platformsTable = array(
            array(
                    'title' => TABLE_HEADING_PLATFORM_NAME,
                    'not_important' => 1
                ),
                array(
                    'title' => TABLE_HEADING_PLATFORM_URL,
                    'not_important' => 1
                ),
                array(
                    'title' => TABLE_HEADING_STATUS,
                    'not_important' => 1
                )
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)$_GET['row'];
        $this->view->filters->pane = Yii::$app->request->get('pane', 'physical');

        return $this->render('index');

    }


    public function actionList() {
        $draw   = Yii::$app->request->get( 'draw', 1 );
        $start  = Yii::$app->request->get( 'start', 0 );
        $length = Yii::$app->request->get( 'length', 10 );

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $type = $output['pane'] ?? 'physical';

        $responseList = array();
        if( $length == -1 ) $length = 10000;
        $query_numrows = 0;

        $platformsQuery = Platforms::getPlatformsByType($type);

        $query_numrows = $platformsQuery->count();

        if( isset( $_GET['search']['value'] ) && tep_not_null( $_GET['search']['value'] ) ) {
            $keywords = tep_db_input( tep_db_prepare_input( $_GET['search']['value'] ) );
            $platformsQuery->andWhere(['like', 'platform_name', $keywords]);
        }

        if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
            switch( $_GET['order'][0]['column'] ) {
                case 0:
                    $platformsQuery->orderBy("platform_name " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] )));
                    break;
                case 1:
                    $platformsQuery->orderBy("sort_order " . tep_db_input(tep_db_prepare_input( $_GET['order'][0]['dir'] )).", platform_id ");
                    break;
                default:
                    $platformsQuery->orderBy("sort_order, platform_name");
                    break;
            }
        } else {
            $platformsQuery->orderBy("sort_order, platform_name");
        }

        $query_show = $platformsQuery->count();
        $platforms = $platformsQuery->limit($length)->offset($start)->all();
        if ($platforms){
            foreach($platforms as $platform){
                $statement = '';
                if (!\common\helpers\Acl::checkExtension('AdditionalPlatforms', 'allowed')) {
                    if ($platform->platform_id != 1) {
                        $statement = ' dis_module';
                        $platform->status = 0;
                    }
                }
                Yii::$app->get('platform')->config($platform->platform_id);

                $status = '<input type="checkbox" value="'. $platform->platform_id . '" name="status" class="check_on_off" ' . ((int) $platform->status > 0 ? 'checked="checked"' : '') . '>';

                $responseList[] = array(
                    '<div class="handle_cat_list'.$statement.'"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                      $platform->platform_name .
                      '<input class="cell_identify" type="hidden" value="' . $platform->platform_id . '">'.
                      '<input class="cell_type" type="hidden" value="top">'.
                    '</div></div>',
                    '<a target="_blank" href="'.tep_catalog_href_link('').'">'.$platform->platform_url.'</a>',
                    $status,
                );
            }
        }

        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => $query_numrows,
            'recordsFiltered' => $query_show,
            'data'            => $responseList,
            'type'            => $type,
        );
        echo json_encode( $response );
    }

    public function actionSwitchStatus() {
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'switchStatus')) {
            $ext::switchStatus();
        }
    }

    public function actionItemPreedit()
    {
        $this->layout = false;

        \common\helpers\Translation::init('admin/platforms');

        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $platform = Platforms::findOne(['platform_id' => (int)$item_id ]);

        if (!$platform ){
            throw new \DomainException('Not found');
        }

        $statement = true;
        if (!\common\helpers\Acl::checkExtension('AdditionalPlatforms', 'allowed')) {
            if ($item_id != 1) {
                $statement = false;
            }
        }

        $multiplatform = '';
        if ( count(platform::getCategoriesAssignList())>1 ) {
            $multiplatform .= '<a href="' . Yii::$app->urlManager->createUrl(['platforms/edit-catalog', 'id' => $item_id]) . '" class="btn btn-edit btn-process-order js-open-tree-popup">'.BUTTON_ASSIGN_CATEGORIES_PRODUCTS.'</a>';
        }

        return $this->render('view', [
            'platform' => $platform,
            'statement' => $statement,
            'multiplatform' => $multiplatform,
        ]);
    }

    public function actionEdit()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/platforms');

        $days = [
            0 => TEXT_EVERYDAY,
            1 => TEXT_MONDAY,
            2 => TEXT_TUESDAY,
            3 => TEXT_WEDNESDAY,
            4 => TEXT_THURSDAY,
            5 => TEXT_FRIDAY,
            6 => TEXT_SATURDAY,
            7 => TEXT_SUNDAY,
        ];

        $item_id = 1;
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'edit')) {
            $item_id = $ext::edit();
        }

        $check_watermark_query = tep_db_query("SELECT * FROM " . TABLE_PLATFORMS_WATERMARK . " WHERE platform_id=" . (int)$item_id);
        if ( tep_db_num_rows($check_watermark_query) > 0 ) {
            $check_watermark = tep_db_fetch_array($check_watermark_query);
            unset($check_watermark['platform_id']);
            $check_watermark['watermark_status'] = $check_watermark['status'];
            unset($check_watermark['status']);
            $watermark = $check_watermark;
        } else {
            $watermark = [
                'watermark_status' => 0,
            ];
        }

        if ($item_id > 0) {
            $groups_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
            $groups = tep_db_fetch_array($groups_query);
            $pInfo = new \objectInfo(array_merge($groups, $watermark));
        } else {
            $pInfo = new \objectInfo($watermark);
        }

        if ($item_id){
            $address_query = tep_db_query("select ab.*, if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as entry_state, c.countries_name  from " . TABLE_PLATFORMS_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where platform_id = '" . (int) $item_id . "' ");
            $d = tep_db_fetch_array($address_query);
        } else {
            $d = [];
        }
        if (!isset($d['entry_country_id'])) {
            $d['entry_country_id'] = STORE_COUNTRY;
        }
        $addresses = new \objectInfo($d);

        $open_hours = [];
        $open_hours_query = tep_db_query("select * from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int) $item_id . "' ");
        while ($d = tep_db_fetch_array($open_hours_query)) {
            if (isset($d['open_days'])) {
                $d['open_days'] = explode(",", $d['open_days']);
            }
            $open_hours[] = new \objectInfo($d);
        }
        if (count($open_hours) == 0) {
            $open_hours[] = new \objectInfo([]);
        }

        $cut_off_times = [];
        $cut_off_times_query = tep_db_query("select * from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where platform_id = '" . (int) $item_id . "' ");
        while ($d = tep_db_fetch_array($cut_off_times_query)) {
            if (isset($d['cut_off_times_days'])) {
                $d['cut_off_times_days'] = explode(",", $d['cut_off_times_days']);
            }
            $cut_off_times[] = new \objectInfo($d);
        }
        if (count($cut_off_times) == 0) {
            $cut_off_times[] = new \objectInfo([]);
        }

        $theme_array = [];
        $pInfo->theme_id = 0;

        $theme = tep_db_fetch_array(tep_db_query("select t.* from " . TABLE_PLATFORMS_TO_THEMES . " AS p2t INNER JOIN " . TABLE_THEMES . " as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)$item_id));

        if (isset($theme['id'])) {
            $pInfo->theme_id = $theme['id'];
            $theme_array[] = $theme;
        }

        $pInfo->platform_urls = [];
        $get_platform_urls_r = tep_db_query("SELECT * FROM ".TABLE_PLATFORMS_URL." WHERE platform_id='".(int)$item_id."' ");
        if ( tep_db_num_rows($get_platform_urls_r)>0 ) {
            while($_platform_url = tep_db_fetch_array($get_platform_urls_r)){
                $pInfo->platform_urls[] = $_platform_url;
            }
        }

        $pInfo->platform_locations = [];
        $get_platform_locations_r = tep_db_query("SELECT * FROM ".TABLE_PLATFORMS_LOCATIONS." WHERE platform_id='".(int)$item_id."' ");
        if ( tep_db_num_rows($get_platform_locations_r)>0 ) {
            while($_platform_locations = tep_db_fetch_array($get_platform_locations_r)){
                $pInfo->platform_locations[] = $_platform_locations;
            }
        }

        $this->view->currencies = [];
        $currencies = Yii::$container->get('currencies');
        foreach ($currencies->currencies as $currency) {
            $this->view->currencies[$currency['code']] = $currency['title'];
        }

        $this->view->languages = [];
        $languages = \common\helpers\Language::get_languages();
        foreach ($languages as $language) {
            $this->view->languages[$language['code']] = $language['name'];
        }

        $text_new_or_edit = ($item_id == 0) ? TEXT_INFO_HEADING_NEW_PLATFORM : TEXT_INFO_HEADING_EDIT_PLATFORM;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('platforms/'), 'title' => $text_new_or_edit . ' ' . $pInfo->platforms_name);
        $this->selectedMenu = array('fronends', 'platforms');

        if (Yii::$app->request->isPost) {
            $this->layout = false;
        }
        $this->view->showState = (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible');

        $have_more_then_one_platform = true;
        if ( tep_db_num_rows(tep_db_query("select platform_id from ".TABLE_PLATFORMS.""))<2 ) {
            $have_more_then_one_platform = false;
            if ( !$pInfo->is_default ) $pInfo->is_default = 1;
        }
        $checkbox_default_platform_attr = array();
        if ($pInfo->is_default) {
            // disable off for default - only on available
            $checkbox_default_platform_attr['readonly'] = 'readonly';
        }

        if ($pInfo->platform_url) {
            $this->topButtons[] = '<a href="//' . $pInfo->platform_url . '" class="btn btn-elements" target="_blank">' . IMAGE_PREVIEW . '</a>';
        }

        if ($serverExt = \common\helpers\Acl::checkExtension('PlatformSoapServer', 'isPresent')) {
            $serverExt::onPlatformEdit($pInfo);
        }

        $sattelites = Platforms::getPlatformsByType('physical')->all();
        $sIds = $sUrls = [];
        if ($sattelites){
            array_map(function($pl) { $pl->platform_url = ($pl->ssl_enabled ? 'https://' : 'http://') .  $pl->platform_url; }, $sattelites);
            $sIds = \yii\helpers\ArrayHelper::map($sattelites, 'platform_id', 'platform_name');
            $sUrls = \yii\helpers\ArrayHelper::map($sattelites, 'platform_id', 'platform_url');
        }

        $repositoryZoneCountries = new ZoneCountriesRepository();
        $zonesArray = $repositoryZoneCountries->getPlatformsZoneCountries($item_id);
        $selected_zones = [];
        foreach ($zonesArray as $item => $zones) {
            $selected_zones[] = $zones->zone_country_id;
        }

        $report = new Report($vars);
        $zones = $report->getGeoZones();
        foreach ($zones as $zonesKey => $zonesValue) {
            foreach ($zonesValue as $zonesValueKey => $zonesValueValue) {
                if (empty($zonesValueValue)) {
                    $zones[$zonesKey][$zonesValueKey] = TEXT_ALL;
                }
            }
        }

        $repositoryCountries = new CountriesRepositiry();
        $countriesArray = $repositoryCountries->getPlatformsCountries($item_id);
        $selected_countries = [];
        foreach ($countriesArray as $item => $country) {
            $selected_countries[] = $country->countries_id;
        }

        $countries = [TEXT_ALL =>\common\helpers\Country::new_get_countries('', false)];
        $pass = dirname(__DIR__);

        $price_settings = defined('PLATFORM_OWN_PRICE') && PLATFORM_OWN_PRICE == 'true';
        $nvPlatforms = null;

        $pInfo->platform_settings = PlatformsSettings::findOne($pInfo->platform_id);
        if (!$pInfo->platform_settings){
            $pInfo->platform_settings = new PlatformsSettings();
            $pInfo->platform_settings->use_owner_descriptions = \common\classes\platform::defaultId();
        }
        $nvPlatforms = \yii\helpers\ArrayHelper::map(Platforms::getPlatformsByType('non-virtual')->andWhere(['and', ['status' => 1], ['<>', 'platform_id', (int)$pInfo->platform_id]])
                    ->orderBy("is_marketplace, platform_name")->asArray()->all(), 'platform_id', 'platform_name');

        $warehouses = [];
        $checkQuery = tep_db_query("select w.warehouse_id, w.warehouse_name, ifnull(w2p.status, w.status) as status from " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p on w.warehouse_id = w2p.warehouse_id and w2p.platform_id = '" . (int)$pInfo->platform_id . "' where 1 order by ifnull(w2p.sort_order, w.sort_order), w.warehouse_name");
        while($check = tep_db_fetch_array($checkQuery)) {
            $warehouses[] = [
                'id' => $check['warehouse_id'],
                'text' => $check['warehouse_name'],
                'status' => $check['status'],
            ];
        }

        $warehouse_priorities = [];
        if ($ext = \common\helpers\Acl::checkExtension('WarehousePriority', 'getInstance')) {
            $warehouse_priorities = $ext::getInstance()->getModules($item_id);
        }

        /*if ($ext = \common\helpers\Acl::checkExtension('WarehousePriority', 'getInstance')) {
            $orderedProduct = [
                'products_id' => 878,
                'products_quantity' => 1,
                ];
            $prefferedWarehouseIds = $ext::getInstance()->getPreferredWarehouseId($orderedProduct, $item_id);
            echo "<pre>";
            print_r($prefferedWarehouseIds);
            echo "</pre>";
            die();
        }*/

        $organization_types = [
            '' => 'Organization',
            'Store' => 'Store',
            'Airline' => 'Airline',
            'Consortium' => 'Consortium',
            'Corporation' => 'Corporation',
            'EducationalOrganization' => 'EducationalOrganization',
            'FundingScheme' => 'FundingScheme',
            'GovernmentOrganization' => 'GovernmentOrganization',
            'LibrarySystem' => 'LibrarySystem',
            'LocalBusiness' => 'LocalBusiness',
            'MedicalOrganization' => 'MedicalOrganization',
            'NGO' => 'NGO',
            'NewsMediaOrganization' => 'NewsMediaOrganization',
            'PerformingGroup' => 'PerformingGroup',
            'Project' => 'Project',
            'SportsOrganization' => 'SportsOrganization',
            'Store' => 'Store',
            'WebSite' => 'WebSite',
            'WorkersUnion' => 'WorkersUnion',
        ];

        return $this->render('edit.tpl',
          [
            'pInfo' => $pInfo,
            'addresses' => $addresses,
            'open_hours' => $open_hours, 'count_open_hours' => count($open_hours),
            'cut_off_times' => $cut_off_times, 'count_cut_off_times' => count($cut_off_times),
            'days' => $days,
            'theme_array' => $theme_array,
            'cdn_url_types' => Yii::$app->get('mediaManager')->getUrlTypes(),
            'checkbox_default_platform_attr' => $checkbox_default_platform_attr,
            'have_more_then_one_platform' => $have_more_then_one_platform,
            'languages' => \common\helpers\Language::get_languages(),
            'platform_languages' => explode(",",strtolower($pInfo->defined_languages)),
            'currencies' => new \common\classes\Currencies(isset($pInfo->platform_id)?(int)$pInfo->platform_id:0),
            'platform_currencies' => explode(",",$pInfo->defined_currencies),
            'sattelits' => $sIds,
            'sattelites_url' => $sUrls,
            'selected_countries' => $selected_countries,
            'countries' => $countries,
            'selected_zones' => $selected_zones,
            'zones' => $zones,
            'pass' => $pass,
            'price_settings' => $price_settings,
            'nvPlatforms' => $nvPlatforms,
            'warehouses' => $warehouses,
            'warehouse_priorities' => $warehouse_priorities,
            'organization_types' => $organization_types,
          ]);
    }

    public function actionSubmit()
    {
        \common\helpers\Translation::init('admin/properties');

        $item_id = 1;
        if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'edit')) {
            $item_id = $ext::edit();
        }

        $platform_owner = tep_db_prepare_input(Yii::$app->request->post('platform_owner'));
        $platform_name = tep_db_prepare_input(Yii::$app->request->post('platform_name'));
        $platform_url = tep_db_prepare_input(Yii::$app->request->post('platform_url'));
        $platform_url = rtrim($platform_url,'/');
        $ssl_enabled = (int) Yii::$app->request->post('ssl_enabled',0);
        $platform_url_secure = tep_db_prepare_input(Yii::$app->request->post('platform_url_secure'));
        $platform_url_secure = rtrim($platform_url_secure,'/');
        $platform_prefix = tep_db_prepare_input(Yii::$app->request->post('platform_prefix'));
        $use_social_login = (int) Yii::$app->request->post('use_social_login',0);
        $platform_please_login = (int) Yii::$app->request->post('platform_please_login',0);

        $platform_email_address = tep_db_prepare_input(Yii::$app->request->post('platform_email_address'));
        $platform_email_from = tep_db_prepare_input(Yii::$app->request->post('platform_email_from'));
        $platform_email_extra = tep_db_prepare_input(Yii::$app->request->post('platform_email_extra'));
        $contact_us_email = tep_db_prepare_input(Yii::$app->request->post('contact_us_email'));
        $landing_contact_email = tep_db_prepare_input(Yii::$app->request->post('landing_contact_email'));
        $platform_telephone = tep_db_prepare_input(Yii::$app->request->post('platform_telephone'));
        $platform_landline = tep_db_prepare_input(Yii::$app->request->post('platform_landline'));

        $price_settings = defined('PLATFORM_OWN_PRICE') && PLATFORM_OWN_PRICE == 'true';
        if ($price_settings){
            $platform_use_own_prices = tep_db_prepare_input(Yii::$app->request->post('use_own_prices'));
            $platform_use_owner_prices = tep_db_prepare_input(Yii::$app->request->post('use_owner_prices'));
        }
        $platform_use_own_desc = tep_db_prepare_input(Yii::$app->request->post('use_own_descriptions'));
        $platform_use_owner_desc = tep_db_prepare_input(Yii::$app->request->post('use_owner_descriptions'));

        $planguages = [];
        if (is_array(Yii::$app->request->post('planguages'))){
          foreach(Yii::$app->request->post('planguages') as $l){
            $planguages[] = $l;
          }
        }
        $default_language = strtolower(tep_db_prepare_input(Yii::$app->request->post('default_language')));

        $pcurrencies = [];
        if (is_array(Yii::$app->request->post('pcurrencies'))){
          foreach(Yii::$app->request->post('pcurrencies') as $c){
            $pcurrencies[] = $c;
          }
        }
        $default_currency = strtoupper(tep_db_prepare_input(Yii::$app->request->post('default_currency')));

        $is_default = false;
        if ( Yii::$app->request->post('present_is_default') ) {
            $is_default = Yii::$app->request->post('is_default', 0);
        }
        $status = (int) Yii::$app->request->post('status');

        if ($is_default) {
            $is_virtual = 0;
            $is_marketplace = 0;
            $is_default_contact = 0;
            $is_default_address = 0;
        } else {
            $is_virtual = (int) Yii::$app->request->post('is_virtual');
            $is_marketplace = (int) Yii::$app->request->post('is_marketplace');
            $is_default_contact = (int) Yii::$app->request->post('is_default_contact');
            $is_default_address = (int) Yii::$app->request->post('is_default_address');
        }
        
        $this->layout = false;
        $error = false;
        $message = '';
        $script = '';
        $delete_btn = '';

        $messageType = 'success';

        $entry_company = tep_db_prepare_input(Yii::$app->request->post('entry_company'));
        $entry_company_vat = tep_db_prepare_input(Yii::$app->request->post('entry_company_vat'));
        $entry_company_reg_number = tep_db_prepare_input(Yii::$app->request->post('entry_company_reg_number'));
        $entry_postcode = tep_db_prepare_input(Yii::$app->request->post('entry_postcode'));
        $entry_street_address = tep_db_prepare_input(Yii::$app->request->post('entry_street_address'));
        $entry_suburb = tep_db_prepare_input(Yii::$app->request->post('entry_suburb'));
        $entry_city = tep_db_prepare_input(Yii::$app->request->post('entry_city'));
        $entry_state = tep_db_prepare_input(Yii::$app->request->post('entry_state'));
        $entry_country_id = tep_db_prepare_input(Yii::$app->request->post('entry_country_id'));
        $address_book_ids = tep_db_prepare_input(Yii::$app->request->post('platforms_address_book_id'));
        $address_lat = tep_db_prepare_input(Yii::$app->request->post('lat'));
        $address_lng = tep_db_prepare_input(Yii::$app->request->post('lng'));
        $entry_zone_id = [];

        $entry_post_code_error = false;
        $entry_street_address_error = false;
        $entry_city_error = false;
        $entry_country_error = false;
        $entry_state_error = false;

        if ($is_default_address == 1) {
            $address_book_ids = [];
        }
        foreach ($address_book_ids as $address_book_key => $address_book_id) {

            $skipAddress = false;

            /*if (strlen($entry_postcode[$address_book_key]) < ENTRY_POSTCODE_MIN_LENGTH) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_post_code_error = true;
                }
                $skipAddress = true;
            }

            if (strlen($entry_street_address[$address_book_key]) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_street_address_error = true;
                }
                $skipAddress = true;
            }

            if (strlen($entry_city[$address_book_key]) < ENTRY_CITY_MIN_LENGTH) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_city_error = true;
                }
                $skipAddress = true;
            }

            if ((int)$entry_country_id[$address_book_key] == 0) {
                if ($address_book_id > 0) {
                    $error = true;
                    $entry_country_error = true;
                }
                $skipAddress = true;
            }*/

            if ($address_book_id == 0 && $skipAddress) {
                unset($address_book_ids[$address_book_key]);
                continue;
            }

            if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
                if ($entry_country_error == true) {
                    //$entry_state_error = true;
                } else {
                    $entry_zone_id[$address_book_key] = 0;
                    //$entry_state_error = false;
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "'");
                    $check_value = tep_db_fetch_array($check_query);
                    $entry_state_has_zones = ($check_value['total'] > 0);
                    if ($entry_state_has_zones == true) {
                        $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "' and (zone_name like '" . tep_db_input($entry_state[$address_book_key]) . "' or zone_code like '" . tep_db_input($entry_state[$address_book_key]) . "')");
                        if (tep_db_num_rows($zone_query) == 1) {
                            $zone_values = tep_db_fetch_array($zone_query);
                            $entry_zone_id[$address_book_key] = $zone_values['zone_id'];
                        } /*else {
                            $error = true;
                            $entry_state_error = true;
                        }*/
                    } else {

                        /*if ($entry_state[$address_book_key] == false) {
                            $error = true;
                            $entry_state_error = true;
                        }*/
                    }
                }
            }
        }


        $platforms_cut_off_times_ids = Yii::$app->request->post('platforms_cut_off_times_id');
        $platforms_cut_off_times_keys = Yii::$app->request->post('platforms_cut_off_times_key');
        $cut_off_times_today = Yii::$app->request->post('cut_off_times_today');
        $cut_off_times_next_day = Yii::$app->request->post('cut_off_times_next_day');


        $platforms_open_hours_ids = Yii::$app->request->post('platforms_open_hours_id');
        $platforms_open_hours_keys = Yii::$app->request->post('platforms_open_hours_key');
        $open_time_from = Yii::$app->request->post('open_time_from');
        $open_time_to = Yii::$app->request->post('open_time_to');

        $platform_code = '';
        $sattelite_platform_id = 0;
        if ($is_virtual == 1) {
            $platforms_open_hours_ids = [];
            $platforms_cut_off_times_ids = [];
            $platform_code = Yii::$app->request->post('platform_code', '');
            $sattelite_platform_id = (int)Yii::$app->request->post('sattelit_id');
        }

        $logo = Yii::$app->request->post('logo', '');
        $logoUpload = Yii::$app->request->post('logo_upload', '');
        $logoRemove = Yii::$app->request->post('image_delete', false);
        if ($logoRemove) {
            $platform = \common\models\Platforms::findOne($item_id);
            @unlink(DIR_FS_CATALOG . $platform->logo);
        }
        if ($logoUpload) {
            $logo = \backend\design\Uploads::move($logoUpload, DIR_WS_IMAGES, true);
        }

        if( $error === FALSE ) {
            $pre_update_default_platform_id = \common\classes\platform::defaultId();
            $sql_data_array = [
                'platform_owner' => $platform_owner,
                'platform_name' => $platform_name,
                'platform_url' => $platform_url,
                'platform_url_secure' => $platform_url_secure,
                'platform_prefix' => $platform_prefix,
                'ssl_enabled' => $ssl_enabled,
                'use_social_login' => $use_social_login,
                'platform_please_login' => $platform_please_login,
                'platform_email_address' => $platform_email_address,
                'platform_email_from' => $platform_email_from,
                'platform_email_extra' => $platform_email_extra,
                'contact_us_email' => $contact_us_email,
                'landing_contact_email' => $landing_contact_email,
                'platform_telephone' => $platform_telephone,
                'platform_landline' => $platform_landline,
                'is_virtual' => $is_virtual,
                'is_marketplace' => $is_marketplace,
                'platform_code' => $platform_code,
                'sattelite_platform_id' => $sattelite_platform_id,
                'is_default_contact' => $is_default_contact,
                'is_default_address' => $is_default_address,
                'status' => $status,
                'defined_languages' => strtolower(implode(",", $planguages)),
                'defined_currencies' => implode(",", $pcurrencies),
                'default_language' => $default_language,
                'default_currency' => $default_currency,
                'organization_site' => Yii::$app->request->post('organization_site', ''),
                'organization_type' => Yii::$app->request->post('organization_type', ''),
                'logo' => $logo,
                'default_platform_id' => (int)Yii::$app->request->post('default_platform_id'),
            ];


            if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'save')) {
                $sql_data_array = array_merge($sql_data_array, $ext::save());
            }

            if ( $is_default!==false ) {
                $sql_data_array['is_default'] = $is_default;
            }
            $platform_updated = false;
            if ($ext = \common\helpers\Acl::checkExtension('AdditionalPlatforms', 'save')) {
                $platform_updated = $item_id>0;
                $item_id = $ext::save($item_id, $planguages, $pcurrencies, $sql_data_array, $message);
            } else {
                $message = "Item updated";
                $sql_data_array['last_modified'] = 'now()';
                tep_db_perform(TABLE_PLATFORMS, $sql_data_array, 'update', "platform_id = '" . $item_id . "'");
                $platform_updated = true;
            }
            if ( $is_default ) {
                tep_db_query(
                  "UPDATE ".TABLE_PLATFORMS." SET is_default=0 ".
                  "WHERE platform_id!='".(int)$item_id."'"
                );
            }
            $ppSettings = PlatformsSettings::findOne($item_id);
            if (!$ppSettings) {
                $ppSettings = new PlatformsSettings;
                $ppSettings->platform_id = (int)$item_id;
            }

            if (!$is_virtual){
                if($price_settings){
                    $ppSettings->use_own_prices = (int)$platform_use_own_prices;
                    if ($ppSettings->use_own_prices){
                        $ppSettings->use_owner_prices = 0;
                    } else {
                        $ppSettings->use_owner_prices = (int)$platform_use_owner_prices;
                    }
                    if (!$status){
                       PlatformsSettings::updateAll(['use_owner_prices' => $pre_update_default_platform_id], ['use_owner_prices' => $item_id]);
                    }
                }
                $ppSettings->use_own_descriptions = (int)$platform_use_own_desc;
                if ($ppSettings->use_own_descriptions){
                    $ppSettings->use_owner_descriptions = 0;
                } else {
                    $ppSettings->use_owner_descriptions = (int)$platform_use_owner_desc;
                }

                if ($is_default){
                    $ppSettings->use_own_prices = 1;
                    $ppSettings->use_owner_prices = 0;
                    $ppSettings->use_own_descriptions = 1;
                    $ppSettings->use_owner_descriptions = 0;
                }
                $ppSettings->save();
            } else {
                PlatformsSettings::updateAll(['use_owner_prices' => $pre_update_default_platform_id], ['use_owner_prices' => $item_id]);
                PlatformsSettings::updateAll(['use_owner_descriptions' => $pre_update_default_platform_id], ['use_owner_descriptions' => $item_id]);
            }


            $googleTool = new \common\components\GoogleTools;
            $activeaddress_book_ids = [];
            foreach ($address_book_ids as $address_book_key => $address_book_id) {
                if ($entry_zone_id[$address_book_key] > 0)
                    $entry_state[$address_book_key] = '';

                $sql_data_array = [
                    'entry_street_address' => $entry_street_address[$address_book_key],
                    'entry_postcode' => $entry_postcode[$address_book_key],
                    'entry_city' => $entry_city[$address_book_key],
                    'entry_country_id' => $entry_country_id[$address_book_key],
                    'entry_company_reg_number' => $entry_company_reg_number[$address_book_key],
                    'is_default' => 1,
                    'lat' => $address_lat[$address_book_key],
                    'lng' => $address_lng[$address_book_key],
                ];

                $sql_data_array['entry_company'] = $entry_company[$address_book_key];
                $sql_data_array['entry_suburb'] = $entry_suburb[$address_book_key];
                $sql_data_array['entry_company_vat'] = $entry_company_vat[$address_book_key];

                if ($entry_zone_id[$address_book_key] > 0) {
                    $sql_data_array['entry_zone_id'] = $entry_zone_id[$address_book_key];
                    $sql_data_array['entry_state'] = '';
                } else {
                    $sql_data_array['entry_zone_id'] = '0';
                    $sql_data_array['entry_state'] = $entry_state[$address_book_key];
                }

                $address = $entry_postcode[$address_book_key] . " " . $entry_street_address[$address_book_key] . " " . $entry_city[$address_book_key] . " " . \common\helpers\Country::get_country_name($entry_country_id[$address_book_key]);
                $location = $googleTool->getGeocodingLocation($address);
                if (is_array($location)) {
                    $sql_data_array['lat'] = $location['lat'];
                    $sql_data_array['lng'] = $location['lng'];
                }

                if ((int)$address_book_id > 0) {
                    tep_db_perform(TABLE_PLATFORMS_ADDRESS_BOOK, $sql_data_array, 'update', "platform_id = '" . (int) $item_id . "' and platforms_address_book_id = '" . (int) $address_book_id . "'");
                    $activeaddress_book_ids[] = $address_book_id;
                } else {
                    tep_db_perform(TABLE_PLATFORMS_ADDRESS_BOOK, array_merge($sql_data_array, array('platform_id' => $item_id)));
                    $new_customers_address_id = tep_db_insert_id();
                    $activeaddress_book_ids[] = $new_customers_address_id;
                }


            }
            if (count($activeaddress_book_ids) > 0) {
                tep_db_query("delete from " . TABLE_PLATFORMS_ADDRESS_BOOK . " where platform_id = '" . (int) $item_id . "' and platforms_address_book_id NOT IN (" . implode(", ", $activeaddress_book_ids) . ")");
            }

            if ($is_default_address == 1) {

            }
            if (!$is_virtual && !$is_marketplace){
                $active_open_hours_ids = [];
                foreach ($platforms_open_hours_ids as $platforms_open_hours_key => $platforms_open_hours_id) {

                    $open_days = Yii::$app->request->post('open_days_' . $platforms_open_hours_keys[$platforms_open_hours_key]);
                    if ($open_days){
                        $sql_data_array = [
                            'open_days' => implode(",", $open_days),
                            'open_time_from' => $open_time_from[$platforms_open_hours_key],
                            'open_time_to' => $open_time_to[$platforms_open_hours_key],
                        ];
                        if ((int)$platforms_open_hours_id > 0) {
                            tep_db_perform(TABLE_PLATFORMS_OPEN_HOURS, $sql_data_array, 'update', "platform_id = '" . (int) $item_id . "' and platforms_open_hours_id = '" . (int) $platforms_open_hours_id . "'");
                            $active_open_hours_ids[] = $platforms_open_hours_id;
                        } else {
                            tep_db_perform(TABLE_PLATFORMS_OPEN_HOURS, array_merge($sql_data_array, array('platform_id' => $item_id)));
                            $new_open_hours_id = tep_db_insert_id();
                            $active_open_hours_ids[] = $new_open_hours_id;
                        }
                    }
                }
                if (count($active_open_hours_ids) > 0) {
                    tep_db_query("delete from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int) $item_id . "' and platforms_open_hours_id NOT IN (" . implode(", ", $active_open_hours_ids) . ")");
                }

                $active_cut_off_times_ids = [];
                if (is_array($platforms_cut_off_times_ids)) {
                    foreach ($platforms_cut_off_times_ids as $platforms_cut_off_times_key => $platforms_cut_off_times_id) {
                        $cut_off_times_days = Yii::$app->request->post('cut_off_times_days_' . $platforms_cut_off_times_keys[$platforms_cut_off_times_key]);
                        if (!is_array($cut_off_times_days)) {
                            $cut_off_times_days = [];
                        }
                        $sql_data_array = [
                            'cut_off_times_days' => implode(",", $cut_off_times_days),
                            'cut_off_times_today' => $cut_off_times_today[$platforms_cut_off_times_key],
                            'cut_off_times_next_day' => $cut_off_times_next_day[$platforms_cut_off_times_key],
                        ];

                        if ((int)$platforms_cut_off_times_id > 0) {
                            tep_db_perform(TABLE_PLATFORMS_CUT_OFF_TIMES, $sql_data_array, 'update', "platform_id = '" . (int) $item_id . "' and platforms_cut_off_times_id = '" . (int) $platforms_cut_off_times_id . "'");
                            $active_cut_off_times_ids[] = $platforms_cut_off_times_id;
                        } else {
                            tep_db_perform(TABLE_PLATFORMS_CUT_OFF_TIMES, array_merge($sql_data_array, array('platform_id' => $item_id)));
                            $active_cut_off_times_ids[] = tep_db_insert_id();
                        }
                    }
                }
                if (count($active_cut_off_times_ids) > 0) {
                    tep_db_query("delete from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where platform_id = '" . (int) $item_id . "' and platforms_cut_off_times_id NOT IN (" . implode(", ", $active_cut_off_times_ids) . ")");
                }
            }

            $theme_id = (int) Yii::$app->request->post('theme_id');
            tep_db_query("delete from " . TABLE_PLATFORMS_TO_THEMES . " where platform_id = '" . (int) $item_id . "'");
            if ($theme_id > 0) {
                $sql_data_array = [
                    'platform_id' => $item_id,
                    'theme_id' => $theme_id,
                    'is_default' => 1,
                ];
                tep_db_perform(TABLE_PLATFORMS_TO_THEMES, $sql_data_array);
            }

            $countries = Yii::$app->request->post('countries');
            $this->serviceCountry->deleteCountries($item_id);
            if (is_array($countries)) {
                $this->serviceCountry->saveCountries($countries, $item_id);
            }

            $selected_zones = Yii::$app->request->post('zones');
            $this->serviceZone->deleteZoneCountries($item_id);
            if (is_array($selected_zones)) {
                $this->serviceZone->saveZoneCountries($selected_zones, $item_id);
            }

            $platforms_locations = Yii::$app->request->post('platform_locations');
            if (!is_array($platforms_locations))
                $platforms_locations = [];
            $valid_locations_ids = [];
            foreach ($platforms_locations as $platforms_location) {
                $platforms_locations_id = (int) $platforms_location['platforms_locations_id'];
                if ($platforms_locations_id > 0) {
                    $check_valid = tep_db_fetch_array(tep_db_query(
                                    "SELECT COUNT(*) AS c " .
                                    "FROM " . TABLE_PLATFORMS_LOCATIONS . " " .
                                    "WHERE platforms_locations_id='" . (int) $platforms_locations_id . "' AND platform_id='" . (int) $item_id . "' "
                    ));
                    if ($check_valid['c'] == 0) {
                        $platforms_locations_id = 0;
                    }
                }
                if ($platforms_locations_id) {
                    tep_db_perform(TABLE_PLATFORMS_LOCATIONS, $platforms_location, 'update', "platforms_locations_id='" . (int) $platforms_locations_id . "'");
                } else {
                    $platforms_location['platform_id'] = (int) $item_id;
                    tep_db_perform(TABLE_PLATFORMS_LOCATIONS, $platforms_location);
                    $platforms_locations_id = intval(tep_db_insert_id());
                }
                $valid_locations_ids[] = $platforms_locations_id;
            }
            tep_db_query(
                    "DELETE FROM " . TABLE_PLATFORMS_LOCATIONS . " " .
                    "WHERE platform_id='" . (int) $item_id . "' " .
                    (count($valid_locations_ids) == 0 ? '' : "AND platforms_locations_id NOT IN('" . implode("','", $valid_locations_ids) . "') ")
            );

            if ( Yii::$app->request->post('platform_urls_present',0) ) {
                $platform_urls = tep_db_prepare_input(Yii::$app->request->post('platform_urls',[]));
                if ( !is_array($platform_urls) ) $platform_urls = [];
                $_valid_url_ids = [];
                foreach( $platform_urls as $platform_url ){
                    $platform_url_id = (int)$platform_url['platform_url_id'];

                    $platform_url['url'] = preg_replace('#^https?://#i','',$platform_url['url']);
                    $platform_url['url'] = rtrim($platform_url['url'],'/').'/';
                    if ($platform_url['url']=='/') continue;

                    $url_data = [
                        'url_type' => $platform_url['url_type'],
                        'status' => $platform_url['status']?1:0,
                        'url' => $platform_url['url'],
                        'ssl_enabled' => $platform_url['ssl_enabled'],
                    ];
                    if ( $platform_url_id ) {
                        $check_valid = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS c ".
                            "FROM ".TABLE_PLATFORMS_URL." ".
                            "WHERE platform_url_id='".(int)$platform_url_id."' AND platform_id='".(int)$item_id."' "
                        ));
                        if ( $check_valid['c']==0 ) {
                            $platform_url_id = 0;
                        }
                    }
                    if ( $platform_url_id ) {
                        tep_db_perform(TABLE_PLATFORMS_URL, $url_data, 'update', "platform_url_id='".(int)$platform_url_id."'");
                    }else{
                        $url_data['platform_id'] = (int)$item_id;
                        tep_db_perform(TABLE_PLATFORMS_URL,$url_data);
                        $platform_url_id = intval(tep_db_insert_id());
                    }
                    $_valid_url_ids[] = $platform_url_id;
                }
                tep_db_query(
                    "DELETE FROM ".TABLE_PLATFORMS_URL." ".
                    "WHERE platform_id='".(int)$item_id."' ".
                    (count($_valid_url_ids)==0?'':"AND platforms_url_id NOT IN('".implode("','",$_valid_url_ids)."') ")
                );
            }

            if (!$platform_updated) {
                \common\helpers\Configuration::copyPlatformModuleSetting($item_id);
            }

            $sql_data_array = [
                'platform_id' => (int)$item_id,
                'status' => (int)Yii::$app->request->post('watermark_status'),
                'top_left_watermark30' => Yii::$app->request->post('top_left_watermark30'),
                'top_watermark30' => Yii::$app->request->post('top_watermark30'),
                'top_right_watermark30' => Yii::$app->request->post('top_right_watermark30'),
                'left_watermark30' => Yii::$app->request->post('left_watermark30'),
                'watermark30' => Yii::$app->request->post('watermark30'),
                'right_watermark30' => Yii::$app->request->post('right_watermark30'),
                'bottom_left_watermark30' => Yii::$app->request->post('bottom_left_watermark30'),
                'bottom_watermark30' => Yii::$app->request->post('bottom_watermark30'),
                'bottom_right_watermark30' => Yii::$app->request->post('bottom_right_watermark30'),

                'top_left_watermark170' => Yii::$app->request->post('top_left_watermark170'),
                'top_watermark170' => Yii::$app->request->post('top_watermark170'),
                'top_right_watermark170' => Yii::$app->request->post('top_right_watermark170'),
                'left_watermark170' => Yii::$app->request->post('left_watermark170'),
                'watermark170' => Yii::$app->request->post('watermark170'),
                'right_watermark170' => Yii::$app->request->post('right_watermark170'),
                'bottom_left_watermark170' => Yii::$app->request->post('bottom_left_watermark170'),
                'bottom_watermark170' => Yii::$app->request->post('bottom_watermark170'),
                'bottom_right_watermark170' => Yii::$app->request->post('bottom_right_watermark170'),

                'top_left_watermark300' => Yii::$app->request->post('top_left_watermark300'),
                'top_watermark300' => Yii::$app->request->post('top_watermark300'),
                'top_right_watermark300' => Yii::$app->request->post('top_right_watermark300'),
                'left_watermark300' => Yii::$app->request->post('left_watermark300'),
                'watermark300' => Yii::$app->request->post('watermark300'),
                'right_watermark300' => Yii::$app->request->post('right_watermark300'),
                'bottom_left_watermark300' => Yii::$app->request->post('bottom_left_watermark300'),
                'bottom_watermark300' => Yii::$app->request->post('bottom_watermark300'),
                'bottom_right_watermark300' => Yii::$app->request->post('bottom_right_watermark300'),
            ];
            $check_watermark_query = tep_db_query("SELECT * FROM " . TABLE_PLATFORMS_WATERMARK . " WHERE platform_id=" . (int)$item_id);
            if ( tep_db_num_rows($check_watermark_query) > 0 ) {
                $prev_data = tep_db_fetch_array($check_watermark_query);
                if ( $prev_data['watermark30']!=$sql_data_array['watermark30'] ) {
                  \common\classes\Images::cacheKeyInvalidateByWatermark($prev_data['watermark30'],$item_id);
                }
                if ( $prev_data['watermark170']!=$sql_data_array['watermark170'] ) {
                  \common\classes\Images::cacheKeyInvalidateByWatermark($prev_data['watermark170'],$item_id);
                }
                if ( $prev_data['watermark300']!=$sql_data_array['watermark300'] ) {
                  \common\classes\Images::cacheKeyInvalidateByWatermark($prev_data['watermark300'],$item_id);
                }
                tep_db_perform(TABLE_PLATFORMS_WATERMARK, $sql_data_array, 'update', 'platform_id=' . (int)$item_id);
            } else {
                $sql_data_array['platform_id'] = (int)$item_id;
                tep_db_perform(TABLE_PLATFORMS_WATERMARK, $sql_data_array);
            }

            if ( (int)$item_id>0 ) {
                tep_db_query(
                    "INSERT IGNORE INTO " . TABLE_PLATFORMS_CATEGORIES . " (platform_id, categories_id) " .
                    "VALUES('" . (int)$item_id . "', 0)"
                );
            }

            $currency_margin = Yii::$app->request->post('currency_margin',[]);
            $this->currenciesMarginService->deleteCurrenciesMargin($item_id);
            if (is_array($currency_margin)) {
                $this->currenciesMarginService->saveCurrenciesMargin($currency_margin, $item_id);
            }

            if ($serverExt = \common\helpers\Acl::checkExtension('PlatformSoapServer', 'isPresent')) {
                $serverExt::onPlatformEditSave((int)$item_id);
            }

            $warehouse_status = Yii::$app->request->post('warehouse_status', []);
            $warehousesQuery = \common\models\Warehouses::find(['status' => 1])->asArray();
            foreach ($warehousesQuery->each() as $warehouses) {
                if (isset($warehouse_status[$warehouses['warehouse_id']]) && $warehouse_status[$warehouses['warehouse_id']] == 1) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $object = \common\models\WarehousesPlatforms::findOne(['warehouse_id' => $warehouses['warehouse_id'], 'platform_id' => (int)$item_id]);
                if (!is_object($object)) {
                    $object = new \common\models\WarehousesPlatforms();
                    $object->sort_order = 0;
                    $object->warehouse_id = $warehouses['warehouse_id'];
                    $object->platform_id = (int)$item_id;
                }
                $object->status = $status;
                $object->save();
            }

            if ($ext = \common\helpers\Acl::checkExtension('WarehousePriority', 'getInstance')) {
                $update_data_array = Yii::$app->request->post('priority',[]);
                if ( is_array($update_data_array) ) {
                    foreach ($update_data_array as $priorityClass => $update_data){
                        $ext::getInstance()->updateModule($priorityClass, $update_data);
                    }
                }
            }

            if ((int)$item_id > 0) {
                $platformId = (int)$item_id;
                tep_db_query(
                    'INSERT IGNORE INTO `platforms_configuration` (`configuration_title`, `configuration_key`, `configuration_value`,'
                        . ' `configuration_description`, `configuration_group_id`, `sort_order`, `last_modified`, `date_added`,'
                        . ' `use_function`, `set_function`, `platform_id`)'
                    . " VALUES ('Display billing address', 'DISPLAY_BILLING_ADDRESS', 'True', 'Display billing address on checkout and user account?',"
                        . " '1', '0', NOW(), NOW(), NULL, 'tep_cfg_select_option(array(\'True\', \'False\'), ', '{$platformId}');"
                );
            }
        }

        if( $error === TRUE ) {
            $messageType = 'warning';

            if( $message == '' ) $message = WARN_UNKNOWN_ERROR;
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
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div>
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>


    <?php
        echo '<script>location.replace("'. Yii::$app->urlManager->createUrl(['platforms/edit', 'id' => $item_id]) .'");</script>';
        die();
        return $this->actionEdit();
    }

    public function actionConfirmitemdelete()
    {
        \common\helpers\Translation::init('admin/properties');

        $this->layout = false;

        $item_id   = (int) Yii::$app->request->post( 'item_id' );


        $message   = $name = $title = '';
        $heading   = array();
        $contents  = array();
        $parent_id = 0;


       $groups_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
        $groups = tep_db_fetch_array($groups_query);
        $pInfo = new \objectInfo($groups);

        echo tep_draw_form( 'item_delete', FILENAME_INVENTORY, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"' );
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_PLATFORM . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_PLATFORM_INTRO . '</div>';
        echo '<div class="col_desc">' . $pInfo->platform_name . '</div>';
        ?>
        <p class="btn-toolbar">
            <?php
                echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
                echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

                echo tep_draw_hidden_field( 'item_id', $item_id );
            ?>
        </p>
        </form>
    <?php
    }

    public function actionItemdelete()
    {
        $this->layout = false;

        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $messageType = 'success';
        $message     = TEXT_INFO_DELETED;

        $check_is_default = tep_db_fetch_array(tep_db_query(
          "SELECT COUNT(*) AS c FROM ".TABLE_PLATFORMS." WHERE is_default=1 AND  platform_id = '" . (int)$item_id . "'"
        ));
        if ( $check_is_default['c'] ) {

        }else {

            tep_db_query("delete from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_ADDRESS_BOOK . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_TO_THEMES . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_PLATFORMS_CATEGORIES . " where platform_id = '" . (int)$item_id . "'");
            //TODO:
            // select plp.products_id, count(plp.platform_id) as used_count from platforms_products plp left join platforms_products p on p.products_id=plp.products_id where plp.platform_id=1 group by plp.products_id having used_count=1;

            tep_db_query("delete from " . TABLE_PLATFORMS_PRODUCTS . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_INFORMATION . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_BANNERS_TO_PLATFORM . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_BANNERS_LANGUAGES . " where platform_id = '" . (int)$item_id . "'");

            tep_db_query("delete from " . TABLE_PLATFORMS_CONFIGURATION . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORM_FORMATS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_HOLIDAYS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_PLATFORMS_WATERMARK . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from " . TABLE_META_TAGS . " where platform_id = '" . (int)$item_id . "'");
            tep_db_query("delete from platforms_price_settings where platform_id = '" . (int)$item_id . "'");
            PlatformsSettings::updateAll(['use_owner_prices' => \common\classes\platform::defaultId()], ['use_owner_prices' => $item_id]);
            PlatformsSettings::updateAll(['use_owner_descriptions' => \common\classes\platform::defaultId()], ['use_owner_descriptions' => $item_id]);

            \common\classes\Images::cacheKeyInvalidateByPlatformId((int)$item_id);

            //\common\models\EmailTemplatesToDesignTemplate::findAll(['platform_id' => (int)$item_id])->delete();
            \common\models\EmailTemplatesToDesignTemplate::deleteAll(['platform_id' => (int)$item_id]);
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
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div>
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>


        <p class="btn-toolbar">
            <?php
                echo '<input type="button" class="btn btn-primary" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
            ?>
        </p>
    <?php
    }

    public function actionConfirmItemCopy()
    {
        \common\helpers\Translation::init('admin/properties');

        $this->layout = false;

        $item_id   = (int) Yii::$app->request->post( 'item_id' );


        $message   = $name = $title = '';
        $heading   = array();
        $contents  = array();
        $parent_id = 0;


       $groups_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
        $groups = tep_db_fetch_array($groups_query);
        $pInfo = new \objectInfo($groups);

        echo tep_draw_form( 'item_copy', FILENAME_INVENTORY, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_copy" onSubmit="return copyItem();"' );
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_COPY_PLATFORM . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_COPY_PLATFORM_INTRO . '</div>';
        echo '<div class="col_desc">' . $pInfo->platform_name . '</div>';
        ?>
        <p class="btn-toolbar">
            <?php
                echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_COPY . '" >';
                echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

                echo tep_draw_hidden_field( 'item_id', $item_id );
            ?>
        </p>
        </form>
    <?php
    }

    public function actionItemCopy()
    {
        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $platforms_query = tep_db_query("select * from " . TABLE_PLATFORMS . " where platform_id = '" . (int)$item_id . "'");
        $platforms = tep_db_fetch_array($platforms_query);
        unset($platforms['platform_id']);
        $platforms['is_default'] = 0;
        $platforms['date_added'] = 'now()';
        tep_db_perform(TABLE_PLATFORMS, $platforms);
        $new_item_id = tep_db_insert_id();

        $platforms_address_query = tep_db_query("select * from " . TABLE_PLATFORMS_ADDRESS_BOOK . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_address = tep_db_fetch_array($platforms_address_query)) {
            unset($platforms_address['platforms_address_book_id']);
            $platforms_address['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_ADDRESS_BOOK, $platforms_address);
        }

        $platforms_categories_query = tep_db_query("select * from " . TABLE_PLATFORMS_CATEGORIES . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_categories = tep_db_fetch_array($platforms_categories_query)) {
            $platforms_categories['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_CATEGORIES, $platforms_categories);
        }

        $platforms_configuration_query = tep_db_query("select * from " . TABLE_PLATFORMS_CONFIGURATION . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_configuration = tep_db_fetch_array($platforms_configuration_query)) {
            unset($platforms_configuration['configuration_id']);
            $platforms_configuration['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_CONFIGURATION, $platforms_configuration);
        }

        tep_db_query("DELETE FROM " . TABLE_VISIBILITY_AREA . " WHERE platform_id='" . (int) $new_item_id . "'");
        $get_data_r = tep_db_query("SELECT * FROM " . TABLE_VISIBILITY_AREA . " WHERE platform_id='" . (int) $item_id . "' ");
        if (tep_db_num_rows($get_data_r) > 0) {
            while ($data = tep_db_fetch_array($get_data_r)) {
                $data['platform_id'] = (int) $new_item_id;
                tep_db_perform(TABLE_VISIBILITY_AREA, $data);
            }
        }

        $platforms_cut_off_times_query = tep_db_query("select * from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_cut_off_times = tep_db_fetch_array($platforms_cut_off_times_query)) {
            unset($platforms_cut_off_times['platforms_cut_off_times_id']);
            $platforms_cut_off_times['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_CUT_OFF_TIMES, $platforms_cut_off_times);
        }

        $platforms_formats_query = tep_db_query("select * from " . TABLE_PLATFORM_FORMATS . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_formats = tep_db_fetch_array($platforms_formats_query)) {
            unset($platforms_formats['paltform_formats_id']);
            $platforms_formats['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORM_FORMATS, $platforms_formats);
        }

        $platforms_holidays_query = tep_db_query("select * from " . TABLE_PLATFORMS_HOLIDAYS . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_holidays = tep_db_fetch_array($platforms_holidays_query)) {
            unset($platforms_holidays['platforms_holidays_id']);
            $platforms_holidays['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_HOLIDAYS, $platforms_holidays);
        }

        $platforms_open_hours_query = tep_db_query("select * from " . TABLE_PLATFORMS_OPEN_HOURS . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_open_hours = tep_db_fetch_array($platforms_open_hours_query)) {
            unset($platforms_open_hours['platforms_open_hours_id']);
            $platforms_open_hours['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_OPEN_HOURS, $platforms_open_hours);
        }

        $platforms_products_query = tep_db_query("select * from " . TABLE_PLATFORMS_PRODUCTS . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_products = tep_db_fetch_array($platforms_products_query)) {
            $platforms_products['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_PRODUCTS, $platforms_products);
        }

        $platforms_to_themes_query = tep_db_query("select * from " . TABLE_PLATFORMS_TO_THEMES . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_to_themes = tep_db_fetch_array($platforms_to_themes_query)) {
            $platforms_to_themes['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_TO_THEMES, $platforms_to_themes);
        }

        $platforms_watermark_query = tep_db_query("select * from " . TABLE_PLATFORMS_WATERMARK . " where platform_id = '" . (int)$item_id . "'");
        while ($platforms_watermark = tep_db_fetch_array($platforms_watermark_query)) {
            $platforms_watermark['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_PLATFORMS_WATERMARK, $platforms_watermark);
        }

        $information_query = tep_db_query("select * from " . TABLE_INFORMATION . " where platform_id = '" . (int)$item_id . "'");
        while ($information = tep_db_fetch_array($information_query)) {
            $information['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_INFORMATION, $information);
        }

        $meta_tags_query = tep_db_query("select * from " . TABLE_META_TAGS . " where platform_id = '" . (int)$item_id . "'");
        while ($meta_tags = tep_db_fetch_array($meta_tags_query)) {
            $meta_tags['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_META_TAGS, $meta_tags);
        }

        $banners_to_platform_query = tep_db_query("select * from " . TABLE_BANNERS_TO_PLATFORM . " where platform_id = '" . (int)$item_id . "'");
        while ($banners_to_platform = tep_db_fetch_array($banners_to_platform_query)) {
            $banners_to_platform['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_BANNERS_TO_PLATFORM, $banners_to_platform);
        }

        $banners_languages_query = tep_db_query("select * from " . TABLE_BANNERS_LANGUAGES . " where platform_id = '" . (int)$item_id . "'");
        while ($banners_languages = tep_db_fetch_array($banners_languages_query)) {
            unset($banners_languages['blang_id']);
            $banners_languages['platform_id'] = $new_item_id;
            tep_db_perform(TABLE_BANNERS_LANGUAGES, $banners_languages);
        }

        \common\classes\Images::cacheKeyInvalidateByPlatformId((int)$new_item_id);

    }

    public function actionAddtheme()
    {
        $get   = Yii::$app->request->get();

        $this->layout = false;
        $results = tep_db_query("select * from " . TABLE_THEMES);
        $tInfo = array();
        while($results_array = tep_db_fetch_array ($results)){
                $tInfo[] = array(
                    'id'=> $results_array['id'],
                    'theme_name'=> $results_array['theme_name'],
                    'title'=> $results_array['title'],
                    'description'=> $results_array['description']
                );
        }
        return $this->render('addtheme.tpl', [
                'results' => $tInfo,
                'mobile' => $get['mobile'] ? true : false
            ]
        );
    }

  public function actionEditCatalog()
  {
      \common\helpers\Translation::init('admin/platforms');

      $platform_id   = (int) Yii::$app->request->get('id');

      $this->layout = false;

      $assigned = $this->get_assigned_catalog($platform_id, true);

      $tree_init_data = $this->load_tree_slice($platform_id,0);
      foreach ($tree_init_data as $_idx=>$_data) {
          if ( isset($assigned[$_data['key']]) ){
              $tree_init_data[$_idx]['selected'] = true;
          }
      }

      $selected_data = json_encode($assigned);

      return $this->render('edit-catalog.tpl', [
        'selected_data' => $selected_data,
        'tree_data' => $tree_init_data,
        'tree_server_url' => Yii::$app->urlManager->createUrl(['platforms/load-tree', 'platform_id' => $platform_id]),
        'tree_server_save_url' => Yii::$app->urlManager->createUrl(['platforms/update-catalog-selection', 'platform_id' => $platform_id])
      ]);
  }

  private function get_assigned_catalog($platform_id,$validate=false){
    return \common\helpers\Categories::get_assigned_catalog($platform_id,$validate);
  }

  private function load_tree_slice($platform_id, $category_id){
    return \common\helpers\Categories::load_tree_slice($platform_id, $category_id);
  }

  private function tep_get_category_children(&$children, $platform_id, $categories_id) {
    if ( !is_array($children) ) $children = array();
    foreach($this->load_tree_slice($platform_id, $categories_id) as $item) {
      $key = $item['key'];
      $children[] = $key;
      if ($item['folder']) {
        $this->tep_get_category_children($children, $platform_id, intval(substr($item['key'],1)));
      }
    }
  }

  public function actionLoadTree()
  {
      \common\helpers\Translation::init('admin/platforms');
      $this->layout = false;

      $platform_id = Yii::$app->request->get('platform_id');
      $do = Yii::$app->request->post('do','');

      $response_data = array();

      if ( $do == 'missing_lazy' ) {
        $category_id = Yii::$app->request->post('id');
        $selected = Yii::$app->request->post('selected');
        $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
        $selected_data = json_decode($req_selected_data,true);
        if ( !is_array($selected_data) ) {
          $selected_data = json_decode($selected_data,true);
        }

        if (substr($category_id, 0, 1) == 'c') $category_id = intval(substr($category_id, 1));

        $response_data['tree_data'] = $this->load_tree_slice($platform_id,$category_id);
        foreach( $response_data['tree_data'] as $_idx=>$_data ) {
          $response_data['tree_data'][$_idx]['selected'] = isset($selected_data[$_data['key']]);
        }
        $response_data = $response_data['tree_data'];
      }

      if ( $do == 'update_selected' ) {
        $id = Yii::$app->request->post('id');
        $selected = Yii::$app->request->post('selected');
        $select_children = Yii::$app->request->post('select_children');
        $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
        $selected_data = json_decode($req_selected_data,true);
        if ( !is_array($selected_data) ) {
          $selected_data = json_decode($selected_data,true);
        }

        if ( substr($id,0,1)=='p' ) {
          list($ppid, $cat_id) = explode('_',$id,2);
          if ( $selected ) {
            // check parent categories
            $parent_ids = array((int)$cat_id);
            \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
            foreach( $parent_ids as $parent_id ) {
              if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                $response_data['update_selection']['c'.(int)$parent_id] = true;
                $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
              }
            }
            if ( !isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = true;
              $selected_data[$id] = $id;
            }
          }else{
            if ( isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = false;
              unset($selected_data[$id]);
            }
          }
        }elseif ( substr($id,0,1)=='c' ) {
          $cat_id = (int)substr($id,1);
          if ( $selected ) {
            $parent_ids = array((int)$cat_id);
            \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
            foreach( $parent_ids as $parent_id ) {
              if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                $response_data['update_selection']['c'.(int)$parent_id] = true;
                $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
              }
            }
            if ( $select_children ) {
              $children = array();
              $this->tep_get_category_children($children,$platform_id,$cat_id);
              foreach($children as $child_key){
                if ( !isset($selected_data[$child_key]) ) {
                  $response_data['update_selection'][$child_key] = true;
                  $selected_data[$child_key] = $child_key;
                }
              }
            }
            if ( !isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = true;
              $selected_data[$id] = $id;
            }
          }else{
            $children = array();
            $this->tep_get_category_children($children,$platform_id,$cat_id);
            foreach($children as $child_key){
              if ( isset($selected_data[$child_key]) ) {
                $response_data['update_selection'][$child_key] = false;
                unset($selected_data[$child_key]);
              }
            }
            if ( isset($selected_data[$id]) ) {
              $response_data['update_selection'][$id] = false;
              unset($selected_data[$id]);
            }
          }
        }

        $response_data['selected_data'] = $selected_data;
      }

      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $response_data;

  }

  function actionUpdateCatalogSelection()
  {
    \common\helpers\Translation::init('admin/platforms');
    $this->layout = false;

    $platform_id = Yii::$app->request->get('platform_id');
    $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
    $selected_data = json_decode($req_selected_data,true);
    if ( !is_array($selected_data) ) {
      $selected_data = json_decode($selected_data,true);
    }
    if ( !isset($selected_data['c0']) ) $selected_data['c0'] = 'c0';

    $assigned = $this->get_assigned_catalog($platform_id);
    $assigned_products = array();
    foreach ( $assigned as $assigned_key ) {
      if ( substr($assigned_key,0,1)=='p' ) {
        $pid = intval(substr($assigned_key,1));
        $assigned_products[$pid] = $pid;
        unset($assigned[$assigned_key]);
      }
    }
    if (is_array($selected_data)) {
      $selected_products = array();
      foreach( $selected_data as $selection ) {
        if ( substr($selection,0,1)=='p' ) {
          $pid = intval(substr($selection,1));
          $selected_products[$pid] = $pid;
          continue;
        }
        if (isset($assigned[$selection])){
          unset($assigned[$selection]);
        }else{
          if ( substr($selection,0,1)=='c' ) {
            $cat_id = (int)substr($selection, 1);
            tep_db_perform(TABLE_PLATFORMS_CATEGORIES,array(
              'platform_id' => $platform_id,
              'categories_id' => $cat_id,
            ));
            unset($assigned[$selection]);
          }
        }
      }
      foreach( $selected_products as $pid ) {
        if (isset($assigned_products[$pid])) {
          unset($assigned_products[$pid]);
        }else{
          tep_db_perform(TABLE_PLATFORMS_PRODUCTS,array(
            'platform_id' => $platform_id,
            'products_id' => $pid,
          ));
        }
      }
    }

    foreach ($assigned as $clean_key) {
      if ( substr($clean_key,0,1)=='c' ) {
        $cat_id = (int)substr($clean_key, 1);
        if ( $cat_id==0 ) continue;
        tep_db_query(
          "DELETE FROM ".TABLE_PLATFORMS_CATEGORIES." ".
          "WHERE platform_id ='".$platform_id."' AND categories_id = '".$cat_id."' "
        );
        unset($assigned[$clean_key]);
      }
    }
    if ( count($assigned_products)>1000 ) {
      foreach( $assigned_products as $assigned_product_id ) {
        tep_db_query(
          "DELETE FROM ".TABLE_PLATFORMS_PRODUCTS." ".
          "WHERE platform_id ='".$platform_id."' AND products_id = '".$assigned_product_id."' "
        );
      }
    }elseif( count($assigned_products)>0 ){
      tep_db_query(
        "DELETE FROM ".TABLE_PLATFORMS_PRODUCTS." ".
        "WHERE platform_id ='".$platform_id."' AND products_id IN ('".implode("','",$assigned_products)."') "
      );
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    Yii::$app->response->data = array(
      'status' => 'ok'
    );

  }

  public function actionDefineFormats(){
      \common\helpers\Translation::init('admin/languages');
      \common\helpers\Translation::init('admin/texts');

      exec("locale -a", $output);

      if (Yii::$app->request->isPost){
        //echo '<pre>';print_r($_POST);die;
        $id = Yii::$app->request->post('id',0);
        if ($id){
          if (is_array($_POST['configuration_key']) && count($_POST['configuration_key']) > 0){
            foreach($_POST['configuration_key'] as $lang => $data){
              tep_db_query("delete from " . TABLE_PLATFORM_FORMATS . " where platform_id='" . (int)$id . "' and language_id = '" . (int)$lang . "'");
              foreach($data as $key => $value){
                if (!tep_not_null($value) || !isset($_POST['configuration_value'][$lang][$key]) || !tep_not_null($_POST['configuration_value'][$lang][$key])) continue;
                tep_db_query("insert into " . TABLE_PLATFORM_FORMATS . " (configuration_key, configuration_value, platform_id, language_id) values ('" . tep_db_input($value) . "', '" . tep_db_input($_POST['configuration_value'][$lang][$key]) . "', '" . (int)$id . "', '" . (int)$lang . "')");
              }
            }
          }
        }
        $messageType = 'success';
        $message = TEXT_MESSEAGE_SUCCESS;
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
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div>
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
<?php
        return $this->actionEdit();
      } else {
        $id = Yii::$app->request->get('id',0);
      }

      if (is_array($output) && class_exists('\ResourceBundle')){
        $all_locales = \ResourceBundle::getLocales ('');
        $lList = [];
        foreach($output as $line){
          if (tep_not_null($line)){
            $ex = explode(".", $line);
            if (in_array($ex[0], $all_locales)){
              array_push($lList, ['id' => $ex[0], 'text' => $ex[0]]);
            }
          }
        }
      }
      if (count($lList) == 0 ){$lList[] = ['id'=> 'en_EN', 'text' => 'en_EN'];}

      $l_formats = [];
      $formats_query = tep_db_query("select * from " . TABLE_LANGUAGES_FORMATS . " where 1");
      if (tep_db_num_rows($formats_query)){
        while($row = tep_db_fetch_array($formats_query)){
          $l_formats[] = $row;
        }
      }

      $p_formats = [];
      $formats_query = tep_db_query("select * from " . TABLE_PLATFORM_FORMATS . " where platform_id = '" . (int)$id . "'");
      if (tep_db_num_rows($formats_query)){
        while($row = tep_db_fetch_array($formats_query)){
          $p_formats[] = $row;
        }
      }
      tep_db_free_result($formats_query);

      return $this->renderAjax('formats.tpl', [
        'languages' => \common\helpers\Language::get_languages(),
        'lList' => $lList,
        'platform_id' => $id,
        'platform_formats' => \yii\helpers\ArrayHelper::map($p_formats, 'configuration_key', 'configuration_value', 'language_id'),
        'defined_formats' => \yii\helpers\ArrayHelper::map($l_formats, 'configuration_key', 'configuration_value', 'language_id'),
      ]);
  }

  public function actionSortOrder()
  {
    $moved_id = (int)$_POST['sort_top'];
    $type = Yii::$app->request->post('type', 'physical');
    $ref_array = (isset($_POST['top']) && is_array($_POST['top']))?array_map('intval',$_POST['top']):array();
    if ( $moved_id && in_array($moved_id, $ref_array) ) {
      // {{ normalize
      $order_counter = 0;
      $platforms = Platforms::getPlatformsByType($type)->orderBy("sort_order, platform_name")->all();
      if ($platforms){
          foreach($platforms as $platform){
              $order_counter++;
              $platform->sort_order = $order_counter;
              $platform->save();
          }
      }
      // }} normalize
      $get_current_order_r = tep_db_query(
        "SELECT platform_id, sort_order ".
        "FROM ".TABLE_PLATFORMS." ".
        "WHERE platform_id IN('".implode("','",$ref_array)."') ".
        "ORDER BY sort_order"
      );
      $ref_ids = array();
      $ref_so = array();
      while($_current_order = tep_db_fetch_array($get_current_order_r)){
        $ref_ids[] = (int)$_current_order['platform_id'];
        $ref_so[] = (int)$_current_order['sort_order'];
      }

      foreach( $ref_array as $_idx=>$id ) {
        tep_db_query("UPDATE ".TABLE_PLATFORMS." SET sort_order='{$ref_so[$_idx]}' WHERE platform_id='{$id}' ");
      }

    }
  }

  public function actionFileManagerUpload() {
        $text = '';
        if (isset($_FILES['files'])) {
            $path = DIR_FS_CATALOG . 'images/stamp/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $uploadfile = $path . basename($_FILES['files']['name']);

            if (move_uploaded_file($_FILES['files']['tmp_name'], $uploadfile)) {
              \common\classes\Images::cacheKeyInvalidateByWatermark(basename($uploadfile)); // override existing file

              $text = $_FILES['files']['name'];
            }
        }
        echo $text;
    }

    public function actionConfiguration() {
        \common\helpers\Translation::init('configuration');

        $platform_id = (int) Yii::$app->request->get('platform_id');
        $languages_id = \Yii::$app->settings->get('languages_id');

        $formats_query = tep_db_query("select platform_name from " . TABLE_PLATFORMS . " where platform_id = '" . (int) $platform_id . "'");
        $formats = tep_db_fetch_array($formats_query);

        $this->selectedMenu = array('fronends', 'platforms');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('platforms/configuration'), 'title' => BOX_HEADING_CONFIGURATION . "::" . $formats['platform_name']);
        $this->view->headingTitle = BOX_HEADING_CONFIGURATION . "::" . $formats['platform_name'];

        $this->view->adminTable = array(
            array(
                'title' => TEXT_TABLE_TITLE,
                'not_important' => 0
            ),
            array(
                'title' => TEXT_TABLE_VALUE,
                'not_important' => 0
            )
        );

        $filterEntity = [];
        $group_query = tep_db_query("select configuration_group_id from platforms_configuration where platform_id=" . $platform_id . ' group by configuration_group_id');
        while ($group = tep_db_fetch_array($group_query)) {
            $title = \common\helpers\Translation::getTranslationValue($group['configuration_group_id'], 'admin/main', $languages_id);
            if (tep_not_null($title)) {
                $group['configuration_group_title'] = $title;
            } else {
                $group['configuration_group_title'] = $group['configuration_group_id'];
            }
            $filterEntity[] = [
                'id' => $group['configuration_group_id'],
                'text' => $group['configuration_group_title'],
            ];
        }

        $this->view->row = (int) $_GET['row'];
        $this->view->filterGroups = tep_draw_pull_down_menu('group_id', $filterEntity, (isset($_GET['group_id']) ? $_GET['group_id'] : 1), 'class="form-control" onchange="return applyFilter();"');

        $this->view->platform_id = $platform_id;

        return $this->render('configuration');
    }

    function actionGetgroupcontent() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->layout = false;
        $customers_query_numrows = 1;

        $draw = (int) Yii::$app->request->get('draw');
        $start = (int) Yii::$app->request->get('start');
        $length = (int) Yii::$app->request->get('length');

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);

        $groupid = (string) $filter['group_id'];
        $platform_id = (int) $filter['platform_id'];

        $responseList = array();
        $extra_html = '';

        $search = '';
        $search_condition = " where 1 ";
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            if (is_array($_GET['search'])) {
                if (isset($_GET['search']['value'])) {
                    if (trim($_GET['search']['value']) != '') {
                        $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
                        $search_condition = " where (configuration_title like '%" . $keywords . "%' or configuration_description like '%" . $keywords . "%' )";
                    }
                }
            }
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "configuration_title " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "configuration_description " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "sort_order";
                    break;
            }
        } else {
            $orderBy = "sort_order";
        }

        $_query = "select configuration_id, configuration_title, configuration_value, use_function, configuration_key
                   from " . TABLE_PLATFORMS_CONFIGURATION . "

                    $search_condition
                    and configuration_group_id = '" . $groupid . "'
                    and platform_id = '" . (int) $platform_id . "'
                    order by $orderBy ";


        $current_page_number = ( $start / $length ) + 1;
        $db_split = new \splitPageResults($current_page_number, $length, $_query, $configuration_query_numrows, 'configuration_id');

        $configuration_query = tep_db_query($_query);
        while ($configuration = tep_db_fetch_array($configuration_query)) {

            if (tep_not_null($configuration['use_function'])) {
                $use_function = $configuration['use_function'];
                if (preg_match('/->/', $use_function)) {

                    $class_method = explode('->', $use_function);

                    if (!is_object(${$class_method[0]})) {

                        require( DIR_WS_CLASSES . 'currencies.php' );
                        ${$class_method[0]} = new $class_method[0]();
                    }

                    $cfgValue = tep_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
                } else {
                    if (method_exists('backend\models\Configuration', $use_function)) {
                        $cfgValue = call_user_func(array('backend\models\Configuration', $use_function), $configuration['configuration_value']);
                    } else if (function_exists($use_function)) {
                        $cfgValue = tep_call_function($use_function, $configuration['configuration_value']);
                    }
                }
            } else {
                $_t = \common\helpers\Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $configuration['configuration_value'])), 'configuration', $languages_id);
                $_t = (tep_not_null($_t) ? $_t : $configuration['configuration_value']);
                $cfgValue = $_t;
            }

            $cfg_extra_query = tep_db_query("select configuration_key, configuration_description, date_added, last_modified, use_function, set_function from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_id = '" . (int) $configuration['configuration_id'] . "'");
            $cfg_extra = tep_db_fetch_array($cfg_extra_query);

            $cInfo_array = array_merge($configuration, $cfg_extra);

            if ($configuration['configuration_key'] == 'STORE_COUNTRY') {
                $cfgValue = \common\helpers\Country::get_country_name($configuration['configuration_value']);
            }

            if ($configuration['configuration_key'] == 'DOWNLOADS_CONTROLLER_ORDERS_STATUS' || $configuration['configuration_key'] == 'AFFILIATE_PAYMENT_ORDER_MIN_STATUS') {
                $extra_html = \common\helpers\Order::get_status_name($cfgValue);
            } elseif ($configuration['configuration_key'] == 'DEFAULT_USER_GROUP' || $configuration['configuration_key'] == 'DEFAULT_USER_LOGIN_GROUP') {
                $extra_html = \common\helpers\Group::get_user_group_name($cfgValue);
            } else {
                $extra_html = htmlspecialchars($cfgValue);
            }

            if (strip_tags(trim(strtolower($extra_html))) === strip_tags(trim(strtolower($cfgValue))))
                $extra_html = '';

            $title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);
            if (!tep_not_null($title)) {
                $title = $cInfo_array['configuration_title'];
            }

            $responseList[] = array(
                $title . "<input class='cell_identify' type='hidden' value='" . $cInfo_array['configuration_id'] . "' />",
                $cfgValue . "<br/> $extra_html "
            );
        }

        $configuration_query_numrows1 = 0;

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $configuration_query_numrows + $configuration_query_numrows1,
            'recordsFiltered' => $configuration_query_numrows + $configuration_query_numrows1,
            'data' => $responseList
        );

        echo json_encode($response);
    }

    function actionPreedit() {
        global $access_levels_id;
        $this->layout = FALSE;

        $languages_id = \Yii::$app->settings->get('languages_id');

        $param_id = (int) Yii::$app->request->post('param_id');
        $platform_id = (int) Yii::$app->request->post('platform_id');

        $table = TABLE_PLATFORMS_CONFIGURATION;

        $_query = "select * from " . $table . " where configuration_id = '$param_id'";
        $configuration_query = tep_db_query($_query);
        $configuration = tep_db_fetch_array($configuration_query);
        
        $group_id = $configuration['configuration_group_id'];

        if (!is_array($configuration)) {
            return;
        }

        $title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);
        if (tep_not_null($title)) {
            $configuration['configuration_title'] = $title;
        }
        ?>
                <div class="or_box_head"> <?php echo $configuration['configuration_title']; ?></div>
                <div class="row_or"><?php echo '<div>' . TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($configuration['date_added']); ?></div></div>

                <input name="param_id" type="hidden" value="<?php echo $param_id; ?>">
                <input name="group_id" type="hidden" value="<?php echo $group_id; ?>">

                <div class="btn-toolbar btn-toolbar-order">
                    <button class="btn btn-primary btn-process-order btn-edit" onclick="return editItem( <?php echo "$param_id, $platform_id"; ?>)"><?php echo IMAGE_EDIT; ?></button>
        <?php
        if ($access_levels_id == 1) {
            ?>
                              <button class="btn btn-process-order btn-delete" onclick="return deleteTrashedItem( <?php echo "$param_id, $platform_id"; ?>)"><?php echo IMAGE_DELETE; ?></button>
            <?php
        }
        ?>
                </div>
        <?php
    }

    function actionGetparam() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->layout = FALSE;

        $param_id = Yii::$app->request->post('param_id');

        $_query = "select * from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_id = '$param_id'";
        $configuration_query = tep_db_query($_query);
        $configuration = tep_db_fetch_array($configuration_query);
        
        $group_id = $configuration['configuration_group_id'];

        if (!is_array($configuration))
            die("Wrong data");

        $method = trim(strtolower(substr($configuration['set_function'], 0, strpos($configuration['set_function'], '('))));

        if ((string) $configuration['set_function'] && method_exists('backend\models\Configuration', $method)) {

            $_args = preg_replace("/" . $method . "[\s\(]*/i", "", $configuration['set_function']) . "'" . htmlspecialchars($configuration['configuration_value']) . "', '" . $configuration['configuration_key'] . "'";

            $value_field = call_user_func(array('backend\models\Configuration', $method), $_args);

            /*
              if( strpos( $configuration['set_function'], 'tep_cfg_select_multioption' ) !== FALSE ) {
              eval( '$value_field = ' . $configuration['set_function'] . '"' . htmlspecialchars( $configuration['configuration_value'] ) . '","' . $configuration['configuration_key'] . '");' );
              } else {
              eval( '$value_field = ' . $configuration['set_function'] . '"' . htmlspecialchars( $configuration['configuration_value'] ) . '");' );
              } */
        } else {
            $value_field = tep_draw_input_field('configuration_value', $configuration['configuration_value'], 'class="form-control"');
        }

        $translated_title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);

        echo tep_draw_form(
                'save_param_form', 'configuration/index', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_param_form" onSubmit="return saveParam();"') .
        tep_draw_hidden_field('group_id', $group_id) .
        tep_draw_hidden_field('param_id', $param_id) .
        tep_draw_hidden_field('configuration_key', $configuration['configuration_key']);

        $languages = \common\helpers\Language::get_languages(true);

        $title = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_TITLE', 'configuration', $languages_id);
        if (tep_not_null($title)) {
            $configuration['configuration_title'] = $title;
        }
        $description = \common\helpers\Translation::getTranslationValue($configuration['configuration_key'] . '_DESC', 'configuration', $languages_id);
        if (tep_not_null($description)) {
            $configuration['configuration_description'] = $description;
        }
        ?>
        				<div class="or_box_head"><?php echo $configuration['configuration_title']; ?></div>
        				<div class="row_or dataTableContent"><?php echo $configuration['configuration_description']; ?></div>
        				<div class="row_or dataTableContent"><?= $value_field ?></div>
        <?php
        if (!tep_not_null($translated_title)) {
            ?>
                    <br>
                    <div class="row_or dataTableContent">
                        <div class="tab-pane">
                            <div class="tabbable tabbable-custom">
                                <ul class="nav nav-tabs">
            <?php foreach ($languages as $lKey => $lItem) { ?>
                                        <li <?php if ($lKey == 0) { ?> class="active"<?php } ?> ><a href="#tab_2_<?= $lItem['id'] ?>" class="flag-span" data-toggle="tab"><?= $lItem['image']; ?><span><?= $lItem['name'] ?></span></a></li>
            <?php } ?>
                                </ul>
                                <div class="tab-content">
            <?php foreach ($languages as $lKey => $lItem) { ?>
                                        <div class="tab-pane<?php if ($lKey == 0) { ?>  active<?php } ?>" id="tab_2_<?= $lItem['id'] ?>">
                                            <div class="">
                                                <label><?= \common\helpers\Translation::getTranslationValue('TEXT_TITLE', 'admin/main', $lItem['id']) ?></label>
                <?php echo Html::textInput($configuration['configuration_key'] . '_TITLE[' . $lItem['id'] . ']', $configuration['configuration_title']); ?>
                                            </div>
                                            <div class="">
                                                <label><?= \common\helpers\Translation::getTranslationValue('TEXT_DESCRIPTION', 'admin/main', $lItem['id']) ?></label>
                <?php echo Html::textarea($configuration['configuration_key'] . '_DESC[' . $lItem['id'] . ']', $configuration['configuration_description']) ?>
                                            </div>
                                        </div>
            <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
        <?php } ?>
        				<div class="btn-toolbar btn-toolbar-order">
        					<button class="btn btn-no-margin"><?php echo IMAGE_UPDATE; ?></button><button class="btn" onclick="return resetStatement()"><?php echo IMAGE_BACK; ?></button>
        				</div>
                </form>
        <?php
    }

    public function actionDeleteParam() {

        $configuration_id = (int) Yii::$app->request->post('param_id');
        tep_db_query("delete from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_id = $configuration_id");
        if (TRUE) {
            $message = TEXT_PARAM_CHANGE_SUCCESS;
        }

        if ($error === TRUE) {
            $messageType = 'warning';
        }

        if ($message != '') {
            ?>
                        <div class="popup-box-wrap pop-mess">
                        <div class="around-pop-up"></div>
                        <div class="popup-box">
                            <div class="pop-up-close pop-up-close-alert"></div>
                            <div class="pop-up-content">
                                <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                                <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
            <?= $message ?>
                                </div>
                            </div>
                            <div class="noti-btn">
                                <div></div>
                                <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                            </div>
                        </div>
                        <script>
                        $('body').scrollTop(0);
                        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                            $(this).parents('.pop-mess').remove();
                            resetStatement();
                        });
                    </script>
                    </div>
            <?php
        }
    }

    function actionSaveparam() {

        $this->layout = FALSE;
        $error = FALSE;
        $message = '';
        $messageType = 'success';
        $html = "";

        $configuration_id = (int) Yii::$app->request->post('param_id');
        $configuration_key = Yii::$app->request->post('configuration_key');
        $configuration_value = Yii::$app->request->post('configuration_value');
        $configuration = Yii::$app->request->post('configuration');

        if (is_array($configuration_value)) {
            $configuration_value = implode(", ", $configuration_value);
            $configuration_value = preg_replace("/, --none--/", "", $configuration_value);
        } elseif (is_array($configuration)) {
            $configuration_value = $configuration[$configuration_key];
        }
        tep_db_query("update " . TABLE_PLATFORMS_CONFIGURATION . "
          set configuration_value = '" . tep_db_input(tep_db_prepare_input($configuration_value)) . "', last_modified = now()
          where configuration_id = '" . $configuration_id . "'");

        if (is_array($_POST)) {
            foreach (tep_db_prepare_input($_POST) as $translation_key => $value) {
                if (strpos($translation_key, 'TITLE') !== false || strpos($translation_key, 'DESC') !== false) {
                    if (is_array($value)) {
                        foreach ($value as $language_id => $translation_value) {
                            \common\helpers\Translation::setTranslationValue($translation_key, 'configuration', $language_id, $translation_value);
                        }
                    } else {
                        $language_id = key($value);
                        $translation_value = current($value);
                        \common\helpers\Translation::setTranslationValue($translation_key, 'configuration', $language_id, $translation_value);
                    }
                }
            }
        }

        // TODO Check if there were no MySql errors
        if (TRUE) {
            $message = TEXT_PARAM_CHANGE_SUCCESS;
        }

        if ($error === TRUE) {
            $messageType = 'warning';
        }

        if ($message != '') {
            ?>
                        <div class="popup-box-wrap pop-mess">
                        <div class="around-pop-up"></div>
                        <div class="popup-box">
                            <div class="pop-up-close pop-up-close-alert"></div>
                            <div class="pop-up-content">
                                <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                                <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
            <?= $message ?>
                                </div>
                            </div>
                            <div class="noti-btn">
                                <div></div>
                                <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                            </div>
                        </div>
                        <script>
                        $('body').scrollTop(0);
                        $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                            $(this).parents('.pop-mess').remove();
                            //location.reload();
                            resetStatement();
                        });
                    </script>
                    </div>

            <?= $html ?>
            <?php
        }

        $this->actionGetParam();
    }

    public function actionHolidays(){
        \common\helpers\Translation::init('admin/platforms');
        $this->view->holidaysTable = array(
            array(
                'title' => TABLE_HEADING_DAY,
                'not_important' => 1
            ),
        );

        $platform_id = Yii::$app->request->get('platform_id');

        if (Yii::$app->request->isPost){
            $hdate = Yii::$app->request->post('hdate', []);
            $platform_id = Yii::$app->request->post('platform_id');
            if ($platform_id){
                $action = Yii::$app->request->post('action');
                $search = Yii::$app->request->post('search');
                if ($action == 'load'){
                 $dates = \common\helpers\Date::getHolidays($platform_id, DATE_FORMAT_DATEPICKER_PHP, $search);
                 echo json_encode($dates);
                 exit();
                }
                tep_db_query("delete from " . TABLE_PLATFORMS_HOLIDAYS . " where platform_id = '" . (int)$platform_id . "' and year(holidate) = '" . tep_db_input($search). "'");
                if (is_array($hdate)){
                    foreach($hdate as $date){
                        if (($_d = \common\helpers\Date::date_raw($date)) !== false  && !empty($date)){
                            $sql_data_array = [
                                'platform_id' => $platform_id,
                                'holidate' => $_d,
                            ];
                            tep_db_perform(TABLE_PLATFORMS_HOLIDAYS, $sql_data_array);
                        }
                    }
                }
            }
            echo json_encode(['messageType' => 'success', 'message' => TEXT_MESSEAGE_SUCCESS]);
            exit();
        }

        $dates = \common\helpers\Date::getHolidays($platform_id, DATE_FORMAT_DATEPICKER_PHP, date("Y"));

        return $this->renderAjax('holidays', [
            'platform_id' => $platform_id,
            'dates' => $dates,
            'hyear' =>date("Y"),
        ]);
    }

    public function actionGenerateKey(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['platform_code' => \common\helpers\Password::create_random_value(6)];
    }

}

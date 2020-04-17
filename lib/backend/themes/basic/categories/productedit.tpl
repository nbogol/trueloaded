{use class="yii\helpers\Html"}
{use class="\common\classes\Images"}
{use class="backend\components\Currencies"}
{use class="\common\classes\platform"}
{use class="\common\classes\department"}
{include file='../assets/tabs.tpl' scope="global"}
{\backend\assets\MultiSelectAsset::register($this)|void}

{Currencies::widget()}
{if $editProductBundleSwitcher || $infoBreadCrumb || $infoSubProducts}
  <div class="row">
{/if}
{if $editProductBundleSwitcher }
    <div class="btn-box-inv-price btn-is-bundle" style="float: right;"><span data-value="1">{$smarty.const.TEXT_BUNDLE_PRODUCT}</span><span data-value="0">{$smarty.const.TEXT_REGULAR_PRODUCT}</span></div>
{/if}
{if $infoBreadCrumb}
    <div class="breadcrumb-additional_info breadcrumb-for-product">{$infoBreadCrumb}</div>
{/if}
{if $infoSubProducts}
  <div class="breadcrumb-additional_info breadcrumb-for-product">{$infoSubProducts}</div>
{/if}
{if $editProductBundleSwitcher || $infoBreadCrumb || $infoSubProducts}
  </div>
{/if}

<link href="{$app->view->theme->baseUrl}/css/product-edit.css" rel="stylesheet" type="text/css" />

<form action="{Yii::$app->urlManager->createUrl('categories/product-submit')}" method="post" enctype="multipart/form-data" id="save_product_form" class="{if $pInfo->parent_products_id and $pInfo->products_id_price==$pInfo->parent_products_id} disable-product-price-data {/if}" name="product_edit" onSubmit="return saveProduct();">
<button type="submit" style="display:none"></button>
    {if $pInfo->parent_products_id}
        <input type="hidden" name="products_id_price" value="{$pInfo->products_id_price}" id="ProductIdPrice">
    {/if}
{tep_draw_hidden_field( 'products_id', $pInfo->products_id )}
{tep_draw_hidden_field( 'categories_id', $categories_id )}
{tep_draw_hidden_field( 'department_id', $selected_department_id )}
{Html::hiddenInput( 'parent_products_id', $pInfo->parent_products_id )}
{if $TabAccess->tabView('TAB_BUNDLES')}
{tep_draw_hidden_field( 'is_bundle', $pInfo->is_bundle, 'id="is_bundle"' )}
<script type="text/javascript">
  (function(){
    $(function(){
      var is_bundle = $('#is_bundle');
      var btn_is_bundle = $('.btn-is-bundle span');
      var isProductBundle = $('.is-product-bundle');
      btn_is_bundle.each(function(){
        $(this).on('click', function(){
          btn_is_bundle.removeClass('active');
          $(this).addClass('active');
          is_bundle.val($(this).data('value'));
          if ($(this).data('value')) {
            $('.is-bundle').show();
            $('.is-not-bundle').hide();
            isProductBundle.addClass('product-is-bundle');
            $('.nav-tabs li > a').each(function(){
                if($(this).attr('href') == '#tab_1_3') {
                    $('span', $(this)).html('{$smarty.const.TEXT_PRICE_BUNDLE}');
                }
            });
            $('.tl-all-pages-block li > a').each(function(){
                if($(this).attr('href') == '#tab_1_3') {
                    $('span', $(this)).html('{$smarty.const.TEXT_PRICE_BUNDLE}');
                }
            });
            $('.product-attribute-setting').hide();
          } else {
            $('.is-bundle').hide();
            $('.is-not-bundle').show();
            isProductBundle.removeClass('product-is-bundle');
            $('.nav-tabs li > a').each(function(){
                if($(this).attr('href') == '#tab_1_3') {
                    $('span', $(this)).html('{$smarty.const.TEXT_PRICE_COST_W}');
                }
            });
            $('.tl-all-pages-block li > a').each(function(){
                if($(this).attr('href') == '#tab_1_3') {
                    $('span', $(this)).html('{$smarty.const.TEXT_PRICE_COST_W}');
                }
            });
            $('.product-attribute-setting').show();
          }
        })
        if ($(this).data('value') == is_bundle.val()){
          $(this).trigger('click');
        }
      })
    })
  })(jQuery);
</script>
{/if}
<div class="w-prod-page after w-or-prev-next">
    {if $app->controller->view->product_prev > 0}
    <a href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $app->controller->view->product_prev])}" class="btn-next-prev-or btn-prev-or" title="{$app->controller->view->product_prev_name}"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-prev-or btn-next-prev-or-dis" title=""></a>
    {/if}
    {if $app->controller->view->product_next > 0}
    <a href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $app->controller->view->product_next])}" class="btn-next-prev-or btn-next-or" title="{$app->controller->view->product_next_name}"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-next-or btn-next-prev-or-dis" title=""></a>
    {/if}
    <div class="tabbable tabbable-custom">
    {if $departments}
    <ul class="nav nav-tabs nav-tabs-scroll">
        <li class="{if $selected_department_id == 0} active {/if}"><a class="js_link_platform_modules_select" href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $pInfo->products_id, 'department_id' => 0])}" data-platform_id="0"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
        {foreach $departments as $department}
        <li class="{if $department['id']==$selected_department_id} active {/if}"><a class="js_link_platform_modules_select" href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $pInfo->products_id, 'department_id' => $department['departments_id']])}" data-platform_id="{$department['departments_id']}"><span>{$department['departments_store_name']}</span></a></li>
        {/foreach}
    </ul>
    {/if}
    <div class="tp-all-pages-btn">
        <div class="tp-all-pages-btn-wrapp">
            <span>{$smarty.const.TEXT_ALL_PAGES}</span>
        </div>
        <div class="tl-all-pages-block">
            {if count(platform::getProductsAssignList())>1 || \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select') }
              <ul class="">
              <li><a href="#tab_platform" data-toggle="tab"><span>{$smarty.const.TEXT_ASSIGN_TAB}</span></a></li>
              {else}
              <ul class="">
          {/if}
            {if $departments && count(department::getCatalogAssignList())>1 }
                <li><a href="#tab_department" data-toggle="tab"><span>{$smarty.const.TEXT_DEPARTMENT_TAB}</span></a></li>
            {/if}
            {*<li><a href="#tab_1_1" data-toggle="tab"><span>{$smarty.const.ITEXT_PAGE_VIEW}</span></a></li>*}
    {if \common\helpers\Acl::checkExtensionAllowed('Handlers', 'allowed')}
            <li><a href="#tab_handlers" data-toggle="tab"><span>{$smarty.const.BOX_HANDLERS}</span></a></li>
    {/if}
{if $app->controller->view->showStatistic == true}
    {if $TabAccess->tabView('TEXT_STATIC')}
            <li><a href="#tab_1_2" data-toggle="tab"><span>{$smarty.const.TEXT_STATIC}</span></a></li>
    {/if}
{/if}
    {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
        <li><a href="#tab_event_program" data-toggle="tab"><span>{$smarty.const.TEXT_EVENT_SYSTEM}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_PRICE_COST_W')}
            <li><a href="#tab_1_3" data-toggle="tab"><span>{$smarty.const.TEXT_PRICE_COST_W}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_NAME_DESCRIPTION')}
            <li><a href="#tab_1_4" data-toggle="tab"><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_MAIN_DETAILS')}
            <li class="active"><a href="#tab_1_5" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_LINKED_PRODUCTS') && \common\helpers\Acl::checkExtensionAllowed('LinkedProducts', 'allowed')}
      <li><a href="#tab_linked_products" data-toggle="tab"><span>{$smarty.const.TEXT_LINKED_PRODUCTS}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_ATTR_INVENTORY') && count($app->controller->view->attributes)>0}
            <li class="attributes-tab"><a href="#attributes" data-toggle="tab"><span>{$smarty.const.TEXT_ATTR_INVENTORY}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TAB_IMAGES')}
            <li><a href="#tab_1_7" data-toggle="tab"><span>{$smarty.const.TAB_IMAGES}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_VIDEO')}
            <li><a href="#tab_1_14" data-toggle="tab"><span>{$smarty.const.TEXT_VIDEO}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_SIZE_PACKAGING')}
            <li><a href="#tab_1_8" data-toggle="tab"><span>{$smarty.const.TEXT_SIZE_PACKAGING}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_SEO')}
            <li><a href="#tab_1_9" data-toggle="tab"><span>{$smarty.const.TEXT_SEO}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_MARKETING')}
            <li><a href="#tab_1_10" data-toggle="tab"><span>{$smarty.const.TEXT_MARKETING}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TAB_PROPERTIES')}
            <li><a href="#tab_1_11" data-toggle="tab" title="{$smarty.const.TAB_PROPERTIES}"><span>{$smarty.const.TAB_PROPERTIES}</span></a></li>
    {/if}
<!-- {*
    {if $TabAccess->tabView('TAB_BUNDLES')}
            <li><a href="#tab_1_12" data-toggle="tab"><span>{$smarty.const.TAB_BUNDLES}</span></a></li>
    {/if}
*} -->
    {if $TabAccess->tabView('TAB_OBSOLETE_PRODUCTS') && \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')}
            <li><a href="#tab_obsolete_products" data-toggle="tab"><span>{$smarty.const.TAB_OBSOLETE_PRODUCTS}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TAB_DOCUMENTS')}
            <li><a href="#tab_1_13" data-toggle="tab"><span>{$smarty.const.TAB_DOCUMENTS}</span></a></li>
    {/if}
    {if \common\helpers\Acl::checkExtensionAllowed('Ebay', 'allowed')}
            <li><a href="#tab_ebay" data-toggle="tab"><span>{$smarty.const.BOX_EBAY}</span></a></li>
    {/if}
                  {if $TabAccess->tabView('TAB_NOTES')}
                      <li><a href="#tabNotes" data-toggle="tab"><span>{$smarty.const.TAB_NOTES}</span></a></li>
                  {/if}
    {if $TabAccess->tabView('TAB_COMPETITORS')}
            <li><a href="#tab_1_15" data-toggle="tab"><span>{$smarty.const.TAB_COMPETITORS}</span></a></li>
    {/if}
        </ul>
        </div>
    </div>
          {if count(platform::getProductsAssignList())>1 || \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select') }
              <ul class="nav nav-tabs nav-tabs-platform nav-tabs-scroll">
              <li><a href="#tab_platform" data-toggle="tab"><span>{$smarty.const.TEXT_ASSIGN_TAB}</span></a></li>
              {else}
              <ul class="nav nav-tabs nav-tabs-scroll">
          {/if}
          {if $departments && count(department::getCatalogAssignList())>1 }
              <li><a href="#tab_department" data-toggle="tab"><span>{$smarty.const.TEXT_DEPARTMENT_TAB}</span></a></li>
          {/if}
                {*<li class="active"><a href="#tab_1_1" data-toggle="tab"><span>{$smarty.const.ITEXT_PAGE_VIEW}</span></a></li>*}
    {if \common\helpers\Acl::checkExtensionAllowed('Handlers', 'allowed')}
            <li><a href="#tab_handlers" data-toggle="tab"><span>{$smarty.const.BOX_HANDLERS}</span></a></li>
    {/if}
{if $app->controller->view->showStatistic == true}
    {if $TabAccess->tabView('TEXT_STATIC')}
            <li><a href="#tab_1_2" data-toggle="tab"><span>{$smarty.const.TEXT_STATIC}</span></a></li>
    {/if}
{/if}
    {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
        <li><a href="#tab_event_program" data-toggle="tab"><span>{$smarty.const.TEXT_EVENT_SYSTEM}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_PRICE_COST_W')}
            <li><a href="#tab_1_3" data-toggle="tab"><span>{$smarty.const.TEXT_PRICE_COST_W}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_NAME_DESCRIPTION')}
            <li><a href="#tab_1_4" data-toggle="tab"><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_MAIN_DETAILS')}
            <li class="active"><a href="#tab_1_5" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_LINKED_PRODUCTS') && \common\helpers\Acl::checkExtensionAllowed('LinkedProducts', 'allowed')}
            <li><a href="#tab_linked_products" data-toggle="tab"><span>{$smarty.const.TEXT_LINKED_PRODUCTS}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_ATTR_INVENTORY') && count($app->controller->view->attributes)>0}
            <li class="attributes-tab"><a href="#attributes" data-toggle="tab"><span>{$smarty.const.TEXT_ATTR_INVENTORY}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TAB_IMAGES')}
            <li><a href="#tab_1_7" data-toggle="tab"><span>{$smarty.const.TAB_IMAGES}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_VIDEO')}
            <li><a href="#tab_1_14" data-toggle="tab"><span>{$smarty.const.TEXT_VIDEO}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_SIZE_PACKAGING')}
            <li><a href="#tab_1_8" data-toggle="tab"><span>{$smarty.const.TEXT_SIZE_PACKAGING}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_SEO')}
            <li><a href="#tab_1_9" data-toggle="tab"><span>{$smarty.const.TEXT_SEO}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TEXT_MARKETING')}
            <li><a href="#tab_1_10" data-toggle="tab"><span>{$smarty.const.TEXT_MARKETING}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TAB_PROPERTIES')}
            <li><a href="#tab_1_11" data-toggle="tab" title="{$smarty.const.TAB_PROPERTIES}"><span>{$smarty.const.TAB_PROPERTIES}</span></a></li>
    {/if}
<!-- {*
    {if $TabAccess->tabView('TAB_BUNDLES')}
            <li><a href="#tab_1_12" data-toggle="tab"><span>{$smarty.const.TAB_BUNDLES}</span></a></li>
    {/if}
*} -->
    {if $TabAccess->tabView('TAB_OBSOLETE_PRODUCTS') && \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')}
            <li><a href="#tab_obsolete_products" data-toggle="tab"><span>{$smarty.const.TAB_OBSOLETE_PRODUCTS}</span></a></li>
    {/if}
    {if $TabAccess->tabView('TAB_DOCUMENTS')}
            <li><a href="#tab_1_13" data-toggle="tab"><span>{$smarty.const.TAB_DOCUMENTS}</span></a></li>
    {/if}
    {if \common\helpers\Acl::checkExtensionAllowed('Ebay', 'allowed')}
            <li><a href="#tab_ebay" data-toggle="tab"><span>{$smarty.const.BOX_EBAY}</span></a></li>
    {/if}
                  {if $TabAccess->tabView('TAB_NOTES')}
                      <li><a href="#tabNotes" data-toggle="tab"><span>{$smarty.const.TAB_NOTES}</span></a></li>
                  {/if}
    {if $TabAccess->tabView('TAB_COMPETITORS')}
            <li><a href="#tab_1_15" data-toggle="tab"><span>{$smarty.const.TAB_COMPETITORS}</span></a></li>
    {/if}
        </ul>
        <div class="tab-content">
          {if count(platform::getProductsAssignList())>1 || \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select') }
            <div class="tab-pane topTabPane tabbable-custom" id="tab_platform">
                {if count(platform::getProductsAssignList())>1}
                   {include 'productedit/platform.tpl'}
                {/if}
                <div class="filter_pad">
                {if \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select')}
                    {\common\extensions\UserGroupsRestrictions\UserGroupsRestrictions::productEditBlock($pInfo)}
                {/if}
                </div>
                {$CustomerProduct = \common\helpers\Acl::checkExtension('CustomerProducts', 'viewProductEdit')}
                {if ($pInfo->products_id>0) AND $CustomerProduct }
                <div class="filter_pad">
                  <h4><span>{$smarty.const.TEXT_CUSTOMERS}</span><span class="semicolon"></span></h4>
                   <a id="customer_prices_a" href="{$app->urlManager->createUrl(['extensions', 'module' => 'CustomerProducts', 'action' => 'adminActionProductPopup', 'product_id' => $pInfo->products_id])}" class="popup-link checkbox-inline">{$smarty.const.TEXT_ASSIGN}</a>
                </div>
  <script type="text/javascript">
    (function($){
      $(function(){
        $('.popup-link').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popup-properties'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading cat-head'>{$smarty.const.TEXT_CUSTOMERS}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
          })
      })
    })(jQuery)
  </script>
                {/if}
            </div>
          {/if}
          {if $departments && count(department::getCatalogAssignList())>1 }
            <div class="tab-pane topTabPane tabbable-custom" id="tab_department">
              {include 'productedit/department.tpl'}
            </div>
          {/if}
            {*<div class="tab-pane active" id="tab_1_1">
                <div id="product-view-edit" style="background: #fff"></div>
                <script type="text/javascript">
                  (function($){ $(function(){
                    $('#product-view-edit').editProduct({
                      page_url: '{Yii::getAlias('@web')}/../catalog/product?products_id={$pInfo->products_id}&is_admin=1'
                    })
                  })})(jQuery)
                </script>
            </div>*}
{if \common\helpers\Acl::checkExtension('Handlers', 'productBlock')}
        {\common\extensions\Handlers\Handlers::productBlock($pInfo)}
{/if}
{if $app->controller->view->showStatistic == true}
    {if $TabAccess->tabView('TEXT_STATIC')}
            <div class="tab-pane" id="tab_1_2">
              {include 'productedit/statistic.tpl'}
            </div>
    {/if}
{/if}
    {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
        <div class="tab-pane" id="tab_event_program">
            {$es::programme()->exec('getProgrammeAdditionalFields', [$pInfo->products_id])}
        </div>
    {/if}
    {if $TabAccess->tabView('TEXT_PRICE_COST_W')}
            <div class="tab-pane" id="tab_1_3">
              {include 'productedit/price.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_NAME_DESCRIPTION')}
            <div class="tab-pane" id="tab_1_4">
              {include 'productedit/name.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_MAIN_DETAILS')}
            <div class="tab-pane active" id="tab_1_5">
                {if $TabAccess->isSubProduct()}
                    {include 'productedit/details_sub_product.tpl'}
                {else}
                    {include 'productedit/details.tpl'}
                {/if}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_LINKED_PRODUCTS') && \common\helpers\Acl::checkExtensionAllowed('LinkedProducts', 'allowed')}
        <div class="tab-pane" id="tab_linked_products">
            {\common\extensions\LinkedProducts\LinkedProducts::productBlock($pInfo)}
        </div>
    {/if}
    {if $TabAccess->tabView('TEXT_ATTR_INVENTORY') && count($app->controller->view->attributes)>0}
            <div class="tab-pane" id="attributes">
              {if \common\helpers\Acl::checkExtension('Inventory', 'productBlock') && PRODUCTS_INVENTORY == 'True'}
                {\common\extensions\Inventory\Inventory::productBlock($pInfo)}
              {else}   
              {include 'productedit/attributes.tpl'}
              {/if}
            </div>
    {/if}
    {if $TabAccess->tabView('TAB_IMAGES')}
            <div class="tab-pane js-tab-images" id="tab_1_7">
              {include 'productedit/images.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_VIDEO')}
            <div class="tab-pane" id="tab_1_14">
              {include 'productedit/video.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_SIZE_PACKAGING')}
            <div class="tab-pane tab-size-pack" id="tab_1_8">
              {include 'productedit/size.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_SEO')}
            <div class="tab-pane" id="tab_1_9">
              {include 'productedit/seo.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TEXT_MARKETING')}
            <div class="tab-pane" id="tab_1_10">
              {include 'productedit/marketing.tpl'}
            </div>
    {/if}
    {if $TabAccess->tabView('TAB_PROPERTIES')}
            <div class="tab-pane" id="tab_1_11">
              {include 'productedit/properties.tpl'}
            </div>
    {/if}
<!-- {*
    {if $TabAccess->tabView('TAB_BUNDLES')}
            <div class="tab-pane" id="tab_1_12">
              {if \common\helpers\Acl::checkExtension('ProductBundles', 'productBlock')}
                {\common\extensions\ProductBundles\ProductBundles::productBlock($pInfo)}
              {else}   
                {include 'productedit/bundles.tpl'}
              {/if}
            </div>
    {/if}
*} -->
    {if $TabAccess->tabView('TAB_OBSOLETE_PRODUCTS') && \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')}
        <div class="tab-pane" id="tab_obsolete_products">
            {\common\extensions\ObsoleteProducts\ObsoleteProducts::productBlock($pInfo)}
        </div>
    {/if}
    {if $TabAccess->tabView('TAB_DOCUMENTS')}
            <div class="tab-pane" id="tab_1_13">
              {if \common\helpers\Acl::checkExtension('ProductDocuments', 'productBlock')}
                {\common\extensions\ProductDocuments\ProductDocuments::productBlock($pInfo)}
              {else}  
                {include 'productedit/documents.tpl'}
              {/if}
            </div>
    {/if}
    {if \common\helpers\Acl::checkExtension('Ebay', 'productBlock')}
        {\common\extensions\Ebay\Ebay::productBlock($pInfo)}
    {/if}
    {if $TabAccess->tabView('TAB_NOTES')}
        <div class="tab-pane" id="tabNotes">
            {include './productedit/notes.tpl'}
        </div>
    {/if}
    {if $TabAccess->tabView('TAB_COMPETITORS')}
            <div class="tab-pane" id="tab_1_15">
                {include 'productedit/competitors.tpl' pID={$pInfo->products_id} competitors_data=$app->controller->getCompetitorsData()}
            </div>
    {/if}
        </div>
    </div>
    <div class="btn-bar btn-bar-edp-page after" style="padding: 0;">
        <div class="btn-left">
            <a href="javascript:void(0)" onclick="return backStatement()" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button><a style="opacity: 0.3; cursor: default;" class="btn btn-primary" title="Will be available in the next version.">{$smarty.const.TEXT_PREVIEW_LIGHTBOX}</a>

          {if isset($app->controller->view->preview_link) && $app->controller->view->preview_link|@count > 1}
            <a href="#choose-frontend" class="btn btn-primary btn-choose-frontend">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
          {else}
            <a href="{$app->controller->view->preview_link[0].link}" target="_blank" class="btn btn-primary">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
          {/if}
        </div>
    </div>
    <div class="btn-bar-text">{$smarty.const.TEXT_AFTER_SAFE_ONLY}</div>
</div>
</form>

{if isset($app->controller->view->preview_link) && $app->controller->view->preview_link|@count > 1}
<div id="choose-frontend" style="display: none">
  <div class="popup-heading">{$smarty.const.CHOOSE_FRONTEND}</div>
  <div class="popup-content frontend-links">
    {foreach $app->controller->view->preview_link as $link}
      <p><a href="{$link.link}" target="_blank">{$link.name}</a></p>
    {/foreach}
  </div>
  <div class="noti-btn">
    <div><button class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</button></div>
  </div>
  <script type="text/javascript">
    (function($){
      $(function(){
        $('.popup-box-wrap .frontend-links a').on('click', function(){
          $('.popup-box-wrap').remove()
        })
      })
    })(jQuery)
  </script>
</div>
  <script type="text/javascript">
    (function($){
      $(function(){
        $('.btn-choose-frontend').popUp();
      })
    })(jQuery)
  </script>
{/if}

<script>

function backStatement() {
    window.history.back();
    return false;
}

function resetStatement() {
    return false;
}

function saveProduct() {
    if (typeof unformatMaskMoney == 'function') {
        unformatMaskMoney();
    }
    //return true;
    if (typeof(CKEDITOR) == 'object'){
        for ( instance in CKEDITOR.instances ) {
            CKEDITOR.instances[instance].updateElement();
        }
    }
    var formData = $('#save_product_form').serializeArray();
    if (formData.length && {intval(ini_get('max_input_vars'))}>0 && formData.length>{intval(ini_get('max_input_vars'))}) {
      alert('Too many inputs. All data could NOT be saved. Try to remove some attributes and/or other data.');
      return false;
    }
    {if $pInfo->parent_products_id && !$pInfo->products_id && \common\helpers\Product::isListing($pInfo->parent_products_id)}
    bootbox.confirm({
        message: "{$smarty.const.TEXT_SUB_PRODUCT_PARENT_MARK_AS_MASTER|escape:'javascript'}",
        buttons: {
            confirm: {
                label: '{$smarty.const.TEXT_YES|escape:'javascript'}',
                className: 'btn'
            },
            cancel: {
                label: '{$smarty.const.TEXT_NO|escape:'javascript'}',
                className: 'btn'
            }
        },
        callback: function (result) {
            if ( result ){
                formData.push({ 'name':'mark_parent_as_master', 'value':'1' });
            }
            $.post("{Yii::$app->urlManager->createUrl('categories/product-submit')}"+window.location.hash, formData, function(data, status){
                if (status == "success") {
                    $('#save_product_form').html(data);

                } else {
                    alert("Request error.");
                }
            },"html");
        }
    });
    {else}
    $.post("{Yii::$app->urlManager->createUrl('categories/product-submit')}"+window.location.hash, formData, function(data, status){
        if (status == "success") {
            $('#save_product_form').html(data);

        } else {
            alert("Request error.");
        }
    },"html");
    {/if}
    return false;
}


//===== Images START =====//
//===== Images END =====//
(function($) {
        var jcarousel = $('.jcarousel').jcarousel();

        $('.jcarousel-control-prev')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '-=1'
            });

        $('.jcarousel-control-next')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({
                target: '+=1'
            });

        var setup = function(data) {
            var html = '<ul>';

            $.each(data.items, function() {
                html += '<li><img src="' + this.src + '" alt="' + this.title + '"></li>';
            });

            html += '</ul>';

            // Append items
            jcarousel
                .html(html);

            // Reload carousel
            jcarousel
                .jcarousel('reload');
        };

        $( "#images-listing" ).sortable({
            handle: ".handle",
            axis: 'x',
            update: function( event, ui ) {
                var data = $(this).sortable('serialize', { attribute: "prefix" });
                $("#images_sort_order").val(data);
            }
        }).disableSelection();
        $('#save_product_form').on('image_removed', function(event, key){
            if ( $("#images_sort_order").val() ) {
                $("#images_sort_order").val($( "#images-listing" ).sortable('serialize', { attribute: "prefix" }));
            }
        });

})(jQuery);

$(document).ready(function(){
    $('.check_on_off_subprice').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        onSwitchChange: function (ob, st) {
            if ($(this).is(':checked')) {
                $('#ProductIdPrice').val($(this).data('on'));
                $('#save_product_form').removeClass('disable-product-price-data');
            }else{
                $('#ProductIdPrice').val($(this).data('off'));
                $('#save_product_form').addClass('disable-product-price-data');
            }
        }
    });
});

    $(document).ready(function(){ 
        
        $('.btn-prev-or, .btn-next-or').click(function(e){
            $(this).attr('href', $(this).attr('href')+window.location.hash);
        })
    
        $(".check_bot_switch_on_off").tlSwitch(
            {
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );
        $(".is-virtual-btn .is_virt_on_off").tlSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.is-virtual').toggle();
                    $('.is-virtual-upload').toggle();
                    $('.stock-indication-p, .stock-indication-v, .delivery-term-section').toggle();
                    $(".stock-indication-id option:selected").filter(function() { return $(this).css("display") == "none" }).each(
                      function () {
                        a = $(this).parent().find("option");
                        for (i=0;i<a.length;i++) {
                          if ($(a[i]).css("display") != "none") {
                            $(a[i]).prop("selected", true);
                            break;
                          }
                        }
    //could be too slow filter(function() { return $(this).css("display") != "none" }).first().prop("selected", true);
                    });


                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );

        $(".check_feat_prod").tlSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.edp-ex-s3').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );

        $(".check_subscription").tlSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('.edp-ex-s9').toggle();
                    return true;
                },
				onText: "{$smarty.const.SW_ON}",
				offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );

        $(".check_quote_switch_on_off").tlSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    $('#request_quote_out_stock').toggle();
                    return true;
                },
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );


        $('.heigh_col').on('click', function(){
            setTimeout(function(){
              $('.mn-tab .cbox-right .widget').css('height', $('.mn-tab .cbox-left').height() - 5);
          }, 100);
        });
        $('.heigh_col2').on('click', function(){
            setTimeout(function(){
              $('.edp-our-price-box .widget-not-full .widget-content').css('min-height', $('.edp-pc-box .cbox-right').height() - $('.widget-full').height() - 2);
          }, 100);
        });
        $(window).resize(function() {
          setTimeout(function(){
              //$('.mn-tab .cbox-right .widget').css('height', $('.mn-tab .cbox-left').height() - 5);
              $('.edp-our-price-box .widget-not-full .widget-content').css('min-height', $('.edp-pc-box .cbox-right').height() - $('.widget-full').height() - 2);
          }, 500);
        });
        $(window).resize();

        $('.click-main').click(function(){
            $('a[href="#tab_1_5"]').click();
        });
        $('.click-price').click(function(){
            $('a[href="#tab_1_3"]').click();
        });
        $('.click-images').click(function(){
            $('a[href="#tab_1_7"]').click();
        });
        $('.widget-content-stat img').click(function(){
            $('a[href="#tab_1_7"]').click();
        });
        $('.edp-qty-t b').click(function(){
            $('a[href="#attributes"]').click();
        });
        $('.pr_plus').click(function(){
            val = $(this).next('input').attr('value');
            //if (val < 9){
              val++;
            //}
            /*if (val == 9){
                $(this).addClass('disableM');
            }*/
            var input = $(this).next('input');
            input.attr('value', val);
            if (val > 1) input.siblings('.pr_minus').removeClass('disable');
        });
         $('.pr_minus').click(function(){
            //productButtonCell = $('#qty').parents('.qty-buttons');
            val = $(this).prev('input').attr('value');
            if (val > 1){
              val--;
              $(this).prev('input').siblings('.more').removeClass('disableM');
            }
            var input = $(this).prev('input');
            input.attr('value', val);
            if (val < 2) $('.pr_minus').addClass('disable');
        });

        $('.upload').uploads();

        $('.jcarousel li').click( function() {
            $('.jcarousel li').removeClass('active');
            $(this).addClass('active');
            $(".image-box.active").removeClass('active').addClass('inactive');
            var prefix = $(this).attr('prefix');
            var $imgActiveTab = $("#"+prefix);
            $imgActiveTab.removeClass('inactive').addClass('active');
            if (!$imgActiveTab.hasClass('inited')) {
              $('input.check_bot_switch_on_off_ni', $imgActiveTab).each(function (e) {
                $el = $(this);
                $el.removeClass('input.check_bot_switch_on_off_ni').addClass('input.check_bot_switch_on_off');
                $el.bootstrapSwitch(
                    {
                        onText: "{$smarty.const.SW_ON}",
                        offText: "{$smarty.const.SW_OFF}",
                        handleWidth: '20px',
                        labelWidth: '24px'
                    }
                );
              });
              $imgActiveTab.addClass('inited');
            }
        });

        $('.js-tab-images').on('click','.js-image-toggle .js-image-toggle-source',function(){
            $(this).parents('.js-image-toggle').find('.js-image-toggle-target').toggle();
        });
{*
        /*$('.w-img-check-all > span').click(function(){
            $('.w-img-check-all > span').removeClass('active');
            $(this).toggleClass('active');
        });

        $('.w-img-list ul li').click(function(){
            $('.w-img-list ul li').removeClass('active');
            $(this).toggleClass('active');
        });

        $('.check_all').click(function(){
            $('.w-img-list ul li .uniform').click().change();
        });

        $('.uncheck_all').click(function(){
            $('.w-img-list ul li .uniform').click().change();
        });*/
*}
        //===== Date Pickers  =====//
        $( ".datepicker" ).datepicker({
                changeMonth: true,
                changeYear: true,
                showOtherMonths:true,
                autoSize: false,
                dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
        });
    });

    $(function() {
        var linksButton = $('.tp-all-pages-btn-wrapp');
        var linksBox = $('.tp-all-pages-btn');
        var body = $('body');
        var hideLinksBoxKey = true;

        var hideLinksBox = function(){
            if (hideLinksBoxKey) {
                linksBox.removeClass('active');
                body.off('click', hideLinksBox)
            }
        };
        linksButton.on('click', function(){
            if (!linksBox.hasClass('active')){
                linksBox.addClass('active');

                setTimeout(function(){
                    body.on('click', hideLinksBox)
                }, 100)
            }
        });

        var all_page_btn = $('.tp-all-pages-btn').width() + 8;
		$('.scrtabs-tab-container').css('margin-right', all_page_btn);
		$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                    //localStorage.setItem('lastTab', $(this).attr('href'));
                    //window.location = (""+window.location).replace(/#[A-Za-z0-9_]*$/,'')+$(this).attr('href');
                    //$(window).scrollTop(0);
                });
        /*var lastTab = localStorage.getItem('lastTab');
        if (lastTab) {
            $('a[href=' + lastTab + ']').tab('show');
        } else {
            $('a[data-toggle="tab"]:first').tab('show');
        }*/
        var activate_categories = {$json_platform_activate_categories};
      $('.check_on_off').bootstrapSwitch( {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            onSwitchChange: function (ob, st) {
                var switched_to_state = false;
                if($(this).is(':checked')){
                    switched_to_state = true;
                }
                $(window).trigger('platform_changed', [ob, st]);
                
                if (switched_to_state && this.name.indexOf('platform')==0 ) {
                    var platform_id = this.value;
                    var askActivateCategories = '';
                    if ( activate_categories[platform_id] ) {
                        for( var cat_id in activate_categories[platform_id]){
                            if ( !activate_categories[platform_id].hasOwnProperty(cat_id) ) continue;
                            askActivateCategories += '<br><label><input name="_assign_select[]" class="js-activate_parent_categories_select" '+(activate_categories[platform_id][cat_id]['selected']?' checked="checked" disabled="disabled" readonly="readonly"':'')+' type="checkbox" value="'+cat_id+'"> '+activate_categories[platform_id][cat_id]['label']+'</label>';
                        }
                    }

                        var $state_input = $('.js-platform_parent_categories').filter('input[name="activate_parent_categories['+platform_id+']"]');
                        if ( switched_to_state && $state_input.val()=='' && (askActivateCategories.length>0) ) {
                            $('body').append(
                                '<div class="popup-box-wrap confirm-popup js-state-confirm-popup">' +
                                '<div class="around-pop-up"></div>' +
                                '<div class="popup-box"><div class="pop-up-close"></div>' +
                                '<div class="pop-up-content">' +
                                '<div class="confirm-text">{$smarty.const.TEXT_ASK_ENABLE_PRODUCT_CATEGORIES} '+askActivateCategories+'</div>' +
                                '<div class="buttons"><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_NO}</span><span class="btn btn-default btn-success">{$smarty.const.TEXT_BTN_YES}</span></div>' +
                                '</div>' +
                                '</div>' +
                                '</div>');
                            $('.popup-box-wrap').css('top', $(window).scrollTop() + Math.max(($(window).height() - $('.popup-box').height()) / 2,0));
                            if ( $('.js-activate_parent_categories_select').filter(':checked').length==0 ) {
                                $('.js-activate_parent_categories_select').trigger('click');
                            }

                            var $popup = $('.js-state-confirm-popup');
                            $popup.find('.pop-up-close').on('click', function(){
                                $('.popup-box-wrap:last').remove();
                            });
                            $popup.find('.btn-cancel').on('click', function(){
                                $state_input.val('');
                                $('.popup-box-wrap:last').remove();
                            });
                            $popup.find('.btn-success').on('click', function(){
                                var selected_values = [];
                                $('.js-activate_parent_categories_select:checked').each(function(){
                                    selected_values.push(this.value);
                                });
                                $state_input.val(selected_values.join(','));
                                $('.popup-box-wrap:last').remove();
                            });
                        }

                }
              },
            handleWidth: '20px',
            labelWidth: '24px'
      } );

      $('#save_product_form input[type="search"]').on('keydown',function(event){
          if (event.keyCode=='13'){
              event.preventDefault();
          }
      });
			$('.metric_system span').off().click(function(){
				$('.metric_system span').removeClass('selected');
				$(this).addClass('selected');
				$('.dimmens').hide();
				$('.'+$(this).data('class')).show();
				return false;
			})
			$('input[name="pack_unit"]').keyup(function(){
                            if ($(this).val() > 0) {
                                $('input[name="packaging"]').removeAttr('disabled');
                            } else {
                                $('input[name="packaging"]').val(0);
                                $('input[name="packaging"]').attr('disabled','disabled');
                            }
                            
				if($('input[name="packaging"]').val()){
                                  $('input[name="units_to_pack"]').val($(this).val()*$('input[name="packaging"]').val());
				}else{
				  $('input[name="units_to_pack"]').val($(this).val());
				}				
			})
                        if ( $('input[name="pack_unit"]').val() > 0 ) {
                            $('input[name="packaging"]').removeAttr('disabled');
                        } else {
                            $('input[name="packaging"]').val(0);
                            $('input[name="packaging"]').attr('disabled','disabled');
                        }
			$('input[name="packaging"]').keyup(function(){
					if($('input[name="packaging"]').val()){
						$('input[name="units_to_pack"]').val($(this).val()*$('input[name="pack_unit"]').val());		
					}else{
						$('input[name="units_to_pack"]').val($('input[name="pack_unit"]').val());
					}
			})

        $(document).on('focus', 'input.js-sources', function(event){
            var $input = $(event.target);
            if ( $input.hasClass('js-sources') && !$input.hasClass('ui-autocomplete-input') ){
                $input.autocomplete({
                    source: "{Yii::$app->urlManager->createUrl(['categories/sources'])}",
                    minLength: 0,
                    autoFocus: true,
                    delay: 200,
                    appendTo: $input.parent(),
                    select: function( event, ui ) {
                        event.preventDefault();
                        $($input).val(ui.item.value);
                        $($input).trigger('blur');
                    }
                }).focus(function () {
                    $(this).autocomplete("search");
                });
                $($input).autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                    if ( this.term && this.term!='>' ) {
                        item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<b>$1</b>');
                    }
                    return $( "<li>" )
                        .data("item.autocomplete", item)
                        .append( "<a>" + item.text + "</a>" )
                        .appendTo( ul );
                };
            }
        });

		$('#attributes').on('click','.js-option-default-group', function (e) {
		    if ( e.target.checked ) {
                var option_group = $(e.target).data('option-group');
                var checkboxCollection = $('.js-option-default-group').filter('[data-option-group="' + option_group + '"]').filter(':checked').not($(e.target));
                checkboxCollection.each(function () {
                    this.checked = false;
                });
            }
        })
    });
</script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>

{use class="common\helpers\Html"}
{use class="\common\classes\platform"}
{use class="\common\classes\department"}
{include file='../assets/tabs.tpl' scope="global"}

{if $infoBreadCrumb}
    <div class="breadcrumb-additional_info breadcrumb-for-category">{$infoBreadCrumb}</div>
{/if}
{if $app->controller->view->contentAlreadyLoaded == 0}
<div class="catEditPage popupEditCat">
{/if}
<form id="save_category_form" name="category_edit" onSubmit="return saveCategory();">
<div class="popupCategory">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            {if count(platform::getCategoriesAssignList())>1 || \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select')}
            <li><a href="#tab_platform" data-toggle="tab">{$smarty.const.TEXT_ASSIGN_TAB}</a></li>
            {/if}
            {if $departments == true && count(department::getCatalogAssignList())>1 }
            <li><a href="#tab_department" data-toggle="tab">{$smarty.const.TEXT_DEPARTMENT_TAB}</a></li>
            {*<li><a href="#tab_department_price" data-toggle="tab">{$smarty.const.TEXT_DEPARTMENT_API_PRICE}</a></li>*}
            {/if}
            <li class="active"><a href="#tab_2" data-toggle="tab">{$smarty.const.TEXT_NAME_DESCRIPTION}</a></li>
            <li><a href="#tab_3" data-toggle="tab">{$smarty.const.TEXT_MAIN_DETAILS}</a></li>
            <li><a href="#tab_4" data-toggle="tab">{$smarty.const.TEXT_SEO}</a></li>
{if {$categories_id > 0}}
            <li><a href="#tab_5" data-toggle="tab">{$smarty.const.TEXT_FILTERS}</a></li>
{/if}
            <li><a href="#tab_6" data-toggle="tab">Templates</a></li>
            <li><a href="#tab_supplier" data-toggle="tab">{$smarty.const.TEXT_TAB_SUPPLIERS}</a></li>
            {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
            <li><a href="#tab_event_mails" data-toggle="tab">{$smarty.const.TAB_EVENT_EMAILS}</a></li>
            {/if}
        </ul>
        <div class="tab-content">
            {if count(platform::getCategoriesAssignList())>1 || \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select') }
            <div class="tab-pane topTabPane tabbable-custom" id="tab_platform">
                <div class="filter_pad">
                    {if count(platform::getCategoriesAssignList())>1}
                    <div class="widget"><div class="widget-header" style="margin-bottom: 0;"><h4>{$smarty.const.TABLE_HEAD_PLATFORM_CATEGORY_ASSIGN}</h4></div>
                    <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                        <thead>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                        <th width="150">{$smarty.const.TEXT_ASSIGN}</th>
                        </thead>
                        <tbody>
                        {foreach platform::getCategoriesAssignList() as $platform}
                            <tr>
                                <td>{$platform['text']}</td>
                                <td>
                                    {Html::checkbox('platform[]', isset($app->controller->view->platform_assigned[$platform['id']]), ['value' => $platform['id'],'class'=>'check_on_off'])}
                                    {Html::hiddenInput('category_product_assign['|cat:$platform['id']|cat:']', '', ['class'=>'js-apply_status_to_sub_categories'])}
                                </td>
                           </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    </div>
                    {/if}
                    {if \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'select')}
                        {\common\extensions\UserGroupsRestrictions\UserGroupsRestrictions::categoryEditBlock($cInfo)}
                    {/if}
                </div>
            </div>
            {/if}
            {if $departments == true && count(department::getCatalogAssignList())>1 }
                <div class="tab-pane topTabPane tabbable-custom" id="tab_department">
                    <div class="filter_pad">
                        <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                            <thead>
                            <tr>
                            <th>{$smarty.const.TABLE_HEAD_DEPARTMENT_NAME}</th>
                            <th>{$smarty.const.TABLE_HEAD_DEPARTMENT_CATEGORY_ASSIGN}</th>
                                <th>Api Price formula</th>
                                <th style="width:100px">Discount%</th>
                                <th style="width:100px">Surcharge</th>
                                <th style="width:100px">Margin%</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach department::getCatalogAssignList() as $department}
                                <tr>
                                    <td>{$department['text']}{if $department['id'] eq $cInfo->created_by_department_id} {$smarty.const.OWNER_DEPARTMENT}{/if}</td>
                                    <td>
                                        {Html::checkbox('departments[]', isset($app->controller->view->department_assigned[$department['id']]), ['value' => $department['id'],'class'=>'check_on_off'])}
                                        {Html::hiddenInput('department_category_product_assign['|cat:$department['id']|cat:']', '', ['class'=>'js-apply_status_to_department_sub_categories'])}
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            {Html::textInput('department_category_price['|cat:$department['id']|cat:'][formula_text]', $cInfo->department_category_price[$department['id']]['formula_text'], ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control', 'readonly'=>'readonly'])}
                                            {Html::hiddenInput('department_category_price['|cat:$department['id']|cat:'][formula]', $cInfo->department_category_price[$department['id']]['formula'], [])}
                                            <div class="input-group-addon js-price-formula" data-formula-rel="input[name='department_category_price[{$department['id']}][formula]" data-formula-allow-params=""><i class="icon-money"></i></div>
                                        </div>
                                    </td>
                                    <td>
                                        {Html::textInput('department_category_price['|cat:$department['id']|cat:'][discount]', $cInfo->department_category_price[$department['id']]['discount'], ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                                    </td>
                                    <td>
                                        {Html::textInput('department_category_price['|cat:$department['id']|cat:'][surcharge]', $cInfo->department_category_price[$department['id']]['surcharge'], ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                                    </td>
                                    <td>
                                        {Html::textInput('department_category_price['|cat:$department['id']|cat:'][margin]', $cInfo->department_category_price[$department['id']]['margin'], ['maxlength'=>'64', 'size'=>'32', 'class'=>'form-control'])}
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                {*<div class="tab-pane topTabPane tabbable-custom" id="tab_department_price">

                </div>*}
            {/if}
            <div class="tab-pane active topTabPane tabbable-custom" id="tab_2">
               {if count($languages) > 1}
               <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
                {/if}
                <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">                    
                    {foreach $cDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_EDIT_CATEGORIES_NAME}</td>
                                <td class="label_value">{$mItem['categories_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_DESCRIPTION_LINKS}</td>
                                <td class="label_value">
                                    {$mItem['id']}
                                    {\backend\design\LocalLinksButtons::widget(['editor' => 'txt_category_description_'|cat:$mItem['languageId'], 'platform_id' => 0, 'languages_id' => $mItem['languageId']])}
                                    <div class="info_desc_links">
                                        {$smarty.const.TEXT_INFO_DESC_LINKS}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_EDIT_CATEGORIES_DESCRIPTION}</td>
                                <td class="label_value">{$mItem['categories_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_KEYWORDS}</td>
                                <td class="label_value">{$mItem['categories_head_keywords_tag']}</td>
                            </tr>
                        </table>
                    </div>        
                    {/foreach}
                    {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}{$es::event()->exec('getEventAdditionalFields', [$categories_id, 'main'])}{/if}
                </div>
            </div>
            <div class="tab-pane topTabPane tabbable-custom" id="tab_3">
                <div class="after">
                  <div class="col-md-4">
                    <div class="md_row after">
                        <label for="status">{$smarty.const.TEXT_CATEGORIES_STATUS}</label>
                        <div class="md_value"><input type="checkbox" value="1" name="categories_status" class="check_on_off"{if $cInfo->categories_status == 1} checked="checked"{/if}></div>
                    </div>
                    {if \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')}
                    {\common\extensions\AutomaticallyStatus\AutomaticallyStatus::viewCategoryEdit($cInfo)}
                    {/if}
                  </div>
                  <div class="col-md-4">
                    <div class="md_row after">
                      <label for="status">{$smarty.const.TEXT_DEFAULT_SORT_ORDER}<span class="colon">:</span></label>
                        <div class="md_value">{\common\helpers\Html::listBox('default_sort_order',
                          $cInfo->default_sort_order,
                          \common\helpers\Sorting::getPossibleSortOptions(1),
                          ['size' => 1, 'class' => 'form-control'])}
                        </div>
                    </div>
                  </div>

                </div>
                <div class="">
                  
                    <div class="platform-settings">
                      {include 'categoryedit/platformsettings.tpl'}

                    </div>

                </div>
            </div>

            <div class="tab-pane topTabPane tabbable-custom" id="tab_4">
                {if count($languages) > 1}
                <ul class="nav nav-tabs">
                    {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if}><a href="#seo_tab_{$lItem['code']}" data-toggle="tab">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                    {/foreach}
                </ul>
                {/if}
                <div class="tab-content seoTab {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $cDescription  as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="seo_tab_{$mItem['code']}">
                        <table class="h-teg-table" cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_SEO_PAGE_NAME}</td>
                                <td class="label_value">{$mItem['categories_seo_page_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_NO_INDEX}</td>
                                <td class="label_value">{$mItem['noindex_option']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_NO_FOLLOW}</td>
                                <td class="label_value">{$mItem['nofollow_option']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CANONICAL}</td>
                                <td class="label_value">{$mItem['rel_canonical']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_PAGE_TITLE}</td>
                                <td class="label_value title_tag">{$mItem['categories_head_title_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_HEADER_DESCRIPTION}</td>
                                <td class="label_value desc_tag">{$mItem['categories_head_desc_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H1_TAG}</td>
                                <td class="label_value">{$mItem['categories_h1_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H2_TAG}</td>
                                <td class="label_value"><span id="categories_h2_tag-{$mItem['languageId']}">{foreach explode("\n", $mItem['categories_h2_tag']) as $value}<span class="row"><input type="text" name="categories_h2_tag[{$mItem['languageId']}][]" value="{$value|escape}" class="form-control" /><span class="del-pt del-tag"></span></span>{/foreach}</span><span onclick="addInput('categories_h2_tag-{$mItem['languageId']}', '{htmlspecialchars('<span class="row"><input type="text" name="categories_h2_tag['|cat:$mItem['languageId']|cat:'][]" value="" class="form-control" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span></td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H3_TAG}</td>
                                <td class="label_value"><span id="categories_h3_tag-{$mItem['languageId']}">{foreach explode("\n", $mItem['categories_h3_tag']) as $value}<span class="row"><input type="text" name="categories_h3_tag[{$mItem['languageId']}][]" value="{$value|escape}" class="form-control" /><span class="del-pt del-tag"></span></span>{/foreach}</span><span onclick="addInput('categories_h3_tag-{$mItem['languageId']}', '{htmlspecialchars('<span class="row"><input type="text" name="categories_h3_tag['|cat:$mItem['languageId']|cat:'][]" value="" class="form-control" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span></td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_IMAGE_ALT_TAG_MASK}</td>
                                <td class="label_value">{$mItem['categories_image_alt_tag_mask']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_IMAGE_TITLE_TAG_MASK}</td>
                                <td class="label_value">{$mItem['categories_image_title_tag_mask']}</td>
                            </tr>
<!-- Moved to SeoRedirectsNamed {*
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CATEGORIES_OLD_SEO_PAGE_NAME}</td>
                                <td class="label_value"><input class="form-control seo-input-field" type="input" name="categories_old_seo_page_name" value="{$cInfo->categories_old_seo_page_name}">
                                <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
                                  {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
                                  <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
                                  {/if}
                                </td>
                            </tr>
*} -->
                            {if \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')}
                                {assign var="language_code" value=$mItem['code']}
                                {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderCategory($categories_id, $language_code)}
                            {/if}
                        </table>                        
                      <script>
                      $(document).ready(function(){
                        $('body').on('click', "#seo_tab_{$mItem['code']} .icon-home", function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
                        });
                        $('body').on('click', '#seo_tab_{$mItem['code']} .icon-external-link', function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
                        });
                        
                        $('input[name=categories_old_seo_page_name]').change(function(){
                            $('input[name=categories_old_seo_page_name]').val($(this).val());
                        })
                      })
                      </script>
                    </div>
                    {/foreach}
                    <table class="h-teg-table" width="100%" cellspacing="0" cellpadding="0">
                        <tbody><tr>
                            <td class="label_name">{$smarty.const.TEXT_GOOGLE_PRODUCT_CATEGORY}</td>
                            <td class="label_value autocompletetd">{$app->controller->view->google_product_type}</td>
                        </tr><tbody>
                    </table>
                    <div>
                        <table cellspacing="0" cellpadding="0" width="100%">
                           
                        </table>
                    </div>
                </div>
            </div>
            {if {$categories_id > 0}}
                {if \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'categoryBlock')}
                    {\common\extensions\ProductPropertiesFilters\ProductPropertiesFilters::categoryBlock($categories_id)}
                {else}   
                    <div class="tab-pane topTabPane tabbable-custom dis_module" id="tab_5">
                        <div class="filter_pad">
                            <table class="table table-striped table-bordered table-hover table-responsive datatable-dashboard table-ordering no-footer filter_table" data-ajax="{Yii::$app->urlManager->createUrl(['categories/filter-tab-list', 'cID' => $categories_id])}" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="filter_th_name">{$smarty.const.TEXT_FILTER_NAME}</th>
                                        <th class="filter_th_count">{$smarty.const.TEXT_COUNT_VALUES}</th>
                                        <th class="filter_th_use">{$smarty.const.TEXT_USE_FILTER}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <script type="text/javascript">
                    $('.datatable-dashboard').DataTable({
                        fnDrawCallback: function () {
                            $(".check_on_off_filters").bootstrapSwitch();
                        }
                    });
                    </script>
                {/if}
            {/if}

            <div class="tab-pane topTabPane tabbable-custom" id="tab_6">

                <div class="row">
                    <div class="col-md-6">

                        <div class="widget box box-no-shadow product-frontend-box">
                            <div class="widget-header">
                                <h4>{$smarty.const.TEMPLATE_FOR_CATEGORY_PAGE}</h4>
                            </div>
                            <div class="widget-content widget-content-center">
                                {foreach $templates.list as $frontend}
                                    <div class="product-frontend frontend-{$frontend.id}{if !$frontend.active} disable{/if}">
                                        <h4>{$frontend.text} <span>({$smarty.const.TEXT_THEME_NAME}: {$frontend.theme_title})</span>
                                        </h4>
                                        <div>
                                            <label>
                                                Default
                                                <input type="radio" name="category_template[{$frontend.id}]" value=""
                                                       class="check_give_wrap"{if !$frontend.template} checked{/if}>
                                            </label>
                                            {foreach $frontend.templates_categories as $name}
                                                <label>
                                                    {$name}
                                                    <input type="radio" name="category_template[{$frontend.id}]" value="{$name}"
                                                           class="check_give_wrap"{if $frontend.template == $name} checked{/if}>
                                                </label>
                                            {/foreach}
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="widget box box-no-shadow product-frontend-box">
                            <div class="widget-header">
                                <h4>{$smarty.const.TEMPLATE_FOR_PRODUCTS_PAGE}</h4>
                            </div>
                            <div class="widget-content widget-content-center">
                                {foreach $templates.list as $frontend}
                                    <div class="product-frontend frontend-{$frontend.id}{if !$frontend.active} disable{/if}">
                                        <h4>{$frontend.text} <span>({$smarty.const.TEXT_THEME_NAME}: {$frontend.theme_title})</span>
                                        </h4>
                                        <div>
                                            <label>
                                                Default
                                                <input type="radio" name="product_template[{$frontend.id}]" value=""
                                                       class="check_give_wrap"{if !$frontend.template_product} checked{/if}>
                                            </label>
                                            {foreach $frontend.templates as $name}
                                                <label>
                                                    {$name}
                                                    <input type="radio" name="product_template[{$frontend.id}]" value="{$name}"
                                                           class="check_give_wrap"{if $frontend.template_product == $name} checked{/if}>
                                                </label>
                                            {/foreach}
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>


            </div>
        </div>

        <div class="tab-pane topTabPane tabbable-custom" id="tab_supplier">
            <div class="widget box box-no-shadow" style="margin-bottom: 0;">
                {include file="suppliers-price-data.tpl" supplier_data=$cInfo->supplier_data mayEditCost=true}{*supplierCurrenciesVariants=$cInfo->supplier_data->currenciesVariants*}
            </div>
        </div>
        {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
            <div class="tab-pane topTabPane tabbable-custom" id="tab_event_mails">
                {$es::event()->exec('getEventAdditionalFields', [$categories_id, 'email'])}
            </div>
        {/if}
    </div>
    <div class="btn-bar edit-btn-bar">
        <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
        <div class="btn-right">
            {if isset($app->controller->view->preview_link) && $app->controller->view->preview_link|@count > 1}
                <a href="#choose-frontend" class="btn btn-primary btn-choose-frontend">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
            {else}
                <a href="{$app->controller->view->preview_link[0].link}" target="_blank" class="btn btn-primary">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
            {/if}

            <button class="btn btn-primary">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
        <div class="btn-bar-text">{$smarty.const.TEXT_AFTER_SAFE_ONLY}</div>
</div>
{tep_draw_hidden_field( 'categories_id', $categories_id )}
{tep_draw_hidden_field( 'parent_category_id', $cInfo->parent_id )}
{if $app->controller->view->usePopupMode}
    <input type="hidden" name="popup" value="1" />
{/if}
</form>
{if $app->controller->view->contentAlreadyLoaded == 0}
</div>
{/if}

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
                $('.btn-choose-frontend').popUp({ one_popup: false });
            })
        })(jQuery)
    </script>
{/if}

<script type="text/javascript">
    $(function(){

        $('.title_tag input').limitValue('title');
        $('.desc_tag textarea').limitValue('description');

        if ($('.search-map').length>0) {
          var searchProductBox = $('.search-map')[0];
        } else {
          var searchProductBox = $('.search-map');
        }


        $('.map-name').keyup(function(e){

            searchProductBox = $('#search_map' + $(this).attr('data-idsuffix'));
            console.log(searchProductBox);
            
            $.get('image-maps/search', {
                key: $(this).val()
            }, function(data){
                $('.suggest').remove();

                searchProductBox.append('<div class="suggest">'+data+'</div>');

                $('a', searchProductBox).on('click', function(e){
                    e.preventDefault();
                    var ids = searchProductBox.attr('data-idsuffix');

                    $('#map_id' + ids).val($(this).data('id'));
                    $('#map_name' + ids).val($('.td_name', this).text());
                    $('#map_image' + ids).show().attr('src', '../images/maps/' + $(this).data('image'));
                    $('#map_image_remove' + ids).show();

                    $('.suggest').remove();
                    return false
                })
            })
        });

        $('.map-image-remove').on('click', function(){
          var ids = $(this).attr('data-idsuffix');
          $('#map_id' + ids).val('');
          $('#map_name' + ids).val('');
          $('#map_image' + ids).hide().attr('src', '');
          $(this).hide()
        });






        $(".check_give_wrap").bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
        });
        $(window).on('platform_changed', function (e, ob, st) {
            if (ob.currentTarget.name == 'platform[]') {
                if (st == true) {
                    $('.frontend-' + ob.currentTarget.value).removeClass('disable');
                } else {
                    $('.frontend-' + ob.currentTarget.value).addClass('disable');
                }
                if ($('.product-frontend:not(.disable) label:nth-child(2)').length > 0) {
                    $('.product-frontend-box').show();
                } else {
                    $('.product-frontend-box').hide();
                }
            }
        });
    });

{*$imageScript*}

{if $app->controller->view->contentAlreadyLoaded == 0}

function backStatement() {
    {if $app->controller->view->usePopupMode}
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
    {else}
        window.history.back();
    {/if}
    return false;
}

CKEDITOR.replaceAll( 'ckeditor');

function saveCategory() {
  if (typeof(CKEDITOR) == 'object'){
    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement();
    }
  }
    
    $.post("{Yii::$app->urlManager->createUrl('categories/category-submit')}", $('#save_category_form').serialize(), function(data, status){
        if (status == "success") {
            {if $app->controller->view->usePopupMode}
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove(); 
                $( ".cat_main_box" ).html(data);
                $('.edit_cat').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Editing category <span class='js-popup-category-name'></span></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.delete_cat').popUp({
                    box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>Delete category</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                });
                $('.collapse_span').click(function(){
                    $(this).toggleClass('c_up');
                    $(this).parent().parent().next().slideToggle();
                });
                resetStatement();
            {else}      
                $('.catEditPage').append(data);
            {/if}
            //$('#manufacturers_management_data').html(data);
            //$("#manufacturers_management").show();

            //$('.gallery-album-image-placeholder').html('');

            //$('.table').DataTable().search( '' ).draw(false);

            

        } else {
            alert("Request error.");
        }
    },"html");

    //$('input[name=categories_image_loaded]').val();

    return false;
}
{/if}

var $filedrop = $('#gallery-filedrop');

function createImage (file, $container){
    var $preview = $('.gallery-template', $filedrop);
    $image = $('img', $preview);
    var reader = new FileReader();
    $image.width(300);
    reader.onload = function(e){
        $image.attr('src',e.target.result);
    };
    reader.readAsDataURL(file);
    $preview.appendTo($('.gallery-filedrop-queue', $container));
    $.data(file, $preview);
}


$(document).ready(function(){
    var switch_assign_stat = {$js_platform_switch_notice};
    {if $departments == true}
    var switch_department_assign_stat = {$js_department_switch_notice};
    {/if}
    $(".check_on_off").bootstrapSwitch( {
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        onSwitchChange: function (ob, st) {
            $(window).trigger('platform_changed', [ob, st]);
          var switched_to_state = false;
          if($(this).is(':checked')){
            switched_to_state = true;
            $(this).parents('tr').find('.handle_cat_list, .count_block').removeClass('dis_module');
          }else{
            $(this).parents('tr').find('.handle_cat_list, .count_block').addClass('dis_module');
          }
          if (this.name.indexOf('platform')==0 ) {
            var platform_id = this.value;
            if ( switch_assign_stat[platform_id] && switch_assign_stat[platform_id]['original_state']!=switched_to_state ) {
                var $state_input = $('input[name="category_product_assign['+platform_id+']"]');
                if ( switched_to_state && $state_input.val()!='yes' && (parseInt(switch_assign_stat[platform_id]['categories'][switched_to_state?0:1])>0 || parseInt(switch_assign_stat[platform_id]['products'][switched_to_state?0:1])>0) ) {
                    $('body').append(
                        '<div class="popup-box-wrap confirm-popup js-state-confirm-popup">' +
                        '<div class="around-pop-up"></div>' +
                        '<div class="popup-box"><div class="pop-up-close"></div>' +
                        '<div class="pop-up-content">' +
                        '<div class="confirm-text">{$smarty.const.TEXT_ASK_ENABLE_CATEGORIES_AND_PRODUCTS_TO_PLATFORM}</div>' +
                        '<div class="buttons"><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_NO}</span><span class="btn btn-default btn-success">{$smarty.const.TEXT_BTN_YES}</span></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>');
                    $('.popup-box-wrap').css('top', $(window).scrollTop() + Math.max(($(window).height() - $('.popup-box').height()) / 2,0));

                    var $popup = $('.js-state-confirm-popup');
                    $popup.find('.pop-up-close').on('click', function(){
                        $('.popup-box-wrap:last').remove();
                    });
                    $popup.find('.btn-cancel').on('click', function(){
                        $state_input.val('');
                        $('.popup-box-wrap:last').remove();
                    });
                    $popup.find('.btn-success').on('click', function(){
                        $state_input.val('yes');
                        $('.popup-box-wrap:last').remove();
                    });
                    {if $departments == true}
                }else if (this.name.indexOf('departments')==0 ) {
                    var department_id = this.value;
                    if ( switch_department_assign_stat[department_id] && switch_department_assign_stat[department_id]['original_state']!=switched_to_state ) {
                        var $state_input = $('input[name="department_category_product_assign['+department_id+']"]');
                        if ( switched_to_state && $state_input.val()!='yes' && (parseInt(switch_department_assign_stat[department_id]['categories'][switched_to_state?0:1])>0 || parseInt(switch_department_assign_stat[department_id]['products'][switched_to_state?0:1])>0) ) {
                            $('body').append(
                                '<div class="popup-box-wrap confirm-popup js-state-confirm-popup">' +
                                '<div class="around-pop-up"></div>' +
                                '<div class="popup-box"><div class="pop-up-close"></div>' +
                                '<div class="pop-up-content">' +
                                '<div class="confirm-text">{$smarty.const.TEXT_ASK_ENABLE_CATEGORIES_AND_PRODUCTS_TO_DEPARTMENT}</div>' +
                                '<div class="buttons"><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_NO}</span><span class="btn btn-default btn-success">{$smarty.const.TEXT_BTN_YES}</span></div>' +
                                '</div>' +
                                '</div>' +
                                '</div>');
                            $('.popup-box-wrap').css('top', $(window).scrollTop() + Math.max(($(window).height() - $('.popup-box').height()) / 2,0));

                            var $popup = $('.js-state-confirm-popup');
                            $popup.find('.pop-up-close').on('click', function(){
                                $('.popup-box-wrap:last').remove();
                            });
                            $popup.find('.btn-cancel').on('click', function(){
                                $state_input.val('');
                                $('.popup-box-wrap:last').remove();
                            });
                            $popup.find('.btn-success').on('click', function(){
                                $state_input.val('yes');
                                $('.popup-box-wrap:last').remove();
                            });
                        }
                    }
                {/if}
                }
            }
          }
        },
        handleWidth: '20px',
        labelWidth: '24px'
    } );

    {if $departments == true}
    $('.js-price-formula').on('click', function(){
        var field = $(this).data('formula-rel');
        var allowed_params = $(this).data('formula-allow-params')||'';

        bootbox.dialog({ message: '<iframe src="{$app->urlManager->createUrl(['popups/price-formula-editor','s'=>(float)microtime()])}&formula_input='+encodeURIComponent(field)+'&allowed_params='+encodeURIComponent(allowed_params)+'" width="900px" height="420px" style="border:0"/>' });
        bootbox.setDefaults( { size:'large', onEscape:true, backdrop:true });
    });

    window.priceFormulaRetrieve = function (inputSelector){
        var jsonString = $(inputSelector).val();
        if ( jsonString ) {
            return JSON.parse(jsonString);
        }
        return { };
    };

    window.priceFormulaUpdate = function (inputSelector, formulaObject ) {
        $(inputSelector).val( JSON.stringify(formulaObject) );
        $(inputSelector.replace('[formula]','[formula_text]')).val($.trim(formulaObject.text));
        bootbox.hideAll();
    };
    {/if}

    {if $cInfo->categories_name}
    $('.js-popup-category-name').html(' - &quot;{$cInfo->categories_name|escape:'javascript'}&quot;');
    {/if}
        
    $('input[name^="google_product_type["]').autocomplete({
        source: "{Yii::$app->urlManager->createUrl(['categories/getgooglecategories','with'=>'id'])}",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.autocompletetd',
        select: function( event, ui ) {
            event.preventDefault();
            $('input[name=google_product_type_id]').val(ui.item.id);
            $('input[name=google_product_type_id]').trigger('change');
            $('input[name^="google_product_type["]').val(ui.item.id);//ui.item.value
            $('input[name^="google_product_type["]').trigger('blur');
            $("#catHierachy").text(ui.item.text);
            reloadDropDownChain(ui.item.id);
        }
    }).focus(function () {
        $(this).autocomplete("search");
    });
    $('input[name^="google_product_type["]').autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        if ( this.term && this.term!='>' ) {
            item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<b>$1</b>');
        }
        return $( "<li>" )
            .data("item.autocomplete", item)
            .append( "<a>" + item.text + "</a>" )
            .appendTo( ul );
    };
    $( "select[name^='google_category_dropdown_']" ).change(onOptionChange);
})

function addInput (id, input) {
  $('#' + id).append(input);
}
$('body').on('click', '.del-pt.del-tag', function(){
  $(this).parent().remove();
});
                      
function reloadDropDownChain(category_id)
{
    $.post("categories/getupdatedgooglecategories?categories_id="+category_id, {}, function(data, status){
        if (status == "success") {
            $('#dropDownChain').html(data);                                    
            $(document)
                .off('change', "select[name^='google_category_dropdown_']", onOptionChange)
                .on('change', "select[name^='google_category_dropdown_']", onOptionChange);
            $('input[name=google_product_type_id]').val(category_id);
            $('input[name^="google_product_type["]').val(category_id);

            $("#catHierachy").text('');
            //console.info($("#catHierachy").length);
            $("select[name^='google_category_dropdown_']").each(function( index ) {
                let addSelection = $( this ).find(":selected").text();
                console.info(addSelection);
                if ($( this ).find(":selected").val() === '') {
                    addSelection = '';
                }
                if (addSelection !== '') {
                    $("#catHierachy").text($("#catHierachy").text() + (index===0?'':' > ') + addSelection);
                }
            });
        } else {
          alert("Request error.");
        }
    },"html");
    return false;
}

function onOptionChange()
{                            
    let selectedOption = $(this).find(":selected").val();
    if (selectedOption === '' && $(this).parent().prev().find("select").length) {
        selectedOption = $(this).parent().prev().find("select").val();
    }
    if (selectedOption === '' && $(this).parent().prev().find("select").length === 0) {                                
        $(this).parent().remove();
        selectedOption = 0;
    }
    reloadDropDownChain(selectedOption);
}
</script>

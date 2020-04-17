{use class="\common\classes\Images"}
{if defined('ADMIN_TOO_MANY_IMAGES') && is_array($app->controller->view->images) && $app->controller->view->images|@count>= intval(ADMIN_TOO_MANY_IMAGES)}
  {$uniform="uniform-dis"}
{else}
  {$uniform="uniform"}
{/if}
<div id="image-box-{$Key}" class="image-box inactive">
<div class="box-gallery-left">
    <div class="edp-our-price-box">
        <div class="widget widget-full box box-no-shadow" style="margin-bottom: 0">
            <div class="widget-content after">
                <div class="status-left st-origin">
                    <span>{$smarty.const.TEXT_STATUS}</span>
                    <input type="checkbox" value="1" name="image_status[{$Key}]" class="check_bot_switch_on_off" checked="checked" />
                </div>
                <div class="status-left">
                    <span>{$smarty.const.TEXT_DEFAULT_IMG}:</span>
                    <input type="radio" value="{$Key}" name="default_image" class="default-images check_bot_switch_on_off" />
                </div>
            </div>
        </div>
        <div class="widget box widget-not-full box-no-shadow" style="margin-bottom: 0; border-top: 0;">
            <div class="widget-content">
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs nav-tabs-vertical nav-tabs-vertical-lang">
                        <li class="active"><a href="#tab_4_{$Key}_0" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN}</span></a></li>
                        {foreach $description as $DKey => $DItem}
                        <li><a href="#tab_4_{$Key}_{$DItem['key']}" class="flag-span" data-toggle="tab">{$DItem['logo']}<span>{$DItem['name']}</span></a></li>
                        {/foreach}
                    </ul>
                    <div class="tab-content tab-content-vertical">
                        <div class="tab-pane active one-img-gal{if $Item['use_external_images']} product-image-external-active{/if}" id="tab_4_{$Key}_0">
                            <div class="one-img-gal-left">
                                <div class="drag-prod-img-2">
                                    <div class="upload new_upload_{$Key}" data-linked="preview-box-{$Key}" data-name="orig_file_name[{$Key}][0]" data-show="ofn_{$Key}_0" data-preload="{$Item['orig_file_name']|escape:'html'}" data-value="{$Item['image_name']|escape:'html'}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                                </div>
                            </div>
                            <div class="one-img-gal-right">
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_ORING_NAME}</label>
                                    <span id="ofn_{$Key}_0">{$Item['orig_file_name']}&nbsp;</span>
                                    <label><input type="checkbox" name="use_origin_image_name[{$Key}][0]" value="1" {if $Item['use_origin_image_name'] == 1} checked{/if} class="{$uniform}" /> {$smarty.const.TEXT_USE_ORIGIN_IMAGE_NAME}</label>
                                </div>
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_HEAD_TITLE}</label>
                                    <input type="text" name="image_title[{$Key}][0]" value="{$Item['image_title']|escape:'html'}" class="form-control" />
                                </div>        
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_ALTER}</label>
                                    <input type="text" name="image_alt[{$Key}][0]" value="{$Item['image_alt']|escape:'html'}" class="form-control" />
                                </div>
                                <div class="our-pr-line">
                                    <label><input type="checkbox" onclick="return imageSwitchExtInt('tab_4_{$Key}_0')" name="use_external_images[{$Key}][0]" value="1" {if $Item['use_external_images']} checked{/if} class="{$uniform}" /> {$smarty.const.USE_EXTERNAL_IMAGES}</label>
                                </div>
                                <div class="our-pr-line external_images-hide js-image-toggle">
                                    <label><input type="checkbox" name="alt_file_name_flag[{$Key}][0]" value="1" {if $Item['alt_file_name'] != ""} checked{/if} class="{$uniform} js-image-toggle-source" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                                    <input type="text" name="alt_file_name[{$Key}][0]" value="{$Item['alt_file_name']|escape:'html'}" class="form-control js-image-toggle-target" {if $Item['alt_file_name'] == ""} style="display: none;"{/if} />
                                </div>
                                <div class="our-pr-line external_images-hide">
                                    <label><input type="checkbox" name="no_watermark[{$Key}][0]" value="1" {if $Item['no_watermark'] == 1} checked{/if} class="{$uniform}" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                                </div>
                                <div class="external_images-show">
                                    <div class="our-pr-line">
                                        <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,'Original')}</label>
                                        <input type="text" name="external_image_original[{$Key}][0]" value="{$Item['external_image_original']|escape:'html'}" class="form-control" />
                                    </div>
                                    {foreach from=$Item['external_images'] item=external_image name=imageTypeList}
                                        <div class="our-pr-line">
                                            <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,$external_image['image_types_name'])} ({$external_image['image_size']})</label>
                                            <input type="text" name="external_image[{$Key}][0][{$external_image['image_types_id']}]" value="{$external_image['image_url']|escape:'html'}" class="form-control{if $smarty.foreach.imageTypeList.last} biggestImage{/if}" />
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                        {foreach $description as $DKey => $DItem}
                        <div class="tab-pane one-img-gal{if $DItem['use_external_images']} product-image-external-active{/if}" id="tab_4_{$Key}_{$DItem['key']}">
                            <div class="one-img-gal-left">
                                <div class="drag-prod-img-2">
                                    <div class="upload new_upload_{$Key}" data-name="orig_file_name[{$Key}][{$DItem['id']}]" data-show="ofn_{$Key}_{$DItem['id']}" data-value="{$DItem['image_name']|escape:'html'}" data-url="{Yii::$app->urlManager->createUrl('upload/index')}"></div>
                                </div>
                            </div>
                            <div class="one-img-gal-right">
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_ORING_NAME}</label>
                                    <span id="ofn_{$Key}_{$DItem['id']}">{$DItem['orig_file_name']}&nbsp;</span>
                                    <label><input type="checkbox" name="use_origin_image_name[{$Key}][{$DItem['id']}]" value="1" {if $DItem['use_origin_image_name'] == 1} checked{/if} class="uniform" /> {$smarty.const.TEXT_USE_ORIGIN_IMAGE_NAME}</label>
                                </div>
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_HEAD_TITLE}</label>
                                    <input type="text" name="image_title[{$Key}][{$DItem['id']}]" value="{$DItem['image_title']|escape:'html'}" class="form-control" />
                                </div>        
                                <div class="our-pr-line">
                                    <label>{$smarty.const.TEXT_IMG_ALTER}</label>
                                    <input type="text" name="image_alt[{$Key}][{$DItem['id']}]" value="{$DItem['image_alt']|escape:'html'}" class="form-control" />
                                </div> 
                                <div class="our-pr-line">
                                    <label><input type="checkbox" onclick="return imageSwitchExtInt('tab_4_{$Key}_{$DItem['id']}')" name="use_external_images[{$Key}][{$DItem['id']}]" value="1" {if $DItem['use_external_images'] == 1} checked{/if} class="uniform" /> {$smarty.const.USE_EXTERNAL_IMAGES}</label>
                                </div>
                                <div class="our-pr-line external_images-hide js-image-toggle">
                                    <label><input type="checkbox" name="alt_file_name_flag[{$Key}][{$DItem['id']}]" value="1" {if $DItem['alt_file_name'] != ""} checked{/if} class="uniform js-image-toggle-source" /> {$smarty.const.TEXT_TYPE_ALTR_FILE}</label>
                                    <input type="text" name="alt_file_name[{$Key}][{$DItem['id']}]" value="{$DItem['alt_file_name']|escape:'html'}" class="form-control js-image-toggle-target" {if $DItem['alt_file_name'] == ""} style="display: none;"{/if} />
                                </div>
                                <div class="our-pr-line external_images-hide">
                                    <label><input type="checkbox" name="no_watermark[{$Key}][{$DItem['id']}]" value="1" {if $DItem['no_watermark'] == 1} checked{/if} class="uniform" /> {$smarty.const.TEXT_NO_WATERMARK}</label>
                                </div>
                                <div class="external_images-show">
                                    <div class="our-pr-line">
                                        <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,'Original')}</label>
                                        <input type="text" name="external_image_original[{$Key}][{$DItem['id']}]" value="{$DItem['external_image_original']|escape:'html'}" class="form-control" />
                                    </div>
                                    {foreach from=$Item['external_images'] item=external_image name=imageTypeList}
                                        <div class="our-pr-line">
                                            <label>{sprintf($smarty.const.EXTERNAL_IMAGE_URL,$external_image['image_types_name'])} ({$external_image['image_size']})</label>
                                            <input type="text" name="external_image[{$Key}][{$DItem['id']}][{$external_image['image_types_id']}]" value="{$external_image['image_url']|escape:'html'}" class="form-control{if $smarty.foreach.imageTypeList.last} biggestImage{/if}" />
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="box-gallery-right">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
                {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock2')}
                    <li class="active"><a href="#tab_{$Key}_5_1" data-toggle="tab"><span>{$smarty.const.TEXT_ASSIGN_TO_ATTR}</span></a></li>
                {else} 
                    <li class="active"><a href="#tab_{$Key}_5_1" data-toggle="tab"><span class="dis_module">{$smarty.const.TEXT_ASSIGN_TO_ATTR}</span></a></li>
                {/if}
                {if \common\helpers\Acl::checkExtension('InventortyImages', 'productBlock2')}
                    <li><a href="#tab_{$Key}_5_2" data-toggle="tab"><span>{$smarty.const.TEXT_ASSIGN_TO_INVENT}</span></a></li>
                {else} 
                    <li><a href="#tab_{$Key}_5_2" data-toggle="tab"><span class="dis_module">{$smarty.const.TEXT_ASSIGN_TO_INVENT}</span></a></li>
                {/if}
        </ul>
        <div class="tab-content">
            {if \common\helpers\Acl::checkExtension('AttributesImages', 'productBlock2')}
                    {\common\extensions\AttributesImages\AttributesImages::productBlock2($Key, $Item)}
                {else} 
            <div class="tab-pane active dis_module" id="tab_{$Key}_5_1">
              <div class="box-head-serch after">
                  <input type="search" placeholder="Search by assigned attributes" disabled class="form-control">
                <button onclick="return false"></button>
              </div>

              <div class="w-img-list w-img-list-attr">
                <div class="w-img-list-ul js-option-images">
                  {foreach $app->controller->view->selectedAttributes as $sel_attr_option}
                    <label class="js-option-group-images" data-ov_id="{$sel_attr_option['products_options_id']}">{$sel_attr_option['products_options_name']}</label>
                    <ul class="js-option-group-images" data-ov_id="{$sel_attr_option['products_options_id']}">
                      {foreach $sel_attr_option['values'] as $sel_attr_value}
                        <li class="js-option-value-images" data-ov_pair="{$sel_attr_option['products_options_id']}_{$sel_attr_value['products_options_values_id']}"><label><input type="checkbox" disabled class="uniform" {if Images::checkAttribute($Item['products_images_id'], $sel_attr_option['products_options_id'], $sel_attr_value['products_options_values_id'])}checked{/if} /> {$sel_attr_value['products_options_values_name']}</label></li>
                      {/foreach}
                    </ul>
                  {/foreach}
                </div>
                <div class="w-btn-list" style="display: none;">
                  <span class="btn">{$smarty.const.TEXT_ASSIGN}</span>
                </div>
              </div>
            </div>
                {/if}
                {if \common\helpers\Acl::checkExtension('InventortyImages', 'productBlock2')}
                    {\common\extensions\InventortyImages\InventortyImages::productBlock2($Key)}
                {else} 
            <div class="tab-pane dis_module" id="tab_{$Key}_5_2">
                <div class="box-head-serch after">
                    <input type="search" placeholder="Search by assigned inventory" class="form-control">
                    <button></button>
                </div>
                <!--div class="w-img-check-all">
                    <span class="check_all_inv check-btn">{$smarty.const.TEXT_CHECK_ALL}</span><span class="uncheck_all_inv check-btn">{$smarty.const.TEXT_UNCHECK_ALL}</span>
                </div-->
                <div class="w-img-list w-img-list-attr">
                    <div class="w-img-list-ul js_image_inventory" data-image_idx="{$Key}">

                    </div>
                    <div class="w-btn-list" style="display: none;">
                        <span class="btn">{$smarty.const.TEXT_ASSIGN}</span>
                    </div>
                </div>
            </div>
                {/if}
        </div>                    
    </div> 
</div>
<input type="hidden" name="products_images_id[{$Key}]" value="0" />
<input type="hidden" id="deleted-image-{$Key}" name="products_images_deleted[{$Key}]" value="0" />
</div>
<script type="text/javascript">
$('li.clickable-box-{$Key}').click( function() { 
    $('.jcarousel li').removeClass('active');
    $(this).addClass('active');
    $(".image-box.active").removeClass('active').addClass('inactive');
    var prefix = $(this).attr('prefix');
    $("#"+prefix).removeClass('inactive').addClass('active');
});

$(".check_bot_switch_on_off").bootstrapSwitch(
    {
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    }
);


$('.new_upload_{$Key}').uploads();
$('.jcarousel').jcarousel();
$('#image-box-{$Key}').find(':radio.uniform, :checkbox.uniform').uniform();
</script>
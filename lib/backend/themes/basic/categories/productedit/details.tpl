{use class="common\helpers\Html"}
{use class="common\helpers\Suppliers"}

<div class="row product-main-detail-top-switchers">
    <div class="status-left">
        <span>{$smarty.const.TEXT_STATUS}</span>
        <input type="checkbox" value="1" name="products_status"
               class="check_bot_switch_on_off"{if $pInfo->products_status == 1} checked="checked"{/if} />
    </div>
    {if \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')}
        {\common\extensions\AutomaticallyStatus\AutomaticallyStatus::viewProductEdit($pInfo)}
    {/if}
{if $isBundle != true}
    <div class="status-left">
        <span>{$smarty.const.TEXT_MANUAL_STOCK_UNLIMITED}:</span>
        <input type="checkbox" value="1" name="manual_stock_unlimited"
               class="check_bot_switch_on_off"{if $pInfo->manual_stock_unlimited == 1} checked="checked"{/if} />
    </div>
{/if}
    {if $smarty.const.LISTING_SUB_PRODUCT=='True'}
    <div class="status-right">
        <span>{$smarty.const.TEXT_LISTING_PRODUCT}:</span>
        <input type="checkbox" value="1" name="is_listing_product"
               class="check_bot_switch_on_off"{if !!$pInfo->is_listing_product} checked="checked"{/if} />
        <input type="hidden" name="listing_switch_present" value="1">
    </div>
    {/if}
</div>
<div class="create-or-wrap after mn-tab">
    <div class="cbox-left">
        <div class="widget box box-no-shadow">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_LABEL_BRAND}</h4>
            </div>
            <div class="widget-content">
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_MANUFACTURERS_NAME}</label>
                    <div class="f_td_group f_td_group-pr">
                        <input id="selectBrand" name="brand" type="text" class="form-control form-control-small"
                               value="{$pInfo->manufacturers_name}" autocomplete="off">
                        {Html::hiddenInput('manufacturers_id', $pInfo->manufacturers_id, ['class'=>'js-product-manufacturer'])}
                        <a href="{Yii::$app->urlManager->createUrl('categories/brandedit')}"
                           class="btn btn-add-brand edit_brand"
                           title="{$smarty.const.TEXT_ADD_NEW_BRAND}">{$smarty.const.TEXT_ADD_NEW_BRAND}</a>
                    </div>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('#selectBrand').autocomplete({
                                source: "{Yii::$app->urlManager->createUrl(['categories/brands','with'=>'id'])}",
                                minLength: 0,
                                autoFocus: true,
                                delay: 0,
                                appendTo: '.f_td_group',
                                select: function( event, ui ) {
                                    event.preventDefault();
                                    $('.js-product-manufacturer').val(ui.item.id);
                                    $('.js-product-manufacturer').trigger('change');
                                    $('#selectBrand').val(ui.item.value);
                                    $('#selectBrand').trigger('blur');
                                }
                            }).focus(function () {
                                $(this).autocomplete("search");
                            });
                            $('#selectBrand').autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                                if ( this.term && this.term!='>' ) {
                                    item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<b>$1</b>');
                                }
                                return $( "<li>" )
                                    .data("item.autocomplete", item)
                                    .append( "<a>" + item.text + "</a>" )
                                    .appendTo( ul );
                            };

                            $('.edit_brand').popUp({
                                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='popup-heading cat-head'>{$smarty.const.TEXT_ADD_NEW_BRAND}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                            });

                            $('.edit_docs').popUp({
                                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupEditCat'><div class='pop-up-close'></div><div class='popup-heading cat-head'>{$smarty.const.CHOOSE_FILE}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
                            });

                        });
                    </script>
                </div>
            </div>
        </div>


        <div class="widget box box-no-shadow">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_STOCK}</h4>
                <div class="edp-line">
                    <span class="edp-qty-t" style="display:none;">{$smarty.const.TEXT_APPLICABLE}</b></span>
                </div>
            </div>
            <div class="widget-content">

                <div class="stock-block">
                    <div class="available-stock">
                        <div>{$smarty.const.TEXT_STOCK_QUANTITY_INFO}</div>
                        <div class="val" id="products_quantity_info">{$pInfo->products_quantity}</div>
                        <input type="hidden" name="products_quantity" value="{$pInfo->products_quantity}">
                    </div>

                    <div class="temporary">
                        <div>{$smarty.const.TEXT_STOCK_TEMPORARY_QUANTITY}</div>
                        <div class="val" id="temporary_quantity_info">{$pInfo->temporary_quantity}</div>
                        <input type="hidden" name="temporary_quantity" value="{$pInfo->temporary_quantity}">
                    </div>

                    <div class="total-allocated">
                        <div>{$smarty.const.TEXT_STOCK_ALLOCATED_QUANTITY}</div>
                        <div class="val" id="allocated_quantity_info">{$pInfo->allocated_quantity}</div>
                        <input type="hidden" name="allocated_quantity" value="{$pInfo->allocated_quantity}">
                    </div>

                    <div class="real-stock-total">
                        <div>{$smarty.const.TEXT_STOCK_WAREHOUSE_QUANTITY}</div>
                        <div class="val" id="warehouse_quantity_info">{$pInfo->warehouse_quantity}</div>
                        <input type="hidden" name="warehouse_quantity" value="{$pInfo->warehouse_quantity}">
                    </div>

                    <div class="available">
                        <div>{$smarty.const.TEXT_STOCK_SUPPLIERS_QUANTITY}</div>
                        <div class="val" id="suppliers_quantity_info">{$pInfo->suppliers_quantity}</div>
                        <input type="hidden" name="suppliers_quantity" value="{$pInfo->suppliers_quantity}">
                    </div>

                    <div class="ordered-stock">
                        <div>{$smarty.const.TEXT_STOCK_ORDERED_QUANTITY}</div>
                        <div class="val" id="ordered_quantity_info">{$pInfo->ordered_quantity}</div>
                        <input type="hidden" name="ordered_quantity" value="{\common\helpers\Product::getStockOrdered($pInfo->products_id)}">
                    </div>
                    <div class="buttons">
{if (defined('ENABLE_FREEZE_STOCK') && ENABLE_FREEZE_STOCK == '1')}

{else}
                        {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                            {if $pInfo->products_id > 0}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/update-stock', 'products_id' => $pInfo->products_id])}" class="btn right-link edp-qty-update" data-class="update-stock-popup">{$smarty.const.TEXT_UPDATE_STOCK}</a>
                            {/if}
                        {/if}
                        {if $pInfo->products_id > 0}
                            {if \common\helpers\Warehouses::get_warehouses_count() > 1}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/order-reallocate', 'prid' => $pInfo->products_id])}" class="btn right-link edp-qty-update" data-class="product-relocate-popup">{$smarty.const.TEXT_ORDER_RELOCATE}</a>
                                {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                                    <a href="{Yii::$app->urlManager->createUrl(['categories/warehouses-relocate', 'prid' => $pInfo->products_id])}" class="btn right-link edp-qty-update" data-class="relocate-stock-popup">{$smarty.const.TEXT_WAREHOUSES_RELOCATE}</a>
                                {/if}
                            {/if}
                        {/if}
{/if}
                    </div>
                    <div class="stock-availability">
                        <label>{$smarty.const.TEXT_STOCK_INDICATION}</label>
                        {tep_draw_pull_down_menu('stock_indication_id', \common\classes\StockIndication::get_variants(), $pInfo->stock_indication_id, 'class="form-control form-control-small stock-indication-id" id="product_stock_indication_id"')}
                    </div>

                    <div class="delivery-terms">
                        <div class="delivery-term-section" id='product-delivery-term-section' {if $pInfo->is_virtual}style="display:none;"{/if}>
                            <label>{$smarty.const.TEXT_STOCK_DELIVERY_TERMS}</label>
                            {tep_draw_pull_down_menu('stock_delivery_terms_id', \common\classes\StockIndication::get_delivery_terms(), $pInfo->stock_delivery_terms_id, 'class="form-control form-control-small"')}
                        </div>
                    </div>

                    <div class="stock-reorder stock-reorder-level">
                        {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                            <label>{$smarty.const.TEXT_STOCK_REORDER_LEVEL}&nbsp;<input type="checkbox" {if $pInfo->stock_reorder_level_on}checked {/if}/></label>
                            {Html::input('text', 'stock_reorder_level', $pInfo->stock_reorder_level, ['class'=>'form-control form-control-small-qty'])}
                        {/if}
                    </div>

                    <div class="stock-reorder stock-reorder-quantity">
                        {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                            <label>{$smarty.const.TEXT_STOCK_REORDER_QUANTITY}&nbsp;<input type="checkbox" {if $pInfo->stock_reorder_quantity_on}checked {/if}/></label>
                            {Html::input('text', 'stock_reorder_quantity', $pInfo->stock_reorder_quantity, ['class'=>'form-control form-control-small-qty'])}
                        {/if}
                    </div>

                    <div class="links">
{if (defined('ENABLE_FREEZE_STOCK') && ENABLE_FREEZE_STOCK == '1')}
    <a href="{Yii::$app->urlManager->createUrl('freeze-stock/')}" target="_blank">{$smarty.const.BOX_FREEZE_STOCK} {$smarty.const.TEXT_ENABLED}</a>
{else}
                        {if $pInfo->products_id > 0}
                            {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
                                {assign var="SuppliersQty" value=count(Suppliers::getSuppliersList($pInfo->products_id))}
                                {assign var="WarehousesQty" value=\common\helpers\Warehouses::get_warehouses_count()}
                                {if $SuppliersQty > 1 && $WarehousesQty > 1}
                                    <a href="{Yii::$app->urlManager->createUrl(['categories/stock', 'prid' => $pInfo->products_id])}" class="right-link edp-qty-update">{$smarty.const.TEXT_SUPPLIERS_WAREHOUSES_STOCK}</a>
                                {else}
                                    {if $SuppliersQty > 1}
                                        <a href="{Yii::$app->urlManager->createUrl(['categories/suppliers-stock', 'prid' => $pInfo->products_id])}" class="right-link edp-qty-update">{$smarty.const.TEXT_SUPPLIERS_STOCK}</a>
                                    {/if}
                                    {if $WarehousesQty > 1}
                                        <a href="{Yii::$app->urlManager->createUrl(['categories/warehouses-stock', 'prid' => $pInfo->products_id])}" class="right-link edp-qty-update">{$smarty.const.TEXT_WAREHOUSES_STOCK}</a>
                                    {/if}
                                {/if}
                            {/if}
                            {if $isBundle == false AND (int)$pInfo->temporary_quantity > 0}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/temporary-stock', 'prid' => $pInfo->products_id])}" class="right-link-upd edp-qty-update">{$smarty.const.TEXT_TEMPORARY_STOCK}</a>
                            {/if}
                            {if \common\helpers\Acl::checkExtension('ProductStockHistory', 'productBlock')}
                                {\common\extensions\ProductStockHistory\ProductStockHistory::productBlock($pInfo->products_id)}
                            {else}
                                <span class="right-link dis_module">{$smarty.const.TEXT_STOCK_HISTORY}</span>
                            {/if}
                        {/if}

                        <div class="disabled">
                            {if $pInfo->products_id > 0 && \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')}
                                <a href="{Yii::$app->urlManager->createUrl(['categories/product-assets', 'prid' => $pInfo->products_id])}" class="right-link edp-qty-update">{$smarty.const.TEXT_PRODUCT_ASSETS}</a>
                            {/if}
                        </div>
{/if}
                    </div>

                    <div class="actions">
                        <h4>Actions</h4>

                        <div class="actions-content">
                            {* add block with product behavior *}
                            {* quote request *}
                            <div class="edp-line-heig">
                                <label>{$smarty.const.TEXT_REQUEST_QUOTE}:</label>
                                <input type="checkbox" name="request_quote" value="1" class="check_quote_switch_on_off"{if $pInfo->request_quote == 1} checked{/if} />
                                <span id="request_quote_out_stock" class=""{if $pInfo->request_quote == 0} style="display: none;"{/if}>
                                    <label>{$smarty.const.TEXT_REQUEST_QUOTE_OUT_STOCK}:</label>
                                    <input type="checkbox" name="request_quote_out_stock" value="1" class="check_bot_switch_on_off"{if $pInfo->request_quote_out_stock == 1} checked{/if} />
                                </span>
                            </div>
                            {* product sample.*}
                            <div class="edp-line-heig">
                                <label>{$smarty.const.TEXT_ASK_SAMPLE}:</label>
                                <input type="checkbox" name="ask_sample" value="1" class="check_bot_switch_on_off"{if $pInfo->ask_sample == 1} checked{/if} />
                            </div>
                            {* add to cart *}
                            <div class="edp-line-heig">
                                <label>{$smarty.const.TEXT_CART_BTN}:</label>
                                <input type="checkbox" name="cart_button" value="1" class="check_bot_switch_on_off"{if $pInfo->cart_button == 1} checked{/if} />
                            </div>
                            <div class="edp-line-heig">
                              <label>{$smarty.const.TEXT_ALLOW_BACKORDER}<span class="title-colon">:</span></label>
                              <div class="allow-bck-ord">
                                <label><span class="label-title">{$smarty.const.TEXT_NO}</span>{Html::radio('allow_backorder',$pInfo->allow_backorder == -1, ['value' => -1])}</label>
                                <label><span class="label-title">{$smarty.const.TEXT_DEFAULT} {if $smarty.const.STOCK_ALLOW_BACKORDER_BY_DEFAULT=='True'}({$smarty.const.TEXT_YES}){/if}{if $smarty.const.STOCK_ALLOW_BACKORDER_BY_DEFAULT=='False'}({$smarty.const.TEXT_NO}){/if}</span>{Html::radio('allow_backorder',$pInfo->allow_backorder == 0, ['value' => 0])}</label>
                                <label><span class="label-title">{$smarty.const.TEXT_YES}</span>{Html::radio('allow_backorder',$pInfo->allow_backorder == 1, ['value' => 1])}</label>
                              </div>
                            </div>
                            {* /add block with product behavior/ *}
                        </div>
                    </div>
                </div>

                <div class="edp-line stock-reorder-auto">
                    <label>{$smarty.const.TEXT_PRODUCT_AUTO_REORDER}</label>
                    <input type="checkbox" name="reorder_auto" value="1" class="check_bot_switch_on_off"{if $pInfo->reorder_auto == 1} checked{/if} />
                </div>


                {if $pInfo->products_id > 0}
<script type="text/javascript">
    function products_quantity_update(uprid) {
        var params = [];
        params.push({ name: 'uprid', value: uprid});
        params.push({ name: 'products_quantity_update', value: $('[name="products_quantity_update"]').val()});
        params.push({ name: 'products_quantity_update_prefix', value: $('[name="products_quantity_update_prefix"]:checked').val()});
        params.push({ name: 'warehouse_id', value: $('[name="warehouse_id"]').val()});
        params.push({ name: 'w_suppliers_id', value: $('[name="w_suppliers_id"]').val()});
        params.push({ name: 'stock_comments', value: $('[name="stock_comments"]').val()});

        var loc = [];
        $('[name="box_location[]"]').each(function() {
            loc.push($(this).val());
        });
        params.push({ name: 'box_location', value: loc });

        $('#location').find('input').each(function() {
            params.push({ name: $(this).attr('name'), value: $(this).val() });
        });

        $.post("{Yii::$app->urlManager->createUrl('categories/product-quantity-update')}", $.param(params), function (data, status) {
            if (status == "success") {
                if (data.products_quantity != undefined) {
                    //$('[name="products_quantity_update"]').val('');
                    $('[name="products_quantity"]').val(data.products_quantity);
                    $('#products_quantity_info').html(data.products_quantity);
                }
                if (data.allocated_quantity != undefined) {
                    $('[name="allocated_quantity"]').val(data.allocated_quantity);
                    $('#allocated_quantity_info').html(data.allocated_quantity);
                }
                if (data.temporary_quantity != undefined) {
                    $('[name="temporary_quantity"]').val(data.temporary_quantity);
                    $('#temporary_quantity_info').html(data.temporary_quantity);
                }
                if (data.warehouse_quantity != undefined) {
                    $('[name="warehouse_quantity"]').val(data.warehouse_quantity);
                    $('#warehouse_quantity_info').html(data.warehouse_quantity);
                }
                if (data.ordered_quantity != undefined) {
                    $('[name="ordered_quantity"]').val(data.ordered_quantity);
                    $('#ordered_quantity_info').html(data.ordered_quantity);
                }
                if (data.suppliers_quantity != undefined) {
                    $('[name="suppliers_quantity"]').val(data.suppliers_quantity);
                    $('#suppliers_quantity_info').html(data.suppliers_quantity);
                }
                $('.popup-box-wrap:last').remove();
            } else {
                alert("Request error.");
            }
        }, "json");
    }

    var isReload = false;
    $('.right-link-upd').popUp({
        'box_class': 'popupCredithistory',
        close: function() {
            $('.pop-up-close').click(function() {
                if (isReload == true) {
                    document.location.reload(true);
                } else {
                    $('.popup-box:last').trigger('popup.close');
                    $('.popup-box-wrap:last').remove();
                }
                return false;
            });
            $('.popup-box').on('click', '.btn-cancel', function() {
                if (isReload == true) {
                    document.location.reload(true);
                } else {
                    $('.popup-box:last').trigger('popup.close');
                    $('.popup-box-wrap:last').remove();
                }
                return false;
            });
        }
    });
    $('.right-link').popUp({ 'box_class': 'popupCredithistory'});
</script>
                {/if}




            </div>
        </div>

    {if ($isBundle == false AND (int)$pInfo->manual_stock_unlimited == 0)}
        <div class="widget box box-no-shadow edp-qty-update" style="background: #fff;">
            <div class="widget-header">
                <h4>Stock splitting</h4>
            </div>
            <div class="widget-content widget-content-center">
                <div class="t-row our-pr-line stock-splitting-row">
                    <label for="stock_control_s2"><input type="radio" name="stock_control" class="stock-options" id="stock_control_s2" value="0" {if $pInfo->stock_control=='0'}checked{/if}/>Overall stock</label>
                    <label for="stock_control_s1"><input type="radio" name="stock_control" class="stock-options" id="stock_control_s1" value="1" {if $pInfo->stock_control=='1'}checked{/if}/>Split stock between platforms</label>
                    <label for="stock_control_s0"><input type="radio" name="stock_control" class="stock-options" id="stock_control_s0" value="2" {if $pInfo->stock_control=='2'}checked{/if}/>Assign platform to warehouse</label>

                </div>

                <div class="t-row" id="stock_by_platforms"{if $pInfo->stock_control!='1'} style="display: none;"{/if}>
                    {foreach $pInfo->platformStockList as $platform}
                    <div class="stock-row">
                        <label class="">{$platform.name}:</label>
                        <div class="slider-controls slider-value-top stock-row-qty">
                                {Html::input('text', 'platform_to_qty_'|cat:$platform.id, $platform.qty, ['class'=>'form-control form-control-small-qty platform-to-qty', 'onchange' => 'updateSlider('|cat:$platform.id|cat:');'])}
                        </div>
                        <div id="slider-range-{$platform.id}"></div>
                    </div>
                    {/foreach}

                    <div class="stock-summary">
                        <label class="">Summary:</label>
                        <div class="">
                            <span id="slider-range-qty-total">0 from {$pInfo->products_quantity}</span>
                        </div>
                    </div>
                </div>

                <div class="t-row" id="platform_to_warehouse"{if $pInfo->stock_control!='2'} style="display: none;"{/if}>
                    {foreach $pInfo->platformWarehouseList as $platform}
                        <div class="platform-row">
                            <label>{$platform.name}:</label>
                            <div class="arrow"></div>
                            <div class="">
                                {tep_draw_pull_down_menu('platform_to_warehouse_'|cat:$platform.id, \common\helpers\Warehouses::get_warehouses(), $platform.warehouse, 'class="form-control form-control-small"')}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    </div>
    <div class="cbox-right">
        <div class="widget box box-no-shadow" style="background: #fff;">
            <div class="widget-header">
                <h4>{$smarty.const.TEXT_PRODUCT_IDENTIFIERS}</h4>
            </div>
            <div class="widget-content widget-content-center">
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_MODEL_SKU}</label>
                    {tep_draw_input_field('products_model', $pInfo->products_model, 'class="form-control form-control-small"')}
                </div>
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_EAN}</label>
                    {tep_draw_input_field('products_ean', $pInfo->products_ean, 'class="form-control form-control-small"')}
                </div>
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_ASIN}</label>
                    {tep_draw_input_field('products_asin', $pInfo->products_asin, 'class="form-control form-control-small"')}
                </div>
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_ISBN}</label>
                    {tep_draw_input_field('products_isbn', $pInfo->products_isbn, 'class="form-control form-control-small"')}
                </div>
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_UPC}</label>
                    {tep_draw_input_field('products_upc', $pInfo->products_upc, 'class="form-control form-control-small"')}
                </div>
            </div>
        </div>
        {if \common\helpers\Acl::checkExtension('TypicalOperatingTemp', 'productBlock')}
            {\common\extensions\TypicalOperatingTemp\TypicalOperatingTemp::productBlock($pInfo)}
        {/if}
        <div class="widget box box-no-shadow" style="margin-bottom: 5px;">
            <div class="widget-header">
                <h4>{$smarty.const.IMAGE_DETAILS}</h4>
            </div>
            <div class="widget-content">
                <div class="t-row">
                    {if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'productBlock')}
                        {\common\extensions\MinimumOrderQty\MinimumOrderQty::productBlock($pInfo)}
                    {else}
                        <div class="t-col-2 dis_module">
                            <div class="edp-line">
                                <label>{$smarty.const.TEXT_PRODUCTS_ORDER_QUANTITY_MINIMAL}:</label>
                                <input class="form-control form-control-small-qty" type="text" disabled>
                            </div>
                        </div>
                    {/if}

                    {if \common\helpers\Acl::checkExtension('MaxOrderQty', 'productBlock')}
                        {\common\extensions\MaxOrderQty\MaxOrderQty::productBlock($pInfo)}
                    {else}
                        <div class="t-col-2 dis_module">
                            <div class="edp-line">
                                <label>{$smarty.const.TEXT_PRODUCTS_ORDER_QUANTITY_MAX}:</label>
                                <input class="form-control form-control-small-qty" type="text" disabled>
                            </div>
                        </div>
                    {/if}

                    {if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'productBlock')}
                        {\common\extensions\OrderQuantityStep\OrderQuantityStep::productBlock($pInfo)}
                    {else}
                        <div class="t-col-2 dis_module">
                            <div class="edp-line">
                                <label>{$smarty.const.TEXT_PRODUCTS_ORDER_QUANTITY_STEP}:</label>
                                <input class="form-control form-control-small-qty" type="text" disabled>
                            </div>
                        </div>
                    {/if}
                </div>
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_PRODUCT_SOURCE}:</label>
                    <div class="s_td_group f_td_group-pr" style="position: relative">{Html::textInput('source', $pInfo->source, ['class'=>'form-control js-sources'])}</div>
                </div>
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_DATE_AVAILABLE}</label>
                    {tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="datepicker form-control form-control-small"' )}
                </div>
                <div class="edp-line edp-line-heig">
                    <label>{$smarty.const.TEXT_FEATURED_PRODUCT}</label>
                    <input type="checkbox" name="featured" value="1"
                           class="check_feat_prod"{if $app->controller->view->featured == 1} checked{/if} />
                    <span class="edp-ex edp-ex-sp featured_expires_date"{if $app->controller->view->featured == 0} style="display: none;"{/if}><label>{$smarty.const.TEXT_EXPIRY_DATE}</label>
                                    <input type="text" name="featured_expires_date"
                                           value="{$app->controller->view->featured_expires_date}"
                                           class="datepicker form-control form-control-small"></span>
                </div>
                {if \common\helpers\Acl::checkExtension('Subscriptions', 'productBlock')}
                    {\common\extensions\Subscriptions\Subscriptions::productBlock($pInfo)}
                {else}
                <div class="edp-line edp-line-heig dis_module">
                    <label>{$smarty.const.TEXT_SUBSCRIPTION}:</label>
                    <input type="checkbox" class="check_subscription" disabled />
                    <span class="edp-ex edp-ex-sp edp-ex-s9">
                        <label>{$smarty.const.TEXT_SUBSCRIPTION_CODE}:</label><input type="text" class="form-control form-control-small" disabled>
                    </span>
                </div>
                {/if}
                {if \common\helpers\Acl::checkExtensionAllowed('UploadCustomerId', 'allowed')}
                    {\common\extensions\UploadCustomerId\UploadCustomerId::productBlock($pInfo)}
                {/if}
                {if ($ext = \common\helpers\Acl::checkExtension('Rma', 'getProductReturnTimeHtml'))}
                    {$ext::getProductReturnTimeHtml($pInfo->products_id)}
                {/if}
                <div class="edp-line">
                    <label>{$smarty.const.TEXT_PRODUCTS_PCTEMPLATE}</label>
                    {tep_draw_pull_down_menu('products_pctemplates_id', \common\helpers\Configurator::get_pctemplates(), $pInfo->products_pctemplates_id, 'class="form-control"')}
                </div>
            </div>
        </div>

        {if \common\helpers\Acl::checkExtension('ProductTemplates', 'productBlock')}
            {\common\extensions\ProductTemplates\ProductTemplates::productBlock()}
        {else}
        <div class="widget box box-no-shadow product-frontend-box">
            <div class="widget-header">
                <h4>{$smarty.const.CHOOSE_PRODUCT_TEMPLATE}</h4>
            </div>
            <div class="widget-content widget-content-center dis_module">
                {foreach $app->controller->view->templates.list as $frontend}
                    <div class="product-frontend frontend-disable">
                        <h4>{$frontend.text}</h4>
                        <div>
                            <label>
                                Default
                                <input type="radio" name="product_template[]" value="" class="check_give_wrap" checked disabled>
                            </label>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
        {/if}

        {* Implements Product Design constructor *}
        {if \common\helpers\Acl::checkExtension('ProductDesigner', 'designBlock')}
            {\common\extensions\ProductDesigner\ProductDesigner::designBlock($Key, $pInfo)}
        {/if}
    </div>
</div>
<script type="text/javascript">
var total_stock = {$pInfo->products_quantity};
var allocated_stock = [];

    (function ($) {

        $('.platform-to-qty').quantity({ 'min' : 0});

        $(function () {
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
            {if $pInfo->is_virtual}
                $('.stock-indication-p').hide();
            {else}
                $('.stock-indication-v').hide();
            {/if}

            $('.stock-options').on('click',function() {
                var option = $(this).val();
                if (option == '0') {
                    $('#stock_by_platforms').hide();
                    $('#platform_to_warehouse').hide();
                }
                if (option == '1') {
                    $('#stock_by_platforms').show();
                    $('#platform_to_warehouse').hide();
                }
                if (option == '2') {
                    $('#stock_by_platforms').hide();
                    $('#platform_to_warehouse').show();
                }
            });

            {foreach $pInfo->platformStockList as $platform}
            allocated_stock[{$platform.id}] = {$platform.qty};
            $( '#slider-range-{$platform.id}' ).slider({
                    range: 'min',
                    value: {$platform.qty},
                    min: 0,
                    max: {$pInfo->products_quantity},
                    slide: function( event, ui ) {
                        allocated_stock[{$platform.id}] = ui.value;
                        //$( '#slider-range-qty-{$platform.id}' ).text( ui.value );
                        updateSliderTotal();
                        $('input[name="platform_to_qty_{$platform.id}"]').val(ui.value);
                    }
            });
            //$('#slider-range-qty-{$platform.id}').text($('#slider-range-{$platform.id}').slider('value'));
            updateSliderTotal();
            {/foreach}

        });
        checkWarehouseLocation();

        $('div.stock-reorder input:checkbox')
            .off()
            .on('change', function() {
                $(this).closest('div').find('input:text.form-control').attr('disabled', 'disabled');
                if ($(this).prop('checked') == true) {
                    $(this).closest('div').find('input:text.form-control').removeAttr('disabled');
                }
            })
            .change();
    })(jQuery);
function updateSlider(id) {
    var val = $('input[name="platform_to_qty_'+id+'"]').val();
    $('#slider-range-'+id).slider('value', val);
    allocated_stock[id] = parseInt(val);
    updateSliderTotal();
}
function updateSliderTotal() {
    var total_allocated_stock = 0;
    for (var i in allocated_stock) {
        total_allocated_stock += allocated_stock[i];
    }
    $('#slider-range-qty-total').text(total_allocated_stock + ' from ' + total_stock);
    if ( (total_stock - total_allocated_stock) < 0 ) {
        $('#slider-range-qty-total').css('color', 'red');
    } else {
        $('#slider-range-qty-total').css('color', 'green');
    }
}
function checkWarehouseLocation() {
    var warehouse_id = $('[name="warehouse_id"]').val();
    var suppliers_id = $('[name="w_suppliers_id"]').val();
    var products_id = $('[name="update_stock_id"]').val();
    var prefix = $('[name="products_quantity_update_prefix"]:checked').val();
    if (prefix == '-') {
        $('.amount').hide();
    } else {
        $('.amount').show();
    }
    $.post("categories/warehouse-location", { 'warehouse_id' : warehouse_id, 'suppliers_id' : suppliers_id, 'prefix' : prefix, 'products_id' : products_id }, function(data, status) {
        if (status == "success") {
            $('#location').html(data);
        }
    },"html");
    return false;
}
function checkLocationChild(obj, warehouse_id) {
    var location_id = $(obj).val();
    $.post("categories/warehouse-location-child", { 'location_id' : location_id, 'warehouse_id' : warehouse_id }, function(data, status) {
        if (status == "success") {
            $(obj).parent('div').parent('div').children('div.sublocation').html(data);
        }
    },"html");
    return false;
}
</script>

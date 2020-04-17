{use class="frontend\design\Info"}
{use class = "Yii"}
{use class = "yii\helpers\Html"}
{use class="\frontend\design\boxes\product\Packs"}
{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}

<div class="cart-listing w-cart-listing">
    <div class="{if !$popupMode && $multiCart['enabled']}multi-cart{/if}{if $smarty.const.GROUPS_IS_SHOW_PRICE === false} no-price{/if}">
        {if $promoMessage}{$promoMessage}{/if}
        <div class="headings">
            {if !$multiCart['enabled'] || $popupMode}
                <div class="head remove">{$smarty.const.REMOVE}</div>
            {/if}
            <div class="head image">{$smarty.const.PRODUCTS}</div>
            <div class="head name"></div>
            <div class="head qty">{$smarty.const.QTY}</div>
            <div class="head price">{if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}{$smarty.const.PRICE}{/if}</div>

        </div>

        {foreach $products as $product}
            <div class="item{if strlen($product.parent) > 0} subitem{/if}">

                {if $popupMode || !$multiCart['enabled']}
                    <div class="remove">
                        {if $product.remove_link}
                            <a href="{$product.remove_link}" class="remove-btn">
                                <span style="display: none">{$smarty.const.REMOVE}</span>
                            </a>
                        {/if}
                    </div>
                {/if}

                {\frontend\design\boxes\product\PromotionIcons::widget(['params' => ['product' => $product]])}

                <div class="image">
                    {if $product._status}
                        <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name}"></a>
                    {else}
                        <span><img src="{$product.image}" alt="{$product.name}"></span>
                    {/if}
                </div>


                <div class="name">
                    <table class="wrapper"><tr><td>
                        {if $product._status}
                            <a href="{$product.link}">{$product.name}</a>
                        {else}
                            <span>{$product.name}</span>
                        {/if}
                    </td></tr></table>

                    {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}
                        <span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>
                    {/if}

                    {if $product.stock_info}
                        <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
                    {/if}

                    {Packs::widget(['product' => $product])}

                    <div class="attributes">
                        {foreach $product.attr as $attr}
                            <div class="">
                                <strong>{$attr.products_options_name}:</strong>
                                <span>{$attr.products_options_values_name}</span>
                            </div>
                        {/foreach}
                    </div>

                    {if $product.is_bundle}
                        {foreach $product.bundles_info as $bundle_product }
                            <div class="bundle_product">
                                <table class="wrapper"><tr><td>{$bundle_product.x_name}</td></tr></table>
                                {if $bundle_product.with_attr}
                                    <div class="attributes">
                                        {foreach $bundle_product.attr as $attr}
                                            <div class="attributes-item">
                                                <strong>{$attr.products_options_name}:</strong>
                                                <span>{$attr.products_options_values_name}</span>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                            </div>
                        {/foreach}
                    {/if}
                </div>


                <div class="qty">
                    {if $product.parent == ''}
                        {if $product.ga}
                            <input type="hidden" name="cart_quantity[]" value="{$product.quantity}"/>
                            <span class="qty-readonly">{$product.quantity}</span>
                        {else}
                            {if $product.is_pack > 0 }
                                <input type="hidden" name="cart_quantity[]" value="{$product.quantity}"/>
                                <div class="qty_cart_colors">
                                    <span class="qc_title">{$smarty.const.UNIT_QTY}: </span>
                                    <input type="text" name="cart_quantity_[{$product.id}][0]" value="{$product.units}" class="qty-inp-s" data-min="0"{if $product.in_stock != false} data-max="{$product.in_stock}"{/if}/>
                                </div>
                                <div class="qty_cart_colors">
                                    <span class="qc_title">{$smarty.const.PACK_QTY}: </span>
                                    <input type="text" name="cart_quantity_[{$product.id}][1]" value="{$product.packs}" class="qty-inp-s" data-min="0"{if $product.in_stock != false} data-max="{$product.in_stock/$product.packs}"{/if}/>
                                </div>
                                <div class="qty_cart_colors">
                                    <span class="qc_title">{$smarty.const.CARTON_QTY}: </span>
                                    <input type="text" name="cart_quantity_[{$product.id}][2]" value="{$product.packagings}" class="qty-inp-s" data-min="0"{if $product.in_stock != false} data-max="{$product.in_stock/($product.packs*$product.packagings)}"{/if}/>
                                </div>
                            {else}
                                <input type="text" name="cart_quantity[]" value="{$product.quantity}" class="qty-inp-s"{if $product.stock_info.quantity_max != false} data-max="{$product.stock_info.quantity_max}"{/if}{if \common\helpers\Acl::checkExtension('MinimumOrderQty', 'setLimit')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($product.order_quantity_data)}{/if}{if \common\helpers\Acl::checkExtension('OrderQuantityStep', 'setLimit')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($product.order_quantity_data)}{/if} />
                            {/if}
                        {/if}
                        {$product.hidden_fields}
                    {else}
                        <span class="qty-readonly">{$product.quantity}</span>
                    {/if}
                </div>

                <div class="price">
                    {if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}
                    {$product.final_price}{if $product.standard_price !== false}<br/><small><i>(<strike>{$product.standard_price}</strike>)</i></small>{/if}
                    {if $product.promo_message}
                    <br><small class="promo-message">{$product.promo_message}</small>
                    {/if}
                    {/if}
                </div>

                {if $product.bonus_points_price && $product.bonus_points_price > 0 && $product.bonus_points_cost && $product.bonus_points_cost > 0}
                <div class="points">
                {if $product.bonus_coefficient === false && $product.bonus_points_price && $product.bonus_points_price > 0}
                    <div class="points-redeem">
                        <b>{number_format($product.bonus_points_price * $product.quantity, 0)}</b>
                        {$smarty.const.TEXT_POINTS_REDEEM}
                    </div>
                {/if}
                {if $product.bonus_points_cost && $product.bonus_points_cost > 0}
                    <div class="points-earn">
                        <b>{number_format($product.bonus_points_cost * $product.quantity, 0)}</b>
                        {$smarty.const.TEXT_POINTS_EARN}
                        {if $product.bonus_coefficient !== false}
                            ({\common\helpers\Points::getBonusPointsPriceInCurrencyFormatted($product.bonus_points_cost * $product.quantity, $groupId)})
                        {/if}
                    </div>
                {/if}
                </div>
                {/if}

                {if $product.parent == '' && $popupMode == false}
                    {if $product.gift_wrap_allowed}
                        <div class="gift-wrap">
                            <label>
                                <span class="title">{$smarty.const.BUYING_GIFT}</span>
                                <span class="value">{$product.gift_wrap_price_formated}</span>
                                <input type="checkbox" name="gift_wrap[{$product.id}]" class="check-on-off" {if $product.gift_wrapped} checked="checked"{/if}/>
                            </label>
                        </div>
                    {/if}
                {/if}

                {if !$popupMode && $multiCart['enabled']}
                    {$product['multicart-actions']}
                {/if}


            </div>
        {/foreach}
    </div>
    {if $bound_quantity_ordered}
        <div class="checkout-attention-message">{sprintf($boundMessage, '<span class="attention_mark">'|cat:$smarty.const.TEXT_INSTOCK_BOUND_MARKER|cat:'</span>', '<span class="attention_mark">'|cat:$smarty.const.TEXT_INSTOCK_BOUND_MARKER|cat:'</span>')}</div>
    {/if}
    {if $oos_product_incart}
        <div class="checkout-attention-message">{$smarty.const.TEXT_INFO_OUT_OF_STOCK_IN_CART}</div>
    {/if}
    {if !$popupMode && $multiCart['enabled']}
        {$multiCart['script']}
    {/if}

    <script type="text/javascript">
        tl(function(){
            $('.btn-to-checkout').each(function(){
                {if $allow_checkout == false}
                $(this).css({
                    'opacity': '0.5',
                    'cursor': 'default'
                });
                $(this).attr('data-href', $(this).attr('href')).removeAttr('href')
                {else}
                $(this).css({
                    'opacity': '',
                    'cursor': ''
                });
                if ($(this).attr('data-href')){
                    $(this).attr('href', $(this).attr('data-href'))
                }
                {/if}
            })


            $('body').on('click', '.js-move-item', function(e){
                e.preventDefault();
                $.post($(this).attr('href'),
                    {
                        _csrf : $('input[name=_csrf]').val(),
                        qty   : $(this).closest('.item').find('.qty-inp-s').val()
                    },
                    function(data){
                        if(data.success){
                            alertMessage(data.dialog);
                        }

                    }, 'json');
                return false;
            });


        })




    </script>
    {if $settings[0].editable_products}
        <script type="text/javascript">
            tl([
                '{Info::themeFile('/js/main.js')}',
                '{Info::themeFile('/js/bootstrap-switch.js')}'
            ], function(){

                $('.multi-product-copy').hide();
                $('.multi-product-move').hide();

                var form = $('.cart-listing');

                {\frontend\design\Info::addBoxToCss('quantity')}
                $('input.qty-inp-s').quantity({
                    event: function(){
                        form.trigger('cart-change');
                    }
                }).on('blur', function(){
                    form.trigger('cart-change');
                });

                {\frontend\design\Info::addBoxToCss('switch')}
                $(".check-on-off").bootstrapSwitch({
                    offText: '{$smarty.const.TEXT_NO}',
                    onText: '{$smarty.const.TEXT_YES}',
                    onSwitchChange: function () {
                        form.trigger('cart-change');
                    }
                });

                {\frontend\design\Info::addBoxToCss('preloader')}
                var send = 0;
                form.off('cart-change').on('cart-change', function(){
                    addPreloader()
                    var data = $('input', form).serializeArray();
                    data.push({ name:'_csrf', value:$('input[name="_csrf"]').val()})

                    send++;
                    $.post('{Yii::$app->urlManager->createUrl(['shopping-cart', 'action' => 'update_product'])}', data, function(d){
                        $.get('{Yii::$app->urlManager->createUrl(['checkout'])}', function(d){
                            send--;
                            if (send == 0) {
                                $('.main-content').html(d)
                            }
                        });
                        $(window).trigger('cart_change');
                    });
                });

                $('.multi-product-delete a').on('click', function(e){
                    addPreloader();
                    e.preventDefault();
                    $.get($(this).attr('href'), { no_redirect: true}, function(d){
                        $.get('{Yii::$app->urlManager->createUrl(['checkout'])}', function(d){
                            $('.main-content').html(d)
                        });
                        $(window).trigger('cart_change');
                    });
                });

                function addPreloader(){
                    $('.w-cart-products').css({
                        'position': 'relative'
                    }).prepend('<div class="preloader"></div>')
                }
            })
        </script>
    {/if}
</div>


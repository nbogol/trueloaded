getOrder = function(options){
    var order = {
        updateProductInRow(obj,action, params, callback) {
            var order = this;
            if (typeof unformatMaskMoney == 'function') {
                unformatMaskMoney('.result-price');
            }
            var postData = {
                'action': action,
                'currentCart': $('input[name=currentCart]').val(),
                'uprid' :  encodeURIComponent($(obj).parents('.product_info').find('input[name=uprid]').val()),
                'products_id': $(obj).parents('.product_info').find('input[name=products_id]').val(),
                //'qty': $(obj).parents('.product_info').find('.qty').val(),
                'tax' : $(obj).parents('.product_info').find('.tax').val(),
                'price' : $(obj).parents('.product_info').find('input.result-price').val(),
                'gift_wrap':$(obj).parents('.product_info').find('.gift_wrap').prop('checked')
            }
            if ($(obj).parents('.product_info').find('.qty').is('input')){
                postData['qty'] = $(obj).parents('.product_info').find('.qty').val();
            } else if ($(obj).parents('.product_info').find('.unit_qty').is('input')){
                
                postData['qty_'] = [];
                postData['qty_'][0] = $(obj).parents('.product_info').find('.unit_qty').val();
                if ($(obj).parents('.product_info').find('.pack_qty').is('input')){
                    postData['qty_'][1] = $(obj).parents('.product_info').find('.pack_qty').val();
                }
                if ($(obj).parents('.product_info').find('.packaging_qty').is('input')){
                    postData['qty_'][2] = $(obj).parents('.product_info').find('.packaging_qty').val();
                }                
            }
            
            if( Array.isArray(params) && params.length > 0 ){                
                params.forEach(function(param, i, arr) {
                    postData[param.name] =  param.value;
                });
            }
            $.post($urlCalculateRow, postData, function(data, status){
                if (status == "success") {
                    if (data.product){
                        $(obj).parents('.product_info').find('td.result-price').html(data.product.result_price);
                        $(obj).parents('.product_info').find('input.result-price').setMaskMoney();
                        $(obj).parents('.product_info').find('.final_price_total_exc_tax').html(data.product.total_exc);
                        $(obj).parents('.product_info').find('.final_price_total_inc_tax').html(data.product.total_inc);
                    }
                    order.renderDetails(data);
                    order.processCallback(callback, data);
                    /*$('#shiping_holder').html(data.shipping_details);
                    $('#products_holder').html(data.products_details);                    
                    $('#totals_holder .mask-money').setMaskMoney();
                    $('#message').html(data.message);
                    setPlugin();
                    localStorage.orderChanged = true;*/
                } else {
                    alert("Request error.");
                }
            },"json");
        },
        renderDetails:function(data){
            if (data.hasOwnProperty('products_listing')){
//                $('.widget-content-prod_').replaceWith(data.products_listing);
//                $('.product_info').find('input.result-price').setMaskMoney();
                var table = $('.datatable').DataTable();
                table.draw(false);
            }
            if (data.hasOwnProperty('order_totals')){
                $('.order_totals').replaceWith(data.order_totals);
            }
        },
        renderCheckoutDetails:function(data){            
            if (data.hasOwnProperty('order_totals')){
                $('.order_totals').replaceWith(data.order_totals);
            }
            if (data.hasOwnProperty('shipping_address')){
                $('.shipping-address-box').html(data.shipping_address);
            }
            if (data.hasOwnProperty('billing_address')){
                $('.billing-address-box').html(data.billing_address);
            }
            if (data.hasOwnProperty('shipping')){
                $('.shipping-modules-box').html(data.shipping);
            }
            if (data.hasOwnProperty('payments')){
                $('.payment-modules-box').html(data.payments);
            }
        },
        removeProduct: function(obj,action, callback){
            var order = this;
            var postData = {
                'action': action,
                'currentCart': $('input[name=currentCart]').val(),
                'uprid' :  encodeURIComponent($(obj).parents('.product_info').find('input[name=uprid]').val()),
            }
            $.post($urlCalculateRow, postData, function(data, status){
                if (status == "success") {
                    order.renderDetails(data);
                    order.processCallback(callback, data);
                }
            }, 'json');
        },
        recalculateTotals: function(module, holder, callback, visible){
            var order = this;
            $('.btn-confirm[data-class=popup-update-pay]').attr('disabled', true);
            if (module.length < 1 || module == 'undefined' ) return;
            var postData = {
                'action': 'recalculate_totals',
                'currentCart': $('input[name=currentCart]').val(),
                'update_totals': {}
            };
            
            if (typeof unformatMaskMoney == 'function') {
                unformatMaskMoney('.use-recalculation');
            }
            $.each($('input[name*=update_totals].use-recalculation', holder), function (i,e){
                let control = $(e).data('control');
                if (!postData.update_totals.hasOwnProperty(control)) postData.update_totals[control] = {};
                postData.update_totals[control].in = $('input[name="update_totals['+control+'][in]"]', holder).val();
                postData.update_totals[control].ex = $('input[name="update_totals['+control+'][ex]"]', holder).val();
            });
            
            if (typeof module != 'undefined' && module.length > 0){
                if (visible){
                    if (Array.isArray(module)){
                        $.each(module, function(i,code){
                            if (code == '$ot_custom'){
                                //postData.update_totals_custom['prefix'] = $('select[name="update_totals_custom[prefix]"]').val();
                                //postData.update_totals_custom['desc'] = $('input[name="update_totals_custom[desc]"]').val();
                            } else {
                                postData.update_totals[code] = {};
                                postData.update_totals[code].in = '0';
                                postData.update_totals[code].ex = '0';
                            }
                        });
                    } else {
                        postData.update_totals[code] = {};
                    }
                }
            }
            $.post($urlCheckout, 
                postData
            , function(data, status){
                order.renderDetails(data);
                order.processCallback(callback, data);
                $('.btn-confirm[data-class=popup-update-pay]').attr('disabled', false);
                //$('#totals_holder .mask-money').setMaskMoney();
            }, 'json');
            
        },
        addModule:function(modules, holder, callback){
            var order = this;
            order.recalculateTotals(modules, holder, callback, true);
        },
        resetTotals:function(callback){
            var order = this;
            var postData = {
                'action': 'reset_totals',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.post($urlCheckout, 
                postData,
            function(data, status){
                if (status == 'success'){
                    order.renderDetails(data);
                    order.processCallback(callback, data);
                    //$('#payment_holder').html(data.payment_details);
                    //$('#totals_holder').html(data.order_total_details);
                    //$('#totals_holder .mask-money').setMaskMoney();
                    //$('#message').html(data.message);	
                    //localStorage.orderChanged = true;
                }
            }, 'json' );
        },
        savePaid:function(form, callback){
            var postData = {
                'action': 'update_amount',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.each($(form).serializeArray(), function(i, e){
                postData[e.name] = e.value;
            });
            $.post($urlCheckout, 
                postData
            , function(data, status){
                closePopup();
                order.renderDetails(data);
                order.processCallback(callback, data);
            }, 'json');
        },
        updatePay: function(obj){
            var ot_total = $('input[name="update_totals[ot_total]"]:first').val();
            var ot_paid = $('input[name="update_totals[ot_paid]"]:first').val();            
            $.post("editor/updatepay", {
                'currentCart': $('input[name=currentCart]').val(),
                'ot_total': ot_total,
                'ot_paid': ot_paid,
            }, function(data, status){
                if (status == "success") {
                    var n = $(window).scrollTop();
                    var a = document.createElement('a');a.className="removeIt";
                    $('body').append(a);
                    $(a).popUp({
                        data: data,
                        event:'show',
                        only_show: true,
                        box_class: $(obj).data('class')
                    }).trigger('click');
                    $('.removeIt').remove();
                    $(window).scrollTop(n);
                } else {
                    alert("Request error.");
                }
            },"html");
        },
        checkRefund: function(_radio){
            var order = this;
            var postData = {
                'action': 'check_refund',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $('[value='+_radio+']:radio').parent().addClass('preloader product-frontend disable');
            $.post($urlCheckout, 
                postData
            , function(data, status){
                $('[value='+_radio+']:radio').parent().removeClass('preloader product-frontend disable');
                if (data.hasOwnProperty('value')){
                    $('[value='+_radio+']:radio').next().html(data.text);
                    $('[value='+_radio+']:radio').val(data.value);
                }
                if (data.hasOwnProperty('message')){
                    order.showMessage(data.message, false, 3000);
                }
            }, 'json');
        },
        saveOrder:function(form, extra, type, difference, callback){
            var order = this;
            var postData = {
                'action': 'save_order',
                'currentCart': $('input[name=currentCart]').val(),
                'type':type,
                'difference':difference,
            };
            $.each($(form).serializeArray(), function(i, e){
                postData[e.name] = e.value;
            });
            if (Array.isArray(extra)){
               $.each(extra, function(i, e){
                    postData[e.name] = e.value;
                }); 
            }
            $.post($urlCheckout, 
                postData
            , function(data, status){
                if (data.hasOwnProperty('type') && data.type == 'warning'){
                    order.showMessage(data.message, true);
                } else {
                    order.processCallback(callback, data);
                }
            }, 'json');
        },
        changeAddressList: function(type, value, callback){
            var order = this;
            var postData = {
                'action': 'change_address_list',
                'currentCart': $('input[name=currentCart]').val(),
                'type':type,
                'value':value
            };            
            $.post($urlCheckout, 
                postData
            , function(data, status){
                closePopup();
                order.renderCheckoutDetails(data);
                order.processCallback(callback, data);
            }, 'json');
        },
        processCallback: function(callback, data){
            if (typeof callback == 'function') {
                callback.call(this, data);
            }
        },
        setBillAsShip: function(callback){
            var order = this;
            var postData = {
                'action': 'set_bill_as_ship',
                'currentCart': $('input[name=currentCart]').val(),
            };            
            $.post($urlCheckout, 
                postData
            , function(data, status){
                order.renderCheckoutDetails(data);
                order.processCallback(callback, data);
            }, 'json');
        },
        copyAddress: function(event, holder, newPrefix){
            var box = $('.' + event.data.address_box, holder);
            $('input:visible, select:visible', box).each(function(){
                var id = $(this).attr('id');
                
                if (id){
                    id = id.replace(event.data.address_prefix, '');
                    var analog = $('.'+newPrefix+'billing-address-box [id *='+id+']', holder);
                    if ( $(this).is('input') && analog.is('input')){
                        analog.val($(this).val());
                    } else if ($(this).is('select') && analog.is('select')){
                        analog.val($(this).val());
                    } 
                } else {
                    if ($(this).is(':radio')){
                        var checked = $(this).filter(':checked');
                        if (checked.is('input')){
                            var analog = $('.'+newPrefix+'billing-address-box [value='+checked.val()+']:radio', holder);
                            if (analog.is('input')){
                                analog.prop('checked', true);
                            }
                        }
                    }
                    
                }

            })
            
        },
        removeModule: function(code, callback){
            var order = this;
            $.post($urlCheckout, {
                'action':'remove_module',
                'module':code,
            }, function(data, status){
                order.renderDetails(data);
                //$('#totals_holder').html(data.order_total_details);
                //$('#totals_holder .mask-money').setMaskMoney();
                order.processCallback(callback, data);
            }, 'json');	
        },
        dataChanged: function($frmCheckout, subaction, extra_post, callback) {
            var order = this;
            var postData = {
                'action': subaction,
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.each($frmCheckout.serializeArray(), function(i, e){
                postData[e.name] = e.value;
            });
            if ( extra_post && $.isArray(extra_post) ) {
                $.each(extra_post, function(i, e){
                    postData[e.name] = e.value;
                });
            }
            $.post($urlCheckout, 
                postData
            , function(data, status){
                order.renderCheckoutDetails(data);
                
                if (data.hasOwnProperty('field')){
                    var vat = $('#'+data.field);
                    if (vat){
                        vat.next().text(data.vat_status);
                    }
                }
                order.processCallback(callback, data);
            }, 'json');
        },
        switchUpdate: function(object, state){
            if (object){
              if (state){
                $(object).removeClass('semi_disabled');
                $(object).find('input, button').removeAttr('disabled').removeAttr('readonly');
              }else{
                $(object).addClass('semi_disabled');
                $(object).find('input, button').attr({
                  disabled:'disabled',
                  readonly:'readonly'
                });
              }
            }
        },
        showMessage: function(message, autoclose, time){
            alertMessage('<div class="widget box"><div class="widget-content">'+message+'</div></div>');
            if (autoclose){
                if (typeof time == 'undefined') time = 2000;
                setTimeout(function(){ $('.pop-up-close:last').trigger('click'); }, time);
            }
        },
        saveCart: function(callback){
            var order = this;
            var postData = {
                'action': 'save_cart',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.post($urlCalculateRow, 
                postData
            , function(data, status){
                if (data.hasOwnProperty('message')){
                    order.showMessage(data.message, true);
                    order.processCallback(callback, data);
                }
            }, 'json');
        },
        activate_plus_minus: function (parent_class) {
            var order = this;
            $('body '+parent_class).off().on('click', '.pr_plus', function(e){
                var _this = this;
                var val = $(this).prev('input').val();
                var input = $(this).prev('input');
                var step = parseInt(input.attr('data-step'));
                var max = parseInt(input.attr('data-max'));
                val = parseInt(val) + parseInt(step);
                if (val > max) val = max;
                input.val(val);
                $(input).trigger('change');
                clearInterval(tout);
                if (val > 1) input.siblings('.pr_minus').removeClass('disable');
                if (!input.hasClass('new-product')){
                    tout = setInterval(function(){
                        order.updateProductInRow(_this, 'change_qty');
                        clearInterval(tout);
                    },500);
                }
           });
           
           $('body '+parent_class).on('click', '.pr_minus', function(){
             if ($(this).hasClass('disable')) return;
             var _this = this;
             var input = $(this).next('input');
             var step = parseInt(input.attr('data-step'));
             var min = parseInt(input.attr('data-min'));
             var val = $(this).next('input').val();
             if (val > min){
               val = parseInt(val) - parseInt(step);
               $(this).next('input').siblings('.more').removeClass('disableM');
             }
             if (val < min) val = min;
             clearInterval(tout);
             
             input.val(val);
             $(input).trigger('change');
             if (val < 2) $(parent_class +'.pr_minus').addClass('disable');
             if (!input.hasClass('new-product') /*&& !user_work*/){
                    tout = setInterval(function(){
                        order.updateProductInRow(_this, 'change_qty');
                        clearInterval(tout);
                    },500);
                 }
           });
        },
        getExtraCharge: function($this, action){
            params = [];
            
            $.each($($this).closest('.dataTableContent').find('input, select'), function(i, e){
                params.push( { 'name': $(e).attr('name'), 'value': $(e).val() } );
            })
            
            this.updateProductInRow($this, action, params, function(data){
                
            });
        },
        resetCart:function(){
            postData = {
                'action': 'reset_cart',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.post($urlCalculateRow, postData, function(data, status){
                if (status == "success") {
                    window.location.reload();
                } else {
                    alert("Request error.");
                }
            },"json");
        },
        resetCheckout:function(){
            postData = {
                'action': 'reset_checkout',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.post($urlCheckout, postData, function(data, status){
                if (status == "success") {
                    window.location.reload();
                } else {
                    alert("Request error.");
                }
            },"json");
        },
        saveCheckout: function(form, callback){
            var order = this;
            var postData = {
                'action': 'save_checkout',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.each(form.serializeArray(), function(i, e){
                postData[e.name] = e.value;
            })
            
            $.post($urlCheckout, 
                postData
            , function(data, status){
                if (data.hasOwnProperty('message')){
                    order.showMessage(data.message, true);
                    order.processCallback(callback, data);
                }
                if (data.hasOwnProperty('redirect')){
                    window.location.href = data.redirect;
                }
                if (data.hasOwnProperty('reload')){
                    window.location.reload();
                }
            }, 'json');
        },
        removeCart: function(callback){
            var order = this;
            var postData = {
                'action': 'remove_cart',
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.post($urlCheckout, 
                postData
            , function(data, status){
                order.processCallback(callback, data);
            }, 'json');
        },
        deleteCart: function(id, callback){
            var order = this;
            var postData = {
                'action': 'delete_cart',
                'deleteCart':id,
                'currentCart': $('input[name=currentCart]').val(),
            };
            $.post($urlCalculateRow, 
                postData
            , function(data, status){
                order.processCallback(callback, data);
            }, 'json');
        },
        reassignCustomer: function (cid){
            var order = this;
            var postData = {
                'action': 'reassign_customer',
                'currentCart': $('input[name=currentCart]').val(),
                'customers_id': cid
            };
            $.post($urlCheckout, 
                postData
            , function(data, status){
                if (status == "success") {
                    if (data.hasOwnProperty('url')){
                        window.location.href = data.url;
                    } else 
                    window.location.reload();
                }
            }, 'json');
        },
        plugins:[],
        addPlugin: function(plug){
            this.plugins.push(plug)
        },
        startPlugins:function(){
            this.plugins.forEach(function(e){
                e.call(this);
            })
        },
        collapse: function(parent){
            $('.widget .toolbar .widget-collapse', parent).click(function() {
                var widget         = $(".widget", parent);
                var widget_content = widget.find(".widget-content");
                var widget_header  = widget.find(".widget-header");
                var widget_chart   = widget.find(".widget-chart");
                var divider        = widget.find(".divider");

                if (widget.hasClass('widget-closed')) {
                    // Open Widget
                    $(widget_header).find('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                    widget_content.slideDown(200, function() {
                        widget.removeClass('widget-closed');
                    });
                    widget_chart.slideDown(200);
                    divider.slideDown(200);
                } else {
                    // Close Widget
                    $(widget_header).find('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                    widget_content.slideUp(200, function() {
                        widget.addClass('widget-closed');
                    });
                    widget_chart.slideUp(200);
                    divider.slideUp(200);
                }
            });
        }
    };
    return order;
}

var order = new getOrder();

$(document).ready(function() { 

$('.unstored_carts .del-pt').click(function(){
        var that = this;
        bootbox.dialog({
            message: $(that).prev().html()+"<br> "+ $tranlations.TEXT_CONFIRM_DELETE,
            title: $tranlations.ICON_WARNING,
            buttons: {
                success: {
                        label: $tranlations.TEXT_BTN_YES,
                        className: "btn-delete",
                        callback: function() {
                            order.deleteCart($(that).attr('data-id'), function(data){
                                if (data.hasOwnProperty('reload')){
                                    window.location.reload();
                                } else if(data.hasOwnProperty('goto')){
                                    window.location.href = data.goto;
                                }
                            })
                        }
                },
                main: {
                        label: $tranlations.TEXT_BTN_NO,
                        className: "btn-cancel",
                        callback: function() {
                           
                        }
                }
            }
        }); 
        
    });
    
});
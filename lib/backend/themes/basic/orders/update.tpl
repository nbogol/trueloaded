{\backend\components\Currencies::widget(['currency' => $manager->get('currency')])}
<div class="gridBg contentContainer">
    <div class="btn-bar btn-bar-top after">
        <div class="btn-left">
            <form action="{$app->urlManager->createUrl('orders/process-order')}" method="get" class="go-to-order" style="margin-left: 20px">
            {$smarty.const.TEXT_GO_TO_ORDER} <input type="text" class="form-control" name="orders_id"/> <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_GO}</button>
            </form>
        {if $ref_id}
           {$smarty.const.TEXT_REORDER_FROM}<a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $ref_id])}">{$ref_id}</a>
        {/if}
        </div>
        {if $dropshipping}{$dropshipping->renderOrderProcessButton()}{/if}
        <div class="btn-right">
        <a href="{$app->urlManager->createUrl(['orders/order-history', 'orders_id' => $order->order_id, 'cid' => $customer_id])}" class="btn-link-create popup">{$smarty.const.TEXT_ORDER_LEGEND}</a><span class="print_but" onclick="printDiv()">Print</span>
        {*if $order->hasTransactions()*}
            <a href="{$app->urlManager->createUrl(['orders/transactions', 'orders_id' => $order->order_id])}" class="btn popup btn-transaction" data-class="transactions-popup-box">Transactions</a>
        {*/if*}
        {if ($ext = \common\helpers\Acl::checkExtension('Ebay', 'adminCrmMessageRenderButton'))}
            {$ext::adminCrmMessageRenderButton($order->order_id)}
        {/if}
        {if $fraudView}{$fraudView->head()}{/if}
        {if \common\helpers\Acl::rule(\common\helpers\AdminBox::buildNavigation('BOX_REPORT_VOLO_BATCH_SUMMARY'))}
        <a href="{$app->urlManager->createUrl(['report-volo-batch-summary/popup', 'orders_id' => $order->order_id])}" class="btn popup" data-class="popupEditCat" id="button-report-volo-batch-summary-journal">{$smarty.const.IMAGE_JOURNAL}</a>
        {/if}
        <a href="{$app->urlManager->createUrl(['orders/hold-on', 'orders_id' => $order->order_id])}" class="btn popup btn-hold-on{if $order->isHoldOn()} holdOnOrder{/if}"><i class="icon-pushpin"></i>&nbsp;{$smarty.const.IMAGE_HOLD_ON}</a>
        <a href="{$app->urlManager->createUrl(['editor/order-edit', 'orders_id' => $order->order_id])}" class="btn btn-delete btn-edit">{$smarty.const.IMAGE_EDIT}</a>
        <a href="javascript:void(0)" onclick="return deleteOrder({$order->order_id});" class="btn btn-delete">{$smarty.const.IMAGE_DELETE}</a></div>
    </div>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
<link href="{{$smarty.const.DIR_WS_ADMIN}}/css/fancybox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$smarty.const.DIR_WS_ADMIN}/js/jquery.fancybox.pack.js"></script>

<!--===Process Order ===-->
<div class="row w-or-prev-next">
    {if $app->controller->view->order_prev > 0}
    <a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $app->controller->view->order_prev])}" class="btn-next-prev-or btn-prev-or" title="{$smarty.const.TEXT_GO_PREV_ORDER} (#{$app->controller->view->order_prev})"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-prev-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_PREV_ORDER}"></a>
    {/if}
    {if $app->controller->view->order_next > 0}
    <a href="{$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $app->controller->view->order_next])}" class="btn-next-prev-or btn-next-or" title="{$smarty.const.TEXT_GO_NEXT_ORDER} (#{$app->controller->view->order_next})"></a>
    {else}
    <a href="javascript:void(0)" class="btn-next-prev-or btn-next-or btn-next-prev-or-dis" title="{$smarty.const.TEXT_GO_NEXT_ORDER}"></a>
    {/if}
    <div class="col-md-12" id="order_management_data">
        {include "process-order.tpl"}
    </div>
    {$manager->render('DeleteOrder', ['order' => $order])}
</div>
<!-- Process Order -->
<script type="text/javascript">

function check_form() {

    $.post("{$app->urlManager->createUrl('orders/ordersubmit')}", $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            $("#order_management_data").html(data.content);
            dialog = bootbox.dialog({
                message: wrap(data.message.messages),
                buttons: {
                    ok: {
                        label: "{$smarty.const.TEXT_BTN_OK}",
                        className: 'btn-info',
                        callback: function(){
                        }
                    }
                }
            });
        } else {
            alert("Request error.");
        }
    },"json");
    return false;
}
function resetStatement() {
     window.history.back();
    return false;
}
function closePopup() {
    $('.popup-box').trigger('popup.close');
    $('.popup-box-wrap').remove();
    return false;
}

function wrap(data){
    let _context = '';
    if (Array.isArray(data)){
        $.each(data, function(i, e){
            _context = _context + "<br/><div class='alert fade in alert-"+e.type+"'><span id='message_plce'>"+e.text+"</span></div>";
        })
    } else {
        _context = "<br/><div class='alert fade in alert-"+data.type+"'><span id='message_plce'>"+data.text+"</span></div>";
    }
    return _context;
}

$(document).ready(function() {
    $('a.btn-link-create.popup').popUp({
        box_class:'legend-info'
    });


    $('.fancybox').fancybox({
      nextEffect: 'fade',
      prevEffect: 'fade',
      padding: 10
    });

	$('body').on('click', '.fancybox-wrap', function(){
		$.fancybox.close();
	})

    $('.ajax-submit').on('click', function(){
        $.post($(this).attr('href'), $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            dialog = bootbox.dialog({
                message: wrap(data),
                buttons: {
                    ok: {
                        label: "{$smarty.const.TEXT_BTN_OK}",
                        className: 'btn-info',
                        callback: function(){
                        }
                    }
                }
            });

            //$("#order_management_data").html(data);
        } else {
            alert("Request error.");
        }
    },"json");
	return false;
    });

    $.post('{$app->urlManager->createUrl(['report-volo-batch-summary/journal-status', 'orders_id' => $order->order_id])}', { }, function(data, status) {
        if (status == "success") {
            if (typeof(data.status) != 'undefined') {
                if (data.status == 'error') {
                    $('#button-report-volo-batch-summary-journal').css('color', 'red');
                } else if (data.status == 'ok') {
                    $('#button-report-volo-batch-summary-journal').css('color', 'lightgreen');
                }
            }
        }
    }, 'json');
});
function printDiv() {
 window.print();
 window.close();
}
</script>
<style>
@media print {
a[href]:after {
   content:"" !important;
}
#content, #container, #container > #content > .container{
	margin:0 !important;
}
#sidebar, header, .btn-bar, .top_header, .pra-sub-box .pra-sub-box-map:nth-child(2), .btn-next-prev-or, .btn-next-prev-or.btn-next-or, .footer{
	display:none !important;
}
.pr-add-det-box.pr-add-det-box02.pr-add-det-box03 .pra-sub-box-map{
	width:100%;
}
.pr-add-det-box.pr-add-det-box03 .pra-sub-box-map .barcode{
margin-top:-132px !important;
}
.box-or-prod-wrap{
padding:0 !important;
}
.filter-wrapp{
display:none;
}
}
</style>
        <!-- /Page Content -->
</div>

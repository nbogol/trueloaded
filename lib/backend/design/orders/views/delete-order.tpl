<script>
function deleteOrder(orders_id) {
    bootbox.dialog({
        message: '{$smarty.const.TEXT_INFO_DELETE_INTRO}<br /><br /><div class="restock"><label class="restock"><input type="checkbox" class="uniform" name="restock" id="restock" value="1" {$restock_disabled} {$restock_selected}> {$smarty.const.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY}</label></div>',
        title: "{$smarty.const.TEXT_INFO_HEADING_DELETE_ORDER}",
        buttons: {
            success: {
                label: "{$smarty.const.TEXT_BTN_OK}",
                className: "btn-delete",
                callback: function () {
                    $.post("{\Yii::$app->urlManager->createUrl('orders/orderdelete')}", {
                        'orders_id': orders_id, 'restock': ($("#restock").is(':checked') ? 'on' : 0)
                    }, function (data, status) {
                        if (status == "success") {
                            $("#order_management_data").html('');
                            window.location.href = "{\Yii::$app->urlManager->createUrl('orders/')}";
                        } else {
                            alert("Request error.");
                        }
                    }, "html");
                }
            }
        }
    });
    return false;
}
</script>
{use class="yii\helpers\Html"}
{use class="yii\helpers\Url"}
<div class="pr-add-det-box pr-add-det-box01 after">
    {if $customer_id}
        <div class="cr-ord-cust">
            <span>{$smarty.const.T_CUSTOMER}</span>
            <div class="order-customer-address">
            {if $customerExists}
                {Html::a($customerLink, Url::to(['customers/customeredit', 'customers_id' => $customer_id]))}
            {else}
                {$order->customer['name']}
            {/if}
            </div>
        </div>
        <div class="cr-ord-cust cr-ord-cust-email">
            <div>{Html::a($order->customer['email_address'], 'mailto:'|cat:$order->customer['email_address'])}</div>
        </div>
		{if $order->customer['telephone']}
        <div class="cr-ord-cust cr-ord-cust-phone">
            <div>{$order->customer['telephone']}</div>
        </div>
		{/if}

        {if $customerExists && \common\helpers\Acl::checkExtensionAllowed('UploadCustomerId', 'allowed')}
            {\common\extensions\UploadCustomerId\UploadCustomerId::downloadCustomerIdBlock($customer_id)}
        {/if}
    {else}
        <div class="cr-ord-cust walkin-order">
            <div>{$smarty.const.TEXT_WALKIN_ORDER} {$admin_name}</div>
        </div>
    {/if}
</div>
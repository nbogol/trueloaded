<div class="unstored_carts">
    {if !$opened}
        <a href="{\yii\helpers\Url::to(['editor/order-edit', 'orders_id' => $orders_id, 'currentCart'=>$cart])}">
    {else}
        <span>
    {/if}
    {if $customer}
        {$customer['customers_firstname']} {$customer['customers_lastname']}'
    {else}
        {$basketId}
    {/if}
     Cart 
    {if $opened}
        (Opened)
        </span>
    {else}
        </a>
    {/if}
    <div class="del-pt" data-id="{$cart}"></div>
 </div>
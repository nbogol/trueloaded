{if $image['href']}<a href="{$image['href']}">{/if}<img src="{$image['src']}" {if $image['alt']}alt="{$image['alt']|escape:'html'}" title="{$image['alt']|escape:'html'}" {/if}{if $image['height']} height="{$image['height']}"{/if}{if $image['height']} width="{$image['width']}"{/if} border="0">{if $image['href']}</a>{/if}
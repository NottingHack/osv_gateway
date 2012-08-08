{assign var="title" value="Buy Product"}
{include file="fixedwidth_header.tpl"}

<p>{$product.name}: &pound;{($product.price / 100)|string_format:"%0.2f"}</p>

<p>From {$product.machine} @ {$product.location}</p>

{* Buy it now button *}
<form action="{$paypal.url}" method="post">
<input type="hidden" name="business" value="{$paypal.business}" />
<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="charset" value="utf-8" />
<input type="hidden" name="return" value="http://localhost:4000/osv/vendproduct.php" />
<input type="hidden" name="currency_code" value="{$currency.code}" />
<input type="hidden" name="amount" value="{$product.price / 100}" />
<input type="hidden" name="item_name" value="{$product.name}" />
<input type="hidden" name="item_number" value="{$product.machine_id}-{$product.hopper_id}" />
<input type="hidden" name="custom" value="{$trans_id}" />
<input type="image" name="submit" border="0" src="https://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif" alt="PayPal - The safer, easier way to pay online" />
</form>





{include file="fixedwidth_footer.tpl"}

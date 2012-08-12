{assign var="title" value="Vend Product"}
{include file="fixedwidth_header.tpl"}

<p>Thank you for purchasing {$product}.</p>

<p>Clicking the button below will send a message to the vending machine to actually vend the product, so make sure you are near the machine before you press it!</p>

<form action="vendproduct.php?govend={$trans}" method="POST">
<input type="submit" name="vend" value="Vend!" />
</form>


{include file="fixedwidth_footer.tpl"}

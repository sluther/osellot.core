				<div class="leftcolumn products">
					<h2>Products</h2>
					<div class="lcontentbox">
						{foreach $products as $product}
						<div class="{$product->sku}">
							<h3>{$product->name}</h3>
							${number_format($product->price, 2)}
							{if array_key_exists($product->id, $cart.items)}
							<span class="edit">
							   <a href="{devblocks_url}c=order&a=cart&section=remove&item_id={$product->id}{/devblocks_url}" class="button">&ndash;</a>
							   <a href="{devblocks_url}c=order&a=cart&section=add&item_id={$product->id}{/devblocks_url}" class="button">+</a>
							</span>
							{else}
							<span class="add">
								<a class="button" href="{devblocks_url}c=order&a=cart&section=add&item_id={$product->id}{/devblocks_url}">Add to cart</a>
							</span>
							{/if}
						</div>
						{/foreach}
					</div>
				</div>
				<div class="rightcolumn">
					<h2>My cart</h2>
					<div class="rcontentbox">
						{$total = 0}
						{foreach $cart.items as $item_id => $quantity}
						{$item = DAO_Product::get($item_id)}
						<div>
							<strong>{$quantity}x</strong> {$item->name} (${number_format($item->price,2)} ea) <span>${number_format($item->price * $quantity, 2)} </span>
						</div>
						{$total = $total + $item->price * $quantity}
						{foreachelse}
						<div>
		                       Add products to your cart from the left.
		                </div>
						{/foreach}
						<div class="total">
							<span>${number_format($total, 2)}</span>
						</div>
						<form id="buy" method="post" action="">
							<div class="submit">
								<a class="button" href="{devblocks_url}c=order&a=checkout{/devblocks_url}">Proceed to checkout</a>
							</div>
						</form>
						<br>
						<div class="return"><a class="button" href="{devblocks_url}c=account{/devblocks_url}">&#8249; Back to my account</a></div>
					</div>
				</div>
			<div id="history">
				<table summary="History of previous orders placed online">
					<tbody><tr>
						<th>Order #</th>
						<th>Order date</th>
						<th>Ship date</th>
						<th>Products</th>
						<th>Cost</th>
					</tr>
					{foreach $invoices as $invoice}
					<tr>
						<td>{$invoice->number}</td>
						<td>{date('F d, Y', $invoice->created_date)}</td>
						<td>{$invoice->ship_date}</td>
						<td>
							<ul>
								{foreach $invoice->getItems() as $item}
								<li><strong>{$item->quantity}x</strong> {DAO_Product::get($item->product_id)->name} (${number_format($item->amount, 2)} ea)</li>
								{/foreach}
							</ul>
						</td>
						<td>{if $invoice->status != 0}{number_format($invoice->amount_paid, 2)}{else}{number_format($invoice->amount, 2)}{/if}</td>
					</tr>
					{/foreach}
				</tbody></table>
				<div class="return"><a href="{devblocks_url}c=account{/devblocks_url}" class="button">‹ Back to my account</a></div>
			</div>
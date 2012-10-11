<a href="javascript:;" class="menu">Billing <span>&#x25be;</span></a>
<ul class="cerb-popupmenu cerb-float" style="display:none;">
	
	{$exts = Extension_PageMenuItem::getExtensions(true, 'core.page.configuration','menu.setup.osellot.core')}
	{if !empty($exts)}<li><hr></li>{/if}
	{foreach from=$exts item=menu_item}
		{if method_exists($menu_item,'render')}<li>{$menu_item->render()}</li>{/if}
	{/foreach}
	
</ul>
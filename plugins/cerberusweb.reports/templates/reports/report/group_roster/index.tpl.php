<div id="headerSubMenu">
	<div style="padding-bottom:5px;">
	</div>
</div>

<h2>Group Rosters</h2>
<br>

{if !empty($groups)}
{foreach from=$groups item=group key=group_id}
	<div class="block">
		<h2>{$group->name}</h2>
		{if isset($rosters.$group_id)}
			<ul style="margin:5px;">
			{foreach from=$rosters.$group_id item=member key=member_id}
				<li style="list-style-type:square;">{$workers.$member_id->getName()} ({if $member->is_manager}Manager{else}Member{/if})</li>
			{/foreach}
			</ul>
		{/if}
	</div>
	<br>
{/foreach}
{/if}
{include file='header.tpl'}

<link rel="stylesheet" type="text/css" href="{$aTemplateWebPathPlugin.page|cat:'css/style.css'}" media="all" />


<div>
	<h2>{$aLang.plugin.page.admin}</h2>
	
	
	{if $aParams.0=='new'}
		<h3>{$aLang.plugin.page.create}</h3>
		{include file=$aTemplatePathPlugin.page|cat:'actions/ActionPage/add.tpl'}
	{elseif $aParams.0=='edit'}
		<h3>{$aLang.plugin.page.edit} «{$oPageEdit->getTitle()}»</h3>
		{include file=$aTemplatePathPlugin.page|cat:'actions/ActionPage/add.tpl'}
	{else}
		<a href="{router page='page'}admin/new/" class="page-new">{$aLang.plugin.page.new}</a><br /><br />
	{/if}


	<table cellspacing="0" class="table">
		<thead>
			<tr>
				<td width="180px">{$aLang.plugin.page.admin_title}</td>
				<td align="center" >{$aLang.plugin.page.admin_url}</td>    	
				<td align="center" width="50px">{$aLang.plugin.page.admin_active}</td>    	   	
				<td align="center" width="70px">{$aLang.plugin.page.admin_main}</td>    	   	
				<td align="center" width="80px">{$aLang.plugin.page.admin_action}</td>
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$aPages item=oPage name=el2} 	
				<tr>
					<td>
						<img src="{$aTemplateWebPathPlugin.page|cat:'images/'}{if $oPage->getLevel()==0}folder{else}document{/if}.gif" alt="" title="" border="0" style="margin-left: {$oPage->getLevel()*20}px;"/>
						<a href="{router page='page'}{$oPage->getUrlFull()}/">{$oPage->getTitle()}</a>
					</td>
					<td>
						/{$oPage->getUrlFull()}/
					</td>   
					<td align="center">
						{if $oPage->getActive()}
							{$aLang.plugin.page.admin_active_yes}
						{else}
							{$aLang.plugin.page.admin_active_no}
						{/if}
					</td>
					<td align="center">
						{if $oPage->getMain()}
							{$aLang.plugin.page.admin_active_yes}
						{else}
							{$aLang.plugin.page.admin_active_no}
						{/if}
					</td>
					<td align="center">  
						<a href="{router page='page'}admin/edit/{$oPage->getId()}/"><img src="{$aTemplateWebPathPlugin.page|cat:'images/edit.png'}" alt="{$aLang.plugin.page.admin_action_edit}" title="{$aLang.plugin.page.admin_action_edit}" /></a>
						<a href="{router page='page'}admin/delete/{$oPage->getId()}/?security_ls_key={$LIVESTREET_SECURITY_KEY}" onclick="return confirm('«{$oPage->getTitle()}»: {$aLang.plugin.page.admin_action_delete_confirm}');"><img src="{$aTemplateWebPathPlugin.page|cat:'images/delete.png'}" alt="{$aLang.plugin.page.admin_action_delete}" title="{$aLang.plugin.page.admin_action_delete}" /></a>
						<a href="{router page='page'}admin/sort/{$oPage->getId()}/?security_ls_key={$LIVESTREET_SECURITY_KEY}"><img src="{$aTemplateWebPathPlugin.page|cat:'images/up.png'}" alt="{$aLang.plugin.page.admin_sort_up}" title="{$aLang.plugin.page.admin_sort_up} ({$oPage->getSort()})" /></a>
						<a href="{router page='page'}admin/sort/{$oPage->getId()}/down/?security_ls_key={$LIVESTREET_SECURITY_KEY}"><img src="{$aTemplateWebPathPlugin.page|cat:'images/down.png'}" alt="{$aLang.plugin.page.admin_sort_down}" title="{$aLang.plugin.page.admin_sort_down} ({$oPage->getSort()})" /></a>
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>


{include file='footer.tpl'}
<!doctype html>  
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>{$module->getTitle($module_response)} | {$page_title}</title>
		<meta name="description" content="A non-profit alternative distribution system for sustainably produced fruits and vegetables including local, regional, unsprayed, transitional, and organic produce.">
		<link rel="stylesheet/less" type="text/css" href="{devblocks_url}c=resource&p=osellot.core&f=portal/less/style.less{/devblocks_url}?v={$smarty.const.APP_BUILD}">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script> 
		<script src="{devblocks_url}c=resource&p=osellot.core&f=portal/js/less.min.js{/devblocks_url}?v={$smarty.const.APP_BUILD}"></script> 
		<script src="{devblocks_url}c=resource&p=osellot.core&f=portal/js/custom.js{/devblocks_url}?v={$smarty.const.APP_BUILD}"></script>

		<script type="text/javascript">
	          $(document).ready(function(){
	               $("label + input, label + textarea").each(function (type) {
	                   $(this).focus(function () {
	                        $(this).prev("label").addClass("focus");
	                   });
	                   $(this).keypress(function () {
	                        $(this).prev("label").addClass("has-text").removeClass("focus");
	                   });
	                   $(this).blur(function () {
	                        if($(this).val() == "") {
	                            $(this).prev("label").removeClass("has-text").removeClass("focus");
	                        }
	                   });
	                  if($(this).val() != "") {
	                        $(this).prev("label").addClass("has-text").removeClass("focus");
	                    }
	              });
	          });
	      </script>
	</head>
	<body class="{$module->getClass($module_response)}">
 	<div id="wrapper">
	 	<header>
		 	<div id="logo">
		 		<a href="#"><img src="logo.png" /></a>
		 	</div>
		 	<nav id="topnav">
		 		<ul><li id="selected"><a href="#">Register</a></li><li><a href="#" >Home</a></li><li><a href="#">Elsewhere</a></li><li><a href="#">Test</a></li><li><a href="#">Test</a></li></ul>
		 	</nav>
	 	</header>

	    <div id="main">
			<!-- <h1>{$module->getHeader($module_response)}</h1>-->
			{if !empty($active_profile)}
				<div class="account">Signed in as: {$active_profile->getPrimaryAddress()->email} ({if !$is_agency}<a href="{devblocks_url}a=account&c=edit{/devblocks_url}">edit account</a> | <a href="{devblocks_url}c=login&a=signout{/devblocks_url}">sign-out</a>{else}<a href="{devblocks_url}c=agency&a=login&action=signout{/devblocks_url}">sign-out</a>{/if}) </div>
			{/if}
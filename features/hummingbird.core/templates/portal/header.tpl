<!doctype html>  
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>{$module->getTitle($module_response)} | The Good Food Box</title>
		<meta name="description" content="A non-profit alternative distribution system for sustainably produced fruits and vegetables including local, regional, unsprayed, transitional, and organic produce.">
		<link rel="stylesheet" type="text/css" media="all" href="{devblocks_url}c=resource&p=hummingbird.core&f=portal/css/main.css{/devblocks_url}?v={$smarty.const.APP_BUILD}">
		<!--[if lt IE 9]>
		<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
		<link rel="stylesheet" type="text/css" media="screen" href="./layout/ie.css">
		<![endif]-->
		<!--[if lt IE 8]><style type="text/css">body { background-position: 0 2.25em; }</style><![endif]-->

		<!--[if !IE]>-->
		<link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px), only screen and (max-width: 480px)" href="./layout/mobile.css">
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
		<!--<![endif]-->
		<link rel="stylesheet" type="text/css" media="print" href="{devblocks_url}c=resource&p=hummingbird.core&f=portal/css/print.css{/devblocks_url}?v={$smarty.const.APP_BUILD}">
		<script src="{devblocks_url}c=resource&p=hummingbird.core&f=portal/js/modernizr-1.7.min.js{/devblocks_url}?v={$smarty.const.APP_BUILD}/"></script> 
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script> 
		<script src="{devblocks_url}c=resource&p=hummingbird.core&f=portal/js/scripts.js{/devblocks_url}?v={$smarty.const.APP_BUILD}"></script>

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
	    <header role="banner" class="vcard">
			<div id="accessibility">
			  <a href="#nav">Skip to Navigation</a> 
			  <a href="#main-content">Skip to Content</a>
			</div>

			<div class="logotype">
				<a href="http://thegoodfoodbox.ca/"><strong>The Good Food Box</strong></a><br>
				<span>A non-profit alternative distribution system for sustainably produced fruits and vegetables including local, regional, unsprayed, transitional, and organic produce.</span>
			</div>
			<div class="contact">
				<a class="email" href="mailto:info@thegoodfoodbox.ca">info@thegoodfoodbox.ca</a> 
				<a class="tel" href="tel:+1-250-381-1552;ext=172">+1 (250) 381-1552 ext. 172</a>

			</div>
		</header>
		<div id="main-nav">
			<nav role="navigation">
				<ul>
					<li><a href="/about/"><span><strong>About</strong><br> the Good Food Box program</span></a></li>
					<li><a href="/boxes/"><span><strong>Boxes</strong><br> what, how &amp; where to buy</a></span></li>

					<li class="selected"><a href="http://order.thegoodfoodbox.ca/"><span><strong>Ordering</strong><br> order online or in person</a></span></li>
					<li><a href="/contact/"><span><strong>Contact</strong><br> reach us by phone or email</a></span></li>
					<li><a href="/news/"><span><strong>News</strong><br> our newsletter &amp; weblog</a></span></li>
				</ul>
			</nav>
		</div>

	    <section id="main-content" role="main">
			{if $module->hasCustomMenu()}
			<ol class="nav">
			{foreach $module->renderCustomMenu($module_response) as $item}
			<li><a href="{$item.url}">{$item.title}</a></l>
			{/foreach}
			</ol>
			{/if}
			{if $is_agency}<div class="wrapper">{/if}
			<h1>{$module->getHeader($module_response)}</h1>
			{if !empty($active_profile)}
				<div class="account">Signed in as: {$active_profile->getPrimaryAddress()->email} ({if !$is_agency}<a href="{devblocks_url}a=account&c=edit{/devblocks_url}">edit account</a> | <a href="{devblocks_url}c=login&a=signout{/devblocks_url}">sign-out</a>{else}<a href="{devblocks_url}c=agency&a=login&action=signout{/devblocks_url}">sign-out</a>{/if}) </div>
			{/if}
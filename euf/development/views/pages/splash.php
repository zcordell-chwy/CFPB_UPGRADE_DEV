<?php
/*
*	This file contains a list of IP addressess to allow during upgrade cutover.
*	There are a few files and configs involved that make the magick happen:
*
*	scripts/custom/upgrade-allowed-ips.php //list of ips, placed in custom scripts folder so we do not have to deploy updates to the list
*	scripts/euf/application/models/custom/splash_model.php	//does the ip restriction
*	scripts/euf/application/config/hooks.php	//configure a model/function to call before page is rendered to the browser
*	scripts/euf/application/views/splash.php	//splash page non-authorized users will see
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if IE 6 ]> <html class="ie6" xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US"> <![endif]-->
<!--[if IE 7 ]> <html class="ie7" xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US"> <![endif]-->
<!--[if IE 8 ]> <html class="ie8" xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US"> <![endif]-->
<!--[if IE 9 ]> <html class="ie9" xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US"> <!--<![endif]-->
<head>
	<meta charset="UTF-8" />
	<meta property="og:title" content="Get Help Now &#8211; Consumer Financial Protection Bureau"/>
	<meta property="og:type" content="government"/>
	<meta property="og:url" content="https://help.consumerfinance.gov/"/>
	<meta property="og:site_name" content="Consumer Financial Protection Bureau"/>
	<meta property="fb:page_id" content="141576752553390" />
	<meta property="fb:app_id" content="210516218981921" />
	<meta http-equiv="Pragma" content="no-cache"/>
	<meta http-equiv="Expires" content="-1"/>
	<title>Consumer Financial Protection Bureau</title>
	<base href='https://vangent--upgrade.custhelp.com/euf/assets/themes/cfpb/'></base>
	<link rel="shortcut icon" href="/euf/assets/themes/cfpb/images/favicona.png">
	<link rel="apple-touch-icon" href="/euf/assets/themes/cfpb/images/faviconb.png">
	<link type="text/css" rel="stylesheet" href="/euf/assets/themes/cfpb/ps_site.css" />
	<link rel="stylesheet" type="text/css" media="all" href="/euf/assets/themes/cfpb/style.css" />
</head>
<body>
	<div class="wrapper-banner">
		<span class="official-website">An official website of the United States Government</span>
	</div>
	<div style="text-align:center; margin:40px 0;"><img src="/euf/assets/themes/cfpb/images/wV2-logo.png"/></div>
	<div id="maintenance-alert">
		<h1>You cannot submit a complaint at this time.</h1>
		<p>We’re working on improving the system we use to accept and process
		complaints this weekend, so you won’t be able submit a new complaint, check
		your complaint status, or Tell Your Story until <font style="font-weight:bold">Monday morning</font>.</p>
	</div>
	<style type="text/css">
		body{
			background-image:none !important;
		}
		#maintenance-alert{
			display:block;
			margin:20px auto 20px auto;
			width:480px;
			background:url(/euf/assets/themes/cfpb/images/intake/paper_texture.png) repeat scroll 0 0 transparent;
			padding:30px;
			border:1px solid #3C3C3C;
		}
		#maintenance-alert p{
			margin:0;
			padding:0;
		}
		#maintenance-alert h1{
			font-size:1.4em;
			margin:0 0 10px 0;
			padding:0;
			font-weight:bold;
		}
		.wrapper-banner {
			width: 100%;
			background-color: #f7f5f4;
			padding: 4px 0;
			font-size: .8em;
			text-align:center;
		}
		.official-website {
			text-align:center;
			padding-right: 24px;
			background: transparent url("http://www.consumerfinance.gov/wp-content/themes/cfpb_nemo/_/img/us_flag_small.png") right center no-repeat;
			font-size:smaller;
			line-height:1em;
		}
	</style>
</body>
</html>
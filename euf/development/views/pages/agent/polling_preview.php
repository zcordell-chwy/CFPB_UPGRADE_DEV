<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<title><?=getMessage(POLLING_SURVEY_PREVIEW_LBL);?></title>
<link rel="stylesheet" type="text/css" href="<?=getYUICodePath('container/assets/skins/sam/container.css')?>" />
</head>
<body class="yui-skin-sam">
<br />
<!-- survey_id is a fake number, the controller will grab the real survey_id from $_REQUEST -->
<rn:widget path="surveys/Polling" admin_console="true" survey_id="1234567"/>
</body>
</html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <base href="<?= getShortEufBaseUrl(false, '/'); ?>" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <title><?=getMessage(ANSWER_QUICK_PREVIEW_LBL);?></title>
    <link href="<?=$baseurl;?>/euf/assets/themes/standard/site.css" rel="stylesheet" type="text/css" media="all" />
    <link href="<?=$baseurl;?>/rnt/rnw/yui_2.7/container/assets/skins/sam/container.css" rel="stylesheet" type="text/css" />

</head>
<body class="yui-skin-sam">
<div id="rn_Container">
    <div id="rn_Header"></div>
    <div id="rn_Navigation"></div>
    <div id="rn_Body">
      <div id="rn_MainColumn">
        <div id="rn_PageTitle" class="rn_AnswerDetail">
            <h1 id="rn_Summary"><?=$summary;?></h1>
            <div id="rn_AnswerInfo"></div>
            <?=$description;?>
        </div>
        <div id="rn_PageContent" class="rn_AnswerDetail">
            <div id="rn_AnswerText">
                <p><?=$solution;?></p>
            </div>
        </div>
      </div>
    </div>
</div>
<script type="text/javascript">

var tags = document.getElementsByTagName('A');

for (var i = tags.length - 1; i >= 0; i--)
{
    tags[i].target = "_blank";
}

tags = document.getElementsByTagName('FORM');

for (var i = tags.length - 1; i >= 0; i--)
{
    tags[i].onsubmit = new Function("return(disabled_msg())");
}

function disabled_msg()
{
    alert("<?= getMessageJS(DISABLED_FOR_PREVIEW_MSG) ?>");
    return(false);
}
</script>

</body>
</html>

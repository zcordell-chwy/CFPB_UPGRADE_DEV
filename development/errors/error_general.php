<html>
<head>
<title><?= function_exists('getMessage') ? getMessage(ERROR_LBL) : 'Error' ?></title>
<style type="text/css">

body {
background-color:    #fff;
margin:                40px;
font-family:        Lucida Grande, Verdana, Sans-serif;
font-size:            .75em;
color:                #000;
}

#content  {
border:                #999 1px solid;
background-color:    #fff;
padding:            20px 20px 12px 20px;
}

h1 {
font-weight:        normal;
font-size:            .875em;
color:                #990000;
margin:             0 0 4px 0;
}
</style>
</head>
<body>
    <div id="content">
        <h1><?=$heading;?></h1>
        <?=$message; ?>
    </div>
</body>
</html>

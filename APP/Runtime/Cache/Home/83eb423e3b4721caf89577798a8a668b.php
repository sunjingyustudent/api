<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="/Public/css/index.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <title></title>
</head>
<body >
<!--logo-->
<div class="detailtop">
    <div class="detailbox">
        <div  class="detailboxtitle"> <?php echo ($lists["post_title"]); ?></div>
        <div class="detailboxtime"> <?php echo ($lists["post_source"]); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ($lists["post_modified"]); ?> &nbsp;</div>
    </div>
    <!--简要-->
    <div class="jianjie">简介：<?php echo ($lists["post_excerpt"]); ?></div>
    <div  class="detailcon"> <?php echo ($lists["post_content"]); ?></div>
</div>
</div>
</body>
</html>
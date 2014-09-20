<?php
header("Content-type:text/html; charset=utf-8");
require( 'htmlParse.class.php' );

$htmlParse = new htmlParse();//创建一个html解析对象
$htmlParse->setUrl("http://www.qiushibaike.com");//设置抓取的网页为qiushibaike.com
$htmlParse->parse();//进行解析
echo '<pre>';
print_r($htmlParse->content);//将提取的内容打印出来
?>
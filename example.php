<?php
header("Content-type:text/html; charset=utf-8");
require( 'htmlParse.class.php' );

$htmlParse = new htmlParse();//����һ��html��������
$htmlParse->setUrl("http://www.qiushibaike.com");//����ץȡ����ҳΪqiushibaike.com
$htmlParse->parse();//���н���
echo '<pre>';
print_r($htmlParse->content);//����ȡ�����ݴ�ӡ����
?>
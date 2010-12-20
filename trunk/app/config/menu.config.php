<?php
$menu=array();
$menu['default']['index']['text'] = '近况';
$menu['default']['index']['tab'] = true;
$menu['books']['index']['tab'] = '帐本';

$GLOBALS['config']['menu'] = $menu;

$tab = array();
$tab[] = array( 'm'=>'default' , 'a'=>'' , 't'=>'近况' );
$tab[] = array( 'm'=>'statistic' , 'a'=>'' , 't'=>'统计' );
$tab[] = array( 'm'=>'menu' , 'a'=>'' , 't'=>'菜谱' );
$tab[] = array( 'm'=>'order' , 'a'=>'' , 't'=>'订单' );


$GLOBALS['config']['tab'] = $tab;
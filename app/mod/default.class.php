<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );

class defaultMod extends appMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	public function index()
	{
		$data['title'] = $data['top_title'] = '测试页面';
		$data['books'] = get_data("SELECT * FROM books ");
		$workOrders= get_data("SELECT * FROM orders where status=0 order by timeline desc limit 10");
		foreach($workOrders as $k=>$d){
		    $workOrders[$k]=orderHelper::getOrderTxt($d);
        }
		$data['workOrders']=$workOrders;
		$endOrders= get_data("SELECT * FROM orders where status=1 order by timeline desc limit 10 ");
		foreach($endOrders as $k=>$d){
		    $endOrders[$k]=orderHelper::getOrderTxt($d);
        }
		$data['endOrders']=$endOrders;
		render( $data );
	}
}


?>
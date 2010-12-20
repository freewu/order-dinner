<?php
if( !defined('IN') ) die('bad request');
include_once( CROOT . 'mod/core.class.php' );

class testMod extends coreMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	// login check or something
	public function index(){
        die('done');
    }
	/**
	 *  更新订单
	 */
	public function updateitemstatistic(){
        $odata=get_data("select detail from orders where detail<>''");
        $itemArr=array();
        foreach($odata as $od){
            $ia=json_decode($od['detail'],1);
            foreach($ia as $ik=>$iv){
                $itemArr[$ik]++;
            }
        }
        echo '<pre>';
        var_dump($itemArr);
        foreach($itemArr as $ia => $iv){
            run_sql("update items set used='$iv' Where itemid='$ia'");
        }
        die('Done');
    }
}

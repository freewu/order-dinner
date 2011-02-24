<?php
if( !defined('IN') ) die('bad request');
include_once( CROOT . 'mod/core.class.php' );

class eventfeedMod extends coreMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}

    function index(){
        $startDate=v('startDate');
        $endDate=v('endDate');

        $eOrders= get_data("SELECT * FROM orders where day>'$startDate' And day<'$endDate'  order by timeline desc ");
        $rData=array();
        foreach($eOrders as $k=>$d){
		    $eData=orderHelper::getOrderTxt($d);
            $rData[$k]=array(
                'title'=>'<span class=order_status'.(int)$eData['status'].' ><a href=?m=order&a=modify&orderid='.$eData['orderid'].'>'.$eData['book_txt'].'  '.$eData['payer_txt'].'  '.$eData['final_amount'].'</a></span>',
                'start'=> $eData['day'],
                'end'=>$eData['day'],
                'location'=>''
              );
        }
        $ejobs=get_data("select * from jobs where endtime>'$startDate' And endtime<'$endDate'  order by endtime desc ");
        foreach($ejobs as $d){
             $eData=jobHelper::getJobTxt($d); 
             $rData[]=array(
                'title'=>'<span class=order2_status'.(int)$eData['status'].' ><a href=?m=addme&a=viewjob&jobid='.$eData['jobid'].'>'.$eData['book_txt'].'</a></span>',
                'start'=> $eData['endtime'],
                'end'=>$eData['endtime'],
                'location'=>''
             );
        }
        echo json_encode($rData);
        die();

    }

}

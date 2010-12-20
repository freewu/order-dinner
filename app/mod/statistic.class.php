<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );

class statisticMod extends appMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	public function index()
	{
		$data['title'] = $data['top_title'] = '统计';
        $tbooks=get_data("SELECT * FROM books ");
        foreach($tbooks as $b){
           $data['books'][$b['bookid']]=$b;
        }

        $data['res'] = get_data("SELECT * FROM restaurants ");
        
        if($_POST){
            $data['fromdate']=$fromdate=v('fromdate');
            $data['todate']=$todate=v('todate');
            $check_bookid =v('check_bookid');
            $check_restaurantid =v('check_restaurantid');
            $data['status']=$status=v('status');
            
            $sqlWhere='bookid>0 ';
            if($fromdate==0&&$todate==0){
                $sqlWhere.=" And day <= '".date('Y-m-d')."' And day >='".date('Y-m-0')."'";
            }elseif($fromdate==0){
                $sqlWhere.=" And day <='".$todate."'";
            }elseif($todate==0){
                $sqlWhere.=" And day >='".$fromdate."'";
            }else{
                $sqlWhere.=" And day <= '".$todate."' And day >='".$fromdate."'";
            }
            if($check_bookid){
                $sqlWhere.=" And bookid in (".implode(',',$check_bookid).")";
            }
            if($check_restaurantid){
                $sqlWhere.=" And restaurantid in (".implode(',',$check_restaurantid).")";
            }
            if(strlen($status)){
                $sqlWhere.=" And status = '".$status."'";
            }
            $reDays=array();//日历
            $reBooks=array();
            $reorders=array(); // 订单数组
            
            $td= get_data("SELECT * FROM orders where ".$sqlWhere." order by day");
    		foreach($td as $k=>$d){
                $reDays[$d['day']]=$d['day'];
                $reBooks[$d['bookid']]=$d['bookid'];
                $reorders[$d['bookid']][$d['day']][]=$d;
            }
            $data['reBooks']=$reBooks;
            $data['reDays']=$reDays;
            $data['reorders']=$reorders;
        }

		render( $data );
	}
}


?>
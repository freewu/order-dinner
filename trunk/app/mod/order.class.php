<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );
include_once( AROOT . 'helper/pages.helper.php' );
class orderMod extends appMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	public function index()
	{
		$data['title'] = $data['top_title'] = '订单列表';
		    $tbooks=get_data("SELECT * FROM books ");
        foreach($tbooks as $b){
           $data['books'][$b['bookid']]=$b;
        }

        $data['res'] = get_data("SELECT * FROM restaurants ");
        
        $data['css'][]='omnigrid.css';
        $data['css'][]='omnigrid2.css';
        $data['js'][]='omnigrid.js';
        $data['js'][]='datepicker.js';
        
        render( $data );
	}
  
  public function ajaxlist(){
      // query
      $sqlWhere='1';
      $data['fromdate']=$fromdate=v('fromdate');
        $data['todate']=$todate=v('todate');
        $check_bookid =v('check_bookid');
        $check_restaurantid =v('check_restaurantid');
        $data['status']=$status=v('status');
      
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
            
      // page  
    	$pagination = false;
    	if ( isset($_REQUEST["page"]) )
    	{
    		$pagination = true;
    		$page=intval(v('page'));
        $perpage=intval(v('perpage'));  
    	}
    	
    	// this variables Omnigrid will send only if serverSort option is true
    	$sorton = v("sorton");
    	$sortby = v("sortby");
    	
    	if($sorton&&$sortby){
    	   $sql_sort=' ORDER BY '.$sorton.' '.$sortby;
    	}else{
         $sql_sort=' ORDER BY orderid DESC';
      }
    	
  	  $c_sql="SELECT count(*) FROM orders Where ".$sqlWhere; 
      $total=get_var($c_sql);
      $pObj=new helper_pages($total,$perpage);
      $sql_limit=$pObj->sqladdlimit($page);
    	$sql="SELECT * FROM orders Where ".$sqlWhere.' '.$sql_sort.' '.$sql_limit;     
    	$ret= get_data($sql);
      foreach($ret as $k=>$d){
		      $ret[$k]=orderHelper::getOrderTxt($d);
      }
      $ret = array("page"=>$page, "total"=>$total, "data"=>$ret);
    	echo json_encode($ret);
      die;
  }
  
  public function olist()
	{
        $status=intval(v('status'));
		$data['title'] = $data['top_title'] = ($status?'结算中的':'预算中的').'订单';
		$workOrders= get_data("SELECT * FROM orders where status=$status order by timeline desc");
		foreach($workOrders as $k=>$d){
		    $workOrders[$k]=orderHelper::getOrderTxt($d);
        }
		$data['workOrders']=$workOrders;
        render( $data );
	}
	
	public function modify()
	{
        $orderid=(int)v('orderid');
        if($orderid){
            $data['title'] = $data['top_title'] = '修改订单';
        		$data['item'] =orderHelper::getOrderTxt(get_line("SELECT * FROM orders where status|1 And orderid=$orderid"));
        }else{
            $data['title'] = $data['top_title'] = '新建订单';
            $data['item']=array('status'=>0,'day'=>date('Y-m-d'),'itemnum'=>0,'total_amount'=>0,'final_amount'=>0,
                'book_txt'=>'请选择帐本', 'restaurant_txt'=>'请选择餐厅','detail_txt'=>'请选择菜单','payer_txt'=>'请选择付款人'
            ); 
        }
		render( $data );
	}
	
	public function save()
    {
        
        $orderid=(int)v('orderid');
        $status=t(v('status'));
        $day=date('Y-m-d',strtotime(v('day'))); // 日期
        
        $tdata=array(
           'restaurantid'=>(int)v('restaurantid'),
           'bookid'=>(int)v('bookid'),
           'payerid' =>(int)v('payerid'),
           'status'=> $status,
           'day'=>$day,
           'itemnum'=>v('itemnum'),
           'quantity'=>v('quantity'),
           'total_amount'=>v('total_amount'),
           'final_amount'=>v('final_amount'),
           'payer'=>t(v('payer')),
           'detail'=>t(v('detail')),
           'users_detail'=>t(v('users_detail')),
           'users_count'=>t(v('users_count')),
           'mark'=>t(v('mark')),
        );
        if($orderid){
            $order=get_line('select * from orders where orderid='.$orderid);
            $sql=sql_update('orders',$tdata,array('orderid'=>$orderid));
            menuHelper::menuStatistic($tdata['detail'],$order['detail']);
        }else{
            $tdata['timeline']=date('Y-m-d H:i:s');
            $sql=sql_insert('orders',$tdata);
        }
        if(run_sql($sql)){
            if(!$orderid) $orderid=last_id() ;
            return ajax_box('更新成功' , NULL , 1 ,'?m=order&a=modify&orderid='.$orderid);
        }
        return ajax_box('更新失败' , NULL , 1 );
    }

    public function remove_confirm_order()
	{
        $ids=@explode('_',v('orderid')); 
        if(is_array($ids)){
           foreach($ids as $k=>$orderid){
               if(!$orderid){
                   unset($ids[$k]);
                   continue 1;
               } 
               $ids[$k]=s($orderid); 
           }
        }
        if(count($ids)<=0){
            return ajax_box('没有要删除的记录' , NULL , 1 );
        }
        $rs=get_data( "SELECT orderid FROM orders WHERE orderid in (" .implode(',',$ids) . ")");
        foreach($rs as $row){
          $data['name'] .=$row['orderid'].',';
        }
		 
		$data['orderid'] = implode('_',$ids);
		$data['title'] = $data['top_title'] = '订单';
		return render( $data , 'ajax' );
	}

	public function remove_order()
	{
        $ids=@explode('_',v('orderid')); 
        if(is_array($ids)){
           foreach($ids as $k=>$orderid){
               if(!$orderid){
                   unset($ids[$k]);
                   continue 1;
               } 
               $ids[$k]=s($orderid); 
           }
        }
        if(count($ids)<=0){
            return ajax_box('没有要删除的记录' , NULL , 1 );
        }
		$sql = "DELETE FROM orders WHERE orderid in (" .implode(',',$ids) . ")";
		run_sql( $sql );

		if( sqlite_last_error(db()) == 0 )
		{
			return ajax_box('更新成功' ,null , 1,null,'datagrid.refresh();');
		}
		else
			return ajax_box('更新失败,请稍后再试' , NULL , 1 );
	}
	
	public function batch_confirm_order_status()
	{
        $ids=@explode('_',v('orderid')); 
        if(is_array($ids)){
           foreach($ids as $k=>$orderid){
               if(!$orderid){
                   unset($ids[$k]);
                   continue 1;
               } 
               $ids[$k]=s($orderid); 
           }
        }
        if(count($ids)<=0){
            return ajax_box('没有要操作的记录' , NULL , 1 );
        }
        $rs=get_data( "SELECT orderid,day FROM orders WHERE orderid in (" .implode(',',$ids) . ")");
        foreach($rs as $row){
          $data['name'] .=$row['day'].': '.$row['orderid'].',';
        }
		 
		$data['orderid'] = implode('_',$ids);
		$data['title'] = $data['top_title'] = '订单';
		return render( $data , 'ajax' );
	}

	public function batch_order_status()
	{
        $ids=@explode('_',v('orderid')); 
        if(is_array($ids)){
           foreach($ids as $k=>$orderid){
               if(!$orderid){
                   unset($ids[$k]);
                   continue 1;
               } 
               $ids[$k]=s($orderid); 
           }
        }
        if(count($ids)<=0){
            return ajax_box('没有要操作的记录' , NULL , 1 );
        }
        $status=v('status');
    		$sql = "update orders set status='".$status."' WHERE orderid in (" .implode(',',$ids) . ")";
    		run_sql( $sql );
    
    		if( sqlite_last_error(db()) == 0 )
    		{
    			return ajax_box('更新成功' ,null , 1,null,'datagrid.refresh();');
    		}
    		else
    			return ajax_box('更新失败,请稍后再试' , NULL , 1 );
	}


	public function addnew()
	{
		$data['title'] = $data['top_title'] = '测试页面';

		render( $data );
	}

    public function sbook()
	{
		$data['title'] = $data['top_title'] = '测试页面';
        $data['books'] = get_data("SELECT * FROM books ");
		render( $data ,'ajax');
	}

	public function srestaurant()
	{
		$data['title'] = $data['top_title'] = '测试页面';
        $data['list']=get_data("select * from restaurants ");
		render( $data ,'ajax');
	}
	
	public function smenu()
	{
        $data['restaurantid']=$restaurantid=(int)v('restaurantid');
        if(empty($restaurantid)){
            die('未选择餐厅');
        }
         $data['res']=get_line("select * from restaurants where restaurantid=$restaurantid");
         $cats=get_data("select * from categories where restaurantid=$restaurantid");
         foreach($cats as $cat){
             $data['cats'][$cat['categoryid']]=$cat;
         }
         $items=get_data("select * from items where restaurantid=$restaurantid");
         foreach($items as $item){ // 按分类 分组
            $data['items'][$item['categoryid']][]=$item;
         }
        render( $data ,'ajax');
	}
	
	public function spayer()
	{
		$data['title'] = $data['top_title'] = '测试页面';
        $data['users'] = get_data("SELECT * FROM users ");
		render( $data ,'ajax');
	}

	public function susers()
	{
		$data['title'] = $data['top_title'] = '测试页面';
        $data['users'] = get_data("SELECT * FROM users ");
		render( $data ,'ajax');
	}
	
    function ajaxsetmenu(){
        $data['restaurantid']=$restaurantid=(int)v('restaurantid');
        $check_item=v('check_item');
        $setnum_item=v('setnum_item');
        $items=orderHelper::getItems($restaurantid);

        $newResMenuDetail=array();
        $tmenuText=array();
        $total_amount=0;
        $final_amount=0;
        $itemnum=0;
        $quantity=0;
        foreach($check_item as $k=>$itemid){
            $newResMenuDetail[$itemid]=$setnum_item[$k];
            $tmenuText[$itemid]=$items[$itemid];
            $tmenuNum[$itemid]=$setnum_item[$k];
            $total_amount+=$items[$itemid]['price']*$setnum_item[$k];
            $itemnum+=$setnum_item[$k];
            $quantity++;
        }
        $menuText=orderHelper::getNumTxt($tmenuNum,$items);
        $newDataResMenuDetail=json_encode($newResMenuDetail);
        echo <<<EOF
<script>
var resMenuDetail=JSON.decode('$newDataResMenuDetail');
$('detail').set('html','$newDataResMenuDetail');
$('detail_txt').set('html','$menuText');
$('itemnum_txt').set('html','$itemnum');
$('quantity_txt').set('html','$quantity');
$('total_amount_txt').set('html','$total_amount');
$('itemnum').set('value','$itemnum');
$('quantity').set('value','$quantity');
$('total_amount').set('value','$total_amount');
$('final_amount').set('value','$total_amount');
(function(){close_float_box()}).delay(500);
</script>
EOF;
        die();
     }
    
     function ajaxsetusers(){
        $newResUsersDetail=array();
        $check_item=v('check_item');
        $users_detail=json_encode($check_item);
        $users_txt=orderHelper::getUserTxt($check_item);
        $users_count=count($check_item);
        echo <<<EOF
<script>
var resUsersDetail=JSON.decode('$users_detail');
$('users_count').set('value','$users_count');
$('users_detail').set('html','$users_detail');
$('users_count_txt').set('html','$users_count');
$('users_txt').set('html','$users_txt');
(function(){close_float_box()}).delay(500);
</script>
EOF;
        die();
     }
     
     function ajaxpms(){
        
     }

}


?>
<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );

class addmeMod extends appMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	public function index()
	{
		$data['title'] = $data['top_title'] = '参与+1';
		$list=get_data("SELECT * FROM jobs where 1 order by jobid desc limit 20");
        foreach($list as $k=>$d){
            $lists[$k]=jobHelper::getJobTxt($d);
        }
        $data['list']=$lists;
		render( $data );
	}

  
	// 进入 查看详细 
	public function viewjob(){
	    if(!userHelper::islogin()){
	        echo "<script>location.replace('?m=user&a=login&return=".urlencode($_SERVER['QUERY_STRING'])."')</script>";
	        die;
      }
	    $data['title'] = $data['top_title'] = '查看';
      $data['books'] = get_data("SELECT * FROM books ");
      $data['res'] = get_data("SELECT * FROM restaurants ");
      $jobid=v('jobid');
      if($jobid){
         $data['jobdata']=jobHelper::getJobTxt(get_line("SELECT j.* FROM jobs as j Where j.jobid='".s($jobid)."'"));
         $data['jobitems']=get_data("SELECT * FROM jobitems Where jobid='".s($jobid)."'");
      	 $data+=$this->_jobmenu($jobid);
	    }else{
         return ajax_box('没有记录' , NULL , 0.5 , '?m=addme&a=');
      }

      render( $data ); 
  }

	public function smenu(){
	     if(!userHelper::islogin()){
           return ajax_box('未登录');
       }
       $jobid=v('jobid');
	     $data['jobdata']=$jobdata=get_line("SELECT * FROM jobs Where jobid='".s($jobid)."'");
       $data['jobitems']=$jobitems=get_data("SELECT * FROM jobitems Where jobid='".s($jobid)."'");
       
       
       $data['restaurantid']=$jobdata['restaurantid'];
       $data['res']=get_line("select * from restaurants where restaurantid=".$jobdata['restaurantid']);
       $cats=get_data("select * from categories where restaurantid=".$jobdata['restaurantid']);
       foreach($cats as $cat){
           $data['cats'][$cat['categoryid']]=$cat;
       }
       $items=get_data("select * from items where restaurantid=".$jobdata['restaurantid']);
       foreach($items as $item){ // 按分类 分组
          $data['items'][$item['categoryid']][]=$item;
       }
      render( $data ,'ajax');
   }
	
  public function editjob(){
  	  $data['title'] = $data['top_title'] = '新建“参与 +1”';
      $data['books'] = get_data("SELECT * FROM books ");
      $data['res'] = get_data("SELECT * FROM restaurants ");
      $jobid=v('jobid');
      if($jobid){
      	$data['jobitem']['jobid']=$jobid;
        $data['jobitem']=jobHelper::getJobTxt(get_line("SELECT j.* FROM jobs as j Where j.jobid='".s($jobid)."'"));
      }else{
         $data['jobitem']['endtime']=date('Y-m-d H:i:s',time()+1800); 
      }
      
      render($data);
  }

  public function savejob(){
      $jobid=v('jobid');
      $row['title']=v('title');
      $row['bookid']=v('bookid');
      $row['restaurantid']=(int)v('restaurantid');
      $row['title']=v('title');
      $row['status']=v('status');
//      $row['createtime']=v('createtime');
//      $row['endtime']=v('endtime');
	  // 需加上 用户 已经 +1 的判断
	    $time=strtotime(v('enddate').' '.v('endtime'));
      $row['endtime']=date('Y-m-d H:i:s',$time);       
      $row['mark']=v('mark');
      if(strtotime($row['endtime'])>time() && $row['status']==1){
        return ajax_box('保存失败，点餐还未结束！' , NULL , 1 ,$_SERVER['HTTP_REFERER']);
      }
      
      if($jobid){
          run_sql(sql_update('jobs',$row,array('jobid'=>$jobid)));
          run_sql("update jobs set version=IFNULL(version+1,1) where jobid='".s($jobid)."'");
      }else{
          run_sql(sql_insert('jobs',$row));
      }
      
      return ajax_box('保存成功' , NULL , 1 ,$_SERVER['HTTP_REFERER']);
  }

  /**
   *  实时查询 剩余时间, 信息变化.
   */     
  public function ajaxjobtime(){
      $jobid=v('jobid');
      $data['jobitem']=$jobitem=get_line("SELECT * FROM jobs Where jobid='".s($jobid)."'");
      $timenow=time();
      $endtime=strtotime($jobitem['endtime']);
       if($endtime>$timenow)
        $timeleft = timediff($endtime-$timenow);
       else
         $timeleft=0;
      $r=array(
          'timeleft'=>$timeleft,
          'nowtime'=>date('Y-m-d H:i:s'),
          'endtime'=>$jobitem['endtime'],
          'version'=>$jobitem['version'],
      );
      $r['gquantity']=$jobitem['quantity'];
      $r['gitemnum']=$jobitem['itemnum'];
      $r['gfinal_amount']=$jobitem['final_amount'];
      if($jobitem['version']<=v('version')){
          echo json_encode($r);
      }else{
          //更新详细          
          $r+=$this->_jobmenu($jobid);

          echo json_encode($r); 
      }
      die;
  }
  
  function ajaxjobmenu(){
                    ;
  }
  
    public function sbook(){
        $data['title'] = $data['top_title'] = '测试页面';
        $data['books'] = get_data("SELECT * FROM books ");
        render( $data ,'ajax');
    }
    
    public function srestaurant(){
        $data['title'] = $data['top_title'] = '测试页面';
        $data['list']=get_data("select * from restaurants ");
        render( $data ,'ajax');
    }
    
    public function remove_confirm_job(){
        $jobid=v('jobid');
        if(!$jobid){
            return ajax_box('没有要删除的记录' , NULL , 1 );
        }
        $rs=get_data( "SELECT jobid FROM jobs WHERE jobid = '".$jobid."'");
        $data['name'] = $row['jobid'];
        $data['jobid'] = $jobid;
        $data['title'] = $data['top_title'] = '“参与+1”';
        return render( $data , 'ajax' );
    }
    
    public function remove_job(){
    	$jobid=v('jobid');
        if(!$jobid){
            return ajax_box('没有要删除的记录' , NULL , 1 );
        }
        $sql = "DELETE FROM jobs WHERE jobid = '".$jobid."'";
        run_sql( $sql );

        if( sqlite_last_error(db()) == 0 )
        {
            return ajax_box('更新成功' ,null , 1,null,'datagrid.refresh();');
        }
        else
            return ajax_box('更新失败,请稍后再试' , NULL , 1 );
    }
/***
   *  
   */     
  function _jobmenu($jobid){
      $myuserid=ss('userid');
      $jobitems=get_data("SELECT * FROM jobitems Where jobid='".s($jobid)."'");
      if(!$jobitems) return array();
      foreach($jobitems as $i ){ 
          $guser[$i['userid']][$i['itemid']]=$i['num'];
          $gmenu[$i['itemid']][$i['userid']]=$i['num'];
          
          if($myuserid){ // 用户登录 
              if($i['userid']==$myuserid){
                  $gmemenu[$i['itemid']]=$i['num'];
              }else{
                  $gothermenu[$i['itemid']]=$i['num'];
              }
          }
          $gallmenu[$i['itemid']]=$i['num'];
          $gallitemids[$i['itemid']]=$i['itemid'];
          $galluserids[$i['userid']]=$i['userid'];
      }
      $itemsA=get_data("select * from items where itemid in ('".implode("','",$gallitemids)."')");
      foreach($itemsA as $itemD){
          $gallitemids[$itemD['itemid']]=$itemD;
      }
      $usersA=get_data("select userid,useren,usercn from users where userid in ('".implode("','",$galluserids)."')");
      foreach($usersA as $itemD){
          $galluserids[$itemD['userid']]=$itemD;
      }
      // 按人排序
      foreach($guser as $userid=>$uitem){
          $tm=array();
          foreach($uitem as $menu=>$num){
               $tm[$menu]=array(
                  'title'=>$gallitemids[$menu]['title'],
                  'num'=>$num 
               );
          }
          $r['guser'][$userid]=array(
               'uname'=>$galluserids[$userid]['useren'].'['.$galluserids[$userid]['usercn'].']',
               'menuA'=>$tm
          );
      }
      $r['guser'];
      
      // 按菜品排序
      foreach($gmenu as $menu=>$mitem){
          $tm=array();
          $tnum=0;
          foreach($mitem as $userid=>$num){
               $tm[$userid]=array(
                  'uname'=>$galluserids[$userid]['useren'].'['.$galluserids[$userid]['usercn'].']',
                  'num'=>$num 
               );
               $tnum+=$num;
          }
          $r['gmenu'][$menu]=array(
               'menu'=>$gallitemids[$menu]['title'],
               'uA'=>$tm,
               'tnum'=>$tnum
          );
          $r['resAllMenuDetail'][$menu]=$tnum;
      }
      //var_dump($r['gmenu']);die;
      // 自己的选择
      if($gmemenu){
          $tm=array();
          foreach($gmemenu as $menu=>$num){
              $tm[$menu]=array(
                  'title'=>$gallitemids[$menu]['title'],
                  'num'=>$num 
               );
          }
          $r['gmemenu']=$tm;
          $r['resMenuDetail']=$gmemenu;
      }
      //其它人的选择            
      if($gothermenu) foreach($gothermenu as $userid=>$uitem){
          $tm=array();
          foreach($uitem as $menu=>$num){
               $tm[$menu]=array(
                  'title'=>$gallitemids[$menu]['title'],
                  'num'=>$num 
               );
          }
          $r['gothermenu'][$userid]=array(
               'uname'=>$galluserids[$userid]['useren'].'['.$galluserids[$userid]['usercn'].']',
               'menuA'=>$tm
          );
      }

      return $r;
  }
  
    function ajaxsetmenu(){
        $data['restaurantid']=$restaurantid=(int)v('restaurantid');
        $check_item=v('check_item');
        $setnum_item=v('setnum_item');
        $jobid=v('jobid');
        $items=orderHelper::getItems($restaurantid);
        
        foreach($check_item as $k=>$itemid){
            $newResMenuDetail[$itemid]=$setnum_item[$k];
        }
        
        $userid=ss('userid');
        $jobitems=get_data("SELECT * FROM jobitems Where jobid='".s($jobid)."' And userid='".s($userid)."' ");
        $removedItemid=array();
        if($jobitems) foreach($jobitems as $oitem){ //修改及删除
             if(isset($newResMenuDetail[$oitem['itemid']])){
                 run_sql(sql_update('jobitems',array('num'=>$newResMenuDetail[$oitem['itemid']]),array('jobid'=>$jobid,'userid'=>$userid,'itemid'=>$oitem['itemid'])));
                 unset($newResMenuDetail[$oitem['itemid']]);
             }else{
                 //dele
                 $removedItemid[]=$oitem['itemid'];  
             }
        }
        $removedItemid && run_sql("delete from jobitems where jobid='".s($jobid)."' And userid='".s($userid)."' And itemid in('".implode("','",$removedItemid)."')");
        // 添加 ,新选择的
        foreach($newResMenuDetail as $nitemid=>$nitemNum){
            run_sql(sql_insert('jobitems',array('jobid'=>$jobid,
                 'userid'=>$userid,
                 'itemid'=>$nitemid,
                 'num'=>$nitemNum
            )));
        }

        $quantity=get_line("SELECT count(*) as num FROM (select distinct itemid from jobitems Where jobid='".s($jobid)."')");
        $itemnum=get_line("SELECT sum(num) as num FROM  jobitems Where jobid='".s($jobid)."'");
        $data=get_data("SELECT num,price FROM jobitems as j INNER JOIN items as i on j.itemid = i.itemid Where j.jobid='".s($jobid)."'");
        $final_amount=0;
        foreach($data as $val){
            $final_amount+=$val['num']*$val['price'];
        }
        run_sql("update jobs set version=IFNULL(version+1,1),quantity='".$quantity['num']."',itemnum='".$itemnum['num']."',final_amount=".$final_amount." Where jobid='".s($jobid)."'");

        echo <<<EOF
<script>
jobinfotimeline();
(function(){close_float_box()}).delay(500);
</script>
EOF;
        die();
     }
}
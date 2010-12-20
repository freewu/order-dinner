<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );

class menuMod extends appMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
	public function index()
	{
         $data['list']=get_data("select * from restaurants ");
         render( $data);
	}
	
	public function view_res()
	{
         $data['restaurantid']=$restaurantid=(int)v('restaurantid');
         
         $data['res']=get_line("select * from restaurants where restaurantid=$restaurantid");
         $cats=get_data("select * from categories where restaurantid=$restaurantid");
         foreach($cats as $cat){
             $data['cats'][$cat['categoryid']]=$cat;
         }
         $items=get_data("select * from items where restaurantid=$restaurantid");
         foreach($items as $item){ // 按分类 分组
            $data['items'][$item['categoryid']][]=$item;
         }

         render( $data);
	}
//////////////////////////////////////////////////////////////////
    public function add_res()
    {
        $data=array();
        render($data,'ajax');
    }

    public function modify_res()
    {
        $restaurantid=(int)v('restaurantid');
        $data['item']=get_line("select * from restaurants where restaurantid=$restaurantid");
        render( $data,'ajax');
    }

    public function save_res()
    {
        $restaurantid=(int)v('restaurantid');
        $title=t(v('title'));
        $tel=t(v('tel'));
        $addr=t(v('addr'));
        $boss=t(v('boss'));
        $mark=t(v('mark'));
        if($restaurantid){
            run_sql("update restaurants set title='$title',tel='$tel',addr='$addr',boss='$boss',mark='$mark' where restaurantid=$restaurantid");
        }else{
            run_sql("insert into restaurants(title,tel,addr,boss,mark)values('$title','$tel','$addr','$boss','$mark')");
        }
        return ajax_box('更新成功' , NULL , 1 ,$_SERVER['HTTP_REFERER']);
    }

    public function remove_confirm_res()
	{
        $restaurantid=(int)v('restaurantid');
		if( $restaurantid < 1 ) return ajax_box('错误的 ID');

		$data['name'] = get_var( "SELECT title FROM restaurants WHERE restaurantid = '" . intval( $restaurantid ) . "' LIMIT 1" );
		$data['restaurantid'] = $restaurantid;
		$data['title'] = $data['top_title'] = '餐厅';
		return render( $data , 'ajax' );
	}

	public function remove_res()
	{
        $restaurantid=(int)v('restaurantid');
		if( $restaurantid < 1 ) return ajax_box('错误的 ID');
		$sql = "DELETE FROM restaurants WHERE restaurantid = '" . intval($restaurantid) . "'";
		run_sql( $sql );

		if( sqlite_last_error(db()) == 0 )
		{
			return ajax_box('更新成功' , NULL , 0.1 , $_SERVER['HTTP_REFERER'] );
		}
		else
			return ajax_box('更新失败,请稍后再试' , NULL , 1 );
	}
	
//////////////////////////////////////////////////////////////////
    public function add_cat()
    {
        $data=array();
        $data['restaurantid']=$restaurantid=(int)v('restaurantid');
        $data['res']=get_line("select * from restaurants WHERE restaurantid = '$restaurantid'");
        render($data,'ajax');
    }

    public function modify_cat()
    {
        $data['categoryid']=$categoryid=(int)v('categoryid');
        $data['item']=get_line("select * from categories where categoryid=$categoryid");
        $data['restaurantid']=$restaurantid=$data['item']['restaurantid'];
        $data['res']=get_line("select * from restaurants WHERE restaurantid = '$restaurantid'");
        render( $data,'ajax');
    }

    public function save_cat()
    {
        $categoryid=(int)v('categoryid');
        $restaurantid=(int)v('restaurantid');
        $title=t(v('title'));
        if(strlen($title)>0){
            $data['name'] = get_var( "SELECT title FROM categories WHERE title = '" . $title . "' LIMIT 1" );
        }else{

        }
        if($categoryid){
            run_sql("update categories set title='$title',restaurantid='$restaurantid' where categoryid=$categoryid");
        }else{
            run_sql("insert into categories(title,restaurantid)values('$title','$restaurantid')");
        }
        return ajax_box('更新成功' , NULL , 1 ,$_SERVER['HTTP_REFERER']);
    }

    public function remove_confirm_cat()
	{
        $categoryid=(int)v('categoryid');
		if( $categoryid < 1 ) return ajax_box('错误的 ID');

		$data['name'] = get_var( "SELECT title FROM categories WHERE categoryid = '" . intval( $categoryid ) . "' LIMIT 1" );
		$data['categoryid'] = $categoryid;
		$data['title'] = $data['top_title'] = '菜分类';
		return render( $data , 'ajax' );
	}

	public function remove_cat()
	{
        $categoryid=(int)v('categoryid');
		if( $categoryid < 1 ) return ajax_box('错误的 ID');
		$sql = "DELETE FROM categories WHERE categoryid = '" . intval($categoryid) . "'";
		run_sql( $sql );

		if( sqlite_last_error(db()) == 0 )
		{
			return ajax_box('更新成功' , NULL , 0.5 , $_SERVER['HTTP_REFERER'] );
		}
		else
			return ajax_box('更新失败,请稍后再试' , NULL , 1 );
	}
//////////////////////////////////////////////////////////////////
//菜单
    public function add_item()
    {
        $data=array();
        $data['restaurantid']=$restaurantid=(int)v('restaurantid');
        $data['categoryid']=$categoryid=(int)v('categoryid');
        $data['res']=get_line("select * from restaurants WHERE restaurantid = '$restaurantid'");
        $data['cats']=get_data("select * from categories WHERE restaurantid = '$restaurantid'");
        render($data,'ajax');
    }

    public function modify_item()
    {
        $itemid=(int)v('itemid');
        $data['item']=get_line("select * from items where itemid=$itemid");
        $data['restaurantid']=$restaurantid=$data['item']['restaurantid'];
        $data['res']=get_line("select * from restaurants WHERE restaurantid = '$restaurantid'");
        $data['cats']=get_data("select * from categories WHERE restaurantid = '$restaurantid'");
        render( $data,'ajax');
    }

    public function save_item()
    {

        $itemid=(int)v('itemid');
        $restaurantid=(int)v('restaurantid');
        $categoryid=(int)v('categoryid');
        $title=t(v('title'));
        $price=t(v('price'));
        $disable=t(v('disable'));
        $detail=t(v('detail'));
        if($itemid){
            run_sql("update items set restaurantid='$restaurantid',categoryid='$categoryid',title='$title',price='$price',disable='$disable',detail='$detail' where itemid=$itemid");
        }else{
            run_sql("insert into items(restaurantid,categoryid,title,price,disable,detail)values('$restaurantid','$categoryid','$title','$price','$disable','$detail')");
        }
        return ajax_box('更新成功' , NULL , 1 ,$_SERVER['HTTP_REFERER']);
    }

    public function remove_confirm_item()
	{
        $itemid=(int)v('itemid');
		if( $itemid < 1 ) return ajax_box('错误的 ID');

		$data['name'] = get_var( "SELECT title FROM items WHERE itemid = '" . intval( $itemid ) . "' LIMIT 1" );
		$data['itemid'] = $itemid;
		$data['title'] = $data['top_title'] = '餐厅';
		return render( $data , 'ajax' );
	}

	public function remove_item()
	{
        $itemid=(int)v('itemid');
		if( $itemid < 1 ) return ajax_box('错误的 ID');
		$sql = "DELETE FROM items WHERE itemid = '" . intval($itemid) . "'";
		run_sql( $sql );

		if( sqlite_last_error(db()) == 0 )
		{
			return ajax_box('更新成功' , NULL , 0.1 , $_SERVER['HTTP_REFERER'] );
		}
		else
			return ajax_box('更新失败,请稍后再试' , NULL , 1 );
	}
}


?>
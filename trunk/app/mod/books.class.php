<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );

class booksMod extends appMod
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
        $data['workOrders'] = get_data("SELECT * FROM orders where status|1 ");
        render( $data );
    }

    public function add()
    {
        $data=array();
        render($data,'ajax');
    }
    
    public function modify()
    {
        $bookid=(int)v('bookid');
        $data['item']=get_line("select * from books where bookid=$bookid");
        render( $data,'ajax');
    }
    
    public function save()
    {
        $bookid=(int)v('bookid');
        $title=t(v('title'));
        $detail=t(v('detail'));
        if($bookid){
            run_sql("update books set title='$title',detail='$detail' where bookid=$bookid");
        }else{
            run_sql("insert into books(title,detail,timeline)values('$title','$detail',datetime('now','localtime'))");
        }
        return ajax_box('更新成功' , NULL , 3 ,$_SERVER['HTTP_REFERER']);
    }
    
    public function remove_confirm()
	{
        $bookid=(int)v('bookid');
		if( $bookid < 1 ) return ajax_box('错误的 ID');

		$data['name'] = get_var( "SELECT title FROM books WHERE bookid = '" . intval( $bookid ) . "' LIMIT 1" );
		$data['bookid'] = $bookid;
		$data['title'] = $data['top_title'] = 'TODO';
		return render( $data , 'ajax' );
	}

	public function remove()
	{
        $bookid=(int)v('bookid');
		if( $bookid < 1 ) return ajax_box('错误的 ID');

		//$todo = get_line( "SELECT * FROM todo WHERE uid = '" . uid() . "' AND id = '" . intval( $tid ) . "' LIMIT 1" );

		$sql = "DELETE FROM books WHERE bookid = '" . intval($bookid) . "'";
		run_sql( $sql );

		if( sqlite_last_error(db()) == 0 )
		{
			/*
			if(  time() - strtotime($todo['timeline']) <= 60 )
				run_sql( "DELETE FROM newsfeed WHERE res_id LIKE " )
			*/
			return ajax_box('更新成功' , NULL , 0.1 , $_SERVER['HTTP_REFERER'] );
		}
		else
			return ajax_box('更新失败,请稍后再试' , NULL , 3 );
	}
	
    function view(){
        die('F');
    }
}


?>
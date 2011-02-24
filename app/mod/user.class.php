<?php
if( !defined('IN') ) die('bad request');
include_once( AROOT . 'mod/app.class.php' );

class userMod extends appMod
{
	function __construct()
	{
		// 载入默认的
		parent::__construct();
	}
	
    public function index()
    {
        $data['title'] = $data['top_title'] = '用户';
        $data['users'] = get_data("SELECT * FROM users ");
        render( $data );
    }

    public function add()
    {
        $data=array();
        render($data,'ajax');
    }

    public function modify()
    {
        $userid=(int)v('userid');
        $data['item']=get_line("select * from users where userid='$userid'");
        render( $data,'ajax');
    }

    public function save()
    {
        $userid=(int)(v('userid'));
        $useren=t(v('useren'));
        $usercn=t(v('usercn'));
        if(empty($useren)||empty($usercn)){
            return ajax_box('更新失败' , NULL , 3 ,$_SERVER['HTTP_REFERER']);
        }
        if($userid){
            run_sql("update users set useren='$useren',usercn='$usercn' where userid='$userid'");
        }else{
            run_sql("insert into users(useren,usercn)values('$useren','$usercn')");
        }
        return ajax_box('更新成功' , NULL , 3 ,$_SERVER['HTTP_REFERER']);
    }

    public function remove_confirm()
	{
        $userid=(int)(v('userid'));
		if(!$userid) return ajax_box('错误的 用户Id');

		$data['usercn'] = get_var( "SELECT usercn FROM users WHERE userid = '" .$userid . "' LIMIT 1" );
        $data['userid'] = $userid;
        $data['title'] = $data['top_title'] = '用户';
		return render( $data , 'ajax' );
	}

	public function remove()
	{
        $userid=(int)(v('userid'));
        if(!$userid) return ajax_box('错误的 用户Id');

		$sql = "DELETE FROM users WHERE userid = '" .$userid . "'";
		run_sql( $sql );

		if( sqlite_last_error(db()) == 0 )
		{
			return ajax_box('更新成功' , NULL , 0.1 , $_SERVER['HTTP_REFERER'] );
		}
		else
			return ajax_box('更新失败,请稍后再试' , NULL , 3 );
	}

    function view(){
        die('F');
    }
    
    // 这里的 login
	public function login()
	{
		//echo 'It works';
		$data['title'] = $data['top_title'] = '登录';
		render( $data );
	}

	public function logout()
	{
		userHelper::logout();
		info_page('<a href="?m=user&a=login">成功退出,点击这里以其他帐号登录</a>');
	}
	
	public function login_check()
	{
		if( !v('user') ) return ajax_box('用户名不能为空');
		$user = z(v('user'));
		if( $user = get_line("SELECT * FROM users WHERE useren = '" . s($user) . "' LIMIT 1") )
		{
		  if($user['password']){
		      if( !v('password') ) return ajax_box('密码不能为空');
          if($user['password']!=md5(z(v('password')))) return ajax_box('密码不正确'); 
      }
			$_SESSION['userid'] = $user['userid'];
			$_SESSION['useren'] = $user['useren'];
			$_SESSION['usercn'] = $user['usercn'];
			$_SESSION['level'] = $user['level'];

			return ajax_box( '欢迎回来,' . $user['usercn'] . '.正在转向' , NULL , 0.5 , '?m=default' );
		}
		else
			return ajax_box('用户名或者密码错误,请重试');
	}
	
	public function reg_check()
	{
		if( !v('useren') || !v('usercn') ) return ajax_box('用户名不能为空');
		$useren = z(v('useren'));
		$usercn = z(v('usercn'));
		if( $user = get_line("SELECT * FROM users WHERE useren = '" . s($useren) . "'  LIMIT 1") ){
		    return ajax_box('用户名已经被注册');
		}else{
		    $user=array(
            'useren'=>$useren,
            'usercn'=>$usercn,
            'level'=>1,
        );
		    run_sql(sql_insert('users',$user));
		    
   			$_SESSION['userid'] = last_id();
  			$_SESSION['useren'] = $user['useren'];
  			$_SESSION['usercn'] = $user['usercn'];
  			$_SESSION['level'] = $user['level'];
  
  			return ajax_box( '欢迎您 ,' . $user['usercn'] . ' 注册成功.正在转向' , NULL , 0.5 , '?m=default' );

    }

	}
}


?>
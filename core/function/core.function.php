<?php
// the main 

// lazy functiones




function v( $str )
{
	return isset( $_REQUEST[$str] ) ? $_REQUEST[$str] : false;
}

function z( $str )
{
	return strip_tags( $str );
}

function c( $str )
{
	return isset( $GLOBALS['config'][$str] ) ? $GLOBALS['config'][$str] : false;
}

function g( $str )
{
	return isset( $GLOBALS[$str] ) ? $GLOBALS[$str] : false;	
}

function e($message = null,$code = null) 
{
	throw new Exception($message,$code);
}

function t( $str )
{
	return trim($str);
}

// session management
function ss( $key )
{
	return isset( $_SESSION[$key] ) ?  $_SESSION[$key] : false;
}

function ss_set( $key , $value )
{
	return $_SESSION[$key] = $value;
}

function is_debug()
{   if(!isset($GLOBALS['debug_mark'])) return false;
	return $GLOBALS['debug_mark'];
}

function debug( $mark = true )
{
	$GLOBALS['debug_mark'] = $mark ;
}

// render functiones
function render( $data = NULL , $layout = 'default' )
{
	$GLOBALS['layout'] = $layout;
	$layout_file = AROOT . 'view/layout/' . $layout . '/index.tpl.html';
	if( file_exists( $layout_file ) )
	{
		@extract( $data );
		require( $layout_file );
	}
	else
	{
		$layout_file = CROOT . 'view/layout/' . $layout . '/index.tpl.html';
		if( file_exists( $layout_file ) )
		{
			@extract( $data );
			require( $layout_file );
		}	
	}
}

function info_page( $info , $layout = 'default' )
{
	$GLOBALS['m'] = 'default';
	$GLOBALS['a'] = 'info';
	$data['title'] = $data['top_title'] = '系统消息';
	$data['info'] = $info;
	render( $data , $layout );
}




// db functions
include_once( CROOT .  'function/dbsqlite.function.php' );
//include_once( CROOT .  'function/db.function.php' );






function ajax_echo( $info )
{
	if( is_debug() )
	{
		return $info;
	}
	else
	{
		header("Content-Type:text/xml;charset=utf-8");
		header("Expires: Thu, 01 Jan 1970 00:00:01 GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		echo $info;
	}
}

function ajax_box( $content , $title = '系统消息' , $close_time = 0 , $forward = '',$js='' )
{
	if( is_debug() )
	{
		return $content;
	}
	else
	{
        require_once( AROOT . 'view/layout/ajax/box.tpl.html' );
	}
}


function fliter( $array , $pre )
{
	$ret = array();
	foreach( $array as $key=>$value )
	{
		if( strpos( $key , $pre ) === 0 )
			$ret[$key] = $value;
	}
	return $ret;
}

function uses( $m )
{
	load( 'function/' . basename($m) . '.function.php' );
}

function load( $file_path ) 
{
	$file = AROOT . $file_path;
	if( file_exists( $file ) )
		include_once( $file );
	else
		include_once( CROOT . $file_path );
}

function menu()
{
	if( !isset( $GLOBALS['config']['menu'] ) )
		include_once( AROOT .  'config/menu.config.php' );

	return $GLOBALS['config']['menu'];
}

function tab()
{
	if( !isset( $GLOBALS['config']['tab'] ) )
		include_once( AROOT .'config/menu.config.php' );
	return $GLOBALS['config']['tab'];
}

function get_innerhtml( $type , $data )
{
	$tpl_file = CROOT . 'view/component/' . basename( $type ) . '.tpl.html';
	if( file_exists( $tpl_file ) )
	{
		ob_start();
		@extract( $data );
		require( $tpl_file );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	else
		return false;
}

?>
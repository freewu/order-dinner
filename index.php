<?php
// the front page of lazyphp

error_reporting(E_ERROR);
ini_set( 'display_errors' , true );
ini_set( 'magic_quotes_gpc' , false );
date_default_timezone_set('Asia/Shanghai');

session_start();
session_name('b2b_dinner'.md5(__FILE__));
// 常量
define( 'IN' , true );
define( 'DS' , '/' );
define( 'ROOT' , dirname( __FILE__ ) . DS );
define( 'CROOT' , ROOT . 'core' . DS  );
define( 'AROOT' , ROOT . 'app' . DS  );


// global functiones
include_once( CROOT . 'function' . DS . 'init.function.php' );
include_once( CROOT . 'function' . DS . 'core.function.php' );

@include_once( AROOT . 'function' . DS . 'app.function.php' );

include_once( CROOT . 'config' .  DS . 'core.config.php' );
include_once( AROOT . 'config' . DS . 'app.config.php' );
include_once( AROOT . 'config' . DS . 'db.config.php' );


$m = $GLOBALS['m'] = v('m') ? v('m') : c('default_mod');
$a = $GLOBALS['a'] = v('a') ? v('a') : c('default_action');
$m = basename(strtolower( $m ));

$post_fix = '.class.php';

$mod_file = AROOT . 'mod'  . DS . $m . $post_fix;

if( !file_exists( $mod_file ) ) die('Can\'t find controller file - ' . $m . $post_fix);
require( $mod_file );

if( !class_exists( $m.'Mod' ) ) die('Can\'t find class - '   . $m . 'Mod');

$class_name =$m.'Mod'; 

$o = new $class_name;
if( !method_exists( $o , $a ) ) die('Can\'t find method - '   . $a . ' ');


//if(ereg('gzip',$_SERVER['HTTP_ACCEPT_ENCODING']))  ob_start("ob_gzhandler");

call_user_method( $a , $o );




?>
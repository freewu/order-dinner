<?php
error_reporting(E_ERROR);
ini_set( 'display_errors' , true );
ini_set( 'magic_quotes_gpc' , false );
date_default_timezone_set('Asia/Shanghai');
// 常量
define( 'IN' , true );
define( 'DS' , '/' );
define( 'ROOT' , dirname( __FILE__ ) . DS );
define( 'CROOT' , ROOT . 'core' . DS  );
define( 'AROOT' , ROOT . 'app' . DS  );


// global functiones
include_once( CROOT . 'function' . DS . 'init.function.php' );
include_once( CROOT . 'function' . DS . 'core.function.php' );
include_once( CROOT . 'config' .  DS . 'core.config.php' );
include_once( AROOT . 'function' . DS . 'app.function.php' );
try{
// 主要
if(!file_exists(AROOT . 'config' . DS . 'db.config.php')){
    $i=copy(AROOT . 'config' . DS . 'db.config.sample.php',AROOT . 'config' . DS . 'db.config.php');
    if(!$i) throw new Exception('Can\'t make config file ! ');
}
include_once( AROOT . 'config' .  DS . 'db.config.php' );
if(file_exists(ROOT.$config['db']['file_name'])&&filesize(ROOT.$config['db']['file_name'])){
    throw new Exception('db file existed! ');
}
cmkdir(dirname(ROOT.$config['db']['file_name']));
$db=sqlite_open(ROOT.$config['db']['file_name'],0666,$sqliteerror);
$sql=<<<EOF
BEGIN TRANSACTION;
DROP TABLE 'categories';
create table categories(
                categoryid integer primary key,
                restaurantid integer,
                title varchar(200)
                );


DROP TABLE 'books';
CREATE TABLE 'books' (
	'bookid' integer PRIMARY KEY, 
	'title' varchar(200), 
	'detail' text, 
	'timeline' datetime
);

DROP TABLE 'restaurants';
CREATE TABLE 'restaurants' (restaurantid integer primary key, title varchar(200), tel varchar(200), addr varchar(200), boss varchar(200), mark text);

DROP TABLE 'users';
CREATE TABLE 'users' (userid INTEGER PRIMARY KEY, useren varchar(200), usercn varchar(200), password char(32), level integer int(4));
DROP TABLE 'orders';
CREATE TABLE 'orders' (orderid integer primary key, bookid integer, restaurantid integer, payerid integer, payer varchar(100), status integer(4), day date, timeline datetime, itemnum integer, quantity integer, total_amount decimal(10,3), final_amount double, detail text, users_detail text, users_count INTEGER, mark text);
DROP TABLE 'items';
CREATE TABLE 'items' (itemid integer primary key, restaurantid varchar(200), title varchar(200), categoryid integer, price double, disable integer, detail text, used int(10), gooded int(10), baded int(10));

Insert into users (useren,usercn,password,level)values('admin','admin','21232f297a57a5a743894a0e4a801fc3',255);
COMMIT;
EOF;
$sql_r=explode(';',$sql);
foreach($sql_r as $sql){
    sqlite_query($db,$sql);
    if($sqliteerror)
       throw new Exception('Can\'t make config file ! ');
}
sqlite_close($db);

echo "<h1>Install success!</h1> <br> <font color=#FF0000>Please Delete this file! </font> <br> <a href='index.php'>Go Home !</a> <br>admin:admin";
}catch(Exception $e){
  echo  "Install End with : ".$e->getMessage(). "\r\n<br> ";
}
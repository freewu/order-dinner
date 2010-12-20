<?php

// db functions

function s( $str , $db = NULL )
{
	if( $db == NULL ) $db = db();
	return  "'" . mysql_real_escape_string( $str , $db ) ."'" ;
	
}


function db()
{
	if( !isset( $GLOBALS['LZ_DB'] ) )
	{
		include_once( AROOT .  'config/db.config.php' );
		$db_config = $GLOBALS['config']['db'];
		
		//print_r( $db_config );
		// 
		if( !$GLOBALS['LZ_DB'] = mysql_connect( $db_config['db_host'].':'.$db_config['db_port'] , $db_config['db_user'] , $db_config['db_password'] , true ) )
		{
			//
			echo 'can\'t connect to database';
			return false;
		}
		else
		{
			if( isset( $db_config['db_name'] ) )
			{
				if( !mysql_select_db( $db_config['db_name'] , $GLOBALS['LZ_DB'] ) )
				{
					echo 'can\'t select database ' . $db_config['db_name'] ;
					return false;
				}
			}
		}
		
		// 
		mysql_query( "SET NAMES 'UTF8'" , $GLOBALS['LZ_DB'] );
	}
	
	return $GLOBALS['LZ_DB'];
}

function get_data( $sql , $db = NULL )
{
	if( $db == NULL ) $db = db();
	
	$GLOBALS['LZ_LAST_SQL'] = $sql;
	$data = Array();
	$i = 0;
	$result = mysql_query( $sql ,$db );
	
	if( mysql_errno() != 0 )
		echo mysql_error() .' ' . $sql;
	
	while( $Array = mysql_fetch_array($result, MYSQL_ASSOC ) )
	{
		$data[$i++] = $Array;
	}
	
	if( mysql_errno() != 0 )
		echo mysql_error() .' ' . $sql;
	
	mysql_free_result($result); 

	if( count( $data ) > 0 )
		return $data;
	else
		return false;
}

function get_line( $sql , $db = NULL )
{
	$data = get_data( $sql , $db  );
	return @reset($data);
}

function get_var( $sql , $db = NULL )
{
	$data = get_line( $sql , $db );
	return $data[ @reset(@array_keys( $data )) ];
}

function last_id( $db = NULL )
{
	if( $db == NULL ) $db = db();
	return get_var( "SELECT LAST_INSERT_ID() " , $db );
}

function run_sql( $sql , $db = NULL )
{
	if( $db == NULL ) $db = db();
	$GLOBALS['LZ_LAST_SQL'] = $sql;
	return mysql_query( $sql , $db );
}

function db_errno()
{
	return mysql_errno( db() );
}


function db_error()
{
	if( isset( $GLOBALS['LZ_DB_LAST_ERROR'] ) )
	return $GLOBALS['LZ_DB_LAST_ERROR'];
}

function close_db( $db = NULL )
{
	if( $db == NULL )
		$db = $GLOBALS['LZ_DB'];
		
	mysql_close( $db );
}

?>
<?php
function s( $str )
{
	return sqlite_escape_string( $str  );
}

function db()
{
	if( !isset($GLOBALS['__SQLITE_INSTANCE']) )
	{
		$db_config = c('db');
		if( !$GLOBALS['__SQLITE_INSTANCE'] = sqlite_open( $db_config['file_name'] ) )
		{
			echo 'bad connect';
		}

	}
 	sqlite_create_function( $GLOBALS['__SQLITE_INSTANCE'] , 'UNIX_TIMESTAMP' ,  'UNIX_TIMESTAMP' );
	return $GLOBALS['__SQLITE_INSTANCE'];
}


function get_data( $sql )
{

	$GLOBALS['LZ_LAST_SQL'] = $sql;
	$data = Array();
	$i = 0;


	$result = sqlite_query( db() , $sql );

	if( sqlite_last_error(db()) != 0 )
		echo sqlite_error_string(sqlite_last_error(db())) .' ' . $sql;

	while( $Array = sqlite_fetch_array( $result, SQLITE_ASSOC  ) )
	{
		$data[$i++] = $Array;
	}

	if( sqlite_last_error(db()) != 0 )
		echo sqlite_error_string(sqlite_last_error(db())) .' ' . $sql;

	if( count( $data ) > 0 )
		return $data;
	else
		return false;
}

function get_line( $sql )
{
	$data = get_data( $sql );
	return @reset($data);
}

function get_var( $sql )
{
	$data = get_line( $sql );
	return $data[ @reset(@array_keys( $data )) ];
}

function last_id()
{
	return sqlite_last_insert_rowid( db());
}

function run_sql( $sql )
{
	$GLOBALS['LZ_LAST_SQL'] = $sql;

	if( !$ret = sqlite_exec( $sql , db() ) )
		echo sqlite_error_string(sqlite_last_error(db())) .' ' . $sql;

	return $ret;
}

function close_db()
{
	sqlite_close( $GLOBALS['LZ_DB'] );
}

function UNIX_TIMESTAMP( $str ) { return strtotime( $str ); }
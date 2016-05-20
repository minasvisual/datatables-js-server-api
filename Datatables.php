<?php
/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
// REMOVE THIS BLOCK - used for DataTables test environment only!
// $file = $_SERVER['DOCUMENT_ROOT'].'/datatables/mysql.php';
// if ( is_file( $file ) ) {
//     include( $file );
// }
class Datatables 
{

	static private $config  = array();
	static public  $columns = array();
    static public  $tables    = array();
	static public  $where    = array();
	static public  $get    = array();
    /**
     * boot connections info
     *
     * @param array database config information array
     *
     * @return void
     */
    public function __construct($config=null)
    {
    	$ci = &get_instance();
		$ci->load->database();
		Self::$config = array(
		    'user' => $ci->db->username,
		    'pass' => $ci->db->password,
		    'db'   => $ci->db->database,
		    'host' => $ci->db->hostname
		); 
		Self::$get = $_GET;
    } 

	/**
     * Add columns to the return
     *
     * @param array database config information array
     *
     * @return void
     */
    public function addCols($field, $alias=null, $params=array(), $dt=null )
    {
    	$column = array_merge( 
    		array( 
	    		'db' => ( (!is_null($alias))?'`'.$alias.'`.':'').'`'.$field.'`', 
	    		'field' => ( !isset( $params['field'] ) ? $field : $params['field'] ), 
	    		'dt'=>( (!is_null($dt)) ? $dt : count(Self::$columns) ) 
    		),
    		$params
    	);
    	Self::$columns[] =  $column;
    }

	/**
     * Add columns to the return
     *
     * @param array database config information array
     *
     * @return void
     */
    public function getCols($field=null, $table=null )
    {
	    	return  Self::$columns;
    }

	/**
     * Add columns to the return
     *
     * @param array database config information array
     *
     * @return void
     */
    public function addTables($table, $key, $alias=null, $join_table=null, $fk=null  )
    {
	    	Self::$tables[$table] = array('table'=>$table, 'key'=>$key, 'alias'=>$alias, 'join'=>$join_table, 'fk'=>$fk);
    }

    /**
     * Add columns to the return
     *
     * @param array database config information array
     *
     * @return void
     */
    public function getTables($table=null )
    {
    		$tables = Self::$tables;

            if(   !is_null($table) && isset($tables[$table])   )  return ' `'.$tables[$table].'` '.( (!is_null($tables[$table])) ? 'AS `'.$tables[$table].'`':' ' ); 

    		$join = 'FROM '; $count = 0;
    		foreach($tables as $tb)
    		{
    			if( $count == 0 )
    			{
    				$join .= ' `'.$tb['table'].'` '.( (!is_null($tb['alias'])) ? 'AS `'.$tb['alias'].'`':' ' ); 
    			}
    			if( $count > 0 && !is_null($tb['join']) && isset($tables[$tb['join']]) )
    			{
    				$tb2 = $tables[$tb['join']];
    				$join .= 'JOIN `'.$tb['table'].'` '.( (!is_null($tb['alias'])) ? 'AS `'.$tb['alias'].'`':'' ).' ON (`'.$tb2['alias'].'`.'.$tb2['key'].' = `'.$tb['alias'].'`.'.$tb['fk'].') ';
    			}

    			$count++;
    		}

	    	return  $join;
    }

    public function addWhere($field, $value, $alias=null, $op='=' )
    {
        Self::$where[$field] = array('field'=>$field, 'value'=>$value, 'alias'=>$alias, 'op'=>$op);
    }

    public function getWhere($field=null)
    {
        $fields = Self::$where;

        if(   !is_null($field) && isset($fields[$field])   )  
            return ( (!is_null($fields[$field]['alias'])) ? '`'.$fields[$field]['alias'].'`.':'' ).'`'.$fields[$field]['field'].'` '.$$fields[$field]['op'].' '.$fields[$field]['value'].' ';
        
        $rtn = ' ';
        foreach ($variable as $row) 
        {
            $rtn .= ( (!is_null($$row['alias'])) ? '`'.$$row['alias'].'`.':'' ).'`'.$row['alias'].'` '.$$row['op'].' '.$$row['value'].' ';
        }

        return $rtn;
    }

    /**
     * Create the data output array for the DataTables rows
     *
     * @param array $columns Column information array
     * @param array $data    Data from the SQL get
     * @param bool  $isJoin  Determine the the JOIN/complex query or simple one
     *
     * @return array Formatted data in a row based format
     */
    static function data_output ( $columns, $data, $isJoin = false )
    {
        $out = array();
        for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
            $row = array();
            for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                $column = $columns[$j];
                // Is there a formatter?
                if ( isset( $column['formatter'] ) ) {
                    $row[ $column['dt'] ] = ($isJoin) ? $column['formatter']( $data[$i][ $column['field'] ], $data[$i] ) : $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                }
                else 
                {
                    $row[ @$column['dt'] ] = ($isJoin) ? $data[$i][ @$columns[$j]['field'] ] : $data[$i][ @$columns[$j]['db'] ];
                }
            }
            $out[] = $row;
        }
        return $out;
    }
    /**
     * Paging
     *
     * Construct the LIMIT clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL limit clause
     */
    static function limit ( $request, $columns )
    {
        $limit = '';
        if ( isset($request['start']) && $request['length'] != -1 ) {
            $limit = "LIMIT ".intval($request['start']).", ".intval($request['length']);
        }
        return $limit;
    }
    /**
     * Ordering
     *
     * Construct the ORDER BY clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param bool  $isJoin  Determine the the JOIN/complex query or simple one
     *
     *  @return string SQL order by clause
     */
    static function order ( $request, $columns, $isJoin = false )
    {
        $order = '';
        if ( isset($request['order']) && count($request['order']) ) {
            $orderBy = array();
            $dtColumns = Datatables::pluck( $columns, 'dt' );
            for ( $i=0, $ien=count($request['order']) ; $i<$ien ; $i++ ) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];
                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                        'ASC' :
                        'DESC';
                    $orderBy[] = ($isJoin) ? $column['db'].' '.$dir : '`'.$column['db'].'` '.$dir;
                }
            }
            $order = 'ORDER BY '.implode(', ', $orderBy);
        }
        return $order;
    }
    /**
     * Searching / Filtering
     *
     * Construct the WHERE clause for server-side processing SQL query.
     *
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here performance on large
     * databases would be very poor
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param  array $bindings Array of values for PDO bindings, used in the sql_exec() function
     *  @param  bool  $isJoin  Determine the the JOIN/complex query or simple one
     *
     *  @return string SQL where clause
     */
    static function filter ( $request, $columns, &$bindings, $isJoin = false )
    {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = Datatables::pluck( $columns, 'dt' );
        if ( isset($request['search']) && $request['search']['value'] != '' ) {
            $str = $request['search']['value'];
            for ( $i=0, $ien=count(@$request['columns']) ; $i<$ien ; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search( $requestColumn['data'], $dtColumns );
                $column = $columns[ $columnIdx ];
                if ( $requestColumn['searchable'] == 'true' ) {
                    $binding = Datatables::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                    $globalSearch[] = ($isJoin) ? $column['db']." LIKE ".$binding : "`".$column['db']."` LIKE ".$binding;
                }
            }
        }
        // Individual column filtering
        for ( $i=0, $ien=count(@$request['columns']) ; $i<$ien ; $i++ ) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search( $requestColumn['data'], $dtColumns );
            $column = $columns[ $columnIdx ];
            $str = $requestColumn['search']['value'];
            if ( $requestColumn['searchable'] == 'true' &&
                $str != '' ) {
                $binding = Datatables::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
                $columnSearch[] = ($isJoin) ? $column['db']." LIKE ".$binding : "`".$column['db']."` LIKE ".$binding;
            }
        }
        // Combine the filters into a single string
        $where = '';
        if ( count( $globalSearch ) ) {
            $where = '('.implode(' OR ', $globalSearch).')';
        }
        if ( count( $columnSearch ) ) {
            $where = $where === '' ?
                implode(' AND ', $columnSearch) :
                $where .' AND '. implode(' AND ', $columnSearch);
        }
        if ( $where !== '' ) {
            $where = 'WHERE '.$where;
        }
        return $where;
    }
    /**
     * Perform the SQL queries needed for an server-side processing requested,
     * utilising the helper functions of this class, limit(), order() and
     * filter() among others. The returned array is ready to be encoded as JSON
     * in response to an SSP request, or can be modified if needed before
     * sending back to the client.
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $sql_details SQL connection details - see sql_connect()
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @param  array $joinQuery Join query String
     *  @param  string $extraWhere Where query String
     *
     *  @return array  Server-side processing response array
     *
     */
    static function simple ( $columns, $joinQuery = NULL, $extraWhere = '', $groupBy = '')
    {
        $bindings = array();
       	$sql_details = Datatables::$config;
       	$request = Self::$get;

       	$tb = reset(Self::$tables);
       	$table = $tb['table'];
		$primaryKey = $tb['key'];

        $db = Datatables::sql_connect( $sql_details );

        // Build the SQL query string from the request
        $limit = Datatables::limit( $request, $columns );
        $order = Datatables::order( $request, $columns, $joinQuery );
        $where = Datatables::filter( $request, $columns, $bindings, $joinQuery );
		// IF Extra where set then set and prepare query
        if($extraWhere)
            $extraWhere = ($where) ? ' AND '.$extraWhere : ' WHERE '.$extraWhere;
        
        $groupBy = ($groupBy) ? ' GROUP BY '.$groupBy .' ' : '';
        
        // Main query to actually get the data
        if($joinQuery){
            $col = Datatables::pluck($columns, 'db', $joinQuery);
            $query =  "SELECT SQL_CALC_FOUND_ROWS ".implode(", ", $col)."
			 $joinQuery
			 $where
			 $extraWhere
			 $groupBy
			 $order
			 $limit";
        }else{
            $query =  "SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", Datatables::pluck($columns, 'db'))."`
			 FROM `$table`
			 $where
			 $extraWhere
			 $groupBy
			 $order
			 $limit";
        }
        
        $data = Datatables::sql_exec( $db, $bindings,$query);
        // Data set length after filtering
        $resFilterLength = Datatables::sql_exec( $db,
            "SELECT FOUND_ROWS()"
        );
        $recordsFiltered = $resFilterLength[0][0];
        // Total data set length
        $resTotalLength = Datatables::sql_exec( $db,
            "SELECT COUNT(`{$primaryKey}`)
			 FROM   `$table`"
        );
        $recordsTotal = $resTotalLength[0][0];
        /*
         * Output
         */
        return array(
            "draw"            => intval( @$request['draw'] ),
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => Datatables::data_output( $columns, $data, $joinQuery )
        );
    }
    /**
     * Connect to the database
     *
     * @param  array $sql_details SQL server connection details array, with the
     *   properties:
     *     * host - host name
     *     * db   - database name
     *     * user - user name
     *     * pass - user password
     * @return resource Database connection handle
     */
    static function sql_connect ( $sql_details )
    {
        try {
            $db = @new PDO(
                "mysql:host={$sql_details['host']};dbname={$sql_details['db']}",
                $sql_details['user'],
                $sql_details['pass'],
                array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION )
            );
            $db->query("SET NAMES 'utf8'");
        }
        catch (PDOException $e) {
            Datatables::fatal(
                "An error occurred while connecting to the database. ".
                "The error reported by the server was: ".$e->getMessage()
            );
        }
        return $db;
    }
    /**
     * Execute an SQL query on the database
     *
     * @param  resource $db  Database handler
     * @param  array    $bindings Array of PDO binding values from bind() to be
     *   used for safely escaping strings. Note that this can be given as the
     *   SQL query string if no bindings are required.
     * @param  string   $sql SQL query to execute.
     * @return array         Result from the query (all rows)
     */
    static function sql_exec ( $db, $bindings, $sql=null )
    {
        // Argument shifting
        if ( $sql === null ) {
            $sql = $bindings;
        }
        $stmt = $db->prepare( $sql );
        //echo $sql;
        // Bind parameters
        if ( is_array( $bindings ) ) {
            for ( $i=0, $ien=count($bindings) ; $i<$ien ; $i++ ) {
                $binding = $bindings[$i];
                $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
            }
        }
        // Execute
        try {
            $stmt->execute();
        }
        catch (PDOException $e) {
            Datatables::fatal( "An SQL error occurred: ".$e->getMessage() );
        }
        // Return all
        return $stmt->fetchAll();
    }
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Internal methods
     */
    /**
     * Throw a fatal error.
     *
     * This writes out an error message in a JSON string which DataTables will
     * see and show to the user in the browser.
     *
     * @param  string $msg Message to send to the client
     */
    static function fatal ( $msg )
    {
        echo json_encode( array(
            "error" => $msg
        ) );
        exit(0);
    }
    /**
     * Create a PDO binding key which can be used for escaping variables safely
     * when executing a query with sql_exec()
     *
     * @param  array &$a    Array of bindings
     * @param  *      $val  Value to bind
     * @param  int    $type PDO field type
     * @return string       Bound key to be used in the SQL where this parameter
     *   would be used.
     */
    static function bind ( &$a, $val, $type )
    {
        $key = ':binding_'.count( $a );
        $a[] = array(
            'key' => $key,
            'val' => $val,
            'type' => $type
        );
        return $key;
    }
    /**
     * Pull a particular property from each assoc. array in a numeric array,
     * returning and array of the property values from each item.
     *
     *  @param  array  $a    Array to get data from
     *  @param  string $prop Property to read
     *  @param  bool  $isJoin  Determine the the JOIN/complex query or simple one
     *  @return array        Array of property values
     */
    static function pluck ( $a, $prop, $isJoin = false )
    {
        $out = array();
        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            $out[] = ($isJoin && isset($a[$i]['as'])) ? $a[$i][$prop]. ' AS '.$a[$i]['as'] : $a[$i][$prop];
        }
        return $out;
    }
}
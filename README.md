# datatables-js-server-api (Use release 1.1)
This api help to datatables server-side integrations by SSP code otimized

#How to use:
<h3>Config:</h3>
<pre>
required 'path/to/Datatables.php';

//set config connections and requests
$config['config'] = array(
                'user' => 'user',
                'pass' => 'pass',
                'db'   => 'db name',
                'host' => 'host'
            ); 

$dt = new Datatables( $config );
</pre>

<h3>Adding columns</h3>         
<pre>
//addCols($field, $alias=null, $params=array(), $dt=null ) 
$dt->addCols( 'post_id', 'p' );
// or
$dt->addCols( 
				array( 
					array('title', 'p' ),
					array('category', 'c', ['as'=>'cat'] ),
					array('created_at', 'p', 
					      array( 'formatter' => function( $d, $row ) {
            				return array( 'display'=>date( 'd/m/Y', strtotime($d) ), 'timestamp'=>$d ); // output formated
        				})
					)
				) // end params
			);
</pre>

<h3>Adding tables ( required least 1 )</h3>
<pre>
// addTables(  $table, $key='id', $alias  )
$dt->addTables( 'posts', 'post_id', 'p');
//or
$dt->addTables( array(
				array( 'categories', 'category_id', 'c' ),
				...
			));
</pre>

<h3>Adding joins (new method linked)</h3>
<pre>
// addJoin( $join_table, $join_pk, $join_alias, $fk, $join_type='', $target_alias=null )
$dt->addTables( 'posts', 'id', 'p')->addJoin('users', 'id', 'u', 'user_id', 'LEFT')->addJoin('users','id','a','autor')
</pre>

<h3>Add additional where </h3>
<pre>
// addWhere($field, $value='', $alias=null, $op='=', $concat='and' )
$dt->addWhere( 'created_at', $_POST['initdate'], 'p', '>=' );
or 
$dt->addWhere( 
        array(
          array( 'created_at', $_POST['initdate'], 'p', '>='),
          ....
        )
      );
</pre>

<h3>Show return:</h3>
<pre>
echo json_encode( $dt->render() );
</pre>

<h3>Javascript render helper</h3>
<pre>
//renderJS( $selector, $params, $lang=null, $urlParams=null, $inst='table' )
$dt->renderJS(
			"#table",            // html selector table   
			'"processing": true, // server side
			"serverSide": true,  // server side
			"ajax": {            // data source
				"url": "path/to/data",
				"type": "GET"    // Use POST if you prefer
			},
			"columnDefs": [      // columns modifications
	            {  targets: 2, render: { _: "display",  sort: "timestamp"  }  	}, //get date formated
	    ],',
			null,                // Language json file (Default portuguese-br) | see doc https://datatables.net/plug-ins/i18n/
			array( 
				"param" => '$("#input").val()',   // additional url parameters to filter data source
	    ),
		);
</pre>
Outputs basic javascript call 

For javascript configuration and server side working see https://datatables.net/;

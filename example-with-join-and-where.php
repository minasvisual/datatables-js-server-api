<?php
	require "DataTables.php";//set config connections and requests
	$config['config'] = [
		                'user' => 'root',
		                'pass' => '',
		                'db'   => 'test',
		                'host' => 'localhost'
		            ]; 
	$dt = new Datatables( $config );

	if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
	{

		
		$dt->addTables( 'users', 'id', 'u');
		$dt->addTables( 'posts', 'id', 'p', 'users','user_id');
		$dt->addCols( 
	         [
	            ['title', 'p'],
	            ['name', 'u'],
	            ['email', 'u'],
	            ['date', 'p', ['formatter' => function( $value, $row ){
	                    return [ 'display'=>date( 'd/m/Y', strtotime($value) ), 'timestamp'=>$d=$value ]; // output formated
	                }]
	            ]
	        ] // end params
	    );
		$dt->addWhere('status','1','u');

		echo json_encode( $dt->render() );
		exit;
	}
?>
<html>
<head>
	<link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" /><!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	<script type="text/javascript" src="//code.jquery.com/jquery-1.12.3.js" ></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js" ></script>
</head>
<body class="container">
	<h2>Data tables example with join query and additional where</h2>
	<table id="example" class="display table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Title</th>
                <th>User</th>
                <th>Email</th>
                <th>Post Date</th>
            </tr>
        </thead>
    </table>

<script>
	$(document).ready(function() 
	{
	    <?=$dt->renderJS(
            "#example",            // html selector table   
            '"processing": true, // server side
            "serverSide": true,  // server side
            "ajax": {            // data source
                "url": "example-with-join-and-where.php",
                "type": "GET"
            },
            "columnDefs": [      // columns modifications
                {  targets: 3, render: { _: "display",  sort: "timestamp"  }    }, //get date formated
        	],'
        );?>
	} );
</script>
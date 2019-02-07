<?php
	require "DataTables.php";//set config connections and requests
	$config['config'] = [
		                'user' => 'root',
		                'pass' => '',
		                'db'   => 'hsiinstitute',
		                'host' => 'localhost'
		            ]; 
	$config['method'] = 'post';
	$dt = new Datatables( $config );

	if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
	{
		// $dt->addTables( 'posts', 'id', 'p')
			// ->addJoin('users', 'id', 'u', 'user_id')
			// ->addJoin('users','id','a','autor');
			
		// $dt->addCols( 
	         // [
	            // ['title', 'p'],
	            // ['name', 'a',['as'=>'aname'] ],
	            // ['name', 'u',['as'=>'uname']],
	            // ['email', 'u'],
	            // ['date', 'p', ['formatter' => function( $value, $row ){
	                    // return [ 'display'=>date( 'd/m/Y', strtotime($value) ), 'timestamp'=>$value ]; // output formated
	                // }]
	            // ]
	        // ] // end params
	    // );
		// $dt->addWhere('status','1','u');

		// echo json_encode( $dt->render() );
		$type = 'partner';
		$countries = ['BR'=>'Brasil'];
		$status = array(1=>'Active', 0=>'Inactive', 2=>'Approval', 3=>'Payment');
		$contr_type = array('exin'=>'Exin', 'apmg'=>'APMG', 'peoplecert'=>'Peoplecert', 'suply'=>'Suply of materials');
	
		//$dt = new Datatables(); 
		//$dt = $this->datatables;

			$dt->addTables( 'smv3_users_account', 'user_id', 'A')
						->addJoin( 'smv3_users_company', 'user_id', 'B', 'user_id', 'LEFT')
						->addJoin( 'smv3_users_contract', 'user_id', 'C', 'user_id', 'LEFT');
	
		$dt->addWhere( 'user_level', 'partner', 'A' );
		$dt->addWhere( 'deleted', '1', 'A', '<>' );

		$dt->addCols('user_id','A');
		$dt->addCols('user_name','A');
		$dt->addCols('user_email','A');
		$dt->addCols('contract_type','B');
		$dt->addCols('country','A', array( 'formatter' => function( $d ) use ($countries) {
			return  @$countries[$d];
		}));
		$dt->addCols('city','A');
		$dt->addCols('state','A');
		$dt->addCols('status','A', array( 'formatter' => function( $d, $row) use ($status) {
			return  $status[$d];
		}));
		$dt->addCols('enddate','C', array( 'formatter' => function( $d, $row) use ($type){
			return  '<a href="/gestao/accounts/contract?id='.$row['user_id'].'&type='.$type.'"  data-toggle="tooltip" data-placement="top" title="Expiration Date">'
													.date('d/m/Y', strtotime($row['enddate']) ).'</a>';
		}));
		$dt->addCols('user_id','A', array( 'formatter' => function( $d, $row) use ($type){
			return  '<a href="/gestao/accounts/editar?id='.$row['user_id'].'&type='.$type.'">'
								.'<i class="glyphicon glyphicon-edit"></i></a>';
		}));
		$dt->addCols('user_id','A', array( 'formatter' => function( $d, $row) use ($type){
			return '<a href="/gestao/accounts/excluir?id='.$row['user_id'].'&type='.$type.'" class="confirmDelete">'
								.'<i class="glyphicon glyphicon-trash"></i></a>';
		}));

		echo json_encode($dt->render());
		exit;
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
                <th>ID</th>
				<th>Company Name</th>
				<th>Email'</th> 
				<th>Type</th>
				<th>Country</th>
				<th>City</th>
				<th>State</th>
				<th>Status</th>
				<th>Contract</th>
				<th>Edit</th>
				<th>Delete</th>
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
                "type": "POST"
            },
            "columnDefs": [      // columns modifications
                //{  targets: 4, render: { _: "display",  sort: "timestamp"  }    }, //get date formated
        	],'
        );?>
	} );
</script>
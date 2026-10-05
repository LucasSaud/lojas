<?php

    include('_core/_includes/config.php'); 
    
    global $db_con;
    
    $insubdominio = $_GET['insubdominio'];
    
    $insubdominio = $_GET['insubdominio'];
    if( !$insubdominio ) {
        $insubdominio = array_shift((explode('.', $_SERVER['HTTP_HOST'])));
        if( $insubdominio == $firstdomain ) {
          $insubdominio = "";
        }
    }
    
    // access token
    $query = mysqli_query( $db_con, "SELECT accesstoken FROM estabelecimentos WHERE subdominio = '$insubdominio' LIMIT 1" );
    $data = mysqli_fetch_array( $query );
    $access_token = $data['accesstoken'];
    
    //update
    require_once('_core/_includes/functions/mercadopago/vendor/autoload.php')	;
	MercadoPago\SDK::setAccessToken($access_token);
	$pagamento = MercadoPago\Payment::find_by_id($_GET["data_id"]);

	$status = $pagamento->{'status'};
	
	$referencia = $pagamento->external_reference;
	
	$pagamentotime = 'NULL';
	
	if($status == 'approved'){
	    $pagamentotime = 'now()';
	}
	$tipo = $pagamento->payment_method_id.' - '.$pagamento->payment_type_id;
	$temp = json_encode($pagamento->transaction_details);
	mysqli_query($db_con, "UPDATE pedidos SET statuspagamento = '$status',datadepagamento=$pagamentotime,detalhespagamento='$temp',pagamentotipo='$tipo' WHERE referencia = '$referencia' ");
	
    echo $access_token;
?>
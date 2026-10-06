<?php

// Notificação de pagamento do Mercado Pago (webhook) para pedidos pagos online.
// Consulta o pagamento com a credencial da loja e grava a situação no pedido.

include('_core/_includes/config.php');

global $db_con;

$insubdominio = isset( $_GET['insubdominio'] ) ? $_GET['insubdominio'] : "";
if( !$insubdominio ) {
	$insubdominio = array_shift( ( explode( '.',$_SERVER['HTTP_HOST'] ) ) );
}
$insubdominio = mysqli_real_escape_string( $db_con,$insubdominio );

$data_id = isset( $_GET['data_id'] ) ? $_GET['data_id'] : "";
if( !ctype_digit( (string) $data_id ) ) {
	http_response_code( 400 );
	exit;
}

$query = mysqli_query( $db_con, "SELECT accesstoken FROM estabelecimentos WHERE subdominio = '$insubdominio' LIMIT 1" );
$data = mysqli_fetch_array( $query );
if( !$data || !$data['accesstoken'] ) {
	http_response_code( 404 );
	exit;
}

require_once('_core/_includes/functions/mercadopago/vendor/autoload.php');
MercadoPago\SDK::setAccessToken( segredo_abre( $data['accesstoken'] ) );
$pagamento = MercadoPago\Payment::find_by_id( $data_id );

if( !$pagamento || !$pagamento->external_reference ) {
	http_response_code( 404 );
	exit;
}

$status = mysqli_real_escape_string( $db_con,$pagamento->status );
$referencia = mysqli_real_escape_string( $db_con,$pagamento->external_reference );
$tipo = mysqli_real_escape_string( $db_con,$pagamento->payment_method_id.' - '.$pagamento->payment_type_id );
$detalhes = mysqli_real_escape_string( $db_con,json_encode( $pagamento->transaction_details ) );
$pagamentotime = $pagamento->status == 'approved' ? 'now()' : 'NULL';

mysqli_query( $db_con, "UPDATE pedidos SET statuspagamento = '$status',datadepagamento=$pagamentotime,detalhespagamento='$detalhes',pagamentotipo='$tipo' WHERE referencia = '$referencia' " );

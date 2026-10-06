<?php

// Cifra as credenciais de pagamento das lojas que ainda estão em texto puro no banco.
// Rodar uma vez depois de definir APP_KEY (pode rodar de novo: linhas já cifradas são ignoradas).
//
//   docker compose exec web php /var/www/scripts/cifrar-credenciais.php

if( PHP_SAPI !== "cli" ) {
	exit( "Somente pela linha de comando.\n" );
}

$_SERVER['HTTP_HOST'] = getenv( "APP_DOMAIN" ) ?: "localhost";
$_SERVER['REQUEST_URI'] = "/";
$_SERVER['HTTPS'] = "on";

chdir( __DIR__."/../app" );
include( "_core/_includes/config.php" );

if( !segredo_chave() ) {
	exit( "APP_KEY ausente ou inválida.\n" );
}

$colunas = array( "accesstoken","pagamento_mercadopago_secret","pagamento_getnet_client_secret" );
$cifradas = 0;

$lojas = mysqli_query( $db_con, "SELECT id,".implode( ",",$colunas )." FROM estabelecimentos" );
while( $loja = mysqli_fetch_assoc( $lojas ) ) {
	foreach( $colunas as $coluna ) {
		if( notnull( $loja[$coluna] ) && strpos( $loja[$coluna],"enc:v1:" ) !== 0 ) {
			mysqli_query( $db_con, "UPDATE estabelecimentos SET $coluna = '".segredo_cifra( $loja[$coluna] )."' WHERE id = '".$loja['id']."'" );
			$cifradas++;
		}
	}
}

echo "Credenciais cifradas: $cifradas\n";

<?php
// Consulta do Caddy (TLS sob demanda, "ask"): responde 200 se o host pode receber certificado,
// isto é, o domínio principal, "conheca" ou o subdomínio de uma loja/cidade existente. Senão, 404.
// Não inclui o config.php de propósito: o Caddy chama por HTTP puro e o config redirecionaria para HTTPS.

$dominio = strtolower( getenv( "APP_DOMAIN" ) );
$host = strtolower( isset( $_GET['domain'] ) ? $_GET['domain'] : "" );
$sufixo = ".".$dominio;

$ok = $host === $dominio;

if( !$ok && substr( $host, -strlen( $sufixo ) ) === $sufixo ) {
	$sub = substr( $host, 0, -strlen( $sufixo ) );
	$ok = $sub === "conheca";
	// A expressão regular é o que protege a consulta: só letras, números e hífen chegam ao SQL.
	if( !$ok && preg_match( '/^[a-z0-9-]+$/', $sub ) ) {
		$db = mysqli_connect( getenv( "DB_HOST" ), getenv( "DB_USER" ), getenv( "DB_PASS" ), getenv( "DB_NAME" ) );
		$ok = $db && mysqli_num_rows( mysqli_query( $db, "SELECT 1 FROM estabelecimentos WHERE subdominio = '$sub' AND excluded != '1' UNION SELECT 1 FROM cidades WHERE subdominio = '$sub' LIMIT 1" ) ) > 0;
	}
}

http_response_code( $ok ? 200 : 404 );

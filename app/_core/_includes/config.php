<?php

set_time_limit(90);

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// error_reporting(E_ALL);

// Time

date_default_timezone_set('America/Sao_Paulo');

// Ambiente: tudo que muda entre instalações vem de variáveis de ambiente (.env no Docker Compose).

function env( $nome,$padrao = "" ) {
	$valor = getenv( $nome );
	return $valor === false ? $padrao : $valor;
}

// Url

$simple_url = env( "APP_DOMAIN","localhost" );
$httprotocol = env( "APP_HTTPS","1" ) == "1" ? "https://" : "http://";

// Em produção, atrás de proxy com TLS, o HTTPS chega em X-Forwarded-Proto.
$https_ativo = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ) || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' );
if( $httprotocol == "https://" && !$https_ativo && isset( $_SERVER['HTTP_HOST'] ) ) {
	header( "Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
}

$suport_url = $httprotocol."conheca.".$simple_url."/#contato";
$system_url = $httprotocol.$simple_url."/administracao";
$panel_url = $httprotocol.$simple_url."/painel";
$admin_url = $httprotocol.$simple_url."/administracao";
$just_url = $httprotocol.$simple_url;
$app_url = $httprotocol.$simple_url."/app";
$afiliado_url = $httprotocol.$simple_url."/afiliado";

// Comissão Afiliados
$comissao_afiliados = "10";

// Title

$seo_title = "Rei do Script";
$seo_description = "Compre sem sair de casa!";
//$titulo_topo = "Velox Imports<strong>.</strong>"; //TITULO DA LOGO PARA USAR TITULO INVES DE IMAGEM TIRAR OS // DO COMEÇO E COLOCAR NO DE BAIXO 
$titulo_topo = '<img src="/_core/_cdn/img/logo.png">'; //US4R LOGO INVES DE TITUL5
$titulo_rodape ="Rei do Script";
$sub_titulo_rodape ="O CATÁLOGO VIRTUAL DESCOMPLICADO!"; //Endereço ou Slogan
$titulo_rodape_marketplace ="Rei do Script, Compre sem sair de casa!"; //Endereço ou Slogan


// Redes/Whatsapp/Email
$whatsapp = "11982889012";
$usrtelefone = "11982889012";
$email ="#";
$youtube ="#";
$instagram="#";
$facebook ="#";

// Db

$db_host = env( "DB_HOST" );
$db_user = env( "DB_USER" );
$db_pass = env( "DB_PASS" );
$db_name = env( "DB_NAME" );

// SMTP

$smtp_name = env( "SMTP_NAME" );
$smtp_user = env( "SMTP_USER" );
$smtp_pass = env( "SMTP_PASS" );

// Manunten

$manutencao = false;

if( $manutencao ) {

	include("manutencao.php");
	die;

}

// Includes

include("functions.php");

// CSRF: todo POST precisa vir do próprio site.
if( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	origem_exige();
}

// Tokens


// Recaptcha
// Gerar em: https://www.google.com/recaptcha/admin/
$recaptcha_sitekey = env( "RECAPTCHA_SITEKEY" );
$recaptcha_secretkey = env( "RECAPTCHA_SECRETKEY" );

//External token Utilizado para receber os callbacks do mercado pago pro sistema, pode manter padr
$external_token = env( "EXTERNAL_TOKEN" );

// Mercado pago
// Gerar em: https://www.mercadopago.com.br/developers/panel/credentials
$mp_sandbox = false;

$mp_sandbox = env( "MP_SANDBOX","0" ) == "1";
$mp_public_key = env( "MP_PUBLIC_KEY" );
$mp_acess_token = env( "MP_ACCESS_TOKEN" );
$mp_client_id = env( "MP_CLIENT_ID" );
$mp_client_secret = env( "MP_CLIENT_SECRET" );

// Plano padr (id)

$plano_default = "5";

// Root path

$rootpath = $_SERVER["DOCUMENT_ROOT"];

// Images

$image_max_width = 1000;
$image_max_height = 1000;
$gallery_max_files  = 10;

// Global header and footer

$system_header = "";
$system_footer = "";

// Keep Alive

if( isset($_SESSION['user']['logged']) && $_SESSION['user']['logged'] == "1" && isset($_SESSION['user']['keepalive']) && strlen( $_SESSION['user']['keepalive'] ) >= 10 && $_SESSION['user']['keepalive'] != $_COOKIE['keepalive'] ) {
	setcookie( 'keepalive', "kill", time() - 3600 );
	if( strlen( $_SESSION['user']['keepalive'] ) >= 10 ) {
		setcookie( 'keepalive', $_SESSION['user']['keepalive'], (time() + (120 * 24 * 3600)) );
	}
}

$keepalive = isset($_COOKIE['keepalive']) ? $_COOKIE['keepalive'] : "";

if( (!isset($_SESSION['user']['logged']) || $_SESSION['user']['logged'] != "1") && strlen( $keepalive ) >= 10 ) {

	make_login($keepalive,"","keepalive","2");

}

?>
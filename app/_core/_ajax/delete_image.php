<?php

// Apaga uma imagem da galeria de um produto.
// Administrador apaga qualquer uma; a loja só apaga as suas. Quem autoriza é a sessão,
// e a chamada precisa trazer o token anti-CSRF (csrf_token()).

include('../_includes/config.php');

global $db_con;
global $rootpath;

// Só por POST e com o token da sessão: um link ou imagem em outro site não consegue disparar a exclusão.
if( $_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_confere( isset( $_POST['csrf'] ) ? $_POST['csrf'] : null ) ) {
	http_response_code( 403 );
	exit;
}

$fileid = (int) ( isset( $_POST['fileid'] ) ? $_POST['fileid'] : 0 );

$logado = isset( $_SESSION['user']['logged'] ) && $_SESSION['user']['logged'] == "1";
$admin = $logado && $_SESSION['user']['level'] == "1";
$loja = isset( $_SESSION['estabelecimento']['id'] ) ? $_SESSION['estabelecimento']['id'] : "";

$midia = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM midia WHERE id = '$fileid' LIMIT 1" ) );

if( !$logado || !$midia || !( $admin || ( $loja && $midia['rel_estabelecimentos_id'] == $loja ) ) ) {
	http_response_code( 403 );
	exit;
}

@unlink( $rootpath."/_core/_uploads/".$midia['url'] );
mysqli_query( $db_con, "DELETE FROM midia WHERE id = '$fileid'" );

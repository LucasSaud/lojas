<?php

// =====================================================================
// user.php - sessão e login, permissões de acesso, planos, vouchers e
// assinaturas, sacola, checkout e pedidos.
//
// Os argumentos chegam já escapados pelos chamadores
// (mysqli_real_escape_string nas páginas); as funções não escapam de novo.
// =====================================================================

if( session_status() == PHP_SESSION_NONE ) {
	session_start();
}

// ---------------------------------------------------------------------
// Log de auditoria
// ---------------------------------------------------------------------

function log_register( $rel_users_id,$rel_lojas_id,$info ) {

	global $db_con;

	$agora = date("Y-m-d H:i:s");

	mysqli_query( $db_con, "INSERT INTO logs(rel_users_id,rel_lojas_id,info,date_time) VALUES ('$rel_users_id','$rel_lojas_id','$info','$agora')" );

}

// ---------------------------------------------------------------------
// Usuário logado e login
// ---------------------------------------------------------------------

// Dado do usuário logado (sessão).
function user_info( $info ) {

	return isset( $_SESSION['user'][$info] ) ? $_SESSION['user'][$info] : null;

}

// Dado de qualquer usuário (tabela users).
function user_info_out( $id,$info ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT * FROM users WHERE id = '$id' LIMIT 1" );
	$data = $query ? mysqli_fetch_array( $query ) : null;

	return isset( $data[$info] ) ? $data[$info] : null;

}

// $method "login": $email e $pass do formulário. $method "keepalive": $email é o token do cookie.
// $keepalive "1" gera um novo token de "manter conectado", "2" mantém o atual, qualquer outro valor apaga.
function make_login( $email,$pass,$method,$keepalive ) {

	global $db_con;

	if( $method == "login" ) {
		$where = "email='$email' AND password='".md5( $pass )."'";
	} elseif( $method == "keepalive" ) {
		// Token vazio casaria com qualquer usuário que nunca marcou "manter conectado".
		if( strlen( $email ) < 10 ) {
			return false;
		}
		$where = "keepalive='$email'";
	} else {
		return false;
	}

	$query = mysqli_query( $db_con, "SELECT * FROM users WHERE ($where) LIMIT 1" );
	$user = $query ? mysqli_fetch_array( $query ) : null;

	if( !$user ) {
		return false;
	}

	$token = "";
	if( $keepalive == "1" ) {
		$token = bin2hex( random_bytes( 16 ) );
	} elseif( $keepalive == "2" ) {
		$token = $user['keepalive'];
	}

	$_SESSION['user'] = array(
		"logged" => "1",
		"id" => $user['id'],
		"nome" => $user['nome'],
		"email" => $user['email'],
		"level" => $user['level'],
		"status" => $user['status'],
		"operacao" => $user['operacao'],
	);

	if( $user['level'] == "2" ) {

		// Lojista: carrega a loja na sessão e atualiza a situação do plano.
		$query = mysqli_query( $db_con, "SELECT * FROM estabelecimentos WHERE (rel_users_id = '".$user['id']."') LIMIT 1" );
		$loja = $query ? mysqli_fetch_array( $query ) : null;

		$_SESSION['user']['keepalive'] = $token;
		$_SESSION['estabelecimento'] = array(
			"id" => $loja['id'],
			"avatar" => isset( $loja['avatar'] ) ? $loja['avatar'] : null,
			"perfil" => $loja['perfil'],
			"nome" => $loja['nome'],
			"subdominio" => $loja['subdominio'],
			"logged" => 1,
			"level" => $user['level'],
			"funcionalidade_marketplace" => $loja['funcionalidade_marketplace'],
			"funcionalidade_banners" => $loja['funcionalidade_banners'],
			"funcionalidade_variacao" => $loja['funcionalidade_variacao'],
			"status" => $loja['status'],
			"status_force" => $loja['status_force'],
			"excluded" => $loja['excluded'],
			"expiracao" => $loja['expiracao'],
		);

		atualiza_estabelecimento( $loja['id'],"online" );

	} else {

		// Administrador ou afiliado: dados complementares em users_data.
		$query = mysqli_query( $db_con, "SELECT * FROM users_data WHERE ( rel_users_id = '".$user['id']."' ) LIMIT 1" );
		$dados = $query ? mysqli_fetch_array( $query ) : null;

		$_SESSION['user']['estado'] = $dados['estado'];
		$_SESSION['user']['cidade'] = $dados['cidade'];
		$_SESSION['user']['telefone'] = $dados['telefone'];
		$_SESSION['user']['keepalive'] = $token;

	}

	mysqli_query( $db_con, "UPDATE users SET keepalive='$token',last_login='".date("Y-m-d H:i:s")."' WHERE id = '".$user['id']."'" );

	data_log( "fez login" );

	return true;

}

// Token anti-CSRF da sessão: as páginas o enviam nas ações feitas por POST e o destino confere.
function csrf_token() {

	if( empty( $_SESSION['csrf'] ) ) {
		$_SESSION['csrf'] = bin2hex( random_bytes( 32 ) );
	}

	return $_SESSION['csrf'];

}

function csrf_confere( $token ) {

	return !empty( $_SESSION['csrf'] ) && is_string( $token ) && hash_equals( $_SESSION['csrf'],$token );

}

// Token usado nas chamadas ajax: base64( id:senha_invertida ).
function user_token_generate( $uid ) {

	return base64_encode( $uid.":".strrev( user_info_out( $uid,"password" ) ) );

}

// Valida o token (id + hash da senha). Devolve "1" quando confere e "" quando não,
// seja qual for o dado pedido em $theinfo, como no sistema original.
function user_token_info( $id,$pass,$theinfo ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT * FROM users WHERE id = '$id' AND password = '$pass' LIMIT 1" );
	$user = $query ? mysqli_fetch_array( $query ) : null;

	if( !$user ) {
		return "";
	}

	if( $user['level'] == "2" ) {
		// Consulta sem efeito herdada do sistema original (id vazio); mantida para não alterar o comportamento.
		mysqli_query( $db_con, "SELECT * FROM estabelecimentos WHERE (rel_users_id = '') LIMIT 1" );
	}

	return "1";

}

// Recuperação de senha: grava a chave e envia o e-mail com o link.
function recover_password_generate( $email,$key,$msg ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT * FROM users WHERE (email='$email') LIMIT 1" );

	if( !$query || !mysqli_num_rows( $query ) ) {
		return false;
	}

	if( mysqli_query( $db_con, "UPDATE users SET recover_key='$key' WHERE email = '$email'" ) ) {
		return html_mail( $email,"Recuperação de senha",$msg ) ? true : false;
	}

}

function recover_password_save( $key,$password ) {

	global $db_con;

	// Chave vazia casaria com todo usuário que já concluiu uma recuperação (recover_key = '')
	// e trocaria a senha de todos eles. O sistema original aceitava.
	if( !notnull( $key ) ) {
		return false;
	}

	$query = mysqli_query( $db_con, "SELECT * FROM users WHERE (recover_key='$key') LIMIT 1" );

	if( !$query || !mysqli_num_rows( $query ) ) {
		return false;
	}

	if( mysqli_query( $db_con, "UPDATE users SET password='".md5( $password )."',recover_key='' WHERE recover_key='$key'" ) ) {
		return true;
	} else {
		return false;
	}

}

// ---------------------------------------------------------------------
// Permissões de acesso (redirecionam e interrompem a página quando o acesso não é permitido)
// ---------------------------------------------------------------------

function user_login_url() {

	global $httprotocol;

	return get_just_url()."/login?msg=restrict&redirect=".$httprotocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

}

// Exige usuário logado do nível $level (1 admin, 2 loja, 3 afiliado). O administrador passa em todos.
function restrict( $level ) {

	$logado = isset( $_SESSION['user']['logged'] ) && $_SESSION['user']['logged'] == "1";
	$nivel = isset( $_SESSION['user']['level'] ) ? $_SESSION['user']['level'] : null;

	if( $logado && ( $nivel == "1" || $nivel == $level ) ) {
		return;
	}

	header( "Location: ".user_login_url() );
	exit;

}

// Redireciona e interrompe a página. Sem interromper, o restante do script (inclusive exclusões)
// rodava para quem não tinha acesso; o navegador só era redirecionado depois.
function user_bloqueia( $url ) {

	header( "Location: ".$url );
	exit;

}

// Exige loja logada, não bloqueada e não excluída.
function restrict_estabelecimento() {

	$loja = isset( $_SESSION['estabelecimento'] ) ? $_SESSION['estabelecimento'] : array();

	if( isset( $loja['excluded'] ) && $loja['excluded'] == "1" ) {
		user_bloqueia( get_just_url()."/painel/configuracoes/reativacao" );
	}

	if( isset( $loja['status_force'] ) && $loja['status_force'] == "1" ) {
		user_bloqueia( get_just_url()."/painel/inativo" );
	}

	if( !isset( $loja['logged'] ) || $loja['logged'] != "1" ) {
		user_bloqueia( user_login_url() );
	}

}

// Loja da vitrine precisa estar ativa e não bloqueada.
function is_active( $eid ) {

	$status_force = data_info( "estabelecimentos",$eid,"status_force" );
	$status = data_info( "estabelecimentos",$eid,"status" );

	if( $status_force == "1" || $status != "1" ) {
		user_bloqueia( "/desativado" );
	}

}

// Exige que o plano da loja logada inclua a funcionalidade.
function restrict_funcionalidade( $funcionalidade ) {

	if( !isset( $_SESSION['estabelecimento'][$funcionalidade] ) || $_SESSION['estabelecimento'][$funcionalidade] != "1" ) {
		user_bloqueia( get_just_url()."/painel/inicio?msg=funcaodesativada" );
	}

}

// Exige plano ativo na loja logada.
function restrict_expirado() {

	if( !isset( $_SESSION['estabelecimento']['status'] ) || $_SESSION['estabelecimento']['status'] != "1" ) {
		user_bloqueia( get_just_url()."/painel/inicio?msg=inativo" );
	}

}

// Bloqueia o cadastro de produtos quando a loja atingiu o limite do plano.
function restrict_limite( $eid ) {

	global $db_con;

	$limite = data_info( "estabelecimentos",$eid,"limite_produtos" );
	$produtos = mysqli_query( $db_con, "SELECT id FROM produtos WHERE rel_estabelecimentos_id = '$eid'" );
	$total = $produtos ? mysqli_num_rows( $produtos ) : 0;

	if( $total >= $limite ) {
		user_bloqueia( get_just_url()."/painel/produtos/limitado" );
	}

}

// ---------------------------------------------------------------------
// Planos
// ---------------------------------------------------------------------

function new_plano( $destaque,$nome,$descricao,$duracao_meses,$duracao_dias,$valor_total,$valor_mensal,$link,$termos,$funcionalidade_marketplace,$funcionalidade_variacao,$funcionalidade_banners,$visible,$status,$ordem,$limite_produtos ) {

	global $db_con;

	if( mysqli_query( $db_con, "INSERT INTO planos (destaque,nome,descricao,duracao_meses,duracao_dias,valor_total,valor_mensal,link,termos,funcionalidade_marketplace,funcionalidade_variacao,funcionalidade_banners,visible,status,ordem,limite_produtos) VALUES ('$destaque','$nome','$descricao','$duracao_meses','$duracao_dias','$valor_total','$valor_mensal','$link','$termos','$funcionalidade_marketplace','$funcionalidade_variacao','$funcionalidade_banners','$visible','$status','$ordem','$limite_produtos')" ) ) {

		data_log( "criou o plano ".$nome,false );
		return true;

	} else {

		return false;

	}

}

function edit_plano( $id,$destaque,$nome,$descricao,$duracao_meses,$duracao_dias,$valor_total,$valor_mensal,$link,$termos,$funcionalidade_marketplace,$funcionalidade_variacao,$funcionalidade_banners,$visible,$status,$ordem,$limite_produtos ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE planos SET nome = '$nome',descricao = '$descricao',duracao_meses = '$duracao_meses',duracao_dias = '$duracao_dias',valor_total = '$valor_total',valor_mensal = '$valor_mensal',link = '$link',termos = '$termos',funcionalidade_marketplace = '$funcionalidade_marketplace',funcionalidade_variacao = '$funcionalidade_variacao',funcionalidade_banners = '$funcionalidade_banners',visible = '$visible',status = '$status',visible = '$visible',ordem = '$ordem',limite_produtos = '$limite_produtos' WHERE id = '$id'" ) ) {

		if( $destaque ) {
			mysqli_query( $db_con, "UPDATE planos SET destaque = '$destaque' WHERE id = '$id'" );
		}

		data_log( "alterou o plano ".$nome,false );
		return true;

	} else {

		return false;

	}

}

function delete_plano( $id ) {

	global $db_con;

	// O sistema original busca o nome na tabela "plano", que não existe; o log sai sem o nome.
	$nome = data_info( "plano",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM planos WHERE id = '$id'" ) ) {

		data_log( "removeu o plano ".$nome,false );
		return true;

	} else {

		return false;

	}

}

// ---------------------------------------------------------------------
// Vouchers
// ---------------------------------------------------------------------

function new_voucher( $plano,$descricao ) {

	global $db_con;

	// Código único no formato XXXX-XXXX-XXXX-XXXX.
	do {
		$codigo = strtoupper( random_key(4)."-".random_key(4)."-".random_key(4)."-".random_key(4) );
		$existe = mysqli_query( $db_con, "SELECT codigo FROM vouchers WHERE codigo = '$codigo'" );
	} while( $existe && mysqli_num_rows( $existe ) );

	if( mysqli_query( $db_con, "INSERT INTO vouchers (rel_planos_id,descricao,codigo,status) VALUES ('$plano','$descricao','$codigo','1')" ) ) {

		data_log( "criou o voucher ".$descricao );
		return true;

	} else {

		return false;

	}

}

function edit_voucher( $id,$plano,$descricao ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE vouchers SET rel_planos_id = '$plano',descricao = '$descricao' WHERE id = '$id'" ) ) {

		data_log( "editou a voucher ".$descricao );
		return true;

	} else {

		return false;

	}

}

// Remove o voucher e cancela a assinatura criada por ele.
function delete_voucher( $id ) {

	global $db_con;

	$descricao = data_info( "vouchers",$id,"descricao" );
	$assinatura = data_info( "vouchers",$id,"rel_assinaturas_id" );

	if( mysqli_query( $db_con, "DELETE FROM vouchers WHERE id = '$id'" ) ) {

		mysqli_query( $db_con, "UPDATE assinaturas SET status = '3',used = '2' WHERE id = '$assinatura'" );

		data_log( "removeu o voucher ".$descricao,false );
		return true;

	} else {

		return false;

	}

}

// ---------------------------------------------------------------------
// Assinaturas (planos contratados pelas lojas)
//   status: 0 aguardando pagamento, 1 disponível, 3 cancelada
//   used:   0 aguardando uso, 1 em uso, 3 expirada
// ---------------------------------------------------------------------

// Cópia dos dados do plano gravada na assinatura (coluna => valor já escapado).
function assinatura_do_plano( $plano ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT * FROM planos WHERE id = '$plano' LIMIT 1" );
	$data = $query ? mysqli_fetch_array( $query ) : null;

	$campos = array();
	foreach( array( "afiliado","nome","descricao","duracao_meses","duracao_dias","valor_total","valor_mensal","termos","funcionalidade_marketplace","funcionalidade_variacao","funcionalidade_banners","limite_produtos" ) as $campo ) {
		$campos[$campo] = isset( $data[$campo] ) ? mysqli_real_escape_string( $db_con,$data[$campo] ) : "";
	}

	return $campos;

}

// Insere a assinatura (coluna => valor) e devolve o id, ou false.
function assinatura_insere( $campos ) {

	global $db_con;

	if( mysqli_query( $db_con, "INSERT INTO assinaturas (".implode( ",",array_keys( $campos ) ).") VALUES ('".implode( "','",$campos )."')" ) ) {
		return mysqli_insert_id( $db_con );
	}

	return false;

}

// Dá o plano à loja sem cobrança (plano padrão do cadastro).
function aplicar_plano( $eid,$plano ) {

	global $db_con;

	$existe = mysqli_query( $db_con, "SELECT * FROM planos WHERE id = '$plano' LIMIT 1" );
	if( !$existe || !mysqli_num_rows( $existe ) ) {
		return false;
	}

	$p = assinatura_do_plano( $plano );

	if( assinatura_insere( array(
		"rel_planos_id" => $plano,
		"rel_estabelecimentos_id" => $eid,
		"afiliado" => $p['afiliado'],
		"nome" => $p['nome'],
		"descricao" => $p['descricao'],
		"duracao_meses" => $p['duracao_meses'],
		"duracao_dias" => $p['duracao_dias'],
		"valor_total" => $p['valor_total'],
		"valor_mensal" => $p['valor_mensal'],
		"termos" => $p['termos'],
		"funcionalidade_marketplace" => $p['funcionalidade_marketplace'],
		"funcionalidade_variacao" => $p['funcionalidade_variacao'],
		"funcionalidade_banners" => $p['funcionalidade_banners'],
		"mode" => "1",
		"status" => "1",
		"used" => "0",
		"created" => date("Y-m-d H:i:s"),
		"limite_produtos" => $p['limite_produtos'],
	) ) === false ) {
		return false;
	}

	data_log( "resgatou o plano ".$plano,false );
	atualiza_estabelecimento( $eid,"offline" );

	return true;

}

// Resgata um voucher disponível: cria a assinatura do plano dele e marca o voucher como usado.
function aplicar_voucher( $eid,$voucher ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT * FROM vouchers WHERE codigo = '$voucher' AND status = '1' LIMIT 1" );
	$data = $query ? mysqli_fetch_array( $query ) : null;

	if( !$data ) {
		return false;
	}

	$plano = $data['rel_planos_id'];
	$p = assinatura_do_plano( $plano );

	$assinatura = assinatura_insere( array(
		"rel_planos_id" => $plano,
		"rel_estabelecimentos_id" => $eid,
		"afiliado" => $p['afiliado'],
		"nome" => $p['nome'],
		"descricao" => $p['descricao'],
		"duracao_meses" => $p['duracao_meses'],
		"duracao_dias" => $p['duracao_dias'],
		"valor_total" => $p['valor_total'],
		"valor_mensal" => $p['valor_mensal'],
		"termos" => $p['termos'],
		"funcionalidade_marketplace" => $p['funcionalidade_marketplace'],
		"funcionalidade_variacao" => $p['funcionalidade_variacao'],
		"funcionalidade_banners" => $p['funcionalidade_banners'],
		"mode" => "2",
		"voucher" => $voucher,
		"status" => "1",
		"used" => "0",
		"created" => date("Y-m-d H:i:s"),
		"limite_produtos" => $p['limite_produtos'],
	) );

	if( $assinatura === false ) {
		return false;
	}

	mysqli_query( $db_con, "UPDATE vouchers SET rel_assinaturas_id = '$assinatura',status = '2' WHERE codigo = '$voucher'" );

	data_log( "resgatou o voucher ".$voucher,false );
	atualiza_estabelecimento( $eid,"offline" );

	return true;

}

// Registra a compra de um plano (aguardando pagamento) e devolve o link de pagamento.
function contratar_plano( $eid,$plano,$gateway_transaction,$gateway_ref,$gateway_link ) {

	$p = assinatura_do_plano( $plano );

	if( assinatura_insere( array(
		"rel_planos_id" => $plano,
		"rel_estabelecimentos_id" => $eid,
		"rel_estabelecimentos_nome" => data_info( "estabelecimentos",$eid,"nome" ),
		"rel_estabelecimentos_subdominio" => data_info( "estabelecimentos",$eid,"subdominio" ),
		"afiliado" => $p['afiliado'],
		"nome" => $p['nome'],
		"descricao" => $p['descricao'],
		"duracao_meses" => $p['duracao_meses'],
		"duracao_dias" => $p['duracao_dias'],
		"valor_total" => $p['valor_total'],
		"valor_mensal" => $p['valor_mensal'],
		"termos" => $p['termos'],
		"funcionalidade_marketplace" => $p['funcionalidade_marketplace'],
		"funcionalidade_variacao" => $p['funcionalidade_variacao'],
		"funcionalidade_banners" => $p['funcionalidade_banners'],
		"gateway_ref" => $gateway_ref,
		"gateway_link" => $gateway_link,
		"gateway_transaction" => $gateway_transaction,
		"gateway_payable" => date( "y-m-d",strtotime( "+4 days" ) ),
		"gateway_expiration" => date( "y-m-d",strtotime( "+8 days" ) ),
		"gateway_payment" => "0",
		"mode" => "1",
		"status" => "0",
		"used" => "0",
		"created" => date("Y-m-d H:i:s"),
		"excluded" => "0",
		"limite_produtos" => $p['limite_produtos'],
	) ) === false ) {
		return false;
	}

	data_log( "contratou o plano ".$p['nome'],false );

	return $gateway_link;

}

function update_assinatura( $id,$gateway_sync,$gateway_transaction,$gateway_email,$gateway_expiration,$gateway_payment,$valor_recebido,$status ) {

	global $db_con;

	if( !$valor_recebido ) {
		$valor_recebido = "0.00";
	}

	if( mysqli_query( $db_con, "UPDATE assinaturas SET gateway_sync = '$gateway_sync',gateway_transaction = '$gateway_transaction',gateway_email = '$gateway_email',gateway_payment = '$gateway_payment',valor_recebido = '$valor_recebido',status = '$status' WHERE id = '$id'" ) ) {
		return true;
	} else {
		return false;
	}

}

// A loja oculta a assinatura da sua lista.
function remover_assinatura( $id ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE assinaturas SET excluded = '1' WHERE id = '$id'" ) ) {
		return true;
	} else {
		return false;
	}

}

function edit_assinatura( $id,$funcionalidade_marketplace,$funcionalidade_variacao,$funcionalidade_banners,$expiration,$limite_produtos ) {

	global $db_con;

	$eid = data_info( "assinaturas",$id,"rel_estabelecimentos_id" );

	if( !mysqli_query( $db_con, "UPDATE assinaturas SET funcionalidade_marketplace = '$funcionalidade_marketplace',funcionalidade_variacao = '$funcionalidade_variacao',funcionalidade_banners = '$funcionalidade_banners',limite_produtos = '$limite_produtos' WHERE id = '$id'" ) ) {
		return false;
	}

	if( $expiration ) {
		mysqli_query( $db_con, "UPDATE assinaturas SET expiration = '$expiration' WHERE id = '$id'" );
	}

	atualiza_estabelecimento( $eid,"offline" );
	data_log( "editou a assinatura #".$id,false );

	return true;

}

// Aplica na assinatura a situação do pedido no Mercado Pago (order_status de merchant_orders).
function change_assinatura_status( $reference,$status ) {

	global $db_con;

	if( !$status ) {
		return;
	}

	$query = mysqli_query( $db_con, "SELECT * FROM assinaturas WHERE gateway_ref = '$reference' LIMIT 1" );
	$data = $query ? mysqli_fetch_array( $query ) : null;

	if( !$data ) {
		return;
	}

	$situacoes = array(
		"paid" => "1",
		"payment_required" => "0",
		"payment_in_process" => "0",
		"reverted" => "3",
		"partially_reverted" => "3",
		"partially_paid" => "3",
		"expired" => "3",
	);
	$novo = isset( $situacoes[$status] ) ? $situacoes[$status] : "";

	mysqli_query( $db_con, "UPDATE assinaturas SET gateway_payment = '$status',status = '$novo',gateway_transaction = '' WHERE gateway_ref = '$reference'" );

	atualiza_estabelecimento( $data['rel_estabelecimentos_id'],"offline" );

}

// Recalcula a situação da loja a partir das assinaturas: vence a que expirou, coloca a próxima em uso
// e grava na loja status, funcionalidades, dias restantes e limite de produtos.
// $mode "online" antes dispara a sincronização de pagamentos (cron.php?acao=sync) para a loja.
function atualiza_estabelecimento( $eid,$mode ) {

	global $db_con;
	global $external_token;

	if( $mode == "online" ) {
		remoter( get_just_url()."/cron.php?acao=sync&token=".$external_token."&eid=".$eid );
	}

	$hoje = date("Y-m-d");

	mysqli_query( $db_con, "UPDATE assinaturas SET status = '3',used = '3' WHERE expiration < '$hoje' AND status = '1' AND used = '1' AND rel_estabelecimentos_id = '$eid'" );

	// Sem assinatura em uso, a mais antiga disponível entra em uso a partir de hoje.
	$emuso = mysqli_query( $db_con, "SELECT id,rel_estabelecimentos_id,duracao_dias FROM assinaturas WHERE status = '1' AND used = '1' AND rel_estabelecimentos_id = '$eid' ORDER BY id ASC LIMIT 1" );
	if( !mysqli_num_rows( $emuso ) ) {
		$proxima = mysqli_fetch_array( mysqli_query( $db_con, "SELECT id,rel_estabelecimentos_id,duracao_dias FROM assinaturas WHERE rel_estabelecimentos_id = '$eid' AND status = '1' AND used = '0' ORDER BY id ASC LIMIT 1" ) );
		if( $proxima ) {
			$expiration = date( "y-m-d",strtotime( "+".$proxima['duracao_dias']." days" ) );
			mysqli_query( $db_con, "UPDATE assinaturas SET used = '1',expiration = '$expiration' WHERE id = '".$proxima['id']."'" );
		}
	}

	$ativa = mysqli_fetch_array( mysqli_query( $db_con, "SELECT id,expiration FROM assinaturas WHERE used = '1' AND status = '1' AND rel_estabelecimentos_id = '$eid' LIMIT 1" ) );

	if( $ativa ) {

		// Dias restantes: até o vencimento da assinatura em uso, mais a duração das que aguardam uso.
		$expiracao = (int) round( ( strtotime( $ativa['expiration'] ) - strtotime( $hoje ) ) / 86400 );
		$aguardando = mysqli_query( $db_con, "SELECT id,duracao_dias FROM assinaturas WHERE status = '1' AND used = '0' AND rel_estabelecimentos_id = '$eid' ORDER BY id DESC" );
		while( $data = mysqli_fetch_array( $aguardando ) ) {
			$expiracao += $data['duracao_dias'];
		}

		// Uma funcionalidade vale se qualquer assinatura válida a incluir; o limite é o maior entre elas.
		$validas = "(used = '0' OR used = '1') AND status = '1' AND rel_estabelecimentos_id = '$eid'";
		$funcionalidade = array();
		foreach( array( "funcionalidade_marketplace","funcionalidade_variacao","funcionalidade_banners" ) as $campo ) {
			$funcionalidade[$campo] = mysqli_num_rows( mysqli_query( $db_con, "SELECT id FROM assinaturas WHERE $validas AND $campo = '1' LIMIT 1" ) ) ? "1" : "2";
		}
		$limite = 0;
		$limites = mysqli_query( $db_con, "SELECT limite_produtos FROM assinaturas WHERE $validas" );
		while( $data = mysqli_fetch_array( $limites ) ) {
			if( $data['limite_produtos'] > $limite ) {
				$limite = $data['limite_produtos'];
			}
		}

		$status = "1";

	} else {

		$status = "2";
		$funcionalidade = array( "funcionalidade_marketplace" => "2","funcionalidade_variacao" => "2","funcionalidade_banners" => "2" );
		$expiracao = "0";
		$limite = "";

	}

	mysqli_query( $db_con, "UPDATE estabelecimentos SET status = '$status',funcionalidade_marketplace = '".$funcionalidade['funcionalidade_marketplace']."',funcionalidade_variacao = '".$funcionalidade['funcionalidade_variacao']."',funcionalidade_banners = '".$funcionalidade['funcionalidade_banners']."',expiracao = '$expiracao',limite_produtos = '$limite' WHERE id = '$eid'" );

	// Se for a loja logada, a sessão acompanha. (Sem loja na sessão e sem $eid a comparação também
	// é verdadeira, como no sistema original.)
	$logada = isset( $_SESSION['estabelecimento']['id'] ) ? $_SESSION['estabelecimento']['id'] : null;
	if( $logada == $eid ) {
		$_SESSION['estabelecimento']['funcionalidade_marketplace'] = $funcionalidade['funcionalidade_marketplace'];
		$_SESSION['estabelecimento']['funcionalidade_variacao'] = $funcionalidade['funcionalidade_variacao'];
		$_SESSION['estabelecimento']['funcionalidade_banners'] = $funcionalidade['funcionalidade_banners'];
		$_SESSION['estabelecimento']['status'] = $status;
		$_SESSION['estabelecimento']['expiracao'] = $expiracao;
	}

}

// Sem ação no sistema original; a sincronização é feita por cron.php?acao=sync.
function sync_assinaturas() {

}

// Consulta no Mercado Pago o pedido da referência. Devolve array( gateway_ref, status ) ou false.
function consulta_pagamento( $gateway_ref ) {

	global $mp_acess_token;

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, "https://api.mercadopago.com/merchant_orders?access_token=".$mp_acess_token."&external_reference=".urlencode( $gateway_ref ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
	$retorno = json_decode( curl_exec( $ch ),true );
	curl_close( $ch );

	if( !isset( $retorno['elements'][0] ) ) {
		return false;
	}

	return array(
		"gateway_ref" => $retorno['elements'][0]['external_reference'],
		"status" => $retorno['elements'][0]['order_status'],
	);

}

// ---------------------------------------------------------------------
// Sacola (carrinho da vitrine), guardada na sessão:
//   $_SESSION['sacola'][<loja>][<linha>] = id do produto, quantidade, observacoes,
//   variacoes (itens escolhidos por variação, ex.: "0,2"), variacoes_texto, valor_adicional
// ---------------------------------------------------------------------

function sacola_criar( $eid ) {

	$_SESSION['sacola'][$eid] = "";
	return $eid;

}

function sacola_adicionar( $eid,$pid,$quantidade,$observacoes,$variacoes ) {

	$texto = "";
	$valor_adicional = 0;
	$variacao = data_variacoes( $pid );

	// Para cada variação escolhida: "*Nome*:  Item (+ R$ 0,00), Item."
	$total = is_array( $variacoes ) ? count( $variacoes ) : 0;
	for( $x = 0; $x < $total; $x++ ) {

		if( !isset( $variacoes[$x] ) || !notnull( $variacoes[$x] ) ) {
			continue;
		}

		$itens = "";
		foreach( explode( ",",$variacoes[$x] ) as $y ) {
			$item = isset( $variacao[$x]['item'][$y] ) ? $variacao[$x]['item'][$y] : array();
			$valor = data_variacao_texto( isset( $item['valor'] ) ? $item['valor'] : null );
			$itens .= " ".data_variacao_texto( isset( $item['nome'] ) ? $item['nome'] : null );
			if( $valor ) {
				$itens .= " (+ R$ ".dinheiro( $valor,"BR" ).")";
				$valor_adicional += $valor;
			}
			$itens .= ",";
		}

		$texto .= "*".data_variacao_texto( isset( $variacao[$x]['nome'] ) ? $variacao[$x]['nome'] : null )."*: ".rtrim( $itens,"," ).".\n";

	}

	if( $observacoes ) {
		$texto .= "*Observações:* ".$observacoes;
	}

	// Chave da linha: número livre de 1 a 9999.
	do {
		$linha = rand( 1,9999 );
	} while( isset( $_SESSION['sacola'][$eid][$linha] ) );

	if( !isset( $_SESSION['sacola'][$eid] ) || !is_array( $_SESSION['sacola'][$eid] ) ) {
		$_SESSION['sacola'][$eid] = array();
	}

	$_SESSION['sacola'][$eid][$linha] = array(
		"id" => $pid,
		"quantidade" => $quantidade,
		"observacoes" => $observacoes,
		"variacoes" => $variacoes,
		"variacoes_texto" => $texto,
		"valor_adicional" => $valor_adicional,
	);

}

function sacola_alterar( $eid,$pid,$quantidade ) {

	if( !isset( $_SESSION['sacola'][$eid] ) || !is_array( $_SESSION['sacola'][$eid] ) ) {
		$_SESSION['sacola'][$eid] = array();
	}

	$_SESSION['sacola'][$eid][$pid]['quantidade'] = $quantidade;

}

function sacola_remover( $eid,$pid ) {

	unset( $_SESSION['sacola'][$eid][$pid] );

}

// Guarda na sessão os dados informados no checkout.
function checkout_salvar( $nome,$whatsapp,$forma_entrega,$estado,$cidade,$endereco_cep,$endereco_numero,$endereco_bairro,$endereco_rua,$endereco_complemento,$endereco_referencia,$forma_pagamento,$forma_pagamento_informacao,$cupom,$mesa ) {

	$_SESSION['checkout']['nome'] = $nome;
	$_SESSION['checkout']['whatsapp'] = $whatsapp;
	$_SESSION['checkout']['forma_entrega'] = $forma_entrega;
	// Estado e cidade só entram quando são ids (numéricos).
	if( is_numeric( $estado ) ) {
		$_SESSION['checkout']['estado'] = $estado;
	}
	if( is_numeric( $cidade ) ) {
		$_SESSION['checkout']['cidade'] = $cidade;
	}
	$_SESSION['checkout']['endereco_cep'] = $endereco_cep;
	$_SESSION['checkout']['endereco_numero'] = $endereco_numero;
	$_SESSION['checkout']['endereco_bairro'] = $endereco_bairro;
	$_SESSION['checkout']['endereco_rua'] = $endereco_rua;
	$_SESSION['checkout']['endereco_complemento'] = $endereco_complemento;
	$_SESSION['checkout']['endereco_referencia'] = $endereco_referencia;
	$_SESSION['checkout']['forma_pagamento'] = $forma_pagamento;
	$_SESSION['checkout']['forma_pagamento_informacao'] = $forma_pagamento_informacao;
	$_SESSION['checkout']['cupom'] = $cupom;
	$_SESSION['checkout']['numero_mesa'] = $mesa;

}

// ---------------------------------------------------------------------
// Pedidos
// ---------------------------------------------------------------------

// Comprovante do pedido montado a partir da sacola e do checkout da sessão.
// $modo "texto" (negrito no padrão do WhatsApp: *assim*) ou "html"; $tamanho "1" inclui os dados do cliente.
function gera_comprovante( $eid,$modo,$tamanho,$numero ) {

	global $db_con;
	global $simple_url;

	$linha = "------------------------------------\n";
	$checkout = isset( $_SESSION['checkout'] ) && is_array( $_SESSION['checkout'] ) ? $_SESSION['checkout'] : array();
	$ck = function( $campo ) use ( $checkout ) {
		return isset( $checkout[$campo] ) ? $checkout[$campo] : "";
	};

	$c = "*".strtoupper( data_info( "estabelecimentos",$eid,"nome" ) )."*\n";
	$c .= data_info( "estabelecimentos",$eid,"endereco_rua" )." ".data_info( "estabelecimentos",$eid,"endereco_numero" ).", ".data_info( "estabelecimentos",$eid,"endereco_bairro" )."\n";
	$whatsapp = clean_str( data_info( "estabelecimentos",$eid,"contato_whatsapp" ) );
	$c .= ( $whatsapp ? mask( $whatsapp,"(##) #####-####" ) : "" )."\n";
	$c .= $linha;
	$c .= "*Pedido ".$numero."*\n";
	$c .= date("d/m/Y")." às ".date("H:i")."\n";
	$c .= $linha;
	$c .= "*PRODUTOS* \n";

	$subtotal = 0;
	$sacola = isset( $_SESSION['sacola'][$eid] ) && is_array( $_SESSION['sacola'][$eid] ) ? $_SESSION['sacola'][$eid] : array();

	foreach( $sacola as $item ) {

		$produto = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM produtos WHERE id = '".$item['id']."' AND status = '1' ORDER BY id ASC LIMIT 1" ) );
		$valor = $produto['oferta'] == "1" ? $produto['valor_promocional'] : $produto['valor'];
		$valor = ( $valor + $item['valor_adicional'] ) * $item['quantidade'];
		$subtotal += $valor;

		$c .= ".......................................\n";
		$c .= "*".$item['quantidade']."x* ".$produto['nome']."\n";
		if( $item['variacoes_texto'] ) {
			$c .= rtrim( html_entity_decode( $item['variacoes_texto'] ) )."\n";
		}
		$c .= "*Valor:* R$ ".dinheiro( $valor,"BR" )."\n";

	}

	$c .= $linha;
	$c .= "*Subtotal:* R$ ".dinheiro( $subtotal,"BR" )."\n";

	// Cupom: tipo 1 = percentual (com teto opcional em valor_maximo), tipo 2 = valor fixo.
	$total = $subtotal;
	$cupom = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM cupons WHERE codigo = '".$ck( "cupom" )."' AND rel_estabelecimentos_id = '$eid' LIMIT 1" ) );
	if( $cupom && $cupom['quantidade'] > 0 && strtotime( $cupom['validade'] ) > time() ) {
		if( $cupom['tipo'] == "1" ) {
			$desconto = $subtotal * $cupom['desconto_porcentagem'] / 100;
			if( $cupom['valor_maximo'] > 0 && $desconto > $cupom['valor_maximo'] ) {
				$desconto = $cupom['valor_maximo'];
			}
		} else {
			$desconto = $cupom['desconto_fixo'];
		}
		$total -= $desconto;
		$c .= "*Cupom:* ".$ck( "cupom" )." (- R$".dinheiro( $desconto,"BR" ).")\n";
	}

	// Entrega: "SEDEX04014" / "PAC04510" vêm do cálculo dos Correios guardado na sessão.
	data_info( "estabelecimentos",$eid,"calcularfrete" );
	$correios = array( "SEDEX04014" => array( "SEDEX","04014" ),"PAC04510" => array( "PAC","04510" ) );
	$entrega = "";
	if( isset( $correios[$ck( "forma_entrega" )] ) ) {
		list( $entrega,$servico ) = $correios[$ck( "forma_entrega" )];
		if( !empty( $_SESSION['sacola']['frete'][$servico]['valor'] ) ) {
			$frete = $_SESSION['sacola']['frete'][$servico]['valor'];
			$total += $frete;
			$entrega .= " (+ R$".dinheiro( $frete,"BR" ).")";
		}
	}

	$c .= "*Entrega:* ".$entrega."\n";
	$c .= "*Total:* R$ ".dinheiro( $total,"BR" )."\n";
	$c .= $linha;

	$formas = array(
		"1" => "*Dinheiro:* R$",
		"2" => "Cartão de débito - Bandeira: ",
		"3" => "Cartão de crédito - Bandeira: ",
		"4" => "Ticket alimentação - Bandeira: ",
		"5" => "Outros - Forma: ",
		"6" => "PIX",
		"7" => "Mercado Pago",
		"8" => "PagSeguro",
		"9" => "Getnet",
		"10" => "PIX PAGO ONLINE",
	);
	$forma = $ck( "forma_pagamento" );
	$c .= "*Forma de pagamento:* \n";
	$c .= ( isset( $formas[$forma] ) ? $formas[$forma] : "" ).$ck( "forma_pagamento_informacao" )." \n";
	$c .= $linha;

	if( $tamanho == "1" ) {

		$c .= "*Nome:* \n".$ck( "nome" )." \n";
		$c .= "*Whatsapp:* \n".$ck( "whatsapp" )." \n";
		$c .= $linha;
		$c .= "*Endereços:* \n";
		if( $ck( "endereco_rua" ) ) {
			$c .= " *Rua:* ".$ck( "endereco_rua" ).", ";
		}
		if( $ck( "endereco_numero" ) ) {
			$c .= " *Nº:* ".$ck( "endereco_numero" ).", \n";
		}
		if( $ck( "endereco_complemento" ) ) {
			$c .= " *Complemento:* ".$ck( "endereco_complemento" ).", \n";
		}
		if( $ck( "endereco_referencia" ) ) {
			$c .= " *Referência:* ".$ck( "endereco_referencia" );
		}
		if( $ck( "endereco_bairro" ) ) {
			$c .= " *Bairro:* ".$ck( "endereco_bairro" ).", \n";
		}
		if( $ck( "cidade" ) ) {
			$c .= " Cidade: ".data_info( "cidades",$ck( "cidade" ),"nome" )."/".data_info( "estados",$ck( "estado" ),"nome" ).", \n";
		}
		if( $ck( "endereco_cep" ) ) {
			$c .= "*CEP:* ".$ck( "endereco_cep" ).", \n";
		}
		$c .= "*Bairro:* ".$entrega."\n\n";
		$c .= $linha;

	}

	$c .= "https://".$simple_url."\n";

	if( $modo == "texto" ) {
		return $c;
	}

	if( $modo == "html" ) {
		return nl2br( bbzap( $c ) );
	}

}

// Link do WhatsApp com o comprovante do pedido. Sem $modo vai para a loja; com $modo o destinatário fica em aberto.
function whatsapp_link( $pedido,$modo = "" ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT * FROM pedidos WHERE id = '$pedido' LIMIT 1" );
	$data = $query ? mysqli_fetch_array( $query ) : null;

	$numero = "55".clean_str( data_info( "estabelecimentos",$data['rel_estabelecimentos_id'],"contato_whatsapp" ) );
	if( notnull( $modo ) ) {
		$numero = "";
	}

	return "https://wa.me/".$numero."?text=".urlencode( $data['comprovante'] );

}

// Cria a preferência de pagamento do pedido no Mercado Pago da loja.
function geralinkpagamento( $vpedido,$pedido,$estabelecimento,$access_token,$reference,$url ) {

	require_once( __DIR__."/mercadopago/vendor/autoload.php" );

	MercadoPago\SDK::setAccessToken( $access_token );

	$item = new MercadoPago\Item();
	$item->title = $estabelecimento." - PAGAMENTO DO PEDIDO: ".$pedido;
	$item->quantity = 1;
	$item->unit_price = (float) $vpedido;

	$preference = new MercadoPago\Preference();
	$preference->items = array( $item );
	$preference->save();

	$preference->back_urls = array(
		"success" => $url."/pedidosabertos",
		"failure" => $url."/pedidosabertos",
		"pending" => $url."/pedidosabertos",
	);
	$preference->notification_url = $url."/confirmapagamento.php";
	$preference->external_reference = $reference;
	$preference->save();

}

function new_pedido( $token,$rel_segmentos_id,$rel_estabelecimentos_id,$nome,$whatsapp,$forma_entrega,$estado,$cidade,$endereco_cep,$endereco_numero,$endereco_bairro,$endereco_rua,$endereco_complemento,$endereco_referencia,$forma_pagamento,$forma_pagamento_informacao,$data_hora,$cupom,$vpedido,$taxa,$mesa ) {

	global $db_con;

	$eid = $rel_estabelecimentos_id;

	if( !mysqli_query( $db_con, "INSERT INTO pedidos (rel_segmentos_id,rel_estabelecimentos_id,nome,whatsapp,forma_entrega,estado,cidade,endereco_cep,endereco_numero,endereco_bairro,endereco_rua,endereco_complemento,endereco_referencia,forma_pagamento,forma_pagamento_informacao,status,data_hora,cupom,v_pedido,taxa,mesa,linkpagamento,referencia,statuspagamento) VALUES ('$rel_segmentos_id','$eid','$nome','$whatsapp','$forma_entrega','$estado','$cidade','$endereco_cep','$endereco_numero','$endereco_bairro','$endereco_rua','$endereco_complemento','$endereco_referencia','$forma_pagamento','$forma_pagamento_informacao','1','$data_hora','$cupom','$vpedido','$taxa','$mesa','','','')" ) ) {
		return false;
	}

	$id = mysqli_insert_id( $db_con );

	$comprovante = gera_comprovante( $eid,"texto","1",$id );

	// Cliente da loja (identificado pelo WhatsApp) e histórico de pontos por pedido.
	// A inscrição de notificações push do navegador fica na sessão (save-subscription.php).
	$p256dh = isset( $_SESSION['p256dh'] ) ? $_SESSION['p256dh'] : "";
	$auth = isset( $_SESSION['auth'] ) ? $_SESSION['auth'] : "";
	$endpoint = isset( $_SESSION['endpoint'] ) ? $_SESSION['endpoint'] : "";
	$operacao = array( "pedido" => $id,"pontosadicionar" => 0,"pontosabater" => 0 );
	$cliente = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM clientes WHERE whatsapp = '$whatsapp' AND id_estabelecimento = '$eid'" ) );
	if( $cliente ) {
		$operacoes = json_decode( $cliente['pontos_op'],true );
		if( !is_array( $operacoes ) ) {
			$operacoes = array();
		}
		$operacoes[] = $operacao;
		mysqli_query( $db_con, "UPDATE clientes SET qtdpontos = '',pontos_op = '".json_encode( $operacoes )."',qtdpedidos = qtdpedidos+1,p256dh = '$p256dh',auth = '$auth',endpoint = '$endpoint' WHERE whatsapp = '$whatsapp' AND id_estabelecimento = '$eid'" );
	} else {
		mysqli_query( $db_con, "INSERT INTO clientes (id_estabelecimento,nome,datadeinclusao,whatsapp,qtdpontos,pontos_op,ativo,p256dh,auth,endpoint) values ('$eid','$nome',now(),'$whatsapp','','".json_encode( array( $operacao ) )."',1,'$p256dh','$auth','$endpoint')" );
	}

	mysqli_query( $db_con, "UPDATE pedidos SET comprovante = '$comprovante' WHERE id = '$id'" );

	// Cupom usado: uma unidade a menos.
	$cupom_data = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM cupons WHERE codigo = '$cupom' AND rel_estabelecimentos_id = '$eid' LIMIT 1" ) );
	if( $cupom_data ) {
		mysqli_query( $db_con, "UPDATE cupons SET quantidade = '".( $cupom_data['quantidade'] - 1 )."' WHERE codigo = '$cupom' AND rel_estabelecimentos_id = '$eid'" );
	}

	log_register( "","","O cliente ".$nome." efetuou o pedido ".$id." às ".date("d/m/Y")." às ".date("H:i")." Hrs" );

	// Guarda o último endereço no cadastro do cliente. As páginas passam a cidade no parâmetro $estado.
	$uf = data_info( "cidades",$estado,"estado" );
	$existe = mysqli_query( $db_con, "SELECT * FROM clientes WHERE whatsapp = $whatsapp and id_estabelecimento = $eid" );
	if( $existe && mysqli_num_rows( $existe ) ) {
		mysqli_query( $db_con, "UPDATE clientes SET cep = '$endereco_cep',rua = '$endereco_rua',numero = '$endereco_numero',bairro = '$endereco_bairro',cidade = '$estado',uf = '$uf',complemento = '$endereco_complemento',referencia = '$endereco_referencia' WHERE whatsapp = $whatsapp AND id_estabelecimento = $eid" );
	} else {
		// Sem cadastro (em MySQL com modo estrito o cadastro acima falha por causa de qtdpontos vazio): cria só com o endereço.
		mysqli_query( $db_con, "INSERT INTO clientes (id_estabelecimento,nome,datadeinclusao,whatsapp,ativo,cep,rua,numero,bairro,cidade,uf,complemento,referencia) VALUES ('$eid','$nome',now(),'$whatsapp',1,'$endereco_cep','$endereco_rua','$endereco_numero','$endereco_bairro','$estado','$uf','$endereco_complemento','$endereco_referencia')" );
	}

	return $id;

}

// Muda o status do pedido (painel da loja) e avisa o cliente por notificação push.
function edit_pedido( $id,$status ) {

	global $db_con;
	global $simple_url;

	if( !mysqli_query( $db_con, "UPDATE pedidos SET status = '$status' WHERE id = '$id'" ) ) {
		return false;
	}

	// enviaNotificacao() fica em push.php, na raiz do site (três níveis acima das páginas do painel).
	include_once( "../../../push.php" );

	$eid = data_info( "pedidos",$id,"rel_estabelecimentos_id" );
	$subdominio = data_info( "estabelecimentos",$eid,"subdominio" );
	$loja = data_info( "estabelecimentos",$eid,"nome" );
	$pedido = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM pedidos WHERE id = '$id'" ) );
	$cliente = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM clientes WHERE id_estabelecimento = '$eid' AND whatsapp = '".$pedido['whatsapp']."'" ) );

	$mensagens = array(
		"4" => "🆗 Seu pedido foi aceito.",
		"5" => "🛵 Seu pedido está a caminho.",
		"6" => "🆗 Seu pedido está pronto para retirada.",
		"2" => "🆗 Seu pedido foi concluído.Obrigado e volte sempre!.",
		"3" => "😢 Seu pedido foi cancelado. ",
	);

	if( isset( $mensagens[$status] ) ) {
		enviaNotificacao( $loja,$mensagens[$status],"https://".$subdominio.".".$simple_url."/pedidosabertos",$cliente['p256dh'],$cliente['auth'],$cliente['endpoint'] );
	}

	data_log( "mudou o status do pedido #".$id." para ".numeric_data( "status_pedido",$status ),false );

	return true;

}

// Desfaz no cadastro do cliente os pontos lançados por um pedido.
function alterapontos( $db_con,$id,$soma ) {

	$whatsapp = data_info( "pedidos",$id,"whatsapp" );
	$eid = data_info( "pedidos",$id,"rel_estabelecimentos_id" );

	$pontos = 0;
	$cliente = mysqli_fetch_array( mysqli_query( $db_con, "SELECT * FROM clientes WHERE id_estabelecimento = '$eid' AND whatsapp = '$whatsapp'" ) );

	if( $cliente ) {

		$restantes = array();
		$operacoes = json_decode( $cliente['pontos_op'],true );
		foreach( is_array( $operacoes ) ? $operacoes : array() as $operacao ) {
			if( $operacao['pedido'] == $id ) {
				$pontos += $operacao['pontosabater'] - $operacao['pontosadicionar'];
			} else {
				$restantes[] = $operacao;
			}
		}

		mysqli_query( $db_con, "UPDATE clientes SET qtdpontos = qtdpontos+$pontos,qtdpedidos = qtdpedidos-1,pontos_op = '".json_encode( $restantes )."' WHERE id_estabelecimento = '$eid' AND whatsapp = '$whatsapp'" );

	}

	return $pontos;

}

// Muda o status e a loja do pedido (administração).
function edit_pedido_admin( $id,$status,$estabelecimento ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE pedidos SET status = '$status',rel_estabelecimentos_id = '$estabelecimento' WHERE id = '$id'" ) ) {

		data_log( "mudou o status do pedido #".$id." para ".numeric_data( "status_pedido",$status ),false );
		return true;

	} else {

		return false;

	}

}

function delete_pedido( $id ) {

	global $db_con;

	// O sistema original busca o nome na tabela de cidades e registra "removeu a cidade"; mantido.
	$nome = data_info( "cidades",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM pedidos WHERE id = '$id'" ) ) {

		data_log( "removeu a cidade ".$nome,false );
		return true;

	} else {

		return false;

	}

}

?>

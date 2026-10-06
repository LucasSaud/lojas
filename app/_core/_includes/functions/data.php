<?php

// =====================================================================
// data.php - cadastros do sistema (estados, cidades, segmentos, subdomínios,
// categorias, banners, usuários, produtos, estabelecimentos) e e-mails.
//
// Os argumentos chegam já escapados pelos chamadores
// (mysqli_real_escape_string nas páginas); as funções não escapam de novo.
// =====================================================================

global $numeric_data;

$numeric_data = array(
	"usuario_nivel" => array(
		array("value" => "1", "name" => "Administrador"),
		array("value" => "2", "name" => "Estabelecimento"),
		array("value" => "3", "name" => "Afiliado"),
	),
	"status" => array(
		array("value" => "1", "name" => "Ativo"),
		array("value" => "2", "name" => "Inativo"),
	),
	"level" => array(
		array("value" => "1", "name" => "Adm"),
		array("value" => "2", "name" => "Loja"),
		array("value" => "3", "name" => "Afiliado"),
	),
	"status_pedido" => array(
		array("value" => "1", "name" => "Pendente"),
		array("value" => "4", "name" => "Aceito"),
		array("value" => "5", "name" => "Saiu para Entrega"),
		array("value" => "6", "name" => "Disponível para Retirada"),
		array("value" => "2", "name" => "Concluído"),
		array("value" => "3", "name" => "Cancelado"),
		array("value" => "7", "name" => "Reembolsado"),
		array("value" => "8", "name" => "Pago"),
	),
	"tipo_pagamento" => array(
		array("value" => "1", "name" => "Dinheiro"),
		array("value" => "2", "name" => "Cartão de débito"),
		array("value" => "3", "name" => "Cartão de crédito"),
		array("value" => "4", "name" => "Ticket alimentação"),
		array("value" => "5", "name" => "Outros"),
		array("value" => "6", "name" => "PIX"),
		array("value" => "7", "name" => "Mercado Pago"),
		array("value" => "9", "name" => "Getnet"),
		array("value" => "10", "name" => "Pix Online"),
	),
	"documento_tipo" => array(
		array("value" => "1", "name" => "CPF"),
		array("value" => "2", "name" => "CNPJ"),
	),
	"usuario_tipo" => array(
		array("value" => "1", "name" => "ADMINISTRADOR MASTER"),
		array("value" => "3", "name" => "AFILIADO"),
	),
	"subdominio_tipo" => array(
		array("value" => "1", "name" => "Estabelecimento"),
		array("value" => "2", "name" => "Cidade"),
		array("value" => "5", "name" => "Blacklist"),
	),
	"status_venda" => array(
		array("value" => "Aguardando pagamento", "name" => "1"),
		array("value" => "Finalizada", "name" => "2"),
		array("value" => "Cancelada", "name" => "3"),
		array("value" => "Devolvida", "name" => "4"),
		array("value" => "Bloqueada", "name" => "5"),
		array("value" => "Completa", "name" => "6"),
	),
	"assinatura_status" => array(
		array("value" => "0", "name" => "Aguardando pagamento"),
		array("value" => "1", "name" => "Disponível"),
		array("value" => "3", "name" => "Cancelada"),
	),
	"assinatura_use" => array(
		array("value" => "0", "name" => "Aguardando uso"),
		array("value" => "1", "name" => "Em uso"),
		array("value" => "3", "name" => "Expirada"),
	),
	"status_voucher" => array(
		array("value" => "1", "name" => "Disponível"),
		array("value" => "2", "name" => "Resgatado"),
	),
	"banner_acao" => array(
		array("value" => "1", "name" => "Categoria"),
		array("value" => "2", "name" => "Produto"),
		array("value" => "3", "name" => "Link"),
	),
	"visibilidade" => array(
		array("value" => "1", "name" => "Visível"),
		array("value" => "2", "name" => "Invisível"),
	),
	"agendamento_dias" => array(
		array("value" => "sun", "name" => "Domingo"),
		array("value" => "mon", "name" => "Segunda-feira"),
		array("value" => "tue", "name" => "Terça-feira"),
		array("value" => "wed", "name" => "Quarta-feira"),
		array("value" => "thu", "name" => "Quinta-feira"),
		array("value" => "fri", "name" => "Sexta-feira"),
		array("value" => "sat", "name" => "Sábado"),
	),
	"agendamento_dias_numericos" => array(
		array("value" => "0", "name" => "sun"),
		array("value" => "1", "name" => "mon"),
		array("value" => "2", "name" => "tue"),
		array("value" => "3", "name" => "wed"),
		array("value" => "4", "name" => "thu"),
		array("value" => "5", "name" => "fri"),
		array("value" => "6", "name" => "sat"),
	),
	"agendamento_acao" => array(
		array("value" => "1", "name" => "Abrir loja"),
		array("value" => "2", "name" => "Fechar loja"),
	),
);

// Retorna o valor do campo $info do registro $id da tabela $table.
function data_info( $table, $id, $info ) {

	global $db_con;

	$query = mysqli_query( $db_con, "SELECT $info FROM $table WHERE id = '$id' LIMIT 1" );

	if( $query && mysqli_num_rows( $query ) ) {
		$data = mysqli_fetch_array( $query );
		return $data[$info];
	}

	return null;

}

// Retorna o "name" de um item da categoria $type cujo "value" == $value.
function numeric_data( $type, $value ) {

	global $numeric_data;

	if( !isset( $numeric_data[$type] ) || !is_array( $numeric_data[$type] ) ) {
		return null;
	}

	foreach( $numeric_data[$type] as $item ) {
		if( $item['value'] == $value ) {
			return $item['name'];
		}
	}

	return null;

}

// Busca $value num array numerico.
// - itens array(value,name): compara (loose) "value" e retorna "name"
// - itens string: compara o primeiro caractere contra $value e o retorna
// - itens numericos (int/float): ignorados
// Parametro $return existe na assinatura original mas e ignorado.
function numeric_find( $value, $array, $return ) {

	foreach( $array as $item ) {

		if( is_array( $item ) ) {
			if( isset( $item['value'] ) && $item['value'] == $value ) {
				return isset( $item['name'] ) ? $item['name'] : null;
			}
		} elseif( isset( $item[0] ) && $item[0] == $value ) {
			return $item[0];
		}

	}

	return null;

}

// Registra no log de auditoria: "<quem> <ação> às <data> às <hora> Hrs".
// Com $afiliado = false o nível 3 fica sem o prefixo "Afiliado", como em parte das funções do sistema original.
function data_log( $acao,$afiliado = true ) {

	$niveis = array( "1" => "O Administrador", "2" => "A Loja", "3" => $afiliado ? "Afiliado" : "" );

	$uid = isset( $_SESSION['user']['id'] ) ? $_SESSION['user']['id'] : "";
	$nome = isset( $_SESSION['user']['nome'] ) ? $_SESSION['user']['nome'] : "";
	$level = isset( $_SESSION['user']['level'] ) ? $_SESSION['user']['level'] : "";
	$quem = isset( $niveis[$level] ) ? $niveis[$level] : "";

	log_register( $uid, "", $quem." ".$nome." ".$acao." às ".date("d/m/Y")." às ".date("H:i")." Hrs" );

}

// Estados

function new_estado( $nome ) {

	global $db_con;

	if( mysqli_query( $db_con, "INSERT INTO estados (nome) VALUES ('$nome')" ) ) {

		data_log( "cadastrou o estado ".$nome );
		return true;

	} else {

		return false;

	}

}

function edit_estado( $id,$nome ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE estados SET nome = '$nome' WHERE id = '$id'" ) ) {

		data_log( "editou o estado ".$nome );
		return true;

	} else {

		return false;

	}

}

function delete_estado( $id ) {

	global $db_con;

	$nome = data_info( "estados",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM estados WHERE id = '$id'" ) ) {

		data_log( "removeu o estado ".$nome );
		return true;

	} else {

		return false;

	}

}

// Cidades

function new_cidade( $estado,$nome ) {

	global $db_con;

	$subdominio = slugify( $nome ).strtolower( data_info( "estados",$estado,"uf" ) );

	if( mysqli_query( $db_con, "INSERT INTO cidades (estado,nome,subdominio) VALUES ('$estado','$nome','$subdominio')" ) ) {

		data_log( "cadastrou a cidade ".$nome." (".data_info( "estados",$estado,"nome" ).")" );
		return true;

	} else {

		return false;

	}

}

function edit_cidade( $id,$estado,$nome ) {

	global $db_con;

	$subdominio = slugify( $nome ).strtolower( data_info( "estados",$estado,"uf" ) );

	if( mysqli_query( $db_con, "UPDATE cidades SET estado = '$estado', nome = '$nome', subdominio = '$subdominio' WHERE id = '$id'" ) ) {

		data_log( "editou a cidade ".$nome." (".data_info( "estados",$estado,"nome" ).")" );
		return true;

	} else {

		return false;

	}

}

function delete_cidade( $id ) {

	global $db_con;

	$nome = data_info( "cidades",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM cidades WHERE id = '$id'" ) ) {

		data_log( "removeu a cidade ".$nome );
		return true;

	} else {

		return false;

	}

}

// Segmentos

function new_segmento( $nome,$icone,$censura ) {

	global $db_con;

	if( mysqli_query( $db_con, "INSERT INTO segmentos (nome,icone,censura) VALUES ('$nome','$icone','$censura')" ) ) {

		data_log( "cadastrou o segmento ".$nome );
		return true;

	} else {

		return false;

	}

}

function edit_segmento( $id,$nome,$icone,$censura ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE segmentos SET nome = '$nome',censura = '$censura' WHERE id = '$id'" ) ) {

		// O ícone só é trocado quando um novo foi enviado. O arquivo antigo fica no disco.
		if( $icone ) {
			data_info( "segmentos",$id,"icone" );
			mysqli_query( $db_con, "UPDATE segmentos SET icone = '$icone' WHERE id = '$id'" );
		}

		data_log( "editou o segmento ".$nome );
		return true;

	} else {

		return false;

	}

}

function delete_segmento( $id ) {

	global $db_con;

	$nome = data_info( "segmentos",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM segmentos WHERE id = '$id'" ) ) {

		data_log( "removeu o segmento ".$nome );
		return true;

	} else {

		return false;

	}

}

// Subdomínios

function new_subdominio( $subdominio,$tipo,$rel_id,$url ) {

	global $db_con;
	global $simple_url;

	if( mysqli_query( $db_con, "INSERT INTO subdominios (subdominio,tipo,rel_id,url) VALUES ('$subdominio','$tipo','$rel_id','$url')" ) ) {

		data_log( "cadastrou o subdominio ".$subdominio.".".$simple_url );
		return true;

	} else {

		return false;

	}

}

function edit_subdominio( $id,$subdominio,$tipo,$rel_id,$url ) {

	global $db_con;
	global $simple_url;

	$anterior = data_info( "subdominios",$id,"subdominio" );

	if( mysqli_query( $db_con, "UPDATE subdominios SET subdominio = '$subdominio',tipo = '$tipo',rel_id = '$rel_id',url = '$url' WHERE id = '$id'" ) ) {

		data_log( "editou o subdominio ".$anterior.".".$simple_url." para ".$subdominio.".".$simple_url );
		return true;

	} else {

		return false;

	}

}

function delete_subdominio( $id ) {

	global $db_con;
	global $simple_url;

	$subdominio = data_info( "subdominios",$id,"subdominio" );

	if( mysqli_query( $db_con, "DELETE FROM subdominios WHERE id = '$id'" ) ) {

		data_log( "removeu o subdominio ".$subdominio.".".$simple_url );
		return true;

	} else {

		return false;

	}

}

// Categorias

function new_categoria( $estabelecimento,$nome,$ordem,$visible,$status ) {

	global $db_con;

	if( mysqli_query( $db_con, "INSERT INTO categorias (rel_estabelecimentos_id,nome,ordem,visible,status) VALUES ('$estabelecimento','$nome','$ordem','$visible','$status')" ) ) {

		data_log( "cadastrou a categoria ".$nome );
		return true;

	} else {

		return false;

	}

}

function edit_categoria( $id,$estabelecimento,$nome,$ordem,$visible,$status ) {

	global $db_con;

	if( mysqli_query( $db_con, "UPDATE categorias SET rel_estabelecimentos_id = '$estabelecimento', nome = '$nome', ordem = '$ordem', visible = '$visible',status = '$status' WHERE id = '$id'" ) ) {

		data_log( "editou a categoria ".$nome );
		return true;

	} else {

		return false;

	}

}

function delete_categoria( $id ) {

	global $db_con;

	$nome = data_info( "categorias",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM categorias WHERE id = '$id'" ) ) {

		data_log( "removeu a categoria ".$nome );
		return true;

	} else {

		return false;

	}

}

// Banners (da loja e do marketplace)

// Devolve a imagem a gravar: a nova, apagando a antiga do disco, ou a antiga se nenhuma foi enviada.
function data_troca_imagem( $nova,$antiga ) {

	global $rootpath;

	if( $nova ) {
		@unlink( $rootpath."/_core/_uploads/".$antiga );
		return $nova;
	}

	return $antiga;

}

function new_banner( $estabelecimento,$titulo,$desktop,$mobile,$link,$status ) {

	global $db_con;

	$agora = date("Y-m-d H:i:s");

	if( mysqli_query( $db_con, "INSERT INTO banners (rel_estabelecimentos_id,titulo,desktop,mobile,link,status,created,last_modified) VALUES ('$estabelecimento','$titulo','$desktop','$mobile','$link','$status','$agora','$agora')" ) ) {

		$id = mysqli_insert_id( $db_con );
		data_log( "cadastrou o banner ".$titulo );
		return $id;

	} else {

		return false;

	}

}

function edit_banner( $id,$estabelecimento,$titulo,$desktop,$mobile,$link,$status ) {

	global $db_con;

	$desktop_antigo = data_info( "banners",$id,"desktop" );
	$mobile_antigo = data_info( "banners",$id,"mobile" );
	$desktop = data_troca_imagem( $desktop,$desktop_antigo );
	$mobile = data_troca_imagem( $mobile,$mobile_antigo );

	if( mysqli_query( $db_con, "UPDATE banners SET rel_estabelecimentos_id = '$estabelecimento',titulo = '$titulo',desktop = '$desktop',mobile = '$mobile',link = '$link',status = '$status' WHERE id = '$id'" ) ) {

		// Texto do log mantido como no sistema original.
		data_log( "editou a produto " );
		return true;

	} else {

		return false;

	}

}

function delete_banner( $id ) {

	global $db_con;

	$titulo = data_info( "banners",$id,"titulo" );
	data_info( "banners",$id,"desktop" );
	data_info( "banners",$id,"mobile" );

	if( mysqli_query( $db_con, "DELETE FROM banners WHERE id = '$id'" ) ) {

		data_log( "removeu o banner ".$titulo );
		return true;

	} else {

		return false;

	}

}

function new_banner_marketplace( $titulo,$desktop,$mobile,$link,$status ) {

	global $db_con;

	$agora = date("Y-m-d H:i:s");

	if( mysqli_query( $db_con, "INSERT INTO banners_marketplace (rel_estabelecimentos_id,titulo,desktop,mobile,link,status,created,last_modified) VALUES ('1','$titulo','$desktop','$mobile','$link','$status','$agora','$agora')" ) ) {

		$id = mysqli_insert_id( $db_con );
		data_log( "cadastrou o banner ".$titulo );
		return $id;

	} else {

		return false;

	}

}

function edit_banner_marketplace( $id,$estabelecimento,$titulo,$desktop,$mobile,$link,$status ) {

	global $db_con;

	$desktop_antigo = data_info( "banners_marketplace",$id,"desktop" );
	$mobile_antigo = data_info( "banners_marketplace",$id,"mobile" );
	$desktop = data_troca_imagem( $desktop,$desktop_antigo );
	$mobile = data_troca_imagem( $mobile,$mobile_antigo );

	if( mysqli_query( $db_con, "UPDATE banners_marketplace SET titulo = '$titulo',desktop = '$desktop',mobile = '$mobile',link = '$link',status = '$status' WHERE id = '$id'" ) ) {

		data_log( "editou a produto " );
		return true;

	} else {

		return false;

	}

}

function delete_banner_marketplace( $id ) {

	global $db_con;

	$titulo = data_info( "banners_marketplace",$id,"titulo" );
	data_info( "banners_marketplace",$id,"desktop" );
	data_info( "banners_marketplace",$id,"mobile" );

	if( mysqli_query( $db_con, "DELETE FROM banners_marketplace WHERE id = '$id'" ) ) {

		data_log( "removeu o banner ".$titulo );
		return true;

	} else {

		return false;

	}

}

// Usuários (administradores e afiliados)

function new_user( $level,$nome,$nascimento,$documento_tipo,$documento,$estado,$cidade,$telefone,$email,$pass ) {

	global $db_con;

	$password = md5( $pass );
	$agora = date("Y-m-d H:i:s");

	if( mysqli_query( $db_con, "INSERT INTO users (nome,email,password,level,status,created) VALUES ('$nome','$email','$password','$level','1','$agora')" ) ) {

		$id = mysqli_insert_id( $db_con );

		if( mysqli_query( $db_con, "INSERT INTO users_data (rel_users_id,nascimento,documento_tipo,documento,estado,cidade,telefone) VALUES ('$id','$nascimento','$documento_tipo','$documento','$estado','$cidade','$telefone')" ) ) {

			data_log( "cadastrou o usuário ".$nome." (".$email.")" );
			return true;

		}

	}

	return false;

}

function edit_user( $id,$level,$nome,$nascimento,$documento_tipo,$documento,$estado,$cidade,$telefone,$email,$pass ) {

	global $db_con;

	// A senha só é trocada quando informada.
	$senha = "";
	if( $pass ) {
		$senha = "password = '".md5( $pass )."',";
	}

	if( mysqli_query( $db_con, "UPDATE users SET nome = '$nome',email = '$email',".$senha."level='$level' WHERE id = '$id'" ) ) {

		if( !mysqli_num_rows( mysqli_query( $db_con, "SELECT * FROM users_data WHERE rel_users_id = '$id' LIMIT 1" ) ) ) {
			mysqli_query( $db_con, "INSERT INTO users_data (rel_users_id) VALUES ($id)" );
		}

		if( mysqli_query( $db_con, "UPDATE users_data SET nascimento = '$nascimento',documento_tipo = '$documento_tipo',documento = '$documento',estado = '$estado',cidade = '$cidade',telefone = '$telefone' WHERE rel_users_id = '$id'" ) ) {

			data_log( "editou o usuário ".$nome." (".$email.")" );
			return true;

		}

	}

	return false;

}

function delete_user( $id ) {

	global $db_con;

	if( mysqli_query( $db_con, "DELETE FROM users WHERE id = '$id'" ) ) {

		mysqli_query( $db_con, "DELETE FROM users_data WHERE rel_users_id = '$id'" );

		// O log original não traz nome nem e-mail do usuário removido.
		data_log( "removeu o usuário  ()" );
		return true;

	} else {

		return false;

	}

}

function delete_assinatura( $id ) {

	global $db_con;

	$nome = data_info( "assinaturas",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM assinaturas WHERE id = '$id'" ) ) {

		data_log( "removeu Assinatura ".$nome );
		return true;

	} else {

		return false;

	}

}

// Produtos

function new_produto( $estabelecimento,$categoria,$destaque,$estoque,$mostrar_estoque,$ref,$nome,$descricao,$valor,$oferta,$valor_promocional,$variacao,$status,$visible,$integrado ) {

	global $db_con;

	$agora = date("Y-m-d H:i:s");

	// Sem oferta, o valor promocional acompanha o valor cheio.
	if( $oferta == "2" ) {
		$valor_promocional = $valor;
	}

	if( mysqli_query( $db_con, "INSERT INTO produtos (rel_estabelecimentos_id,rel_categorias_id,destaque,estoque,mostrar_estoque,ref,nome,descricao,valor,oferta,valor_promocional,variacao,status,visible,created,last_modified,integrado) VALUES ('$estabelecimento','$categoria','$destaque','$estoque','$mostrar_estoque','$ref','$nome','$descricao','$valor','$oferta','$valor_promocional','$variacao','$status','$visible','$agora','$agora','$integrado')" ) ) {

		$id = mysqli_insert_id( $db_con );

		if( !$ref ) {
			mysqli_query( $db_con, "UPDATE produtos SET ref = 'REF-$id' WHERE id = '$id'" );
		}

		if( $categoria ) {
			mysqli_query( $db_con, "UPDATE categorias SET last_modified = '$agora' WHERE id = '$categoria'" );
		}

		data_log( "cadastrou o produto ".$nome );
		return $id;

	} else {

		return false;

	}

}

function edit_produto( $id,$estabelecimento,$categoria,$destaque,$estoque,$mostrar_estoque,$ref,$nome,$descricao,$valor,$oferta,$valor_promocional,$variacao,$status,$visible,$integrado,$pesofrete,$alturafrete,$largurafrete,$comprimentofrete,$diametrofrete ) {

	global $db_con;

	$agora = date("Y-m-d H:i:s");

	if( $oferta == "2" ) {
		$valor_promocional = $valor;
	}

	// Medidas de frete vão sem aspas; vazio vira NULL.
	if( $pesofrete == '' ) { $pesofrete = "NULL"; }
	if( $alturafrete == '' ) { $alturafrete = "NULL"; }
	if( $largurafrete == '' ) { $largurafrete = "NULL"; }
	if( $comprimentofrete == '' ) { $comprimentofrete = "NULL"; }
	if( $diametrofrete == '' ) { $diametrofrete = "NULL"; }

	if( mysqli_query( $db_con, "UPDATE produtos SET pesofrete=$pesofrete,alturafrete=$alturafrete,largurafrete=$largurafrete,comprimentofrete=$comprimentofrete,diametrofrete=$diametrofrete, rel_estabelecimentos_id = '$estabelecimento',rel_categorias_id = '$categoria',ref = '$ref',nome = '$nome',estoque = '$estoque',mostrar_estoque = '$mostrar_estoque',descricao = '$descricao',valor = '$valor',oferta = '$oferta',valor_promocional = '$valor_promocional',variacao = '$variacao',status = '$status',visible = '$visible',last_modified = '$agora',integrado = '$integrado' WHERE id = '$id'" ) ) {

		mysqli_query( $db_con, "UPDATE categorias SET last_modified = '$agora' WHERE id = '$categoria'" );

		if( !$ref ) {
			mysqli_query( $db_con, "UPDATE produtos SET ref = 'REF-$id' WHERE id = '$id'" );
		}

		// A foto destaque só é trocada quando uma nova foi enviada. O arquivo antigo fica no disco.
		if( $destaque ) {
			data_info( "produtos",$id,"destaque" );
			mysqli_query( $db_con, "UPDATE produtos SET destaque = '$destaque' WHERE id = '$id'" );
		}

		// Texto do log mantido como no sistema original.
		data_log( "editou a produto ".$nome );
		return true;

	} else {

		return false;

	}

}

function delete_produto( $id ) {

	global $db_con;
	global $rootpath;

	$nome = data_info( "produtos",$id,"nome" );

	if( mysqli_query( $db_con, "DELETE FROM produtos WHERE id = '$id'" ) ) {

		// Remove a galeria do produto (registros e arquivos). A foto destaque fica no disco.
		$midias = mysqli_query( $db_con, "SELECT * FROM midia WHERE rel_id = '$id' ORDER BY id DESC LIMIT 999" );
		while( $midia = mysqli_fetch_array( $midias ) ) {
			@unlink( $rootpath."/_core/_uploads/".$midia['url'] );
			mysqli_query( $db_con, "DELETE FROM midia WHERE id = '".$midia['id']."'" );
		}

		data_log( "removeu o produto ".$nome );
		return true;

	} else {

		return false;

	}

}

// Variações de produto. Ficam em produtos.variacao como JSON, com os textos em base64:
// [ { nome, escolha_minima, escolha_maxima, item: [ { nome, descricao, valor, estoque } ] } ]

function data_variacoes( $pid ) {

	$variacao = json_decode( data_info( "produtos",$pid,"variacao" ), TRUE );
	return is_array( $variacao ) ? $variacao : array();

}

// Lê um texto do JSON de variações já pronto para exibir.
function data_variacao_texto( $valor ) {

	return is_string( $valor ) ? htmljson( $valor ) : "";

}

// Total de itens somando todas as variações do produto (0 = produto sem variação).
function has_variacao( $pid ) {

	$total = 0;

	foreach( data_variacoes( $pid ) as $variacao ) {
		if( isset( $variacao['item'] ) && is_array( $variacao['item'] ) ) {
			$total += count( $variacao['item'] );
		}
	}

	return $total;

}

function variacao_info( $pid,$id,$info ) {

	$variacao = data_variacoes( $pid );
	return data_variacao_texto( isset( $variacao[$id][$info] ) ? $variacao[$id][$info] : null );

}

function variacao_item_info( $pid,$id,$item,$info ) {

	$variacao = data_variacoes( $pid );
	return data_variacao_texto( isset( $variacao[$id]['item'][$item][$info] ) ? $variacao[$id]['item'][$item][$info] : null );

}

// Diz se o item $compare da variação $id está escolhido na sacola (0 = não).
// As escolhas ficam na sessão como texto separado por vírgula, ex.: "0,2".
function variacao_opcao_ativa( $pid,$id,$compare ) {

	$eid = data_info( "produtos",$pid,"rel_estabelecimentos_id" );
	$ativa = 0;

	if( !isset( $_SESSION['sacola'][$eid][$pid]['variacoes'][$id] ) ) {
		return $ativa;
	}

	$escolhas = $_SESSION['sacola'][$eid][$pid]['variacoes'][$id];

	if( !is_scalar( $escolhas ) || !notnull( $escolhas ) ) {
		return $ativa;
	}

	if( $escolhas == $compare ) {
		$ativa++;
	}

	foreach( explode( ",",$escolhas ) as $escolha ) {
		if( $escolha == $compare ) {
			$ativa++;
		}
	}

	return $ativa;

}

// E-mails

// Envia um e-mail HTML pelo SMTP configurado em config.php ($smtp_user, $smtp_pass, $smtp_name).
// O servidor é "mail." + domínio da conta de envio, porta 587.
function html_mail( $to,$subject,$msg ) {

	global $smtp_name;
	global $smtp_user;
	global $smtp_pass;

	$dominio = explode( "@",$smtp_user,2 );

	$mail = new PHPMailer;
	$mail->isSMTP();
	$mail->CharSet = "UTF-8";
	$mail->Host = "mail.".( isset( $dominio[1] ) ? $dominio[1] : "" );
	$mail->Port = 587;
	$mail->Timeout = 60;
	$mail->SMTPAuth = true;
	$mail->Username = $smtp_user;
	$mail->Password = $smtp_pass;
	$mail->setFrom( $smtp_user,$smtp_name );
	$mail->addAddress( $to );
	$mail->isHTML( true );
	$mail->Subject = $subject;
	$mail->Body = $msg;

	return $mail->send();

}

function template_mail( $to,$subject,$msg ) {

	return html_mail( $to,$subject,$msg );

}

// Monta o e-mail no layout padrão (_core/_email/default.php), que lê $title e $msg e devolve $fullmsg.
function data_email_layout( $title,$msg ) {

	$fullmsg = "";
	include( __DIR__."/../../_email/default.php" );
	return $fullmsg;

}

function welcome_mail( $email,$pass ) {

	global $smtp_name;

	$login = get_just_url()."/login";

	$msg = "Bem vindo!<br/>Você já pode utilizar o seu sistema!:<br/><br/>Painel: <a style='color: ##004f2a;' href='".$login."'>".$login."</a><br/>Login: ".$email."<br/>Senha: ".$pass."<br/><br/>Atenciosamente<br/>Equipe ".$smtp_name."!";

	return html_mail( $email,"Bem vindo ao ".$smtp_name."!",data_email_layout( "Bem vindo!",$msg ) );

}

// Estabelecimentos (lojas)

// Cria o usuário lojista e a loja com os campos informados (coluna => valor),
// aplica o plano padrão ($plano_default) e envia o e-mail de boas-vindas.
function data_cria_estabelecimento( $campos,$email,$pass ) {

	global $db_con;
	global $simple_url;
	global $plano_default;

	$agora = date("Y-m-d H:i:s");
	$password = md5( $pass );

	if( !mysqli_query( $db_con, "INSERT INTO users (nome,email,password,level,status,created) VALUES ('".$campos['nome']."','$email','$password','2','1','$agora')" ) ) {
		return false;
	}

	$campos = array_merge( array( "rel_users_id" => mysqli_insert_id( $db_con ) ), $campos, array(
		"email" => $email,
		"created" => $agora,
		"last_modified" => $agora,
		"last_login" => $agora,
		"status" => "1",
		"status_force" => "2",
		"funcionamento" => "1",
		"excluded" => "2",
	) );

	if( !mysqli_query( $db_con, "INSERT INTO estabelecimentos (".implode( ",",array_keys( $campos ) ).") VALUES ('".implode( "','",$campos )."')" ) ) {
		return false;
	}

	aplicar_plano( mysqli_insert_id( $db_con ),$plano_default );
	welcome_mail( $email,$pass );

	data_log( "cadastrou o estabelecimento ".$campos['subdominio'].".".$simple_url );
	return true;

}

// Cadastro rápido feito pelo próprio lojista em /comece.
function new_simples( $afiliado,$nome,$descricao,$segmento,$estado,$cidade,$subdominio,$responsavel_nome,$responsavel_nascimento,$responsavel_documento_tipo,$responsavel_documento,$email,$pass ) {

	return data_cria_estabelecimento( compact(
		"afiliado","nome","descricao","segmento","estado","cidade","subdominio",
		"responsavel_nome","responsavel_nascimento","responsavel_documento_tipo","responsavel_documento"
	),$email,$pass );

}

// Cadastro completo feito pelo administrador ou pelo afiliado.
function new_estabelecimento( $afiliado,$nome,$descricao,$segmento,$estado,$cidade,$subdominio,$perfil,$capa,$cor,$pedido_minimo,$pagamento_dinheiro,$pagamento_cartao_debito,$pagamento_cartao_debito_bandeiras,$pagamento_cartao_credito,$pagamento_cartao_credito_bandeiras,$pagamento_cartao_alimentacao,$pagamento_cartao_alimentacao_bandeiras,$pagamento_outros,$pagamento_outros_descricao,$endereco_cep,$endereco_numero,$endereco_bairro,$endereco_rua,$endereco_complemento,$endereco_referencia,$horario_funcionamento,$entrega_retirada,$entrega_entrega,$entrega_entrega_tipo,$entrega_entrega_valor,$contato_whatsapp,$contato_email,$contato_instagram,$contato_facebook,$contato_youtube,$responsavel_nome,$responsavel_nascimento,$responsavel_documento_tipo,$responsavel_documento,$email,$pass ) {

	return data_cria_estabelecimento( compact(
		"afiliado","nome","descricao","segmento","estado","cidade","subdominio","perfil","capa","cor","pedido_minimo",
		"pagamento_dinheiro","pagamento_cartao_debito","pagamento_cartao_debito_bandeiras",
		"pagamento_cartao_credito","pagamento_cartao_credito_bandeiras",
		"pagamento_cartao_alimentacao","pagamento_cartao_alimentacao_bandeiras",
		"pagamento_outros","pagamento_outros_descricao",
		"endereco_cep","endereco_numero","endereco_bairro","endereco_rua","endereco_complemento","endereco_referencia",
		"horario_funcionamento","entrega_retirada","entrega_entrega","entrega_entrega_tipo","entrega_entrega_valor",
		"contato_whatsapp","contato_email","contato_instagram","contato_facebook","contato_youtube",
		"responsavel_nome","responsavel_nascimento","responsavel_documento_tipo","responsavel_documento"
	),$email,$pass );

}

function edit_estabelecimento( $accesstoken,$id,$nome,$descricao,$segmento,$estado,$cidade,$subdominio,$perfil,$capa,$cor,$exibicao,$pedido_minimo,$pagamento_dinheiro,$pagamento_cartao_debito,$pagamento_cartao_debito_bandeiras,$pagamento_cartao_credito,$pagamento_cartao_credito_bandeiras,$pagamento_mercadopago,$pagamento_mercadopago_sandbox,$pagamento_mercadopago_public,$pagamento_mercadopago_secret,$pagamento_getnet,$pagamento_getnet_sandbox,$pagamento_getnet_client_id,$pagamento_getnet_client_secret,$pagamento_getnet_seller_id,$pagamento_pix_mp,$pagamento_pix,$pagamento_pix_chave,$pagamento_pix_beneficiario,$endereco_cep,$endereco_numero,$endereco_bairro,$endereco_rua,$endereco_complemento,$endereco_referencia,$horario_funcionamento,$entrega_retirada,$entrega_entrega,$entrega_entrega_tipo,$entrega_entrega_valor,$entrega_delivery,$entrega_balcao,$entrega_mesa,$entrega_outros,$entrega_outros_nome,$contato_whatsapp,$contato_email,$contato_instagram,$contato_facebook,$contato_youtube,$estatisticas_analytics,$estatisticas_pixel,$html,$responsavel_nome,$responsavel_nascimento,$responsavel_documento_tipo,$responsavel_documento,$email,$pass,$status_force,$excluded ) {

	global $db_con;
	global $rootpath;
	global $simple_url;

	// Coluna => valor, na ordem gravada. Alguns parâmetros têm nome diferente da coluna.
	$campos = compact(
		"accesstoken","nome","descricao","segmento","estado","cidade","subdominio","cor","exibicao","pedido_minimo",
		"pagamento_dinheiro","pagamento_cartao_debito","pagamento_cartao_debito_bandeiras",
		"pagamento_cartao_credito","pagamento_cartao_credito_bandeiras",
		"pagamento_mercadopago","pagamento_mercadopago_sandbox","pagamento_mercadopago_public","pagamento_mercadopago_secret",
		"pagamento_getnet","pagamento_getnet_sandbox","pagamento_getnet_client_id","pagamento_getnet_client_secret","pagamento_getnet_seller_id",
		"pagamento_pix_mp","pagamento_pix"
	) + array(
		"chave_pix" => $pagamento_pix_chave,
		"beneficiario_pix" => $pagamento_pix_beneficiario,
	) + compact(
		"endereco_cep","endereco_numero","endereco_bairro","endereco_rua","endereco_complemento","endereco_referencia",
		"horario_funcionamento","entrega_retirada","entrega_entrega","entrega_entrega_tipo","entrega_entrega_valor"
	) + array(
		"delivery" => $entrega_delivery,
		"balcao" => $entrega_balcao,
		"mesa" => $entrega_mesa,
		"outros" => $entrega_outros,
		"nomeoutros" => $entrega_outros_nome,
	) + compact(
		"contato_whatsapp","contato_email","contato_instagram","contato_facebook","contato_youtube",
		"estatisticas_analytics","estatisticas_pixel","html",
		"responsavel_nome","responsavel_nascimento","responsavel_documento_tipo","responsavel_documento","email"
	) + array(
		"last_modified" => date("Y-m-d H:i:s"),
		"status_force" => $status_force,
		"excluded" => $excluded,
	);

	// Credenciais de pagamento: cifradas no banco. Campo vazio no formulário mantém a que já está gravada.
	foreach( array( "accesstoken","pagamento_mercadopago_secret","pagamento_getnet_client_secret" ) as $coluna ) {
		if( notnull( $campos[$coluna] ) ) {
			$campos[$coluna] = segredo_cifra( stripslashes( $campos[$coluna] ) );
		} else {
			unset( $campos[$coluna] );
		}
	}

	$set = array();
	foreach( $campos as $coluna => $valor ) {
		$set[] = $coluna." = '".$valor."'";
	}

	if( !mysqli_query( $db_con, "UPDATE estabelecimentos SET ".implode( ",",$set )." WHERE id = '$id'" ) ) {
		return false;
	}

	// O login do lojista acompanha o e-mail da loja; a senha só muda quando informada.
	$uid = data_info( "estabelecimentos",$id,"rel_users_id" );
	mysqli_query( $db_con, "UPDATE users SET email = '$email' WHERE id = '$uid'" );
	if( $pass ) {
		mysqli_query( $db_con, "UPDATE users SET password = '".md5( $pass )."' WHERE id = '$uid'" );
	}

	// Imagens: só troca quando uma nova foi enviada, apagando a anterior do disco.
	if( $perfil ) {
		@unlink( $rootpath."/_core/_uploads/".data_info( "estabelecimentos",$id,"perfil" ) );
		mysqli_query( $db_con, "UPDATE estabelecimentos SET perfil = '$perfil' WHERE id = '$id'" );
	}
	if( $capa ) {
		@unlink( $rootpath."/_core/_uploads/".data_info( "estabelecimentos",$id,"capa" ) );
		mysqli_query( $db_con, "UPDATE estabelecimentos SET capa = '$capa' WHERE id = '$id'" );
	}

	// Consulta sem efeito herdada do sistema original (id vazio); mantida para não alterar o comportamento.
	data_info( "subdominios","","subdominio" );
	mysqli_query( $db_con, "UPDATE subdominios SET subdominio = '$subdominio' WHERE rel_id = '$id'" );

	data_log( "editou o estabelecimento ".$subdominio.".".$simple_url );
	return true;

}

// Marca a loja como excluída sem apagar os dados.
function semidelete_estabelecimento( $id ) {

	global $db_con;
	global $simple_url;

	$agora = date("Y-m-d H:i:s");

	if( mysqli_query( $db_con, "UPDATE estabelecimentos SET excluded = '1',excluded_date = '$agora' WHERE id = '$id'" ) ) {

		// O log original não traz o subdomínio da loja removida.
		data_log( "removeu o estabelecimento .".$simple_url );
		return true;

	} else {

		return false;

	}

}

// Apaga a loja de vez: usuário, subdomínio, categorias, produtos, pedidos, banners,
// assinaturas e a pasta de uploads. Mídias, cupons, fretes, agendamentos e clientes ficam no banco.
function delete_estabelecimento( $id ) {

	global $db_con;
	global $rootpath;
	global $simple_url;

	$uid = data_info( "estabelecimentos",$id,"rel_users_id" );
	$perfil = data_info( "estabelecimentos",$id,"perfil" );
	$capa = data_info( "estabelecimentos",$id,"capa" );

	if( !mysqli_query( $db_con, "DELETE FROM estabelecimentos WHERE id = '$id'" ) ) {
		return false;
	}

	mysqli_query( $db_con, "DELETE FROM users WHERE id = '$uid'" );
	mysqli_query( $db_con, "DELETE FROM users_data WHERE rel_users_id = '$uid'" );
	mysqli_query( $db_con, "DELETE FROM subdominios WHERE rel_id = '$id' AND tipo = '1'" );
	mysqli_query( $db_con, "DELETE FROM categorias WHERE rel_estabelecimentos_id = '$id'" );
	mysqli_query( $db_con, "DELETE FROM produtos WHERE rel_estabelecimentos_id = '$id'" );
	mysqli_query( $db_con, "DELETE FROM pedidos WHERE rel_estabelecimentos_id = '$id'" );
	mysqli_query( $db_con, "DELETE FROM banners WHERE rel_estabelecimentos_id = '$id'" );
	mysqli_query( $db_con, "DELETE FROM assinaturas WHERE rel_estabelecimentos_id = '$id'" );

	@unlink( $rootpath."/_core/_uploads/".$perfil );
	@unlink( $rootpath."/_core/_uploads/".$capa );

	// A pasta da loja é _uploads/<id>. Só apaga com id numérico: no sistema original um id
	// vazio apagava a pasta _uploads inteira, e um id com "../" sairia dela.
	if( ctype_digit( (string) $id ) ) {
		deletedir( $rootpath."/_core/_uploads/".$id );
	}

	data_log( "removeu o estabelecimento .".$simple_url );
	return true;

}

?>

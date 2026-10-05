<?php
include('../../../_core/_includes/config.php'); 
$token = mysqli_real_escape_string($db_con, $_POST['token']);
$modo = $_POST['modo'];
session_id($token);
$eid = mysqli_real_escape_string($db_con, $_POST['eid']);
$pid = mysqli_real_escape_string($db_con, $_POST['produto']);
$produto = mysqli_real_escape_string($db_con, $_POST['produto']);
if (isset($_POST['produtoId'])) {
	$produtoId = (int) $_POST['produtoId'];
}
if (isset($_POST['variacaoId'])) {
	$variacaoId = (int) $_POST['variacaoId'];
}
if (isset($_POST['variacaoProdutoId'])) {
	$variacaoProdutoId = (int) $_POST['variacaoProdutoId'];
}
$parsedata = parse_str(urldecode($_POST['data']), $data);
$quantidade = $data['quantidade'];
$observacoes = $data['observacoes'];
$variacoes = $data['variacao'];
$variacoesQuantidade = $data['quantidade_variacao'];


// CHECKOUT

$nome = mysqli_real_escape_string($db_con, $_POST['nome']);
$whatsapp = mysqli_real_escape_string($db_con, clean_str($_POST['whatsapp']));
$forma_entrega = mysqli_real_escape_string($db_con, $_POST['forma_entrega']);
$estado = mysqli_real_escape_string($db_con, $_POST['estado']);
$cidade = mysqli_real_escape_string($db_con, $_POST['cidade']);
$endereco_cep = mysqli_real_escape_string($db_con, $_POST['endereco_cep']);
$endereco_numero = mysqli_real_escape_string($db_con, $_POST['endereco_numero']);
$endereco_bairro = mysqli_real_escape_string($db_con, $_POST['endereco_bairro']);
$endereco_rua = mysqli_real_escape_string($db_con, $_POST['endereco_rua']);
$endereco_complemento = mysqli_real_escape_string($db_con, $_POST['endereco_complemento']);
$endereco_referencia = mysqli_real_escape_string($db_con, $_POST['endereco_referencia']);
$forma_pagamento = mysqli_real_escape_string($db_con, $_POST['forma_pagamento']);
$forma_pagamento_informacao = mysqli_real_escape_string($db_con, $_POST['forma_pagamento_informacao']);
$cupom = mysqli_real_escape_string($db_con, $_POST['cupom']);
$mesa = mysqli_real_escape_string($db_con, $_POST['numero_mesa']);

if ($token) {

    if($modo == "calcularFrete" ) {
        
        $total_peso = 0;
        $total_cm_cubico = 0;
	    
	    foreach( $_SESSION['sacola'][$eid] AS $key => $value ) {
	        
	        
	        $id = $_SESSION['sacola'][$eid][$key]['id'];
	        
	        $quantidade       = floatval($_SESSION['sacola'][$eid][$key]['quantidade']);
	        
	        $pesofrete        = data_info('produtos',$id,'pesofrete');
	        $alturafrete      = data_info('produtos',$id,'alturafrete');
	        $largurafrete     = data_info('produtos',$id,'largurafrete');
	        $comprimentofrete = data_info('produtos',$id,'comprimentofrete');
	        $diametrofrete    = data_info('produtos',$id,'diametrofrete');
	        
            $row_peso = $pesofrete * $quantidade;
            $row_cm = ($alturafrete * $largurafrete * $comprimentofrete) * $quantidade;
        
            $total_peso += $row_peso;
            $total_cm_cubico += $row_cm;
        }
        
        $raiz_cubica = round(pow($total_cm_cubico, 1/3), 2);
        
        $comprimento =  $raiz_cubica < 16 ? 16 : $raiz_cubica;
        $altura = $raiz_cubica < 2 ? 2 : $raiz_cubica;
        $largura = $raiz_cubica < 11 ? 11 : $raiz_cubica;
        $peso = $total_peso < 0.3 ? 0.3 : $total_peso;
        $diametro = hypot($comprimento, $largura); 

        /*$empresa   = data_info('estabelecimentos',$eid,'empresa');
        $senha     = data_info('estabelecimentos',$eid,'senha');*/
        
        $tipofrete = "1";
        $ceporigem = data_info('estabelecimentos',$eid,'endereco_cep');
        $cepdestino = $_POST['endereco_cep'];
        
        require('Frete.php');
        
        //init
        echo '<option value="">Selecione...</option>';
        
        //PAC
        $frete = new Frete(
            "",
            "",
            '04510',
            $tipofrete,
            $ceporigem, 
            $cepdestino, 
            $peso, 
            $comprimento,
    		$altura,
    		$largura, 
            0
        );
        $vlr = floatVal($frete->getValor());
        
        $_SESSION['sacola']['frete']['04510']['valor'] = $vlr;
        
        $aqui = '';
        if($_SESSION['checkout']['forma_entrega'] == '04510'){
            $aqui = 'selected';    
        }
        
        if($vlr != 0){
            echo '<option value="PAC04510" '.$aqui.'>PAC = R$ '.dinheiro($vlr,'BR').' entrega em até '.$frete->getPrazoEntrega().' dias úteis.</option>';
        }
        
        //SEDEX
        $frete = new Frete(
            "",
            "",
            '04014',
            $tipofrete,
            $ceporigem, 
            $cepdestino, 
            $peso, 
            $comprimento,
    		$altura,
    		$largura, 
            0
        );
        $vlr = floatVal($frete->getValor());
        
        $_SESSION['sacola']['frete']['04014']['valor'] = $vlr;
        
        $aqui = '';
        if($_SESSION['checkout']['forma_entrega'] == '04014'){
            $aqui = 'selected';    
        }
        if($vlr != 0){
            echo '<option value="SEDEX04014" '.$aqui.'>SEDEX = R$ '.dinheiro($vlr,'BR').' entrega em até '.$frete->getPrazoEntrega().' dias úteis.</option>';
        }
	    die();
        
	}
	
	if ($modo == "adicionar") {

		$query_content = mysqli_query($db_con, "SELECT * FROM produtos WHERE id = '$pid' AND status = '1' ORDER BY id ASC LIMIT 1");
		$has_content = mysqli_num_rows($query_content);
		$data_content = mysqli_fetch_array($query_content);
		if ($has_content) {
		$eid = $data_content['rel_estabelecimentos_id'];
		$pid = $data_content['id'];
		sacola_adicionar($eid, $pid, $quantidade, $observacoes, $variacoes, $variacoesQuantidade);  

		?>
				
		<div class="row">

				<div class="col-md-12">

					<div class="adicionado">

						<i class="checkicon lni lni-checkmark-circle"></i>
						<span class="title"><?php echo htmlclean($data_content['nome']); ?></span>
						<span class="text">Adicionado a sacola com sucesso!</span>

					</div>

			  	</div>

			</div>

			<div class="row lowpadd">

				<div class="col-md-6">
					    <a href="<?php echo $app['url']; ?>/categoria" class="botao-acao botao-acao-gray"><i class="icone icone-sacola"></i> <span>Continuar comprando</span></a>
				</div>

				<div class="col-md-6">
					    <a href="<?php echo $app['url']; ?>/sacola" class="botao-acao"><i class="lni lni-checkmark-circle"></i> <span>Ver sacola</span></a>
				</div>

			</div>

		<?php } else { ?>

			<div class="row">

				<div class="col-md-12">

					<div class="adicionado">

						<i class="erroricon lni lni-close"></i>
						<span class="title">Erro!</span>
						<span class="text">Solicitação inválida!</span>

					</div>

			  	</div>

			</div>

			<div class="row lowpadd">

				<div class="col-md-12">
					    <a href="#" data-dismiss="modal" class="botao-acao botao-acao-gray"><i class="icone icone-sacola"></i> <span>Fechar</span></a>
				</div>

			</div>

		<?php

		}
	} 

	if ($modo == "estoque") {

		$query_content = mysqli_query($db_con, "SELECT estoque FROM produtos WHERE id = $produtoId");
		$data_content = mysqli_fetch_array($query_content);

		if ($quantidade <= $data_content['estoque']) {
			
			echo json_encode(['error' => false]);
		} else {
			echo json_encode(['error' => true, 'estoque' => $data_content['estoque'], 'message' => 'Estoque esgotado']);
		}
		
	}

	if ($modo == "estoque-variavel") {

		$query_content = mysqli_query($db_con, "SELECT variacao FROM produtos WHERE id = $produtoId");
		$data_content = mysqli_fetch_array($query_content);
		$data_content['variacao'] = json_decode($data_content['variacao'], true);

		$variacao = $data_content['variacao'][$variacaoId]['item'][$variacaoProdutoId];

		if ($quantidade <= base64_decode($variacao['estoque'])) {			
			echo json_encode(['error' => false]);
		} else {
			echo json_encode(['error' => true, 'estoque' => base64_decode($variacao['estoque']), 'message' => 'Estoque esgotado']);
		}
		
	}

	if ($modo == "alterar") {

		$query_content = mysqli_query($db_con, "SELECT estoque FROM produtos WHERE id = $produtoId");
		$data_content = mysqli_fetch_array($query_content);

		if ($quantidade <= $data_content['estoque']) {
			sacola_alterar($eid, $pid, $quantidade);
			echo json_encode(['error' => false]);
		} else {
			echo json_encode(['error' => true, 'message' => 'Estoque esgotado']);
		}
		
	}

	if ($modo == "remover") {

		sacola_remover($eid, $pid);
	}

	if ($modo == "contagem") {

		$contagem = count($_SESSION['sacola'][$eid]);
		echo $contagem;
	}

	if ($modo == "subtotal") {

		$subtotal = array();

		foreach ($_SESSION['sacola'][$eid] as $key => $value) {
			$query_content = mysqli_query($db_con, "SELECT * FROM produtos WHERE id = '$key' AND status = '1' ORDER BY id ASC LIMIT 1");
			$data_content = mysqli_fetch_array($query_content);
			if ($data_content['oferta'] == "1") {
				$valor_final = $data_content['valor_promocional'];
			} else {
				$valor_final = $data_content['valor'];
			}
			$valor_adicional = $_SESSION['sacola'][$eid][$key]['valor_adicional'];
			$subtotal[] = (($valor_final + $valor_adicional) * $_SESSION['sacola'][$eid][$key]['quantidade']);
		}

		$subtotal = array_sum($subtotal);
		echo "R$ " . dinheiro($subtotal, "BR");
	}

	if ($modo == "subtotal_clean") {

		$subtotal = array();

		foreach ($_SESSION['sacola'][$eid] as $key => $value) {
			$query_content = mysqli_query($db_con, "SELECT * FROM produtos WHERE id = '$key' AND status = '1' ORDER BY id ASC LIMIT 1");
			$data_content = mysqli_fetch_array($query_content);
			if ($data_content['oferta'] == "1") {
				$valor_final = $data_content['valor_promocional'];
			} else {
				$valor_final = $data_content['valor'];
			}
			$subtotal[] = (($valor_final + $valor_adicional) * $_SESSION['sacola'][$eid][$key]['quantidade']);
		}

		$subtotal = array_sum($subtotal);
		echo $subtotal;
	}

	if( $modo == "checkout" ) {

		checkout_salvar( $nome,$whatsapp,$forma_entrega,$estado,$cidade,$endereco_cep,$endereco_numero,$endereco_bairro,$endereco_rua,$endereco_complemento,$endereco_referencia,$forma_pagamento,$forma_pagamento_informacao,$cupom,$mesa );

	}

	if( $modo == "comprovante" ) {

		echo gera_comprovante($eid,"html","2","");

	}

}

?>
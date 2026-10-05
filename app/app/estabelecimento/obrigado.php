<?php
// CORE
include($virtualpath.'/_layout/define.php');
// APP
global $app;
is_active( $app['id'] );
$back_button = "true";
// Querys
$exibir = "8";
$app_id = $app['id'];
$pedido = mysqli_real_escape_string( $db_con, $_GET['pedido'] );
$forma_pagamento = mysqli_real_escape_string( $db_con, $_GET['forma'] );
$vpedido = mysqli_real_escape_string( $db_con, $_GET['codex'] );

//ALTERAÇÃO PARA SOMAR VALOR DO FRETE JUNTO DO TOTAL FEITA EM 25/05/22
$fid = $_SESSION['checkout']['forma_entrega'];
	$query_content = mysqli_query( $db_con, "SELECT * FROM frete WHERE id = '$fid' ORDER BY id ASC LIMIT 1" );
	$data_content = mysqli_fetch_array( $query_content );
	$frete_valor = $data_content['valor'];
	$frete = "R$ ".dinheiro( $frete_valor, "BR" );
	$frete_desc = htmlclean( $data_content['nome'] );
	if( $frete_valor >= 0.01 ) {
		$frete_desc .= " (+ R$".dinheiro( $frete_valor, "BR" ).")";
	}

	$total = "R$ ".( dinheiro( $vpedido + $frete_valor - $cupom_descontado , "BR") );

$whatsapp_linkx = whatsapp_link( $pedido );

if($forma_pagamento == 6) {
    
    $msg1="\n";
    $msg1.="*O cliente confirmou o pagamento deste pedido via PIX*\n";
    $msg1;
    $text1 = urlencode($msg1);
    
    $msg2="\n";
    $msg2.="*Período de confirmação do PIX foi finalizado. Confirme com o cliente via WhatsAPP*\n";
    $msg2;
    $text2 = urlencode($msg2);

  $whatsapp_link = $whatsapp_linkx."".$text1."";
  
  $whatsapp_linF = $whatsapp_linkx."".$text2."";

} else {
  
  $whatsapp_link = whatsapp_link( $pedido );  
    
}



if((isset($_POST['acao'])) AND ($_POST['acao'] == 'confirmaPIX')){
    $statupagamento = data_info('pedidos',$_GET['pedido'],'statuspagamento');
    echo $statupagamento;
    die();
}


// SEO
$seo_subtitle = $app['title']." - Pedido efetuado com sucesso!";
$seo_description = "Seu pedido ".$app['title']." no ".$seo_title." foi efetuado com sucesso!";
$seo_keywords = $app['title'].", ".$seo_title;
$seo_image = thumber( $app['avatar_clean'], 400 );
// HEADER
$system_header .= "";
include($virtualpath.'/_layout/head.php');
include($virtualpath.'/_layout/top.php');
include($virtualpath.'/_layout/sidebars.php');
include($virtualpath.'/_layout/modal.php');
?>

<div class="sceneElement">

	<div class="header-interna">

		<div class="locked-bar visible-xs visible-sm">

			<div class="avatar">
				<div class="holder">
					<a href="<?php echo $app['url']; ?>">
						<img src="<?php echo $app['avatar']; ?>"/>
					</a>
				</div>	
			</div>

		</div>

		<div class="holder-interna holder-interna-nopadd holder-interna-sacola visible-xs visible-sm"></div>

	</div>

	<div class="minfit">

			<div class="middle">

				<div class="container nopaddmobile">

					<div class="row rowtitle">

						<div class="col-md-12">
							<div class="title-icon">
								<span>Já recebemos seu Pedido</span>
							</div>
							<div class="bread-box">
								<div class="bread">
									<a href="<?php echo $app['url']; ?>"><i class="lni lni-home"></i></a>
									<span>/</span>
									<a href="<?php echo $app['url']; ?>/sacola.php">Minha sacola</a>
									<span>/</span>
									<a href="<?php echo $app['url']; ?>/pedido.php">Pedido</a>
									<span>/</span>
									<a href="<?php echo $app['url']; ?>/obrigado.php?pedido=<?php echo $pedido; ?>">Efetuado</a>
								</div>
							</div>
						</div>

						<div class="col-md-12 hidden-xs hidden-sm">
							<div class="clearline"></div>
						</div>

					</div>

					<div class="obrigado">

						<div class="row">

							<div class="col-md-12" align="center">
							    
							    <?php if($forma_pagamento == 6) { ?>
							    <div class="adicionado">
							        
							        <i class="checkicon lni lni-checkmark-circle"></i>  
							        <h4>Pague agora via PIX</h4>
							        <label>Valor de R$: <?php echo number_format($vpedido + $frete_valor,2,',','.'); ?></label>
							         
							        
							        <div class="row">

									<div class="col-md-7">

									  <div class="form-field-default">
									      <label>Chave Pix <?php echo $app['beneficiario_pix']; ?></label>
									      <input class="strupper" type="text" id="brcodepix" name="cupom" value="<?php echo $app['chave_pix']; ?>" style="text-align:center">

									  </div>

									</div>

									<div class="col-md-5">

									  <div class="form-field-default">
									      <label class="hidden-xs hidden-sm" style="color:#FFFFFF">.</label>
									      <span id="clip_btn" class="btn btn-primary btn-block btn-lg" style="color:#FFFFFF; font-size:14px;" onClick="copiar('brcodepix')">Copiar Chave</span>

									  </div>

									</div>

								</div>
							        
							    <p><strong>Após realizar o pagamento clique no botão abaixo para confirmar</strong></p>
							    
							    <a target="_blank" href="<?php echo $whatsapp_link; ?>" class="botao-acao"><i class="lni lni-whatsapp"></i> <span>Já fiz o PIX</span></a>
                                <br/>
                                <p><strong>Se for pagar somente no recebimento do pedido clique abaixo</strong></p>
                                <a target="_blank" href="<?php echo $whatsapp_linkx; ?>" class="botao-acao"><i class="lni lni-whatsapp"></i> <span>Farei ao Receber</span></a>
                                <br/>
                                <p><strong>Confirme uma das opções acima<br/>em até 5 minutos</strong></p>
                                <div class="progress">
          <div id="cont" class="progress-bar progress-bar-striped text-center active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" >
              <label id="contador"></label>%
          </div>
        </div>
                                
                                </div>
								<?php }else if($forma_pagamento == 10) { 
								    
								    $qrCode   = data_info('pedidos',$pedido,'qrcode');
								    $qrCode64 = data_info('pedidos',$pedido,'qrcode64');
								    $nome     = data_info('pedidos',$pedido,'nome');
								    $vpedido  = data_info('pedidos',$pedido,'v_pedido');
								    $whatsapp = data_info('pedidos',$pedido,'whatsapp');
								    $status   = data_info('pedidos',$pedido,'statuspagamento');
								    
								    if($qrCode == ''){
    
    								    $accessToken     = data_info('estabelecimentos',$app['id'],'accesstoken');
    								    
    								    //echo $accessToken;
    								    
                                        $url             = 'https://'.data_info("estabelecimentos",$app['id'],"subdominio").'.'.$simple_url.'/confirmapagamento.php';
                
    								    $date       = new DateTime(); 
    								    $a = rand();
                                        $b = rand();
                                        $c = rand(5, 15);
                                        $referencia = $a.$b.$c;
                            			$referencia = $pedido.$referencia;
                            			
                            			require_once('_core/_includes/functions/mercadopago/vendor/autoload.php');
                            			
                            			MercadoPago\SDK::setAccessToken($accessToken);
                            			
                            			$pagamento = new MercadoPago\Payment();
                            			$pagamento->description = "Pagamento PIX Pedido: ".$pedido;
                            			$pagamento->transaction_amount = (double)$vpedido + $frete_valor;
                            			$pagamento->payment_method_id = 'pix';
                            			$pagamento->notification_url = $url;
                            			$pagamento->external_reference = $referencia;
                            			
                            			$pagamento->payer=array(
                            				'email' => $whatsapp.'@gmail.com',
                            				'first_name' => $nome
                            			);
                            			
                            			$pagamento->save();
                            			
                            			//print_r($pagamento);
                            			
                            			if($pagamento->point_of_interaction==''){
                            				echo "<script>alert('Falha ao gerar qrCode entre em contato com a empresa.' );</script>";
                            			}else{
                            				$qrCode   = $pagamento->point_of_interaction->transaction_data->qr_code;
                            				$qrCode64 = $pagamento->point_of_interaction->transaction_data->qr_code_base64;
                            				
                            				$sql = "UPDATE pedidos SET VencimentoqrCode = now(), qrcode = '".$qrCode."',qrcode64='".$qrCode64."', referencia = '".$referencia."' WHERE id = ".$pedido.";";
                            				mysqli_query( $db_con,$sql);
                            			}
								    }
								
								?>
								
								
								
								
								
								
								<div class="adicionado">
							        
							        <i class="checkicon lni lni-checkmark-circle"></i>  
							        <h4>Pague agora via PIX</h4>
							        <label>Valor de R$: <?php echo number_format($vpedido + $frete_valor,2,',','.'); ?></label>

                                        <img width="50%" src="data:image/png;base64,<?php echo $qrCode64?>"
							            
    							       <div class="row" >
    							           
    									<div class="col-md-7">
    
    									  <div class="form-field-default">
    									      <label>Código PIX</label>
    									      <input class="strupper" type="text" id="codigopix" name="cupom" value="<?php echo $qrCode; ?>" style="text-align:center">
    
    									  </div>
    
    									</div>
    
    									<div class="col-md-5">
    
    									  <div class="form-field-default">
    									      <label class="hidden-xs hidden-sm" style="color:#FFFFFF">.</label>
    									      <span id="clip_btn" class="btn btn-primary btn-block btn-lg" style="color:#FFFFFF; font-size:14px;" onClick="copiar('codigopix')">Copiar Código PIX</span>
    
    									  </div>
    
    									</div>
    
    								</div>                 
                                </div>
                                
                                
                                
                                
								
								<?php }else { ?>
								
								<div class="adicionado">

									<i class="checkicon lni lni-checkmark-circle"></i>
									<span class="text">O seu pedido foi efetuado com sucesso. Aguarde que iremos enviar para o whatsapp ou clique abaixo!</span>
									<a target="_blank" href="<?php echo $whatsapp_link; ?>" class="botao-acao"><i class="lni lni-whatsapp"></i> <span>Enviar pedido</span></a>

								</div>
								
								<?php } ?>

							</div>

						</div>

					</div>

				</div>

			</div>

	</div>

</div>

<?php 
// FOOTER
$system_footer .= "";
include($virtualpath.'/_layout/rdp.php');
include($virtualpath.'/_layout/footer.php');
?>

<?php if($forma_pagamento == 6) { ?>

<script type="text/javascript">
        var contador = 0;
        function contar()
        {

            document.getElementById('contador').innerHTML = contador;
            if (contador < 100)
           {
               contador++
               document.getElementById('cont').style.width = contador + "%";
           }
       }
       function redirecionar()
       {
           contar();
           if (contador == 100)
           {
                let a= document.createElement('a');
                a.target= '_blank';
                a.href= '<?php echo $whatsapp_link; ?>';
                a.click();
                window.location = "<?php echo $app['url'].'/pedidosabertos'; ?>";
           }
       }

       setInterval(redirecionar, 6000);
       setInterval(contar, 6000);
       </script>

 

<?php } else if($forma_pagamento == 10) { ?>

<script>
    function verificapagamento(){
       $.post('<?php echo $app['url'].'/obrigado?pedido='.$_GET['pedido']; ?>',{acao:'confirmaPIX'},function(pago){
           if (pago == 'approved'){
                window.location = "<?php echo $app['url'].'/pedidosabertos'; ?>";
           }
       })
   }
   setInterval(verificapagamento, 5000);
</script>    
    
    
<?php }else { ?>

<script>

$(document).ready( function() {
       
	setTimeout(function(){ 
        let a= document.createElement('a');
        a.target= '_blank';
        a.href= '<?php echo $whatsapp_link; ?>';
        a.click();
        window.location = "<?php echo $app['url'].'/pedidosabertos'; ?>";
	}, 4000);

}); 

</script>

<?php } ?>


<script>
    
    function copiar(campo) {
  var copyText = document.getElementById(campo);
  copyText.select();
  copyText.setSelectionRange(0, 99999); /* For mobile devices */
  document.execCommand("copy");
  document.getElementById("clip_btn").innerHTML='Copiado';
}
    
</script>
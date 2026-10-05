<?php

// CORE

include($virtualpath.'/_layout/define.php');



// APP

global $app;
global $db_con;

//PAGUSEGURO CONFIG
include('config.php');



//Se PagSeguro está ativo
if ($data_estabelecimento['pagamento_mercadopago'] == 2) {
    header("Location: ".$app['url']."/sacola");
}

is_active( $app['id'] );

$back_button = "true";

// Querys

$exibir = "8";

$app_id = $app['id'];

$pedido = mysqli_real_escape_string( $db_con, $_GET['pedido'] );

$forma_pagamento = mysqli_real_escape_string( $db_con, $_GET['forma'] );

$vpedido = mysqli_real_escape_string( $db_con, $_GET['codex'] );

$tpedido = mysqli_real_escape_string( $db_con, $_GET['taxa'] );

// print_r($app);
// echo $vpedido."<br>";
// echo $tpedido;


if ($tpedido > 0)

	$vpedido += $tpedido;



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



$acompanhamento_finalizacao = '';



$query = "SELECT acompanhamento_finalizacao FROM estabelecimentos WHERE id = " . $app['id'];

$res = mysqli_query($db_con, $query);

$row = mysqli_fetch_row($res);



if ($row) {

  $acompanhamento_finalizacao = $row[0];

}





// Verificando e coletando pedido

$data_pedido = mysqli_query( $db_con, "SELECT * FROM pedidos WHERE id = '$pedido'");
$haspedido = mysqli_num_rows( $data_pedido );
$data_pedido = mysqli_fetch_array( $data_pedido );


    if ($haspedido) {

        //1 pendente
        if ($data_pedido['status'] == 1) {

            if (strlen($data_pedido['cupom']) > 0) {

                // $data_pedido = mysqli_query( $db_con, "SELECT * FROM cupom WHERE id = '$pedido'");
                // $haspedido = mysqli_num_rows( $data_pedido );
                // $data_pedido = mysqli_fetch_array( $data_pedido );
                // echo "desc";

                $pedido_total = ($data_pedido['v_pedido'] + $data_pedido['taxa']);

            } else {
                $pedido_total = ($data_pedido['v_pedido'] + $data_pedido['taxa']);
            }


        } else {
            header("Location: ".$app['url']."/pedidosabertos");
        }        

    } else {
        header("Location: ".$app['url']."/pedidosabertos");
    }



// print_r($data_pedido);

// print_r($app);






?>




<style>


    /* h2 {
        text-transform: uppercase;
        font-size: 25px;
    }
    label {
        text-align: left;
    } */

    /* select {
        width: 100%;
        height: 47px;
        background-color: #f2f2f2;
        border:1px solid #f2f2f2;
        padding: 5px;
		margin-bottom: 15px;

    } */
/* 
    .cont {
        margin-right: 250px;
        margin-left: 250px;
    } */

    /* button {
		background-color: #c64c35;
		text-transform: uppercase;
		color: #FFF;
		width: 100%;
		height: 50px;
		border: 1px solid #c64c35;
    }

    @media(max-width:1000px) {
        .cont {
            margin-right: 150px;
             margin-left: 150px;
        }
    }


    @media(max-width:768px) {
        .cont {
            margin-right: 10px;
             margin-left: 10px;
        }
    } */

	/* input {
		width: 100%;
        height: 47px !important;
        background-color: #f2f2f2;
        border:1px solid #f2f2f2;
        padding: 5px;
		margin-bottom: 15px;
	} */

	/* #form-checkout {
		display: flex;
		flex-direction: column;
		max-width: 600px;
	}

	.container-mp {
		height: 18px;
		display: inline-block;
		border: 1px solid rgb(118, 118, 118);
		border-radius: 2px;
		padding: 1px 2px;
	}

	.form-checkout__cardNumber-container  {
		height: 50px;
		background-color:orange;
		width: 100%;
	} */

</style>


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

				<input type="hidden" id="mercado-pago-public-key" value="{{ public_key }}">

				<section class="payment-form " style="margin-bottom: 50px">
						<div class="container__payment">
						
							<div class="form-payment">
						
								<div class="payment-details">
									<form id="form-checkout">
										<div class="row">
										<?php if ($sandbox == 1) { ?>
										<div class="cont" style="background-color:green; padding:20px;color:white">
											<div style="text-align:left">
												<h3>MODO TESTE</h3>
												<p>Este gateway está configurado com as credenciais de teste (sandbox).</p>
											</div>
										</div>
									<?php } ?>
							<img src="https://imgmp.mlstatic.com/org-img/MLB/MP/BANNERS/tipo2_735X40.jpg?v=1" 
							alt="Mercado Pago - Meios de pagamento" title="Mercado Pago - Meios de pagamento" 
							style="width: 100%;" /><br><br>
							
										</div>
										<center><h3 >DADOS DO COMPRADOR</h3></center>
									<div class="row">
										
											<div class="form-group col-sm-12">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">E-mail </label>
												<input id="form-checkout__cardholderEmail" name="cardholderEmail" type="email" style="background-color: transparent;border:1px solid #ccc" class="form-control h-40"/>
											</div>
										</div>
										<div class="row">
											
											<div class="form-group col-sm-5">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">Tipo de Documento </label>
												<select id="form-checkout__identificationType" name="identificationType" style="background-color: transparent;border:1px solid #ccc" class="form-control h-40"></select>
											</div>
											<div class="form-group col-sm-7">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">Numero do Documento </label>
												<input id="form-checkout__identificationNumber" name="docNumber" type="text" style="background-color: transparent;border:1px solid #ccc" class="form-control h-40"/>
											</div>
										</div>
										<br>
										<center><h3 >DADOS DO CARTÃO </h3></center>
										<div class="row">
											<div class="form-group col-sm-12">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">Número do Cartão </label>
												<div id="form-checkout__cardNumber" class="form-control h-40"></div>
												
											</div>
										
											<div class="form-group col-sm-12">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">Nome do Titular do Cartão </label>
												<input id="form-checkout__cardholderName" name="cardholderName" style="background-color: transparent;border:1px solid #ccc" type="text" class="form-control h-40"/>
											</div>


											
											<div class="form-group col-sm-8">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">Validade </label>
												<div class="input-group expiration-date">
													<div id="form-checkout__expirationDate" class="form-control h-40"></div>
												</div>
											</div>
											<div class="form-group col-sm-4">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">CVV do Cartão </label>
												<div id="form-checkout__securityCode" class="form-control h-40"></div>
											</div>

											
											<div id="issuerInput" class="form-group col-sm-12 hidden">
												<select id="form-checkout__issuer" name="issuer" class="form-control"></select>
											</div>
											<div class="form-group col-sm-12">
												<label style="display: block;min-height: 22px;font-size: 14px;font-weight: 600;" for="">Parcelas</label>
												<select id="form-checkout__installments" name="installments" type="text" class="form-control h-40"></select>
											</div>
											<div class="form-group col-sm-12">
												<input type="hidden" id="amount" value="<?php echo number_format(floatval($pedido_total), 2);?>" />
												<input type="hidden" id="description" value="Compras - <?=$app['title']?>"/>
												<div id="validation-error-messages">
												</div>
												<br>
												<button  id="form-checkout__submit" type="submit" class="btn h-40 btn-primary btn-block">CONCLUIR PAGAMENTO</button>
												<br>
												
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</section>


				</div>
			</div>
	</div>
</div>

<?php 



$system_footer .= "";

include($virtualpath.'/_layout/rdp.php');

include($virtualpath.'/_layout/footer.php');

?>


<script src="https://sdk.mercadopago.com/js/v2"></script>  
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    alertify.defaults.glossary.title = 'Aviso';
    alertify.defaults.glossary.ok = 'OK';
    alertify.defaults.glossary.cancel = 'CANCELAR';
</script>
    <script>
        const publicKey = document.getElementById("mercado-pago-public-key").value;
        const mercadopago = new MercadoPago("<?=PUBLIC_MERCADOPAGO?>");

        function loadCardForm() {
            const productCost = document.getElementById('amount').value;
            const productDescription = "descricao";
            const payButton = document.getElementById("form-checkout__submit");
            const validationErrorMessages= document.getElementById('validation-error-messages');

            const form = {
                id: "form-checkout",
                cardholderName: {
                    id: "form-checkout__cardholderName",
                    placeholder: "",
                },
                cardholderEmail: {
                    id: "form-checkout__cardholderEmail",
                    placeholder: "",
                },
                cardNumber: {
                    id: "form-checkout__cardNumber",
                    placeholder: "",
                    style: {
                        fontSize: "1rem"
                    },
                },
                expirationDate: {
                    id: "form-checkout__expirationDate",
                    placeholder: "MM/YYYY",
                    style: {
                        fontSize: "1rem"
                    },
                },
                securityCode: {
                    id: "form-checkout__securityCode",
                    placeholder: "",
                    style: {
                        fontSize: "1rem"
                    },
                },
                installments: {
                    id: "form-checkout__installments",
                    placeholder: "Selecione",
                },
                identificationType: {
                    id: "form-checkout__identificationType",
                },
                identificationNumber: {
                    id: "form-checkout__identificationNumber",
                    placeholder: "",
                },
                issuer: {
                    id: "form-checkout__issuer",
                    placeholder: "",
                },
            };

            const cardForm = mercadopago.cardForm({
                amount: productCost,
                iframe: true,
                form,
                callbacks: {
                    onFormMounted: error => {
                        if (error) {
                            console.log('x')

                            alertify.alert('O Mercadgo Pago está configurado incorretamente. Contate o proprietário(a) do(a) <?=$app["title"]?>.');
                            setInterval(function(){ window.location.href = "<?=$app['url']?>/sacola";}, 10000)
                        }
                        console.log("Form mounted");
                    },
                    onSubmit: event => {
                        event.preventDefault();
                        // document.getElementById("loading-message").style.display = "block";

                        // const {
                        //     paymentMethodId,
                        //     issuerId,
                        //     cardholderEmail: email,
                        //     amount,
                        //     token,
                        //     installments,
                        //     identificationNumber,
                        //     identificationType,
                        // } = cardForm.getCardFormData();


                            $.ajax({
                                    method: "POST",
                                    url:  "/mercadopago_process",
                                    data:{
                                        pedido:'<?=$data_pedido['id']?>',
                                        estabelecimento: '<?=$app['id']?>',
                                        data: cardForm.getCardFormData(),
                                    },
                                    success: function(retorna){
                                        response = JSON.parse(retorna)

                                        if (response.status == 'error')   {
                                            alertify.alert(response.message)
                                        } else {
                                         
                                            window.location.href = '<?=$app["url"]?>/mercadopago_status?pedido='+response.pedido+'&estabelecimento='+response.estabelecimento+'&pagamento='+response.id+"&status="+response.message;
                                        }                          
                                    },
                                    error: function(retorna) {
                                        console.log('=-=========')
                                        console.log(retorna)                                   
                                     }
                                });
                        // fetch("/mercadopago_process", {
                        //     method: "POST",
                        //     headers: {
                        //         "Content-Type": "application/json",
                        //     },
						// 		body: JSON.stringify({
						// 			token,
						// 			issuerId,
						// 			paymentMethodId,
						// 			transactionAmount: Number(amount),
						// 			installments: Number(installments),
						// 			description: productDescription,
						// 			payer: {
						// 				email,
						// 				identification: {
						// 					type: identificationType,
						// 					number: identificationNumber,
						// 				},
						// 			},
						// 		}),
						// 	})
                            
                        //     .then(result => {
                        //        return result.json()

                        //     })
                        //     .catch(error => {
                        //         // alert("Unexpected error\n"+JSON.stringify(error));
                        //         // alertify.alert('Ocorreu um erro inesperado. Contate o proprietário(a) do(a) .');
                        //         console.log('errofront')
                        //         console.log(error)
                        //     });
                    },
                    onFetching: (resource) => {
                        console.log("Fetching resource: ", resource);
                        payButton.setAttribute('disabled', true);
                        return () => {
                            payButton.removeAttribute("disabled");
                        };
                    },
                    onCardTokenReceived: (errorData, token) => {
                        if (errorData && errorData.error.fieldErrors.length !== 0) {
                            errorData.error.fieldErrors.forEach(errorMessage => {
                                // alert(errorMessage);
                                alertify.alert('Ocorreu um erro ao gerar o Token. Contate o proprietário(a) do(a) <?=$app["title"]?>.');
                            
                            });
                        }

                        return token;
                    },
                    onValidityChange: (error, field) => {
                        const input = document.getElementById(form[field].id);
                        removeFieldErrorMessages(input, validationErrorMessages);
                        addFieldErrorMessages(input, validationErrorMessages, error);
                        enableOrDisablePayButton(validationErrorMessages, payButton);
                    },
                    onError: (error) => {
                        // alertify.alert(error);
                        // console.log(error[0].message)
                        switch(error[0].message) {
                            case "cardNumber should be a number.":
                                alertify.alert('Digite o número do cartão.')
                                break;
                            case "cardNumber should be of length between '8' and '19'.":
                                alertify.alert('O número do cartão deve ter entre 8 e 19 caracteres.');
                                break;
                            case "expirationMonth should be a number.":
                                alertify.alert('O mês de expiração precisa ser um número.');
                                break;
                            case "expirationYear should be of length '2' or '4'.":
                                alertify.alert('O ano de expiração precisa de 2 ou 4 digitos.');
                                break;
                            case "expirationYear should be a number.":
                                alertify.alert('O ano de expiração deve ser um número válido.');
                                break;
                            case "parameter cardholderName can not be null/empty":
                                alertify.alert('O títular do cartão não pode ser vazio.');
                                break;
                            case "parameter identificationNumber can not be null/empty":
                                alertify.alert('O documento (CPF/CNPJ) não pode ser vazio.');
                                break;
                            case "securityCode should be of length '3'.":
                                alertify.alert('O CVV precisa ter no mínimo 3 digitos.');
                                break;
                            case "securityCode should be a number.":
                                alertify.alert('O CVV deve ser um número.');
                                break;
                            case "cardNumber should be of length '16'.":
                                alertify.alert('O número do cartão precisa ter 16 dígitos.');
                                break;
                            case "expirationYear value should be greater or equal than 2022.":
                                alertify.alert('O ano de expiração de ser igual ou maior que 2022.');
                                break;
                            default:
                                alertify.alert(error[0].message);


                        }
                    }

                },
            });
        };

        function removeFieldErrorMessages(input, validationErrorMessages) {
            Array.from(validationErrorMessages.children).forEach(child => {
                const shouldRemoveChild = child.id.includes(input.id);
                if (shouldRemoveChild) {
                    validationErrorMessages.removeChild(child);
                }
            });
        }

        function addFieldErrorMessages(input, validationErrorMessages, error) {
            if (error) {
                input.classList.add('validation-error');
                // error.forEach((e, index) => {
                    // const p = document.createElement('p');
                    // p.id = `${input.id}-${index}`;
                    // p.innerText = e.message;
                    // validationErrorMessages.appendChild(p);
                    // alertify.alert(error);
                    // console.log('erro')

                // });
            } else {
                input.classList.remove('validation-error');
            }
        }

        function enableOrDisablePayButton(validationErrorMessages, payButton) {
            if (validationErrorMessages.children.length > 0) {
                payButton.setAttribute('disabled', true);
            } else {
                payButton.removeAttribute('disabled');
            }
        }

        loadCardForm();
     

    </script>


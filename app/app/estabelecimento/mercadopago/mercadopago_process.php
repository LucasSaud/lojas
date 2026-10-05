<?php
// header('Content-Type: application/json; charset=utf-8');



include($virtualpath.'/_layout/define.php');



include('config.php');

require_once './vendor/autoload.php';
global $app;
global $db_con;


MercadoPago\SDK::setAccessToken(SECRET_MERCADOPAGO);

    
            try {

             
                $contents = $_POST;
                $pedido = $contents['pedido'];
                $estabelecimento = $contents['estabelecimento'];

                $payment = new MercadoPago\Payment();
        
                $payment->transaction_amount = $contents['data']['amount'];
                $payment->token = $contents['data']['token'];
                $payment->description = "Compras - ".$app['url'];
                $payment->installments = $contents['data']['installments'];
                $payment->payment_method_id = $contents['data']['paymentMethodId'];
                $payment->issuer_id = $contents['data']['issuerId'];
            
                $payer = new MercadoPago\Payer();
                $payer->email = $contents['data']['cardholderEmail'];
                $payer->identification = array(
                    "type" => $contents['data']['identificationType'],
                    "number" => $contents['data']['identificationNumber']
                );
                $payment->payer = $payer;
            
                $payment->save();
        
                if( $payment->error == ""){
                    if($payment->status == 'approved'){

                        $html= array(
                            'status'=>'approved',
                            'message'=> getStatus($payment),
                            'valor' => $payment->transaction_amount,
                            'id' => $payment->id,
                            'pedido' => $pedido,
                            'estabelecimento' => $estabelecimento
                        );

                        $codigo = createPayment($html, $db_con);

                        $html2= array(
                            'status'=>'in_process',
                            'message'=> getStatus($payment),
                            'valor' => $payment->transaction_amount,
                            'id' => $payment->id,
                            'pedido' => $pedido,
                            'estabelecimento' => $estabelecimento,
                            'codigo' =>  $codigo,
                        );

                    }else if ($payment->status == 'in_process') {
                        $html= array(
                            'status'=>'in_process',
                            'message'=> getStatus($payment),
                            'valor' => $payment->transaction_amount,
                            'id' => $payment->id,
                            'pedido' => $pedido,
                            'estabelecimento' => $estabelecimento
                        );

                        $codigo = createPayment($html, $db_con);

                        $html2= array(
                            'status'=>'in_process',
                            'message'=> getStatus($payment),
                            'valor' => $payment->transaction_amount,
                            'id' => $payment->id,
                            'pedido' => $pedido,
                            'estabelecimento' => $estabelecimento,
                            'codigo' =>  $codigo,
                        );


                    } else {
                        $html= array(
                            'status'=>'rejected',
                            'message'=> getStatus($payment),
                            'valor' => $payment->transaction_amount,
                            'id' => $payment->id,
                            'pedido' => $pedido,
                            'estabelecimento' => $estabelecimento
                        );

                        $codigo = createPayment($html, $db_con);

                        $html2= array(
                            'status'=>'in_process',
                            'message'=> getStatus($payment),
                            'valor' => $payment->transaction_amount,
                            'id' => $payment->id,
                            'pedido' => $pedido,
                            'estabelecimento' => $estabelecimento,
                            'codigo' =>  $codigo,
                        );
                    }
        
                }else{
                    $html2= array(
                        'status'=>'error',
                        'message'=> getErrors($payment)
                        
                    );
                }
                
        
                print_r(json_encode($html2));
                // print_r($payment);
        
        
        
            } catch(Exception $exception) {
        
                // $response_body = json_encode($exception->getMessage());
                // print_r(json_encode($exception->getMessage()));
                $html2= array(
                    'status'=>'error',
                    'message'=> $exception->getMessage()
                );

                print_r(json_encode($html2));
        
            }
        
      
    
        // #Get Status
        function getStatus($payment) {

            $status=[
                'accredited'=>'Pronto, seu pagamento foi aprovado!',
                'pending_contingency'=>'Estamos processando o pagamento. Em até 2 dias úteis informaremos por e-mail o resultado.',
                'pending_review_manual'=>'Estamos processando o pagamento. Em até 2 dias úteis informaremos por e-mail se foi aprovado ou se precisamos de mais informações.',
                'cc_rejected_bad_filled_card_number'=>'Confira o número do cartão.',
                'cc_rejected_bad_filled_date'=>'Confira a data de validade.',
                'cc_rejected_bad_filled_other'=>'Confira os dados.',
                'cc_rejected_bad_filled_security_code'=>'Confira o código de segurança.',
                'cc_rejected_blacklist'=>'Não conseguimos processar seu pagamento.',
                'cc_rejected_call_for_authorize'=>'Você deve autorizar o pagamento do valor ao Mercado Pago.',
                'cc_rejected_card_error'=>'Não conseguimos processar seu pagamento.',
                'cc_rejected_duplicated_payment'=>'Você já efetuou um pagamento com esse valor. Caso precise pagar novamente, utilize outro cartão ou outra forma de pagamento.',
                'cc_rejected_high_risk'=>'Seu pagamento foi recusado. Escolha outra forma de pagamento. Recomendamos meios de pagamento em dinheiro.',
                'cc_rejected_insufficient_amount'=>'O cartão possui saldo insuficiente.',
                'cc_rejected_invalid_installments'=>'O cartão não processa pagamentos parcelados.',
                'cc_rejected_max_attempts'=>'Você atingiu o limite de tentativas permitido. Escolha outro cartão ou outra forma de pagamento.',
                'cc_rejected_other_reason'=>'O cartão não processou seu pagamento'
            ];
    
            if(array_key_exists($payment->status_detail,$status)){
                return $status[$payment->status_detail];
            }else{
                return "Houve um problema na sua requisição. Tente novamente!";
            }
        }


        // #Get Error
        function getErrors($payment) {
            $error=[
                '106' => 'Não pode efetuar pagamentos a usuários de outros países.',
                '109' => 'O método de pagamento não processa pagamentos parcelados',
                '126' => 'Não conseguimos processar seu pagamento.',
                '129' => 'O método de pagamento não processa pagamentos para o valor selecionado. Escolha outro cartão ou outra forma de pagamento.',
                '145' => 'Uma das partes com a qual está tentando realizar o pagamento é um usuário de teste e a outra é um usuário real.',
                '150' => 'Você não pode efetuar pagamentos.',
                '151' => 'Você não pode efetuar pagamentos.',
                '160' => 'Não conseguimos processar seu pagamento.',
                '204' => 'O payment_method_id não está disponível nesse momento.Escolha outro cartão ou outra forma de pagamento.',
                '801' => 'Você realizou um pagamento similar há poucos instantes.Tente novamente em alguns minutos.',
                '205'=>'Digite o número do seu cartão.',
                '208'=>'Escolha um mês.',
                '209'=>'Escolha um ano.',
                '212'=>'Informe seu documento.',
                '213'=>'Informe seu documento.',
                '214'=>'Informe seu documento.',
                '220'=>'Informe seu banco emissor.',
                '221'=>'Informe seu sobrenome.',
                '224'=>'Digite o código de segurança.',
                'E301'=>'Há algo de errado com esse número. Digite novamente.',
                'E302'=>'Confira o código de segurança.',
                '316'=>'Por favor, digite um nome válido.',
                '322'=>'Confira seu documento.',
                '323'=>'Confira seu documento.',
                '324'=>'Confira seu documento.',
                '325'=>'Confira a data.',
                '326'=>'Confira a data.'
            ];
    
            if(array_key_exists( $payment->error->causes[0]->code,$error)){
                return $error[ $payment->error->causes[0]->code];
            }else{
                // return "Houve um problema na sua requisição. Tente novamente!";
            
                if ($payment->error->causes[0]->code == "2067") {
                    
                    return "Informe um número de documento (CPF/CNPJ) válido.";
                }

            }
        }

        function createPayment($pagamento, $db_con) {


            $pedido = $pagamento['pedido'];
            $estabelecimento = $pagamento['estabelecimento'];
            $data = date('d-m-Y');
            $hora = date('H:i');
            $valor = $pagamento['valor'];
            $codigo = $pagamento['id'];
            $status =$pagamento['status'];
            $gateway = 'mercadopago';
            $status_description = $pagamento['message'];


            //Cria pagamento

            if ( mysqli_query( $db_con, " INSERT INTO pagamentos(estabelecimento, pedido, data, hora, valor,gateway, codigo, status) VALUES ('$estabelecimento', '$pedido', '$data','$hora','$valor','$gateway','$codigo','$status') ")   ) {
                
                //Atualiza o status do pedido se o pagamento for aprovado.
                if ($pagamento['status'] == "approved") {
                    //Atualizando status do pedido.
                    mysqli_query( $db_con, "UPDATE pedidos SET status = '8' WHERE id = '$pedido'");

                    //Atualiza o comprovante com código se o pagamento for aprovado.
                    $_SESSION['checkout']['id_pagamento'] = $codigo;
                    $comprovante = gera_comprovante($estabelecimento,"texto","1",$pedido);
                    mysqli_query( $db_con, "UPDATE pedidos SET comprovante = '$comprovante' WHERE id = '$pedido'" );

                    //Limpa a sacola
                    unset( $_SESSION['sacola'] );
                    unset( $_SESSION['checkout']['id_pagamento'] );


                
                }

                return mysqli_insert_id($db_con);


            } 
        }

    
?>
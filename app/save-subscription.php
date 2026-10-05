<?php
session_start();

include($virtualpath.'_core/_includes/config.php');

include('push.php');

global $db_con;

header("Content-type: application/json");

$data = json_decode(file_get_contents('php://input'), true);

$insubdominio = $_GET['insubdominio'];
if( !$insubdominio ) {
    $insubdominio = array_shift((explode('.', $_SERVER['HTTP_HOST'])));
    if( $insubdominio == $firstdomain ) {
      $insubdominio = "";
    }
}

// Estabelecimento
$query = mysqli_query( $db_con, "SELECT * FROM estabelecimentos WHERE subdominio = '$insubdominio' LIMIT 1" );
$empresa = mysqli_fetch_array( $query );
  
if(is_array($data) && isset($data['endpoint'])){
    if((isset($_SESSION['checkout']['whatsapp']))AND($_SESSION['checkout']['whatsapp'] !='')){
        $whatsapp = $_SESSION['checkout']['whatsapp'];
    }else if((isset($_COOKIE['celcli']))AND($_COOKIE['celcli'] !='')){
        $whatsapp = $_COOKIE['celcli'];
    }
    if($whatsapp != ''){
        $p256dh   = $data['keys']['p256dh'];
        $auth     = $data['keys']['auth'];
        $endpoint = $data['endpoint'];
        $query = mysqli_query($db_con,"SELECT * FROM clientes WHERE whatsapp = ".$whatsapp);
        if(mysqli_num_rows($query) > 0){
            mysqli_query($db_con,"UPDATE clientes SET p256dh='$p256dh', auth='$auth', endpoint='$endpoint' WHERE whatsapp = '$whatsapp' ");
        }
    }
    if(isset($_SESSION['p256dh'])){
        unset($_SESSION['p256dh']);
        unset($_SESSION['auth']);
        unset($_SESSION['endpoint']);
    }
    
    $_SESSION['p256dh']     = $p256dh;
    $_SESSION['auth']       = $auth;
    $_SESSION['endpoint']   = $endpoint;
    
    if((isset($_GET['checknotification']) AND ($_GET['checknotification'])=='true')){
        enviaNotificacao($empresa['nome'],'Você está inscrito para receber notificações.','/',$p256dh,$auth,$endpoint,'/_core/_uploads/'.$empresa['perfil']);
    }
    echo json_encode(['status'=>'ok', 'message'=>'Subscribed']);
}
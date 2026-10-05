<?php
session_start();
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
// use Minishlink\WebPush\VAPID;

require "includes/database.php";
require 'web-push/vendor/autoload.php';

function enviaNotificacao($titulo,$mensagem,$site,$p256dh,$au,$endp,$icone=''){
    
    $publicKey = getenv( "VAPID_PUBLIC_KEY" );
    $privateKey = getenv( "VAPID_PRIVATE_KEY" );
    
    $message = json_encode([
        'title' => $titulo,
        'body' => $mensagem,
        'icon' => $icone,
        'badge' => '',
        'extraData' => $site
    ]);
    
    $auth = [
        'VAPID' => [
            'subject' => $site, // can be a mailto: or your website address
            'publicKey' => $publicKey, // (recommended) uncompressed public key P-256 encoded in Base64-URL
            'privateKey' => $privateKey, // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
        ],
    ];
    
    $webPush = new WebPush($auth);

    $subscription = Subscription::create([
            "endpoint" => $endp,
            "keys" => [
                'p256dh' => $p256dh,
                'auth' => $au
            ]
        ]);
    $webPush->queueNotification($subscription, $message);

    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();
            
        if ($report->isSuccess()) {
            echo "Notificado com sucesso!";
        } else {
            echo "Falha ao enviar notificação!";
        }
    }
}
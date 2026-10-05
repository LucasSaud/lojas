<?php

// Conexão usada por push.php. Mesmas credenciais do sistema (variáveis de ambiente).
$con = mysqli_connect( getenv( "DB_HOST" ),getenv( "DB_USER" ),getenv( "DB_PASS" ),getenv( "DB_NAME" ) );

if( !$con ) {
    echo "MySql Connection Error<br>";
    die;
}

$con->set_charset('utf8mb4');

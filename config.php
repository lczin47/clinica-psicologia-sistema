<?php
// Configurações do Banco de Dados usando constantes (melhor prática)
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'clinicapsico'); 

// Tenta fazer a conexão e armazena na variável $link
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Checa a conexão
if($link === false){
    // O die() interrompe o script e mostra o erro
    die("ERRO: Não foi possível conectar ao banco de dados. " . mysqli_connect_error());
}

// Garante o charset UTF-8
mysqli_set_charset($link, "utf8");

// A variável de conexão AGORA se chama $link.
?>
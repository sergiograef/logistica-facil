
<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "logistica_facil";

// Criar conexão
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// Verificar conexão
if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Configurar caracteres
mysqli_set_charset($conexao, "utf8mb4");

?>

<?php
// Exemplo de arquivo de configuração de conexão com o MySQL.
// NÃO comite suas credenciais reais no repositório. Copie este arquivo
// para 'conexao.local.php' e altere os valores conforme seu ambiente.

$host = 'localhost';
$user = 'seu_usuario_db';
$pass = 'sua_senha_db';
$banco = 'nome_do_banco';

// Cria a conexão e atribui $conn usada pelo restante do projeto
$conn = mysqli_connect($host, $user, $pass, $banco);
if (!$conn) {
    die('Erro ao conectar no banco de dados: ' . mysqli_connect_error());
}

// Opcional: definir charset
mysqli_set_charset($conn, 'utf8mb4');

?>

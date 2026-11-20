
<?php
include "config/conexao.php";

// CONTAR REGISTROS NO BANCO DE DADOS
function contar($conexao, $tabela) {

    // Somente tabelas permitidas
    $tabelas = [
        "produtos",
        "enderecos",
        "transportadores",
        "fretes"
    ];

    if (!in_array($tabela, $tabelas)) {
        return 0;
    }

    $resultado = mysqli_query(
        $conexao,
        "SELECT COUNT(*) AS total FROM $tabela"
    );

    $dados = mysqli_fetch_assoc($resultado);

    return $dados['total'];
}

$totalProdutos = contar($conexao, "produtos");
$totalEnderecos = contar($conexao, "enderecos");
$totalTransportadoras = contar($conexao, "transportadores");
$totalFretes = contar($conexao, "fretes");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Logística Fácil</title>

    <!-- BOOTSTRAP -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- NOSSO CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background: #f1f3f5;
        }

        .sidebar {
            background: #263445;
            min-height: 100vh;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 11px;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .sidebar a:hover {
            background: #40536a;
        }

        .sidebar .ativo {
            background: #40536a;
        }

        .card {
            border: none;
            border-radius: 8px;
        }

        .card h2 {
            font-size: 32px;
            color: #2467a8;
        }

        .card p {
            color: #666;
            margin-bottom: 0;
        }
    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- MENU LATERAL -->
        <nav class="col-md-2 sidebar p-3">

            <h4 class="text-white mb-4">
                Logística Fácil
            </h4>

            <a href="index.php" class="ativo">
                Dashboard
            </a>

            <a href="pages/produtos.php">
                Produtos
            </a>

            <a href="pages/enderecos.php">
                Endereços
            </a>

            <a href="pages/transportadores.php">
                Transportadoras
            </a>

            <a href="pages/fretes.php">
                Fretes
            </a>

            <a href="pages/relatorios.php">
                Relatórios
            </a>

        </nav>

        <!-- CONTEÚDO PRINCIPAL -->
        <main class="col-md-10 p-4">

            <h2>Dashboard</h2>

            <p>
                Bem-vindo ao sistema de
                gerenciamento logístico!
            </p>

            <div class="row mt-4">

                <!-- PRODUTOS -->
                <div class="col-md-6 col-lg-3 mb-3">

                    <div class="card shadow-sm p-3">

                        <h5>Produtos</h5>

                        <h2>
                            <?php echo $totalProdutos; ?>
                        </h2>

                        <p>Produtos cadastrados</p>

                    </div>

                </div>

                <!-- ENDEREÇOS -->
                <div class="col-md-6 col-lg-3 mb-3">

                    <div class="card shadow-sm p-3">

                        <h5>Endereços</h5>

                        <h2>
                            <?php echo $totalEnderecos; ?>
                        </h2>

                        <p>Endereços cadastrados</p>

                    </div>

                </div>

                <!-- TRANSPORTADORAS -->
                <div class="col-md-6 col-lg-3 mb-3">

                    <div class="card shadow-sm p-3">

                        <h5>Transportadoras</h5>

                        <h2>
                            <?php echo $totalTransportadoras; ?>
                        </h2>

                        <p>Empresas cadastradas</p>

                    </div>

                </div>

                <!-- FRETES -->
                <div class="col-md-6 col-lg-3 mb-3">

                    <div class="card shadow-sm p-3">

                        <h5>Fretes</h5>

                        <h2>
                            <?php echo $totalFretes; ?>
                        </h2>

                        <p>Fretes cadastrados</p>

                    </div>

                </div>

            </div>

            <!-- ACESSO RÁPIDO -->
            <h4 class="mt-4">Acesso rápido</h4>

            <div class="row mt-3">

                <div class="col-md-6 mb-3">

                    <div class="card shadow-sm p-3">

                        <h5>Gerenciar Fretes</h5>

                        <p class="mb-3">
                            Cadastre entregas, selecione
                            transportadoras e adicione produtos.
                        </p>

                        <a href="pages/fretes.php"
                           class="btn btn-primary">
                            Acessar Fretes
                        </a>

                    </div>

                </div>

                <div class="col-md-6 mb-3">

                    <div class="card shadow-sm p-3">

                        <h5>Relatórios</h5>

                        <p class="mb-3">
                            Consulte fretes por data,
                            imprima e exporte relatórios.
                        </p>

                        <a href="pages/relatorios.php"
                           class="btn btn-success">
                            Acessar Relatórios
                        </a>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

</body>
</html>

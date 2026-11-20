
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Logística Fácil</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Nosso CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- MENU LATERAL -->
            <nav class="col-md-2 sidebar p-3">

                <h4 class="text-white mb-4">Logística Fácil</h4>

                <a href="index.php">Dashboard</a>
                <a href="pages/produtos.php">Produtos</a>
                <a href="pages/enderecos.php">Endereços</a>
                <a href="pages/transportadores.php">Transportadores</a>
                <a href="pages/fretes.php">Fretes</a>
                <a href="pages/relatorios.php">Relatórios</a>

            </nav>

            <!-- CONTEÚDO PRINCIPAL -->
            <main class="col-md-10 p-4">

                <h2>Dashboard</h2>
                <p>Bem-vindo ao sistema de gerenciamento logístico!</p>

                <div class="row mt-4">

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm p-3">
                            <h5>Produtos</h5>
                            <h2>0</h2>
                            <p>Produtos cadastrados</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm p-3">
                            <h5>Transportadores</h5>
                            <h2>0</h2>
                            <p>Transportadores cadastrados</p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm p-3">
                            <h5>Fretes</h5>
                            <h2>0</h2>
                            <p>Fretes realizados</p>
                        </div>
                    </div>

                </div>

            </main>

        </div>
    </div>

</body>
</html>

<?php

include "../config/conexao.php";
// Excluir produto
if (isset($_GET['excluir'])) {

    $id = $_GET['excluir'];

    $sql = "DELETE FROM produtos WHERE id = ?";

    $consulta = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param($consulta, "i", $id);

    mysqli_stmt_execute($consulta);

    header("Location: produtos.php?excluido=1");
    exit;
}
// Atualizar produto
if (isset($_POST['atualizar'])) {

    $id = $_POST['id'];
    $codigo = $_POST['codigo'];
    $nome = $_POST['nome'];
    $quantidade = $_POST['quantidade'];
    $valor = $_POST['valor'];

    $sql = "UPDATE produtos
            SET codigo = ?, nome = ?, quantidade = ?, valor = ?
            WHERE id = ?";

    $consulta = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param(
        $consulta,
        "ssidi",
        $codigo,
        $nome,
        $quantidade,
        $valor,
        $id
    );

    mysqli_stmt_execute($consulta);

    header("Location: produtos.php?atualizado=1");
    exit;
}
// Buscar produto para edição
$produtoEditar = null;

if (isset($_GET['editar'])) {

    $id = $_GET['editar'];

    $sqlEditar = "SELECT * FROM produtos WHERE id = ?";

    $consultaEditar = mysqli_prepare($conexao, $sqlEditar);

    mysqli_stmt_bind_param($consultaEditar, "i", $id);

    mysqli_stmt_execute($consultaEditar);

    $resultadoEditar = mysqli_stmt_get_result($consultaEditar);

    $produtoEditar = mysqli_fetch_assoc($resultadoEditar);
}

// Salvar produto
if (isset($_POST['salvar'])) {

    $codigo = $_POST['codigo'];
    $nome = $_POST['nome'];
    $quantidade = $_POST['quantidade'];
    $valor = $_POST['valor'];

    $sql = "INSERT INTO produtos (codigo, nome, quantidade, valor)
            VALUES (?, ?, ?, ?)";

    $consulta = mysqli_prepare($conexao, $sql);

    mysqli_stmt_bind_param(
        $consulta,
        "ssid",
        $codigo,
        $nome,
        $quantidade,
        $valor
    );

    mysqli_stmt_execute($consulta);

    header("Location: produtos.php?sucesso=1");
    exit;
}

// Buscar produtos cadastrados
$sql = "SELECT * FROM produtos ORDER BY id DESC";
$resultado = mysqli_query($conexao, $sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos - Logística Fácil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- Menu -->
            <nav class="col-md-2 sidebar p-3">

                <h4 class="text-white mb-4">Logística Fácil</h4>

                <a href="../index.php">Dashboard</a>
                <a href="produtos.php">Produtos</a>
                <a href="enderecos.php">Endereços</a>
                <a href="transportadores.php">Transportadores</a>
                <a href="fretes.php">Fretes</a>
                <a href="relatorios.php">Relatórios</a>

            </nav>

            <!-- Conteúdo -->
            <main class="col-md-10 p-4">

                <h2>Cadastro de Produtos</h2>
                <p>Cadastre e consulte os produtos.</p>

                <?php if (isset($_GET['sucesso'])) { ?>

                    <div class="alert alert-success">
                        Produto cadastrado com sucesso!
                    </div>

                <?php } ?>
                
                <?php if (isset($_GET['excluido'])) { ?>

                    <div class="alert alert-success">
                        Produto excluído com sucesso!
                    </div>

                <?php } ?>

                <!-- Formulário -->
                <div class="card shadow-sm p-4 mt-4">

                    <h5>

                        <?php echo $produtoEditar ? 'Editar Produto' : 'Novo Produto'; ?>

                    </h5>

                    <form method="POST">

                        <?php if ($produtoEditar) { ?>

                            <input type="hidden" name="id" value="<?php echo $produtoEditar['id']; ?>">

                        <?php } ?>

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo"
                                    value="<?php echo $produtoEditar ? htmlspecialchars($produtoEditar['codigo']) : ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome do produto</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="<?php echo $produtoEditar ? htmlspecialchars($produtoEditar['nome']) : ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade"
                                    value="<?php echo $produtoEditar ? $produtoEditar['quantidade'] : ''; ?>" min="0"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="valor" class="form-label">Valor unitário (R$)</label>
                                <input type="number" class="form-control" id="valor" name="valor"
                                    value="<?php echo $produtoEditar ? $produtoEditar['valor'] : ''; ?>" min="0"
                                    step="0.01" required>
                            </div>

                        </div>

                        <?php if ($produtoEditar) { ?>

                            <button type="submit" name="atualizar" class="btn btn-success">
                                Atualizar Produto
                            </button>

                        <?php } else { ?>

                            <button type="submit" name="salvar" class="btn btn-primary">
                                Salvar Produto
                            </button>

                        <?php } ?>

                        <button type="reset" class="btn btn-secondary">
                            Limpar
                        </button>

                    </form>

                </div>

                <!-- Tabela -->
                <div class="card shadow-sm p-4 mt-4">

                    <h5>Produtos Cadastrados</h5>


                    <div class="table-responsive mt-3">

                        <table class="table table-striped table-hover">

                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php while ($produto = mysqli_fetch_assoc($resultado)) { ?>

                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($produto['codigo']); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($produto['nome']); ?>
                                        </td>

                                        <td>
                                            <?php echo $produto['quantidade']; ?>
                                        </td>

                                        <td>
                                            R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?>
                                        </td>

                                        <td>
                                            <a href="produtos.php?editar=<?php echo $produto['id']; ?>"
                                                class="btn btn-warning btn-sm">
                                                Editar
                                            </a>

                                            <a href="produtos.php?excluir=<?php echo $produto['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Deseja realmente excluir este produto?')">
                                                Excluir
                                            </a>
                                        </td>
                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>


                </div>

            </main>

        </div>
    </div>

</body>

</html>
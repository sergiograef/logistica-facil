<?php
include "../config/conexao.php";

// Excluir endereço
if (isset($_GET['excluir'])) {

    $id = $_GET['excluir'];

    $sql = "DELETE FROM enderecos WHERE id = ?";
    $stmt = $conexao->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    header("Location: enderecos.php");
    exit;
}
// Buscar endereço para edição
$endereco_editar = null;

if (isset($_GET['editar'])) {

    $id = $_GET['editar'];

    $sql = "SELECT * FROM enderecos WHERE id = ?";
    $stmt = $conexao->prepare($sql);

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $resultado_editar = $stmt->get_result();

    $endereco_editar = $resultado_editar->fetch_assoc();
}


if (isset($_POST['salvar'])) {

    $identificacao = $_POST['identificacao'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cep = $_POST['cep'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];

    $id = $_POST['id'] ?? '';

    if ($id != '') {

        // Atualizar endereço
        $sql = "UPDATE enderecos SET
                identificacao = ?,
                rua = ?,
                numero = ?,
                bairro = ?,
                cep = ?,
                cidade = ?,
                estado = ?
                WHERE id = ?";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "sssssssi",
            $identificacao,
            $rua,
            $numero,
            $bairro,
            $cep,
            $cidade,
            $estado,
            $id
        );

    } else {

        // Cadastrar novo endereço
        $sql = "INSERT INTO enderecos
                (identificacao, rua, numero, bairro, cep, cidade, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);

        $stmt->bind_param(
            "sssssss",
            $identificacao,
            $rua,
            $numero,
            $bairro,
            $cep,
            $cidade,
            $estado
        );
    }

    if ($stmt->execute()) {
        header("Location: enderecos.php?sucesso=1");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Endereços - Logística Fácil</title>

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

                <h2>Cadastro de Endereços</h2>
                <p>Cadastre e consulte os endereços de origem e destino.</p>

                <!-- Formulário -->
                <div class="card shadow-sm p-4 mt-4">

                    <h5>Novo Endereço</h5>


                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo $endereco_editar['id'] ?? ''; ?>">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Identificação do endereço</label>
                                <input type="text" name="identificacao"
                                    value="<?php echo $endereco_editar['identificacao'] ?? ''; ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rua</label>
                                <input type="text" name="rua" value="<?php echo $endereco_editar['rua'] ?? ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Número</label>
                                <input type="text" name="numero" value="<?php echo $endereco_editar['numero'] ?? ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" value="<?php echo $endereco_editar['bairro'] ?? ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" value="<?php echo $endereco_editar['cep'] ?? ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" value="<?php echo $endereco_editar['cidade'] ?? ''; ?>"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado (UF)</label>
                                <input type="text" name="estado" value="<?php echo $endereco_editar['estado'] ?? ''; ?>"
                                    required>
                            </div>

                        </div>

                        <button type="submit" name="salvar" class="btn btn-primary">
                            Salvar Endereço
                        </button>

                        <button type="reset" class="btn btn-secondary">
                            Limpar
                        </button>

                    </form>


                    <h2>Endereços cadastrados</h2>

                    <table border="1" cellpadding="10" cellspacing="0">
                        <tr>
                            <th>ID</th>
                            <th>Identificação</th>
                            <th>Rua</th>
                            <th>Número</th>
                            <th>Bairro</th>
                            <th>CEP</th>
                            <th>Cidade</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>

                        <?php
                        $sql = "SELECT * FROM enderecos ORDER BY id DESC";
                        $resultado = $conexao->query($sql);

                        while ($endereco = $resultado->fetch_assoc()) {
                            ?>

                            <tr>
                                <td>
                                    <?php echo $endereco['id']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['identificacao']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['rua']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['numero']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['bairro']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['cep']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['cidade']; ?>
                                </td>
                                <td>
                                    <?php echo $endereco['estado']; ?>
                                </td>
                                <td>
                                    <a href="enderecos.php?editar=<?php echo $endereco['id']; ?>">
                                        Editar
                                    </a>
                                </td>
                                <td>
                                    <a href="enderecos.php?excluir=<?php echo $endereco['id']; ?>"
                                        onclick="return confirm('Deseja realmente excluir este endereço?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>

                        <?php } ?>

                    </table>



                </div>

            </main>

        </div>
    </div>

</body>

</html>
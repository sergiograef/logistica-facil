
<?php
include "../config/conexao.php";

// CADASTRAR OU ATUALIZAR
if (isset($_POST['salvar'])) {

    $id = $_POST['id'] ?? '';
    $produto = $_POST['produto_id'];
    $origem = $_POST['origem_id'];
    $destino = $_POST['destino_id'];
    $transportador = $_POST['transportador_id'];
    $quantidade = $_POST['quantidade'];
    $data = $_POST['data_transporte'];
    $status = $_POST['status'];

    if ($id == '') {

        $sql = "INSERT INTO transportes
                (produto_id, origem_id, destino_id,
                 transportador_id, quantidade, data_transporte, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $consulta = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param(
            $consulta, "iiiiiss",
            $produto, $origem, $destino,
            $transportador, $quantidade, $data, $status
        );

    } else {

        $sql = "UPDATE transportes SET
                produto_id = ?,
                origem_id = ?,
                destino_id = ?,
                transportador_id = ?,
                quantidade = ?,
                data_transporte = ?,
                status = ?
                WHERE id = ?";

        $consulta = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param(
            $consulta, "iiiiissi",
            $produto, $origem, $destino,
            $transportador, $quantidade, $data, $status, $id
        );
    }

    mysqli_stmt_execute($consulta);

    header("Location: transportes.php?salvo=1");
    exit;
}

// EXCLUIR
if (isset($_GET['excluir'])) {

    $id = $_GET['excluir'];

    $consulta = mysqli_prepare(
        $conexao,
        "DELETE FROM transportes WHERE id = ?"
    );

    mysqli_stmt_bind_param($consulta, "i", $id);
    mysqli_stmt_execute($consulta);

    header("Location: transportes.php?excluido=1");
    exit;
}

// BUSCAR TRANSPORTE PARA EDITAR
$editar = [];

if (isset($_GET['editar'])) {

    $id = $_GET['editar'];

    $consulta = mysqli_prepare(
        $conexao,
        "SELECT * FROM transportes WHERE id = ?"
    );

    mysqli_stmt_bind_param($consulta, "i", $id);
    mysqli_stmt_execute($consulta);

    $resultado = mysqli_stmt_get_result($consulta);
    $editar = mysqli_fetch_assoc($resultado) ?: [];
}

// DADOS DOS CAMPOS
$produtos = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY nome");
$enderecos = mysqli_query($conexao, "SELECT * FROM enderecos ORDER BY identificacao");
$transportadores = mysqli_query($conexao, "SELECT * FROM transportadores ORDER BY nome");

// LISTAGEM
$lista = mysqli_query($conexao, "
    SELECT t.*,
           p.nome AS produto_nome,
           o.identificacao AS origem_nome,
           d.identificacao AS destino_nome,
           tr.nome AS transportador_nome
    FROM transportes t
    INNER JOIN produtos p ON t.produto_id = p.id
    INNER JOIN enderecos o ON t.origem_id = o.id
    INNER JOIN enderecos d ON t.destino_id = d.id
    INNER JOIN transportadores tr ON t.transportador_id = tr.id
    ORDER BY t.id DESC
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transportes - Logística Fácil</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            margin: 20px;
        }

        h1, h2 {
            color: #333;
        }

        .formulario {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 650px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
        }

        input, select, textarea {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            margin-top: 15px;
            cursor: pointer;
        }

        .salvar {
            background: #237a38;
            color: white;
            border: none;
        }

        .observacao {
            background: #e1e1e1;
            border: 1px solid #aaa;
        }

        table {
            border-collapse: collapse;
            background: white;
            width: 100%;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #ddd;
        }

        a {
            color: #075bb5;
        }

        .mensagem {
            color: green;
        }

        .erro {
            color: #b00020;
        }
    </style>
</head>

<body>

<a href="../index.php">← Voltar ao início</a>

<h1>Gerenciamento de Transportes</h1>

<?php if (isset($_GET['salvo'])) { ?>
    <p class="mensagem">Transporte salvo com sucesso!</p>
<?php } ?>

<?php if (isset($_GET['excluido'])) { ?>
    <p class="mensagem">Transporte excluído!</p>
<?php } ?>

<div class="formulario">

    <h2>
        <?php echo !empty($editar) ? "Editar transporte" : "Novo transporte"; ?>
    </h2>

    <form method="POST">

        <input type="hidden" name="id"
               value="<?php echo htmlspecialchars($editar['id'] ?? ''); ?>">

        <label>Produto:</label>
        <select name="produto_id" required>
            <option value="">Selecione</option>

            <?php while ($p = mysqli_fetch_assoc($produtos)) { ?>
                <option value="<?php echo $p['id']; ?>"
                    <?php
                    if (($editar['produto_id'] ?? '') == $p['id']) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo htmlspecialchars($p['nome']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Origem:</label>
        <select name="origem_id" required>
            <option value="">Selecione</option>

            <?php while ($e = mysqli_fetch_assoc($enderecos)) { ?>
                <option value="<?php echo $e['id']; ?>"
                    <?php
                    if (($editar['origem_id'] ?? '') == $e['id']) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo htmlspecialchars($e['identificacao']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Destino:</label>
        <select name="destino_id" required>
            <option value="">Selecione</option>

            <?php
            mysqli_data_seek($enderecos, 0);
            while ($e = mysqli_fetch_assoc($enderecos)) {
            ?>
                <option value="<?php echo $e['id']; ?>"
                    <?php
                    if (($editar['destino_id'] ?? '') == $e['id']) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo htmlspecialchars($e['identificacao']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Transportador:</label>
        <select name="transportador_id" required>
            <option value="">Selecione</option>

            <?php while ($t = mysqli_fetch_assoc($transportadores)) { ?>
                <option value="<?php echo $t['id']; ?>"
                    <?php
                    if (($editar['transportador_id'] ?? '') == $t['id']) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo htmlspecialchars($t['nome']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Quantidade:</label>
        <input type="number" name="quantidade" min="1" required
               value="<?php echo htmlspecialchars($editar['quantidade'] ?? ''); ?>">

        <label>Data do transporte:</label>
        <input type="date" name="data_transporte" required
               value="<?php echo htmlspecialchars($editar['data_transporte'] ?? ''); ?>">

        <label>Status:</label>
        <select name="status" required>
            <?php
            $statusOpcoes = ["Pendente", "Em andamento", "Concluído"];

            foreach ($statusOpcoes as $opcao) {
                $selecionado = (($editar['status'] ?? '') == $opcao)
                    ? "selected" : "";

                echo "<option value='$opcao' $selecionado>$opcao</option>";
            }
            ?>
        </select>

        <!-- CAMPO DINÂMICO COM JAVASCRIPT -->
        <div id="areaObservacao"></div>

        <button type="button" class="observacao"
                onclick="adicionarObservacao()">
            + Adicionar observação
        </button>

        <br>

        <button type="submit" name="salvar" class="salvar">
            Salvar transporte
        </button>

    </form>
</div>

<h2>Transportes cadastrados</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Produto</th>
        <th>Origem</th>
        <th>Destino</th>
        <th>Transportador</th>
        <th>Quantidade</th>
        <th>Data</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>

    <?php while ($t = mysqli_fetch_assoc($lista)) { ?>
        <tr>
            <td><?php echo $t['id']; ?></td>
            <td><?php echo htmlspecialchars($t['produto_nome']); ?></td>
            <td><?php echo htmlspecialchars($t['origem_nome']); ?></td>
            <td><?php echo htmlspecialchars($t['destino_nome']); ?></td>
            <td><?php echo htmlspecialchars($t['transportador_nome']); ?></td>
            <td><?php echo $t['quantidade']; ?></td>
            <td><?php echo htmlspecialchars($t['data_transporte']); ?></td>
            <td><?php echo htmlspecialchars($t['status']); ?></td>
            <td>
                <a href="?editar=<?php echo $t['id']; ?>">Editar</a>
                |
                <a href="?excluir=<?php echo $t['id']; ?>"
                   onclick="return confirm('Deseja excluir este transporte?')">
                    Excluir
                </a>
            </td>
        </tr>
    <?php } ?>
</table>

<script>
function adicionarObservacao() {

    let area = document.getElementById("areaObservacao");

    if (area.innerHTML !== "") {
        return;
    }

    area.innerHTML = `
        <label>Observação:</label>
        <textarea name="observacao" rows="3"
                  placeholder="Digite uma observação"></textarea>
    `;
}
</script>

</body>
</html>

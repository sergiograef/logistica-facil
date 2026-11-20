
<?php
include "../config/conexao.php";

$erro = "";
$editar = [];
$produtosFrete = [];

// SALVAR OU ATUALIZAR FRETE
if (isset($_POST['salvar'])) {

    $id = (int) ($_POST['id'] ?? 0);
    $transportador = (int) $_POST['transportador_id'];
    $origem = (int) $_POST['origem_id'];
    $destino = (int) $_POST['destino_id'];
    $data = $_POST['data_frete'];
    $status = $_POST['status'];

    $produtos = $_POST['produto_id'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];

    if ($origem == $destino) {
        $erro = "A origem e o destino devem ser diferentes.";
    } elseif (count($produtos) == 0 ||
              count($produtos) != count($quantidades)) {
        $erro = "Adicione pelo menos um produto.";
    } else {

        foreach ($produtos as $i => $produto) {
            if ((int)$produto <= 0 ||
                (int)$quantidades[$i] <= 0) {
                $erro = "Selecione os produtos e informe quantidades válidas.";
                break;
            }
        }
    }

    if ($erro == "") {

        mysqli_begin_transaction($conexao);

        try {

            if ($id == 0) {

                $sql = "INSERT INTO fretes
                        (transportador_id, origem_id, destino_id,
                         data_frete, status)
                        VALUES (?, ?, ?, ?, ?)";

                $consulta = mysqli_prepare($conexao, $sql);

                mysqli_stmt_bind_param(
                    $consulta, "iiiss",
                    $transportador, $origem, $destino,
                    $data, $status
                );

                mysqli_stmt_execute($consulta);

                $id = mysqli_insert_id($conexao);

            } else {

                $sql = "UPDATE fretes SET
                        transportador_id = ?,
                        origem_id = ?,
                        destino_id = ?,
                        data_frete = ?,
                        status = ?
                        WHERE id = ?";

                $consulta = mysqli_prepare($conexao, $sql);

                mysqli_stmt_bind_param(
                    $consulta, "iiissi",
                    $transportador, $origem, $destino,
                    $data, $status, $id
                );

                mysqli_stmt_execute($consulta);

                $consulta = mysqli_prepare(
                    $conexao,
                    "DELETE FROM frete_produtos WHERE frete_id = ?"
                );

                mysqli_stmt_bind_param($consulta, "i", $id);
                mysqli_stmt_execute($consulta);
            }

            // SALVAR PRODUTOS DO FRETE
            $sql = "INSERT INTO frete_produtos
                    (frete_id, produto_id, quantidade)
                    VALUES (?, ?, ?)";

            $consulta = mysqli_prepare($conexao, $sql);

            foreach ($produtos as $i => $produto) {

                $produto = (int) $produto;
                $quantidade = (int) $quantidades[$i];

                mysqli_stmt_bind_param(
                    $consulta, "iii",
                    $id, $produto, $quantidade
                );

                mysqli_stmt_execute($consulta);
            }

            mysqli_commit($conexao);

            header("Location: fretes.php?salvo=1");
            exit;

        } catch (Throwable $e) {

            mysqli_rollback($conexao);
            $erro = "Não foi possível salvar o frete.";
        }
    }
}

// EXCLUIR FRETE
if (isset($_POST['excluir'])) {

    $id = (int) $_POST['excluir'];

    $consulta = mysqli_prepare(
        $conexao,
        "DELETE FROM fretes WHERE id = ?"
    );

    mysqli_stmt_bind_param($consulta, "i", $id);

    if (mysqli_stmt_execute($consulta)) {
        header("Location: fretes.php?excluido=1");
        exit;
    } else {
        $erro = "Não foi possível excluir o frete.";
    }
}

// BUSCAR FRETE PARA EDIÇÃO
if (isset($_GET['editar'])) {

    $id = (int) $_GET['editar'];

    $consulta = mysqli_prepare(
        $conexao,
        "SELECT * FROM fretes WHERE id = ?"
    );

    mysqli_stmt_bind_param($consulta, "i", $id);
    mysqli_stmt_execute($consulta);

    $resultado = mysqli_stmt_get_result($consulta);
    $editar = mysqli_fetch_assoc($resultado) ?: [];

    if (!empty($editar)) {

        $consulta = mysqli_prepare(
            $conexao,
            "SELECT produto_id, quantidade
             FROM frete_produtos
             WHERE frete_id = ?"
        );

        mysqli_stmt_bind_param($consulta, "i", $id);
        mysqli_stmt_execute($consulta);

        $resultado = mysqli_stmt_get_result($consulta);

        while ($p = mysqli_fetch_assoc($resultado)) {
            $produtosFrete[] = $p;
        }
    }
}

// CARREGAR CADASTROS
$produtos = mysqli_query(
    $conexao,
    "SELECT id, nome FROM produtos ORDER BY nome"
);

$enderecos = mysqli_query(
    $conexao,
    "SELECT id, identificacao FROM enderecos ORDER BY identificacao"
);

$transportadoras = mysqli_query(
    $conexao,
    "SELECT id, nome FROM transportadores ORDER BY nome"
);

// LISTAR FRETES
$lista = mysqli_query($conexao, "
    SELECT f.*,
           t.nome AS transportadora,
           o.identificacao AS origem,
           d.identificacao AS destino
    FROM fretes f
    INNER JOIN transportadores t
        ON f.transportador_id = t.id
    INNER JOIN enderecos o
        ON f.origem_id = o.id
    INNER JOIN enderecos d
        ON f.destino_id = d.id
    ORDER BY f.id DESC
");

// FUNÇÃO PARA PROTEGER TEXTOS NO HTML
function mostrar($texto) {
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Fretes - Logística Fácil</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            margin: 20px;
        }

        h1, h2, h3 {
            color: #333;
        }

        .formulario {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 750px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            margin-top: 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        .salvar {
            background: #237a38;
            color: white;
        }

        .adicionar {
            background: #2467a8;
            color: white;
        }

        .remover {
            background: #c0392b;
            color: white;
        }

        .produto-item {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 12px;
            margin-top: 12px;
            border-radius: 5px;
        }

        .tabela {
            overflow-x: auto;
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

        .excluir-form {
            display: inline;
        }

        .excluir-form button {
            background: none;
            color: #b00020;
            padding: 0;
            margin: 0;
            text-decoration: underline;
        }
    </style>
</head>

<body>

<a href="../index.php">← Voltar ao início</a>

<h1>Gerenciamento de Fretes</h1>

<?php if (isset($_GET['salvo'])) { ?>
    <p class="mensagem">Frete salvo com sucesso!</p>
<?php } ?>

<?php if (isset($_GET['excluido'])) { ?>
    <p class="mensagem">Frete excluído com sucesso!</p>
<?php } ?>

<?php if ($erro != "") { ?>
    <p class="erro"><?php echo mostrar($erro); ?></p>
<?php } ?>

<div class="formulario">

    <h2>
        <?php
        echo !empty($editar)
            ? "Editar frete"
            : "Cadastrar novo frete";
        ?>
    </h2>

    <form method="POST">

        <input type="hidden" name="id"
               value="<?php echo mostrar($editar['id'] ?? ''); ?>">

        <label>Transportadora:</label>
        <select name="transportador_id" required>
            <option value="">Selecione a transportadora</option>

            <?php while ($t = mysqli_fetch_assoc($transportadoras)) { ?>
                <option value="<?php echo $t['id']; ?>"
                    <?php
                    if (($editar['transportador_id'] ?? '') == $t['id']) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo mostrar($t['nome']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Origem:</label>
        <select name="origem_id" required>
            <option value="">Selecione a origem</option>

            <?php while ($e = mysqli_fetch_assoc($enderecos)) { ?>
                <option value="<?php echo $e['id']; ?>"
                    <?php
                    if (($editar['origem_id'] ?? '') == $e['id']) {
                        echo "selected";
                    }
                    ?>>
                    <?php echo mostrar($e['identificacao']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Destino:</label>
        <select name="destino_id" required>
            <option value="">Selecione o destino</option>

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
                    <?php echo mostrar($e['identificacao']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Data do frete:</label>
        <input type="date" name="data_frete" required
               value="<?php echo mostrar($editar['data_frete'] ?? date('Y-m-d')); ?>">

        <label>Status:</label>
        <select name="status" required>

            <?php
            $opcoes = ["Pendente", "Em andamento", "Concluído"];

            foreach ($opcoes as $opcao) {

                $selecionado =
                    (($editar['status'] ?? '') == $opcao)
                    ? "selected" : "";
            ?>
                <option value="<?php echo mostrar($opcao); ?>"
                        <?php echo $selecionado; ?>>
                    <?php echo mostrar($opcao); ?>
                </option>
            <?php } ?>

        </select>

        <h3>Produtos do frete</h3>

        <div id="areaProdutos">

            <?php foreach ($produtosFrete as $p) { ?>

                <div class="produto-item">

                    <label>Produto:</label>
                    <select name="produto_id[]" required>
                        <option value="">Selecione</option>

                        <?php
                        mysqli_data_seek($produtos, 0);

                        while ($produto = mysqli_fetch_assoc($produtos)) {
                        ?>
                            <option value="<?php echo $produto['id']; ?>"
                                <?php
                                if ($p['produto_id'] == $produto['id']) {
                                    echo "selected";
                                }
                                ?>>
                                <?php echo mostrar($produto['nome']); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label>Quantidade:</label>
                    <input type="number"
                           name="quantidade[]"
                           min="1"
                           required
                           value="<?php echo mostrar($p['quantidade']); ?>">

                    <button type="button" class="remover"
                            onclick="removerProduto(this)">
                        Remover produto
                    </button>

                </div>

            <?php } ?>

        </div>

        <button type="button" class="adicionar"
                onclick="adicionarProduto()">
            + Adicionar produto
        </button>

        <br>

        <button type="submit" name="salvar" class="salvar">
            Salvar frete
        </button>

    </form>

</div>

<h2>Fretes cadastrados</h2>

<div class="tabela">

<table>
    <tr>
        <th>ID</th>
        <th>Transportadora</th>
        <th>Origem</th>
        <th>Destino</th>
        <th>Data</th>
        <th>Status</th>
        <th>Produtos</th>
        <th>Ações</th>
    </tr>

    <?php while ($f = mysqli_fetch_assoc($lista)) { ?>

        <tr>
            <td><?php echo $f['id']; ?></td>
            <td><?php echo mostrar($f['transportadora']); ?></td>
            <td><?php echo mostrar($f['origem']); ?></td>
            <td><?php echo mostrar($f['destino']); ?></td>
            <td><?php echo mostrar($f['data_frete']); ?></td>
            <td><?php echo mostrar($f['status']); ?></td>

            <td>
                <?php
                $consulta = mysqli_prepare(
                    $conexao,
                    "SELECT p.nome, fp.quantidade
                     FROM frete_produtos fp
                     INNER JOIN produtos p
                         ON fp.produto_id = p.id
                     WHERE fp.frete_id = ?"
                );

                mysqli_stmt_bind_param(
                    $consulta, "i", $f['id']
                );

                mysqli_stmt_execute($consulta);

                $itens = mysqli_stmt_get_result($consulta);

                while ($item = mysqli_fetch_assoc($itens)) {
                    echo mostrar($item['nome']);
                    echo " (Qtd: " . (int)$item['quantidade'] . ")";
                    echo "<br>";
                }
                ?>
            </td>

            <td>
                <a href="?editar=<?php echo $f['id']; ?>">
                    Editar
                </a>

                |

                <form method="POST" class="excluir-form"
                      onsubmit="return confirm('Deseja excluir este frete?')">

                    <button type="submit"
                            name="excluir"
                            value="<?php echo $f['id']; ?>">
                        Excluir
                    </button>

                </form>
            </td>
        </tr>

    <?php } ?>

</table>

</div>

<!-- MODELO DE PRODUTO PARA O JAVASCRIPT -->
<template id="modeloProduto">

    <div class="produto-item">

        <label>Produto:</label>

        <select name="produto_id[]" required>
            <option value="">Selecione um produto</option>

            <?php
            mysqli_data_seek($produtos, 0);

            while ($p = mysqli_fetch_assoc($produtos)) {
            ?>
                <option value="<?php echo $p['id']; ?>">
                    <?php echo mostrar($p['nome']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Quantidade:</label>

        <input type="number"
               name="quantidade[]"
               min="1"
               value="1"
               required>

        <button type="button" class="remover"
                onclick="removerProduto(this)">
            Remover produto
        </button>

    </div>

</template>

<script>

// ADICIONAR PRODUTO DINAMICAMENTE
function adicionarProduto() {

    let modelo = document.getElementById("modeloProduto");
    let area = document.getElementById("areaProdutos");

    let novoProduto = modelo.content.cloneNode(true);

    area.appendChild(novoProduto);
}

// REMOVER PRODUTO
function removerProduto(botao) {

    let produto = botao.closest(".produto-item");

    produto.remove();
}

// ADICIONAR PRIMEIRO PRODUTO AUTOMATICAMENTE
if (document.querySelectorAll(".produto-item").length === 0) {
    adicionarProduto();
}

</script>

</body>
</html>

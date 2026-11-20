
<?php
include "../config/conexao.php";

// CADASTRAR OU ATUALIZAR TRANSPORTADORA
if (isset($_POST['salvar'])) {

    $id = $_POST['id'] ?? '';
    $nome = trim($_POST['nome']);
    $cnpj = trim($_POST['documento']);
    $telefone = trim($_POST['telefone']);

    if ($id == '') {

        $sql = "INSERT INTO transportadores
                (nome, documento, telefone)
                VALUES (?, ?, ?)";

        $consulta = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param(
            $consulta, "sss",
            $nome, $cnpj, $telefone
        );

    } else {

        $sql = "UPDATE transportadores
                SET nome = ?, documento = ?, telefone = ?
                WHERE id = ?";

        $consulta = mysqli_prepare($conexao, $sql);

        mysqli_stmt_bind_param(
            $consulta, "sssi",
            $nome, $cnpj, $telefone, $id
        );
    }

    mysqli_stmt_execute($consulta);

    header("Location: transportadores.php?salvo=1");
    exit;
}

// EXCLUIR TRANSPORTADORA
if (isset($_GET['excluir'])) {

    $id = (int) $_GET['excluir'];

    $consulta = mysqli_prepare(
        $conexao,
        "DELETE FROM transportadores WHERE id = ?"
    );

    mysqli_stmt_bind_param($consulta, "i", $id);

    if (mysqli_stmt_execute($consulta)) {
        header("Location: transportadores.php?excluido=1");
    } else {
        header("Location: transportadores.php?erro=1");
    }

    exit;
}

// BUSCAR TRANSPORTADORA PARA EDITAR
$editar = [];

if (isset($_GET['editar'])) {

    $id = (int) $_GET['editar'];

    $consulta = mysqli_prepare(
        $conexao,
        "SELECT * FROM transportadores WHERE id = ?"
    );

    mysqli_stmt_bind_param($consulta, "i", $id);
    mysqli_stmt_execute($consulta);

    $resultado = mysqli_stmt_get_result($consulta);
    $editar = mysqli_fetch_assoc($resultado) ?: [];
}

// LISTAR TRANSPORTADORAS
$lista = mysqli_query(
    $conexao,
    "SELECT * FROM transportadores ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Transportadoras - Logística Fácil</title>

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
            max-width: 600px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
        }

        input {
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

<h1>Cadastro de Transportadoras</h1>

<?php if (isset($_GET['salvo'])) { ?>
    <p class="mensagem">Transportadora salva com sucesso!</p>
<?php } ?>

<?php if (isset($_GET['excluido'])) { ?>
    <p class="mensagem">Transportadora excluída!</p>
<?php } ?>

<?php if (isset($_GET['erro'])) { ?>
    <p class="erro">
        Não foi possível excluir a transportadora.
        Ela pode estar vinculada a um transporte.
    </p>
<?php } ?>

<div class="formulario">

    <h2>
        <?php
        echo !empty($editar)
            ? "Editar transportadora"
            : "Nova transportadora";
        ?>
    </h2>

    <form method="POST">

        <input type="hidden" name="id"
               value="<?php echo htmlspecialchars($editar['id'] ?? ''); ?>">

        <label>Nome da empresa:</label>
        <input type="text" name="nome" required
               placeholder="Ex: Transportadora Rápida LTDA"
               value="<?php echo htmlspecialchars($editar['nome'] ?? ''); ?>">

        <label>CNPJ:</label>
        <input type="text" name="documento" required
               maxlength="18"
               placeholder="00.000.000/0000-00"
               value="<?php echo htmlspecialchars($editar['documento'] ?? ''); ?>">

        <label>Telefone:</label>
        <input type="text" name="telefone" required
               maxlength="20"
               placeholder="(45) 99999-9999"
               value="<?php echo htmlspecialchars($editar['telefone'] ?? ''); ?>">

        <button type="submit" name="salvar" class="salvar">
            Salvar transportadora
        </button>

    </form>
</div>

<h2>Transportadoras cadastradas</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Nome da empresa</th>
        <th>CNPJ</th>
        <th>Telefone</th>
        <th>Ações</th>
    </tr>

    <?php while ($t = mysqli_fetch_assoc($lista)) { ?>
        <tr>
            <td><?php echo $t['id']; ?></td>
            <td><?php echo htmlspecialchars($t['nome']); ?></td>
            <td><?php echo htmlspecialchars($t['documento']); ?></td>
            <td><?php echo htmlspecialchars($t['telefone']); ?></td>
            <td>
                <a href="?editar=<?php echo $t['id']; ?>">Editar</a>
                |
                <a href="?excluir=<?php echo $t['id']; ?>"
                   onclick="return confirm('Deseja excluir esta transportadora?')">
                    Excluir
                </a>
            </td>
        </tr>
    <?php } ?>

</table>

</body>
</html>

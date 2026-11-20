
<?php
include "../config/conexao.php";

// FUNÇÃO PARA EXIBIR TEXTOS COM SEGURANÇA
function mostrar($texto) {
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        'UTF-8'
    );
}

// DATA DO RELATÓRIO
$data = $_GET['data'] ?? date('Y-m-d');

// VERIFICAR SE A DATA É VÁLIDA
$verificar = DateTime::createFromFormat('!Y-m-d', $data);

if (!$verificar || $verificar->format('Y-m-d') != $data) {
    $data = date('Y-m-d');
}

// BUSCAR FRETES NO BANCO
$sql = "
    SELECT
        f.id,
        f.data_frete,
        f.status,
        t.nome AS transportadora,
        o.identificacao AS origem,
        d.identificacao AS destino,
        GROUP_CONCAT(
            CONCAT(p.nome, ' (Qtd: ', fp.quantidade, ')')
            ORDER BY p.nome
            SEPARATOR ', '
        ) AS produtos

    FROM fretes f

    INNER JOIN transportadores t
        ON f.transportador_id = t.id

    INNER JOIN enderecos o
        ON f.origem_id = o.id

    INNER JOIN enderecos d
        ON f.destino_id = d.id

    LEFT JOIN frete_produtos fp
        ON fp.frete_id = f.id

    LEFT JOIN produtos p
        ON fp.produto_id = p.id

    WHERE f.data_frete = ?

    GROUP BY
        f.id,
        f.data_frete,
        f.status,
        t.nome,
        o.identificacao,
        d.identificacao

    ORDER BY f.id DESC
";

$consulta = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($consulta, "s", $data);
mysqli_stmt_execute($consulta);

$resultado = mysqli_stmt_get_result($consulta);

$fretes = [];

while ($linha = mysqli_fetch_assoc($resultado)) {
    $fretes[] = $linha;
}

// EXPORTAR CSV
if (isset($_GET['baixar'])) {

    header("Content-Type: text/csv; charset=UTF-8");

    header(
        'Content-Disposition: attachment; filename="relatorio_fretes_' .
        $data . '.csv"'
    );

    $arquivo = fopen("php://output", "w");

    // ACENTOS NO EXCEL
    fwrite($arquivo, "\xEF\xBB\xBF");

    fputcsv($arquivo, [
        "ID",
        "Data",
        "Transportadora",
        "Origem",
        "Destino",
        "Produtos",
        "Status"
    ], ";", '"', "");

    // EVITAR INTERPRETAÇÃO DE TEXTOS COMO FÓRMULAS
    function textoCSV($valor) {
        $valor = (string)$valor;

        if (preg_match('/^[\s]*[=+\-@]/u', $valor)) {
            return "'" . $valor;
        }

        return $valor;
    }

    foreach ($fretes as $f) {

        fputcsv($arquivo, [
            $f['id'],
            date("d/m/Y", strtotime($f['data_frete'])),
            textoCSV($f['transportadora']),
            textoCSV($f['origem']),
            textoCSV($f['destino']),
            textoCSV($f['produtos'] ?? ''),
            textoCSV($f['status'])
        ], ";", '"', "");
    }

    fclose($arquivo);
    exit;
}

// RESUMO DOS FRETES
$total = count($fretes);
$pendentes = 0;
$andamento = 0;
$concluidos = 0;

foreach ($fretes as $f) {

    if ($f['status'] == "Pendente") {
        $pendentes++;
    }

    if ($f['status'] == "Em andamento") {
        $andamento++;
    }

    if ($f['status'] == "Concluído") {
        $concluidos++;
    }
}

$dataFormatada = date("d/m/Y", strtotime($data));
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Relatórios - Logística Fácil</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            margin: 20px;
            color: #333;
        }

        h1, h2 {
            color: #333;
        }

        .cabecalho {
            margin-bottom: 25px;
        }

        .filtro {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            padding: 10px;
            width: 100%;
            max-width: 250px;
            box-sizing: border-box;
        }

        button, .botao {
            display: inline-block;
            padding: 11px 16px;
            margin-top: 15px;
            border: none;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .consultar {
            background: #2467a8;
        }

        .imprimir {
            background: #555;
        }

        .baixar {
            background: #237a38;
        }

        .resumo {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            min-width: 140px;
            border: 1px solid #ddd;
        }

        .card h3 {
            margin: 0;
            font-size: 15px;
            color: #555;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0 0;
        }

        .relatorio {
            background: white;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .titulo-relatorio {
            text-align: center;
            margin-bottom: 25px;
        }

        .titulo-relatorio h2 {
            margin-bottom: 5px;
        }

        .titulo-relatorio p {
            margin-top: 0;
            color: #666;
        }

        .tabela {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        th {
            background: #e6e6e6;
        }

        tr:nth-child(even) {
            background: #f8f8f8;
        }

        .acoes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        .mensagem {
            padding: 15px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }

        a {
            color: #075bb5;
        }

        /* FORMATAÇÃO PARA IMPRESSÃO E PDF */
        @media print {

            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            body {
                background: white;
                margin: 0;
                font-size: 11px;
            }

            .nao-imprimir {
                display: none !important;
            }

            .relatorio {
                border: none;
                padding: 0;
            }

            .resumo {
                display: flex;
                gap: 10px;
            }

            .card {
                padding: 10px;
                min-width: 100px;
                border: 1px solid #ccc;
            }

            .card p {
                font-size: 20px;
            }

            .tabela {
                overflow: visible;
            }

            table {
                width: 100%;
                table-layout: fixed;
                font-size: 10px;
            }

            th, td {
                padding: 6px;
                overflow-wrap: anywhere;
            }

            thead {
                display: table-header-group;
            }

            tr {
                break-inside: avoid;
            }
        }
    </style>

</head>

<body>

<div class="nao-imprimir cabecalho">

    <a href="../index.php">← Voltar ao início</a>

    <h1>Relatórios de Fretes</h1>

    <p>
        Consulte os fretes, visualize os resultados
        e gere relatórios para impressão.
    </p>

</div>

<!-- FILTRO -->
<div class="filtro nao-imprimir">

    <h2>Consultar fretes</h2>

    <form method="GET">

        <label>Selecione a data:</label>

        <input type="date"
               name="data"
               value="<?php echo mostrar($data); ?>"
               required>

        <br>

        <button type="submit" class="consultar">
            Gerar relatório
        </button>

    </form>

</div>

<!-- BOTÕES -->
<div class="acoes nao-imprimir">

    <button type="button"
            class="imprimir"
            onclick="window.print()">
        Imprimir / Salvar PDF
    </button>

    <?php if ($total > 0) { ?>

        <a class="botao baixar"
           href="?data=<?php echo urlencode($data); ?>&baixar=1">
            Baixar CSV (Excel)
        </a>

    <?php } ?>

</div>

<!-- RELATÓRIO -->
<div class="relatorio">

    <div class="titulo-relatorio">

        <h2>Logística Fácil</h2>

        <p>Relatório de Fretes</p>

        <strong>
            Data: <?php echo $dataFormatada; ?>
        </strong>

    </div>

    <h2>Resumo do dia</h2>

    <div class="resumo">

        <div class="card">
            <h3>Total de fretes</h3>
            <p><?php echo $total; ?></p>
        </div>

        <div class="card">
            <h3>Pendentes</h3>
            <p><?php echo $pendentes; ?></p>
        </div>

        <div class="card">
            <h3>Em andamento</h3>
            <p><?php echo $andamento; ?></p>
        </div>

        <div class="card">
            <h3>Concluídos</h3>
            <p><?php echo $concluidos; ?></p>
        </div>

    </div>

    <h2>Fretes cadastrados</h2>

    <?php if ($total > 0) { ?>

        <div class="tabela">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Transportadora</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Produtos</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($fretes as $f) { ?>

                        <tr>

                            <td>
                                <?php echo (int)$f['id']; ?>
                            </td>

                            <td>
                                <?php
                                echo date(
                                    "d/m/Y",
                                    strtotime($f['data_frete'])
                                );
                                ?>
                            </td>

                            <td>
                                <?php echo mostrar($f['transportadora']); ?>
                            </td>

                            <td>
                                <?php echo mostrar($f['origem']); ?>
                            </td>

                            <td>
                                <?php echo mostrar($f['destino']); ?>
                            </td>

                            <td>
                                <?php echo mostrar($f['produtos'] ?? ''); ?>
                            </td>

                            <td>
                                <?php echo mostrar($f['status']); ?>
                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    <?php } else { ?>

        <p class="mensagem">
            Nenhum frete encontrado para esta data.
        </p>

    <?php } ?>

</div>

</body>
</html>

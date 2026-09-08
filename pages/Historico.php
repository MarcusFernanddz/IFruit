<?php
require_once __DIR__ . '/../connect/conexao.php';


/*
|--------------------------------------------------------------------------
| VERIFICA SE A TABELA EXISTE
|--------------------------------------------------------------------------
*/

function table_exists($con, $name)
{
    $name = mysqli_real_escape_string($con, $name);

    $res = mysqli_query(
        $con,
        "SHOW TABLES LIKE '$name'"
    );

    return $res && mysqli_num_rows($res) > 0;
}


/*
|--------------------------------------------------------------------------
| VERIFICA SE EXISTEM TABELAS DE VENDA
|--------------------------------------------------------------------------
*/

$hasSales =
    table_exists($con, 'venda') &&
    table_exists($con, 'itemvenda');


/*
|--------------------------------------------------------------------------
| PAGINAÇÃO
|--------------------------------------------------------------------------
*/

$page = max(
    1,
    intval($_GET['page'] ?? 1)
);

$perPage = 20;

$offset = ($page - 1) * $perPage;


/*
|--------------------------------------------------------------------------
| EXPORTAÇÃO
|--------------------------------------------------------------------------
*/

$export = $_GET['export'] ?? '';


if ($export === 'html' || $export === 'pdf') {

    if ($hasSales) {

        $all = mysqli_query(
            $con,
            "
            SELECT
                v.id_venda AS id,
                v.id_comprador,
                v.valortotal AS total,
                v.datavenda AS created_at,
                c.nome AS cliente

            FROM venda v

            LEFT JOIN comprador c
                ON c.id_comprador = v.id_comprador

            ORDER BY v.datavenda DESC
            "
        );

        $rows = [];

        if ($all) {

            while ($r = mysqli_fetch_assoc($all)) {

                $rows[] = $r;
            }
        }

    } else {

        $rows = [];

        $csvFile =
            __DIR__ . '/../data/sales.csv';


        if (file_exists($csvFile)) {

            $lines = file(
                $csvFile,
                FILE_IGNORE_NEW_LINES |
                FILE_SKIP_EMPTY_LINES
            );


            foreach ($lines as $i => $ln) {

                if ($i === 0) {
                    continue;
                }


                $cols = str_getcsv($ln);


                $rows[] = [

                    'id' =>
                        $cols[0] ?? '',

                    'cliente' =>
                        $cols[1] ?? '',

                    'total' =>
                        $cols[2] ?? 0,

                    'created_at' =>
                        $cols[3] ?? '',

                    'items_json' =>
                        $cols[4] ?? '[]'
                ];
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PÁGINA DE IMPRESSÃO
    |--------------------------------------------------------------------------
    */

    ?>

    <!doctype html>

    <html lang="pt-BR">

    <head>

        <meta charset="utf-8">

        <title>
            Histórico de Vendas
        </title>


        <style>

            body {
                font-family:
                    Arial,
                    Helvetica,
                    sans-serif;

                padding: 30px;

                color: #222;
            }


            h2 {
                margin-bottom: 20px;
            }


            table {
                width: 100%;
                border-collapse: collapse;
            }


            th,
            td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }


            th {
                background: #0f3d2e;
                color: white;
            }

        </style>

    </head>


    <body>

        <h2>
            Histórico de Vendas
        </h2>


        <table>

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Cliente
                    </th>

                    <th>
                        Total
                    </th>

                    <th>
                        Data
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php foreach ($rows as $s): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $s['id']
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $s['cliente']
                                ?? 'Cliente não informado'
                            ) ?>
                        </td>


                        <td>

                            R$

                            <?= number_format(
                                floatval(
                                    $s['total']
                                ),
                                2,
                                ',',
                                '.'
                            ) ?>

                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $s['created_at']
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>


        <?php if ($export === 'pdf'): ?>

            <script>

                window.onload = function () {
                    window.print();
                };

            </script>

        <?php endif; ?>


    </body>

    </html>

    <?php

    exit;
}

?>


<!doctype html>

<html lang="pt-BR">


<head>

    <meta charset="utf-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >


    <title>
        Histórico de Vendas
    </title>


    <!-- SIDEBAR -->

    <link
        rel="stylesheet"
        href="../css/sidebar.css"
    >


    <style>


        /*
        ============================================================
        ÁREA PRINCIPAL
        ============================================================
        */

        .historico-container {

            margin-left: 270px;

            padding: 30px;

            width:
                calc(100% - 270px);

            min-height:
                100vh;

            box-sizing:
                border-box;

            background:
                #f8f9fa;
        }


        /*
        ============================================================
        CABEÇALHO
        ============================================================
        */

        .historico-header {

            display:
                flex;

            align-items:
                center;

            margin-bottom:
                20px;
        }


        .historico-header h2 {

            margin: 0;

            font-size:
                28px;

            color:
                #333;
        }


        /*
        ============================================================
        BOTÕES DE EXPORTAÇÃO
        ============================================================
        */

        .botoes-exportacao {

            display:
                flex;

            gap:
                10px;

            margin-bottom:
                20px;

            flex-wrap:
                wrap;
        }


        .btn-html,
        .btn-pdf {

            display:
                inline-block;

            padding:
                10px 16px;

            background:
                #0f3d2e;

            color:
                #fff;

            border-radius:
                6px;

            text-decoration:
                none;

            font-weight:
                bold;

            transition:
                0.2s;
        }


        .btn-html:hover,
        .btn-pdf:hover {

            background:
                #237523;
        }


        /*
        ============================================================
        CAIXA DA TABELA
        ============================================================
        */

        .tabela-box {

            background:
                #fff;

            border-radius:
                10px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.08);

            overflow-x:
                auto;
        }


        /*
        ============================================================
        TABELA
        ============================================================
        */

        .tabela-vendas {

            width:
                100%;

            min-width:
                700px;

            border-collapse:
                collapse;
        }


        .tabela-vendas th {

            padding:
                14px;

            background:
                #0f3d2e;

            color:
                white;

            text-align:
                left;
        }


        .tabela-vendas td {

            padding:
                14px;

            border-bottom:
                1px solid #eee;

            color:
                #444;
        }


        .tabela-vendas tbody tr:hover {

            background:
                #f1f8f1;
        }


        /*
        ============================================================
        BOTÃO VER
        ============================================================
        */

        .btn-ver {

            display:
                inline-block;

            padding:
                7px 14px;

            background:
                #09c306;

            color:
                white;

            text-decoration:
                none;

            border-radius:
                5px;

            font-size:
                14px;

            font-weight:
                bold;

            border:
                none;

            cursor:
                pointer;

            transition:
                0.2s;
        }


        .btn-ver:hover {

            background:
                #237523;
        }


        /*
        ============================================================
        MODAL
        ============================================================
        */

        .modal {

            display:
                none;

            position:
                fixed;

            z-index:
                9999;

            top:
                0;

            left:
                0;

            width:
                100%;

            height:
                100%;

            background:
                rgba(0, 0, 0, 0.55);

            align-items:
                center;

            justify-content:
                center;

            padding:
                20px;

            box-sizing:
                border-box;
        }


        /*
        ============================================================
        JANELA DO MODAL
        ============================================================
        */

        .modal-conteudo {

            width:
                100%;

            max-width:
                850px;

            max-height:
                90vh;

            overflow-y:
                auto;

            background:
                white;

            border-radius:
                12px;

            box-shadow:
                0 10px 40px
                rgba(0, 0, 0, 0.3);

            animation:
                aparecer 0.2s ease;
        }


        @keyframes aparecer {

            from {

                opacity:
                    0;

                transform:
                    scale(0.95);
            }

            to {

                opacity:
                    1;

                transform:
                    scale(1);
            }
        }


        /*
        ============================================================
        CABEÇALHO DO MODAL
        ============================================================
        */

        .modal-header {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            padding:
                20px 25px;

            background:
                #0f3d2e;

            color:
                white;

            border-radius:
                12px 12px 0 0;
        }


        .modal-header h3 {

            margin:
                0;

            font-size:
                22px;
        }


        /*
        ============================================================
        BOTÃO FECHAR
        ============================================================
        */

        .btn-fechar {

            width:
                35px;

            height:
                35px;

            border:
                none;

            border-radius:
                50%;

            background:
                rgba(255,255,255,0.2);

            color:
                white;

            font-size:
                22px;

            cursor:
                pointer;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;
        }


        .btn-fechar:hover {

            background:
                rgba(255,255,255,0.35);
        }


        /*
        ============================================================
        CORPO DO MODAL
        ============================================================
        */

        .modal-body {

            padding:
                25px;
        }


        /*
        ============================================================
        INFORMAÇÕES DA VENDA
        ============================================================
        */

        .informacoes-venda {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                15px;

            margin-bottom:
                25px;
        }


        .informacao {

            padding:
                15px;

            background:
                #f1f8f1;

            border-left:
                4px solid #2d8f2d;

            border-radius:
                6px;
        }


        .informacao-label {

            display:
                block;

            font-size:
                13px;

            color:
                #777;

            margin-bottom:
                5px;
        }


        .informacao-valor {

            font-size:
                16px;

            font-weight:
                bold;

            color:
                #333;
        }


        /*
        ============================================================
        TÍTULO DOS PRODUTOS
        ============================================================
        */

        .titulo-itens {

            margin:
                0 0 15px 0;

            color:
                #333;

            font-size:
                18px;
        }


        /*
        ============================================================
        TABELA DOS PRODUTOS
        ============================================================
        */

        .tabela-itens {

            width:
                100%;

            border-collapse:
                collapse;
        }


        .tabela-itens th {

            padding:
                12px;

            background:
                #0f3d2e;

            color:
                white;

            text-align:
                left;
        }


        .tabela-itens td {

            padding:
                12px;

            border-bottom:
                1px solid #eee;
        }


        .tabela-itens tbody tr:hover {

            background:
                #f1f8f1;
        }


        /*
        ============================================================
        RODAPÉ DO MODAL
        ============================================================
        */

        .modal-footer {

            display:
                flex;

            justify-content:
                flex-end;

            padding:
                15px 25px;

            border-top:
                1px solid #eee;
        }


        /*
        ============================================================
        BOTÃO FECHAR
        ============================================================
        */

        .btn-fechar-modal {

            padding:
                10px 20px;

            background:
                #0f3d2e;

            color:
                white;

            border:
                none;

            border-radius:
                6px;

            cursor:
                pointer;

            font-weight:
                bold;
        }


        .btn-fechar-modal:hover {

            background:
                #237523;
        }


        /*
        ============================================================
        RESPONSIVO
        ============================================================
        */

        @media (max-width: 768px) {

            .historico-container {

                margin-left:
                    0;

                width:
                    100%;

                padding:
                    20px;
            }


            .informacoes-venda {

                grid-template-columns:
                    1fr;
            }


            .modal {

                padding:
                    10px;
            }


            .modal-conteudo {

                max-height:
                    95vh;
            }


            .modal-body {

                padding:
                    15px;
            }

        }


    </style>

</head>


<body>


<?php

/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

$paginaAtiva = 'historico';

require_once 'sidebar.php';

?>


<!--
|--------------------------------------------------------------------------
| CONTEÚDO PRINCIPAL
|--------------------------------------------------------------------------
-->

<div class="historico-container">


    <!-- TÍTULO -->

    <div class="historico-header">

        <h2>
            Histórico de Vendas
        </h2>

    </div>


    <!-- BOTÕES -->

    <div class="botoes-exportacao">

        <a
            href="?export=html"
            target="_blank"
            class="btn-html"
        >
            Abrir como HTML
        </a>


        <a
            href="?export=pdf"
            target="_blank"
            class="btn-pdf"
        >
            Imprimir / Salvar PDF
        </a>

    </div>


    <!--
    ============================================================
    TABELA PRINCIPAL
    ============================================================
    -->

    <div class="tabela-box">

        <table class="tabela-vendas">

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Cliente
                    </th>

                    <th>
                        Total
                    </th>

                    <th>
                        Data
                    </th>

                    <th>
                        Ação
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php

            /*
            ========================================================
            BANCO DE DADOS
            ========================================================
            */

            if ($hasSales) {


                $sql = "

                    SELECT

                        v.id_venda AS id,

                        v.id_comprador,

                        v.valortotal AS total,

                        v.datavenda AS created_at,

                        c.nome AS cliente

                    FROM venda v

                    LEFT JOIN comprador c
                        ON c.id_comprador =
                           v.id_comprador

                    ORDER BY
                        v.datavenda DESC

                    LIMIT $perPage
                    OFFSET $offset

                ";


                $res =
                    mysqli_query(
                        $con,
                        $sql
                    );


                if (
                    $res &&
                    mysqli_num_rows($res) > 0
                ) {


                    while (
                        $s =
                        mysqli_fetch_assoc($res)
                    ) {

                        ?>


                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $s['id']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['cliente']
                                    ??
                                    'Cliente não informado'
                                ) ?>

                            </td>


                            <td>

                                R$

                                <?= number_format(
                                    floatval(
                                        $s['total']
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['created_at']
                                ) ?>

                            </td>


                            <td>

                                <!--
                                BOTÃO VER

                                Passa o ID da venda
                                para o JavaScript.
                                -->

                                <button
                                    type="button"
                                    class="btn-ver"
                                    onclick="abrirVenda(<?= intval($s['id']) ?>)"
                                >
                                    Ver
                                </button>

                            </td>

                        </tr>


                        <?php

                    }


                } else {

                    ?>


                    <tr>

                        <td
                            colspan="5"
                            style="
                                text-align:center;
                                padding:30px;
                            "
                        >

                            Nenhuma venda encontrada.

                        </td>

                    </tr>


                    <?php

                }


            }


            /*
            ========================================================
            CSV
            ========================================================
            */

            else {


                $rows = [];

                $csvFile =
                    __DIR__ .
                    '/../data/sales.csv';


                if (
                    file_exists($csvFile)
                ) {


                    $lines =
                        file(
                            $csvFile,
                            FILE_IGNORE_NEW_LINES |
                            FILE_SKIP_EMPTY_LINES
                        );


                    foreach (
                        $lines as $i => $ln
                    ) {


                        if ($i === 0) {

                            continue;

                        }


                        $cols =
                            str_getcsv($ln);


                        $rows[] = [

                            'id' =>
                                $cols[0] ?? '',

                            'cliente' =>
                                $cols[1] ?? '',

                            'total' =>
                                $cols[2] ?? 0,

                            'created_at' =>
                                $cols[3] ?? '',

                            'items_json' =>
                                $cols[4] ?? '[]'

                        ];

                    }

                }


                /*
                PAGINAÇÃO
                */

                $rows =
                    array_slice(
                        $rows,
                        $offset,
                        $perPage
                    );


                if (
                    count($rows) > 0
                ) {


                    foreach (
                        $rows as $s
                    ) {

                        ?>


                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $s['id']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['cliente']
                                    ?:
                                    'Cliente não informado'
                                ) ?>

                            </td>


                            <td>

                                R$

                                <?= number_format(
                                    floatval(
                                        $s['total']
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['created_at']
                                ) ?>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="btn-ver"
                                    onclick="abrirVenda(<?= intval($s['id']) ?>)"
                                >
                                    Ver
                                </button>

                            </td>

                        </tr>


                        <?php

                    }


                } else {

                    ?>


                    <tr>

                        <td
                            colspan="5"
                            style="
                                text-align:center;
                                padding:30px;
                            "
                        >

                            Nenhuma venda encontrada.

                        </td>

                    </tr>


                    <?php

                }

            }

            ?>


            </tbody>

        </table>

    </div>


</div>


<!--
==================================================================
MODAL DA VENDA
==================================================================
-->

<div
    id="modalVenda"
    class="modal"
>


    <div class="modal-conteudo">


        <!-- CABEÇALHO -->

        <div class="modal-header">


            <h3 id="modalTitulo">

                Detalhes da venda

            </h3>


            <button
                type="button"
                class="btn-fechar"
                onclick="fecharVenda()"
            >
                ×
            </button>


        </div>


        <!-- CORPO -->

        <div class="modal-body">


            <!-- INFORMAÇÕES -->

            <div class="informacoes-venda">


                <div class="informacao">

                    <span
                        class="informacao-label"
                    >
                        Cliente
                    </span>


                    <span
                        class="informacao-valor"
                        id="modalCliente"
                    >
                        -
                    </span>

                </div>


                <div class="informacao">

                    <span
                        class="informacao-label"
                    >
                        Data
                    </span>


                    <span
                        class="informacao-valor"
                        id="modalData"
                    >
                        -
                    </span>

                </div>


                <div class="informacao">

                    <span
                        class="informacao-label"
                    >
                        Total
                    </span>


                    <span
                        class="informacao-valor"
                        id="modalTotal"
                    >
                        -
                    </span>

                </div>


            </div>


            <!-- PRODUTOS -->

            <h4 class="titulo-itens">

                Produtos da venda

            </h4>


            <div
                style="
                    overflow-x:auto;
                "
            >

                <table class="tabela-itens">


                    <thead>

                        <tr>

                            <th>
                                Produto
                            </th>

                            <th>
                                Quantidade
                            </th>

                            <th>
                                Preço
                            </th>

                            <th>
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody id="modalItens">

                    </tbody>


                </table>

            </div>


        </div>


        <!-- RODAPÉ -->

        <div class="modal-footer">


            <button
                type="button"
                class="btn-fechar-modal"
                onclick="fecharVenda()"
            >

                Fechar

            </button>


        </div>


    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| DADOS DAS VENDAS
|--------------------------------------------------------------------------
|
| O PHP coloca aqui as vendas disponíveis.
|
*/

const vendas = {};


/*
|--------------------------------------------------------------------------
| BUSCA OS DADOS DO BANCO
|--------------------------------------------------------------------------
*/

<?php

if ($hasSales) {

    $todasVendas = mysqli_query(
        $con,
        "
        SELECT
            v.id_venda AS id,
            v.id_comprador,
            v.valortotal AS total,
            v.datavenda AS created_at,
            c.nome AS cliente

        FROM venda v

        LEFT JOIN comprador c
            ON c.id_comprador =
               v.id_comprador

        ORDER BY
            v.datavenda DESC
        "
    );


    if ($todasVendas) {

        while (
            $venda =
            mysqli_fetch_assoc($todasVendas)
        ) {


            $idVenda =
                intval(
                    $venda['id']
                );


            /*
            Busca os itens
            */

            $itensVenda =
                mysqli_query(
                    $con,
                    "
                    SELECT
                        iv.*,
                        f.nome

                    FROM itemvenda iv

                    LEFT JOIN fruta f
                        ON f.id_fruta =
                           iv.id_fruta

                    WHERE
                        iv.id_venda =
                        $idVenda
                    "
                );


            $itens = [];


            if ($itensVenda) {

                while (
                    $item =
                    mysqli_fetch_assoc(
                        $itensVenda
                    )
                ) {

                    $itens[] = [

                        'produto' =>
                            $item['nome']
                            ??
                            'Produto não informado',

                        'quantidade' =>
                            floatval(
                                $item['peso']
                                ??
                                0
                            ),

                        'preco' =>
                            floatval(
                                $item['preco']
                                ??
                                0
                            ),

                        'subtotal' =>
                            floatval(
                                ($item['peso'] ?? 0)
                                *
                                ($item['preco'] ?? 0)
                            )
                    ];
                }
            }


            $dadosVenda = [

                'id' =>
                    $idVenda,

                'cliente' =>
                    $venda['cliente']
                    ??
                    'Cliente não informado',

                'total' =>
                    floatval(
                        $venda['total']
                    ),

                'data' =>
                    $venda['created_at']
                    ??
                    '',

                'itens' =>
                    $itens
            ];


            ?>

            vendas[<?= $idVenda ?>] =
                <?= json_encode(
                    $dadosVenda,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ) ?>;

            <?php

        }
    }

} else {

    /*
    ================================================================
    VENDA PELO CSV
    ================================================================
    */

    $csvFile =
        __DIR__ .
        '/../data/sales.csv';


    if (file_exists($csvFile)) {

        $lines =
            file(
                $csvFile,
                FILE_IGNORE_NEW_LINES |
                FILE_SKIP_EMPTY_LINES
            );


        foreach (
            $lines as $i => $ln
        ) {


            if ($i === 0) {

                continue;

            }


            $cols =
                str_getcsv($ln);


            $idVenda =
                intval(
                    $cols[0] ?? 0
                );


            $itemsJson =
                $cols[4] ?? '[]';


            $itemsArr =
                json_decode(
                    $itemsJson,
                    true
                );


            if (
                !is_array($itemsArr)
            ) {

                $itemsArr = [];

            }


            $itens = [];


            foreach (
                $itemsArr as $item
            ) {

                $itens[] = [

                    'produto' =>
                        $item['fruta_id']
                        ??
                        'Produto',

                    'quantidade' =>
                        floatval(
                            $item['quantidade']
                            ??
                            0
                        ),

                    'preco' =>
                        floatval(
                            $item['preco_unit']
                            ??
                            0
                        ),

                    'subtotal' =>
                        floatval(
                            $item['subtotal']
                            ??
                            0
                        )
                ];
            }


            $dadosVenda = [

                'id' =>
                    $idVenda,

                'cliente' =>
                    $cols[1]
                    ??
                    'Cliente não informado',

                'total' =>
                    floatval(
                        $cols[2] ?? 0
                    ),

                'data' =>
                    $cols[3]
                    ??
                    '',

                'itens' =>
                    $itens

            ];


            ?>

            vendas[<?= $idVenda ?>] =
                <?= json_encode(
                    $dadosVenda,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                ) ?>;

            <?php

        }

    }

}

?>


/*
|--------------------------------------------------------------------------
| ABRIR VENDA
|--------------------------------------------------------------------------
*/

function abrirVenda(id)
{

    const venda =
        vendas[id];


    if (!venda) {

        alert(
            'Não foi possível encontrar os dados desta venda.'
        );

        return;
    }


    /*
    Título
    */

    document.getElementById(
        'modalTitulo'
    ).textContent =
        'Detalhes da venda #' +
        venda.id;


    /*
    Cliente
    */

    document.getElementById(
        'modalCliente'
    ).textContent =
        venda.cliente ||
        'Cliente não informado';


    /*
    Data
    */

    document.getElementById(
        'modalData'
    ).textContent =
        venda.data ||
        '-';


    /*
    Total
    */

    document.getElementById(
        'modalTotal'
    ).textContent =
        formatarMoeda(
            venda.total
        );


    /*
    Itens
    */

    const tabela =
        document.getElementById(
            'modalItens'
        );


    tabela.innerHTML = '';


    if (
        venda.itens &&
        venda.itens.length > 0
    ) {


        venda.itens.forEach(
            function(item)
            {

                const tr =
                    document.createElement(
                        'tr'
                    );


                tr.innerHTML = `

                    <td>
                        ${escaparHtml(
                            item.produto
                        )}
                    </td>

                    <td>
                        ${item.quantidade}
                    </td>

                    <td>
                        ${formatarMoeda(
                            item.preco
                        )}
                    </td>

                    <td>
                        ${formatarMoeda(
                            item.subtotal
                        )}
                    </td>

                `;


                tabela.appendChild(
                    tr
                );

            }
        );


    } else {


        tabela.innerHTML = `

            <tr>

                <td
                    colspan="4"
                    style="
                        text-align:center;
                        padding:20px;
                    "
                >

                    Nenhum produto encontrado
                    nesta venda.

                </td>

            </tr>

        `;

    }


    /*
    Mostra o modal
    */

    document.getElementById(
        'modalVenda'
    ).style.display =
        'flex';


    /*
    Impede rolagem da página
    */

    document.body.style.overflow =
        'hidden';

}


/*
|--------------------------------------------------------------------------
| FECHAR VENDA
|--------------------------------------------------------------------------
*/

function fecharVenda()
{

    document.getElementById(
        'modalVenda'
    ).style.display =
        'none';


    document.body.style.overflow =
        '';

}


/*
|--------------------------------------------------------------------------
| FECHAR CLICANDO FORA DA JANELA
|--------------------------------------------------------------------------
*/

document.getElementById(
    'modalVenda'
).addEventListener(
    'click',
    function(event)
    {

        if (
            event.target === this
        ) {

            fecharVenda();

        }

    }
);


/*
|--------------------------------------------------------------------------
| FECHAR COM ESC
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event)
    {

        if (
            event.key === 'Escape'
        ) {

            fecharVenda();

        }

    }
);


/*
|--------------------------------------------------------------------------
| FORMATAR MOEDA
|--------------------------------------------------------------------------
*/

function formatarMoeda(valor)
{

    return Number(
        valor || 0
    ).toLocaleString(
        'pt-BR',
        {
            style: 'currency',
            currency: 'BRL'
        }
    );

}


/*
|--------------------------------------------------------------------------
| EVITA HTML INJETADO NOS NOMES DOS PRODUTOS
|--------------------------------------------------------------------------
*/

function escaparHtml(texto)
{

    const div =
        document.createElement(
            'div'
        );

    div.textContent =
        texto ?? '';

    return div.innerHTML;

}

</script>


</body>

</html>

<?php

require_once __DIR__ . '/../connect/conexao.php';


/*
|--------------------------------------------------------------------------
| FUNÇÃO: VERIFICA SE A TABELA EXISTE
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
| VERIFICA AS TABELAS NECESSÁRIAS
|--------------------------------------------------------------------------
*/

$hasSales =
    table_exists($con, 'venda') &&
    table_exists($con, 'itemvenda');

$hasComprador =
    table_exists($con, 'comprador');

$hasFruta =
    table_exists($con, 'fruta');

$hasAdministrador =
    table_exists($con, 'administrador');


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

$offset =
    ($page - 1) * $perPage;


/*
|--------------------------------------------------------------------------
| TOTAL DE VENDAS
|--------------------------------------------------------------------------
*/

$totalVendas = 0;

if ($hasSales) {

    $resCount = mysqli_query(
        $con,
        "SELECT COUNT(*) AS total FROM venda"
    );

    if ($resCount) {

        $rowCount =
            mysqli_fetch_assoc(
                $resCount
            );

        $totalVendas =
            intval(
                $rowCount['total'] ?? 0
            );
    }
}


$totalPaginas =
    max(
        1,
        (int) ceil(
            $totalVendas / $perPage
        )
    );


/*
|--------------------------------------------------------------------------
| EXPORTAÇÃO HTML / PDF
|--------------------------------------------------------------------------
*/

$export =
    $_GET['export'] ?? '';


if (
    $export === 'html' ||
    $export === 'pdf'
) {

    $rows = [];


    if ($hasSales) {

        $sqlExport = "

            SELECT

                v.id_venda,

                v.id_administrador,

                v.id_comprador,

                v.valortotal,

                v.datavenda,

                v.numrecib,

                v.formapag,

                v.cliente_nome AS cliente,

                a.nome AS administrador

            FROM venda v

            LEFT JOIN administrador a
                ON a.id_administrador =
                   v.id_administrador

            ORDER BY

                v.datavenda DESC,

                v.id_venda DESC

        ";


        $all =
            mysqli_query(
                $con,
                $sqlExport
            );


        if ($all) {

            while (
                $r =
                mysqli_fetch_assoc($all)
            ) {

                $rows[] = $r;
            }
        }
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


        <style>

            * {
                box-sizing: border-box;
            }


            body {

                font-family:
                    Arial,
                    Helvetica,
                    sans-serif;

                padding:
                    30px;

                color:
                    #222;

                background:
                    #fff;
            }


            h2 {

                margin-top:
                    0;

                margin-bottom:
                    20px;

                color:
                    #0f3d2e;
            }


            table {

                width:
                    100%;

                border-collapse:
                    collapse;
            }


            th,
            td {

                padding:
                    10px;

                border:
                    1px solid #ddd;

                text-align:
                    left;
            }


            th {

                background:
                    #0f3d2e;

                color:
                    white;
            }


            tr:nth-child(even) {

                background:
                    #f7f7f7;
            }


            @media print {

                body {

                    padding:
                        10px;
                }


                .nao-imprimir {

                    display:
                        none;
                }

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

                    <th>
                        Forma de pagamento
                    </th>

                    <th>
                        Recibo
                    </th>

                    <th>
                        Administrador
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (count($rows) > 0): ?>

                    <?php foreach ($rows as $s): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars(
                                    $s['id_venda'] ?? ''
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
                                        $s['valortotal']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $s['datavenda']
                                    ?? ''
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $s['formapag']
                                    ?? '-'
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $s['numrecib']
                                    ?? '-'
                                ) ?>
                            </td>


                            <td>
                                <?= htmlspecialchars(
                                    $s['administrador']
                                    ?? '-'
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            style="
                                text-align:center;
                                padding:30px;
                            "
                        >

                            Nenhuma venda encontrada.

                        </td>

                    </tr>

                <?php endif; ?>

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


    <link
        rel="stylesheet"
        href="../css/sidebar.css"
    >


    <style>

        /*
        ============================================================
        RESET
        ============================================================
        */

        * {

            box-sizing:
                border-box;

        }


        /*
        ============================================================
        BODY
        ============================================================
        */

        body {

            margin:
                0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                #f8f9fa;

            color:
                #333;

            transition:
                background .25s,
                color .25s;
        }


        /*
        ============================================================
        CONTAINER PRINCIPAL
        ============================================================
        */

        .historico-container {

            margin-left:
                270px;

            padding:
                30px;

            width:
                calc(100% - 270px);

            min-height:
                100vh;
        }


        /*
        ============================================================
        CABEÇALHO
        ============================================================
        */

        .historico-header {

            display:
                flex;

            justify-content:
                space-between;

            align-items:
                center;

            margin-bottom:
                20px;

            gap:
                20px;

            flex-wrap:
                wrap;
        }


        .historico-header h2 {

            margin:
                0;

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
                .2s;
        }


        .btn-html:hover,
        .btn-pdf:hover {

            background:
                #237523;
        }


        /*
        ============================================================
        TABELA
        ============================================================
        */

        .tabela-box {

            background:
                #fff;

            border-radius:
                10px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, .08);

            overflow-x:
                auto;

            transition:
                background .25s,
                box-shadow .25s;
        }


        .tabela-vendas {

            width:
                100%;

            min-width:
                950px;

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

            transition:
                color .25s,
                border-color .25s;
        }


        .tabela-vendas tbody tr:hover {

            background:
                #f1f8f1;
        }


        /*
        ============================================================
        BOTÃO VER - MODO CLARO
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
                .2s;
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
                rgba(0, 0, 0, .55);

            align-items:
                center;

            justify-content:
                center;

            padding:
                20px;
        }


        .modal-conteudo {

            width:
                100%;

            max-width:
                950px;

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
                rgba(0, 0, 0, .3);

            animation:
                aparecer .2s ease;

            transition:
                background .25s,
                color .25s;
        }


        @keyframes aparecer {

            from {

                opacity:
                    0;

                transform:
                    scale(.95);
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
        BOTÃO X
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
                rgba(255,255,255,.2);

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

            transition:
                .2s;
        }


        .btn-fechar:hover {

            background:
                rgba(255,255,255,.35);

        }


        /*
        ============================================================
        CORPO MODAL
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

            transition:
                background .25s;
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

            word-break:
                break-word;
        }


        /*
        ============================================================
        PRODUTOS
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

            color:
                #444;
        }


        .tabela-itens tbody tr:hover {

            background:
                #f1f8f1;
        }


        /*
        ============================================================
        PAGINAÇÃO
        ============================================================
        */

        .paginacao {

            display:
                flex;

            justify-content:
                center;

            align-items:
                center;

            gap:
                8px;

            margin-top:
                25px;

            flex-wrap:
                wrap;
        }


        .paginacao a,
        .paginacao span {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            min-width:
                38px;

            height:
                38px;

            padding:
                0 10px;

            border-radius:
                6px;

            text-decoration:
                none;

            background:
                white;

            color:
                #0f3d2e;

            border:
                1px solid #ddd;

            font-weight:
                bold;

            transition:
                .2s;
        }


        .paginacao .ativa {

            background:
                #0f3d2e;

            color:
                white;

            border-color:
                #0f3d2e;
        }


        /*
        ============================================================
        MODO ESCURO
        ============================================================
        */

        body.dark-mode {

            background:
                #121212;

            color:
                #eee;
        }


        body.dark-mode .historico-header h2 {

            color:
                #fff;
        }


        body.dark-mode .tabela-box {

            background:
                #1e1e1e;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, .35);
        }


        body.dark-mode .tabela-vendas td {

            color:
                #ddd;

            border-bottom-color:
                #333;
        }


        body.dark-mode .tabela-vendas tbody tr:hover {

            background:
                #252525;
        }


        /*
        ============================================================
        BOTÃO VER NO MODO ESCURO
        ============================================================
        */

        body.dark-mode .btn-ver {

            background:
                #16a816;

            color:
                #fff;
        }


        body.dark-mode .btn-ver:hover {

            background:
                #1fc51f;
        }


        /*
        ============================================================
        MODAL MODO ESCURO
        ============================================================
        */

        body.dark-mode .modal-conteudo {

            background:
                #1e1e1e;

            color:
                #eee;
        }


        body.dark-mode .informacao {

            background:
                #252525;

            border-left-color:
                #16a816;
        }


        body.dark-mode .informacao-label {

            color:
                #aaa;
        }


        body.dark-mode .informacao-valor {

            color:
                #fff;
        }


        body.dark-mode .titulo-itens {

            color:
                #fff;
        }


        body.dark-mode .tabela-itens td {

            color:
                #ddd;

            border-bottom-color:
                #333;
        }


        body.dark-mode .tabela-itens tbody tr:hover {

            background:
                #252525;
        }


        /*
        ============================================================
        PAGINAÇÃO MODO ESCURO
        ============================================================
        */

        body.dark-mode .paginacao a,
        body.dark-mode .paginacao span {

            background:
                #1e1e1e;

            color:
                #fff;

            border-color:
                #444;
        }


        body.dark-mode .paginacao .ativa {

            background:
                #0f3d2e;

            color:
                #fff;

            border-color:
                #0f3d2e;
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

$paginaAtiva =
    'historico';

require_once 'sidebar.php';

?>


<div class="historico-container">


    <!--
    ==================================================================
    CABEÇALHO
    ==================================================================
    -->

    <div class="historico-header">

        <h2>
            Histórico de Vendas
        </h2>

    </div>


    <!--
    ==================================================================
    BOTÕES
    ==================================================================
    -->

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
    ==================================================================
    TABELA PRINCIPAL
    ==================================================================
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
                        Pagamento
                    </th>

                    <th>
                        Recibo
                    </th>

                    <th>
                        Ação
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            /*
            |--------------------------------------------------------------------------
            | BUSCA VENDAS
            |--------------------------------------------------------------------------
            */

            if ($hasSales) {


                $sql = "

                    SELECT

                        v.id_venda,

                        v.id_administrador,

                        v.id_comprador,

                        v.valortotal,

                        v.datavenda,

                        v.numrecib,

                        v.formapag,

                        v.cliente_nome AS cliente,

                        a.nome AS administrador

                    FROM venda v

                    LEFT JOIN administrador a
                        ON a.id_administrador =
                           v.id_administrador

                    ORDER BY

                        v.datavenda DESC,

                        v.id_venda DESC

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
                        mysqli_fetch_assoc(
                            $res
                        )
                    ) {

                        ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $s['id_venda']
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
                                        $s['valortotal']
                                        ?? 0
                                    ),
                                    2,
                                    ',',
                                    '.'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['datavenda']
                                    ?? ''
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['formapag']
                                    ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $s['numrecib']
                                    ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="btn-ver"
                                    onclick="abrirVenda(<?= intval($s['id_venda']) ?>)"
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
                            colspan="7"
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


            } else {

                ?>

                <tr>

                    <td
                        colspan="7"
                        style="
                            text-align:center;
                            padding:30px;
                        "
                    >

                        As tabelas
                        <strong>venda</strong>
                        e
                        <strong>itemvenda</strong>
                        não foram encontradas.

                    </td>

                </tr>

                <?php

            }

            ?>

            </tbody>

        </table>

    </div>


    <!--
    ==================================================================
    PAGINAÇÃO
    ==================================================================
    -->

    <?php if ($totalPaginas > 1): ?>

        <div class="paginacao">

            <?php if ($page > 1): ?>

                <a
                    href="?page=<?= $page - 1 ?>"
                >
                    ‹
                </a>

            <?php endif; ?>


            <?php

            for (
                $i = 1;
                $i <= $totalPaginas;
                $i++
            ):

            ?>

                <a
                    href="?page=<?= $i ?>"
                    class="<?= $i === $page ? 'ativa' : '' ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>


            <?php if ($page < $totalPaginas): ?>

                <a
                    href="?page=<?= $page + 1 ?>"
                >
                    ›
                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>


</div>


<!--
==================================================================
MODAL
==================================================================
-->

<div
    id="modalVenda"
    class="modal"
>

    <div class="modal-conteudo">


        <!--
        ==============================================================
        CABEÇALHO
        ==============================================================
        -->

        <div class="modal-header">

            <h3 id="modalTitulo">

                Detalhes da venda

            </h3>


            <!--
            ==========================================================
            ÚNICO BOTÃO PARA FECHAR
            ==========================================================
            -->

            <button
                type="button"
                class="btn-fechar"
                onclick="fecharVenda()"
                aria-label="Fechar"
            >

                ×

            </button>

        </div>


        <!--
        ==============================================================
        CORPO
        ==============================================================
        -->

        <div class="modal-body">


            <div class="informacoes-venda">


                <!-- CLIENTE -->

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


                <!-- DATA -->

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


                <!-- TOTAL -->

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


                <!-- PAGAMENTO -->

                <div class="informacao">

                    <span
                        class="informacao-label"
                    >
                        Forma de pagamento
                    </span>


                    <span
                        class="informacao-valor"
                        id="modalPagamento"
                    >
                        -
                    </span>

                </div>


                <!-- RECIBO -->

                <div class="informacao">

                    <span
                        class="informacao-label"
                    >
                        Número do recibo
                    </span>


                    <span
                        class="informacao-valor"
                        id="modalRecibo"
                    >
                        -
                    </span>

                </div>


                <!-- ADMINISTRADOR -->

                <div class="informacao">

                    <span
                        class="informacao-label"
                    >
                        Administrador
                    </span>


                    <span
                        class="informacao-valor"
                        id="modalAdministrador"
                    >
                        -
                    </span>

                </div>


            </div>


            <!--
            ==========================================================
            PRODUTOS
            ==========================================================
            -->

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
                                Preço / kg
                            </th>

                            <th>
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody
                        id="modalItens"
                    >

                    </tbody>

                </table>

            </div>


        </div>


    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| OBJETO QUE RECEBERÁ AS VENDAS DO PHP
|--------------------------------------------------------------------------
*/

const vendas = {};


/*
|--------------------------------------------------------------------------
| ENVIA OS DADOS DO PHP PARA O JAVASCRIPT
|--------------------------------------------------------------------------
*/

<?php

if ($hasSales) {


    $todasVendas =
        mysqli_query(
            $con,

            "

            SELECT

                v.id_venda,

                v.id_administrador,

                v.id_comprador,

                v.valortotal,

                v.datavenda,

                v.numrecib,

                v.formapag,

                v.cliente_nome AS cliente,

                a.nome AS administrador

            FROM venda v

            LEFT JOIN administrador a
                ON a.id_administrador =
                   v.id_administrador

            ORDER BY

                v.datavenda DESC,

                v.id_venda DESC

            "
        );


    if ($todasVendas) {


        while (
            $venda =
            mysqli_fetch_assoc(
                $todasVendas
            )
        ) {


            $idVenda =
                intval(
                    $venda['id_venda']
                );


            $itensVenda =
                mysqli_query(

                    $con,

                    "

                    SELECT

                        iv.id_itemvenda,

                        iv.id_venda,

                        iv.id_fruta,

                        iv.nome AS nome_item,

                        iv.peso,

                        iv.preco

                    FROM itemvenda iv

                    WHERE

                        iv.id_venda =
                        $idVenda

                    ORDER BY

                        iv.id_itemvenda ASC

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


                    $nomeProduto =
                        'Produto não informado';


                    if (
                        !empty(
                            $item['nome_item']
                        )
                    ) {

                        $nomeProduto =
                            $item['nome_item'];

                    } elseif (
                        !empty(
                            $item['nome_fruta']
                        )
                    ) {

                        $nomeProduto =
                            $item['nome_fruta'];
                    }


                    $peso =
                        floatval(
                            $item['peso']
                            ?? 0
                        );


                    $preco =
                        floatval(
                            $item['preco']
                            ?? 0
                        );


                    $subtotal =
                        $peso * $preco;


                    $itens[] = [

                        'id_fruta' =>
                            intval(
                                $item['id_fruta']
                                ?? 0
                            ),

                        'produto' =>
                            $nomeProduto,

                        'quantidade' =>
                            $peso,

                        'preco' =>
                            $preco,

                        'subtotal' =>
                            $subtotal
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
                        $venda['valortotal']
                        ?? 0
                    ),

                'data' =>
                    $venda['datavenda']
                    ??
                    '',

                'pagamento' =>
                    $venda['formapag']
                    ??
                    '-',

                'recibo' =>
                    $venda['numrecib']
                    ??
                    '-',

                'administrador' =>
                    $venda['administrador']
                    ??
                    'Administrador não informado',

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
| FUNÇÃO: ABRIR VENDA
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


    document.getElementById(
        'modalTitulo'
    ).textContent =

        'Detalhes da venda #' +
        venda.id;


    document.getElementById(
        'modalCliente'
    ).textContent =

        venda.cliente ||
        'Cliente não informado';


    document.getElementById(
        'modalData'
    ).textContent =

        venda.data ||
        '-';


    document.getElementById(
        'modalTotal'
    ).textContent =

        formatarMoeda(
            venda.total
        );


    document.getElementById(
        'modalPagamento'
    ).textContent =

        venda.pagamento ||
        '-';


    document.getElementById(
        'modalRecibo'
    ).textContent =

        venda.recibo ||
        '-';


    document.getElementById(
        'modalAdministrador'
    ).textContent =

        venda.administrador ||
        '-';


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


                const quantidade =
                    Number(
                        item.quantidade || 0
                    ).toLocaleString(
                        'pt-BR',
                        {
                            minimumFractionDigits: 3,
                            maximumFractionDigits: 3
                        }
                    );


                tr.innerHTML = `

                    <td>
                        ${escaparHtml(
                            item.produto
                        )}
                    </td>

                    <td>
                        ${quantidade} kg
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
    |--------------------------------------------------------------------------
    | MOSTRA MODAL
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'modalVenda'
    ).style.display =
        'flex';


    document.body.style.overflow =
        'hidden';

}


/*
|--------------------------------------------------------------------------
| FUNÇÃO: FECHAR VENDA
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
| FECHAR CLICANDO FORA
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
| FORMATA MOEDA
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
| EVITA HTML INJETADO
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
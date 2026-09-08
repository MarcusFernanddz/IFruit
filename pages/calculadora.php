<?php
$paginaAtiva = 'calculadora';
include __DIR__ . '/../connect/conexao.php';

$itens = [];
$totalGeral = 0;
$erro = "";

// lista de frutas ordenada para o autocomplete
$listaFrutas = mysqli_query($conn, "SELECT id_fruta, nome, precokg FROM fruta ORDER BY nome");

if (isset($_POST['frutas']) && is_array($_POST['frutas'])) {

    $postFrutas = $_POST['frutas'];
    // segurança: `quantidade` pode não estar presente ou não ser array
    if (isset($_POST['quantidade']) && is_array($_POST['quantidade'])) {
        $listaQtds = $_POST['quantidade'];
    } else {
        // preenche com zeros garantindo índices compatíveis
        $listaQtds = array_fill(0, count($postFrutas), 0);
    }

    foreach ($postFrutas as $i => $id_fruta) {

        $qtd = isset($listaQtds[$i]) ? floatval($listaQtds[$i]) : 0;

        if ($id_fruta === "" || $qtd <= 0) {
            continue;
        }

        $sql = "SELECT * FROM fruta WHERE id_fruta = '$id_fruta'";
        $result = mysqli_query($conn, $sql);

        if ($row = mysqli_fetch_assoc($result)) {
            $subtotal = $row['precokg'] * $qtd;
            $totalGeral += $subtotal;

            $itens[] = [
                'nome'     => $row['nome'],
                'precokg'  => $row['precokg'],
                'qtd'      => $qtd,
                'subtotal' => $subtotal,
            ];
        }
    }

    if (empty($itens)) {
        $erro = "Nenhum item válido foi informado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Preços</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/calculadora.css?v=2">
    <style>
        /* =========================================================
           calculadora.css — Estilos exclusivos da página Calculadora
           Segue o mesmo padrão visual das demais páginas do iFruit:
           fundo claro, painel lateral verde, cartão verde de conteúdo
           e botões no verde de destaque (#1ed760).
           ========================================================= */

        .main .calc-titulo, .main .calc-card, .main .calc-linhas, .main .calc-linha,
        .main .calc-btn-add, .main .calc-btn-remover, .main .calc-resultado {
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .main .calc-titulo {
            width: 100%;
            height: 60px;
            background: #d9d9d9;
            display: flex;
            align-items: center;
            padding: 0 25px;
            border-radius: 10px;
            border-bottom: 1px solid #bdbdbd;
            margin-bottom: 30px;
        }

        .calc-titulo p {
            font-size: 22px;
            font-weight: bold;
            color: #0f3d2e;
        }

        .calc-card {
            width: 100%;
            max-width: 800px;
            background: #3a5f4c;
            border-radius: 12px;
            padding: 28px 25px;
            color: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .calc-card h2 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .calc-card form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .calc-card label {
            font-size: 13px;
            font-weight: 600;
            color: #cde8d9;
            margin-bottom: -8px;
        }

        .calc-card select,
        .calc-card input[type="number"],
        .calc-card input[type="text"] {
            padding: 12px;
            border: 2px solid transparent;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            font-family: inherit;
            background: #fff;
            color: #222;
        }

        .calc-card select:focus,
        .calc-card input:focus {
            border-color: #1ed760;
        }

        .calc-card input[type="submit"] {
            width: 200px;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #1ed760;
            color: #0f3d2e;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        .calc-card input[type="submit"]:hover {
            background: #18b84f;
            transform: translateY(-1px);
        }

        .calc-linhas {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .calc-linha {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .calc-linha .calc-select-fruta {
            flex: 2;
        }

        .calc-linha input[type="number"] {
            flex: 0 0 180px;
            min-width: 140px;
            padding: 14px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background: #fff;
            color: #222;
            font-size: 16px;
        }

        .calc-btn-remover {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            transition: 0.2s;
        }

        .calc-btn-remover:hover {
            background: #ff6b6b;
        }

        .calc-btn-add {
            align-self: flex-start;
            padding: 9px 16px;
            border: 1px dashed #1ed760;
            border-radius: 6px;
            background: transparent;
            color: #1ed760;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .calc-btn-add:hover {
            background: rgba(30, 215, 96, 0.12);
        }

        .calc-resultado {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 15px;
            line-height: 1.7;
        }

        .calc-resultado strong {
            color: #1ed760;
        }

        .calc-resultado.calc-erro {
            color: #ffb3b3;
        }

        .calc-lista-itens {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        .calc-lista-itens li {
            font-size: 14px;
            color: #cde8d9;
        }

        .calc-total-geral {
            font-size: 20px;
            font-weight: bold;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        @media (max-width: 480px) {
            .calc-linha {
                flex-wrap: wrap;
            }

            .calc-linha .calc-select-fruta,
            .calc-linha input[type="number"] {
                flex: 1 1 100%;
            }
        }

        @media (max-width: 768px) {
            .calc-card {
                max-width: 100%;
            }
        }

        .lista-frutas,
        .lista-frutas .item-fruta {
            color: #1f2d2a;
        }

        .lista-frutas .item-fruta {
            background: #fff;
        }

        .lista-frutas .item-fruta .match {
            font-weight: 700;
            color: #1ed760;
        }

        .lista-frutas .item-fruta.focused, .lista-frutas .item-fruta:hover {
            background: #e8f7ef;
        }

        .autocomplete { position: relative; width: 100%; }
        .lista-frutas { position: absolute; left: 0; right: 0; top: calc(100% + 6px); max-height: 220px; overflow: auto; border-radius: 8px; border: 1px solid rgba(0,0,0,0.06); background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.06); display: none; z-index: 1100; }
        .lista-frutas .item-fruta { padding: 8px 10px; border-bottom: 1px solid #f4f4f4; cursor: pointer; background: #fff; }
        .calc-select-fruta-input { padding: 8px; width: 100%; border-radius: 6px; border: 1px solid #ddd; background: #fff; }
    </style>
</head>

<body>

    <?php require_once 'sidebar.php'; ?>

    <main class="main">

        <div class="calc-titulo">
            <p>Calculadora</p>
        </div>

        <div class="calc-card">
            <h2>Calcular conta</h2>

            <form method="post" id="form-calculadora">

                <div class="calc-linhas" id="calc-linhas">

                    <div class="calc-linha">
                        <div class="autocomplete">
                            <input type="text" class="calc-select-fruta-input" placeholder="Pesquisar fruta..." autocomplete="off">
                            <input type="hidden" name="frutas[]" class="fruta-id-input" value="">
                            <div class="lista-frutas">
                                <?php
                                mysqli_data_seek($listaFrutas, 0);
                                while ($fr = mysqli_fetch_assoc($listaFrutas)):
                                    $precoFmt = number_format($fr['precokg'], 2, ',', '.');
                                ?>
                                    <div class="item-fruta" data-id="<?= $fr['id_fruta'] ?>">
                                        <?= htmlspecialchars($fr['nome']) ?> — R$ <?= $precoFmt ?>/kg
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <input type="number" step="0.01" name="quantidade[]" placeholder="Qtd (kg)" required>
                        <button type="button" class="calc-btn-remover">&times;</button>
                    </div>

                </div>

                <button type="button" class="calc-btn-add">+ Adicionar item</button>

                <input type="submit" value="Calcular total">

            </form>

            <?php if ($erro): ?>
                <div class="calc-resultado calc-erro"><?= $erro ?></div>
            <?php elseif (!empty($itens)): ?>
                <div class="calc-resultado">
                    <ul class="calc-lista-itens">
                        <?php foreach ($itens as $item): ?>
                            <li>
                                <?= $item['nome'] ?> — <?= $item['qtd'] ?> kg
                                × R$ <?= number_format($item['precokg'], 2, ',', '.') ?>
                                = <strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="calc-total-geral">
                        Total geral: <strong>R$ <?= number_format($totalGeral, 2, ',', '.') ?></strong>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </main>

            <template id="template-linha">
        <div class="calc-linha">
            <div class="autocomplete">
                <input type="text" class="calc-select-fruta-input" placeholder="Pesquisar fruta..." autocomplete="off">
                <input type="hidden" name="frutas[]" class="fruta-id-input" value="">
                <div class="lista-frutas">
                <?php
                mysqli_data_seek($listaFrutas, 0);
                while ($fr = mysqli_fetch_assoc($listaFrutas)):
                    $precoFmt = number_format($fr['precokg'], 2, ',', '.');
                ?>
                    <div class="item-fruta" data-id="<?= $fr['id_fruta'] ?>">
                        <?= htmlspecialchars($fr['nome']) ?> — R$ <?= $precoFmt ?>/kg
                    </div>
                <?php endwhile; ?>
                </div>
            </div>
            <input type="number" step="0.01" name="quantidade[]" placeholder="Qtd (kg)" required>
            <button type="button" class="calc-btn-remover">&times;</button>
        </div>
            </template>

    <script>
        function adicionarLinha() {
            const template = document.getElementById('template-linha');
            const clone = template.content.cloneNode(true);
            document.getElementById('calc-linhas').appendChild(clone);
        }

        function removerLinha(botao) {
            const linhas = document.querySelectorAll('#calc-linhas .calc-linha');
            if (linhas.length > 1) {
                botao.closest('.calc-linha').remove();
            }
        }
    </script>

    <script>
        // Autocomplete for fruits per line
        function attachFrutaAutocomplete(line) {
            const input = line.querySelector('.calc-select-fruta-input');
            const hidden = line.querySelector('.fruta-id-input');
            const list = line.querySelector('.lista-frutas');
            const items = Array.from(list ? list.querySelectorAll('.item-fruta') : []);
            if (!input || !list) return;

            let focusIndex = -1;
            function visibleItems(){ return items.filter(i => i.style.display !== 'none'); }
            function clearFocus(){ items.forEach(it => it.classList.remove('focused')); focusIndex = -1; }
            function setFocus(idx){ const vis = visibleItems(); if (!vis.length) return; if (idx < 0) idx = 0; if (idx >= vis.length) idx = vis.length-1; clearFocus(); vis[idx].classList.add('focused'); focusIndex = idx; vis[idx].scrollIntoView({block:'nearest'}); }

            function escapeHtml(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

            input.addEventListener('focus', function(){
                items.forEach(function(el){ el.style.display = ''; el.innerHTML = escapeHtml(el.textContent.trim()); });
                if (items.length) list.style.display = 'block';
            });

            input.addEventListener('input', function(){
                const q = this.value.trim().toLowerCase();
                let any = false;
                items.forEach(function(el){
                    const txt = el.textContent.trim();
                    const low = txt.toLowerCase();
                    if (q.length >= 1 && low.indexOf(q) !== -1) {
                        const start = low.indexOf(q);
                        const end = start + q.length;
                        const before = txt.substring(0,start);
                        const match = txt.substring(start,end);
                        const after = txt.substring(end);
                        el.innerHTML = escapeHtml(before) + '<span class="match">' + escapeHtml(match) + '</span>' + escapeHtml(after);
                        el.style.display = '';
                        any = true;
                    } else {
                        el.style.display = 'none';
                        el.innerHTML = escapeHtml(el.textContent.trim());
                    }
                });
                list.style.display = any ? 'block' : 'none';
                clearFocus();
            });

            input.addEventListener('keydown', function(e){
                const vis = visibleItems();
                if (e.key === 'ArrowDown'){
                    e.preventDefault();
                    setFocus((focusIndex === -1) ? 0 : focusIndex+1);
                } else if (e.key === 'ArrowUp'){
                    e.preventDefault();
                    setFocus((focusIndex === -1) ? vis.length-1 : focusIndex-1);
                } else if (e.key === 'Enter'){
                    if (focusIndex !== -1){ e.preventDefault(); const sel = visibleItems()[focusIndex]; sel.click(); }
                } else if (e.key === 'Escape'){
                    list.style.display = 'none';
                }
            });

            items.forEach(function(el){
                el.addEventListener('click', function(){
                    const id = this.getAttribute('data-id');
                    hidden.value = id;
                    input.value = this.textContent.trim();
                    list.style.display = 'none';
                });
            });

            document.addEventListener('click', function(e){
                if (!e.target.closest('.lista-frutas') && !e.target.closest('.calc-select-fruta-input')) {
                    list.style.display = 'none';
                }
            });
        }

        // initialize for existing lines
        document.querySelectorAll('#calc-linhas .calc-linha').forEach(function(line){ attachFrutaAutocomplete(line); });

        // override adicionarLinha to attach autocomplete on new clone
        (function(){
            const originalAdicionar = window.adicionarLinha;
            window.adicionarLinha = function(){
                if (typeof originalAdicionar === 'function') originalAdicionar();
                const lines = document.querySelectorAll('#calc-linhas .calc-linha');
                const last = lines[lines.length - 1];
                if (last) attachFrutaAutocomplete(last);
            };
        })();

        // bind add button (no inline onclick to improve portability)
        document.addEventListener('DOMContentLoaded', function(){
            const btn = document.querySelector('.calc-btn-add');
            if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); window.adicionarLinha(); });
        });
    </script>

    <script>
    // Delegação para garantir funcionamento do botão remover e seleção de item
    document.addEventListener('click', function(e){
        // remover linha (botão ×)
        const rem = e.target.closest('.calc-btn-remover');
        if (rem) {
            const linhas = document.querySelectorAll('#calc-linhas .calc-linha');
            if (linhas.length > 1) {
                rem.closest('.calc-linha').remove();
            }
            return;
        }

        // selecionar item (quando clickar em .item-fruta)
        const item = e.target.closest('.item-fruta');
        if (item) {
            const container = item.closest('.autocomplete');
            if (container) {
                const hidden = container.querySelector('.fruta-id-input');
                const input = container.querySelector('.calc-select-fruta-input');
                if (hidden) hidden.value = item.getAttribute('data-id');
                if (input) input.value = item.textContent.trim();
                const lista = container.querySelector('.lista-frutas');
                if (lista) lista.style.display = 'none';
            }
        }
    });
    </script>

</body>
</html>
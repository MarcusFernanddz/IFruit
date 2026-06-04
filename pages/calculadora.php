<?php
require __DIR__ . '/../connect/conexao.php';

$result = mysqli_query($conn, "SELECT id_fruta, nome, precokg FROM fruta ORDER BY nome");
$frutas = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iFruit - Calculadora</title>
<link rel="stylesheet" href="../css/sidebar.css">
<link rel="stylesheet" href="../css/global.css">
<link rel="stylesheet" href="../css/calculadora.css">
</head>

<body>

<?php $paginaAtiva = 'calculadora'; require_once 'sidebar.php'; ?>

<main class="main">

    <div class="bloco1fundo">
        <p>Calculadora</p>
    </div>

    <button class="btn-calculo" id="btnCalculo">Cálculo</button>
    <hr class="divisor">

    <div class="accordion" id="accordionArea" style="display:none;">

        <div class="accordion-item open" id="acc-frutas">
            <div class="accordion-header" onclick="toggleAcc('acc-frutas')">
                Selecionar Frutas
                <span class="accordion-arrow">▼</span>
            </div>
            <div class="accordion-body">
                <ul class="lista-frutas">
                    <?php if (empty($frutas)): ?>
                        <li style="color:#ffcdd2;">Nenhuma fruta cadastrada.</li>
                    <?php else: ?>
                        <?php foreach ($frutas as $f): ?>
                            <li>
                                <input
                                    type="checkbox"
                                    id="chk-<?= $f['id_fruta'] ?>"
                                    value="<?= $f['id_fruta'] ?>"
                                    data-nome="<?= htmlspecialchars($f['nome']) ?>"
                                    data-preco="<?= $f['precokg'] ?>">
                                <label for="chk-<?= $f['id_fruta'] ?>">
                                    <?= htmlspecialchars($f['nome']) ?>
                                    — R$ <?= number_format($f['precokg'], 2, ',', '.') ?>/kg
                                </label>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <button class="btn-confirmar" onclick="confirmarFrutas()">Confirmar</button>
            </div>
        </div>

        <div class="accordion-item" id="acc-peso" style="display:none;">
            <div class="accordion-header" onclick="toggleAcc('acc-peso')">
                Definir Peso
                <span class="accordion-arrow">▼</span>
            </div>
            <div class="accordion-body">
                <div class="inputs-peso" id="inputsPeso"></div>
                <button class="btn-confirmar" onclick="confirmarPeso()">Confirmar</button>
            </div>
        </div>

        <div class="accordion-item" id="acc-resultado" style="display:none;">
            <div class="accordion-header" onclick="toggleAcc('acc-resultado')">
                Resultado
                <span class="accordion-arrow">▼</span>
            </div>
            <div class="accordion-body">
                <p style="font-size:13px;color:#cde8d9;margin-bottom:6px;">Preço final</p>
                <div class="resultado-valor" id="resultadoValor">R$ 0,00</div>
            </div>
        </div>

    </div>

</main>

<script>
let selecionadas = [];

document.getElementById("btnCalculo").addEventListener("click", function(){
    document.getElementById("accordionArea").style.display = "flex";
    abrirAcc("acc-frutas");
});

function confirmarFrutas(){
    const checks = document.querySelectorAll(".lista-frutas input[type='checkbox']:checked");
    selecionadas = Array.from(checks).map(c => ({
        id:    c.value,
        nome:  c.dataset.nome,
        preco: parseFloat(c.dataset.preco)
    }));
    if(selecionadas.length === 0){ alert("Selecione ao menos uma fruta."); return; }

    const container = document.getElementById("inputsPeso");
    container.innerHTML = "";
    selecionadas.forEach(f => {
        const div = document.createElement("div");
        div.className = "peso-linha";
        div.innerHTML = `
            <label>${f.nome}</label>
            <input type="number" id="peso-${f.id}" placeholder="Peso em kg" min="0" step="0.1">
        `;
        container.appendChild(div);
    });

    document.getElementById("acc-peso").style.display = "block";
    fecharAcc("acc-frutas");
    abrirAcc("acc-peso");
}

function confirmarPeso(){
    let total = 0, valido = true;
    selecionadas.forEach(f => {
        const input = document.getElementById("peso-" + f.id);
        const peso  = parseFloat(input.value);
        if(isNaN(peso) || peso <= 0){
            valido = false;
            input.style.border = "2px solid #ff6b6b";
        } else {
            input.style.border = "none";
            total += f.preco * peso;
        }
    });
    if(!valido){ alert("Preencha todos os pesos corretamente."); return; }

    document.getElementById("resultadoValor").textContent =
        "R$ " + total.toLocaleString("pt-BR", { minimumFractionDigits:2, maximumFractionDigits:2 });

    document.getElementById("acc-resultado").style.display = "block";
    fecharAcc("acc-peso");
    abrirAcc("acc-resultado");
}

function toggleAcc(id){ document.getElementById(id).classList.toggle("open"); }
function abrirAcc(id){  document.getElementById(id).classList.add("open"); }
function fecharAcc(id){ document.getElementById(id).classList.remove("open"); }
</script>
</body>
</html>

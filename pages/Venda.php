<?php
require_once __DIR__ . '/../connect/conexao.php';

function table_exists($con, $name){
  $name = mysqli_real_escape_string($con, $name);
  $res = mysqli_query($con, "SHOW TABLES LIKE '".$name."'");
  return $res && mysqli_num_rows($res) > 0;
}

// ensure required base tables exist (adapted to project schema)
$hasClientes = table_exists($con, 'comprador');
$hasFrutas = table_exists($con, 'fruta');
$hasSales = table_exists($con, 'venda') && table_exists($con, 'itemvenda');

$clientes = $hasClientes ? mysqli_query($con, "SELECT id_comprador, nome FROM comprador ORDER BY nome LIMIT 200") : null;
$frutas = $hasFrutas ? mysqli_query($con, "SELECT id_fruta, nome, precokg FROM fruta ORDER BY nome LIMIT 500") : null;
$numLinhas = max(1, min(8, intval($_POST['num_linhas'] ?? $_GET['linhas'] ?? 1)));
$numLinhas = isset($_POST['adicionar_linha']) ? min(8, $numLinhas + 1) : $numLinhas;
// flags para feedback após salvar
$saved = false;
$saved_total = 0.0;
$saleDateInput = trim($_POST['datavenda'] ?? '');
$saleDate = date('Y-m-d');
if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $saleDateInput)) {
  $dateParts = explode('/', $saleDateInput);
  if (checkdate((int) $dateParts[1], (int) $dateParts[0], (int) $dateParts[2])) {
    $saleDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
  }
} elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $saleDateInput)) {
  $dateParts = explode('-', $saleDateInput);
  if (checkdate((int) $dateParts[1], (int) $dateParts[2], (int) $dateParts[0])) {
    $saleDate = $saleDateInput;
  }
}
$customPayment = trim($_POST['formapag_custom'] ?? '');
$paymentMethod = $customPayment !== '' ? substr($customPayment, 0, 30) : ($_POST['formapag'] ?? 'Dinheiro');
// Handle POST save (DB when available, CSV fallback otherwise)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['adicionar_linha'])) {
  $cliente_name = $_POST['cliente_name'] ?? null;
  $items = [];
  if (isset($_POST['items'])) {
    $itemsRaw = $_POST['items'];
    $decoded = is_string($itemsRaw) ? json_decode($itemsRaw, true) : $itemsRaw;
    if (is_array($decoded) && array_key_exists('items', $decoded)) {
      $items = $decoded['items'];
      $cliente_name = $decoded['cliente_name'] ?? $cliente_name;
    } else {
      $items = is_array($decoded) ? $decoded : [];
    }
  } elseif (isset($_POST['fruta_id']) || isset($_POST['fruta_name'])) {
    $frutaIds = $_POST['fruta_id'] ?? [];
    $frutaNames = $_POST['fruta_name'] ?? [];
    $precos = $_POST['preco_unit'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];
    for ($i = 0; $i < count($quantidades); $i++) {
      $items[] = !empty($frutaIds[$i])
        ? ['fruta_id' => $frutaIds[$i], 'quantidade' => $quantidades[$i]]
        : ['fruta_name' => $frutaNames[$i] ?? '', 'quantidade' => $quantidades[$i], 'preco_unit' => $precos[$i] ?? 0];
    }
  }
  $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
  $cliente_nome = trim($_POST['cliente_name'] ?? $_POST['cliente_search'] ?? '');
  if ($hasClientes && $cliente_id > 0) {
    $stmtCliente = mysqli_prepare($con, "SELECT nome FROM comprador WHERE id_comprador = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtCliente, 'i', $cliente_id);
    mysqli_stmt_execute($stmtCliente);
    $clienteRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCliente));
    $cliente_nome = $clienteRow['nome'] ?? $cliente_nome;
  }
  if (!$hasSales && $cliente_name === null && $cliente_id === 0) {
    $cliente_name = $_POST['cliente_name'] ?? null;
  }

  if ($hasSales && $cliente_id === 0) {
    $error = 'Selecione um cliente para salvar no banco.';
  } else {
    if ($hasSales) {
        // save to DB using schema: venda / itemvenda
        // prepare items and compute total first
        $prepared = [];
        $total = 0.0;
        foreach ($items as $it) {
          $peso = floatval($it['quantidade'] ?? 0);
          $preco = 0.0;
          $nome = '';
          $id_fruta = null;
          if (!empty($it['fruta_id'])) {
            $id_fruta = intval($it['fruta_id']);
            if ($hasFrutas) {
              $r = mysqli_query($con, "SELECT nome, precokg FROM fruta WHERE id_fruta = " . $id_fruta . " LIMIT 1");
              $row = mysqli_fetch_assoc($r);
              if ($row) { $nome = $row['nome']; $preco = floatval($row['precokg']); }
            }
          } else {
            $nome = $it['fruta_name'] ?? '';
            $preco = floatval($it['preco_unit'] ?? 0);
          }
          $subtotal = $preco * $peso;
          $total += $subtotal;
          $prepared[] = ['id_fruta' => $id_fruta, 'nome' => $nome, 'peso' => $peso, 'preco' => $preco, 'subtotal' => $subtotal];
        }

        // determine administrador id; if missing, fallback to CSV
        $forceCsv = false;
        $id_adm = null;
        if (table_exists($con, 'administrador')) {
          $r = mysqli_query($con, "SELECT id_administrador FROM administrador WHERE id_administrador = 1 LIMIT 1");
          if ($r && mysqli_num_rows($r) > 0) {
            $id_adm = 1; // prefer id 1 per user test instruction
          } else {
            $r2 = mysqli_query($con, "SELECT id_administrador FROM administrador LIMIT 1");
            if ($r2 && mysqli_num_rows($r2) > 0) {
              $id_adm = intval(mysqli_fetch_assoc($r2)['id_administrador']);
            } else {
              // try to create a default administrator record (minimal) to satisfy FK
              $nomeDef = mysqli_real_escape_string($con, 'Administrador');
              $emailDef = mysqli_real_escape_string($con, 'admin@local');
              $senhaDef = mysqli_real_escape_string($con, '');
              $ins = mysqli_query($con, "INSERT INTO administrador (nome, email, senha) VALUES ('$nomeDef', '$emailDef', '$senhaDef')");
              if ($ins) {
                $id_adm = intval(mysqli_insert_id($con));
              } else {
                $forceCsv = true;
                $error = 'Nenhum administrador encontrado e não foi possível criar um administrador padrão. Salvando em CSV como fallback.';
              }
            }
          }
        } else {
          $forceCsv = true;
          $error = 'Tabela administrador ausente. Salvando em CSV como fallback.';
        }

        if ($forceCsv) {
          // fallback: save to CSV file in data/ (reuse similar logic to below)
          $dataDir = __DIR__ . '/../data';
          if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
          $csvFile = $dataDir . '/sales.csv';

          // enrich items with price if fruta available
          $total = 0.0;
          $itemsOut = [];
          foreach ($items as $it) {
            $fruta_id = intval($it['fruta_id'] ?? 0);
            $quantidade = floatval($it['quantidade'] ?? 0);
            $preco = 0.0;
              if ($hasFrutas) {
                $r = mysqli_query($con, "SELECT precokg FROM fruta WHERE id_fruta = " . $fruta_id . " LIMIT 1");
              $row = mysqli_fetch_assoc($r);
              $preco = $row ? floatval($row['precokg']) : 0.0;
            }
            $subtotal = $preco * $quantidade;
            $total += $subtotal;
            $itemsOut[] = ['fruta_id' => $fruta_id, 'quantidade' => $quantidade, 'preco_unit' => $preco, 'subtotal' => $subtotal];
          }

          // ensure CSV header
          $nextId = 1;
          if (!file_exists($csvFile)) {
            $h = fopen($csvFile, 'w');
            fputcsv($h, ['id','cliente_id','total','created_at','items_json']);
            fclose($h);
          } else {
            $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (count($lines) > 1) {
              $last = array_pop($lines);
              $cols = str_getcsv($last);
              if (!empty($cols[0])) $nextId = intval($cols[0]) + 1;
            }
          }

          $h = fopen($csvFile, 'a');
          $now = $saleDate . ' ' . date('H:i:s');
          $clientField = $cliente_name ? $cliente_name : $cliente_id;
          fputcsv($h, [$nextId, $clientField, number_format($total,2,'.',''), $now, json_encode($itemsOut, JSON_UNESCAPED_UNICODE)]);
          fclose($h);
          $saved = true;
          $saved_total = $total;
        }

        mysqli_begin_transaction($con);
        try {
          $datavenda = $saleDate;
          $numrecib = mt_rand(100000, 9999999);
          $formapag = $paymentMethod;

          $stmt = mysqli_prepare($con, "INSERT INTO venda (id_administrador, id_comprador, cliente_nome, valortotal, datavenda, numrecib, formapag) VALUES (?, ?, ?, ?, ?, ?, ?)");
          mysqli_stmt_bind_param($stmt, 'iisdsis', $id_adm, $cliente_id, $cliente_nome, $total, $datavenda, $numrecib, $formapag);
          mysqli_stmt_execute($stmt);
          $sale_id = mysqli_insert_id($con);

          $stmtItem = mysqli_prepare($con, "INSERT INTO itemvenda (id_venda, id_fruta, nome, peso, preco) VALUES (?, ?, ?, ?, ?)");
          foreach ($prepared as $p) {
            $fid = $p['id_fruta'] ?? null;
            $n = $p['nome'];
            $peso = $p['peso'];
            $preco = $p['preco'];
            mysqli_stmt_bind_param($stmtItem, 'iissd', $sale_id, $fid, $n, $peso, $preco);
            mysqli_stmt_execute($stmtItem);
          }

          mysqli_commit($con);
          $saved = true;
          $saved_total = $total;
        } catch (Exception $e) {
          mysqli_rollback($con);
          $error = $e->getMessage();
        }
    } else {
      // fallback: save to CSV file in data/
      $dataDir = __DIR__ . '/../data';
      if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
      $csvFile = $dataDir . '/sales.csv';

      // enrich items with price if frutas available
      $total = 0.0;
      $itemsOut = [];
      foreach ($items as $it) {
        $fruta_id = intval($it['fruta_id'] ?? 0);
        $quantidade = floatval($it['quantidade'] ?? 0);
        $preco = 0.0;
        if ($hasFrutas) {
          $r = mysqli_query($con, "SELECT precokg FROM fruta WHERE id_fruta = " . $fruta_id . " LIMIT 1");
          $row = mysqli_fetch_assoc($r);
          $preco = $row ? floatval($row['precokg']) : 0.0;
        }
        $subtotal = $preco * $quantidade;
        $total += $subtotal;
        $itemsOut[] = ['fruta_id' => $fruta_id, 'quantidade' => $quantidade, 'preco_unit' => $preco, 'subtotal' => $subtotal];
      }

      // ensure CSV header
      $nextId = 1;
      if (!file_exists($csvFile)) {
        $h = fopen($csvFile, 'w');
        fputcsv($h, ['id','cliente_id','total','created_at','items_json']);
        fclose($h);
      } else {
        // read last line to get id
        $lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) > 1) {
          $last = array_pop($lines);
          $cols = str_getcsv($last);
          if (!empty($cols[0])) $nextId = intval($cols[0]) + 1;
        }
      }

      $h = fopen($csvFile, 'a');
      $now = $saleDate . ' ' . date('H:i:s');
      $clientField = $cliente_name ? $cliente_name : $cliente_id;
      fputcsv($h, [$nextId, $clientField, number_format($total,2,'.',''), $now, json_encode($itemsOut, JSON_UNESCAPED_UNICODE)]);
      fclose($h);
      $saved = true;
      $saved_total = $total;
    }
  }
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>iFruit - Registrar Venda</title>
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/venda.css?v=<?= filemtime(__DIR__ . '/../css/venda.css') ?>">
</head>
<body>

<?php $paginaAtiva = 'venda'; require_once 'sidebar.php'; ?>

<main class="main">

  <div class="bloco1fundo">
    <p>Vendas / Registrar Venda</p>
  </div>

  <div class="bloco2fundo">
    <?php if (!empty($error)): ?><div class="mensagem erro"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($saved)): ?><div class="mensagem sucesso">Venda salva com sucesso. Total: R$ <?= number_format($saved_total,2,',','.') ?></div><?php endif; ?>

    <p>Nova Venda</p>
    <form id="saleForm" method="POST" class="formulario sales-form">
      <?php if ($clientes): ?>
        <datalist id="clientes_disponiveis">
          <?php mysqli_data_seek($clientes, 0); while ($c = mysqli_fetch_assoc($clientes)): ?>
            <option value="<?= htmlspecialchars($c['nome']) ?>" data-id="<?= intval($c['id_comprador']) ?>"><?= htmlspecialchars($c['nome']) ?></option>
          <?php endwhile; ?>
        </datalist>
      <?php endif; ?>

      <?php if ($frutas): ?>
        <datalist id="frutas_disponiveis">
          <?php mysqli_data_seek($frutas, 0); while ($f = mysqli_fetch_assoc($frutas)): ?>
            <option value="<?= htmlspecialchars($f['nome']) ?>" data-id="<?= intval($f['id_fruta']) ?>"><?= htmlspecialchars($f['nome']) ?> (R$ <?= number_format($f['precokg'],2,',','.') ?> /kg)</option>
          <?php endwhile; ?>
        </datalist>
      <?php endif; ?>

      <div class="field-group">
        <?php if ($clientes): ?>
          <label for="cliente_search">Cliente</label>
          <input id="cliente_search" list="clientes_disponiveis" type="search" name="cliente_search" class="pesquisa-datalist" placeholder="Pesquisar cliente por nome" value="<?= htmlspecialchars($_POST['cliente_search'] ?? '') ?>" required>
          <input type="hidden" name="cliente_id" id="cliente_id_hidden">
        <?php else: ?>
          <label for="cliente_name">Cliente</label>
          <input id="cliente_name" type="text" name="cliente_name" placeholder="Nome do cliente" required>
        <?php endif; ?>
      </div>

      <div class="field-group">
        <label for="datavenda">Data da venda</label>
        <input id="datavenda" type="text" name="datavenda" inputmode="numeric" placeholder="dd/mm/aaaa" value="<?= htmlspecialchars(date('d/m/Y', strtotime($saleDate))) ?>" required>
      </div>

      <div class="field-group payment-group">
        <label for="formapag">Forma de pagamento</label>
        <select id="formapag" name="formapag">
          <option value="Dinheiro" <?= ($_POST['formapag'] ?? 'Dinheiro') === 'Dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
          <option value="Pix" <?= ($_POST['formapag'] ?? '') === 'Pix' ? 'selected' : '' ?>>Pix</option>
          <option value="Cartão" <?= ($_POST['formapag'] ?? '') === 'Cartão' ? 'selected' : '' ?>>Cartão</option>
          <option value="Transferência" <?= ($_POST['formapag'] ?? '') === 'Transferência' ? 'selected' : '' ?>>Transferência</option>
        </select>
        <input id="formapag_custom" type="text" name="formapag_custom" maxlength="30" placeholder="Outra forma de pagamento (opcional)" value="<?= htmlspecialchars($customPayment) ?>">
      </div>

      <div id="items" class="sale-list">
        <?php for ($linha = 0; $linha < $numLinhas; $linha++): ?>
        <div class="sale-row">
          <?php if ($frutas): ?>
            <input type="search" name="fruta_search[]" list="frutas_disponiveis" class="fruta-search" placeholder="Pesquisar produto" value="<?= htmlspecialchars($_POST['fruta_search'][$linha] ?? '') ?>">
            <input type="hidden" name="fruta_id[]" class="fruta-id-hidden" value="<?= htmlspecialchars($_POST['fruta_id'][$linha] ?? '') ?>">
            <input type="number" name="quantidade[]" step="0.001" class="quantidade" placeholder="kg" value="<?= htmlspecialchars($_POST['quantidade'][$linha] ?? '1') ?>" min="0.001">
          <?php else: ?>
            <input type="text" name="fruta_name[]" class="fruta_input" placeholder="Produto (nome)" value="<?= htmlspecialchars($_POST['fruta_name'][$linha] ?? '') ?>">
            <input type="number" name="preco_unit[]" step="0.01" class="preco_unit" placeholder="preço" value="<?= htmlspecialchars($_POST['preco_unit'][$linha] ?? '') ?>">
            <input type="number" name="quantidade[]" step="0.001" class="quantidade" placeholder="kg" value="<?= htmlspecialchars($_POST['quantidade'][$linha] ?? '1') ?>" min="0.001">
          <?php endif; ?>
          <button type="button" class="remove-item" aria-label="Excluir item" title="Excluir item">&times;</button>
        </div>
        <?php endfor; ?>
      </div>

      <div class="row-actions">
        <?php if ($numLinhas < 8): ?><button type="submit" name="adicionar_linha" formnovalidate class="sale-add-item">+ Adicionar item</button><?php endif; ?>
      </div>

      <input type="hidden" name="num_linhas" value="<?= $numLinhas ?>">
      <div class="form-actions">
        <button type="submit" class="primary-button">Salvar Venda</button>
      </div>
    </form>

  </div>

</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const clienteInput = document.getElementById('cliente_search');
    const clienteHidden = document.getElementById('cliente_id_hidden');
    if (clienteInput && clienteHidden) {
      const clienteMap = {};
      document.querySelectorAll('#clientes_disponiveis option').forEach(function (option) {
        clienteMap[option.value.trim()] = option.dataset.id || '';
      });

      const syncCliente = function () {
        const valor = clienteInput.value.trim();
        clienteHidden.value = clienteMap[valor] || '';
      };

      clienteInput.addEventListener('input', syncCliente);
      clienteInput.addEventListener('change', syncCliente);
      syncCliente();
    }

    document.querySelectorAll('.fruta-search').forEach(function (input) {
      const hidden = input.parentElement.querySelector('.fruta-id-hidden');
      const frutaMap = {};
      document.querySelectorAll('#frutas_disponiveis option').forEach(function (option) {
        frutaMap[option.value.trim()] = option.dataset.id || '';
      });

      const syncFruta = function () {
        const valor = input.value.trim();
        if (hidden) hidden.value = frutaMap[valor] || '';
      };

      input.addEventListener('input', syncFruta);
      input.addEventListener('change', syncFruta);
      syncFruta();
    });

    const items = document.getElementById('items');
    const lineCount = document.querySelector('input[name="num_linhas"]');
    document.querySelectorAll('.remove-item').forEach(function (button) {
      button.addEventListener('click', function () {
        const rows = items.querySelectorAll('.sale-row');
        const row = button.closest('.sale-row');
        if (rows.length > 1) {
          row.remove();
        } else {
          row.querySelectorAll('input').forEach(function (input) {
            if (!input.classList.contains('quantidade')) input.value = '';
          });
        }
        if (lineCount) lineCount.value = items.querySelectorAll('.sale-row').length;
      });
    });
  });
</script>

</body>
</html>

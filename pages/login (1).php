<?php
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
      header('Location: CadCliente.php');
        /*include __DIR__ . '/../connect/conexao.php';

        try {
            $stmt = $pdo->prepare('SELECT id_administrador, nome, senha FROM administrador WHERE email = ?');
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($senha, $admin['senha'])) {
                $_SESSION['admin_id']   = $admin['id_administrador'];
                $_SESSION['admin_nome'] = $admin['nome'];
                header('Location: teste.php');
                exit;
            } else {
                $erro = 'E-mail ou senha incorretos.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao conectar ao banco de dados.';
        }*/
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>IFruit — Entrar</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --verde-escuro:  #1e3a2f;
      --verde-medio:   #2d5a3d;
      --verde-claro:   #4a8c5c;
      --verde-suave:   #a8c5a0;
      --creme:         #f5f0e8;
      --creme-escuro:  #e8e0d0;
      --terra:         #8b6914;
      --terra-claro:   #c4952a;
      --branco:        #fdfaf5;
      --texto-escuro:  #1a2e1f;
      --texto-medio:   #4a5e4f;
      --erro:          #c0392b;
    }

    html, body {
      height: 100%;
      font-family: 'DM Sans', sans-serif;
      background-color: var(--verde-escuro);
      color: var(--texto-escuro);
      overflow: hidden;
    }

    /* Fundo com textura orgânica */
    .bg {
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 60% at 15% 20%, rgba(74,140,92,0.25) 0%, transparent 60%),
        radial-gradient(ellipse 60% 80% at 85% 80%, rgba(45,90,61,0.4) 0%, transparent 55%),
        radial-gradient(ellipse 40% 40% at 50% 50%, rgba(196,149,42,0.08) 0%, transparent 70%),
        linear-gradient(160deg, #0f2019 0%, #1e3a2f 40%, #162d23 100%);
      z-index: 0;
    }

    /* Círculos decorativos */
    .bg::before {
      content: '';
      position: absolute;
      width: 500px; height: 500px;
      border-radius: 50%;
      border: 1px solid rgba(74,140,92,0.15);
      top: -150px; left: -150px;
    }
    .bg::after {
      content: '';
      position: absolute;
      width: 700px; height: 700px;
      border-radius: 50%;
      border: 1px solid rgba(196,149,42,0.1);
      bottom: -300px; right: -200px;
    }

    /* Layout principal */
    .page {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      height: 100vh;
    }

    /* Lado esquerdo — identidade */
    .side-left {
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 64px;
      animation: fadeInLeft 0.8s ease both;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(74,140,92,0.2);
      border: 1px solid rgba(74,140,92,0.35);
      border-radius: 100px;
      padding: 6px 14px;
      margin-bottom: 40px;
      width: fit-content;
    }
    .badge-dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--verde-suave);
      animation: pulse 2s infinite;
    }
    .badge span {
      font-size: 12px;
      font-weight: 500;
      color: var(--verde-suave);
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }

    .brand {
      font-family: 'Playfair Display', serif;
      font-size: clamp(52px, 6vw, 80px);
      font-weight: 700;
      line-height: 1;
      color: var(--branco);
      letter-spacing: -0.02em;
      margin-bottom: 8px;
    }
    .brand em {
      font-style: normal;
      color: var(--terra-claro);
    }

    .tagline {
      font-size: 15px;
      font-weight: 300;
      color: rgba(245,240,232,0.55);
      line-height: 1.7;
      max-width: 340px;
      margin-top: 20px;
    }

    /* Frutas decorativas */
    .fruit-row {
      display: flex;
      gap: 12px;
      margin-top: 56px;
    }
    .fruit-tag {
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 100px;
      padding: 8px 16px;
      font-size: 13px;
      color: rgba(245,240,232,0.6);
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.3s;
      cursor: default;
    }
    .fruit-tag:hover {
      background: rgba(74,140,92,0.2);
      border-color: rgba(74,140,92,0.4);
      color: var(--verde-suave);
      transform: translateY(-2px);
    }

    .campus-info {
      margin-top: auto;
      padding-top: 40px;
      font-size: 12px;
      color: rgba(245,240,232,0.3);
      letter-spacing: 0.04em;
    }

    /* Lado direito — formulário */
    .side-right {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      animation: fadeInRight 0.8s ease both;
    }

    .card {
      background: var(--branco);
      border-radius: 24px;
      padding: 48px 44px;
      width: 100%;
      max-width: 420px;
      box-shadow:
        0 30px 80px rgba(0,0,0,0.35),
        0 0 0 1px rgba(255,255,255,0.08);
    }

    .card-header {
      margin-bottom: 36px;
    }
    .card-header h2 {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 500;
      color: var(--texto-escuro);
      margin-bottom: 6px;
    }
    .card-header p {
      font-size: 14px;
      color: var(--texto-medio);
      font-weight: 300;
    }

    /* Alerta de erro */
    .alert-erro {
      background: #fdf0ef;
      border: 1px solid #f5c6c2;
      border-left: 3px solid var(--erro);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 13px;
      color: var(--erro);
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Campos */
    .field {
      margin-bottom: 20px;
    }
    .field label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--texto-medio);
      margin-bottom: 8px;
      letter-spacing: 0.02em;
    }
    .input-wrap {
      position: relative;
    }
    .input-wrap svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: 0.35;
      pointer-events: none;
    }
    .field input {
      width: 100%;
      padding: 13px 14px 13px 42px;
      border: 1.5px solid var(--creme-escuro);
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      color: var(--texto-escuro);
      background: var(--creme);
      transition: all 0.2s;
      outline: none;
    }
    .field input:focus {
      border-color: var(--verde-claro);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(74,140,92,0.12);
    }
    .field input::placeholder { color: #b0b8b3; }

    /* Botão */
    .btn-entrar {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--verde-medio), var(--verde-escuro));
      color: var(--branco);
      border: none;
      border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      margin-top: 8px;
      letter-spacing: 0.02em;
      transition: all 0.25s;
      position: relative;
      overflow: hidden;
    }
    .btn-entrar::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
      opacity: 0;
      transition: opacity 0.25s;
    }
    .btn-entrar:hover {
      transform: translateY(-1px);
      box-shadow: 0 8px 24px rgba(30,58,47,0.4);
    }
    .btn-entrar:hover::after { opacity: 1; }
    .btn-entrar:active { transform: translateY(0); }

    .card-footer {
      margin-top: 28px;
      padding-top: 24px;
      border-top: 1px solid var(--creme-escuro);
      text-align: center;
      font-size: 12px;
      color: #b0b8b3;
    }

    /* Animações */
    @keyframes fadeInLeft {
      from { opacity: 0; transform: translateX(-30px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInRight {
      from { opacity: 0; transform: translateX(30px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50%       { opacity: 0.4; }
    }

    /* Responsivo */
    @media (max-width: 768px) {
      .page { grid-template-columns: 1fr; }
      .side-left { display: none; }
      .side-right { background: var(--verde-escuro); }
      html, body { overflow: auto; }
    }
  </style>
</head>
<body>

<div class="bg"></div>

<div class="page">

  <!-- Lado esquerdo -->
  <div class="side-left">
    <div class="badge">
      <span class="badge-dot"></span>
      <span>IF Campus Morrinhos</span>
    </div>

    <div class="brand">IF<em>ruit</em></div>
    <p class="tagline">
      Gestão de vendas de excedentes agrícolas do campus. Simples, rápido e organizado.
    </p>

    <div class="campus-info">
      Instituto Federal Goiano &mdash; Campus Morrinhos
    </div>
  </div>

  <!-- Lado direito — formulário -->
  <div class="side-right">
    <div class="card">
      <div class="card-header">
        <h2>Bem-vindo</h2>
        <p>Entre com suas credenciais para acessar o sistema</p>
      </div>

      <?php if ($erro): ?>
        <div class="alert-erro">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($erro) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="field">
          <label for="email">E-mail</label>
          <div class="input-wrap">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3a2f" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="seu@email.com"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <div class="input-wrap">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e3a2f" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input
              type="password"
              id="senha"
              name="senha"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
          </div>
        </div>

        <button type="submit" class="btn-entrar">Entrar no sistema</button>
      </form>

      <div class="card-footer">
        IFruit &copy; <?= date('Y') ?> &mdash; IF Goiano Campus Morrinhos
      </div>
    </div>
  </div>

</div>

</body>
</html>

<?php
/**
 * sidebar.php â€” Componente reutilizÃ¡vel da navegaÃ§Ã£o lateral do IFruit
 *
 * Como usar em qualquer pÃ¡gina:
 *   <?php require_once 'sidebar.php'; ?>
 *
 * Para marcar o item ativo na sidebar, defina a variÃ¡vel $paginaAtiva
 * ANTES de incluir este arquivo. Valores possÃ­veis:
 *   'cadastros' | 'venda' | 'calculadora' | 'historico'
 *
 * Exemplo:
 *   <?php $paginaAtiva = 'calculadora'; require_once 'sidebar.php'; ?>
 */

$paginaAtiva = $paginaAtiva ?? '';
?>

<aside class="sidebar">

    <div class="logo-area">
        <img
            class="logo-img"
            src="https://presencial.ifgoiano.edu.br/pluginfile.php/1/theme_mb2nl/logo/1777469559/Logo-Horizontal-Moodle%20%281%29.png"
            alt="Logo IF Goiano">
    </div>

    <div class="welcome">
        Seja bem-vindo ao
        <strong>iFruit</strong>
    </div>

    <nav>

        <div class="nav-item <?= $paginaAtiva === 'cadastros'    ? 'active' : '' ?>">
            <a href="CadCliente.php">
                <div class="label">Cadastros/Ajuste</div>
                <div class="sublabel">Clientes e Frutas</div>
            </a>
        </div>

        <div class="nav-item <?= $paginaAtiva === 'venda'        ? 'active' : '' ?>">
            <a href="Venda.php">
                <div class="label">Venda</div>
                <div class="sublabel">Realizar venda</div>
            </a>
        </div>

        <div class="nav-item <?= $paginaAtiva === 'calculadora'  ? 'active' : '' ?>">
            <a href="calculadora.php">
                <div class="label">Calculadora</div>
                <div class="sublabel">Teste de valores</div>
            </a>
        </div>

        <div class="nav-item <?= $paginaAtiva === 'historico'    ? 'active' : '' ?>">
            <a href="Historico.php">
                <div class="label">Histórico</div>
                <div class="sublabel">Vendas anteriores</div>
            </a>
        </div>

    </nav>

    <button class="theme-toggle" type="button" aria-pressed="false">
        <span class="theme-toggle-icon" aria-hidden="true">&#9790;</span>
        <span class="theme-toggle-label">Modo escuro</span>
    </button>

</aside>

<script>
    (function () {
        const toggle = document.querySelector('.theme-toggle');
        const savedTheme = localStorage.getItem('ifruit-theme');

        function setTheme(isDark) {
            document.body.classList.toggle('dark-mode', isDark);
            toggle.setAttribute('aria-pressed', String(isDark));
            toggle.querySelector('.theme-toggle-label').textContent = isDark
                ? 'Modo claro'
                : 'Modo escuro';
            toggle.querySelector('.theme-toggle-icon').innerHTML = isDark
                ? '&#9728;'
                : '&#9790;';
        }

        setTheme(savedTheme === 'dark');

        toggle.addEventListener('click', function () {
            const isDark = !document.body.classList.contains('dark-mode');
            setTheme(isDark);
            localStorage.setItem('ifruit-theme', isDark ? 'dark' : 'light');
        });
    }());
</script>

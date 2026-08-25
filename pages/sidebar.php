<?php
/**
 * sidebar.php — Componente reutilizável da navegação lateral do IFruit
 *
 * Como usar em qualquer página:
 *   <?php require_once 'sidebar.php'; ?>
 *
 * Para marcar o item ativo na sidebar, defina a variável $paginaAtiva
 * ANTES de incluir este arquivo. Valores possíveis:
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

</aside>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>iFruit - Cadastros</title>

<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
}

/* BODY */
body{
    background:#f3f5f4;
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */
.sidebar{
    width:250px;
    min-height:100vh;
    background:#0f3d2e;
    border-right:3px solid #1ed760;
    position:fixed;
    top:0;
    left:0;
    display:flex;
    flex-direction:column;
    z-index:100;
    box-shadow:3px 0 10px rgba(0,0,0,0.08);
}

/* LOGO */
.logo-area{
    padding:16px 12px;
    border-bottom:1px solid rgba(255,255,255,0.1);
    display:flex;
    align-items:center;
    justify-content:center;
    background:#0b3024;
}

.logo-img{
    width:150px;
    height:55px;
    object-fit:contain;
    filter:brightness(0) invert(1);
}

/* TEXTO */
.welcome{
    padding:16px 16px 8px;
    font-size:11px;
    color:#cde8d9;
}

.welcome strong{
    display:block;
    font-size:18px;
    font-weight:700;
    color:#1ed760;
    margin-top:2px;
}

/* MENU */
nav{
    padding:10px 0;
    flex:1;
}

.nav-item{
    margin:4px 8px;
    border-radius:10px;
    overflow:hidden;
    transition:all 0.2s ease;
    border-left:4px solid transparent;
}

.nav-item:hover{
    background:rgba(255,255,255,0.08);
    transform:translateX(4px);
    border-left-color:#1ed760;
}

/* MENU ATIVO */
.nav-item.active{
    background:rgba(30,215,96,0.15);
    border-left-color:#1ed760;
}
.nav-item a{
    text-decoration:none;
    color:inherit;
    display:block;
    padding:12px 14px;
}

.label{
    font-size:14px;
    font-weight:600;
    color:#fff;
}

.sublabel{
    font-size:11px;
    color:#b7d7c7;
    margin-top:2px;
}

/* CONTEÚDO */
.main{
    margin-left:250px;
    flex:1;
    padding:30px;
}

/* TOPO */
.bloco1fundo{
    width:100%;
    height:60px;
    background:#d9d9d9;
    display:flex;
    align-items:center;
    padding:0 25px;
    border-radius:10px;
    border-bottom:1px solid #bdbdbd;
    margin-bottom:30px;
}

.bloco1fundo p{
    font-size:22px;
    font-weight:bold;
    color:#0f3d2e;
}

/* NAVBAR */
.navbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:30px;
}

.navbar a{
    text-decoration:none;
}

/* BOTÕES */
.navbar button{
    padding:10px 15px;
    border:none;
    background:#3a5f4c;
    color:white;
    border-radius:6px;
    cursor:pointer;
    transition:0.2s;
    font-weight:600;
}

.navbar button:hover{
    background:#2d4b3b;
    transform:translateY(-2px);
}

/* BOTÃO ATIVO */
.navbar button.ativo{
    background: #11c911c2;
    color:white;
    box-shadow:0 0 10px rgba(13, 158, 64, 0.4);
}

/* BLOCO PRINCIPAL */
.bloco2fundo{
    width:100%;
    max-width:800px;
    min-height:340px;
    background:#3a5f4c;
    border-radius:12px;
    padding:25px;
    color:white;
    box-shadow:0px 4px 10px rgba(0,0,0,0.2);
}

.bloco2fundo p{
    font-size:24px;
    font-weight:bold;
    margin-bottom:20px;
}

/* FORMULÁRIO */
.formulario{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.formulario input{
    padding:12px;
    border:none;
    border-radius:6px;
    outline:none;
    font-size:14px;
}

.formulario button{
    width:200px;
    padding:12px;
    border:none;
    border-radius:6px;
    background:#1ed760;
    color:#0f3d2e;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
}

.formulario button:hover{
    background:#18b84f;
}

/* RESPONSIVO */
@media(max-width:768px){

    body{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
        position:relative;
        min-height:auto;
    }

    .main{
        margin-left:0;
        padding:20px;
    }

    .navbar{
        flex-direction:column;
    }

    .navbar button{
        width:100%;
    }

    .bloco2fundo{
        width:100%;
    }
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <div class="logo-area">
        <img 
            class="logo-img"
            src="https://presencial.ifgoiano.edu.br/pluginfile.php/1/theme_mb2nl/logo/1777469559/Logo-Horizontal-Moodle%20%281%29.png"
            alt="Logo IF">
    </div>

    <div class="welcome">
        Seja bem-vindo ao
        <strong>iFruit</strong>
    </div>

    <nav>

        <!-- MENU ATIVO -->
        <div class="nav-item active">
            <a href="#">
                <div class="label">Cadastros/Ajuste</div>
                <div class="sublabel">Clientes e Frutas</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="#">
                <div class="label">Venda</div>
                <div class="sublabel">Realizar venda</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="#">
                <div class="label">Calculadora</div>
                <div class="sublabel">Teste de valores</div>
            </a>
        </div>

        <div class="nav-item">
            <a href="#">
                <div class="label">Histórico</div>
                <div class="sublabel">Vendas anteriores</div>
            </a>
        </div>

    </nav>

</aside>

<!-- CONTEÚDO -->
<main class="main">

    <!-- TOPO -->
    <div class="bloco1fundo">
        <p>Cadastros/Ajuste</p>
    </div>

    <!-- MENU INTERNO -->
    <div class="navbar">

        <!-- BOTÃO ATIVO -->
        <a href="teste.php">
            <button type="button" >
                Produtos
            </button>
        </a>

        <a href="teste2.php">
            <button type="button">
                Cliente
            </button>
        </a>

        <a href="Ajuste_C.php">
            <button type="button">
                Ajuste de Cliente
            </button>
        </a>

        <a href="Ajuste_F.php">
            <button type="button" class="ativo">
                Ajuste de Produto
            </button>
        </a>

    </div>

    <!-- BLOCO PRINCIPAL -->
    <div class="bloco2fundo">

        <p>Ajustar Produto</p>

        <form class="formulario" action="" method="POST">

            <input 
                type="text" 
                name="produto" 
                placeholder="Nome do Produto" 
                required>

            <input 
                type="number" 
                name="quantidade" 
                placeholder="Quantidade" 
                min="0" 
                step="0.5" 
                required>

            <input 
                type="number" 
                name="preco" 
                placeholder="Preço" 
                min="0" 
                step="0.05" 
                required>

            <button type="submit">
                Ajustar Produto
            </button>

        </form>

    </div>

</main>

</body>
</html>
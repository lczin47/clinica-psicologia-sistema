<?php
// Inicia a sessão para controle
session_start();

// Redireciona se o usuário já estiver logado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: painel_admin.php");
    exit;
}

// Configuração e Conexão (Variável $link)
require_once 'config.php'; 

// Inicialização de variáveis
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processamento do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Validação do Usuário
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor, insira o usuário (e-mail).";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validação da Senha
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, insira a senha.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Se não há erros, tente buscar o usuário
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id_usuario, username, password, nome FROM usuarios WHERE username = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                // Se o usuário existir (1 linha retornada)
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id_usuario, $username, $hashed_password, $nome);
                    if (mysqli_stmt_fetch($stmt)) {
                        
                        // Verifica a senha (função segura de hash)
                        if (password_verify($password, $hashed_password)) {
                            // Sucesso! Inicia a sessão.
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id_usuario;
                            $_SESSION["username"] = $username;
                            $_SESSION["nome"] = $nome; // Nome para exibir na área admin

                            // Redireciona para o Painel Admin
                            header("location: painel_admin.php");
                        } else {
                            // Senha incorreta
                            $login_err = "Usuário ou senha inválidos.";
                        }
                    }
                } else {
                    // Usuário não encontrado
                    $login_err = "Usuário ou senha inválidos.";
                }
            } else {
                $login_err = "Ops! Algo deu errado. Tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clínica Mulher</title>
    <link rel="stylesheet" href="assets/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos específicos para a tela de login */
        body { background-color: var(--color-light-bg); }
        .login-container {
            max-width: 400px;
            margin: 10vh auto;
            padding: 2rem;
            background-color: var(--color-white);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .login-container h2 {
            color: var(--color-primary);
            margin-bottom: 1.5rem;
        }
        .login-form .form-group {
            text-align: left;
            margin-bottom: 1rem;
        }
        .login-form label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 500;
            color: var(--color-secondary);
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .help-block {
            color: red;
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
        }
        .login-form .btn-primary {
            width: 100%;
            margin-top: 1.5rem;
        }
        .alert.error {
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Acesso Administrativo</h2>
    
    <?php 
    // Exibe erro de login (Usuário ou Senha inválidos)
    if (!empty($login_err)) {
        echo '<div class="alert error">' . $login_err . '</div>';
    }        
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
        <div class="form-group">
            <label>Usuário (E-mail)</label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <span class="help-block"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="password">
            <span class="help-block"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary">Entrar</button>
        </div>
        <p><a href="index.php">Voltar para a Home</a></p>
    </form>
</div>

</body>
</html>
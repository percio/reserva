<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'setup_config.php'; // Include the setup configuration file

// Check if admin already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'");
$stmt->execute();
$admin_exists = $stmt->fetchColumn() > 0;

$success = false;
$error = '';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$admin_exists) {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $senha = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';
    $setup_key = $_POST['setup_key'] ?? '';
    
    // Validate setup key (using the constant from setup_config.php)
    $correct_key = SETUP_KEY;
    
    if (empty($nome) || empty($email) || empty($telefone) || empty($senha)) {
        $error = 'Todos os campos são obrigatórios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } elseif ($senha !== $senha_confirm) {
        $error = 'As senhas não coincidem';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres';
    } elseif ($setup_key !== $correct_key) {
        $error = 'Chave de configuração inválida';
    } else {
        try {
            $hashed_password = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, tipo, status, data_cadastro) 
                                VALUES (?, ?, ?, ?, 'admin', 'ativo', NOW())");
            $stmt->execute([$nome, $email, $telefone, $hashed_password]);
            
            $success = true;
        } catch (PDOException $e) {
            $error = 'Erro ao criar administrador: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração Inicial - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 600px;
            margin: 80px auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            font-size: 32px;
            color: #333;
        }
        .logo span {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <div class="logo">
                <h1>Aluga<span>Fácil</span></h1>
                <p>Configuração Inicial do Sistema</p>
            </div>
            
            <div class="card shadow">
                <div class="card-body p-4">
                    <?php if ($admin_exists): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Aviso:</strong> Um administrador já está configurado no sistema.
                            <p class="mt-2 mb-0">Por razões de segurança, esta página está bloqueada.</p>
                        </div>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-primary">Ir para o Login</a>
                            <a href="index.php" class="btn btn-outline-primary ms-2">Ir para o Início</a>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Sucesso!</strong> Administrador criado com sucesso.
                        </div>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-primary">Ir para o Login</a>
                            <a href="index.php" class="btn btn-outline-primary ms-2">Ir para o Início</a>
                        </div>
                    <?php else: ?>
                        <h3 class="card-title mb-4">Criar Administrador Inicial</h3>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="setup.php">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="senha" class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="senha" name="senha" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="senha_confirm" class="form-label">Confirmar Senha</label>
                                    <input type="password" class="form-control" id="senha_confirm" name="senha_confirm" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="setup_key" class="form-label">Chave de Configuração</label>
                                <input type="password" class="form-control" id="setup_key" name="setup_key" required>
                                <small class="text-muted">Chave fornecida para configuração inicial do sistema.</small>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Criar Administrador</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <p class="text-center mt-3 text-muted">
                &copy; <?php echo date('Y'); ?> AlugaFácil - Todos os direitos reservados.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Telefone mask
        document.getElementById('telefone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            if (value.length > 6) {
                value = `(${value.substring(0, 2)}) ${value.substring(2, 7)}-${value.substring(7)}`;
            } else if (value.length > 2) {
                value = `(${value.substring(0, 2)}) ${value.substring(2)}`;
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html>
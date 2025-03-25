<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirecionar se já estiver logado
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$errors = [];
$name = $email = $phone = '';

// Processar formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validações
    if (empty($name)) {
        $errors[] = 'Nome é obrigatório';
    }
    
    if (empty($email)) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    } else {
        // Verificar se o email já está em uso
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Este email já está em uso';
        }
    }
    
    if (empty($phone)) {
        $errors[] = 'Telefone é obrigatório';
    }
    
    if (empty($password)) {
        $errors[] = 'Senha é obrigatória';
    } elseif (strlen($password) < 6) {
        $errors[] = 'A senha deve ter pelo menos 6 caracteres';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'As senhas não coincidem';
    }
    
    if (empty($errors)) {
        // Cadastrar o usuário
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(16));
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, telefone, senha, token, status, tipo, data_cadastro) VALUES (?, ?, ?, ?, ?, 'pendente', 'cliente', NOW())");
            $stmt->execute([$name, $email, $phone, $hashed_password, $token]);
            
            // Enviar email de confirmação
            $subject = 'Confirme seu cadastro - AlugaFácil';
            $message = "Olá $name,\n\nObrigado por se cadastrar no AlugaFácil! Por favor, clique no link abaixo para confirmar seu email:\n\n";
            $message .= "http://seusite.com/confirmar.php?token=$token\n\n";
            $message .= "Se você não solicitou este cadastro, por favor ignore este email.\n\nAtenciosamente,\nEquipe AlugaFácil";
            
            mail($email, $subject, $message, 'From: noreply@alugafacil.com');
            
            // Redirecionar para página de sucesso
            $_SESSION['registration_success'] = true;
            redirect('cadastro-sucesso.php');
        } catch (PDOException $e) {
            $errors[] = 'Erro ao cadastrar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Cadastre-se</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="cadastro.php" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor, informe seu nome.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor, informe um email válido.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="(00) 00000-0000" required>
                                <div class="invalid-feedback">
                                    Por favor, informe seu telefone.
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                    <div class="invalid-feedback">
                                        A senha deve ter pelo menos 6 caracteres.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirm" class="form-label">Confirmar Senha</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                                    <div class="invalid-feedback">
                                        As senhas não coincidem.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">Concordo com os <a href="termos.php" target="_blank">Termos de Uso</a> e <a href="privacidade.php" target="_blank">Política de Privacidade</a></label>
                                <div class="invalid-feedback">
                                    Você deve concordar com os termos para continuar.
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Cadastrar</button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <div class="text-center">
                            <p>Já tem uma conta?</p>
                            <a href="login.php" class="btn btn-outline-primary">Fazer Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Validação de senha em tempo real
        document.getElementById('password_confirm').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const password_confirm = this.value;
            
            if (password !== password_confirm) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Máscara para telefone
        document.getElementById('phone').addEventListener('input', function(e) {
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


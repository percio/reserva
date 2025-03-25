<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirecionar se já estiver logado
if (isset($_SESSION['user_id'])) {
    // Redirect based on user type
    switch ($_SESSION['user_tipo']) {
        case 'admin':
            redirect('admin/dashboard.php');
            break;
        case 'proprietario':
            redirect('proprietario/dashboard.php');
            break;
        default:
            redirect('dashboard.php');
    }
}

$errors = [];
$email = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $errors[] = 'Email é obrigatório';
    }
    
    if (empty($password)) {
        $errors[] = 'Senha é obrigatória';
    }
    
    if (empty($errors)) {
        // Verificar credenciais
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            
            // Definir o destino com base no tipo de usuário
            switch ($_SESSION['user_tipo']) {
                case 'admin':
                    redirect('admin/dashboard.php');
                    break;
                case 'proprietario':
                    redirect('proprietario/dashboard.php');
                    break;
                default:
                    redirect('dashboard.php');
            }
        } else {
            $errors[] = 'Email ou senha inválidos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Login</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Lembrar-me</label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="recuperar-senha.php">Esqueceu sua senha?</a>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <p>Ainda não tem uma conta?</p>
                            <a href="cadastro.php" class="btn btn-outline-primary">Cadastre-se</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
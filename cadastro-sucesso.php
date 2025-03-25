<?php
session_start();

// Redirect if not coming from registration
if (!isset($_SESSION['registration_success'])) {
    header('Location: cadastro.php');
    exit;
}

// Clear success flag
unset($_SESSION['registration_success']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Realizado - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow text-center">
                    <div class="card-body p-5">
                        <i class="fas fa-check-circle text-success fa-5x mb-4"></i>
                        <h2 class="mb-4">Cadastro Realizado com Sucesso!</h2>
                        <p class="mb-4">Enviamos um email de confirmação para o endereço informado. Por favor, verifique sua caixa de entrada e confirme seu cadastro clicando no link enviado.</p>
                        
                        <div class="alert alert-info">
                            <p>Se não encontrar o email, verifique também sua pasta de spam ou lixo eletrônico.</p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">Voltar para a Página Inicial</a>
                            <a href="login.php" class="btn btn-outline-primary ms-2">Ir para o Login</a>
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
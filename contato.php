<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$errors = [];
$success = false;
$nome = $email = $telefone = $assunto = $mensagem = '';

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $assunto = filter_input(INPUT_POST, 'assunto', FILTER_SANITIZE_STRING);
    $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);
    
    // Validate inputs
    if (empty($nome)) {
        $errors[] = 'Por favor, informe seu nome';
    }
    
    if (empty($email)) {
        $errors[] = 'Por favor, informe seu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Por favor, informe um email válido';
    }
    
    if (empty($assunto)) {
        $errors[] = 'Por favor, informe o assunto';
    }
    
    if (empty($mensagem)) {
        $errors[] = 'Por favor, digite sua mensagem';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contatos (nome, email, telefone, assunto, mensagem, status, data_envio) VALUES (?, ?, ?, ?, ?, 'novo', NOW())");
            $stmt->execute([$nome, $email, $telefone, $assunto, $mensagem]);
            
            // Reset form fields
            $nome = $email = $telefone = $assunto = $mensagem = '';
            $success = true;
            
            // Send email notification to admin
            $admin_email = ADMIN_EMAIL;
            $subject = "Nova mensagem de contato: " . $assunto;
            $message = "Nova mensagem recebida através do formulário de contato:\n\n";
            $message .= "Nome: " . $nome . "\n";
            $message .= "Email: " . $email . "\n";
            $message .= "Telefone: " . $telefone . "\n";
            $message .= "Assunto: " . $assunto . "\n";
            $message .= "Mensagem: " . $mensagem . "\n\n";
            $message .= "Acesse o painel administrativo para responder.";
            
            mail($admin_email, $subject, $message, "From: " . $email);
            
        } catch (PDOException $e) {
            $errors[] = 'Erro ao enviar mensagem. Por favor, tente novamente mais tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header bg-primary text-white">
        <div class="container py-5">
            <h1>Entre em Contato</h1>
            <p class="lead">Estamos à disposição para ajudar você a encontrar o imóvel perfeito para suas férias.</p>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4">Envie sua mensagem</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i> Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="contato.php" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome completo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor, informe seu nome.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor, informe um email válido.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" placeholder="(00) 00000-0000">
                            </div>
                            
                            <div class="mb-3">
                                <label for="assunto" class="form-label">Assunto *</label>
                                <select class="form-select" id="assunto" name="assunto" required>
                                    <option value="" <?php echo empty($assunto) ? 'selected' : ''; ?>>Selecione um assunto</option>
                                    <option value="Informações sobre imóvel" <?php echo ($assunto == 'Informações sobre imóvel') ? 'selected' : ''; ?>>Informações sobre imóvel</option>
                                    <option value="Reservas" <?php echo ($assunto == 'Reservas') ? 'selected' : ''; ?>>Reservas</option>
                                    <option value="Parcerias" <?php echo ($assunto == 'Parcerias') ? 'selected' : ''; ?>>Parcerias</option>
                                    <option value="Sugestões" <?php echo ($assunto == 'Sugestões') ? 'selected' : ''; ?>>Sugestões</option>
                                    <option value="Reclamações" <?php echo ($assunto == 'Reclamações') ? 'selected' : ''; ?>>Reclamações</option>
                                    <option value="Outros" <?php echo ($assunto == 'Outros') ? 'selected' : ''; ?>>Outros</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione um assunto.
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="mensagem" class="form-label">Mensagem *</label>
                                <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required><?php echo htmlspecialchars($mensagem); ?></textarea>
                                <div class="invalid-feedback">
                                    Por favor, digite sua mensagem.
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Enviar Mensagem</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="contact-info p-4">
                    <h3 class="mb-4">Informações de Contato</h3>
                    
                    <div class="contact-item mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h5>Endereço</h5>
                            <p>Av. Beira Mar, 1000<br>Centro, Caraguatatuba - SP<br>CEP: 11665-000</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h5>Telefone</h5>
                            <p>(12) 3456-7890</p>
                            <p>(12) 98765-4321 (WhatsApp)</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h5>Email</h5>
                            <p>contato@alugafacil.com.br</p>
                            <p>suporte@alugafacil.com.br</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h5>Horário de Atendimento</h5>
                            <p>Segunda a Sexta: 9h às 18h</p>
                            <p>Sábado: 9h às 13h</p>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4">
                        <h5>Redes Sociais</h5>
                        <div class="d-flex gap-3 mt-2">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map Section -->
        <div class="mt-5">
            <h3 class="mb-4">Nossa Localização</h3>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14750.632867556174!2d-45.42721217907108!3d-23.635164046901364!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94cd631551d2d585%3A0x84c2880a14d69201!2sCaraguatatuba%2C%20SP!5e0!3m2!1spt-BR!2sbr!4v1661458236544!5m2!1spt-BR!2sbr" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="mt-5">
            <h3 class="mb-4">Perguntas Frequentes</h3>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Como funciona o processo de reserva?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Para reservar um imóvel, basta selecionar o imóvel desejado, escolher as datas disponíveis, fazer o login ou cadastro em nossa plataforma e finalizar o pagamento. Após a confirmação do pagamento, você receberá todas as informações necessárias para sua estadia.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Qual a política de cancelamento?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Nossa política de cancelamento pode variar de acordo com o imóvel. Em geral, cancelamentos realizados com mais de 30 dias de antecedência recebem reembolso total, entre 15 e 30 dias recebem 50% de reembolso, e com menos de 15 dias não há reembolso. Verifique as condições específicas de cada imóvel antes de finalizar sua reserva.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Como posso cadastrar meu imóvel para aluguel?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Para cadastrar seu imóvel, acesse a opção "Cadastrar meu imóvel" em nosso site, preencha o formulário com as informações necessárias e envie fotos de qualidade. Nossa equipe irá revisar as informações e entrar em contato para finalizar o processo e agendar uma visita técnica, se necessário.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            Quais formas de pagamento são aceitas?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Aceitamos diversas formas de pagamento, incluindo cartão de crédito (parcelado em até 6x), PIX, boleto bancário e transferência bancária. O método de pagamento pode ser selecionado durante o processo de reserva.
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
        // Telefone mask
        document.getElementById('telefone').addEventListener('input', function(e) {
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
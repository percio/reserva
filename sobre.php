<?php
session_start();
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header bg-primary text-white">
        <div class="container py-5">
            <h1>Sobre Nós</h1>
            <p class="lead">Conheça nossa história e missão de proporcionar experiências inesquecíveis de férias no litoral.</p>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2>Nossa História</h2>
                <p>A AlugaFácil nasceu em 2020 com uma visão simples: tornar o aluguel de casas de temporada no litoral uma experiência fácil, transparente e segura para todos.</p>
                <p>Fundada por um grupo de amigos apaixonados por viagens, nossa empresa surgiu da frustração com processos complicados e falta de transparência no mercado de aluguel por temporada. Decidimos criar uma plataforma que resolvesse esses problemas e oferecesse experiências incríveis aos nossos clientes.</p>
                <p>Desde então, crescemos rapidamente e hoje somos referência no mercado de aluguel por temporada no litoral paulista, com planos de expansão para outras regiões do Brasil.</p>
            </div>
            <div class="col-md-6">
                <img src="img/sobre-historia.jpg" alt="Nossa história" class="img-fluid rounded shadow">
            </div>
        </div>
        
        <div class="row align-items-center mb-5 flex-md-row-reverse">
            <div class="col-md-6">
                <h2>Nossa Missão</h2>
                <p>Proporcionar experiências memoráveis de férias, conectando viajantes a imóveis de qualidade de maneira simples, segura e transparente.</p>
                <p>Buscamos constantemente a excelência em nossos serviços, garantindo que cada cliente encontre o imóvel perfeito para suas necessidades e possa aproveitar momentos incríveis em família e com amigos.</p>
                <p>Valorizamos a transparência em todas as etapas do processo, desde a busca pelo imóvel até o checkout, para que nossos clientes possam viajar com tranquilidade e segurança.</p>
            </div>
            <div class="col-md-6">
                <img src="img/sobre-missao.jpg" alt="Nossa missão" class="img-fluid rounded shadow">
            </div>
        </div>
        
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2>Nossos Valores</h2>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Transparência</strong> - Informações claras e precisas sobre todos os imóveis e serviços.</li>
                    <li class="list-group-item"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Qualidade</strong> - Rigorosa seleção e verificação de todos os imóveis em nossa plataforma.</li>
                    <li class="list-group-item"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Confiança</strong> - Construímos relacionamentos duradouros baseados em confiança mútua.</li>
                    <li class="list-group-item"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Excelência</strong> - Buscamos constantemente aprimorar nossos serviços e atendimento.</li>
                    <li class="list-group-item"><i class="fas fa-check-circle text-primary me-2"></i> <strong>Inovação</strong> - Utilizamos tecnologia para simplificar e melhorar a experiência de nossos clientes.</li>
                </ul>
            </div>
            <div class="col-md-6">
                <img src="img/sobre-valores.jpg" alt="Nossos valores" class="img-fluid rounded shadow">
            </div>
        </div>
        
        <div class="text-center mt-5">
            <h2>Nossa Equipe</h2>
            <p class="lead mb-5">Conheça os profissionais que fazem a AlugaFácil acontecer</p>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card team-card">
                        <img src="img/team/team1.jpg" class="card-img-top" alt="Membro da equipe">
                        <div class="card-body">
                            <h5 class="card-title">Carlos Silva</h5>
                            <p class="card-text text-muted">CEO & Fundador</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card team-card">
                        <img src="img/team/team2.jpg" class="card-img-top" alt="Membro da equipe">
                        <div class="card-body">
                            <h5 class="card-title">Mariana Santos</h5>
                            <p class="card-text text-muted">Diretora de Operações</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card team-card">
                        <img src="img/team/team3.jpg" class="card-img-top" alt="Membro da equipe">
                        <div class="card-body">
                            <h5 class="card-title">Pedro Oliveira</h5>
                            <p class="card-text text-muted">Gerente de Marketing</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card team-card">
                        <img src="img/team/team4.jpg" class="card-img-top" alt="Membro da equipe">
                        <div class="card-body">
                            <h5 class="card-title">Amanda Costa</h5>
                            <p class="card-text text-muted">Atendimento ao Cliente</p>
                            <div class="social-links">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Depoimentos -->
    <section class="testimonials bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">O que dizem nossos parceiros</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Trabalhar com a AlugaFácil revolucionou a gestão das minhas propriedades. Eles cuidam de tudo enquanto recebo um retorno financeiro excelente."</p>
                        <div class="client">
                            <h4>Marcos Pereira</h4>
                            <p>Proprietário de 3 imóveis</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Após começar a trabalhar com a AlugaFácil, a taxa de ocupação do meu imóvel aumentou significativamente. Eles realmente sabem o que estão fazendo!"</p>
                        <div class="client">
                            <h4>Juliana Mendes</h4>
                            <p>Proprietária em Ubatuba</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p>"Transparência e profissionalismo são marcas registradas da AlugaFácil. Como proprietário, me sinto valorizado e bem assistido em todos os momentos."</p>
                        <div class="client">
                            <h4>Felipe Costa</h4>
                            <p>Proprietário em Ilhabela</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="cta bg-primary text-white py-5">
        <div class="container text-center">
            <h2 class="mb-4">Quer alugar seu imóvel com a gente?</h2>
            <p class="lead mb-4">Temos uma equipe especializada pronta para ajudar você a maximizar o rendimento do seu imóvel.</p>
            <a href="cadastro-proprietario.php" class="btn btn-light btn-lg">Cadastrar meu imóvel</a>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
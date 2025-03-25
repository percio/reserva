<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Buscar imóveis em destaque
$stmt = $pdo->prepare("
    SELECT id, titulo, descricao, cidade, estado, valor_diaria, quartos, banheiros, capacidade, foto_principal
    FROM imoveis
    WHERE destaque = 1 AND status = 'ativo'
    ORDER BY id DESC
    LIMIT 3
");
$stmt->execute();
$featured_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aluga Fácil - Casas para Temporada no Litoral</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2>Encontre a casa perfeita para suas férias no litoral</h2>
                    <p>Oferecemos as melhores opções de casas e apartamentos para temporada com conforto e segurança</p>
                    <a href="imoveis.php" class="btn btn-lg btn-primary">Ver Imóveis</a>
                </div>
                <div class="col-md-6">
                    <div class="search-box">
                        <h3>Busca Rápida</h3>
                        <form action="imoveis.php" method="GET">
                            <div class="mb-3">
                                <select name="cidade" class="form-select">
                                    <option value="">Selecione a cidade</option>
                                    <option value="Ubatuba">Ubatuba</option>
                                    <option value="Caraguatatuba">Caraguatatuba</option>
                                    <option value="São Sebastião">São Sebastião</option>
                                    <option value="Ilhabela">Ilhabela</option>
                                    <option value="Bertioga">Bertioga</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="quartos" class="form-select">
                                    <option value="">Número de quartos</option>
                                    <option value="1">1 quarto</option>
                                    <option value="2">2 quartos</option>
                                    <option value="3">3 quartos</option>
                                    <option value="4">4+ quartos</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="date" name="check_in" class="form-control" placeholder="Check-in">
                                    <input type="date" name="check_out" class="form-control" placeholder="Check-out">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-properties">
        <div class="container">
            <h2 class="text-center mb-5">Imóveis em Destaque</h2>
            <div class="row" id="featured-properties-container">
                <?php if (empty($featured_properties)): ?>
                    <div class="col-12 text-center">
                        <p>Não há imóveis em destaque no momento.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_properties as $property): ?>
                        <div class="col-md-4 mb-4">
                            <div class="property-card">
                                <div class="property-image" style="background-image: url('<?php echo htmlspecialchars($property['foto_principal']); ?>')"></div>
                                <div class="property-info">
                                    <h3><?php echo htmlspecialchars($property['titulo']); ?></h3>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['cidade'] . ', ' . $property['estado']); ?></p>
                                    <div class="price">R$ <?php echo number_format($property['valor_diaria'], 2, ',', '.'); ?> / noite</div>
                                    <div class="property-features">
                                        <span><i class="fas fa-bed"></i> <?php echo $property['quartos']; ?> quartos</span>
                                        <span><i class="fas fa-bath"></i> <?php echo $property['banheiros']; ?> banheiros</span>
                                        <span><i class="fas fa-users"></i> <?php echo $property['capacidade']; ?> pessoas</span>
                                    </div>
                                    <a href="imovel.php?id=<?php echo $property['id']; ?>" class="btn btn-primary w-100">Ver Detalhes</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="imoveis.php" class="btn btn-outline-primary">Ver Todos os Imóveis</a>
            </div>
        </div>
    </section>

    <section class="benefits">
        <div class="container">
            <h2 class="text-center mb-5">Por que escolher o AlugaFácil?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="fas fa-house-user"></i>
                        <h3>Propriedades Selecionadas</h3>
                        <p>Todas as casas passam por rigorosa avaliação de qualidade e segurança</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="fas fa-credit-card"></i>
                        <h3>Pagamento Seguro</h3>
                        <p>Métodos de pagamento seguros e flexíveis para sua tranquilidade</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <i class="fas fa-headset"></i>
                        <h3>Suporte 24/7</h3>
                        <p>Atendimento disponível todos os dias para resolver qualquer problema</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <h2 class="text-center mb-5">O que dizem nossos clientes</h2>
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
                        <p>"Férias perfeitas! A casa era exatamente como nas fotos e o atendimento foi excepcional."</p>
                        <div class="client">
                            <h4>Roberto Silva</h4>
                            <p>Ubatuba, SP</p>
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
                        <p>"Processo simplificado de reserva e casa impecável. Voltarei com certeza nas próximas férias!"</p>
                        <div class="client">
                            <h4>Ana Paula Mendes</h4>
                            <p>São Sebastião, SP</p>
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
                        <p>"Ótima localização e preço justo. Recomendo para famílias que buscam conforto e praticidade."</p>
                        <div class="client">
                            <h4>Carlos Eduardo Gomes</h4>
                            <p>Ilhabela, SP</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
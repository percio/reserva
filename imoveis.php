<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get filter parameters
$cidade = filter_input(INPUT_GET, 'cidade', FILTER_SANITIZE_STRING);
$quartos = filter_input(INPUT_GET, 'quartos', FILTER_SANITIZE_NUMBER_INT);
$capacidade = filter_input(INPUT_GET, 'capacidade', FILTER_SANITIZE_NUMBER_INT);
$preco_min = filter_input(INPUT_GET, 'preco_min', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$preco_max = filter_input(INPUT_GET, 'preco_max', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// Base query
$sql = "SELECT * FROM imoveis WHERE status = 'ativo'";
$params = [];

// Apply filters
if (!empty($cidade)) {
    $sql .= " AND cidade = ?";
    $params[] = $cidade;
}

if (!empty($quartos)) {
    $sql .= " AND quartos >= ?";
    $params[] = $quartos;
}

if (!empty($capacidade)) {
    $sql .= " AND capacidade >= ?";
    $params[] = $capacidade;
}

if (!empty($preco_min)) {
    $sql .= " AND valor_diaria >= ?";
    $params[] = $preco_min;
}

if (!empty($preco_max)) {
    $sql .= " AND valor_diaria <= ?";
    $params[] = $preco_max;
}

// Order by
$sql .= " ORDER BY destaque DESC, id DESC";

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cities for filter
$stmt = $pdo->query("SELECT DISTINCT cidade, estado FROM imoveis WHERE status = 'ativo' ORDER BY cidade");
$cidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imóveis para Temporada - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <h1 class="mb-4">Imóveis para Temporada</h1>
        
        <div class="row">
            <!-- Filters -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form action="imoveis.php" method="GET">
                            <div class="mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <select name="cidade" id="cidade" class="form-select">
                                    <option value="">Todas as cidades</option>
                                    <?php foreach ($cidades as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c['cidade']); ?>" <?php echo ($cidade == $c['cidade']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['cidade'] . ' - ' . $c['estado']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="quartos" class="form-label">Quartos</label>
                                <select name="quartos" id="quartos" class="form-select">
                                    <option value="">Qualquer</option>
                                    <option value="1" <?php echo ($quartos == 1) ? 'selected' : ''; ?>>1+</option>
                                    <option value="2" <?php echo ($quartos == 2) ? 'selected' : ''; ?>>2+</option>
                                    <option value="3" <?php echo ($quartos == 3) ? 'selected' : ''; ?>>3+</option>
                                    <option value="4" <?php echo ($quartos == 4) ? 'selected' : ''; ?>>4+</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="capacidade" class="form-label">Capacidade</label>
                                <select name="capacidade" id="capacidade" class="form-select">
                                    <option value="">Qualquer</option>
                                    <option value="2" <?php echo ($capacidade == 2) ? 'selected' : ''; ?>>2+</option>
                                    <option value="4" <?php echo ($capacidade == 4) ? 'selected' : ''; ?>>4+</option>
                                    <option value="6" <?php echo ($capacidade == 6) ? 'selected' : ''; ?>>6+</option>
                                    <option value="8" <?php echo ($capacidade == 8) ? 'selected' : ''; ?>>8+</option>
                                    <option value="10" <?php echo ($capacidade == 10) ? 'selected' : ''; ?>>10+</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Preço por diária</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="preco_min" placeholder="Min" class="form-control" value="<?php echo $preco_min; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="preco_max" placeholder="Max" class="form-control" value="<?php echo $preco_max; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Properties List -->
            <div class="col-md-9">
                <?php if (empty($imoveis)): ?>
                    <div class="alert alert-info">
                        Nenhum imóvel encontrado com os filtros selecionados. <a href="imoveis.php">Limpar filtros</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($imoveis as $imovel): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="property-card">
                                    <div class="property-image" style="background-image: url('<?php echo htmlspecialchars($imovel['foto_principal']); ?>')">
                                        <?php if ($imovel['destaque']): ?>
                                            <span class="badge bg-warning position-absolute m-2">Destaque</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="property-info">
                                        <h3><?php echo htmlspecialchars($imovel['titulo']); ?></h3>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($imovel['cidade'] . ', ' . $imovel['estado']); ?></p>
                                        <div class="price">R$ <?php echo number_format($imovel['valor_diaria'], 2, ',', '.'); ?> / noite</div>
                                        <div class="property-features">
                                            <span><i class="fas fa-bed"></i> <?php echo $imovel['quartos']; ?> quartos</span>
                                            <span><i class="fas fa-bath"></i> <?php echo $imovel['banheiros']; ?> banheiros</span>
                                            <span><i class="fas fa-users"></i> <?php echo $imovel['capacidade']; ?> pessoas</span>
                                        </div>
                                        <a href="imovel.php?id=<?php echo $imovel['id']; ?>" class="btn btn-primary w-100">Ver Detalhes</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
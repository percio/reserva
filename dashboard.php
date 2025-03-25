<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// Buscar dados do usuário
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Usuário não encontrado, fazer logout
    logout();
    redirect('login.php');
}

// Buscar estatísticas do usuário (cliente)
$stats = [
    'reservas_ativas' => 0,
    'reservas_concluidas' => 0,
    'reservas_canceladas' => 0,
    'favoritos' => 0
];

// Contagem de reservas ativas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE id_usuario = ? AND status = 'confirmada' AND data_saida >= CURDATE()");
$stmt->execute([$user_id]);
$stats['reservas_ativas'] = $stmt->fetchColumn();

// Contagem de reservas concluídas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE id_usuario = ? AND (status = 'concluida' OR (status = 'confirmada' AND data_saida < CURDATE()))");
$stmt->execute([$user_id]);
$stats['reservas_concluidas'] = $stmt->fetchColumn();

// Contagem de reservas canceladas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE id_usuario = ? AND status = 'cancelada'");
$stmt->execute([$user_id]);
$stats['reservas_canceladas'] = $stmt->fetchColumn();

// Contagem de imóveis favoritos
$stmt = $pdo->prepare("SELECT COUNT(*) FROM favoritos WHERE id_usuario = ?");
$stmt->execute([$user_id]);
$stats['favoritos'] = $stmt->fetchColumn();

// Buscar próximas reservas do usuário
$stmt = $pdo->prepare("
    SELECT r.*, i.titulo, i.cidade, i.estado, i.foto_principal 
    FROM reservas r
    JOIN imoveis i ON r.id_imovel = i.id
    WHERE r.id_usuario = ? AND r.status = 'confirmada' AND r.data_entrada >= CURDATE()
    ORDER BY r.data_entrada ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$proximas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar últimas reservas do usuário
$stmt = $pdo->prepare("
    SELECT r.*, i.titulo, i.cidade, i.estado, i.foto_principal  
    FROM reservas r
    JOIN imoveis i ON r.id_imovel = i.id
    WHERE r.id_usuario = ?
    ORDER BY r.data_reserva DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$ultimas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Aluga<span>Fácil</span></h3>
            </div>
            
            <div class="sidebar-menu">
                <ul>
                    <li>
                        <a href="dashboard.php" class="active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="minhas-reservas.php">
                            <i class="fas fa-calendar-check"></i>
                            <span>Minhas Reservas</span>
                        </a>
                    </li>
                    <li>
                        <a href="favoritos.php">
                            <i class="fas fa-heart"></i>
                            <span>Favoritos</span>
                        </a>
                    </li>
                    <li>
                        <a href="mensagens.php">
                            <i class="fas fa-envelope"></i>
                            <span>Mensagens</span>
                        </a>
                    </li>
                    <li>
                        <a href="avaliacoes.php">
                            <i class="fas fa-star"></i>
                            <span>Avaliações</span>
                        </a>
                    </li>
                    <li>
                        <a href="perfil.php">
                            <i class="fas fa-user"></i>
                            <span>Meu Perfil</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h2>Dashboard</h2>
                </div>
                <div class="dashboard-user">
                    <img src="img/user-avatar.png" alt="Avatar">
                    <div>
                        <h4><?php echo htmlspecialchars($user['nome']); ?></h4>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="stats-card primary">
                    <i class="fas fa-calendar-check"></i>
                    <div class="stats-info">
                        <h4>Reservas Ativas</h4>
                        <p><?php echo $stats['reservas_ativas']; ?></p>
                    </div>
                </div>
                
                <div class="stats-card success">
                    <i class="fas fa-check-circle"></i>
                    <div class="stats-info">
                        <h4>Reservas Concluídas</h4>
                        <p><?php echo $stats['reservas_concluidas']; ?></p>
                    </div>
                </div>
                
                <div class="stats-card warning">
                    <i class="fas fa-times-circle"></i>
                    <div class="stats-info">
                        <h4>Reservas Canceladas</h4>
                        <p><?php echo $stats['reservas_canceladas']; ?></p>
                    </div>
                </div>
                
                <div class="stats-card danger">
                    <i class="fas fa-heart"></i>
                    <div class="stats-info">
                        <h4>Favoritos</h4>
                        <p><?php echo $stats['favoritos']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-table">
                <h3>Próximas Reservas</h3>
                
                <?php if (empty($proximas_reservas)): ?>
                    <div class="alert alert-info">
                        Você não possui reservas futuras. <a href="imoveis.php">Encontre um imóvel para alugar</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Imóvel</th>
                                    <th>Local</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximas_reservas as $reserva): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($reserva['foto_principal']); ?>" alt="<?php echo htmlspecialchars($reserva['titulo']); ?>" width="50" height="50" class="rounded me-2">
                                            <?php echo htmlspecialchars($reserva['titulo']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($reserva['cidade'] . ', ' . $reserva['estado']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($reserva['data_entrada'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($reserva['data_saida'])); ?></td>
                                    <td>R$ <?php echo number_format($reserva['valor_total'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge bg-success">Confirmada</span>
                                    </td>
                                    <td>
                                        <a href="reserva-detalhes.php?id=<?php echo $reserva['id']; ?>" class="btn btn-sm btn-primary">Detalhes</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-table">
                <h3>Últimas Reservas</h3>
                
                <?php if (empty($ultimas_reservas)): ?>
                    <div class="alert alert-info">
                        Você ainda não fez nenhuma reserva. <a href="imoveis.php">Encontre um imóvel para alugar</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Imóvel</th>
                                    <th>Local</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_reservas as $reserva): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($reserva['foto_principal']); ?>" alt="<?php echo htmlspecialchars($reserva['titulo']); ?>" width="50" height="50" class="rounded me-2">
                                            <?php echo htmlspecialchars($reserva['titulo']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($reserva['cidade'] . ', ' . $reserva['estado']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($reserva['data_entrada'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($reserva['data_saida'])); ?></td>
                                    <td>R$ <?php echo number_format($reserva['valor_total'], 2, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch ($reserva['status']) {
                                            case 'pendente':
                                                $status_class = 'bg-warning';
                                                $status_text = 'Pendente';
                                                break;
                                            case 'confirmada':
                                                if (strtotime($reserva['data_saida']) < time()) {
                                                    $status_class = 'bg-secondary';
                                                    $status_text = 'Concluída';
                                                } else {
                                                    $status_class = 'bg-success';
                                                    $status_text = 'Confirmada';
                                                }
                                                break;
                                            case 'cancelada':
                                                $status_class = 'bg-danger';
                                                $status_text = 'Cancelada';
                                                break;
                                            default:
                                                $status_class = 'bg-info';
                                                $status_text = ucfirst($reserva['status']);
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <a href="reserva-detalhes.php?id=<?php echo $reserva['id']; ?>" class="btn btn-sm btn-primary">Detalhes</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="minhas-reservas.php" class="btn btn-outline-primary">Ver Todas as Reservas</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>


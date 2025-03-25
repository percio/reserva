<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verificar se o ID do imóvel foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('imoveis.php');
}

$imovel_id = intval($_GET['id']);

// Buscar dados do imóvel
$stmt = $pdo->prepare("
    SELECT i.*, u.nome as proprietario_nome, u.email as proprietario_email, u.telefone as proprietario_telefone
    FROM imoveis i
    JOIN usuarios u ON i.id_proprietario = u.id
    WHERE i.id = ? AND i.status = 'ativo'
");
$stmt->execute([$imovel_id]);
$imovel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$imovel) {
    // Imóvel não encontrado ou inativo
    redirect('imoveis.php');
}

// Buscar características do imóvel
$stmt = $pdo->prepare("SELECT * FROM caracteristicas_imoveis WHERE id_imovel = ?");
$stmt->execute([$imovel_id]);
$caracteristicas = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar regras do imóvel
$stmt = $pdo->prepare("SELECT regra FROM regras_imoveis WHERE id_imovel = ? ORDER BY id");
$stmt->execute([$imovel_id]);
$regras = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Buscar imagens do imóvel
$stmt = $pdo->prepare("SELECT * FROM imagens_imoveis WHERE id_imovel = ? ORDER BY ordem");
$stmt->execute([$imovel_id]);
$imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar avaliações do imóvel
$stmt = $pdo->prepare("
    SELECT a.*, u.nome as usuario_nome
    FROM avaliacoes a
    JOIN usuarios u ON a.id_usuario = u.id
    WHERE a.id_imovel = ? AND a.status = 'aprovada'
    ORDER BY a.data_avaliacao DESC
    LIMIT 5
");
$stmt->execute([$imovel_id]);
$avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular média das avaliações
$stmt = $pdo->prepare("
    SELECT AVG(nota) as media_avaliacoes, COUNT(*) as total_avaliacoes
    FROM avaliacoes
    WHERE id_imovel = ? AND status = 'aprovada'
");
$stmt->execute([$imovel_id]);
$dados_avaliacoes = $stmt->fetch(PDO::FETCH_ASSOC);
$media_avaliacoes = round($dados_avaliacoes['media_avaliacoes'] ?? 0, 1);
$total_avaliacoes = $dados_avaliacoes['total_avaliacoes'] ?? 0;

// Verificar disponibilidade (próximos 30 dias)
$hoje = date('Y-m-d');
$futuro = date('Y-m-d', strtotime('+30 days'));

$stmt = $pdo->prepare("
    SELECT data_entrada, data_saida
    FROM reservas
    WHERE id_imovel = ? AND status = 'confirmada'
    AND ((data_entrada BETWEEN ? AND ?) OR (data_saida BETWEEN ? AND ?))
");
$stmt->execute([$imovel_id, $hoje, $futuro, $hoje, $futuro]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Criar array de datas reservadas
$datas_reservadas = [];
foreach ($reservas as $reserva) {
    $inicio = new DateTime($reserva['data_entrada']);
    $fim = new DateTime($reserva['data_saida']);
    $intervalo = new DateInterval('P1D');
    $periodo = new DatePeriod($inicio, $intervalo, $fim->modify('+1 day'));
    
    foreach ($periodo as $data) {
        $datas_reservadas[] = $data->format('Y-m-d');
    }
}
$datas_reservadas = array_unique($datas_reservadas);

// Processar formulário de reserva se enviado
$erro_reserva = null;
$sucesso_reserva = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar'])) {
    if (!isset($_SESSION['user_id'])) {
        // Usuário não logado, redirecionar para login
        $_SESSION['reserva_pendente'] = [
            'imovel_id' => $imovel_id,
            'check_in' => $_POST['check_in'],
            'check_out' => $_POST['check_out']
        ];
        redirect('login.php?redirect=imovel.php?id=' . $imovel_id);
    }
    
    $check_in = filter_input(INPUT_POST, 'check_in', FILTER_SANITIZE_STRING);
    $check_out = filter_input(INPUT_POST, 'check_out', FILTER_SANITIZE_STRING);
    $adultos = filter_input(INPUT_POST, 'adultos', FILTER_SANITIZE_NUMBER_INT);
    $criancas = filter_input(INPUT_POST, 'criancas', FILTER_SANITIZE_NUMBER_INT);
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    
    // Validações
    if (empty($check_in) || empty($check_out) || empty($adultos)) {
        $erro_reserva = "Preencha todos os campos obrigatórios";
    } elseif ($check_in >= $check_out) {
        $erro_reserva = "A data de check-out deve ser posterior à data de check-in";
    } elseif (strtotime($check_in) < strtotime($hoje)) {
        $erro_reserva = "A data de check-in não pode ser no passado";
    } elseif ((int)$adultos <= 0 || (int)$adultos > $imovel['capacidade']) {
        $erro_reserva = "O número de adultos deve estar entre 1 e a capacidade máxima do imóvel";
    } else {
        // Verificar disponibilidade
        $disponivel = check_property_availability($pdo, $imovel_id, $check_in, $check_out);
        
        if (!$disponivel) {
            $erro_reserva = "O imóvel não está disponível para as datas selecionadas";
        } else {
            // Calcular valor total
            $valor_total = calculate_reservation_total($pdo, $imovel_id, $check_in, $check_out);
            
            // Gerar código único para a reserva
            $codigo = 'RES' . strtoupper(substr(md5(uniqid()), 0, 8));
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO reservas (
                        id_usuario, id_imovel, codigo, data_entrada, data_saida, 
                        adultos, criancas, valor_diaria, valor_taxas, valor_total, 
                        forma_pagamento, status, observacoes, data_reserva
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pix', 'pendente', ?, NOW()
                    )
                ");
                
                $valor_diaria = $imovel['valor_diaria'];
                $valor_taxas = $imovel['taxa_limpeza'] + $imovel['taxa_servico'];
                
                $stmt->execute([
                    $_SESSION['user_id'], $imovel_id, $codigo, $check_in, $check_out,
                    $adultos, $criancas, $valor_diaria, $valor_taxas, $valor_total,
                    'pix', 'pendente', $observacoes
                ]);
                
                $sucesso_reserva = true;
                $reserva_id = $pdo->lastInsertId();
                
                // Redirecionar para página de confirmação de reserva
                redirect('reserva-confirmacao.php?id=' . $reserva_id);
                
            } catch (PDOException $e) {
                $erro_reserva = "Erro ao processar reserva: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($imovel['titulo']); ?> - AlugaFácil</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .property-gallery .carousel-item {
            height: 500px;
        }
        
        .property-gallery .carousel-item img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        
        .gallery-thumbs {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            margin-top: 15px;
        }
        
        .gallery-thumbs img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .gallery-thumbs img:hover {
            opacity: 0.8;
        }
        
        .feature-list {
            list-style: none;
            padding-left: 0;
            display: flex;
            flex-wrap: wrap;
        }
        
        .feature-list li {
            width: 50%;
            padding: 5px 0;
        }
        
        .feature-active {
            color: #28a745;
        }
        
        .feature-inactive {
            color: #dc3545;
            text-decoration: line-through;
        }
        
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .calendar-header {
            grid-column: span 7;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .calendar-day {
            padding: 8px;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .calendar-day.unavailable {
            background-color: #f8d7da;
            color: #721c24;
            cursor: not-allowed;
            text-decoration: line-through;
        }
        
        .calendar-day.available {
            background-color: #d4edda;
            color: #155724;
        }
        
        .calendar-day.selected {
            background-color: #3498db;
            color: white;
        }
        
        .calendar-day.empty {
            visibility: hidden;
        }
        
        .review-card {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        
        .review-card:last-child {
            border-bottom: none;
        }
        
        .rating {
            color: #ffc107;
        }
        
        .total-price mb-3 d-none">
            <div class="d-flex justify-content-between align-items-center">
                <strong>Total Estimado:</strong>
                <span class="h4 mb-0" id="total_price">R$ 0,00</span>
            </div>
            <div id="price_breakdown" class="small text-muted">
                <!-- Detalhamento do preço será preenchido via JavaScript -->
            </div>
            <div id="total_price_container" class="small text-muted">
                <!-- Detalhamento do preço será preenchido via JavaScript -->
            </div>
        </div>
        
        <div class="availability-calendar mt-4">
            <h5>Disponibilidade</h5>
            <div class="calendar-container mt-3">
                <div class="calendar-header">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="prev-month">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="current-month">Agosto 2023</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="next-month">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar mt-2">
                    <!-- Calendário será preenchido via JavaScript -->
                </div>
                <div class="calendar-legend mt-2 d-flex justify-content-around small">
                    <div>
                        <span class="badge bg-success">Disponível</span>
                    </div>
                    <div>
                        <span class="badge bg-danger">Reservado</span>
                    </div>
                    <div>
                        <span class="badge bg-primary">Selecionado</span>
                    </div>
                </div>
            </div>
            
            <!-- Destaques Relacionados -->
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Você também pode gostar</h2>
                </div>
                <div class="card-body">
                    <!-- Consulta para buscar imóveis similares será adicionada aqui -->
                    <div class="text-center">
                        <a href="imoveis.php?cidade=<?php echo urlencode($imovel['cidade']); ?>" class="btn btn-outline-primary">
                            Ver mais imóveis em <?php echo htmlspecialchars($imovel['cidade']); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <footer>
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <h3>Aluga<span>Fácil</span></h3>
                            <p>Sua melhor opção para aluguel de temporada no litoral.</p>
                            <div class="social-icons">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fab fa-whatsapp"></i></a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h4>Links Rápidos</h4>
                            <ul>
                                <li><a href="index.php">Início</a></li>
                                <li><a href="imoveis.php">Imóveis</a></li>
                                <li><a href="sobre.php">Sobre Nós</a></li>
                                <li><a href="contato.php">Contato</a></li>
                                <li><a href="termos.php">Termos de Uso</a></li>
                                <li><a href="privacidade.php">Política de Privacidade</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Galeria de Fotos
        function setActiveSlide(index) {
            const carouselElement = document.getElementById('propertyCarousel');
            const carousel = bootstrap.Carousel.getInstance(carouselElement);
            carousel.to(index);
        }
        
        // Calendário de Disponibilidade
        document.addEventListener('DOMContentLoaded', function() {
            const reservedDates = <?php echo json_encode($datas_reservadas); ?>;
            const checkInInput = document.getElementById('check_in');
            const checkOutInput = document.getElementById('check_out');
            const totalPriceElement = document.getElementById('total_price');
            const priceBreakdownElement = document.getElementById('price_breakdown');
            const totalPriceContainer = document.querySelector('.total-price');
            
            let currentDate = new Date();
            let selectedCheckIn = null;
            let selectedCheckOut = null;
            
            // Renderizar calendário inicial
            renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
            
            // Event listeners para navegação do calendário
            document.getElementById('prev-month').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
            });
            
            document.getElementById('next-month').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
            });
            
            // Event listeners para campos de data
            checkInInput.addEventListener('change', function() {
                selectedCheckIn = this.value;
                updateCalendarSelection();
                calculatePrice();
            });
            
            checkOutInput.addEventListener('change', function() {
                selectedCheckOut = this.value;
                updateCalendarSelection();
                calculatePrice();
            });
            
            function renderCalendar(year, month) {
                const monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                document.getElementById('current-month').textContent = `${monthNames[month]} ${year}`;
                
                const calendarElement = document.querySelector('.calendar');
                calendarElement.innerHTML = '';
                
                // Dias da semana
                const dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                for (let i = 0; i < 7; i++) {
                    const dayNameElement = document.createElement('div');
                    dayNameElement.className = 'day-name';
                    dayNameElement.textContent = dayNames[i];
                    calendarElement.appendChild(dayNameElement);
                }
                
                // Obter o primeiro dia do mês e o número de dias
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                
                // Adicionar dias vazios para o início do mês
                for (let i = 0; i < firstDay.getDay(); i++) {
                    const emptyDay = document.createElement('div');
                    emptyDay.className = 'calendar-day empty';
                    calendarElement.appendChild(emptyDay);
                }
                
                // Adicionar todos os dias do mês
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const dateString = date.toISOString().split('T')[0];
                    
                    // Verificar se a data é antes de hoje
                    if (date < today) {
                        dayElement.className = 'calendar-day unavailable';
                    }
                    
                    // Verificar se a data está reservada
                    else if (reservedDates.includes(dateString)) {
                        dayElement.className = 'calendar-day unavailable';
                    }
                    
                    // Verificar se a data está selecionada
                    else if (
                        (selectedCheckIn && dateString === selectedCheckIn) ||
                        (selectedCheckOut && dateString === selectedCheckOut) ||
                        (selectedCheckIn && selectedCheckOut && 
                         dateString > selectedCheckIn && dateString < selectedCheckOut)
                    ) {
                        dayElement.className = 'calendar-day selected';
                    }
                    
                    // Data disponível
                    else {
                        dayElement.className = 'calendar-day available';
                        
                        // Adicionar event listener para selecionar data
                        dayElement.addEventListener('click', function() {
                            const clickedDate = this.dataset.date;
                            
                            if (!selectedCheckIn || (selectedCheckIn && selectedCheckOut)) {
                                // Selecionar check-in se nenhuma data estiver selecionada ou ambas estiverem selecionadas
                                selectedCheckIn = clickedDate;
                                selectedCheckOut = null;
                                checkInInput.value = clickedDate;
                                checkOutInput.value = '';
                            } else {
                                // Selecionar check-out se for após o check-in
                                selectedCheckOut = clickedDate;
                                checkOutInput.value = clickedDate;
                            }
                            
                            updateCalendarSelection();
                            calculatePrice();
                        });
                    }
                    
                    calendarElement.appendChild(dayElement);
                }
            }
            
            function updateCalendarSelection() {
                const dayElements = document.querySelectorAll('.calendar-day');
                
                dayElements.forEach(day => {
                    if (day.classList.contains('empty') || day.classList.contains('unavailable')) {
                        return;
                    }
                    
                    const dateString = day.dataset.date;
                    
                    if (
                        (selectedCheckIn && dateString === selectedCheckIn) ||
                        (selectedCheckOut && dateString === selectedCheckOut)
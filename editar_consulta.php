<?php
// Inicia a sessão
session_start();
 
// ** PROTEÇÃO DE ACESSO **
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Configuração e Conexão (Variável $link)
require_once 'config.php'; 

$path_prefix = ''; 
$consulta_id = null;
$error = '';
$success = '';

// Variáveis para preencher o formulário
$nome = $email = $telefone = $tipo_servico = $data_agendada = $hora_agendada = $motivo_contato = $status = "";
$id_paciente = null;


// -------------------------------------------------------------------
// 1. LÓGICA DE PROCESSAMENTO DO FORMULÁRIO (POST - UPDATE)
// -------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Captura o ID da consulta e do paciente (campos ocultos)
    $consulta_id = $_POST['id_consulta'];
    $id_paciente = $_POST['id_paciente'];

    // Captura os dados do formulário
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $tipo_servico = trim($_POST['tipo_servico']);
    $data_agendada = trim($_POST['data_agendada']);
    $hora_agendada = trim($_POST['hora_agendada']);
    $motivo_contato = trim($_POST['motivo_contato']);
    $status = trim($_POST['status']);

    // Validação básica
    if (empty($nome) || empty($email) || empty($tipo_servico) || empty($data_agendada)) {
        $error = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Inicia a transação para garantir que ambas as atualizações ocorram
        mysqli_begin_transaction($link);
        $update_ok = true;

        // --- UPDATE NA TABELA PACIENTES ---
        $sql_update_paciente = "UPDATE pacientes SET nome = ?, email = ?, telefone = ? WHERE id_paciente = ?";
        if ($stmt_p = mysqli_prepare($link, $sql_update_paciente)) {
            mysqli_stmt_bind_param($stmt_p, "sssi", $nome, $email, $telefone, $id_paciente);
            if (!mysqli_stmt_execute($stmt_p)) {
                $update_ok = false;
                $error .= "Erro ao atualizar dados do paciente: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_p);
        } else {
            $update_ok = false;
            $error .= "Erro de preparação (paciente).";
        }

        // --- UPDATE NA TABELA CONSULTAS ---
        $sql_update_consulta = "UPDATE consultas SET tipo_servico = ?, data_agendada = ?, hora_agendada = ?, motivo_contato = ?, status = ? WHERE id_consulta = ?";
        if ($stmt_c = mysqli_prepare($link, $sql_update_consulta)) {
            mysqli_stmt_bind_param($stmt_c, "sssssi", $tipo_servico, $data_agendada, $hora_agendada, $motivo_contato, $status, $consulta_id);
            if (!mysqli_stmt_execute($stmt_c)) {
                $update_ok = false;
                $error .= "Erro ao atualizar dados da consulta: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt_c);
        } else {
            $update_ok = false;
            $error .= "Erro de preparação (consulta).";
        }

        // Finaliza a transação
        if ($update_ok) {
            mysqli_commit($link);
            $success = "✅ Consulta #{$consulta_id} atualizada com sucesso!";
        } else {
            mysqli_rollback($link);
        }
    }
}


// -------------------------------------------------------------------
// 2. LÓGICA DE BUSCA DE DADOS (GET ou após POST)
// -------------------------------------------------------------------

// Se o ID for recebido (via GET ou POST)
if (isset($_GET['id']) || isset($_POST['id_consulta'])) {
    
    $consulta_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['id_consulta'];

    $sql_fetch = "
        SELECT 
            c.id_paciente, c.tipo_servico, c.data_agendada, c.hora_agendada, c.motivo_contato, c.status,
            p.nome, p.email, p.telefone
        FROM 
            consultas c
        JOIN 
            pacientes p ON c.id_paciente = p.id_paciente
        WHERE
            c.id_consulta = ?
    ";

    if ($stmt_fetch = mysqli_prepare($link, $sql_fetch)) {
        mysqli_stmt_bind_param($stmt_fetch, "i", $consulta_id);
        
        if (mysqli_stmt_execute($stmt_fetch)) {
            $result_fetch = mysqli_stmt_get_result($stmt_fetch);
            if (mysqli_num_rows($result_fetch) == 1) {
                $data = mysqli_fetch_assoc($result_fetch);
                
                // Preenche as variáveis para o formulário
                $id_paciente = $data['id_paciente'];
                $nome = $data['nome'];
                $email = $data['email'];
                $telefone = $data['telefone'];
                $tipo_servico = $data['tipo_servico'];
                $data_agendada = $data['data_agendada'];
                $hora_agendada = $data['hora_agendada'];
                $motivo_contato = $data['motivo_contato'];
                $status = $data['status'];
            } else {
                $error = "Consulta não encontrada.";
            }
        } else {
            $error = "Erro ao buscar dados da consulta: " . mysqli_error($link);
        }
        mysqli_stmt_close($stmt_fetch);
    }
} else {
    $error = "ID da consulta não fornecido para edição.";
}


include_once 'includes/header.php'; 
?>

<section class="page-content appointment-section">
    <h1>Editar Consulta #<?php echo htmlspecialchars($consulta_id); ?></h1>

    <div class="alert-messages">
        <?php if (!empty($error)): ?>
            <div class="alert error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert success">👍 <?php echo $success; ?></div>
        <?php endif; ?>
    </div>

    <?php if ($consulta_id && empty($error)): ?>
    
        <div class="appointment-form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="appointment-form">
                
                <input type="hidden" name="id_consulta" value="<?php echo htmlspecialchars($consulta_id); ?>">
                <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($id_paciente); ?>">

                <fieldset class="form-group">
                    <legend>Dados Pessoais do Paciente</legend>
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>

                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

                    <label for="telefone">Telefone / WhatsApp</label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>">
                </fieldset>
                
                <fieldset class="form-group">
                    <legend>Detalhes e Agendamento</legend>
                    
                    <label for="tipo_servico">Serviço/Tipo de Consulta</label>
                    <select id="tipo_servico" name="tipo_servico" required>
                        <option value="Psicoterapia Individual" <?php if ($tipo_servico == 'Psicoterapia Individual') echo 'selected'; ?>>Psicoterapia Individual</option>
                        <option value="Terapia em Grupo" <?php if ($tipo_servico == 'Terapia em Grupo') echo 'selected'; ?>>Terapia em Grupo</option>
                        <option value="Workshop/Palestra" <?php if ($tipo_servico == 'Workshop/Palestra') echo 'selected'; ?>>Workshop/Palestra</option>
                        <option value="Outro" <?php if ($tipo_servico == 'Outro') echo 'selected'; ?>>Outro</option>
                    </select>

                    <label for="data_agendada">Data Agendada</label>
                    <input type="date" id="data_agendada" name="data_agendada" value="<?php echo htmlspecialchars($data_agendada); ?>" required>
                    
                    <label for="hora_agendada">Hora Agendada</label>
                    <input type="time" id="hora_agendada" name="hora_agendada" value="<?php echo htmlspecialchars($hora_agendada); ?>">

                    <label for="motivo_contato">Motivo Principal da Consulta (Mensagem)</label>
                    <textarea id="motivo_contato" name="motivo_contato" rows="4"><?php echo htmlspecialchars($motivo_contato); ?></textarea>
                    
                    <label for="status">Status da Consulta</label>
                    <select id="status" name="status">
                        <option value="Pendente" <?php if ($status == 'Pendente') echo 'selected'; ?>>Pendente</option>
                        <option value="Confirmada" <?php if ($status == 'Confirmada') echo 'selected'; ?>>Confirmada</option>
                        <option value="Cancelada" <?php if ($status == 'Cancelada') echo 'selected'; ?>>Cancelada</option>
                        <option value="Concluída" <?php if ($status == 'Concluída') echo 'selected'; ?>>Concluída</option>
                    </select>
                </fieldset>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                    <a href="painel_admin.php" class="btn-secondary">Voltar para a Lista</a>
                </div>
            </form>
        </div>

    <?php endif; ?>

</section>

<?php
include_once 'includes/footer.php';
?>
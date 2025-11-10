<?php
// Inicia a sessão
session_start();
 
// ** PROTEÇÃO DE ACESSO **
// Verifica se o usuário não está logado; se não estiver, redireciona para a página de login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Define o prefixo de caminho (está na raiz)
$path_prefix = ''; 

// Inclui config (conexão $link) e header
require_once 'config.php'; 
include_once 'includes/header.php'; 

$mensagem_status = ''; // Variável para mensagens de sucesso/erro

// -------------------------------------------------------------------
// LÓGICA DE AÇÕES (UPDATE STATUS e DELETE)
// -------------------------------------------------------------------
if (isset($_GET['action']) && isset($_GET['id'])) {
    
    $id_consulta = (int)$_GET['id'];
    $action = $_GET['action'];
    $novo_status = '';

    if ($action == 'confirmar' || $action == 'cancelar') {
        // --- UPDATE STATUS LOGIC ---
        $novo_status = ($action == 'confirmar') ? 'Confirmada' : 'Cancelada';
        
        $sql_update = "UPDATE consultas SET status = ? WHERE id_consulta = ?";
        
        if ($stmt = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt, "si", $param_status, $param_id);
            
            $param_status = $novo_status;
            $param_id = $id_consulta;
            
            if (mysqli_stmt_execute($stmt)) {
                $mensagem_status = "<div class='alert success'>✅ Status da consulta #{$id_consulta} alterado para {$novo_status} com sucesso!</div>";
            } else {
                $mensagem_status = "<div class='alert error'>❌ Erro ao atualizar status: " . mysqli_error($link) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }

    } elseif ($action == 'deletar') {
        // --- DELETE LOGIC ---
        // Ação de DELETE: Exclui a consulta
        $sql_delete = "DELETE FROM consultas WHERE id_consulta = ?";
        
        if ($stmt = mysqli_prepare($link, $sql_delete)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            $param_id = $id_consulta;
            
            if (mysqli_stmt_execute($stmt)) {
                // Sucesso na exclusão
                $mensagem_status = "<div class='alert success'>🗑️ Consulta #{$id_consulta} excluída com sucesso!</div>";
            } else {
                $mensagem_status = "<div class='alert error'>❌ Erro ao excluir consulta: " . mysqli_error($link) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// -------------------------------------------------------------------
// LÓGICA DE BUSCA DE CONSULTAS (MANTIDA)
// -------------------------------------------------------------------

$sql = "
    SELECT 
        c.id_consulta, 
        c.tipo_servico AS servico,           
        c.data_agendada AS data_preferencial, 
        c.motivo_contato AS mensagem,           
        c.data_solicitacao, 
        c.status, 
        p.nome, 
        p.email, 
        p.telefone 
    FROM 
        consultas c
    JOIN 
        pacientes p ON c.id_paciente = p.id_paciente
    ORDER BY 
        c.data_solicitacao DESC
";

$result = mysqli_query($link, $sql);

// Verifica se a busca retornou erro
if (!$result) {
    $erro_bd = "Erro ao buscar consultas: " . mysqli_error($link);
}
?>

<section class="page-content admin-section">
    <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?></h2>
    <h1>Lista de Consultas Agendadas</h1>
    
    <?php echo $mensagem_status; ?>

    <?php 
    // Exibe erro de BD, se houver
    if (isset($erro_bd)) {
        echo "<div class='alert error'>❌ Erro no Banco de Dados: " . $erro_bd . "</div>";
    }
    
    // Verifica se há resultados
    elseif ($result && mysqli_num_rows($result) > 0) {
    ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente/Email</th>
                        <th>Telefone</th>
                        <th>Serviço</th>
                        <th>Data Pref.</th>
                        <th>Data Solicitação</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Loop para exibir cada consulta
                    while ($consulta = mysqli_fetch_assoc($result)) {
                        
                        // Formatação de datas para melhor visualização
                        $data_solicitacao = date('d/m/Y H:i', strtotime($consulta['data_solicitacao']));
                        $data_preferencial = $consulta['data_preferencial'] ? date('d/m/Y', strtotime($consulta['data_preferencial'])) : '-';

                        // Define a classe CSS baseada no status
                        $status_class = strtolower(str_replace(' ', '-', $consulta['status']));
                    ?>
                        <tr>
                            <td><?php echo $consulta['id_consulta']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($consulta['nome']); ?></strong><br>
                                <small><?php echo htmlspecialchars($consulta['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($consulta['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($consulta['servico']); ?></td>
                            <td><?php echo $data_preferencial; ?></td>
                            <td><?php echo $data_solicitacao; ?></td>
                            <td><span class="status-badge status-<?php echo $status_class; ?>"><?php echo htmlspecialchars($consulta['status']); ?></span></td>
                            <td class="action-buttons">
                                <a href="?action=confirmar&id=<?php echo $consulta['id_consulta']; ?>" class="btn-action confirm" title="Confirmar">✅</a>
                                
                                <a href="?action=cancelar&id=<?php echo $consulta['id_consulta']; ?>" class="btn-action cancel" title="Cancelar">❌</a>
                                
                                <a href="editar_consulta.php?id=<?php echo $consulta['id_consulta']; ?>" class="btn-action edit" title="Editar">✏️</a>

                                <a href="?action=deletar&id=<?php echo $consulta['id_consulta']; ?>" 
                                   class="btn-action delete" 
                                   title="Deletar" 
                                   onclick="return confirm('Tem certeza que deseja DELETAR a consulta #<?php echo $consulta['id_consulta']; ?>? Esta ação é irreversível.');">🗑️</a>
                                
                                <button class="btn-action view" 
        title="Ver Detalhes (Motivo)" 
        data-id="<?php echo $consulta['id_consulta']; ?>"
        data-message="<?php echo htmlspecialchars($consulta['mensagem']); ?>" 
        onclick="openModal(this)">🔍</button>
                            </td>
                        </tr>
                    <?php 
                    } // Fim do loop while
                    ?>
                </tbody>
            </table>
        </div>
        
    <?php 
    } else {
        // Exibe mensagem se não houver consultas
        echo "<div class='alert info'>Não há nenhuma consulta solicitada no momento.</div>";
    }

    // Libera a memória do resultado
    if (isset($result) && $result !== false) {
        mysqli_free_result($result);
    }
    ?>

</section>
<div id="motivoModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>Detalhes da Consulta #<span id="modal-id"></span></h2>
        <p><strong>Motivo / Mensagem do Paciente:</strong></p>
        <p id="modal-message"></p>
    </div>
</div>
<script>
    // Função para abrir o modal
    function openModal(button) {
        var modal = document.getElementById("motivoModal");
        var message = button.getAttribute("data-message");
        var id = button.getAttribute("data-id");

        document.getElementById("modal-id").textContent = id;
        document.getElementById("modal-message").textContent = message;
        
        modal.style.display = "block";
    }

    // Função para fechar o modal
    function closeModal() {
        var modal = document.getElementById("motivoModal");
        modal.style.display = "none";
    }

    // Fecha o modal se o usuário clicar fora dele
    window.onclick = function(event) {
        var modal = document.getElementById("motivoModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
<?php
include_once 'includes/footer.php';
?>
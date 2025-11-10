<?php 
// Define o prefixo de caminho (está na raiz)
$path_prefix = '';
include_once 'includes/header.php'; 
?>

<section class="page-content hero-section">
    <h1>Cuidar de si é um ato de coragem</h1>
    <p>Agende sua consulta e comece sua jornada de bem-estar hoje.</p>
    
    <div class="hero-image-container">
        <img src="<?= $path_prefix ?>assets/hero_home.png" alt="Ilustração de autocuidado">
    </div>
</section>

<section class="page-content services-overview">
    <h2>Nossos Principais Serviços</h2>
    <div class="services-grid">
        
        <div class="service-item">
            <img src="<?= $path_prefix ?>assets/icone_psicoterapia_individual.png" alt="Ícone Psicoterapia Individual">
            <h3>Psicoterapia Individual</h3>
            <p>Sessões focadas em suas necessidades pessoais e desenvolvimento.</p>
        </div>

        <div class="service-item">
            <img src="<?= $path_prefix ?>assets/icone_psicoterapia_grupo.png" alt="Ícone Terapia em Grupo">
            <h3>Psicoterapia em Grupo</h3>
            <p>Troca de experiências e apoio em um ambiente acolhedor e seguro.</p>
        </div>

        <div class="service-item">
            <img src="<?= $path_prefix ?>assets/icone_workshop.png" alt="Ícone Workshop">
            <h3>Palestras e Workshops</h3>
            <p>Eventos temáticos para aprendizado e crescimento pessoal.</p>
        </div>

    </div>
</section>

<?php 
include_once 'includes/footer.php'; 
?>
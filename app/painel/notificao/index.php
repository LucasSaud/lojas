<?php
// CORE
include('../../_core/_includes/config.php');
include_once ('../../_core/_includes/functions/user.php');
// RESTRICT
restrict_estabelecimento();
restrict_expirado();
// SEO
$seo_subtitle = "Enviar Notificação";
$seo_description = "";
$seo_keywords = "";
// HEADER
$system_header .= "";
include('../_layout/head.php');
include('../_layout/top.php');
include('../_layout/sidebars.php');
include('../_layout/modal.php');



?>


<div class="middle minfit bg-gray">

	<div class="container">

		<div class="row">

			<div class="col-md-12">

        <div class="title-icon pull-left">
          <i class="lni lni-ticket"></i>
          <span>Adicionar cupom</span>
        </div>

        <div class="bread-box pull-right">
          <div class="bread">
            <a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>
            <span>/</span>
            <a href="<?php panel_url(); ?>/cupons">Cupons</a>
            <span>/</span>
            <a href="<?php panel_url(); ?>/cupons/adicionar">Adicionar</a>
          </div>
        </div>
        
			</div>

		</div>

		<!-- Content -->

		<div class="data box-white mt-16">

      <form id="the_form" class="form-default" method="POST" enctype="multipart/form-data">
    <!-- Outros campos do formulário -->
    
    <label for="titulo">Título da Notificação:</label>
    <input type="text" name="titulo" id="titulo" required>

    <label for="texto">Texto da Notificação:</label>
    <textarea name="texto" id="texto" required></textarea>

    <input type="submit" value="Enviar Notificação">
</form>


		</div>

		<!-- / Content -->

	</div>

</div>

<?php 
// FOOTER
$system_footer .= "";
include('../../_layout/rdp.php');
include('../../_layout/footer.php');
?>

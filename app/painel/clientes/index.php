<?php
// CORE
include('../../_core/_includes/config.php');
// RESTRICT
restrict_estabelecimento();
restrict_expirado();
// SEO
$seo_subtitle = "Pedidos";
$seo_description = "";
$seo_keywords = "";

global $db_con;
$eid = $_SESSION['estabelecimento']['id'];

// HEADER
$system_header .= "";
include('../_layout/head.php');
include('../_layout/top.php');
include('../_layout/sidebars.php');
include('../_layout/modal.php');

$sqlnp = "(SELECT count(*) FROM pedidos p WHERE p.whatsapp = cl.whatsapp and rel_estabelecimentos_id = cl.id_estabelecimento)";

if(isset($_GET['whatsapp'])){
    $numero = $_GET['whatsapp'];
    $sqlwhatsapp = " AND whatsapp like '%$numero%' ";
}

if(isset($_GET['nome'])){
    $nome = $_GET['nome'];
    $sqlnome = " AND nome like '%$nome%' ";
}

$order = 'nome';

if(isset($_GET['ordem'])){
    if(($_GET['ordem'] != 'ativos') AND ($_GET['ordem'] != 'naoativos')){ 
        
        $storder = $_GET['order'];
        
        $order = $_GET['ordem'];
        if($order == 'qtdpontos'){
            $order .= ' DESC';
        }
        if($order == 'npedidos'){
            $order = $sqlnp.' DESC';
        }
    }else{
        if($_GET['ordem'] == 'ativos'){
            $inscritos = " AND auth IS NOT NULL AND auth <> ''";
        }else{
            $inscritos = ' AND auth IS NULL';
        }
    }
}

$sql = "SELECT $sqlnp AS NPedidos, cl.* FROM clientes cl WHERE id_estabelecimento = '$eid' $sqlnome $sqlwhatsapp $inscritos ORDER BY $order";

$sql  = mysqli_query($db_con,$sql);

$total_results_full = mysqli_num_rows($sql);

$total_results = $total_results_full;

?>

<div class="middle minfit bg-gray">

	<div class="container">

		<div class="row">

			<div class="col-md-12">

				<div class="title-icon pull-left">
					<i class="lni lni-users"></i>
					<span>Clientes</span>
				</div>

				<div class="bread-box pull-right">
					<div class="bread">
						<a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>
						<span>/</span>
						<a href="<?php panel_url(); ?>/pedidos">Pedidos</a>
					</div>
				</div>

			</div>

		</div>

		<!-- Filters -->

		<div class="row">

			<div class="col-md-12">

				<div class="panel-group panel-filters">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" href="#collapse-filtros">
									<span class="desc">Filtrar</span>
									<i class="lni lni-funnel"></i>
									<div class="clear"></div>
								</a>
							</h4>
						</div>
						<div id="collapse-filtros" class="panel-collapse collapse <?php if( $_GET['filtered'] ) { echo 'in'; }; ?>">
							<div class="panel-body">

								<form class="form-filters form-100" method="GET">

									<div class="row">
										<div class="col-md-4 col-xs-6 col-sm-6">
											<div class="form-field-default">
												<label>Whatsapp:</label>
												<input type="text" name="whatsapp" placeholder="Whatsapp" value="<?php echo htmlclean( $numero ); ?>"/>
											</div>
										</div>
										<div class="clear visible-xs visible-sm"></div>
										<div class="col-md-4">
											<div class="form-field-default">
												<label>Nome do cliente:</label>
												<input type="text" name="nome" placeholder="Nome" value="<?php echo htmlclean( $nome ); ?>"/>
											</div>
										</div>
										
										<div class="col-md-4 col-xs-6 col-sm-6">
							              <div class="form-field-default">
							              <div class="clear"></div>
							               <label>Ordernar por:</label>
											<div class="fake-select">
												<i class="lni lni-chevron-down"></i>
												<select name="ordem">
		                                            <option value="nome" <?php if((isset($_GET['ordem'])) AND ($_GET['ordem'] == 'nome')){ echo 'SELECTED'; }?>>Nome</option>
		                                            <!--<option value="qtdpontos" <?php if((isset($_GET['ordem'])) AND ($_GET['ordem'] == 'qtdpontos')){ echo 'SELECTED'; }?>>Qtd pontos</option>-->
		                                            <option value="npedidos" <?php if((isset($_GET['ordem'])) AND ($_GET['ordem'] == 'npedidos')){ echo 'SELECTED'; }?>>Número de pedidos</option>
		                                            <option value="ativos" <?php if((isset($_GET['ordem'])) AND ($_GET['ordem'] == 'ativos')){ echo 'SELECTED'; }?>>Inscritos</option>
		                                            <option value="naoativos" <?php if((isset($_GET['ordem'])) AND ($_GET['ordem'] == 'naoativos')){ echo 'SELECTED'; }?>>Não inscritos</option>
												</select>
											</div>
							              </div>
										</div>
										
										<div class="clear visible-xs visible-sm"></div>
										<div class="col-md-3">
											<div class="form-field-default">
												<label class="hidden-xs hidden-sm"></label>
												<input type="hidden" name="filtered" value="1"/>
												<button>
													<span>Buscar</span>
													<i class="lni lni-search-alt"></i>
												</button>
											</div>
										</div>
									</div>
									<?php if( $_GET['filtered'] ) { ?>
									<div class="row">
										<div class="col-md-12">
										    <a href="<?php panel_url(); ?>/pedidos" class="limpafiltros"><i class="lni lni-close"></i> Limpar filtros</a>
										</div>
									</div>
									<?php } ?>
								</form>

							</div>
						</div>
					</div>
				</div> 

			</div>

		</div>

		<!-- / Filters -->

		<!-- Content -->

		<div class="listing">

			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-6">
					<span class="listing-title"><strong class="counter"><?php echo $total_results_full; ?></strong> Registros:</span>
				</div>
			</div>

			<div class="row">

				<div class="col-md-12">

					<table class="listing-table fake-table clean-table table-pedidos">
						<thead>
							<th>Whatsapp</th>
							<th>Nome</th>
							<!--<th>Pontuação</th>-->
							<th>Pedidos</th>
							<th>Inscrição</th>
						</thead>
						<tbody>

							<?php
                            while ( $data = mysqli_fetch_array( $sql ) ) {
                                $whatsapp = $data['whatsapp'];
                            ?>

							<tr class="fullwidth">
								<td>
                                    <span class="fake-table-title hidden-xs hidden-sm">whatsapp</span>
                                    <div class="fake-table-data"><span class="pedido-numero"></span><?php echo formato($whatsapp,'whatsapp'); ?></span></div>
                                    <div class="fake-table-break"></div>
								</td>
								<td>
                                    <span class="fake-table-title hidden-xs hidden-sm">Nome</span>
                                    <div class="fake-table-data"><?php echo htmlclean( $data['nome'] ); ?></div>
                                    <div class="fake-table-break"></div>
								</td>
								<!--<td class="hidden-xs hidden-sm">
                                    <span class="fake-table-title">Pntos</span>
                                    <div class="fake-table-data"><?php echo $data['qtdpontos']; ?></div>
                                    <div class="fake-table-break"></div>
								</td>-->
								<td>
								    <?php echo $data['NPedidos']; ?>
								</td>
								<td>
								    <?php if($data['auth'] != ''){
								        echo '<i class="lni lni-checkmark"></i>';
								    }else{
								        echo '<i class="lni lni-ban"></i>';
								    }?>
								</td>
								
							</tr>

                            <?php } ?>

                            <?php if( $total_results == 0 ) { ?>

                               <tr class="fullwidth">
                                <td colspan="6">
                                  <div class="fake-table-data">
                                    <span class="nulled">Nenhum registro cadastrado ou compatível com a sua filtragem!</span>
                                  </div>
                                  <div class="fake-table-break"></div>
                                </td>
                               </tr>

                            <?php } ?>

						</tbody>
					</table>

				</div>

			</div>

		</div>

		<!-- / Content -->

		<!-- Pagination -->

        <div class="paginacao">

          <ul class="pagination">

            <?php
            $paginationpath = "clientes";
            if($pagina > 1) {
              $back = $pagina-1;
              echo '<li class="page-item pagination-back"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$back.$getdata.' "><i class="lni lni-chevron-left"></i></a></li>';
            }
     
              for($i=$pagina-1; $i <= $pagina-1; $i++) {

                  if($i > 0) {
                  
                      echo '<li class="page-item pages-before"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$i.$getdata.' ">'.$i.'</a></li>';
                  }

              }

              if( $pagina >= 1 ) {

                echo '<li class="page-item active"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$i.$getdata.'" class="page-link">'.$i.'</a></li>';

              }

              for($i=$pagina+1; $i <= $pagina+1; $i++) {

                  if($i >= $total_paginas) {
                    break;
                  }  else {
                      echo '<li class="page-item pages-after"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$i.$getdata.' ">'.$i.'</a></li> ';
                  }
              
              }

            if($pagina < $total_paginas-1) {
              $next = $pagina+1;
              echo '<li class="page-item pagination-next"><a class="page-link" href=" '.get_panel_url().'/'.$paginationpath.'/?pagina='.$next.$getdata.' "><i class="lni lni-chevron-right"></i></a></li>';
            }

            ?>

          </ul>

        </div>

		<!-- / Pagination -->

	</div>

<?php 
// FOOTER
$system_footer .= "";
include('../_layout/rdp.php');
include('../_layout/footer.php');
?>
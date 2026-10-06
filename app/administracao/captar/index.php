<?php
// CORE
include('../../_core/_includes/config.php');
// RESTRICT
restrict('1');
// SEO
$seo_subtitle = "Usuários";
$seo_description = "";
$seo_keywords = "";
// HEADER
$system_header .= "";
include('../_layout/head.php');
include('../_layout/top.php');
include('../_layout/sidebars.php');
include('../_layout/modal.php');
?>

<?php

global $db_con;

// Chave da plataforma para a Places API do Google (GOOGLE_PLACES_KEY no .env).
$key = env( "GOOGLE_PLACES_KEY" );
$location = isset( $_GET['location'] ) ? trim( $_GET['location'] ) : "";
$type = isset( $_GET['type'] ) ? trim( $_GET['type'] ) : "";
$filtered = isset( $_GET['filtered'] ) ? $_GET['filtered'] : "";

// Busca empresas por texto ("<tipo> em <cidade>") na Places API (New). Uma chamada devolve nome,
// endereço, telefone, site e tipos. Devolve array( lugares, mensagem de erro ).
function captar_busca( $key,$type,$location ) {

	$ch = curl_init( "https://places.googleapis.com/v1/places:searchText" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/json",
		"X-Goog-Api-Key: ".$key,
		"X-Goog-FieldMask: places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.websiteUri,places.types",
	) );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( array(
		"textQuery" => trim( $type." em ".$location ),
		"languageCode" => "pt-BR",
		"regionCode" => "BR",
		"pageSize" => 20,
	) ) );
	$resposta = curl_exec( $ch );
	$erro = curl_error( $ch );
	curl_close( $ch );

	if( $resposta === false ) {
		return array( array(),"Não foi possível falar com o Google: ".$erro );
	}

	$dados = json_decode( $resposta,true );

	if( isset( $dados['error'] ) ) {
		return array( array(),"O Google recusou a busca: ".( isset( $dados['error']['message'] ) ? $dados['error']['message'] : "erro desconhecido" ) );
	}

	return array( isset( $dados['places'] ) && is_array( $dados['places'] ) ? $dados['places'] : array(),"" );

}
?>

<div class="middle minfit bg-gray">

	<div class="container">

		<div class="row">

			<div class="col-md-12">

				<div class="title-icon pull-left">
					<i class="lni lni-codepen"></i>
					<span>Captar</span>
				</div>

				<div class="bread-box pull-right">
					<div class="bread">
						<a href="<?php admin_url(); ?>"><i class="lni lni-home"></i></a>
						<span>/</span>
						<a href="<?php admin_url(); ?>">Captar</a>
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

										<div class="col-md-4">
											<div class="form-field-default">
												<label>Cidade:</label>
												<input type="text" name="location" placeholder="Cidade" value="<?php echo htmlclean( $location ); ?>"/>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-field-default">
												<label>Tipo:</label>
												<input type="text" name="type" placeholder="Tipo" value="<?php echo htmlclean( $type ); ?>"/>
											</div>
										</div>
										<div class="col-md-4">
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
										    <a href="<?php admin_url(); ?>/captar" class="limpafiltros"><i class="lni lni-close"></i> Limpar filtros</a>
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
				<div class="col-md-12">
					<span class="listing-title">Registros:</span>
				</div>
			</div>

			<div class="row">

				<div class="col-md-12">

					<?php if( $filtered == 1 ) { ?>

						<?php if( !$key ) { ?>

							<span>Configure a chave <strong>GOOGLE_PLACES_KEY</strong> no arquivo .env para usar a busca.</span>

						<?php } elseif( !$location && !$type ) { ?>

							<span>Informe a cidade e o tipo de empresa.</span>

						<?php } else { ?>

							<?php list( $lugares,$erro ) = captar_busca( $key,$type,$location ); ?>

							<?php if( $erro ) { ?>
								<span><?php echo htmlclean( $erro ); ?></span>
							<?php } elseif( !$lugares ) { ?>
								<span>Nenhuma empresa encontrada.</span>
							<?php } ?>

							<?php foreach( $lugares as $lugar ) { ?>
							<div class="col-md-4">
								<div class="captar-local">
									<span><?php echo htmlclean( isset( $lugar['displayName']['text'] ) ? $lugar['displayName']['text'] : "" ); ?></span><br/>
									<span><?php echo htmlclean( isset( $lugar['formattedAddress'] ) ? $lugar['formattedAddress'] : "" ); ?></span><br/>
									<span><?php echo htmlclean( isset( $lugar['nationalPhoneNumber'] ) ? $lugar['nationalPhoneNumber'] : "" ); ?></span><br/>
									<span><?php echo htmlclean( isset( $lugar['websiteUri'] ) ? $lugar['websiteUri'] : "" ); ?></span><br/>
									<span><?php echo htmlclean( isset( $lugar['types'] ) ? implode( ", ",$lugar['types'] ) : "" ); ?></span>
								</div>
							</div>
							<?php } ?>

						<?php } ?>

					<?php } ?>

				</div>

			</div>

		</div>

		<!-- / Content -->

	</div>

</div>

<?php 
// FOOTER
$system_footer .= "";
include('../_layout/rdp.php');
include('../_layout/footer.php');
?>
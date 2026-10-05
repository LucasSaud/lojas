<?php
global $back_button;
global $categoria;
global $app;
?>

<div class="header">

	<div class="minitop hidden-xs hidden-sm">

		<div class="container">

			<div class="row">

				<div class="col-md-6">

					<div class="info-badges-desktop">
						<?php if( $app['pedido_minimo'] ) { ?>
						<div class="info-badge">
							<i class="lni lni-money-protection"></i> 
							<span>Pedido minímo: <?php echo $app['pedido_minimo']; ?></span>
						</div>
						<?php } ?>
						<div class="info-badge">
							<i class="lni lni-user"></i> 
							<span>Olá <?php if(isset($_COOKIE['nomecli'])){ ?><?php print $_COOKIE['nomecli']; ?><?php } ?></span>
							<div class="clear"></div>
						</div>
					</div>

				</div>
				<div class="col-md-6">

					<div class="funcionamento">

						<?php if( data_info( "estabelecimentos", $app['id'], "funcionamento" ) == "1" ) { ?>

						<div class="aberto">

							<i class="lni lni-checkmark-circle"></i>
							<span>Estabelecimento aberto</span>

						</div>

						<?php } else { ?>

						<div class="fechado">

							<i class="lni lni-cross-circle"></i>
							<span>Estabelecimento fechado</span>

						</div>

						<?php } ?>

					</div>

				</div>

			</div>

		</div>

	</div>

	<div class="top">

		<div class="container">

			<div class="row align-middle hidden-sm hidden-xs">

				<div class="col-md-3">

					<div class="brand">
						<a href="<?php echo $app['url']; ?>">
							<img src="<?php echo $app['avatar']; ?>" alt="<?php echo $app['title']; ?>"/>
							<span><?php echo $app['title']; ?><strong class="colored">.</strong></span>
						</a>
					</div>

				</div>

				<div class="col-md-9">

					<div class="row">

						<div class="col-md-9">

							<div class="search-bar">

								<div class="clear"></div>

								<form class="align-middle" method="GET" <?php if( $inacao != "categoria" ) {?>action="<?php echo $app['url']; ?>/categoria"<?php } ?>>

									<input type="text" name="busca" placeholder="Digite sua busca..." value="<?php echo htmlclean( $_GET['busca'] ); ?>"/>
									<input type="hidden" name="categoria" value="<?php echo $categoria; ?>"/>
									<button>
										<i class="lni lni-search-alt"></i>
									</button>
									<div class="clear"></div>

								</form>

							</div>

						</div>

						<div class="col-md-3">
									<?php

									$eid = $app['id'];

									$subtotal = array();

									foreach( $_SESSION['sacola'][$eid] AS $key => $value ) {

										$produto = $value['id'];

										$query_produtos = mysqli_query( $db_con, "SELECT * FROM produtos WHERE id = '$produto' AND status = '1' ORDER BY id ASC LIMIT 1" );

										$data_produtos = mysqli_fetch_array( $query_produtos );

										if( $data_produtos['oferta'] == "1" ) {

											$valor_final = $data_produtos['valor_promocional'];

										} else {

											$valor_final = $data_produtos['valor'];

										}

										$subtotal[] = ( ( $valor_final + $_SESSION['sacola'][$eid][$key]['valor_adicional'] ) * $_SESSION['sacola'][$eid][$key]['quantidade'] );

									}



									$subtotal = array_sum( $subtotal );

									if( $subtotal >= $app['pedido_minimo_valor'] ) {

										$field_minimo = "1";

									}

									?>

							<a class="holder-shop-bag pull-right" href="<?php echo $app['url']; ?>/sacola" title="Minha sacola">
							
								<div>
									<div class="shop-bag">
										<i class="icone icone-sacola"></i>
										<span class="counter"><?php echo "R$ ".dinheiro( $subtotal, "BR"); ?></span>
									</div>
								</div>
							</a>

						</div>

					</div>

				</div>

			</div>

			<div class="row align-middle-mobile visible-sm visible-xs">

				<div class="col-md-3 col-sm-3 col-xs-3">

					<?php if( $back_button == "true" ) { ?>

						<div class="user-menu pull-left">
							<a href="javascript:history.back(1)" title="Voltar">
								<i class="lni lni-arrow-left"></i>
							</a>
						</div>

					<?php } else { ?>

						<div class="user-menu pull-left">
							<a href="#" class="sidrLeft" href="#sidebarLeft" title="Menu">
								<i class="lni lni-menu"></i>
							</a>
						</div>

					<?php } ?>

				</div>

				<div class="col-md-6 col-sm-6 col-xs-6 nopadd">

				</div>

				<div class="col-md-3 col-sm-3 col-xs-3">

					<div class="user-info pull-right">

						<div class="holder-shop-bag pull-right">
							<a href="<?php echo $app['url']; ?>/sacola" title="Minha sacola">
								<div class="shop-bag">
									<i class="icone icone-sacola"></i>
										<span class="counter"><?php echo "R$ ".dinheiro( $subtotal, "BR"); ?></span>
								</div>
							</a>
						</div>

					</div>

				</div>

			</div>


		</div>

	</div>

	<div class="navigator naver hidden-sm hidden-xs">

		<div class="container">

			<div class="row">

				<div class="col-md-12">

					<?php include('navigation.php'); ?>

				</div>

			</div>

		</div>

	</div>

</div>
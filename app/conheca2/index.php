<?php
// Endereço do site, das mesmas variáveis de ambiente usadas pelo sistema.
$dominio = getenv( "APP_DOMAIN" ) ?: "localhost";
$proto = getenv( "APP_HTTPS" ) === "0" ? "http://" : "https://";
$site = $proto.$dominio;
?>
<!DOCTYPE html>

<html>

    <head>

        <meta charset="utf-8">

        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

        <title>WorldShop - Catálogo e Cardápio de Produtos</title>
        
        <meta name=description content="Crie seu catálogo ou cardápio online de produtos com pedidos via WhatsApp." />
	
	    <meta name=keywords content="catalogo ou cardápio online, catalogo digital, catalogo online, cardapio online, catalogo via whatsapp, cardapios online, app de cardapio" />
	    
    	<meta name=resource-type content=document />
    	
    	<meta name=revisit-after content=1 />
    	
    	<meta name=distribution content=Global />
    	
    	<meta name=rating content=General />
    	
    	<meta name=author content="WorldShop - Catálogo ou Cardápio Online de Produtos" />
    	
    	<meta name=language content=pt-br />
    	
    	<meta name=doc-class content=Completed />
    	
    	<meta name=doc-rights content=Public />
    	
    	<meta name=Subject content="Crie seu catálogo ou cardápio online de produtos com pedidos via WhatsApp." />
    	
    	<meta name=audience content=all />
    	
    	<meta name=robots content="index,follow" />
    	
    	<meta name=googlebot content=all />
    	
    	<meta name=copyright content="WorldShop - Catálogo ou Cardápio Online de Produtos" />
    	
    	<meta name=url content="<?php echo $site; ?>" />
    	
    	<meta name=audience content=all />

        <meta name="viewport" content="width=device-width">
        
        <meta property="og:url" content="<?php echo $site; ?>/" />
        
    	<meta property="og:type" content="website" />
    	
    	<meta property="og:title" content="WorldShop - Catálogo ou Cardápio Online de Produtos" />
    	
    	<meta property="og:description" content="Crie seu catálogo ou cardápio online de produtos com pedidos via WhatsApp. WorldShop!" />
    	
    	<meta property="og:image" content="<?php echo $site; ?>/conheca2/img/favicon.png" />
    	
    	<link rel="shortcut icon" href="<?php echo $site; ?>/conheca2/img/favicon.png" type="image/x-icon">

        <link rel="icon" href="<?php echo $site; ?>/conheca2/img/favicon.png" type="image/x-icon">

        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet"> 

        <link rel="stylesheet" href="<?php echo $site; ?>/conheca2/style.css">

        <link rel="stylesheet" href="<?php echo $site; ?>/conheca2/css/bootstrap.css">

        <link rel="stylesheet" href="<?php echo $site; ?>/conheca2/css/bootstrap-theme.css">

        <link rel="stylesheet" href="<?php echo $site; ?>/conheca2/css/animate.css">

        <link rel="stylesheet" href="<?php echo $site; ?>/conheca2/plugins/lineicons/css/LineIcons.min.css">

        <link rel="stylesheet" href="<?php echo $site; ?>/conheca2/fonts/logo/logofont.css">
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <script src="<?php echo $site; ?>/conheca2/js/jquery.js"></script>

        <script src="<?php echo $site; ?>/conheca2/js/wow.min.js"></script>

        <script src="<?php echo $site; ?>/conheca2/js/calls.js"></script>

        <script>

            new WOW().init()

        </script>
		
    </head>

    <body>

<?php include('template/top.php'); ?>

<?php include('template/destaque.php'); ?>

<?php include('template/comofunciona.php'); ?>

<?php include('template/demonstracao.php'); ?>

<?php include('template/precos.php'); ?>

<?php include('template/novosprecos.php'); ?>

<?php include('template/duvidas.php'); ?>

<?php include('template/rodape.php'); ?>

	<script src="js/bootstrap.min.js"></script>

	<script src="js/jquery.sticky.js"></script>

	<script>
	  $(document).ready(function(){
	    $(".sticker").sticky({
	    	topSpacing: 0
	    });
	  });
	</script> 

	<script>

		$( "#input-segmento" ).change(function() {

			var slide = parseInt( $("#input-segmento option:selected").val() );
			$('.carousel').carousel(slide);

		});

		$(".carousel").on("touchstart", function(event){
		        var xClick = event.originalEvent.touches[0].pageX;
		    $(this).one("touchmove", function(event){
		        var xMove = event.originalEvent.touches[0].pageX;
		        if( Math.floor(xClick - xMove) > 5 ){
		            $(this).carousel('next');
		        }
		        else if( Math.floor(xClick - xMove) < -5 ){
		            $(this).carousel('prev');
		        }
		    });
		    $(".carousel").on("touchend", function(){
		            $(this).off("touchmove");
		    });
		});

		$('.navbar-collapse *').on('click', function(){
		    $('.lni-menu').trigger('click');
		});

	</script>

    </body>

</html>

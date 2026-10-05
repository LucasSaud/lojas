<?php
include('../../../_core/_includes/config.php');
restrict('1');
$subtitle = "Deletar usuário";
?>

<!-- Aditional Header's -->

<?php

	$id = (int) $_GET['id'];

	if( $id )  {
	
		if( delete_user( $id ) ) {

			header("Location: ../index.php?msg=sucesso");

		} else {

			header("Location: ../index.php?msg=erro");

		}

	}

?>




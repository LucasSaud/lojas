<?php
// CORE
include('../../../_core/_includes/config.php');
// RESTRICT
restrict_estabelecimento();
restrict_expirado();
// SEO
$seo_subtitle = "Importar Produtos";
$seo_description = "";
$seo_keywords = "";
// HEADER
$system_header .= "";
include('../../_layout/head.php');
include('../../_layout/top.php');
include('../../_layout/sidebars.php');
include('../../_layout/modal.php');
?>

<?php

global $db_con;
$eid = $_SESSION['estabelecimento']['id'];

// Variables

$estabelecimento = mysqli_real_escape_string( $db_con, $_GET['estabelecimento_id'] );
$categoria = mysqli_real_escape_string( $db_con, $_GET['categoria'] );
$nome = mysqli_real_escape_string( $db_con, $_GET['nome'] );
$visible = mysqli_real_escape_string( $db_con, $_GET['visible'] );
$status = mysqli_real_escape_string( $db_con, $_GET['status'] );

$getdata = "";

foreach($_GET as $query_string_variable => $value) {
  if( $query_string_variable != "pagina" ) {
    $getdata .= "&$query_string_variable=".htmlclean($value);
  }
}

// Config

$limite = 12;
$pagina = $_GET["pagina"] == "" ? 1 : $_GET["pagina"];
$inicio = ($pagina * $limite) - $limite;

// Query

$query .= "SELECT * FROM produtos ";

$query .= "WHERE 1=1 ";

$query .= "AND rel_estabelecimentos_id = '$eid' ";

if( $categoria ) {
  $query .= "AND rel_categorias_id = '$categoria' ";
}

if( $nome ) {
  $query .= "AND nome LIKE '%$nome%' ";
}

if( $visible ) {
  $query .= "AND visible = '$visible' ";
}

if( $status ) {
  $query .= "AND status = '$status' ";
}

$query_full = $query;

$query .= "ORDER BY last_modified DESC LIMIT $inicio,$limite";

// Run

$sql = mysqli_query( $db_con, $query );

$total_results = mysqli_num_rows( $sql );

$sql_full = mysqli_query( $db_con, $query_full );

$total_results_full = mysqli_num_rows( $sql_full );

$total_paginas = Ceil($total_results_full / $limite) + ($limite / $limite);

if( !$pagina OR $pagina > $total_paginas OR !is_numeric($pagina) ) {

    $pagina = 1;

}

?>

<?php if( $_GET['msg'] == "erro" ) { ?>

<?php modal_alerta("Erro, tente novamente!","erro"); ?>

<?php } ?>

<?php if( $_GET['msg'] == "sucesso" ) { ?>

<?php modal_alerta("Ação efetuada com sucesso!","sucesso"); ?>

<?php } ?>

<div class="middle minfit bg-gray">

	<div class="container">

		<div class="row">

			<div class="col-md-12">

				<div class="title-icon pull-left">
					<i class="lni lni-shopping-basket"></i>
					<span>Importar Produtos</span>
				</div>

				<div class="bread-box pull-right">
					<div class="bread">
						<a href="<?php panel_url(); ?>"><i class="lni lni-home"></i></a>
						<span>/</span>
						<a href="<?php panel_url(); ?>/produtos/importar">Importar Produtos</a>
					</div>
				</div>

			</div>

		</div>
		
<!-- IMPORTADOR CSV -->
<div class="container">
    <?php
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        //echo "Successfully Connected!";
    }
    $message = ""; // Variável para armazenar a mensagem de sucesso
    $datetime = date('Y-m-d H:i:s');

    if (isset($_POST["import"])) {
        $fileName = $_FILES["file"]["tmp_name"];

        if ($_FILES["file"]["size"] > 0) {
            $file = fopen($fileName, "r");
            $find_header = 0;

            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                $find_header++; // Atualiza o contador

                // Isso garante que pulamos o cabeçalho
                if ($find_header > 1) {
                    // Atribua os valores a variáveis separadas para maior clareza
                    $rel_estabelecimentos_id = $eid;
                    $rel_categorias_id = $column[1];
                    $destaque = $column[2];
                    $ref = $column[3];
                    $codigo_pdv = $column[4];
                    $pontos = $column[5];
                    $permitir_troca = $column[6];
                    $pontos_item = $column[7];
                    $nome = $column[8];
                    $descricao = $column[9];
                    $valor = $column[10];
                    $oferta = $column[11];
                    $valor_promocional = $column[12];
                    $variacao = $column[13];
                    $visible = $column[14];
                    $status = $column[15];
                    $created = $datetime;
                    $last_modified = $datetime;
                    $statusp = $column[18];
                    $integrado = $column[19];
                    $pesofrete = $column[20];
                    $alturafrete = $column[21];
                    $largurafrete = $column[22];
                    $comprimentofrete = $column[23];
                    $diametrofrete = $column[24];
                    $estoque = $column[25];
                    $posicao = $column[26];

                    // Escapa os valores para evitar injeção de SQL
                    $rel_estabelecimentos_id = mysqli_real_escape_string($conn, $rel_estabelecimentos_id);
                    $rel_categorias_id = mysqli_real_escape_string($conn, $rel_categorias_id);
                    $destaque = mysqli_real_escape_string($conn, $destaque);
                    $ref = mysqli_real_escape_string($conn, $ref);
                    $codigo_pdv = mysqli_real_escape_string($conn, $codigo_pdv);
                    $pontos = mysqli_real_escape_string($conn, $pontos);
                    $permitir_troca = mysqli_real_escape_string($conn, $permitir_troca);
                    $pontos_item = mysqli_real_escape_string($conn, $pontos_item);
                    $nome = mysqli_real_escape_string($conn, $nome);
                    $descricao = mysqli_real_escape_string($conn, $descricao);
                    $valor = mysqli_real_escape_string($conn, $valor);
                    $oferta = mysqli_real_escape_string($conn, $oferta);
                    $valor_promocional = mysqli_real_escape_string($conn, $valor_promocional);
                    $variacao = mysqli_real_escape_string($conn, $variacao);
                    $visible = mysqli_real_escape_string($conn, $visible);
                    $status = mysqli_real_escape_string($conn, $status);
                    $created = mysqli_real_escape_string($conn, $created);
                    $last_modified = mysqli_real_escape_string($conn, $last_modified);
                    $statusp = mysqli_real_escape_string($conn, $statusp);
                    $integrado = mysqli_real_escape_string($conn, $integrado);
                    $pesofrete = mysqli_real_escape_string($conn, $pesofrete);
                    $alturafrete = mysqli_real_escape_string($conn, $alturafrete);
                    $largurafrete = mysqli_real_escape_string($conn, $largurafrete);
                    $comprimentofrete = mysqli_real_escape_string($conn, $comprimentofrete);
                    $diametrofrete = mysqli_real_escape_string($conn, $diametrofrete);
                    $estoque = mysqli_real_escape_string($conn, $estoque);
                    $posicao = mysqli_real_escape_string($conn, $posicao);

                    // Monta a consulta SQL
                    $sqlInsert = "INSERT INTO produtos (rel_estabelecimentos_id, rel_categorias_id, destaque, ref, codigo_pdv, pontos, permitir_troca, pontos_item, nome, descricao, valor, oferta, valor_promocional, variacao, visible, status, created, last_modified, statusp, integrado, pesofrete, alturafrete, largurafrete, comprimentofrete, diametrofrete, estoque, posicao) 
                                  VALUES ('$rel_estabelecimentos_id', '$rel_categorias_id', '$destaque', '$ref', '$codigo_pdv', '$pontos', '$permitir_troca', '$pontos_item', '$nome', '$descricao', '$valor', '$oferta', '$valor_promocional', '$variacao', '$visible', '$status', '$created', '$last_modified', '$statusp', '$integrado', '$pesofrete', '$alturafrete', '$largurafrete', '$comprimentofrete', '$diametrofrete', '$estoque', '$posicao')";

                    $result = mysqli_query($conn, $sqlInsert);

                    if (!empty($result)) {
                        $type = "success";
                        $message = "CSV Foi Importado Com Sucesso";
                    } else {
                        $type = "error";
                        $message = "Houve um Problema ao Importar o CSV";
                    }
                }
            }
            fclose($file);
        }
    }

    $sqlSelect = "SELECT * FROM categorias WHERE rel_estabelecimentos_id = '$eid'";
    $result = mysqli_query($conn, $sqlSelect);

    if (mysqli_num_rows($result) > 0) {
    ?>
    <div class="table-responsive">
        <button id="toggleButton" onclick="toggleTable()" class="btn btn-primary">Mostrar/Esconder Tabela</button>
        <table id="userTable" class="table table-striped" style="display: none;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_array($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nome']; ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleTable() {
            var table = document.getElementById("userTable");
            var button = document.getElementById("toggleButton");

            if (table.style.display === "none") {
                table.style.display = "table";
                button.innerHTML = "Esconder Tabela";
            } else {
                table.style.display = "none";
                button.innerHTML = "Mostrar Tabela";
            }
        }
    </script>
<?php } ?>


<!-- Código HTML com a mensagem de sucesso -->
<div class="container">
    <div class="jumbotron" <?php echo ($type !== "success" || empty($message)) ? 'style="display: none;"' : ''; ?>>
        <h2><?php echo $message; ?></h2>
    </div>

    <form class="form-horizontal" action="" method="post" name="uploadCSV" enctype="multipart/form-data">
        <div class="form-group">

            <label class="col-md-4 control-label">Escolha o arquivo CSV a importar</label>

            <div class="col-md-4">
                <input type="file" name="file" id="file" accept=".csv" class="form-control">
            </div>

            <div class="col-md-4">
                <button type="submit" id="submit" name="import" class="btn btn-primary">Importar</button>
            </div>
        </div>

        <div class="form-group">

            <label class="col-md-12 control-label" style="text-align:center;">
                <a href="modelo_importacao.csv" download class="btn btn-primary">BAIXAR MODELO DA TABELA DE IMPORTAÇÃO</a>
            </label>

        </div>
        <div id="labelError"></div>
    </form>
</div>
    <!-- FIM IMPORTADOR CSV -->


<?php 
// FOOTER
$system_footer .= "";
include('../_layout/rdp.php');
include('../_layout/footer.php');
?>

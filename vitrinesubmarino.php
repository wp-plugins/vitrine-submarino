<?php
/*
Plugin Name: Vitrine Submarino
Plugin URI: http://www.bernabauer.com/wp-plugins/
Description: Inspirado em <a href='http://jobsonlemos.com/?p=64'>script de Jobson Lemos</a>. O plugin mostra uma quantidade de ofertas configuráveis ao gosto do freguês. Requer tema de wordpress compatível com widgets.
Version: 1.1
Author: Bernardo Bauer
Author URI: http://www.bernabauer.com/
*/
global $wpdb;
global $vs_options;
$vs_options = get_option('vs_options');

register_activation_hook("/wp-content/plugins/vitrinesubmarino.php", 'vs_activate');
register_deactivation_hook("/wp-content/plugins/vitrinesubmarino.php", 'vs_deactivate');

add_action('admin_notices', 'vs_alerta');

// Run widget code and init
add_action('widgets_init', 'vs_widget_init');

// Run plugin code and init
add_action('admin_menu', 'vs_option_menu');

// Vitrine Automática
if ($vs_options['ctx_exib_auto'] == 'auto') {
	add_filter('the_content', 'vs_auto');
} 


/***************************************************************************************************
 *  Coisas para serem feitas na instalacao do plugin
 */
function vs_activate() {

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	global $wpdb;

	$vs_options = array(
		'codafil'=>			'',
		'version'=>			'1.1',
		'host'=>			'',
		'wid_title'=>		'Ofertas Submarino',
		'wid_show'=>		'5',
		'wid_bgcolor'=>		'#FFFFFF',
		'wid_brdcolor'=>	'#DDDDDD',
		'wid_word'=>		'Celular',
		'wid_altcode'=>		'BVD',
		'wid_track'=>		'nao',
		'ctx_tipo'=>		'horizontal',
		'ctx_local'=>		'depois',
		'ctx_show'=>		'4',
		'ctx_exib_auto'=>	'auto',
		'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
		'ctx_track'=>		'nao'
	);

	add_option('vs_options', $vs_options);
}

/***************************************************************************************************
 *  Antes de desativar a funcao abaixo eh executada
 */
 function vs_deactivate() {

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	global $wpdb;
	
	delete_option('vs_options');
}

/***************************************************************************************************
 *  Alerta sobre problemas com a configuracao do plugin
 */
function vs_alerta() {

	global $vs_options;

	if (  !isset($_POST['info_update']) ) {
		if(!function_exists(curl_init)) {
			$msg = '* Seu host não permite a utilização do cURL.';
		}
		if ($vs_options == '') {
			$msg = '* Parece que você atualizou a versão nova sem desativar o plugin!! Por favor desative e re-ative.';
		} else {
	
			if ( $vs_options['codafil'] == '') {
				$msg = '* '.__('Você ainda não informou seu código de afiliados do Submarino!!!',$domain).'<br />'.sprintf(__('Se você já tem uma conta informe <a href="%1$s">aqui</a>, caso contrário <a href="%2$s">crie uma agora</a>.',$domain), "options-general.php?page=vitrinesubmarino.php","http://afiliados.submarino.com.br/affiliates/").'<br />'; 
			}
			$current_plugins = get_option('active_plugins');
			if (!in_array('palavrasmonetizacao.php', $current_plugins)) {
			$msg = '* Você não está apto para mostrar a Vitrine Contextual. Você precisa do plugin Palavras de Monetização.';
			}

		}
		
		if ($msg) {
			echo "<div class='updated fade-ff0000'><p><strong>".__('Vitrine Submarino Alerta!', $domain)."</strong><br /> ".$msg."</p></div>";
		}
		return;
	}
}

/***************************************************************************************************
 *  Formatação de texto para link
 */
function textoparalink ($texto)
{
	$texto = utf8_decode ( $texto );
	$texto = strtolower ( $texto );
	
	// Remove acentos sobre a string
	$texto = ereg_replace( "[¡¿¬√ƒ]", "A", $texto);
	$texto = ereg_replace( "[·‡‚„‰™]", "a", $texto);
	$texto = ereg_replace( "[…» À]", "E", $texto);
	$texto = ereg_replace( "[ÈËÍÎ]", "e", $texto);
	$texto = ereg_replace( "[ÕÃŒœ]", "I", $texto);
	$texto = ereg_replace( "[ÌÏÓÔ]", "i", $texto);
	$texto = ereg_replace( "[”“‘’÷]", "O", $texto);
	$texto = ereg_replace( "[ÛÚÙıˆ∫]", "o", $texto);
	$texto = ereg_replace( "[⁄Ÿ€‹]", "U", $texto);
	$texto = ereg_replace( "[˙˘˚¸]", "u", $texto);
	$texto = str_replace( "«", "C", $texto);
	$texto = str_replace( "Á", "c", $texto);
	
	// Remove acentos
	$texto = str_replace( "¥", "", $texto );
	$texto = str_replace( "`", "", $texto );
	$texto = str_replace( "~", "", $texto );
	$texto = str_replace( "^", "", $texto );
	$texto = str_replace( "®", "", $texto );
	
	$texto = ereg_replace( "[:,-/\|.;*]", "", $texto);
	$texto = str_replace( "       ", " ", $texto );
	$texto = str_replace( "      ", " ", $texto );
	$texto = str_replace( "     ", " ", $texto );
	$texto = str_replace( "    ", " ", $texto );
	$texto = str_replace( "   ", " ", $texto );
	$texto = str_replace( "  ", " ", $texto );

	$texto = trim ( $texto );
	$texto = str_replace (' ', '_', $texto);
	
	return $texto;
} // 

/***************************************************************************************************
 *  Vitrine automatica
 */
function vs_auto($text) {

	global $vs_options;
	
	if ((is_single()) AND ($vs_options["ctx_exib_auto"] == 'auto')) {

		$vitrine = vs_vitrine($vs_options["ctx_show"]);

		if ($vs_options["ctx_local"] == 'antes') {
		   $text = $vitrine.$text;
		} elseif ($vs_options["ctx_local"]=='depois') {
			$text .= $vitrine;
		}
	}
	
return $text;
}



/***************************************************************************************************
 *  Funcao principal
 */
function vs_vitrine ($show = 3, $widbg = "#FFFFFF", $word = "notebook", $widbrd = "#DDDDDD") {

	global $vs_options;

	$current_plugins = get_option('active_plugins');
	if (in_array('palavrasmonetizacao.php', $current_plugins)) {
		$words_array = pm_get_words();
		$word_pm = $words_array[0];
	}
	if ($word_pm)
		$word = $word_pm;

	$vs_options = get_option('vs_options');
	
	$vitrine_temp = vs_core($show, $widbg, $word, $widbrd, TRUE);

	if ($vs_options['ctx_exib_auto'] == 'auto') {
		$vitrine = $vs_options['ctx_titulo'].$vitrine_temp;
		return $vitrine;
	} else {
		echo $vitrine_temp;
		return '';
	}
}

/***************************************************************************************************
 *  Funcao principal
 */
function vs_core ($show = 2, $widbg = "#FFFFFF", $word = "celular" , $widbrd = "#DDDDDD", $contextual = FALSE) {
	global $wpdb;

	error_reporting( 0 );

	global $vs_options;
	
	if ($vs_options['codafil'] == '')
		return "ERRO: Código de Afiliado não informado.";

	$idsubmarino = $vs_options['codafil'];			// Define codigo de afiliado para o script funcionar
	$palavrapadrao =  str_replace( " ", "%20", $word );			// Define a palavra chave para o script funcionar

	$palavrabuscada = $_GET['pal'];   
	if ( !$palavrabuscada ) { $palavrabuscada = $palavrapadrao; }

	$desde = $_GET['pag'];
	if ( !$desde ) { $desde = 1; }

        $urlsub = 'http://www.submarino.com.br/HomeCache/AllSearchResult.aspx?PageHits=50&OrderBy=sortordersell&Query=';
        
		$urlaserlida = $urlsub.$palavrabuscada;

	// Pego a pagina do produto procurado

	if(function_exists(curl_init)) {
	
		$ch = curl_init();
		// informar URL e outras funcoes ao CURL
		curl_setopt($ch, CURLOPT_URL, $urlaserlida);

		if ($vs_options['host'] == 'godaddy') {
			// BEGIN GoDaddy Hack
			curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt($this->curl, CURLOPT_PROXY,"http://proxy.shr.secureserver.net:3128");
			// END GoDaddy Hack 
        }
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Acessar a URL e retornar a saida
		$buffer = curl_exec($ch);
		// liberar
		curl_close($ch);
	
	} else {
	
		$buffer = file_get_contents($urlaserlida);
	
	}

	$doc = new DOMDocument();

	$doc->loadHTML( $buffer );

	// Pego as imagens

	$img = $doc->getElementsByTagName( "img" );

	$i = 1;

	foreach( $img as $img )	{
		$teste = $img->getAttribute("class");

		if($teste == 'imgresult') { 
			$imagem[$i] = $img->getAttribute("src"); 
			$i++; 
		}
	}


	// Pego os links e os titulos

	$img = $doc->getElementsByTagName( "a" );

	$i = 1;

	foreach( $img as $img ) {
		$teste = $img->getAttribute("class");

		if($teste == 'link-prod') { 
			$link[$i] = $img->getAttribute("href").'&franq='.$idsubmarino; 
			$titulo[$i] = utf8_decode($img->nodeValue); 
			$i++; 
		}
	}


	// Pego os precos e as condicoes

	$img = $doc->getElementsByTagName( "li" );

	$i = 1;

	foreach( $img as $img ) {
		$teste = $img->getAttribute("class");

		if($teste == 'preco resultado-comprar') { 
			$preco[$i] = utf8_decode($img->nodeValue); $i++; 
		}
	}

	if (($contextual) AND  $vs_options['ctx_tipo'] == "horizontal") { 
		$lista_de_produtos = "<table id=\"vs_ctx_tabela_produtos\"><tr>";
	}


	for($a=1;$a<=$show;$a++) {
		if($imagem[$a]) { 

			$palavras = explode('_',textoparalink ($titulo[$a]));

			$tc = '';
		
			if ($contextual) {
				
				//mostra vitrine contextual
				if ($vs_options['ctx_track'] == "sim") {
					// Code for click tracking using Google Analytics.
					$tc = 'onClick="javascript: pageTracker._trackPageview (\'/out/sub/contextual/'.utf8_encode($titulo[$a]).'\');"';
				}
				if ($vs_options['ctx_tipo'] == "horizontal") {
					//mostra vitrine com produtos em uma unica linha
					$lista_de_produtos .= '<td style="text-align:center;font-size:11px;"><a href="'.$link[$a].'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$imagem[$a].'"></a><div style="">'.utf8_encode($titulo[$a]).'</div><div style="font-weight: bold;">'.utf8_encode($preco[$a]).'</div><br /><div style=""><a href="'.$link[$a].'" rel="nofollow" target="_blank"'.$tc.'>[ Comprar ]</a></div></td>';
				} else {
					//mostra vitrine com um produto por linha
					$lista_de_produtos .= '<div style="border:2px solid '.$widbrd.';height:104px;"><div style="background-color:'.$widbg.';padding:3px;"><table><tr><td><a href="'.$link[$a].'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$imagem[$a].'"></a></td><td>'.utf8_encode($titulo[$a]).'<br><b>'.utf8_encode($preco[$a]).'</b><br><br><a href="'.$link[$a].'" rel="nofollow" target="_blank"'.$tc.'>[ Comprar ]</a></td></tr></table></div></div>';
				}
			} else {
				
				//mostra vitrine widget
				if ($vs_options['wid_track'] == "sim") {
					// Code for click tracking using Google Analytics.
					$tc = 'onClick="javascript: pageTracker._trackPageview (\'/out/sub/widget/'.utf8_encode($titulo[$a]).'\');"';
				}
				$lista_de_produtos .= '<div style="background-color:'.$widbg.';padding:3px;"><center><p><a href="'.$link[$a].'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$imagem[$a].'"></a></p>'.utf8_encode($titulo[$a]).'<br><b>'.utf8_encode($preco[$a]).'</b><br><br><a href="'.$link[$a].'" rel="nofollow" target="_blank"'.$tc.'>[ Comprar ]</a> </center></div>';
			}
		 }

	}
	
	if (empty($lista_de_produtos)) {
		if ($contextual) {
			return vs_core ( "3", "#FFFFFF", "celular", "#DDDDDD", TRUE); 
		} else {

			switch ($vs_options['wid_altcode']) {
			case "BVD":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical&franq='.$vs_options['codafil'].'></script>';
				break;
			case "BVD":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical2&franq='.$vs_options['codafil'].'></script>';
				break;
			case "SKYD":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky&franq='.$vs_options['codafil'].'></script>';
				break;
			case "SKYG":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky2&franq='.$vs_options['codafil'].'></script>';
				break;
			case "BTD":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao&franq='.$vs_options['codafil'].'></script>';
				break;
			case "BTG":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao2&franq='.$vs_options['codafil'].'></script>';
				break;
			case "HBD":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&franq='.$vs_options['codafil'].'></script>';
				break;
			case "HBG":
				$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&franq='.$vs_options['codafil'].'></script>';
				break;
			}

		return $altcode;
		}
	} else {
	
	if (($contextual) AND  $vs_options['ctx_tipo'] == "horizontal") { 
			$lista_de_produtos .= "</tr></table>";
		}

	
#		return "<div style=\"width:100%;\"><div style=\"border:2px solid ".$widbrd.";\">".$lista_de_produtos."</div><div style=\"font-size:8px; text-align:right;\"><a href='http://www.bernabauer.com/wp-plugins/'>vitrine</a> by <a href='http://bernabauer.com'>bernabauer.com</a></div></div>";

		return "<div style=\"border:2px solid ".$widbrd.";\">".$lista_de_produtos."</div><div style=\"text-align:right;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>vitrine</a> by <a href='http://bernabauer.com'>bernabauer.com</a></small></div>";
	}
}


/***************************************************************************************************
 *  Menu de configuracao
 */
function vs_option_menu() {
    if ( function_exists('add_options_page') ) {
        add_options_page('Vitrine Submarino', 'Vitrine Submarino', 9, 'vitrinesubmarino.php', 'vs_options_subpanel');
	}
}




/***************************************************************************************************
 *  Pagina de opcoes
 */
function vs_options_subpanel() {

	global $wpdb;

	//pega dados da base
	global $vs_options;

	//processa novos dados para atualizacao
    if ( isset($_POST['info_update']) ) {

        if (isset($_POST['id'])) 
           $vs_options['codafil'] = $_POST['id'];
            
		// Remember to sanitize and format use input appropriately.
		if (isset($_POST['Submarino-title'])) 
			$vs_options['wid_title'] = strip_tags(stripslashes($_POST['Submarino-title']));
		if (isset($_POST['Submarino-show'])) 
			$vs_options['wid_show'] = strip_tags(stripslashes($_POST['Submarino-show']));
		if (isset($_POST['Submarino-bgcolor'])) 
			$vs_options['wid_bgcolor'] = strip_tags(stripslashes($_POST['Submarino-bgcolor']));
		if (isset($_POST['Submarino-word'])) 
			$vs_options['wid_word'] = strip_tags(stripslashes($_POST['Submarino-word']));

		$vs_options['wid_altcode'] = strip_tags(stripslashes($_POST['Submarino-altcode']));

		if (isset($_POST['ctx_titulo'])) 
			$vs_options['ctx_titulo'] = stripslashes($_POST['ctx_titulo']);
		if (isset($_POST['ctx_show'])) 
			$vs_options['ctx_show'] = strip_tags(stripslashes($_POST['ctx_show']));
		$vs_options['ctx_exib_auto'] = strip_tags(stripslashes($_POST['ctx_exib_auto']));
		$vs_options['ctx_local'] = strip_tags(stripslashes($_POST['ctx_local']));
		$vs_options['ctx_track'] = strip_tags(stripslashes($_POST['ctx_track']));
		$vs_options['wid_track'] = strip_tags(stripslashes($_POST['wid_track']));
		$vs_options['host'] = strip_tags(stripslashes($_POST['host']));
		$vs_options['ctx_tipo'] = strip_tags(stripslashes($_POST['ctx_tipo']));


		//atualiza base de dados com informacaoes do formulario		
		update_option('vs_options',$vs_options);
		
    }
    
    if ( $vs_options['ctx_exib_auto'] == 'auto') {
		$auto = 'checked=\"checked\"';
    } else {
    	$manual = 'checked=\"checked\"';
    }
    
    if ( $vs_options['ctx_local'] == 'antes') {
		$antes = 'checked=\"checked\"';
    } else {
    	$depois = 'checked=\"checked\"';
    }

    if ( $vs_options['ctx_track'] == 'sim') {
		$ctxsim = 'checked=\"checked\"';
    } else {
    	$ctxnao = 'checked=\"checked\"';
    }

    if ( $vs_options['wid_track'] == 'sim') {
		$sim = 'checked=\"checked\"';
    } else {
    	$nao = 'checked=\"checked\"';
    }

    if ( $vs_options['host'] == '') {
		$outro = 'checked=\"checked\"';
    } else {
    	$godaddy = 'checked=\"checked\"';
    }

    if ( $vs_options['ctx_tipo'] == 'horizontal') {
		$horizontal = 'checked=\"checked\"';
    } else {
    	$vertical = 'checked=\"checked\"';
    }
    
    
	switch ($vs_options['wid_altcode']) {
		case "BVD":
			$BVD = 'checked=\"checked\"';
			break;
		case "BVG":
			$BVG = 'checked=\"checked\"';
			break;
		case "SKYD":
			$SKYD = 'checked=\"checked\"';
			break;
		case "SKYG":
			$SKYG = 'checked=\"checked\"';
			break;
		case "BTD":
			$BTD = 'checked=\"checked\"';
			break;
		case "BTG":
			$BTG = 'checked=\"checked\"';
			break;
		case "HBD":
			$HBD = 'checked=\"checked\"';
			break;
		case "HBG":
			$HBG = 'checked=\"checked\"';
			break;
	}

?>
	<div class=wrap>

    <h2>Configurações</h2>
  <form method="post">

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Código de Afiliado</th>
		<td>
			 <input name="id" type="text" id="id" value="<?php echo $vs_options['codafil']; ?>" size=8  />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Hospedagem</th>
		<td>
			<input type="radio" name="host" value="" <?php echo $outro; ?>> Outro
			<br />
			<input type="radio" name="host" value="godaddy" <?php echo $godaddy; ?>> Godaddy
			<br />Este host precisa de uma configuração especial para usar a função cURL. Escolha esta opção se este for o seu host. Caso contrário escolha a opção "Outro".
		</td>
	 </tr>
	</table>

    <h3>Contextual</h3>
<?php

$current_plugins = get_option('active_plugins');
if (in_array('palavrasmonetizacao.php', $current_plugins)) {

?>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Título</th>
		<td>
				<input id="ctx_titulo" name="ctx_titulo" type="text" value="<?php echo $vs_options['ctx_titulo']; ?>" /><br />
				<label for="ctx_titulo">Este é o texto que será mostrado acima da vitrine. Você pode usar HTML.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Quant. Produtos</th>
		<td>
				<input style="width: 20px;" id="ctx_show" name="ctx_show" type="text" value="<?php echo $vs_options['ctx_show']; ?>" /><br />
				<label for="Submarino-show">Quantos produtos deverão ser motrados na vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Exibição da Vitrine</th>
		<td>
			<input type="radio" name="ctx_exib_auto" value="auto" <?php echo $auto; ?>> Automática
			<br />
			<input type="radio" name="ctx_exib_auto" value="manual" <?php echo $manual; ?>> Manual
			<br />
			Para mostrar 3 produtos, com cor de fundo branca, vitrine para "iPod" e borda cinza, por exemplo:<br />
			 &lt;?php if(function_exists('vs_vitrine')) {<br />
			 vs_vitrine (3, "#FFFFFF", "iPod", "#DDDDDD");<br />
			 } ?&gt;<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Tipo de Vitrine</th>
		<td>
			<input type="radio" name="ctx_tipo" value="horizontal" <?php echo $horizontal; ?>> Horizontal
			<br />
			<input type="radio" name="ctx_tipo" value="vertical" <?php echo $vertical; ?>> Vertical
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br />
			<input type="radio" name="ctx_track" value="sim" <?php echo $ctxsim; ?>> Sim
			<br />
			<input type="radio" name="ctx_track" value="nao" <?php echo $ctxnao; ?>> Não
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Localização da Vitrine</th>
		<td>
			Estas opções só funcionam caso a exibição da vitrine esteja configurada para automática.<br />
			<input type="radio" name="ctx_local" value="antes" <?php echo $antes; ?>> Antes do artigo
			<br />
			<input type="radio" name="ctx_local" value="depois" <?php echo $depois; ?>> Depois do artigo
			<br />
		</td>
	 </tr>
	</table>
	
<?php } else { ?>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Atenção</th>
		<td>
			O módulo contextual do Vitrine Submarino requer o plugin Palavras de Monetização.<br />
		</td>
	 </tr>
	</table>
	
<?php }  ?>

    <h3>Widget</h3>
    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Título</th>
		<td>
				<input id="Submarino-title" name="Submarino-title" type="text" value="<?php echo $vs_options['wid_title']; ?>" /><br />
				<label for="Submarino-title">Este é o texto que será mostrado acima da vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Quant. Produtos</th>
		<td>
				<input style="width: 20px;" id="Submarino-show" name="Submarino-show" type="text" value="<?php echo $vs_options['wid_show']; ?>" /><br />
				<label for="Submarino-show">Quantos produtos deverão ser motrados na vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor de fundo</th>
		<td>
  				<input style="width: 60px;" id="Submarino-bgcolor" name="Submarino-bgcolor" type="text" value="<?php echo $vs_options['wid_bgcolor']; ?>" /><br />
 				<label for="Submarino-bgcolor">Cor de fundo dos produtos. A melhor cor é branca.</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Produto</th>
		<td>
 				<input style="width: 30%;" id="Submarino-word" name="Submarino-word" type="text" value="<?php echo $vs_options['wid_word']; ?>" /><br />
 				<label for="Submarino-word">Informe a palavra para popular a vitrine. Evite utilização de acentos.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br />
			<input type="radio" name="wid_track" value="sim" <?php echo $sim; ?>> Sim
			<br />
			<input type="radio" name="wid_track" value="nao" <?php echo $nao; ?>> Não
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Anúncio alternativo</th>
		<td>
			<input type="radio" name="Submarino-altcode" value="BVD" <?php echo $BVD; ?>> Barra Vertical (150x350px) Campanha de Duráveis
			<br>
			<input type="radio" name="Submarino-altcode" value="BVG" <?php echo $BVG; ?>> Barra Vertical (150x350px) Campanha de Giro
			<br>
			<input type="radio" name="Submarino-altcode" value="SKYD" <?php echo $SKYD; ?>> Sky (120x600px) Campanha de Duráveis
			<br>
			<input type="radio" name="Submarino-altcode" value="SKYG" <?php echo $SKYG; ?>> Sky (120x600px) Campanha de Giro
			<br>
			<input type="radio" name="Submarino-altcode" value="BTD" <?php echo $BTD; ?>> Botão (125x125px) Campanha de Duráveis
			<br>
			<input type="radio" name="Submarino-altcode" value="BTG" <?php echo $BTG; ?>> Botão (125x125px) Campanha de Giro
			<br>
			<input type="radio" name="Submarino-altcode" value="HBD" <?php echo $HBD; ?>> HalfBanner (120x60px) Campanha de Duráveis
			<br>
			<input type="radio" name="Submarino-altcode" value="HBG" <?php echo $HBG; ?>> HalfBanner (120x60px) Campanha de Giro
			<br>
			<label for="Submarino-altcode">Código HTML para ser mostrado caso não sejam encontrados produtos com a palavra acima.</label>
		</td>
	 </tr>
	</table>
	<br />

<div class="submit">
  <input type="submit" name="info_update" value="Atualizar" />
</div>
</form> 

<?php
}

/***************************************************************************************************
 *  Configuracao do widget
 */

function vs_widget_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

		function widget_Submarino($args) {
		
			// "$args is an array of strings that help widgets to conform to
			// the active theme: before_widget, before_title, after_widget,
			// and after_title are the array keys." - These are set up by the theme
			extract($args);

			// These are our own options
			global $vs_options;
			$title = $vs_options['wid_title'];  // Title in sidebar for widget
			$show = $vs_options['wid_show'];  // # of Posts we are showing
			$bgcolor = $vs_options['wid_bgcolor'];  // Showing the width or not
			$word = $vs_options['wid_word'];  // Showing the width or not
					

		// Output
			echo $before_widget . $before_title . $title . $after_title;

			// start list
			echo '<ul>';
				// were there any posts found?
				$prod = vs_core ( $show, $bgcolor, $word) ;
				if (!empty($prod)) {
					echo $prod;
				}
				;
		// end list
		echo '</ul>';
		
		// echo widget closing tag
		echo $after_widget;
	}

	
	$widget_ops = array('classname' => 'widget_Submarino', 'description' => __( 'Vitrine de Produtos do Submarino.com' ) );
	wp_register_sidebar_widget('vitrine-submarino', 'Vitrine Submarino', 'widget_Submarino', $widget_ops);
}

?>
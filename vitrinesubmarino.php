<?php
/*
Plugin Name: Vitrine Submarino
Plugin URI: http://www.bernabauer.com/wp-plugins/
Description: Mostre vitrines de produtos do Submarino em seu blog. Com o <a href="http://wordpress.org/extend/plugins/palavras-de-monetizacao/">Palavras de Monetização</a> você pode contextualizar manualmente os produtos. Para usar widgets é neecessário um tema compatível.
Version: 3.3
Author: Bernardo Bauer
Author URI: http://www.bernabauer.com/

	Copyright 2008  Bernardo Bauer  (email : bernabauer@bernabauer.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


global $wpdb;
global $vs_options;
global $vs_version;

$vs_version = "3.3";
$vs_options = get_option('vs_options');

register_activation_hook(__FILE__, 'vs_activate');
register_deactivation_hook(__FILE__, 'vs_deactivate');

add_action('admin_notices', 'vs_alerta');

// Run widget code and init
add_action('widgets_init', 'vs_widget_init');

// Run plugin code and init
add_action('admin_menu', 'vs_option_menu');

// Vitrine Contextual Automática
if ($vs_options['ctx_exib_auto'] == 'auto') {
	add_filter('the_content', 'vs_auto',1);
}

add_action('vs_cron', 'vs_atualiza_produtos' );

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'vs_plugin_actions' );

/**************************************************************************************************
 *  Coisas para serem feitas na instalacao do plugin
 */
function vs_activate() {

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	global $wpdb;
	global $vs_version;

	$vs_options = get_option('vs_options');

	if ($vs_options == FALSE) {
		$vs_options = array(
			'codafil'=>			'',
			'cod_BP'=>			'',
			'cod_ML'=>			'',
			'cod_JC'=>			'',
			'LCP'=>				'[ Compare Preços ]',
			'PCP'=>				'BB',
			'LP'=>				'[ Veja mais ]',
			'LPT'=>				'submarino',
			'version'=>			$vs_version,
			'remover'=>			'nao',
			'wid_title'=>		'Ofertas Submarino',
			'wid_word'=>		'celular',
			'wid_show'=>		'3',
			'wid_fontcolor'=>	'#000000',
			'wid_bgcolor'=>		'#FFFFFF',
			'wid_brdcolor'=>	'#DDDDDD',
			'wid_prcolor'=>		'#3982C6',
			'wid_track'=>		'nao',
			'wid_altcode'=>		'BVD',
			'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
			'ctx_word'=>		'notebook',
			'ctx_show'=>		'4',
			'ctx_exib_auto'=>	'auto',
			'ctx_slot1'=>		'normal',
			'ctx_tipo'=>			'horizontal',
			'ctx_local'=>		'depois',
			'ctx_prcolor'=>		'#3982C6',
			'ctx_track'=>		'nao',
			'ctx_altcode'=>		'ctx_FBD',
		);
		add_option('vs_options', $vs_options);
		
			$sql = 'CREATE TABLE wp_vitrinesubmarino (
					nomep varchar(255) NOT NULL,
					linkp varchar(255) NOT NULL,
					imagemp varchar(255) NOT NULL,
					precop varchar(15) NOT NULL,
					rss_source varchar(255) NOT NULL
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
	
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		
	} else {
		if ($vs_options['version'] != $vs_version) {
			$vs_options = get_option('vs_options');
			$vs_options[	'version'] = $vs_version;
			$vs_options[	'wid_word'] = 'celular';
			$vs_options[	'ctx_word'] = 'notebook';
			$vs_options[	'ctx_slot1'] = 'normal';
			update_option('vs_options', $vs_options);
		}
	}

	if (!wp_next_scheduled('vs_cron')) {
		wp_schedule_event( time(), 'daily', 'vs_cron' );
	}
	vs_atualiza_produtos();
}

/***************************************************************************************************
 *  Antes de desativar a funcao abaixo eh executada
 */
 function vs_deactivate() {

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	global $wpdb;

	//pega dados da base
	global $vs_options;

	wp_clear_scheduled_hook('vs_cron');
	
	if ($vs_options['remover'] == 'sim')
		delete_option('vs_options');
}

/***************************************************************************************************
 *  Alerta sobre problemas com a configuracao do plugin
 */
function vs_alerta() {

	global $vs_options;
	global $vs_version;

	if (  !isset($_POST['info_update']) ) {
		if ($vs_options['version'] != $vs_version) {
			$msg = '* Você atualizou para a versão '.$vs_version.' sem desativar a versão anterior ('.$vs_options['version'].')!! Por favor desative e re-ative <a href="plugins.php">aqui</a>';
		} else {
	
			if ( $vs_options['codafil'] == '') {
				$msg = '* '.__('Você ainda não informou seu código de afiliados do Submarino!!!',$domain).'<br />'.sprintf(__('Se você já tem uma conta informe <a href="%1$s">aqui</a>, caso contrário <a href="%2$s">crie uma agora</a>.',$domain), "options-general.php?page=vitrinesubmarino.php","http://afiliados.submarino.com.br/affiliates/").'<br />'; 
			}
		}
		
		if ($msg) {
			echo "<div class='updated fade-ff0000'><p><strong>".__('Vitrine Submarino Alerta!', $domain)."</strong><br /> ".$msg."</p></div>";
		}
		return;
	}
}

############################################
# função criada por bruno alves
function vs_latin2html ($text) {

$p = array ('/Á/', '/á/', '/Â/', '/â/', '/À/', '/à/', '/Å/', '/å/', '/Ã/', '/ã/', '/Ä/', '/ä/', '/Æ/', '/æ/', '/É/', '/é/', '/Ê/', '/ê/', '/È/', '/è/', '/Ë/', '/ë/', '/Ð/', '/ð/', '/Í/', '/í/', '/Î/', '/î/', '/Ì/', '/ì/', '/Ï/', '/ï/', '/Ó/', '/ó/', '/Ô/', '/ô/', '/Ò/', '/ò/', '/Ø/', '/ø/', '/Õ/', '/õ/', '/Ö/', '/ö/', '/Ú/', '/ú/', '/Û/', '/û/', '/Ù/', '/ù/', '/Ü/', '/ü/', '/Ç/', '/ç/', '/Ñ/', '/ñ/', '/®/', '/©/', '/Ý/', '/ý/','/«/','/»/','/ª/','/º/');

$r = array   ('&Aacute;', '&aacute;', '&Acirc;', '&acirc;', '&Agrave;', '&agrave;', '&Aring;', '&aring;', '&Atilde;', '&atilde;', '&Auml;', '&auml;', '&AElig;', '&aelig;', '&Eacute;', '&eacute;', '&Ecirc;', '&ecirc;', '&Egrave;', '&egrave;', '&Euml;', '&euml;', '&ETH;', '&eth;', '&Iacute;', '&iacute;', '&Icirc;', '&icirc;', '&Igrave;', '&igrave;', '&Iuml;', '&iuml;', '&Oacute;', '&oacute;', '&Ocirc;', '&ocirc;', '&Ograve;', '&ograve;', '&Oslash;', '&oslash;', '&Otilde;', '&otilde;', '&Ouml;', '&ouml;', '&Uacute;', '&uacute;', '&Ucirc;', '&ucirc;', '&Ugrave;', '&ugrave;', '&Uuml;', '&uuml;', '&Ccedil;', '&ccedil;', '&Ntilde;', '&ntilde;', '&reg;', '&copy;', '&Yacute;', '&yacute;','&laquo;','&raquo;','&ordf;','&ordm;');

return preg_replace ($p, $r, $text); 

};


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

/**************************************************************************************************
 * Cria banners dos produtos @@@
 */
function vs_banner ($bannershow) {

	global $vs_options;

	$vs_options = get_option('vs_options');

	$banner = vs_core(1, 'celular', $bannershow, $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor'], $vs_options['ctx_prcolor']);

	echo $banner;
}



/***************************************************************************************************
 * Função para chamada manual da vitrine. Não permite abas.
 */
function vs_vitrine () {

	global $vs_options;

	$vs_options = get_option('vs_options');

	$current_plugins = get_option('active_plugins');
	if (in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) {
		$words_array = pm_get_words();
		
		if (count($words_array) == 0)
			$word = $vs_options['ctx_word'];
		else
			$word = $words_array[rand(0, count($words_array)-1)];
	} else {
		$word = $vs_options['ctx_word'];
	}

	$vitrine_temp = vs_core($vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor'], $vs_options['ctx_prcolor']);

	if ($vs_options['ctx_exib_auto'] == 'auto') {
		$vitrine = $vs_options['ctx_titulo'].$vitrine_temp;
		return $vitrine;
	} else {
	echo $vitrine_temp;
		return '';
	}
}

/***************************************************************************************************
 * Vitrine Automatica
 */
function vs_auto($text) {

	global $vs_options;

	$vs_options = get_option('vs_options');
//
	$current_plugins = get_option('active_plugins');
	if (in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) {
		$words_array = pm_get_words();
		
		if (count($words_array) == 0)
			$word = $vs_options['ctx_word'];
		else
			$word = $words_array[rand(0, count($words_array)-1)];
	} else {
		$word = $vs_options['ctx_word'];
	}
//
	if ((is_single()) AND ($vs_options["ctx_exib_auto"] == 'auto')) {

		$vitrine = vs_core ( $vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor'], $vs_options['ctx_prcolor']) ;

		if ($vs_options["ctx_local"] == 'antes') {
		   $text = $vitrine.$text;
		} elseif ($vs_options["ctx_local"]=='depois') {
			$text .= $vitrine;
		}

	}	

return $text;
	
}


/**************************************************************************************************
 *  pega produtos da base de dados
 */
function vs_pegaprodutos($palavra){ 

	global $wpdb;
	global $vs_options;

	$select = "SELECT * FROM wp_vitrinesubmarino WHERE rss_source = '". $palavra ."'";

	$results = $wpdb->get_results( $wpdb->prepare($select) , ARRAY_A);

	if ($results == "") {
		$results = vs_pesquisaprodutos($palavra);
	}

	echo "<!-- Produtos do Vitrine Submarino com a palavra: $palavra -->";

	return $results;
}

/*******************************************************************
* safe_mode and open_basedir workaround by http://www.edmondscommerce.co.uk/blog/curl/php-curl-curlopt_followlocation-and-open_basedir-or-safe-mode/
*/
//follow on location problems workaround
function curl_redir_exec($ch) {
	static $curl_loops = 0;
	static $curl_max_loops = 20;
	if ($curl_loops++>= $curl_max_loops) {
		$curl_loops = 0;
		return FALSE;
	}
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	list($header, $data) = explode("\n\n", $data, 2);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match('/Location:(.*?)\n/', $header, $matches);
		$url = @parse_url(trim(array_pop($matches)));
		if (!$url) {
			//couldn't process the url to redirect to
			$curl_loops = 0;
			return $data;
		}
		$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
		if (!$url['scheme'])
			$url['scheme'] = $last_url['scheme'];
		if (!$url['host'])
			$url['host'] = $last_url['host'];
		if (!$url['path'])
			$url['path'] = $last_url['path'];
		$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
		curl_setopt($ch, CURLOPT_URL, $new_url);
		return curl_redir_exec($ch);
	} else {
	$curl_loops=0;
	return $data;
	}
}
function curl($url){
	$go = curl_init($url);
	curl_setopt ($go, CURLOPT_URL, $url);
	//follow on location problems
	if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')){
		curl_setopt ($go, CURLOPT_FOLLOWLOCATION, $l);
		$syn = curl_exec($go);
	}else{
		$syn = curl_redir_exec($go);
	}
	curl_close($go);
	return $syn;
}

/**************************************************************************************************
 *  pega produtos da base de dados
 */
function vs_pesquisaprodutos($palavra){ 

	global $wpdb;
	global $vs_options;
	
	if ($palavra == '')
		$palavra = $vs_options['ctx_word'];
	$urlaserlida = "http://www.submarino.com.br/busca?q=".$palavra;

############## ACESSO A PÁGINA COM PRODUTOS

	// Pego a pagina do produto procurado
/*
	if(function_exists(curl_init)) {
		$ch = curl_init();
		// informar URL e outras funcoes ao CURL
		curl_setopt($ch, CURLOPT_URL, $urlaserlida);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// Acessar a URL e retornar a saida
		$buffer = curl_exec($ch);

		// liberar
		curl_close($ch);

	} else {
	
		$buffer = file_get_contents($urlaserlida);
	
	}
*/

	$buffer = curl($urlaserlida);
	$doc = new DOMDocument();

	$doc->loadHTML( str_replace("&", "&amp;", $buffer) );

############## COMEÇO DO CORE SCRIPT

		// Pego as imagens
	
		$img = $doc->getElementsByTagName( "img" );
	
		$i = 1;
	
		foreach( $img as $img )	{
			$teste = $img->getAttribute("class");
	
			if($teste == 'image') { 
				$imagem[$i] = "<img src=\"".$img->getAttribute("src")."\"  hspace=\"3\" border=\"0\">."; 
				$i++; 
			}
		}

		$totalprod = $i-1;
	
		// Pego os links e os titulos
	
		$img = $doc->getElementsByTagName( "span" );
		
		$i = 1;
		$j = 1;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");
	
			if($teste == 'name entry-title' OR $teste == 'name') { 
				$titulo[$i] = $img->nodeValue; 
				$i++; 
			}
			if($teste == 'for') { 
				$preco[$j] = ltrim($img->nodeValue,'por: '); 
				$j++; 
			}
	
		}

		// Pego os links e os titulos para categorias
	
		$img = $doc->getElementsByTagName( "a" );
	
		$i = 1;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");
	
			if($teste == 'link') { 
					$link[$i] = "http://www.submarino.com.br".$img->getAttribute("href").'?franq='.$vs_options['codafil']; 
					$i++; 
			}
		} // foreach

		$produtos = array();

		for($i=1;$i<=$totalprod;$i++) {
			if ($titulo[$i] != '' AND $link[$i] != '' AND $imagem[$i] != '' AND $preco[$i] != '') 
				$lprod .= "('" . $wpdb->escape($titulo[$i]) . "','" . $wpdb->escape($link[$i]) . "','" . $wpdb->escape($imagem[$i]) . "','" . $wpdb->escape($preco[$i]) . "', '". $wpdb->escape($palavra) ."'), ";
		} //for

	if ($lprod != '') {
		$insert = "INSERT INTO wp_vitrinesubmarino (nomep, linkp, imagemp, precop, rss_source) VALUES " . rtrim($lprod, ", ");

		$results = $wpdb->query( $wpdb->prepare($insert) );
	}

	$select = "SELECT * FROM wp_vitrinesubmarino WHERE rss_source = '". $palavra ."'";
	$produtos = $wpdb->get_results( $wpdb->prepare($select) , ARRAY_A);

	return $produtos;
}


/**************************************************************************************************
 *  atualiza o cache
 */
function vs_atualiza_produtos(){ 

	global $wpdb;
	global $vs_options;

	#cache vai pras cucuias
	$truncate = "TRUNCATE TABLE wp_vitrinesubmarino";
	$results = $wpdb->query( $wpdb->prepare($truncate) );

	#atualiza palavra padrão da vitrine contextual
	vs_pesquisaprodutos($vs_options['ctx_word']);
	vs_pesquisaprodutos($vs_options['wid_word']);

}

/**************************************************************************************************
 *  Funcao principal
 */
function vs_core ($show, $word, $vitrine, $fundo, $borda, $desc, $corprec) {
	
	global $wpdb;

	error_reporting( 0 );

	global $vs_options;
	global $vs_version;
	$i=1;
	$cod_BP = $vs_options['cod_BP'];
	$cod_ML = $vs_options['cod_ML'];
	$cod_JC = $vs_options['cod_JC'];
	
	if ($vs_options['codafil'] == '')
		return "ERRO: Código de Afiliado não informado.";

	if ($vs_options['version'] != $vs_version)
		return "Vitrine Submarino ** ERRO: Atualização necessária! **";

	$idsubmarino = $vs_options['codafil'];			// Define codigo de afiliado para o script funcionar

	//pega produtos da BD (devolve um array)

	$produtos = vs_pegaprodutos($word);

	shuffle($produtos);

	if (is_array($produtos)) {

		foreach ($produtos as $produto) {
	
			$nome = $produto['nomep'];
			$link_prod = $produto['linkp'];
			$imagem = $produto['imagemp'];
			$preco = $produto['precop'];
	
			$tc = '';
	
			//código de tracking do Google Analytics dos links de comparação de preços
			$tccp = 'onclick="javascript: pageTracker._trackPageview (\'/out/sub/compare/'.$vs_options['PCP'].'/'.$nome.'/\');"';
		
			//código de tracking do Google Analytics dos produtos da vitrine
			$tc = 'onclick="javascript: pageTracker._trackPageview (\'/out/sub/'.$vitrine.'/'.$nome.'\');"';
	
			if (($vs_options['ctx_track'] == "nao") AND $vitrine == "contextual") {
				$tc = '';
			}
			if (($vs_options['wid_track'] == "nao") AND $vitrine == "widget")  {
				$tc = '';
			}

			switch ($vs_options['PCP']) {
				case "BP":
					$compare_precos = "<a href=\"http://busca.buscape.com.br/cprocura?lkout=1&amp;site_origem=".$cod_BP."&produto=".urlencode(utf8_decode($word))."\" ".$tccp." target='_blank'>".$vs_options['LCP']."</a>"; 
					break;
				case "ML":
					$compare_precos = "<a href=\"http://pmstrk.mercadolivre.com.br/jm/PmsTrk?tool=".$cod_ML."&amp;go=http://lista.mercadolivre.com.br/".urlencode($word)."\"  ".$tccp." target='_blank'>".$vs_options['LCP']."</a>"; 
					break;
				case "JC":
					$compare_precos = "<a href=\"http://www.jacotei.com.br/mod.php?module=jacotei.pesquisa&amp;texto=".urlencode($word)."&amp;precomin=&amp;precomax=&amp;af=".$cod_JC."\" ".$tccp." target='_blank'>".$vs_options['LCP']."</a>";  
					break;
				case "NS":
					$compare_precos = ''; 
					break;
				case "BB":
					$compare_precos = "<a href=\"http://bernabauer.shopping.busca.uol.com.br/busca.html?q=".urlencode(utf8_decode($word))."\" "	.$tccp." target='_blank'>".$vs_options['LCP']."</a>"; 
					break;
			}
	
			switch ($vitrine) {
		
				case "contextual":
					// vitrine contextual
					if ($vs_options['ctx_tipo'] == "horizontal") {
						$td = 92 / $vs_options['ctx_show'];
						$imagem = str_replace("<img ", "<img width=90px height=90px alt=\"".$nome."\"", $imagem);
						$imagem = rtrim($imagem,".");

						//mostra vitrine com produtos em uma unica linha (VITRINE HORIZONTAL)
#						$lista_de_produtos .= "<div onMouseover=\"ddrivetip('".$nome."', '#EFEFEF')\";=\"\" onMouseout=\"hideddrivetip()\">";
						$lista_de_produtos[] = '<div style="width:'. $td.'%;background-color:white;text-align:center; line-height:120%;padding-right: 10px;font-size:12px;border:0px;float:left;overflow: hidden;"><a href="'.$link_prod.'" '.$tc.'  target="_blank"><span style="width:90px;height:90px;position:relative;">'.$imagem.'</span></a><br />'.$nome.'<br /><span style="color:'.$corprec.';">&nbsp;'.$preco.'&nbsp;</span><br /><a href="'.$link_prod.'" '.$tc.'  target="_blank"><strong>Veja mais</strong></a>'.$compare_precos.'</div>';
						
					} elseif ($vs_options['ctx_tipo'] == "vertical") {
						$imagem = str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 10px 0;\" alt=\"".$nome."\"", $imagem);
						//mostra vitrine com um produto por linha (VITRINE VERTICAL)
						$lista_de_produtos[] = '<div style="height:130px;background-color:white;padding:3px;"><a href="'.$link_prod.'" '.$tc.'  target="_blank">'.$imagem.'</a><a href="'.$link_prod.'" '.$tc.' target="_blank">'.$nome.'</a><br /><div style="color:'.$corprec.';">'.$preco.'</div>'.$compare_precos.'</div>';
												
					} elseif ($vs_options['ctx_tipo'] == "banner-468") {
						$credits='<div style="position:absolute;bottom:0px;right:0px;"><small><a href="http://www.bernabauer.com/wp-plugins/">Vitrine Submarino '.$vs_options['version'].'</a></small></div>';
						$imagem = rtrim(str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 0px 10px;height:60px;\" alt=\"".$nome."\"", $imagem), ".");
						$lista_de_produtos[] = '<div style="border: 1px solid #ccc;color:'.$desc.';background-color:white;padding:3px;text-decoration: none ! important;width:468px;height:60px;position:relative;"><a href="http://www.submarino.com.br?franq='.$vs_options['codafil'].'" target="_blank"><img style=" display: inline; float: right; margin: 0 10px 0px 10px;" src="http://i.s8.com.br/images/affiliates/selos/70x70_selo.gif" WIDTH=40 HEIGHT=40 ></a><a href="'.$link_prod.'" '.$tc.' target="_blank">'.$imagem.'<font size="+1">'.$nome.'</font></a><br /><div style="color:'.$corprec.';font-size: 100%;">'.$preco.'</div>'.$compare_precos.''.$credits.'</div>';
						$i = $show;
						
					} elseif ($vs_options['ctx_tipo'] == "box-250") {
						$imagem = rtrim(str_replace("<img ", "<img width=90px height=90px alt=\"".$nome."\"", $imagem),".");
#						$lista_de_produtos .= "<div onMouseover=\"ddrivetip('".$nome."', '#EFEFEF')\";=\"\" onMouseout=\"hideddrivetip()\">";
						$lista_de_produtos[] = "<div style=\"width:110px;height:125px;background-color:white;text-align:center; line-height:120%;padding-left: 10px;font-size:12px;border:0px;float:left;overflow: hidden;\"><a href=\"".$link_prod.'" '.$tc.'  target="_blank"><span style="width:90px;height:90px;position:relative;">'.$imagem.'<div style="color:'.$corprec.';font-size: 120%;background-color:white;position: absolute; bottom: 65px; right: 0px;">&nbsp;'.$preco.'&nbsp;</div></span></a><br />'.$nome.''.$compare_precos.'</div></div>';
					}
					break;
	
				case "widget":
#					$imagem = rtrim(str_replace("<img ", "<img name=image".$i." onload=\"resizeimage('image".$i."');\"", $imagem), ".");
					$lista_de_produtos[] = '<div style="color:'.$desc.';background-color:'.$fundo.';text-align:center;padding:3px;text-decoration: none ! important;"><a href="'.$link_prod.'" '.$tc.' target="_blank">'.$imagem.'<br />'.$nome.'</a><br /><div style="color:'.$corprec.';">'.$preco.'</div>'.$compare_precos.'</div>';
					break;
				case "banner-728-1":
					$credits='<div style="position:absolute;bottom:0px;right:0px;"><small><a href="http://www.bernabauer.com/wp-plugins/">Vitrine Submarino '.$vs_options['version'].'</a></small></div>';
					$imagem = rtrim(str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 0px 10px;height:90px;\" alt=\"".$nome."\"", $imagem), ".");
					$lista_de_produtos[] = '<div style="border: 1px solid #ccc;color:'.$desc.';background-color:white;padding:3px;text-decoration: none ! important;width:728px;height:90px;position:relative;"><a href="http://www.submarino.com.br?franq='.$vs_options['codafil'].'" target="_blank"><img style=" display: inline; float: right; margin: 0 10px 0px 10px;" src="http://i.s8.com.br/images/affiliates/selos/70x70_selo.gif"></a><a href="'.$link_prod.'" '.$tc.' target="_blank">'.$imagem.'<div style="font-size: 200%;">'.$nome.'</div></a><br /><div style="color:'.$corprec.';font-size: 150%;">'.$preco.'</div>'.$compare_precos.''.$credits.'</div>';
					$i = $show;
					break;
						
				case "banner-728-2":
					$imagem = str_replace("<img ", "<img style=\" display: inline; margin: 0 10px 0px 10px;height:90px;\" alt=\"".$nome."\"", $imagem);
					$lista_de_produtos[] = '<span style="position:relative;"><a href="'.$link_prod.'" '.$tc.' target="_blank" title="'.$nome.'">'.$imagem.'</a><div style="color:'.$corprec.';font-size: 100%;position: absolute; top: 0px; left: 10px;">'.$preco.'</div></span>';
					break;

			} //switch

#		if ($vs_options['ctx_slot1'] != "normal" AND $i == $show-1)
#			break;

		if ($i >= $show)
			break;
		else
			$i++;
		} //foreach	

		#mostra primeiro slot diferenciado (adsense ou link de comparação de preços)
		$current_plugins = get_option('active_plugins');
		if (in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) {
			$words_array = pm_get_words();
		
			if ($vs_options['ctx_rss_source'] == "ctx_contextual" AND $words_array != '' AND $vs_options['ctx_slot1'] != "normal" AND $vitrine == "contextual" AND $vs_options['ctx_tipo'] == "horizontal" || $vs_options['ctx_tipo'] == "box-250") {
			
				if (count($lista_de_produtos) >= $show)
					$lixo = array_pop($lista_de_produtos);
				
				foreach ($lista_de_produtos as $produto) {
					$lista_final_de_produtos .= $produto;
				}

				$current_plugins = get_option('active_plugins');
				if (in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) 
					$words_array = pm_get_words();
			
				shuffle($words_array);
			
				$compareprecoshead = '<strong>Compare Preços</strong><br />';
			
				switch($vs_options['ctx_slot1']) {
					case "adsense":
						$lista_final_de_produtos = '<div style="width:120px;height:125px;background-color:white;border:0px;float:left;"><div style="float:center; " align="center" >'.stripslashes($vs_options['adsense_code']).'</div></div>'.$lista_final_de_produtos; 
						break;
					case "compareBP":
						foreach ($words_array as $word) {
							$compare_precos = "<a href=\"http://busca.buscape.com.br/cprocura?lkout=1&amp;site_origem=".$cod_BP."&produto=".urlencode(utf8_decode($word))."\" ".$tccp." target='_blank'>".$word."</a><br />"; 
							$comparacao .= $compare_precos;
							if ($u == 3)
								break;
							else
								$u++;
						}
						$lista_final_de_produtos = '<div style="width:'. $td.'%;height:125px;background-color:white;border:0px;float:left;"><div style="float:center; " align="center" >'.$compareprecoshead.' '.rtrim($comparacao,"<br />").'</div></div>'.$lista_final_de_produtos; 
						break;
					case "compareML":
						foreach ($words_array as $word) {
							$compare_precos = "<a href=\"http://pmstrk.mercadolivre.com.br/jm/PmsTrk?tool=".$cod_ML."&amp;go=http://lista.mercadolivre.com.br/".urlencode($word)."\"  ".$tccp." target='_blank'>".$word."</a><br />"; 
							$comparacao .= $compare_precos;
							if ($u == 3)
								break;
							else
								$u++;
						}
						$lista_final_de_produtos = '<div style="width:'. $td.'%;height:125px;background-color:white;border:0px;float:left;"><div style="float:center; " align="center" >'.$compareprecoshead.' '.rtrim($comparacao,"<br />").'</div></div>'.$lista_final_de_produtos; 
						break;
					case "compareJC":
						foreach ($words_array as $word) {
							$compare_precos = "<a href=\"http://www.jacotei.com.br/mod.php?module=jacotei.pesquisa&amp;texto=".urlencode($word)."&amp;precomin=&amp;precomax=&amp;af=".$cod_JC."\" ".$tccp." target='_blank'>".$word."</a><br />";  
							$comparacao .= $compare_precos;
							if ($u == 3)
								break;
							else
								$u++;
						}
						$lista_final_de_produtos = '<div style="width:'. $td.'%;height:125px;background-color:white;border:0px;float:left;"><div style="float:center; " align="center" >'.$compareprecoshead.' '.rtrim($comparacao,"<br />").'</div></div>'.$lista_final_de_produtos; 
						break;
					case "compareBB":
						foreach ($words_array as $word) {
							$compare_precos = "<a href=\"http://bernabauer.shopping.busca.uol.com.br/busca.html?q=".urlencode(utf8_decode($word))."\" "	.$tccp." target='_blank'>".$word."</a><br />"; 
							$comparacao .= $compare_precos;
							if ($u == 3)
								break;
							else
								$u++;
						}
						$lista_final_de_produtos = '<div style="width:'. $td.'%;height:125px;background-color:white;border:0px;float:left;"><div style="float:center; " align="center" >'.$compareprecoshead.' '.rtrim($comparacao,"<br />").'</div></div>'.$lista_final_de_produtos; 
						break;
			
				} //switch
					$show = $show-1;
			} else { //if
				foreach ($lista_de_produtos as $produto) {
					$lista_final_de_produtos .= $produto;
				}
			}
		} else { //if	
			foreach ($lista_de_produtos as $produto) {
				$lista_final_de_produtos .= $produto;
			}
		}

	$lista_de_produtos = $lista_final_de_produtos;	

	} else {
		if ($vitrine == "contextual") { 
			// anuncio alternativo contextual

			switch ($vs_options['ctx_altcode']) {
				case "ctx_FBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_FBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_BVD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_BVG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SKYD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SKYG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_BTD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_BTG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_HBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_HBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&amp;franq='.$vs_options['codafil'].'></script>';
					break;
			} // switch
		} else {
			// anuncio alternativo widget
			switch ($vs_options['wid_altcode']) {
				case "FBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "FBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "SBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "SBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "BVD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "BVG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "SKYD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "SKYG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "BTD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "BTG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao2&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "HBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&amp;franq='.$vs_options['codafil'].'></script>';
					break;
				case "HBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&amp;franq='.$vs_options['codafil'].'></script>';
					break;
			} //switch
		} //if
		return "<br /><div style=\"float:center;\" align=\"center\" border=\"0\">
".$altcode."</div>";
	}# else {
	
	$credits = "<br /><div style=\"text-align:right;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>Vitrine Submarino ".$vs_options['version']."</a></small></div>";

	if (($vitrine == "contextual") AND ($vs_options['ctx_exib_auto'] == 'auto'))
		$titulo = $vs_options['ctx_titulo'];
	else
		$titulo = '';

	if (strstr($vs_options['ctx_tipo'], "banner") != FALSE OR strstr($vitrine, "banner") != FALSE) {
		$credits = "<br />";
	}

	if (($vitrine == "contextual") AND ($vs_options['ctx_tipo'] == "box-250")) {
		$box_antes = '<div style="width:250px;">';
		$box_depois = "</div>";
	}

	

	if ($vitrine == "widget") {
		$widgetid = "id=\"vswi\""; 
		$combordas = "border:2px solid ".$borda.";background-color:".$fundo.";";
	} else
		$combordas = '';

	return "<br />".$box_antes.''.$titulo."<div ".$widgetid." style=\"".$combordas."\">".$lista_de_produtos."</div>".$credits.''.$box_depois;
	#}
}


/**************************************************************************************************
 *  Menu de configuracao
 */
function vs_option_menu() {
    if ( function_exists('add_options_page') ) {
        add_options_page('Vitrine Submarino', 'Vitrine Submarino', 9, basename(__FILE__), 'vs_options_subpanel');
	}
}

/**************************************************************************************************
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
        if (isset($_POST['cod_BP'])) 
           $vs_options['cod_BP'] = $_POST['cod_BP'];
        if (isset($_POST['cod_ML'])) 
           $vs_options['cod_ML'] = $_POST['cod_ML'];
        if (isset($_POST['cod_JC'])) 
           $vs_options['cod_JC'] = $_POST['cod_JC'];
        if (isset($_POST['LP'])) 
           $vs_options['LP'] = $_POST['LP'];
        if (isset($_POST['LCP'])) 
           $vs_options['LCP'] = $_POST['LCP'];

		$vs_options['PCP'] = strip_tags(stripslashes($_POST['PCP']));
		$vs_options['LPT'] = strip_tags(stripslashes($_POST['LPT']));
		$vs_options['remover'] = strip_tags(stripslashes($_POST['remover']));
            
		// Opções WIDGET
		$vs_options['wid_orderby'] = strip_tags(stripslashes($_POST['wid_orderby']));
		if (isset($_POST['wid_title'])) 
			$vs_options['wid_title'] = strip_tags(stripslashes($_POST['wid_title']));

		if (isset($_POST['wid_show'])) 
			$vs_options['wid_show'] = strip_tags(stripslashes($_POST['wid_show']));

		if (isset($_POST['wid_fontcolor'])) 
			$vs_options['wid_fontcolor'] = strip_tags(stripslashes($_POST['wid_fontcolor']));

		if (isset($_POST['wid_bgcolor'])) 
			$vs_options['wid_bgcolor'] = strip_tags(stripslashes($_POST['wid_bgcolor']));

		if (isset($_POST['wid_brdcolor'])) 
			$vs_options['wid_brdcolor'] = strip_tags(stripslashes($_POST['wid_brdcolor']));

		if (isset($_POST['wid_prcolor'])) 
			$vs_options['wid_prcolor'] = strip_tags(stripslashes($_POST['wid_prcolor']));

		$vs_options['wid_word'] = strip_tags(stripslashes($_POST['wid_word']));
		$vs_options['wid_altcode'] = strip_tags(stripslashes($_POST['wid_altcode']));
		$vs_options['wid_track'] = strip_tags(stripslashes($_POST['wid_track']));

		// Opções CONTEXTUAL
		if (isset($_POST['ctx_prcolor'])) 
			$vs_options['ctx_prcolor'] = strip_tags(stripslashes($_POST['ctx_prcolor']));
		
		if (isset($_POST['ctx_titulo'])) 
			$vs_options['ctx_titulo'] = stripslashes($_POST['ctx_titulo']);

		if (isset($_POST['ctx_show'])) 
			$vs_options['ctx_show'] = strip_tags(stripslashes($_POST['ctx_show']));


		$vs_options['ctx_word'] = strip_tags(stripslashes($_POST['ctx_word']));
		$vs_options['ctx_altcode'] = strip_tags(stripslashes($_POST['ctx_altcode']));
		$vs_options['ctx_exib_auto'] = strip_tags(stripslashes($_POST['ctx_exib_auto']));
		$vs_options['ctx_slot1'] = strip_tags(stripslashes($_POST['ctx_slot1']));
		$vs_options['ctx_local'] = strip_tags(stripslashes($_POST['ctx_local']));
		$vs_options['ctx_track'] = strip_tags(stripslashes($_POST['ctx_track']));
		$vs_options['ctx_tipo'] = strip_tags(stripslashes($_POST['ctx_tipo']));
		$vs_options['ctx_style'] = strip_tags(stripslashes($_POST['ctx_style']));
		$vs_options['ctx_alt'] = strip_tags(stripslashes($_POST['ctx_alt']));
		$vs_options['adsense_code'] = $_POST['adsense_code'];

		//atualiza base de dados com informacaoes do formulario		
		update_option('vs_options',$vs_options);

		//atualiza o cache local de produtos com a nova configuracao
		vs_atualiza_produtos();
#		vs_pesquisaprodutos($vs_options['ctx_word']);
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
		$ctxtrksim = 'checked=\"checked\"';
    } else {
    	$ctxtrknao = 'checked=\"checked\"';
    }

    if ( $vs_options['wid_track'] == 'sim') {
		$widtrksim = 'checked=\"checked\"';
    } else {
    	$widtrknao = 'checked=\"checked\"';
    }

    switch ( $vs_options['ctx_tipo'] ) {
		case 'horizontal':
			$horizontal = 'checked=\"checked\"';
			break;
		case 'vertical':
			$vertical = 'checked=\"checked\"';
			break;
		case 'banner-468':
			$banner468 = 'checked=\"checked\"';
			break;
		case 'banner-728-1':
			$banner7281 = 'checked=\"checked\"';
			break;
		case 'box-250':
			$banner250 = 'checked=\"checked\"';
			break;
    }

    if ( $vs_options['remover'] == 'nao') {
		$remover_nao = 'checked=\"checked\"';
    } else {
    	$remover_sim = 'checked=\"checked\"';
    }

	switch ($vs_options['ctx_slot1']) {
		case "adsense":
			$slot1_adsense = 'checked=\"checked\"';
			break;
		case "compareBP":
			$slot1_compareBP = 'checked=\"checked\"';
			break;
		case "compareML":
			$slot1_compareML = 'checked=\"checked\"';
			break;
		case "compareJC":
			$slot1_compareJC = 'checked=\"checked\"';
			break;
		case "compareBB":
			$slot1_compareBB = 'checked=\"checked\"';
			break;
		case "normal":
			$slot1_normal = 'checked=\"checked\"';
			break;
	}
    
	switch ($vs_options['wid_altcode']) {
		case "FBD":
			$FBD = 'checked=\"checked\"';
			break;
		case "FBG":
			$FBG = 'checked=\"checked\"';
			break;
		case "SBD":
			$SBD = 'checked=\"checked\"';
			break;
		case "SBG":
			$SBG = 'checked=\"checked\"';
			break;
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
	
	switch ($vs_options['ctx_altcode']) {
		case "ctx_FBD":
			$ctx_FBD = 'checked=\"checked\"';
			break;
		case "ctx_FBG":
			$ctx_FBG = 'checked=\"checked\"';
			break;
		case "ctx_SBD":
			$ctx_SBD = 'checked=\"checked\"';
			break;
		case "ctx_SBG":
			$ctx_SBG = 'checked=\"checked\"';
			break;
		case "ctx_BVD":
			$ctx_BVD = 'checked=\"checked\"';
			break;
		case "ctx_BVG":
			$ctx_BVG = 'checked=\"checked\"';
			break;
		case "ctx_SKYD":
			$ctx_SKYD = 'checked=\"checked\"';
			break;
		case "ctx_SKYG":
			$ctx_SKYG = 'checked=\"checked\"';
			break;
		case "ctx_BTD":
			$ctx_BTD = 'checked=\"checked\"';
			break;
		case "ctx_BTG":
			$ctx_BTG = 'checked=\"checked\"';
			break;
		case "ctx_HBD":
			$ctx_HBD = 'checked=\"checked\"';
			break;
		case "ctx_HBG":
			$ctx_HBG = 'checked=\"checked\"';
			break;
	}
	
	switch ($vs_options['PCP']) {
		case "BP":
			$PCP_BP = 'checked=\"checked\"';
			break;
		case "ML":
			$PCP_ML = 'checked=\"checked\"';
			break;
		case "JC":
			$PCP_JC = 'checked=\"checked\"';
			break;
		case "NS":
			$PCP_NS = 'checked=\"checked\"';
			break;
		case "BB":
			$PCP_BB = 'checked=\"checked\"';
			break;
	}		


?>

	<div class=wrap>

    <h2>Configurações</h2>
  <form method="post">

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Estatística</th>
		<td>
			Produtos : 
			<?php 
			$sql = "SELECT count( linkp ) as count FROM wp_vitrinesubmarino";
			$results = $wpdb->get_results( $wpdb->prepare($sql) , ARRAY_A);
			echo $results['0']['count'];
			 ?>
			<br />
			Palavras distintas de produtos : 
			<?php 
			$sql = "SELECT count(distinct rss_source ) as count FROM wp_vitrinesubmarino";
			$results = $wpdb->get_results( $wpdb->prepare($sql) , ARRAY_A);
			echo $results['0']['count'];
			
			?>
			<br />Próxima limpeza do cache de produtos : 
			 <?php 
			 
				//GET Difference between Server TZ and desired TZ
				$sec_diff = date('Z') - (get_option('gmt_offset') * 3600);
				$sec_diff = (($sec_diff <= 0) ? '+' : '-') . abs($sec_diff);			
									
				echo date('d/m/Y H:i:s', wp_next_scheduled('vs_cron') + $sec_diff); 

			 ?>
		</td>
	 </tr>
	</table>
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
		<th scope="row" valign="top">Comparação de Preços<br /></th>
		<td>
			 <input name="cod_BP" type="text" id="cod_BP" value="<?php echo $vs_options['cod_BP']; ?>" size=8  /> Informe o código do BuscaPé
			 <br />
			 <input name="cod_ML" type="text" id="cod_ML" value="<?php echo $vs_options['cod_ML']; ?>" size=8  /> Informe o código de ferramenta do Mercado Livre
			 <br />
			 <input name="cod_JC" type="text" id="cod_JC" value="<?php echo $vs_options['cod_JC']; ?>" size=8  /> Informe o código do Jacotei
			 <br />
			 <input name="LCP" type="text" id="LCP" value="<?php echo $vs_options['LCP']; ?>" size=15  /> Informe o que você quer mostrar como nome do link para a página de comparação de preços. 
			<br />
			<br />
			Escolha abaixo qual programa será mostrado: <br />
			<input type="radio" id="PCP_BP" name="PCP" value="BP" <?php echo $PCP_BP; ?> /><label for="PCP_BP"> BuscaPé</label>
			<br />
			<input type="radio" id="PCP_ML" name="PCP" value="ML" <?php echo $PCP_ML; ?> /> <label for="PCP_ML">Mercado Livre</label>
			<br />
			<input type="radio" id="PCP_JC" name="PCP" value="JC" <?php echo $PCP_JC; ?> /> <label for="PCP_JC">Jacotei</label>
			<br />
			<input type="radio" id="PCP_BB" name="PCP" value="BB" <?php echo $PCP_BB; ?> /> <label for="PCP_BB">Shopping bernabauer.com</label>
			<br />
			<input type="radio" id="PCP_NS" name="PCP" value="NS" <?php echo $PCP_NS; ?> /> <label for="PCP_NS">Não mostrar links para comparação de preços</label>
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Remover opções ao desativar</th>
		<td>
			<input type="radio" id="RemoN" name="remover" value="nao" <?php echo $remover_nao; ?> /> <label for="RemoN">Não</label>
			<br />
			<input type="radio" id="RemoS" name="remover" value="sim" <?php echo $remover_sim; ?> /> <label for="RemoS">Sim</label>
			<br />
		</td>
	 </tr>
	</table>

<br />
    <h2>Vitrine Contextual</h2>
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
		<th scope="row" valign="top">Palavra de pesquisa da vitrine</th>
		<td>
			<input style="width: 60px;" id="ctx_word" name="ctx_word" type="text" value="<?php echo $vs_options['ctx_word']; ?>" <?php echo " ".$PM_Present; ?> /><label for="ctx_word"> Palavra padrão</label><br />
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Quant. Produtos</th>
		<td>
				<input style="width: 20px;" id="ctx_show" name="ctx_show" type="text" value="<?php echo $vs_options['ctx_show']; ?>" /><br />
				Quantos produtos deverão ser motrados na vitrine.<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Exibição da Vitrine</th>
		<td>
			<input type="radio" id="vitauto" name="ctx_exib_auto" value="auto" <?php echo $auto; ?> /> <label for="vitauto">Automática</label>
			<br />
			<input type="radio" id="vitman" name="ctx_exib_auto" value="manual" <?php echo $manual; ?> /> <label for="vitman">Manual</label>
			<br />
			Para mostrar a vitrine manualmente basta usar o código abaixo. As configurações são feitas nesta página.<br />
			 <code>&lt;?php if(function_exists('vs_vitrine')) { vs_vitrine (); } ?&gt;</code><br />
			 O código deve ser inserido dentro do <a href="http://codex.wordpress.org/The_Loop" target="_blank">Loop</a>.<br /><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Configuração da Vitrine</th>
		<td VALIGN="TOP">
			Primeiro slot de produto deve ser diferenciado com <br />
<!--
			<input type="radio" id="vitslot1a" name="ctx_slot1" value="adsense" <?php echo $slot1_adsense; ?> /> <label for="vitslot1a">Adsense</label>
-->			
			<br />
			<input type="radio" id="vitslot1cBP" name="ctx_slot1" value="compareBP" <?php echo $slot1_compareBP; ?> /> <label for="vitslot1cBP">Comparação de Preços BuscaPé</label>
			<br />
			<input type="radio" id="vitslot1cML" name="ctx_slot1" value="compareML" <?php echo $slot1_compareML; ?> /> <label for="vitslot1cML">Comparação de Preços Mercado Livre</label>
			<br />
			<input type="radio" id="vitslot1cJC" name="ctx_slot1" value="compareJC" <?php echo $slot1_compareJC; ?> /> <label for="vitslot1cJC">Comparação de Preços Jacotei</label>
			<br />
			<input type="radio" id="vitslot1cBB" name="ctx_slot1" value="compareBB" <?php echo $slot1_compareBB; ?> /> <label for="vitslot1cBB">Comparação de Preços Shopping bernabauer.com</label>
			<br />
			<input type="radio" id="vitslot1n" name="ctx_slot1" value="normal" <?php echo $slot1_normal; ?> /> <label for="vitslot1n">Nada</label>
			<br />
		</td>
<!--
		<td VALIGN="TOP">
			<label for="adsense">Código Adsense</label><br /><textarea name="adsense_code" rows="5" cols="40" id="adsense"><?php echo stripslashes($vs_options['adsense_code']); ?></textarea>
		</td>
-->
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Tipo de Vitrine</th>
		<td>
			<input type="radio" id="vithor" name="ctx_tipo" value="horizontal" <?php echo $horizontal; ?> /> <label for="vithor">Horizontal (produtos em uma única linha)</label>
			<br />
			<input type="radio" id="vitver" name="ctx_tipo" value="vertical" <?php echo $vertical; ?> /> <label for="vitver">Vertical (um produto por linha)</label>
			<br />
<!--
			<input type="radio" id="box-250" name="ctx_tipo" value="box-250" <?php echo $banner250; ?> /> <label for="box-250">Box 250x250 (até 4 produtos em 2 linhas)</label>
			<br />
			<input type="radio" id="banner-468" name="ctx_tipo" value="banner-468" <?php echo $banner468; ?> /> <label for="banner-468">Banner 468x60</label>
			<br />
			<input type="radio" id="banner-728-1" name="ctx_tipo" value="banner-728-1" <?php echo $banner7281; ?> /> <label for="banner-728-1">Banner 728x90</label>
			<br />
-->
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Localização da Vitrine</th>
		<td>
			Estas opções só funcionam caso a exibição da vitrine esteja configurada para automática.<br />
			<input type="radio" name="ctx_local" id="vant" value="antes" <?php echo $antes; ?> /> <label for="vant">Antes do artigo</label>
			<br />
			<input type="radio" name="ctx_local" id="vdep" value="depois" <?php echo $depois; ?> /> <label for="vdep">Depois do artigo</label>
			<br />
		</td>
	 </tr>
	</table>
	
    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor para preço</th>
		<td>
  				<input style="width: 60px;" id="ctx_prcolor" name="ctx_prcolor" type="text" value="<?php echo $vs_options['ctx_prcolor']; ?>" /><label for="ctx_prcolor"> Cor do preço dos produtos.</label><br />
  				Você pode digitar "red", "blue", "green" de acordo com a correspondencia de cores de HTML. Lista completa <a href="http://www.w3schools.com/Html/html_colornames.asp" target="_blank">aqui</a>.
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br /> Para ver a estatística de cliques você precisa acessar sua conta no Google Analytics, acionar o relatório "Conteúdo Principal" e incluir o filtro abaixo da tabela "Desempenho de conteúdo". Em "Localizar Página" escolha a opção  "contendo" e escreva "/sub/" na caixa de texto (sem as aspas, é claro!). Pressione "Ir".<br />
			<input type="radio" name="ctx_track" id="ctrackS" value="sim" <?php echo $ctxtrksim; ?> /> <label for="ctrackS">Sim</label>
			<br />
			<input type="radio" name="ctx_track" id="ctrackN" value="nao" <?php echo $ctxtrknao; ?> /> <label for="ctrackN">Não</label>
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Anúncio alternativo</th>
		<td>
			<input type="radio" name="ctx_altcode" id="cfbd" value="ctx_FBD" <?php echo $ctx_FBD; ?> /> <label for="cfbd">Fullbanner (468x60px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cfbg" value="ctx_FBG" <?php echo $ctx_FBG; ?> /> <label for="cfbg">Fullbanner (468x60px) Campanha de Giro</label>
			<br />
			<input type="radio" name="ctx_altcode" id="csbd" value="ctx_SBD" <?php echo $ctx_SBD; ?> /> <label for="csbd">Superbanner (728x90px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="ctx_altcode" id="csbg" value="ctx_SBG" <?php echo $ctx_SBG; ?> /> <label for="csbg">Superbanner (728x90px) Campanha de Giro</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cbvd" value="ctx_BVD" <?php echo $ctx_BVD; ?> /> <label for="cbvd">Barra Vertical (150x350px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cbvg" value="ctx_BVG" <?php echo $ctx_BVG; ?> /> <label for="cbvg">Barra Vertical (150x350px) Campanha de Giro</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cskyd" value="ctx_SKYD" <?php echo $ctx_SKYD; ?> /> <label for="cskyd">Sky (120x600px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cskyg" value="ctx_SKYG" <?php echo $ctx_SKYG; ?> /> <label for="cskyg">Sky (120x600px) Campanha de Giro</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cbtd" value="ctx_BTD" <?php echo $ctx_BTD; ?> /> <label for="cbtd">Botão (125x125px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="ctx_altcode" id="cbtg" value="ctx_BTG" <?php echo $ctx_BTG; ?> /> <label for="cbtg">Botão (125x125px) Campanha de Giro</label>
			<br />
			<input type="radio" name="ctx_altcode" id="chbd" value="ctx_HBD" <?php echo $ctx_HBD; ?> /> <label for="chbd">HalfBanner (120x60px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="ctx_altcode" id="chbg" value="ctx_HBG" <?php echo $ctx_HBG; ?> /> <label for="chbg">HalfBanner (120x60px) Campanha de Giro</label>
			<br />
		</td>
	 </tr>
	</table>



<br />


    <h2>Widget</h2>
    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Título</th>
		<td>
				<input id="wid_title" name="wid_title" type="text" value="<?php echo $vs_options['wid_title']; ?>" /><br />
				<label for="wid_title">Este é o texto que será mostrado acima da vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Palavra de pesquisa da vitrine</th>
		<td>
			<input style="width: 60px;" id="wid_word" name="wid_word" type="text" value="<?php echo $vs_options['wid_word']; ?>" /><label for="wid_word"> Palavra padrão</label><br />
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Quant. Produtos</th>
		<td>
				<input style="width: 20px;" id="wid_show" name="wid_show" type="text" value="<?php echo $vs_options['wid_show']; ?>" /><br />
				<label for="wid_show">Quantos produtos deverão ser motrados na vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cores</th>
		<td>
  				Você pode digitar "red", "blue", "green" de acordo com a correspondencia de cores de HTML. Lista completa <a href="http://www.w3schools.com/Html/html_colornames.asp" target="_blank">aqui</a>.<br /><br />
  				<table><td style="border-width:2px;"><strong>Texto:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_fontcolor" name="wid_fontcolor" type="text" value="<?php echo $vs_options['wid_fontcolor']; ?>" /><label for="wid_fontcolor"> Cor do texto de descrição dos produtos. A melhor cor é preta (#000000 ou BLACK). </label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Fundo:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_bgcolor" name="wid_bgcolor" type="text" value="<?php echo $vs_options['wid_bgcolor']; ?>" /><label for="wid_bgcolor"> Cor de fundo dos produtos. A melhor cor é branca (#FFFFFF).</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Borda:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_brdcolor" name="wid_brdcolor" type="text" value="<?php echo $vs_options['wid_brdcolor']; ?>" /><label for="wid_brdcolor"> Cor da borda da vitrine. A melhor cor é cinza (#DDDDDD). </label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Preço:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_prcolor" name="wid_prcolor" type="text" value="<?php echo $vs_options['wid_prcolor']; ?>" /><label for="wid_prcolor"> Cor do preço dos produtos.</label></td></tr>
  				</table>
 		</td>
	 </tr>
	</table>
    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br /> Para ver a estatística de cliques você precisa acessar sua conta no Google Analytics, acionar o relatório "Conteúdo Principal" e incluir o filtro abaixo da tabela "Desempenho de conteúdo". Em "Localizar Página" escolha a opção  "contendo" e escreva "/sub/" na caixa de texto (sem as aspas, é claro!). Pressione "Ir".<br />
			<input type="radio" name="wid_track" id="wts" value="sim" <?php echo $widtrksim; ?> /> <label for="wts">Sim</label>
			<br />
			<input type="radio" name="wid_track" id="wtn" value="nao" <?php echo $widtrknao; ?> /> <label for="wtn">Não</label>
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Anúncio alternativo</th>
		<td>
			<input type="radio" name="wid_altcode" value="FBD" id="wfbd" <?php echo $FBD; ?> /> <label for="wFBD">Fullbanner (468x60px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="wid_altcode" value="FBG" id="wfbg" <?php echo $FBG; ?> /> <label for="wfbg">Fullbanner (468x60px) Campanha de Giro</label>
			<br />
			<input type="radio" name="wid_altcode" value="SBD" id="wsbd" <?php echo $SBD; ?> /> <label for="wsbd">Superbanner (728x90px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="wid_altcode" value="SBG" id="wsbg" <?php echo $SBG; ?> /> <label for="wsbg">Superbanner (728x90px) Campanha de Giro</label>
			<br />
			<input type="radio" name="wid_altcode" value="BVD" id="wbvd" <?php echo $BVD; ?> /> <label for="wbvd">Barra Vertical (150x350px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="wid_altcode" value="BVG" id="wbvg" <?php echo $BVG; ?> /> <label for="wbvg">Barra Vertical (150x350px) Campanha de Giro</label>
			<br />
			<input type="radio" name="wid_altcode" value="SKYD" id="wskyd" <?php echo $SKYD; ?> /> <label for="wskyd">Sky (120x600px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="wid_altcode" value="SKYG" id="wskyg" <?php echo $SKYG; ?> /> <label for="wskyg">Sky (120x600px) Campanha de Giro</label>
			<br />
			<input type="radio" name="wid_altcode" value="BTD" id="wbtd" <?php echo $BTD; ?> /> <label for="wbtd">Botão (125x125px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="wid_altcode" value="BTG" id="wbtg" <?php echo $BTG; ?> /> <label for="wbtg">Botão (125x125px) Campanha de Giro</label>
			<br />
			<input type="radio" name="wid_altcode" value="HBD" id="whbd" <?php echo $HBD; ?> /> <label for="whbd">HalfBanner (120x60px) Campanha de Duráveis</label>
			<br />
			<input type="radio" name="wid_altcode" value="HBG" id="whbg" <?php echo $HBG; ?> /> <label for="whbg">HalfBanner (120x60px) Campanha de Giro</label>
			<br />
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

/**************************************************************************************************
 * Link para configuração do plugin na página de administração de plugins
 */
function vs_plugin_actions($links){

	$settings_link = '<a href="options-general.php?page=vitrinesubmarino.php">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
 
	return $links;
}
/**************************************************************************************************
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
			$bgcolor = $vs_options['wid_bgcolor'];  // Showing the width or not
					

		// Output
			echo $before_widget . $before_title . $vs_options['wid_title'] . $after_title;

			// start list
			echo '<ul>';
			
				// were there any posts found?
				$prod = vs_core ( $vs_options['wid_show'], $vs_options['wid_word'], "widget", $vs_options['wid_bgcolor'], $vs_options['wid_brdcolor'], $vs_options['wid_fontcolor'], $vs_options['wid_prcolor'], $vs_options['wid_procolor'], $vs_options['wid_prccolor']) ;
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
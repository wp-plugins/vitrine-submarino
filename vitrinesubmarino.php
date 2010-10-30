<?php
/*
Plugin Name: Vitrine Submarino
Plugin URI: http://www.bernabauer.com/wp-plugins/
Description: Mostre vitrines de produtos do Submarino em seu blog. Com o <a href="http://wordpress.org/extend/plugins/palavras-de-monetizacao/">Palavras de Monetização</a> você pode contextualizar manualmente os produtos. Para usar widgets é neecessário um tema compatível.
Version: 3.5
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

$vs_version = "3.5";
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
	if ( $vs_options['codafil'] != '') {
		add_filter('the_content', 'vs_auto',6);
	}
}

add_action('vs_cron', 'vs_atualiza_produtos' );

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'vs_plugin_actions' );

/**************************************************************************************************
 *  Coisas para serem feitas na instalacao do plugin
 */
function vs_activate() {

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
			'version'=>			$vs_version,
			'remover'=>			'nao',
			'wid_title'=>		'Ofertas Submarino',
			'wid_word'=>		'celular',
			'wid_ctx'=>			FALSE,
			'wid_show'=>		'3',
			'wid_fontcolor'=>	'#000000',
			'wid_bgcolor'=>		'#FFFFFF',
			'wid_brdcolor'=>	'#DDDDDD',
			'wid_prcolor'=>		'#3982C6',
			'wid_track'=>		'nao',
			'wid_altcode'=>		'wid_BVD',
			'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
			'ctx_word'=>		'notebook',
			'ctx_show'=>		'4',
			'ctx_exib_auto'=>	'auto',
			'ctx_tipo'=>		'tp_vit_horiz',
			'ctx_local'=>		'depois',
			'ctx_prcolor'=>		'#3982C6',
			'ctx_bg'=>			'white',
			'ctx_track'=>		'nao',
			'ctx_altcode'=>		'ctx_FBD',
		);
		add_option('vs_options', $vs_options);
		
		$sql = 'CREATE TABLE wp_vitrinesubmarino (
				id_sub 	int(20)			NOT NULL,
				name 	varchar(255) 	NOT NULL,
				link	varchar(255) 	NOT NULL,
				imgp	varchar(255) 	NOT NULL,
				imgm	varchar(255) 	NOT NULL,
				imgg	varchar(255) 	NOT NULL,
				descr	varchar(255) 	NOT NULL,
				priced	varchar(15) 	NOT NULL,
				pricep	varchar(15) 	NOT NULL,
				cat 	int(20)			NOT NULL,
				seed	varchar(255) 	NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
	
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);

	} else {
		if ($vs_options['version'] != $vs_version) {
		$vs_options = array(
			'codafil'=>			'',
			'cod_BP'=>			'',
			'cod_ML'=>			'',
			'cod_JC'=>			'',
			'LCP'=>				'[ Compare Preços ]',
			'PCP'=>				'BB',
			'LP'=>				'[ Veja mais ]',
			'version'=>			$vs_version,
			'remover'=>			'nao',
			'wid_title'=>		'Ofertas Submarino',
			'wid_word'=>		'celular',
			'wid_ctx'=>			FALSE,
			'wid_show'=>		'3',
			'wid_fontcolor'=>	'#000000',
			'wid_bgcolor'=>		'#FFFFFF',
			'wid_brdcolor'=>	'#DDDDDD',
			'wid_prcolor'=>		'#3982C6',
			'wid_track'=>		'nao',
			'wid_altcode'=>		'wid_BVD',
			'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
			'ctx_word'=>		'notebook',
			'ctx_show'=>		'4',
			'ctx_exib_auto'=>	'auto',
			'ctx_tipo'=>		'tp_vit_horiz',
			'ctx_local'=>		'depois',
			'ctx_prcolor'=>		'#3982C6',
			'ctx_bg'=>			'white',
			'ctx_track'=>		'nao',
			'ctx_altcode'=>		'ctx_FBD',
		);

			$vs_options['version'] = $vs_version;
			$sql = "DROP TABLE wp_vitrinesubmarino";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
		$sql = 'CREATE TABLE wp_vitrinesubmarino (
				id_sub 	int(20)			NOT NULL,
				name 	varchar(255) 	NOT NULL,
				link	varchar(255) 	NOT NULL,
				imgp	varchar(255) 	NOT NULL,
				imgm	varchar(255) 	NOT NULL,
				imgg	varchar(255) 	NOT NULL,
				descr	varchar(255) 	NOT NULL,
				priced	varchar(15) 	NOT NULL,
				pricep	varchar(15) 	NOT NULL,
				cat 	int(20)			NOT NULL,
				seed	varchar(255) 	NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
	
			dbDelta($sql);


			update_option('vs_options', $vs_options);
		}
	}

	if (!wp_next_scheduled('vs_cron')) {
		wp_schedule_event( time(), 'daily', 'vs_cron' );
	}

}

/***************************************************************************************************
 *  Antes de desativar a funcao abaixo eh executada
 */
 function vs_deactivate() {

	global $wpdb;

	wp_clear_scheduled_hook('vs_cron');
	
}

/***************************************************************************************************
 *  Alerta sobre problemas com a configuracao do plugin
 */
function vs_alerta() {

	global $vs_options;
	global $vs_version;
	global $domain;
	$msg = '';

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

	$word = vs_palcontext();

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
 * Pega palavra contextual do Palavras de Monetização
 */
function vs_palcontext($id = '') {

	global $vs_options;

	$current_plugins = get_option('active_plugins');
	if (in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) {
		$words_array = pm_get_words($id);

		if (count($words_array) == 0)
			$word = $vs_options['ctx_word'];
		else
			$word = $words_array[rand(0, count($words_array)-1)];
	} else {
		$word = $vs_options['ctx_word'];
	}

return $word;
}

/***************************************************************************************************
 * Vitrine Automatica
 */
function vs_auto($text) {

	global $vs_options;
 
	$vs_options = get_option('vs_options');

	$word = vs_palcontext();
	
	if ((is_single()) AND ($vs_options["ctx_exib_auto"] == 'auto')) {

		$vitrine = vs_core ( $vs_options["ctx_show"], $word, "contextual", "", "", "", $vs_options['ctx_prcolor']) ;

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

	if ($palavra == '') {
		return '';
	}

	$select = "SELECT * FROM wp_vitrinesubmarino WHERE seed = '". mysql_real_escape_string($palavra) ."'";

	$results = $wpdb->get_results( $wpdb->prepare($select) , ARRAY_A);

// tive que mudar o teste de vazio de string para vazio de array por conta da versão 3.0 do WP.
//  if ($results == "") {
	if (empty($results)) {
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
	@list($header, $data) = explode("\n\n", $data, 2);
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
		@$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
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
	$lprod = '';
	
	if ($palavra == '')
		$palavra = $vs_options['ctx_word'];
	
	if ($palavra == '') {
		error_log("vs_pagaprodutos: Não foi definada palavra alguma", 0);
		return '';
	}
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

	if ($buffer) {
		$buffer = mb_convert_encoding($buffer, 'HTML-ENTITIES', "UTF-8"); 
		@$doc->loadHTML( str_replace("&", "&amp;", $buffer) );
	}
	
############## COMEÇO DO CORE SCRIPT
	
		// Pego os links e os titulos
	
		$img = $doc->getElementsByTagName( "span" );
		
		$i = 0;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");
	
			if($teste == 'name entry-title' OR $teste == 'name') { 
				$i++;
				$produtos1[$i]['name'] = $img->nodeValue; 
			}
			if($teste == 'from') { 
				$produtos1[$i]['priced'] = str_replace(",",".",str_replace(".", "", ltrim($img->nodeValue,'de: R$ '))); 
			}
			if($teste == 'for') { 
				$produtos1[$i]['pricep'] = str_replace(",",".",str_replace(".", "", ltrim($img->nodeValue,'por: R$ '))); 
			}
			/** pega a descricao do produto **/
			if($teste == 'description') { 
				$produtos1[$i]['descr'] = $img->nodeValue; 
			}

		}
		$totalprod = $i;

		// Pego as imagens
	
		$img = $doc->getElementsByTagName( "img" );
	
		$i = 0;
	
		foreach( $img as $img )	{
			$teste = $img->getAttribute("class");
	
			if($teste == 'image') { 
				$i++; 
				$produtos1[$i]['imgp'] = "<img src=\"".ltrim($img->getAttribute("src"))."\" alt=\"".$produtos1[$i]['name']."\" hspace=\"3\" border=\"0\">"; 

				$tmp = '';
				$tmp = str_replace("_tn", "", $img->getAttribute("src"));
				$tmp = str_replace("pq", "", $tmp);
				$produtos1[$i]['imgm'] = "<img src=\"".$tmp."\" alt=\"".$produtos1[$i]['name']."\" hspace=\"3\" border=\"0\">"; 

				$tmp = str_replace(".jpg", "_4.jpg", $tmp);
				$produtos1[$i]['imgg'] = "<img src=\"".$tmp."\" alt=\"".$produtos1[$i]['name']."\" hspace=\"3\" border=\"0\">"; 
			}
		}

		// Pego os links e os titulos para categorias
	
		$img = $doc->getElementsByTagName( "a" );
	
		$i = 0;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");
	
			if($teste == 'link') { 
					$i++; 
					$produtos1[$i]['link'] = "http://www.submarino.com.br".$img->getAttribute("href").'?franq='.$vs_options['codafil']; 
					$elem = explode("/", $img->getAttribute("href"));
					$produtos1[$i]['cat'] = $elem[2];
					$produtos1[$i]['subid'] = $elem[3];
			}
		} // foreach
###############
		$produtos = array();

		for($i=1;$i<=$totalprod;$i++) {
			if ($produtos1[$i]['subid'] != '' AND @$produtos1[$i]['pricep'] != '') {
				if ( @$produtos1[$i]['priced'] != '') {
					$pd = $wpdb->escape($produtos1[$i]['priced']);
				} else {
					$pd = '';
				}
				
					$pp = $produtos1[$i]['pricep'];

				$lprod .= "('" . 
				$wpdb->escape($produtos1[$i]['name']) . "','" . 
				$pd . "','" . 
				$pp . "','" . 
				trim($wpdb->escape(@$produtos1[$i]['descr'])) . "','" . 
				$wpdb->escape($produtos1[$i]['link']) . "','" .  
				$wpdb->escape($produtos1[$i]['cat']) . "','" .  
				$wpdb->escape($produtos1[$i]['subid']) . "','" .  
				$produtos1[$i]['imgp'] . "','" .  
				$produtos1[$i]['imgm'] . "','" .  
				$produtos1[$i]['imgg'] . "','" .  
				$wpdb->escape($palavra) ."'), ";
			}
		} //for

	if ($lprod != '') {
		$insert = "INSERT INTO wp_vitrinesubmarino (name, priced, pricep, descr, link, cat, id_sub, imgp, imgm, imgg, seed) VALUES " . rtrim($lprod, ", ");
		
		$results = @$wpdb->query( $insert );
	}

	$select = "SELECT * FROM wp_vitrinesubmarino WHERE seed = '". mysql_real_escape_string($palavra) ."'";

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
	$ctx_bg = $vs_options['ctx_bg'];
	
	if ($vs_options['codafil'] == '')
		return "<p>ERRO: Código de Afiliado não informado.</p>";

	if ($vs_options['version'] != $vs_version)
		return "Vitrine Submarino ** ERRO: Atualização necessária! **";

	$idsubmarino = $vs_options['codafil'];			// Define codigo de afiliado para o script funcionar

	//pega produtos da BD (devolve um array)
	$produtos = vs_pegaprodutos($word);

	if (count($produtos) != 0) {

		shuffle($produtos);

		foreach ($produtos as $produto) {
	
			$nome = $produto['name'];
			$link_prod = $produto['link'];
			$imagem = $produto['imgp'];
			$preco = $produto['pricep'];
	
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

		if (strstr($vitrine, "banner") == FALSE) {
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
			} // switch
		} //if
			switch ($vitrine) {
		
				case "contextual":
					// vitrine contextual
					if ($vs_options['ctx_tipo'] == "tp_vit_horiz") {
						$td = 92 / $vs_options['ctx_show'];
						$imagem = str_replace("<img ", "<img width=90px height=90px ", $imagem);
						$imagem = rtrim($imagem,".");

						//mostra vitrine com produtos em uma unica linha (VITRINE HORIZONTAL)
#						$lista_de_produtos .= "<div onMouseover=\"ddrivetip('".$nome."', '#EFEFEF')\";=\"\" onMouseout=\"hideddrivetip()\">";
						$lista_de_produtos[] = '
						<div style="width:'.$td.'%;background-color:'.$ctx_bg.';text-align:center; line-height:120%;padding-right: 10px;padding-bottom: 10px;font-size:12px;border:0px;float:left;overflow: hidden;">
							<a href="'.$link_prod.'" '.$tc.'  target="_blank">
								<span style="width:90px;height:90px;position:relative;">'.$imagem.'</span>
							</a><div style="height:43px;overflow: hidden;">'.$nome.'</div>
							<div style="color:'.$corprec.';">
								&nbsp; R$ '.number_format($preco,2,",","").'&nbsp;
							</div>
							<div>
								<a href="'.$link_prod.'" '.$tc.' target="_blank"><strong>Veja mais</strong></a>
							</div>
							<div>'.$compare_precos.'</div>
						</div>';
						
					} elseif ($vs_options['ctx_tipo'] == "tp_vit_vert") {
						$imagem = str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 10px 0;\" alt=\"".$nome."\"", $imagem);
						//mostra vitrine com um produto por linha (VITRINE VERTICAL)
						$lista_de_produtos[] = '
							<div style="height:130px;background-color:'.$ctx_bg.';padding:3px;">
								<a href="'.$link_prod.'" '.$tc.'  target="_blank">
									<span style="width:90px;height:90px;position:relative;">'.$imagem.'</span>
								</a><div style="height:43px;overflow: hidden;">'.$nome.'</div>
								<div style="color:'.$corprec.';">
									<center>&nbsp; R$ '.number_format($preco,2,",","").'&nbsp;</center>
								</div>
								<div>
									<center><a href="'.$link_prod.'" '.$tc.' target="_blank"><strong>Veja mais</strong></a></center>
								</div>
								<div><center>'.$compare_precos.'</center></div>
							</div>';
												
					} elseif ($vs_options['ctx_tipo'] == "tp_vit_b468") {
						$credits='<div style="position:absolute;bottom:-2px;right:2px;font-size: 55%;"><small><a style="text-decoration:none;" href="http://www.bernabauer.com/wp-plugins/">Vitrine Submarino '.$vs_options['version'].'</a></small></div>';
						$imagem = rtrim(str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 0px 10px;height:60px;\" alt=\"".$nome."\"", $imagem), ".");
						$lista_de_produtos[] = '
							<div style="text-align: center;border: 1px solid #ccc;color:'.$desc.';background-color:white;padding:3px;text-decoration: none;width:468px;height:60px;position:relative;line-height:95%;"><a href="http://www.submarino.com.br?franq='.$vs_options['codafil'].'" target="_blank"><img style=" display: inline; float: right; margin: 0 0px 0px 0px; width: 50px;" src="http://i.s8.com.br/images/affiliates/selos/70x70_selo.gif" ></a><a style="text-decoration:none;" href="'.$link_prod.'" '.$tc.' target="_blank">'.$imagem.''.$nome.'</a><div style="color:'.$corprec.';font-size: 100%;position:absolute;left:50%;bottom:2px;">R$ '.number_format($preco,2,",","").'</div>'.$credits.'</div>';
						$i = $show;
					} elseif ($vs_options['ctx_tipo'] == "tp_vit_b728") {
						$credits='<div style="position:absolute;bottom:-2px;right:2px;font-size: 55%;"><small><a style="text-decoration:none;" href="http://www.bernabauer.com/wp-plugins/">Vitrine Submarino '.$vs_options['version'].'</a></small></div>';
						$imagem = rtrim(str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 0px 10px;height:90px;\" alt=\"".$nome."\"", $imagem), ".");
						$lista_de_produtos[] = '<div style="border: 1px solid #ccc;color:'.$desc.';background-color:white;padding:3px;text-decoration: none ! important;width:728px;height:90px;position:relative;"><a href="http://www.submarino.com.br?franq='.$vs_options['codafil'].'" target="_blank"><img style=" display: inline; float: right; margin: 0 10px 0px 10px;" src="http://i.s8.com.br/images/affiliates/selos/70x70_selo.gif"></a><a style="text-decoration:none;" href="'.$link_prod.'" '.$tc.' target="_blank">'.$imagem.'<div style="font-size: 150%;">'.$nome.'</div></a><br /><div style="color:'.$corprec.';font-size: 100%;position:absolute;left:50%;bottom:2px;">R$ '.number_format($preco,2,",","").'</div>'.$credits.'</div>';
						$i = $show;
						
					} elseif ($vs_options['ctx_tipo'] == "tp_vit_b250") {
						$imagem = rtrim(str_replace("<img ", "<img style=\"height:108px;\" alt=\"".$nome."\"", $imagem),".");
#						$lista_de_produtos .= "<div onMouseover=\"ddrivetip('".$nome."', '#EFEFEF')\";=\"\" onMouseout=\"hideddrivetip()\">";
						$lista_de_produtos[] = "<div style=\"width:110px;height:110px;background-color:white;text-align:center; padding-left: 10px;padding-bottom: 5px;font-size:12px;border:0px;float:left;overflow: hidden;\"><a style=\"text-decoration:none;\" href=\"".$link_prod.'" '.$tc.'  target="_blank"><span style="width:80px;height:80px;position:relative;">'.$imagem.'<div style="color:'.$corprec.';font-size: 120%;background-color:white;position: absolute; bottom: 65px; right: 2px;">&nbsp;R$ '.number_format($preco,2,",","").'&nbsp;</div></span></a><br />'.$nome.'</div>';
						if ($i == 4)
							$i = $show;
					}
					break;
	
				case "widget":
#					$imagem = rtrim(str_replace("<img ", "<img name=image".$i." onload=\"resizeimage('image".$i."');\"", $imagem), ".");
					$lista_de_produtos[] = '<div style="color:'.$desc.';background-color:'.$fundo.';text-align:center;padding:3px;text-decoration: none ! important;"><a href="'.$link_prod.'" '.$tc.' target="_blank">'.$imagem.'<br />'.$nome.'</a><br /><div style="color:'.$corprec.';">&nbsp; R$ '.number_format($preco,2,",","").'&nbsp;</div>'.$compare_precos.'</div>';
					break;

			} //switch

		if ($i >= $show)
			break;
		else
			$i++;
		} //foreach	

		#mostra primeiro slot diferenciado (adsense ou link de comparação de preços)
		$current_plugins = get_option('active_plugins');
		if (in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) {
			$words_array = pm_get_words();
		
			if ($words_array != '' AND $vs_options['ctx_slot1'] != "normal" AND $vitrine == "contextual" AND $vs_options['ctx_tipo'] == "horizontal" || $vs_options['ctx_tipo'] == "box-250") {
			
				if (count($lista_de_produtos) >= $show)
					$lixo = array_pop($lista_de_produtos);
				
				foreach ($lista_de_produtos as $produto) {
					$lista_final_de_produtos .= $produto;
				}

				shuffle($words_array);
			
				$compareprecoshead = '<strong>Compare Preços</strong><br />';
			
				switch($vs_options['ctx_slot1']) {
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
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_FBG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_SBD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_SBG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_BVD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_BVG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_SKYD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_SKYG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_BTD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_BTG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_HBD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_HBG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_BXD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=box&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "ctx_BXG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=box2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
			} // switch
		} else {
			// anuncio alternativo widget
			switch ($vs_options['wid_altcode']) {
				case "FBD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "FBG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "SBD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "SBG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "BVD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "BVG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=vertical2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "SKYD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "SKYG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "BTD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "BTG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "HBD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "HBG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "BXD":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=box&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
				case "BXG":
					$altcode = '<div style=\'margin-bottom:-20px;\'><a href=\'http://www.submarino.com.br\'>Submarino.com.br</a></div><div><script type=\'text/javascript\' src=\'http://www.submarino.com.br/afiliados/get_banner.asp?tipo=box2&franq='.$vs_options['codafil'].'\'></script></div>';
					break;
			} //switch
		} //if
		return "<br /><div style=\"float:center;\" align=\"center\" border=\"0\">
".$altcode."</div>";
	}# else {
	
	$credits = "<br /><div style=\"text-align:right;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>Vitrine Submarino ".$vs_options['version']."</a></small></div>";
	$credits='<div style="text-align:right;right:2px;"><small><a style="text-decoration:none;" href="http://www.bernabauer.com/wp-plugins/">Vitrine Submarino '.$vs_options['version'].'</a></small></div>';


	if (($vitrine == "contextual") AND ($vs_options['ctx_exib_auto'] == 'auto')) {
			$titulo = $vs_options['ctx_titulo'];
	 } else
		$titulo = '';

	if (strstr($vs_options['ctx_tipo'], "tp_vit_b") != FALSE OR strstr($vitrine, "banner") != FALSE) {
		$credits = "<br />";
		$titulo = '';
	}

	if (($vitrine == "contextual") AND ($vs_options['ctx_tipo'] == "tp_vit_b250")) {
		$credits='<span style="float:right;bottom:0px;right:2px;font-size: 55%;"><small><a style="text-decoration:none;" href="http://www.bernabauer.com/wp-plugins/">Vitrine Submarino '.$vs_options['version'].'</a></small></span>';
		$box_antes = '<div style="width:250px;height:250px;overflow:hidden;border: 1px solid #ccc; padding: 5px;">';
		$box_depois = $credits."</div>";
		$credits = '';
	}

	

	if ($vitrine == "widget") {
		$widgetid = "id=\"vswi\""; 
		$combordas = "border:2px solid ".$borda.";background-color:".$fundo.";";
	} else
		$combordas = '';

	return "<br />".$box_antes.''.$titulo."<div ".$widgetid." style=\"".$combordas."\">".$lista_de_produtos."".$credits."</div>".$box_depois;
	#}
}


/**************************************************************************************************
 *  Menu de configuracao
 */
function vs_option_menu() {
    if ( function_exists('add_options_page') ) {
        add_options_page('Vitrine Submarino', 'Vitrine Submarino', 'manage_options', basename(__FILE__), 'vs_options_subpanel');
	}
}

/**************************************************************************************************
 *  Pagina de opcoes
 */
function vs_options_subpanel() {

	global $wpdb;
	global $vs_options;

	//declaração de variáveis
	$PCP_BP = '';
	$PCP_ML = '';
	$PCP_JC = '';
	$PCP_BB = '';
	$PCP_NS = '';
	$PM_Present = '';
	$auto = '';
	$manual = '';
	$horizontal = '';
	$vertical = '';
	$antes = '';
	$depois = '';
	$ctxtrksim = '';
	$ctxtrknao = '';
	$widctxsim = '';
	$widctxnao = '';
	$widtrksim =  '';
	$widtrknao = '';
	
	$tp_vitrines = array(
		array("tp_vit_horiz", "Horizontal (produtos em uma única linha)"),
		array("tp_vit_vert","Vertical (um produto por linha)"),
		array("tp_vit_b250","Box 250x250 (até 4 produtos em 2 linhas)"),
		array("tp_vit_b468","Banner 468x60"),
		array("tp_vit_b728","Banner 728x90")
	);
	
	$alt_banners = array(
	array("ctx_FBD", 	"wid_FBD"	, "Fullbanner (468x60px) Campanha de Duráveis"),
	array("ctx_FBG", 	"wid_FBG"	, "Fullbanner (468x60px) Campanha de Giro"),
	array("ctx_SBD", 	"wid_SBD"	, "Superbanner (728x90px) Campanha de Duráveis"),
	array("ctx_SBG", 	"wid_SBG"	, "Superbanner (728x90px) Campanha de Giro"),
	array("ctx_BVD", 	"wid_BVD"	, "Barra Vertical (150x350px) Campanha de Duráveis"),
	array("ctx_BVG", 	"wid_BVG"	, "Barra Vertical (150x350px) Campanha de Giro"),
	array("ctx_SKYD", 	"wid_SKYD"	, "Sky (120x600px) Campanha de Duráveis"),
	array("ctx_SKYG", 	"wid_SKYG"	, "Sky (120x600px) Campanha de Giro"),
	array("ctx_BTD", 	"wid_BTD"	, "Botão (125x125px) Campanha de Duráveis"),
	array("ctx_BTG", 	"wid_BTG"	, "Botão (125x125px) Campanha de Giro"),
	array("ctx_HBD", 	"wid_HBD"	, "HalfBanner (120x60px) Campanha de Duráveis"),
	array("ctx_HBG", 	"wid_HBG"	, "HalfBanner (120x60px) Campanha de Giro"),
	array("ctx_BXD", 	"wid_BXD"	, "Box (300x250px) Campanha de Duráveis"),
	array("ctx_BXG", 	"wid_BXG"	, "Box (300x250px) Campanha de Giro")
	);
	$alt_banners_selected = '';
	
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

        if (isset($_POST['PCP'])) 
			$vs_options['PCP'] = strip_tags(stripslashes($_POST['PCP']));
            
		// Opções WIDGET

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
		$vs_options['wid_ctx'] = strip_tags(stripslashes($_POST['wid_ctx']));

		// Opções CONTEXTUAL
		if (isset($_POST['ctx_prcolor'])) 
			$vs_options['ctx_prcolor'] = strip_tags(stripslashes($_POST['ctx_prcolor']));
		if (isset($_POST['ctx_bg'])) 
			$vs_options['ctx_bg'] = strip_tags(stripslashes($_POST['ctx_bg']));
		
		if (isset($_POST['ctx_titulo'])) 
			$vs_options['ctx_titulo'] = stripslashes($_POST['ctx_titulo']);

		if (isset($_POST['ctx_show'])) 
			$vs_options['ctx_show'] = strip_tags(stripslashes($_POST['ctx_show']));


		$vs_options['ctx_word'] = strip_tags(stripslashes($_POST['ctx_word']));
		$vs_options['ctx_altcode'] = strip_tags(stripslashes($_POST['ctx_altcode']));
		$vs_options['ctx_exib_auto'] = strip_tags(stripslashes($_POST['ctx_exib_auto']));
		$vs_options['ctx_local'] = strip_tags(stripslashes($_POST['ctx_local']));
		$vs_options['ctx_track'] = strip_tags(stripslashes($_POST['ctx_track']));
		$vs_options['ctx_tipo'] = strip_tags(stripslashes($_POST['ctx_tipo']));

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

    if ( $vs_options['wid_ctx'] == 'sim') {
		$widctxsim = 'checked=\"checked\"';
    } else {
    	$widctxnao = 'checked=\"checked\"';
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
			$sql = "SELECT count( id_sub ) as count FROM wp_vitrinesubmarino";
			$results = $wpdb->get_results( $wpdb->prepare($sql) , ARRAY_A);
			echo $results['0']['count'];
			 ?>
			<br />
			Palavras distintas de produtos : 
			<?php 
			$sql = "SELECT count(distinct seed ) as count FROM wp_vitrinesubmarino";
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
		<th scope="row" valign="top">Tipo de Vitrine</th>
		<td>
			<?php foreach ($tp_vitrines as $tp_vitrine) {
					if ($vs_options['ctx_tipo'] == $tp_vitrine[0])
						$tp_vitrines_selected = "checked=\"checked\"";
					else
						$tp_vitrines_selected = "";
					echo "
					<input type=\"radio\" name=\"ctx_tipo\" id=\"".$tp_vitrine[0]."\" value=\"".$tp_vitrine[0]."\"" .$tp_vitrines_selected. " /> <label for=\"".$tp_vitrine[0]."\">".$tp_vitrine[1]."</label>
					<br />
					";
			 } ?>
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
		<th scope="row" valign="top">Cores</th>
		<td>
  				Preço: <input style="width: 70px;" id="ctx_prcolor" name="ctx_prcolor" type="text" value="<?php echo $vs_options['ctx_prcolor']; ?>" /><label for="ctx_prcolor"> Cor do preço dos produtos.</label><br />
  				Fundo: <input style="width: 70px;" id="ctx_bg" name="ctx_bg" type="text" value="<?php echo $vs_options['ctx_bg']; ?>" /><label for="ctx_br"> Cor de fundo da vitrine.</label><br />
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
			<?php foreach ($alt_banners as $alt_banner) {
					if ($vs_options['ctx_altcode'] == $alt_banner[0])
						$alt_banners_selected = "checked=\"checked\"";
					else
						$alt_banners_selected = "";
					echo "
					<input type=\"radio\" name=\"ctx_altcode\" id=\"".$alt_banner[0]."\" value=\"".$alt_banner[0]."\"" .$alt_banners_selected. " /> <label for=\"".$alt_banner[0]."\">".$alt_banner[2]."</label>
					<br />
					";
			 } ?>
			
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
		<th scope="row" valign="top">Fonte de produtos</th>
		<td>
			<input type="radio" name="wid_ctx" id="wcn" value="nao" <?php echo $widctxnao; ?> /> <label for="wcn">Palavra padrão</label>
			<input style="width: 60px;" id="wid_word" name="wid_word" type="text" value="<?php echo $vs_options['wid_word']; ?>" />
			<br />
			<input type="radio" name="wid_ctx" id="wcs" value="sim" <?php echo $widctxsim; ?> /> <label for="wcs">Contextual</label>
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
			<?php foreach ($alt_banners as $alt_banner) {
					if ($vs_options['wid_altcode'] == $alt_banner[1])
						$alt_banners_selected = "checked=\"checked\"";
					else
						$alt_banners_selected = "";
					echo "
					<input type=\"radio\" name=\"wid_altcode\" id=\"".$alt_banner[1]."\" value=\"".$alt_banner[1]."\"" .$alt_banners_selected. " /> <label for=\"".$alt_banner[1]."\">".$alt_banner[2]."</label>
					<br />
					";
			 } ?>



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

			if (is_single() AND $vs_options['wid_ctx'] == 'sim') {
				$word = vs_palcontext(get_the_ID());
			} else {
				$word = $vs_options['wid_word'];
			}

			// start list
			echo '<ul>';
			
				// were there any posts found?
				$prod = vs_core ( $vs_options['wid_show'], $word, "widget", $vs_options['wid_bgcolor'], $vs_options['wid_brdcolor'], $vs_options['wid_fontcolor'], $vs_options['wid_prcolor']) ;
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
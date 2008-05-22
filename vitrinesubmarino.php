<?php
/*
Plugin Name: Vitrine Submarino
Plugin URI: http://www.bernabauer.com/wp-plugins/
Description: Inspirado em <a href='http://jobsonlemos.com/?p=64'>script de Jobson Lemos</a>. O plugin mostra uma quantidade de ofertas configuráveis ao gosto do freguês. Requer tema de wordpress compatível com widgets.
Version: 2.0
Author: Bernardo Bauer
Author URI: http://www.bernabauer.com/
*/
global $wpdb;
global $vs_options;
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
	add_filter('the_content', 'vs_auto');
} 
	add_filter('the_content', 'vs_shopping');

// #beta inclui javascript para tabs
add_action('wp_head', 'vs_header');

#beta
function vs_header() {
$url = get_settings('siteurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__));
echo '<!-- Dependencies --> 
		<!-- Sam Skin CSS for TabView --> 
		<link rel="stylesheet" type="text/css" href="'.$url.'/tabs.css">

		<!-- JavaScript Dependencies for Tabview: --> 
		<script type="text/javascript" src="'.$url.'/yahoo-dom-event.js"></script> 
		<script type="text/javascript" src="'.$url.'/element-beta-min.js"></script> 

		<!-- Source file for TabView --> 
		<script type="text/javascript" src="'.$url.'/tabview-min.js"></script>';

}

/***************************************************************************************************
 *  Coisas para serem feitas na instalacao do plugin
 */
function vs_activate() {

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	global $wpdb;

	$vs_options = array(
		'codafil'=>			'',
		'cod_BP'=>			'',
		'cod_ML'=>			'',
		'cod_JC'=>			'',
		'LCP'=>				'[ Compare Preços ]',
		'PCP'=>				'BB',
		'LP'=>				'[ Veja mais ]',
		'LPT'=>				'submarino',
		'version'=>			'2.0',
		'host'=>			'',
		'orderby'=>			'sortordersell',
		'categorias'=>		'sim',
		'cat_track'=>		'nao',
		'remover'=>			'nao',
		'shp_url'=>			'/loja',
		'shp_orderby'=>		'sortordersell',
		'shp_show'=>		'21',
		'shp_word'=>		'Celular',
		'shp_track'=>		'nao',
		'shp_bgcolor'=>		'#FFFFFF',
		'shp_brdcolor'=>	'#DDDDDD',
		'wid_title'=>		'Ofertas Submarino',
		'wid_orderby'=>		'sortordersell',
		'wid_show'=>		'5',
		'wid_fontcolor'=>	'#000000',
		'wid_bgcolor'=>		'#FFFFFF',
		'wid_brdcolor'=>	'#DDDDDD',
		'wid_word'=>		'Celular',
		'wid_altcode'=>		'BVD',
		'wid_track'=>		'nao',
		'wid_word'=>		'Notebook',
		'ctx_orderby'=>		'sortordersell',
		'ctx_fontcolor'=>	'#000000',
		'ctx_bgcolor'=>		'#FFFFFF',
		'ctx_brdcolor'=>	'#DDDDDD',
		'ctx_word'=>		'Notebook',
		'ctx_tipo'=>		'horizontal',
		'ctx_style'=>		'semabas',
		'ctx_local'=>		'depois',
		'ctx_show'=>		'4',
		'ctx_exib_auto'=>	'auto',
		'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
		'ctx_track'=>		'nao',
		'ctx_altcode'=>		'FBD',
	);

	add_option('vs_options', $vs_options);
}

/***************************************************************************************************
 *  Antes de desativar a funcao abaixo eh executada
 */
 function vs_deactivate() {

	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

	global $wpdb;

	//pega dados da base
	global $vs_options;

	
	if ($vs_options['remover'] == 'sim')
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
function vs_auto1($text) {

	global $vs_options;
	
	$current_plugins = get_option('active_plugins');
	if (in_array('palavrasmonetizacao.php', $current_plugins)) {
		$words_array = pm_get_words();
		$word_pm = $words_array[0];
	}
	if ($word_pm)
		$word = $word_pm;
	else
		$word = $vs_options['ctx_word'];

	if ((is_single()) AND ($vs_options["ctx_exib_auto"] == 'auto')) {

		$vitrine = vs_core ( $vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor']) ;
		
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
function vs_vitrine ($show = 3, $word = "notebook", $fundo = "#FFFFFF", $borda = "#DDDDDD", $texto = "#000000") {

	global $vs_options;

	$vs_options = get_option('vs_options');
	
	$vitrine_temp = vs_core($show, $word, "contextual", $fundo, $borda, $texto);

	if ($vs_options['ctx_exib_auto'] == 'auto') {
		$vitrine = $vs_options['ctx_titulo'].$vitrine_temp;
		return $vitrine;
	} else {
		echo $vitrine_temp;
		return '';
	}
}

/**************************************************************************************************/
function vs_vitrine_tabs($words) {

	global $vs_options;

		for ($i=1; $i<=count($words); $i++) {
			$vitrine[$i] = vs_core ( $vs_options["ctx_show"], $words[$i-1], "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor']) ;
		}
		
		for ($i=1; $i<=count($words);$i++) {
			$abas .= "<li class=\"selected\"><a href=\"#tab".$i."\"><em>&nbsp;".$words[$i-1]."&nbsp;</em></a></li>";
		}
		for ($i=1; $i<=count($words);$i++) {
			$vitrines .= "<div>".$vitrine[$i]."</div>";
		}
		$vitrine_final = "<br /><br />".$vs_options['ctx_titulo']."<script type=\"text/javascript\">
		var myTabs = new YAHOO.widget.TabView(\"demo\");
		</script> 
		<div class=\"yui-skin-xp\">
		<div id=\"demo\" class=\"yui-navset\">
			<ul class=\"yui-nav\">
			".$abas."
			</ul>            
			<div class=\"yui-content\">
			".$vitrines."
			</div>
		</div>
		</div>
		".$links_cats."<BR><BR>".$lista_de_produtos."".$credits;

return $vitrine_final;

}

/***************************************************************************************************
 *  Funcao principal
 */
function vs_auto($text) {

	global $vs_options;

	$vs_options = get_option('vs_options');
	
	$current_plugins = get_option('active_plugins');
	if (in_array('palavrasmonetizacao.php', $current_plugins)) {
		$words_array = pm_get_words();
		$word_pm = $words_array[0];
	}
	if ($word_pm)
		$word = $word_pm;
	else
		$word = $vs_options['ctx_word'];

	if ((is_single()) AND ($vs_options["ctx_exib_auto"] == 'auto')) {

		if ($vs_options['ctx_style'] == "comabas") {
			if (!in_array('palavras-de-monetizacao/palavrasmonetizacao.php', get_option('active_plugins'))) {
				$vs_options['ctx_style'] = "semabas";
				update_option('vs_options',$vs_options);
			} else 
				$vitrine = vs_vitrine_tabs($words_array);
		} else
			$vitrine = vs_core ( $vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor']) ;


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
function vs_core ($show, $word, $vitrine, $fundo, $borda, $desc) {
	global $wpdb;

	error_reporting( 0 );

	global $vs_options;
	
	if ($vs_options['codafil'] == '')
		return "ERRO: Código de Afiliado não informado.";

	$idsubmarino = $vs_options['codafil'];			// Define codigo de afiliado para o script funcionar
	$palavrapadrao = $word; // Define a palavra chave para o script funcionar
	
	if ($vitrine != "widget") {
		$palavrabuscada = $_GET['pal'];   
		if ( !$palavrabuscada ) { 
			$palavrabuscada = $palavrapadrao; 
		}
	} else {
		$palavrabuscada = $palavrapadrao; 
	}

	$palavrabuscada = urlencode(utf8_decode($palavrabuscada));

	$desde = $_GET['pag'];
	if ( !$desde ) { 
		$desde = 1; 
	}

switch ($vitrine) {

	case "widget":
		$urlsub = 'http://www.submarino.com.br/HomeCache/AllSearchResult.aspx?PageHits=50&OrderBy='.$vs_options['wid_orderby'].'&Query=';
		$catsep = "<br /> * ";
		break;

	case "contextual":
		$urlsub = 'http://www.submarino.com.br/HomeCache/AllSearchResult.aspx?PageHits=50&OrderBy='.$vs_options['ctx_orderby'].'&Query=';
		$catsep = ", ";

		if ($vs_options['ctx_tipo'] == "horizontal") { 
			$lista_de_produtos = "<table id=\"vs_ctx_tabela_produtos\"><tr>";
		}
		break;

	case "shopping":
		$urlsub = 'http://www.submarino.com.br/HomeCache/AllSearchResult.aspx?PageHits=50&OrderBy='.$vs_options['shp_orderby'].'&Query=';
		$shop = $_GET['shop'];   
	
#		if ( !$shop ) { 
#			$shop = "DL"; 
#		}
		
		switch($shop) {
		
			case "LL":
				//livros lançamento
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int02.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28264";
				break;
			case "DL":
				//DVDs lançamento
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int03.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28262";
				break;
			case "CL":
				//CDs lançamento
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int04.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28266";
				break;
			case "SL":
				//Shows lançamento
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int05.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28268";
				break;
			case "GL":
				//Games lançamento
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int06.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28270";
				break;
			case "LP":
				//livros Pré-venda
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int02p.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28265";
				break;
			case "DP":
				//DVDs Pré-venda
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int03p.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28263";
				break;
			case "CP":
				//CDs Pré-venda
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int04p.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28267";
				break;
			case "SP":
				//Shows Pré-venda
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int05p.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28269";
				break;
			case "GP":
				//Games Pré-venda
				$url_shp = "http://www.submarino.com.br/local/lancamentos/home_lancamentos_int06p.asp?Query=ProductPage&ProdTypeId=4&PROMOID=28271";
				break;
		}

		$lista_de_produtos = "";
		//links para o shopping condicional para permalinks
		if (get_option('permalink_structure') == '') {
			$urlshops = $vs_options['shp_url'] ."&shop=" ;
		} else {
			$urlshops = $vs_options['shp_url'] ."/?shop=";	
		}
		echo "<table width=\"100%\" cellpadding=\"10\">";
		echo "<tr><th><center>Livros</center></th><th><center>Shows</center></th><th><center>DVDs</center></th><th><center>Games</center></th><th><center>CDs</center></th></tr>";
		echo "<tr><td><center><a href=\"".get_option(siteurl)."/".$urlshops."LP\">Pré-Venda</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."SP\">Pré-Venda</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."DP\">Pré-Venda</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."GP\">Pré-Venda</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."CP\">Pré-Venda</a></center></td></tr>";
		echo "<tr><td><center><a href=\"".get_option(siteurl)."/".$urlshops."LL\">Lançamentos</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."SL\">Lançamentos</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."DL\">Lançamentos</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."GL\">Lançamentos</a></center></td><td><center><a href=\"".get_option(siteurl)."/".$urlshops."CL\">Lançamentos</a></center></td></tr>";
		echo "<tr><td colspan=5>";
		
		break;
}
 		$urlaserlida = $urlsub.$palavrabuscada;

############## ACESSO A PÁGINA COM PRODUTOS

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

############## COMEÇO DO CORE SCRIPT

#	if ($vitrine != "shopping") {
	if ($shop == "") {

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
		// Pego os links e os titulos
	
		$img = $doc->getElementsByTagName( "a" );
	
		$i = 1;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");
	
			if($teste == 'link-mais') { 
				if (!in_array( substr(utf8_decode($img->nodeValue), strrpos(utf8_decode($img->nodeValue), "/")+1), $titulo_mais)) {
					$link_mais[$i] = $img->getAttribute("href").'&franq='.$idsubmarino; 
					$titulo_mais[$i] = substr(utf8_decode($img->nodeValue), strrpos(utf8_decode($img->nodeValue), "/")+1); 
					$i++; 
				}
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
	} else {
	#### CORE SCRIPT PARA O SHOPPING PERSONALIZADO
	// Pego as imagens
	
    $send  = "GET ".$url_shp." HTTP/1.1\r\n";
    $send .= "Host: www.submarino.com.br\r\n";
    $send .= "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) Gecko/20021204\r\n";
    $send .= "Referer: http://www.yahoo.com/\r\n";
    $send .= "Accept: text/xml,application/xml,application/xhtml+xml,";
    $send .= "text/html;q=0.9,text/plain;q=0.8,video/x-mng,image/png,";
    $send .= "image/jpeg,image/gif;q=0.2,text/css,*/*;q=0.1\r\n";
    $send .= "Accept-Language: en-us, en;q=0.50\r\n";
    $send .= "Connection: Close\r\n\r\n";
	
		$response = '';
		if( false != ( $fs = @fsockopen('www.submarino.com.br', 80, $errno, $errstr, 10) ) ) {
			fwrite($fs, $send);
	
			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
			fclose($fs);
			$response = explode("\r\n\r\n", $response, 2);
		}
	
	$doc = new DOMDocument();

	$doc->loadHTML( $response[1] );


//pega preco
		$img = $doc->getElementsByTagName( "span" );
	
		$i = 1;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");

			if($teste == 'priceblue') { 
				$preco[$i] = utf8_decode($img->nodeValue); 
				$i++; 
			}
		}

//pega marca
		$img = $doc->getElementsByTagName( "span" );
	
		$i = 1;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");

			if($teste == 'marca') { 
				$marca[$i] = utf8_decode($img->nodeValue); 
				$i++; 
			}
		}


// Pego os links e os titulos
		$img = $doc->getElementsByTagName( "a" );
	
		$i = 1;
	
		foreach( $img as $img ) {
			$teste = $img->getAttribute("class");
	
			if($teste == 'titulo') { 
				$link[$i] = "http://www.submarino.com.br".$img->getAttribute("href").'&franq='.$idsubmarino; 
				$titulo[$i] = str_replace(utf8_encode($preco[$i]), "", $img->nodeValue); 
				$titulo[$i] = str_replace(utf8_encode($marca[$i]), "", $titulo[$i]);
				$titulocompleto[$i] .= $titulo[$i]."<br />".utf8_encode($marca[$i]); 
				$i++; 
			}
	
		}

// Pego as imagens
		$img = $doc->getElementsByTagName( "img" );
	
		$i = 1;
	
		foreach( $img as $img )	{
			$teste = $img->getAttribute("vspace");
	
			if($teste == '10') { 
				$imagem[$i] = $img->getAttribute("src"); 
				$i++; 
			}
		}
}


############## FIM DO CORE SCRIPT

if (count($imagem) > 0) {

	for($a = 1; $a <= $show ; $a++) {

		if (($imagem[$a])) { 

			$palavras = explode('_',textoparalink ($titulo[$a]));

			$tc = '';

				$texto = utf8_encode($titulo[$a]);
				$texto = str_replace( " com ", " ", $texto );
				$texto = str_replace( " de ", " ", $texto );
				$texto = str_replace( " do ", " ", $texto );
				$texto = str_replace( " da ", " ", $texto );
				$texto = str_replace( " para ", " ", $texto );
				$texto = str_replace( " por ", " ", $texto );
				$pal = explode(" ", $texto);
				$busca = $pal[0]." ".$pal[1]." ".$pal[2]." ".$pal[3];

				$tccp = 'onClick="javascript: pageTracker._trackPageview (\'/out/sub/compare/uol/'.utf8_encode($titulo[$a]).'/\');"';

				//código de tracking do Google Analytics
				$tc = 'onClick="javascript: pageTracker._trackPageview (\'/out/sub/'.$vitrine.'/'.utf8_encode($titulo[$a]).'\');"';

				if (($vs_options['ctx_track'] == "nao") AND $vitrine == "contextual") {
					$tc = '';
				}
				if (($vs_options['wid_track'] == "nao") AND $vitrine == "widget")  {
					$tc = '';
				}
				if (($vs_options['shp_track'] == "nao")  AND $vitrine == "shopping") {
					$tc = '';
				}
				if ($vs_options['cat_track'] == "nao") {
					$tccat = '';
				}
				
				//FIM do código de tracking

				switch ($vs_options['PCP']) {
					case "BP":
						$compare_precos = "<a href=\"http://busca.buscape.com.br/cprocura?lkout=1&site_origem=".$cod_BP."&produto=".urlencode(utf8_decode($busca))."\" ".$tccp.">".$vs_options['LCP']."</a>"; 
						break;
					case "ML":
						$compare_precos = "<a href=\"http://pmstrk.mercadolivre.com.br/jm/PmsTrk?tool=".$cod_ML."&go=http://lista.mercadolivre.com.br/".urlencode($busca)."\"  ".$tccp.">".$vs_options['LCP']."</a>"; 
						break;
					case "JC":
						$compare_precos = "<a href=\"http://www.jacotei.com.br/mod.php?module=jacotei.pesquisa&texto=".urlencode($busca)."&precomin=&precomax=&af=".$cod_JC."\" ".$tccp.">".$vs_options['LCP']."</a>";  
						break;
					case "NS":
						$compare_precos = ''; 
						break;
					case "BB":
						$compare_precos = "<a href=\"http://bernabauer.shopping.busca.uol.com.br/busca.html?q=".urlencode(utf8_decode($busca))."\" ".$tccp.">".$vs_options['LCP']."</a>"; 
						break;
				}
				
				//ajuste quando não há produtos disponíveis
				if ($preco[$a])
					$preco_show = utf8_encode($preco[$a]);
				else
					$preco_show = "Esgotado";

				//links para o shopping condicional para permalinks
				if (get_option('permalink_structure') == '') {
					//não usa permalink
					$estaurl = $vs_options['shp_url'] ."&pal=";
				} else {
					//usa permalink
					$estaurl = $vs_options['shp_url'] ."/?pal=";	
				}
				
				//para onde é o link do produto?
				if ($vs_options['LPT'] == 'shopping') {
					$LPT = $estaurl."".$palavrabuscada;
				} else {
					$LPT = $link[$a];	
				}

				//código de tracking do Google Analytics
				$tccat = 'onClick="javascript: pageTracker._trackPageview (\'/out/sub/categoria/'.utf8_encode($titulo_mais[$a]).'\');"';
				$categorias .= $catsep."<a href=\"http://www.submarino.com.br".$link_mais[$a]."\" ".$tccat.">".utf8_encode($titulo_mais[$a])."</a>";

				switch ($vitrine) {

					case "contextual":
						// vitrine contextual
						if ($vs_options['ctx_tipo'] == "horizontal") {
		
							//mostra vitrine com produtos em uma unica linha (VITRINE HORIZONTAL)
							$lista_de_produtos .= '<td style="background-color:'.$fundo.';text-align:center;padding:3px;font-size:11px;"><a href="'.$link[$a].'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$imagem[$a].'"></a><div style="color:'.$desc.';">'.utf8_encode($titulo[$a]).'</div><div style="color:'.$desc.';font-weight: bold;">'.$preco_show.'</div><br /><div style=""><a href="'.$LPT.'" rel="nofollow" target="_blank"'.$tc.'>'.$vs_options["LP"].'</a></div><div>'.$compare_precos.'</div></td>';
						} else {
							
							//mostra vitrine com um produto por linha (VITRINE VERTICAL)
							$lista_de_produtos .= '<div style="color:'.$desc.';border:2px solid '.$borda.';height:104px;"><div style="background-color:'.$fundo.';padding:3px;"><table><tr><td><a href="'.$link[$a].'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$imagem[$a].'"></a></td><td>'.utf8_encode($titulo[$a]).'<br><b>'.$preco_show.'</b><br><br><a href="'.$LPT.'" rel="nofollow" target="_blank"'.$tc.'>'.$vs_options["LP"].'</a><div>'.$compare_precos.'</div></td></tr></table></div></div>';
						}
			
						break;

					case "widget":
						$lista_de_produtos .= '<div style="color:'.$desc.';background-color:'.$fundo.';padding:3px;"><center><p><a href="'.$link[$a].'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$imagem[$a].'"></a></p>'.utf8_encode($titulo[$a]).'<br><br><a href="'.$LPT.'" rel="nofollow" target="_blank"'.$tc.'><strong>'.$vs_options["LP"].'</strong></a><br><br>'.$preco_show.'<br><br><div>'.$compare_precos.'</div></center></div>';
						break;

					case "shopping":

 						$palavras = explode( '_', textoparalink($titulo[$a]) );
				
						$lista_de_produtos .= '<div style="float:left;width:30%;height:335px;border:2px solid '.$borda.'color:'.$desc.';background-color:'.$fundo.';padding:3px;"><div><center><p><a href="'.$link[$a].'" rel="nofollow" target="_blank"'.$tc.'><img src="'.$imagem[$a].'"></a></p>'.$titulocompleto[$a].'<br><br><b>'.$preco_show.'</b><br><br>'.'<a href="'.$link[$a].'" rel="nofollow" target="_blank"'.$tc.'>'.$vs_options["LP"].'</a> '.'<br><br>'.	'<small><a href="'.$estaurl.''.$palavras[0].'">'.$palavras[0].'</a>, '.'<a href="'.$estaurl.''.$palavras[0].'%20'.$palavras[1].'">'.$palavras[0].' '.$palavras[1].'</a>, '.'<a href="'.$estaurl.''.$palavras[0].'%20'.$palavras[1].'%20'.$palavras[2].'">'.$palavras[0].' '.$palavras[1].' '.$palavras[2].'</a>'.'</small>'.'</center></div></div>';
						break;
				}
		 }
	}
}	

	
	if (count($imagem) == 0) {
		if ($vitrine == "contextual") { 
			// anuncio alternativo contextual
			
			switch ($vs_options['ctx_altcode']) {
				case "ctx_FBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_FBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full2&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super2&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SKYD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_SKYG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=sky2&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_BTD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_BTG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=botao2&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_HBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&franq='.$vs_options['codafil'].'></script>';
					break;
				case "ctx_HBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=half&franq='.$vs_options['codafil'].'></script>';
					break;
			}
		} else {
			// anuncio alternativo widget
			switch ($vs_options['wid_altcode']) {
				case "FBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full&franq='.$vs_options['codafil'].'></script>';
					break;
				case "FBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=full2&franq='.$vs_options['codafil'].'></script>';
					break;
				case "SBD":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super&franq='.$vs_options['codafil'].'></script>';
					break;
				case "SBG":
					$altcode = '<script language="JavaScript1.1" type="text/javascript" src=http://www.submarino.com.br/afiliados/get_banner.asp?tipo=super2&franq='.$vs_options['codafil'].'></script>';
					break;
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
		}
		return $altcode;
	} else {
	
	$credits = "<div style=\"text-align:right;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>vitrine</a> by <a href='http://bernabauer.com'>bernabauer.com</a></small></div>";

	if (($vitrine == "contextual") AND  ($vs_options['ctx_tipo'] == "horizontal")) { 
		$lista_de_produtos .= "</tr></table>";
	}
	if ($vitrine == "shopping") { 
		$lista_de_produtos .= "</td></tr></table>";
		$credits = "<br /><br /><div style=\"text-align:center;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>shopping</a> by <a href='http://bernabauer.com'>bernabauer.com</a><br />Baseado em script original de Jobson Lemos</small></div>";

		return $lista_de_produtos." ".$credits;
	}
	if (($vitrine == "contextual") AND ($vs_options['ctx_exib_auto'] == 'auto') AND ($vs_options['ctx_style'] == 'semabas'))
		$titulo = $vs_options['ctx_titulo'];
	else
		$titulo = '';

	if ($vs_options['categorias'] == 'sim')
		$links_cats = "<div style=\"color:".$desc.";\">Conheça os melhores produtos das categorias:".$categorias."</div>";
	else
		$links_cats = '';

	return $titulo."<div style=\"border:2px solid ".$borda.";background-color:$fundo;\">".$links_cats."<BR><BR>".$lista_de_produtos."</div>".$credits;
	}
}

/***************************************************************************************************
 *  Menu de configuracao
 */
function vs_shopping($content) {
    
    global $vs_options;

	//links para o shopping condicional para permalinks
	if (get_option('permalink_structure') == '') {
		//não usa permalink
		$pag_shop = substr( $vs_options['shp_url'], stripos($vs_options['shp_url'], "=")+1);
	} else {
		//usa permalink
		$pag_shop = $vs_options['shp_url'];
	}

	if (is_page($pag_shop)) { 
		$lista_de_produtos = vs_core ( $vs_options['shp_show'], $vs_options['shp_word'], "shopping", $vs_options['shp_bgcolor'], $vs_options['shp_border'], "#000000") ;
	  
		$content = str_ireplace("#shopping#", $lista_de_produtos, $content);
	}
    return $content;
}

/***************************************************************************************************
 *  Menu de configuracao
 */
function vs_option_menu() {
    if ( function_exists('add_options_page') ) {
        add_options_page('Vitrine Submarino', 'Vitrine Submarino', 9, basename(__FILE__), 'vs_options_subpanel');
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
		$vs_options['host'] = strip_tags(stripslashes($_POST['host']));
		$vs_options['remover'] = strip_tags(stripslashes($_POST['remover']));
		$vs_options['categorias'] = strip_tags(stripslashes($_POST['categorias']));
		$vs_options['cat_track'] = strip_tags(stripslashes($_POST['cat_track']));
            
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

		if (isset($_POST['wid_word'])) 
			$vs_options['wid_word'] = strip_tags(stripslashes($_POST['wid_word']));

		$vs_options['wid_altcode'] = strip_tags(stripslashes($_POST['wid_altcode']));
		$vs_options['wid_track'] = strip_tags(stripslashes($_POST['wid_track']));

		// Opções SHOPPING
		$vs_options['shp_orderby'] = strip_tags(stripslashes($_POST['shp_orderby']));
		if (isset($_POST['shp_url'])) 
			$vs_options['shp_url'] = strip_tags(stripslashes($_POST['shp_url']));

		if (isset($_POST['shp_bgcolor'])) 
			$vs_options['shp_bgcolor'] = strip_tags(stripslashes($_POST['shp_bgcolor']));

		if (isset($_POST['shp_brdcolor'])) 
			$vs_options['shp_brdcolor'] = strip_tags(stripslashes($_POST['shp_brdcolor']));

		if (isset($_POST['shp_word'])) 
			$vs_options['shp_word'] = strip_tags(stripslashes($_POST['shp_word']));

		$vs_options['shp_track'] = strip_tags(stripslashes($_POST['shp_track']));


		// Opções CONTEXTUAL
		$vs_options['ctx_orderby'] = strip_tags(stripslashes($_POST['ctx_orderby']));
		if (isset($_POST['ctx_fontcolor'])) 
			$vs_options['ctx_fontcolor'] = strip_tags(stripslashes($_POST['ctx_fontcolor']));

		if (isset($_POST['ctx_bgcolor'])) 
			$vs_options['ctx_bgcolor'] = strip_tags(stripslashes($_POST['ctx_bgcolor']));

		if (isset($_POST['ctx_brdcolor'])) 
			$vs_options['ctx_brdcolor'] = strip_tags(stripslashes($_POST['ctx_brdcolor']));

		if (isset($_POST['ctx_titulo'])) 
			$vs_options['ctx_titulo'] = stripslashes($_POST['ctx_titulo']);

		if (isset($_POST['ctx_show'])) 
			$vs_options['ctx_show'] = strip_tags(stripslashes($_POST['ctx_show']));

		if (isset($_POST['ctx_word'])) 
			$vs_options['ctx_word'] = strip_tags(stripslashes($_POST['ctx_word']));

		$vs_options['ctx_altcode'] = strip_tags(stripslashes($_POST['ctx_altcode']));
		$vs_options['ctx_exib_auto'] = strip_tags(stripslashes($_POST['ctx_exib_auto']));
		$vs_options['ctx_local'] = strip_tags(stripslashes($_POST['ctx_local']));
		$vs_options['ctx_track'] = strip_tags(stripslashes($_POST['ctx_track']));
		$vs_options['ctx_tipo'] = strip_tags(stripslashes($_POST['ctx_tipo']));
		$vs_options['ctx_style'] = strip_tags(stripslashes($_POST['ctx_style']));
		$vs_options['ctx_alt'] = strip_tags(stripslashes($_POST['ctx_alt']));

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
		$ctxtrksim = 'checked=\"checked\"';
    } else {
    	$ctxtrknao = 'checked=\"checked\"';
    }

    if ( $vs_options['wid_track'] == 'sim') {
		$widtrksim = 'checked=\"checked\"';
    } else {
    	$widtrknao = 'checked=\"checked\"';
    }

    if ( $vs_options['cat_track'] == 'sim') {
		$cattrksim = 'checked=\"checked\"';
    } else {
    	$cattrknao = 'checked=\"checked\"';
    }

    if ( $vs_options['shp_track'] == 'sim') {
		$shptrksim = 'checked=\"checked\"';
    } else {
    	$shptrknao = 'checked=\"checked\"';
    }

    if ( $vs_options['host'] == '') {
		$outro = 'checked=\"checked\"';
    } else {
    	$godaddy = 'checked=\"checked\"';
    }

    if ( $vs_options['LPT'] == 'shopping') {
		$LPT_shp = 'checked=\"checked\"';
    } else {
    	$LPT_sub = 'checked=\"checked\"';
    }

    if ( $vs_options['ctx_tipo'] == 'horizontal') {
		$horizontal = 'checked=\"checked\"';
    } else {
    	$vertical = 'checked=\"checked\"';
    }

    if ( $vs_options['ctx_style'] == 'comabas') {
		$style_comabas = 'checked=\"checked\"';
    } else {
    	$style_semabas = 'checked=\"checked\"';
    }

    if ( $vs_options['remover'] == 'nao') {
		$remover_nao = 'checked=\"checked\"';
    } else {
    	$remover_sim = 'checked=\"checked\"';
    }
    
    if ( $vs_options['categorias'] == 'nao') {
		$cat_nao = 'checked=\"checked\"';
    } else {
    	$cat_sim = 'checked=\"checked\"';
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

	switch ($vs_options['wid_orderby']) {
		case "sellprice":
			$wid_sellprice = 'checked=\"checked\"';
			break;
		case "sortordersell":
			$wid_sortordersell = 'checked=\"checked\"';
			break;
		case "searchall":
			$wid_searchall = 'checked=\"checked\"';
			break;
		case "ranking":
			$wid_ranking = 'checked=\"checked\"';
			break;
		default:
			$wid_noorder = 'checked=\"checked\"';
			break;
	}		

	switch ($vs_options['shp_orderby']) {
		case "sellprice":
			$shp_sellprice = 'checked=\"checked\"';
			break;
		case "sortordersell":
			$shp_sortordersell = 'checked=\"checked\"';
			break;
		case "searchall":
			$shp_searchall = 'checked=\"checked\"';
			break;
		case "ranking":
			$shp_ranking = 'checked=\"checked\"';
			break;
		default:
			$shp_noorder = 'checked=\"checked\"';
			break;
	}		

	switch ($vs_options['ctx_orderby']) {
		case "sellprice":
			$ctx_sellprice = 'checked=\"checked\"';
			break;
		case "sortordersell":
			$ctx_sortordersell = 'checked=\"checked\"';
			break;
		case "searchall":
			$ctx_searchall = 'checked=\"checked\"';
			break;
		case "ranking":
			$ctx_ranking = 'checked=\"checked\"';
			break;
		default:
			$ctx_noorder = 'checked=\"checked\"';
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
		<th scope="row" valign="top">Comparação de Preços</th>
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
			<input type="radio" name="PCP" value="BP" <?php echo $PCP_BP; ?>> BuscaPé
			<br />
			<input type="radio" name="PCP" value="ML" <?php echo $PCP_ML; ?>> Mercado Livre
			<br />
			<input type="radio" name="PCP" value="JC" <?php echo $PCP_JC; ?>> Jacotei
			<br />
			<input type="radio" name="PCP" value="NS" <?php echo $PCP_NS; ?>> Não mostrar nada
			<br />
			<input type="radio" name="PCP" value="BB" <?php echo $PCP_BB; ?>> Shopping bernabauer.com
			<br />
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

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Link do Produto</th>
		<td>
			 <input name="LP" type="text" id="LP" value="<?php echo $vs_options['LP']; ?>" size=15  /><br />
			 Informe o que você quer mostrar como nome do link para o produto no Submarino. Bons exemplos são: "[ COMPRAR ]", "Veja mais" e "Compre agora". 
			<br />
			<br />
			<input type="radio" name="LPT" value="submarino" <?php echo $LPT_sub; ?>> Link aponta para o Submarino
			<br />
			<input type="radio" name="LPT" value="shopping" <?php echo $LPT_shp; ?>> Link aponta para o Shopping
			<br />
			Esta opção não afeta o link da imagem do produto.
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Mostrar categorias</th>
		<td>
			<input type="radio" name="categorias" value="nao" <?php echo $cat_nao; ?>> Não
			<br />
			<input type="radio" name="categorias" value="sim" <?php echo $cat_sim; ?>> Sim
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques em Categorias</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br />
			<input type="radio" name="cat_track" value="sim" <?php echo $cattrksim; ?>> Sim
			<br />
			<input type="radio" name="cat_track" value="nao" <?php echo $cattrknao; ?>> Não
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Remover opções ao desativar</th>
		<td>
			<input type="radio" name="remover" value="nao" <?php echo $remover_nao; ?>> Não
			<br />
			<input type="radio" name="remover" value="sim" <?php echo $remover_sim; ?>> Sim
			<br />
		</td>
	 </tr>
	</table>
<br />
    <h2>Contextual</h2>
<?php

$current_plugins = get_option('active_plugins');
if (!in_array('palavras-de-monetizacao/palavrasmonetizacao.php', $current_plugins)) 
	$PMdisabled = "DISABLED";

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
		<th scope="row" valign="top">Cor do texto</th>
		<td>
  				<input style="width: 60px;" id="ctx_fontcolor" name="ctx_fontcolor" type="text" value="<?php echo $vs_options['ctx_fontcolor']; ?>" /><br />
 				<label for="ctx_fontcolor">Cor do texto de descrição dos produtos. A melhor cor é preta (#000000 ou BLACK).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor de fundo</th>
		<td>
  				<input style="width: 60px;" id="ctx_bgcolor" name="ctx_bgcolor" type="text" value="<?php echo $vs_options['ctx_bgcolor']; ?>" /><br />
 				<label for="ctx_bgcolor">Cor de fundo dos produtos. A melhor cor é branca (#FFFFFF).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor da borda</th>
		<td>
  				<input style="width: 60px;" id="ctx_brdcolor" name="ctx_brdcolor" type="text" value="<?php echo $vs_options['ctx_brdcolor']; ?>" /><br />
 				<label for="ctx_brdcolor">Cor da borda da vitrine. A melhor cor é cinza (#DDDDDD).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Produto Padrão</th>
		<td>
 				<input style="width: 30%;" id="ctx_word" name="ctx_word" type="text" value="<?php echo $vs_options['ctx_word']; ?>" /><br />
 				<label for="Submarino-word">Informe a palavra para popular a vitrine quando não houver outra fonte para busca de produtos. Evite utilização de acentos.</label><br />
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
			 &lt;?php if(function_exists('vs_vitrine')) { vs_vitrine (3, "#FFFFFF", "iPod", "#DDDDDD"); } ?&gt;<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Tipo de Vitrine</th>
		<td>
			<input type="radio" name="ctx_tipo" value="horizontal" <?php echo $horizontal; ?>> Horizontal (produtos em uma única linha)
			<br />
			<input type="radio" name="ctx_tipo" value="vertical" <?php echo $vertical; ?>> Vertical (um produto por linha)
			<br />
			<br />
			<input <?php echo $PMdisabled; ?> type="radio" name="ctx_style" value="comabas" <?php echo $style_comabas; ?>> Com Abas (Requer <a href="http://www.bernabauer.com/wp-plugins/">Palavras de Monetização</a>)
			<br />
			<input type="radio" name="ctx_style" value="semabas" <?php echo $style_semabas; ?>> Sem Abas 
			<br />
			
			
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Ordem dos Produtos</th>
		<td>
			<input type="radio" name="ctx_orderby" value="" <?php echo $ctx_noorder; ?>> Não Ordenar
			<br />
			<input type="radio" name="ctx_orderby" value="sellprice" <?php echo $ctx_sellprice; ?>> Preço
			<br />
			<input type="radio" name="ctx_orderby" value="sortordersell" <?php echo $ctx_sortordersell; ?>> Mais Vendidos
			<br />
			<input type="radio" name="ctx_orderby" value="searchall" <?php echo $ctx_searchall; ?>> Relevância
			<br />
			<input type="radio" name="ctx_orderby" value="ranking" <?php echo $ctx_ranking; ?>> Avaliações
			<br />
			<label for="orderby">Ordem dos produtos a serem mostrados. Se a vitrine estiver mostrando produtos estranhos, mude a ordem selecionada. Às vezes a ordem "Mais Vendidos" não funciona corretamente e trocando a opção para "Não Ordenar" ou "Relevância" resolve a questão</label>
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br />
			<input type="radio" name="ctx_track" value="sim" <?php echo $ctxtrksim; ?>> Sim
			<br />
			<input type="radio" name="ctx_track" value="nao" <?php echo $ctxtrknao; ?>> Não
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Anúncio alternativo</th>
		<td>
			<input type="radio" name="ctx_altcode" value="ctx_FBD" <?php echo $ctx_FBD; ?>> Fullbanner (468x60px) Campanha de Duráveis
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_FBG" <?php echo $ctx_FBG; ?>> Fullbanner (468x60px) Campanha de Giro
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_SBD" <?php echo $ctx_SBD; ?>> Superbanner (728x90px) Campanha de Duráveis
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_SBG" <?php echo $ctx_SBG; ?>> Superbanner (728x90px) Campanha de Giro
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_BVD" <?php echo $ctx_BVD; ?>> Barra Vertical (150x350px) Campanha de Duráveis
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_BVG" <?php echo $ctx_BVG; ?>> Barra Vertical (150x350px) Campanha de Giro
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_SKYD" <?php echo $ctx_SKYD; ?>> Sky (120x600px) Campanha de Duráveis
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_SKYG" <?php echo $ctx_SKYG; ?>> Sky (120x600px) Campanha de Giro
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_BTD" <?php echo $ctx_BTD; ?>> Botão (125x125px) Campanha de Duráveis
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_BTG" <?php echo $ctx_BTG; ?>> Botão (125x125px) Campanha de Giro
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_HBD" <?php echo $ctx_HBD; ?>> HalfBanner (120x60px) Campanha de Duráveis
			<br>
			<input type="radio" name="ctx_altcode" value="ctx_HBG" <?php echo $ctx_HBG; ?>> HalfBanner (120x60px) Campanha de Giro
			<br>
			<label for="ctx_altcode">Código HTML para ser mostrado caso não sejam encontrados produtos com a palavra acima.</label>
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
<!--		
<?php # } else { ?>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Atenção</th>
		<td>
			O módulo contextual do Vitrine Submarino requer o plugin Palavras de Monetização.<br />
		</td>
	 </tr>
	</table>
	
<?php #}  ?>
-->
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
		<th scope="row" valign="top">Quant. Produtos</th>
		<td>
				<input style="width: 20px;" id="wid_show" name="wid_show" type="text" value="<?php echo $vs_options['wid_show']; ?>" /><br />
				<label for="wid_show">Quantos produtos deverão ser motrados na vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor do texto</th>
		<td>
  				<input style="width: 60px;" id="wid_fontcolor" name="wid_fontcolor" type="text" value="<?php echo $vs_options['wid_fontcolor']; ?>" /><br />
 				<label for="wid_fontcolor">Cor do texto de descrição dos produtos. A melhor cor é preta (#000000 ou BLACK).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor de fundo</th>
		<td>
  				<input style="width: 60px;" id="wid_bgcolor" name="wid_bgcolor" type="text" value="<?php echo $vs_options['wid_bgcolor']; ?>" /><br />
 				<label for="wid_bgcolor">Cor de fundo dos produtos. A melhor cor é branca (#FFFFFF).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor da borda</th>
		<td>
  				<input style="width: 60px;" id="wid_brdcolor" name="wid_brdcolor" type="text" value="<?php echo $vs_options['wid_brdcolor']; ?>" /><br />
 				<label for="wid_brdcolor">Cor da borda da vitrine. A melhor cor é cinza (#DDDDDD).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Produto</th>
		<td>
 				<input style="width: 30%;" id="wid_word" name="wid_word" type="text" value="<?php echo $vs_options['wid_word']; ?>" /><br />
 				<label for="wid_word">Informe a palavra para popular a vitrine. Evite utilização de acentos.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Ordem dos Produtos</th>
		<td>
			<input type="radio" name="wid_orderby" value="" <?php echo $wid_noorder; ?>> Não Ordenar
			<br />
			<input type="radio" name="wid_orderby" value="sellprice" <?php echo $wid_sellprice; ?>> Preço
			<br />
			<input type="radio" name="wid_orderby" value="sortordersell" <?php echo $wid_sortordersell; ?>> Mais Vendidos
			<br />
			<input type="radio" name="wid_orderby" value="searchall" <?php echo $wid_searchall; ?>> Relevância
			<br />
			<input type="radio" name="wid_orderby" value="ranking" <?php echo $wid_ranking; ?>> Avaliações
			<br />
			<label for="orderby">Ordem dos produtos a serem mostrados. Se a vitrine estiver mostrando produtos estranhos, mude a ordem selecionada. Às vezes a ordem "Mais Vendidos" não funciona corretamente e trocando a opção para "Não Ordenar" ou "Relevância" resolve a questão</label>
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br />
			<input type="radio" name="wid_track" value="sim" <?php echo $widtrksim; ?>> Sim
			<br />
			<input type="radio" name="wid_track" value="nao" <?php echo $widtrknao; ?>> Não
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Anúncio alternativo</th>
		<td>
			<input type="radio" name="wid_altcode" value="FBD" <?php echo $FBD; ?>> Fullbanner (468x60px) Campanha de Duráveis
			<br>
			<input type="radio" name="wid_altcode" value="FBG" <?php echo $FBG; ?>> Fullbanner (468x60px) Campanha de Giro
			<br>
			<input type="radio" name="wid_altcode" value="SBD" <?php echo $SBD; ?>> Superbanner (728x90px) Campanha de Duráveis
			<br>
			<input type="radio" name="wid_altcode" value="SBG" <?php echo $SBG; ?>> Superbanner (728x90px) Campanha de Giro
			<br>
			<input type="radio" name="wid_altcode" value="BVD" <?php echo $BVD; ?>> Barra Vertical (150x350px) Campanha de Duráveis
			<br>
			<input type="radio" name="wid_altcode" value="BVG" <?php echo $BVG; ?>> Barra Vertical (150x350px) Campanha de Giro
			<br>
			<input type="radio" name="wid_altcode" value="SKYD" <?php echo $SKYD; ?>> Sky (120x600px) Campanha de Duráveis
			<br>
			<input type="radio" name="wid_altcode" value="SKYG" <?php echo $SKYG; ?>> Sky (120x600px) Campanha de Giro
			<br>
			<input type="radio" name="wid_altcode" value="BTD" <?php echo $BTD; ?>> Botão (125x125px) Campanha de Duráveis
			<br>
			<input type="radio" name="wid_altcode" value="BTG" <?php echo $BTG; ?>> Botão (125x125px) Campanha de Giro
			<br>
			<input type="radio" name="wid_altcode" value="HBD" <?php echo $HBD; ?>> HalfBanner (120x60px) Campanha de Duráveis
			<br>
			<input type="radio" name="wid_altcode" value="HBG" <?php echo $HBG; ?>> HalfBanner (120x60px) Campanha de Giro
			<br>
			<label for="wid_altcode">Código HTML para ser mostrado caso não sejam encontrados produtos com a palavra acima.</label>
		</td>
	 </tr>
	</table>
<br />
    <h2>Shopping</h2>
    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Página do Shopping</th>
		<td>
				<input id="shp_url" name="shp_url" type="text" value="<?php echo $vs_options['shp_url']; ?>" /><br />
				<label for="shp_url">Se você usar Permalinks não coloque a barra no final. Se a página for www.seusite.com/loja coloque apenas "loja" sem as apas! ;-) Se você não usa permalinks a URL da página do shopping deve ser algo parecido com "?page_id=11".</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Quant. Produtos</th>
		<td>
				<input style="width: 20px;" id="shp_show" name="shp_show" type="text" value="<?php echo $vs_options['shp_show']; ?>" /><br />
				<label for="shp_show">Quantos produtos deverão ser motrados no shopping.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor de fundo</th>
		<td>
  				<input style="width: 60px;" id="shp_bgcolor" name="shp_bgcolor" type="text" value="<?php echo $vs_options['shp_bgcolor']; ?>" /><br />
 				<label for="shp_bgcolor">Cor de fundo dos produtos. A melhor cor é branca (#FFFFFF).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Cor da borda</th>
		<td>
  				<input style="width: 60px;" id="shp_brdcolor" name="shp_brdcolor" type="text" value="<?php echo $vs_options['shp_brdcolor']; ?>" /><br />
 				<label for="shp_brdcolor">Cor da borda da vitrine. A melhor cor é cinza (#DDDDDD).</label><br />
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Produto</th>
		<td>
 				<input style="width: 30%;" id="shp_word" name="shp_word" type="text" value="<?php echo $vs_options['shp_word']; ?>" /><br />
 				<label for="shp_word">Informe a palavra para popular o shopping na primeira visita. Evite utilização de acentos.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Ordem dos Produtos</th>
		<td>
			<input type="radio" name="shp_orderby" value="" <?php echo $shp_noorder; ?>> Não Ordenar
			<br />
			<input type="radio" name="shp_orderby" value="sellprice" <?php echo $shp_sellprice; ?>> Preço
			<br />
			<input type="radio" name="shp_orderby" value="sortordersell" <?php echo $shp_sortordersell; ?>> Mais Vendidos
			<br />
			<input type="radio" name="shp_orderby" value="searchall" <?php echo $shp_searchall; ?>> Relevância
			<br />
			<input type="radio" name="shp_orderby" value="ranking" <?php echo $shp_ranking; ?>> Avaliações
			<br />
			<label for="orderby">Ordem dos produtos a serem mostrados. Se a vitrine estiver mostrando produtos estranhos, mude a ordem selecionada. Às vezes a ordem "Mais Vendidos" não funciona corretamente e trocando a opção para "Não Ordenar" ou "Relevância" resolve a questão</label>
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Rastrear Cliques</th>
		<td>
			É necessário ter uma conta no Google Analytics.<br />
			<input type="radio" name="shp_track" value="sim" <?php echo $shptrksim; ?>> Sim
			<br />
			<input type="radio" name="shp_track" value="nao" <?php echo $shptrknao; ?>> Não
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
			$bgcolor = $vs_options['wid_bgcolor'];  // Showing the width or not
					

		// Output
			echo $before_widget . $before_title . $vs_options['wid_title'] . $after_title;

			// start list
			echo '<ul>';
				// were there any posts found?
				$prod = vs_core ( $vs_options['wid_show'], $vs_options['wid_word'], "widget", $vs_options['wid_bgcolor'], $vs_options['wid_brdcolor'], $vs_options['wid_fontcolor']) ;
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
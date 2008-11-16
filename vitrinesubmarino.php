<?php
/*
Plugin Name: Vitrine Submarino
Plugin URI: http://www.bernabauer.com/wp-plugins/
Description: Mostre vitrines de produtos do Submarino em seu blog. Com o <a href="http://wordpress.org/extend/plugins/palavras-de-monetizacao/">Palavras de Monetização</a> você pode contextualizar manualmente os produtos. Para usar widgets é neecessário um tema compatível.
Version: 3.0
Author: Bernardo Bauer
Author URI: http://www.bernabauer.com/
*/

/*  Copyright 2008  Bernardo Bauer  (email : bernabauer@bernabauer.com)

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

$vs_version = "3.0";
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
	add_filter('the_content', 'vs_auto',99);
} 

/***************************************************************************************************
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
			'orderby'=>			'precoD',
			'remover'=>			'nao',
			'wid_title'=>		'Ofertas Submarino',
			'wid_orderby'=>		'precoD',
			'wid_show'=>		'3',
			'wid_fontcolor'=>	'#000000',
			'wid_bgcolor'=>		'#FFFFFF',
			'wid_brdcolor'=>	'#DDDDDD',
			'wid_prcolor'=>		'#3982C6',
			'wid_procolor'=>	'#3982C6',
			'wid_prccolor'=>	'#3982C6',
			'wid_word'=>		'Celular',
			'wid_valores'=>		'Preco',
			'wid_altcode'=>		'BVD',
			'wid_track'=>		'nao',
			'ctx_orderby'=>		'precoD',
			'ctx_fontcolor'=>	'#000000',
			'ctx_bgcolor'=>		'#FFFFFF',
			'ctx_brdcolor'=>	'#DDDDDD',
			'ctx_prcolor'=>		'#3982C6',
			'ctx_procolor'=>	'#3982C6',
			'ctx_prccolor'=>	'#3982C6',
			'ctx_valores'=>		'Preco',
			'ctx_word'=>		'Notebook',
			'ctx_tipo'=>		'horizontal',
			'ctx_local'=>		'depois',
			'ctx_show'=>		'4',
			'ctx_exib_auto'=>	'auto',
			'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
			'ctx_track'=>		'nao',
			'ctx_altcode'=>		'ctx_FBD',
		);
		add_option('vs_options', $vs_options);
	} else {
		$vs_options['version'] = $vs_version;
		update_option('vs_options', $vs_options);

	}
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

	$vitrine_temp = vs_core($vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor'], $vs_options['ctx_prcolor'], $vs_options['ctx_procolor'], $vs_options['ctx_prccolor']);

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
	

	if ((is_single()) AND ($vs_options["ctx_exib_auto"] == 'auto')) {

		$vitrine = vs_core ( $vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor'], $vs_options['ctx_prcolor'], $vs_options['ctx_procolor'], $vs_options['ctx_prccolor']) ;

		if ($vs_options["ctx_local"] == 'antes') {
		   $text = $vitrine.$text;
		} elseif ($vs_options["ctx_local"]=='depois') {
			$text .= $vitrine;
		}

	}	

return $text;
	
}


/**
 * xml2array() will convert the given XML text to an array in the XML structure.
 * Link: http://www.bin-co.com/php/scripts/xml2array/
 * Arguments : $contents - The XML text
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 * Return: The parsed XML in an array form.
 */
function xml2array($contents, $get_attributes=1) {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }
    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create();
    xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
    xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
    xml_parse_into_struct( $parser, $contents, $xml_values );
    xml_parser_free( $parser );

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array;

    //Go through the tags.
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = '';
        if($get_attributes) {//The second argument of the function decides this.
            $result = array();
            if(isset($value)) $result['value'] = $value;

            //Set the attributes too.
            if(isset($attributes)) {
                foreach($attributes as $attr => $val) {
                    if($get_attributes == 1) $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                    /**  :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */
                }
            }
        } elseif(isset($value)) {
            $result = $value;
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;

            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                $current = &$current[$tag];

            } else { //There was another element with the same tag name
                if(isset($current[$tag][0])) {
                    array_push($current[$tag], $result);
                } else {
                    $current[$tag] = array($current[$tag],$result);
                }
                $last = count($current[$tag]) - 1;
                $current = &$current[$tag][$last];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;

            } else { //If taken, put all things inside a list(array)
                if((is_array($current[$tag]) and $get_attributes == 0)//If it is already an array...
                        or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
                    array_push($current[$tag],$result); // ...push the new element into that array.
                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }

    return($xml_array);
} 



/***************************************************************************************************
 *  Funcao principal
 */
function vs_core ($show, $word, $vitrine, $fundo, $borda, $desc, $corprec, $corparc, $corprom) {
	global $wpdb;

	error_reporting( 0 );

	global $vs_options;
	global $vs_version;
	
	if ($vs_options['codafil'] == '')
		return "ERRO: Código de Afiliado não informado.";

	if ($vs_options['version'] != $vs_version)
		return "ERRO: Atualização necessária.";

	$idsubmarino = $vs_options['codafil'];			// Define codigo de afiliado para o script funcionar

	// ROV (http://mesquita.blog.br/) - Aqui começa a randomização...
	$larrWords = explode(",",$word);
	$word = $larrWords[rand(0, count($larrWords)-1)];
	// ROV - Aqui termina ....

	$palavrapadrao = $word; // Define a palavra chave para o script funcionar
	
	if ( !$palavrabuscada ) { 
		$palavrabuscada = $palavrapadrao; 
	}
	$palavrabuscada = vs_latin2html($palavrabuscada);

	if ($vitrine != "widget") {
		if ($vs_options['ctx_tipo'] == "horizontal") { 
			$lista_de_produtos = "<table id=\"vs_ctx_tabela_produtos\"><tr>";
		}
		$sort = $vs_options['ctx_orderby'];
		$valores = $vs_options['ctx_valores'];
		$cats = $vs_options['ctx_cats'];
		
	} else {
		$sort = $vs_options['wid_orderby'];
		$valores = $vs_options['wid_valores'];
		$cats = $vs_options['wid_cats'];
	}

	$request = "f=".$vs_options['codafil']."&q=".$palavrabuscada."&s=".$sort."&k=".$vs_options['chave']."&e=".get_option('admin_email')."&u=".get_option('siteurl')."&c=".$cats;
	
	$response = vs_http_post($request, "wp.bernabauer.com", "/vs/index.php");

	$data = ltrim($response[1]);

	$produtos = xml2array($data);

	if (is_string($produtos["vitrine_submarino"]["oferta"]["produto"]["value"]))
		$produtos2 = $produtos["vitrine_submarino"];
	else
		$produtos2 = $produtos["vitrine_submarino"]["oferta"];
	
	$i=1;
	foreach ($produtos2 as $produto) {

		$nome = $produto["produto"]["value"];
		$promo = str_replace(".", ",", $produto["promo"]["value"]);
		if (strlen($promo) - strpos($promo, ",") == 2 AND (strpos($promo, ",") > 0))
			$promo = $promo."0";
		$preco = str_replace(".", ",", $produto["preco"]["value"]);
		if (strlen($preco) - strpos($preco, ",") == 2 AND (strpos($preco, ",") > 0))
			$preco = $preco."0";

		$preco_parc = $produto["parcela"]["value"];
		$img = $produto["imagem"]["value"];
		$link_prod = $produto["link"]["value"];
	
		$palavras = explode('_',textoparalink ($nome));
	
		$tc = '';
	
		$texto = $nome;
		$texto = str_replace( " com ", " ", $texto );
		$texto = str_replace( " de ", " ", $texto );
		$texto = str_replace( " do ", " ", $texto );
		$texto = str_replace( " da ", " ", $texto );
		$texto = str_replace( " para ", " ", $texto );
		$texto = str_replace( " por ", " ", $texto );
		$pal = explode(" ", $texto);
		$busca = $pal[0]." ".$pal[1]." ".$pal[2]." ".$pal[3];
	
		$tccp = 'onclick="javascript: pageTracker._trackPageview (\'/out/sub/compare/uol/'.$nome.'/\');"';
	
		//código de tracking do Google Analytics
		$tc = 'onclick="javascript: pageTracker._trackPageview (\'/out/sub/'.$vitrine.'/'.$nome.'\');"';
	
		if (($vs_options['ctx_track'] == "nao") AND $vitrine == "contextual") {
			$tc = '';
		}
		if (($vs_options['wid_track'] == "nao") AND $vitrine == "widget")  {
			$tc = '';
		}
		
		//FIM do código de tracking
	
		switch ($vs_options['PCP']) {
			case "BP":
				$compare_precos = "<a href=\"http://busca.buscape.com.br/cprocura?lkout=1&amp;site_origem=".$cod_BP."&produto=".urlencode(utf8_decode($busca))."\" ".$tccp." target='_blank'>".$vs_options['LCP']."</a>"; 
				break;
			case "ML":
				$compare_precos = "<a href=\"http://pmstrk.mercadolivre.com.br/jm/PmsTrk?tool=".$cod_ML."&amp;go=http://lista.mercadolivre.com.br/".urlencode($busca)."\"  ".$tccp." target='_blank'>".$vs_options['LCP']."</a>"; 
				break;
			case "JC":
				$compare_precos = "<a href=\"http://www.jacotei.com.br/mod.php?module=jacotei.pesquisa&amp;texto=".urlencode($busca)."&amp;precomin=&amp;precomax=&amp;af=".$cod_JC."\" ".$tccp." target='_blank'>".$vs_options['LCP']."</a>";  
				break;
			case "NS":
				$compare_precos = ''; 
				break;
			case "BB":
				$compare_precos = "<a href=\"http://bernabauer.shopping.busca.uol.com.br/busca.html?q=".urlencode(utf8_decode($busca))."\" "	.$tccp." target='_blank'>".$vs_options['LCP']."</a>"; 
				break;
		}
		
		//ajuste quando não há produtos disponíveis
		switch ($valores) {
			case "Preco":
				if ($preco AND $promo != 0)
					$preco = $promo;			
				$preco = "<div style=\"font-weight: bold;color:".$corprec.";\">R$ ".utf8_encode($preco)."</div>";
				$promo='';
				$parce='';
				break;
			case "Promo":
				if ($promo == 0)
					$promo = $preco;
				$promo = "<div style=\"font-weight: bold;color:".$corprom.";\">R$ ".utf8_encode($promo)."</div>";
				$preco='';
				$parce='';
				break;
			case "Parce":
				if ($preco_parc)
					$parce = "<div style=\"color:".$corparc.";\"><small>".str_replace("X","x R\$", $preco_parc)."</small></div>";
				else {
					if ($preco AND $promo != 0)
						$preco = $promo;			
					$preco = "<div style=\"font-weight: bold;color:".$corprom.";\">R$ ".utf8_encode($preco)."</div>";
					$parc = '';
				}
				$preco='';
				$promo='';
				break;
			case "Preco,Promo":
				if ($preco AND $promo != 0)
					$preco = "<div style=\"font-weight: bold;color:gray;\"><small>De <strike>R$ ".utf8_encode($preco)."</strike></small><div style=\"font-weight: bold;color:".$corprom.";\">Por R$ ".utf8_encode($promo)."</div></div>";
				else {
					$preco = "<div style=\"font-weight: bold;color:".$corprec.";\">R$ ".utf8_encode($preco)."</div>";
					$promo='';
				}
				$parce='';
				break;
			case "Preco,Parce":
				if ($preco AND $preco_parc) {
					if ($preco AND $promo != 0)
						$preco = $promo;			
					$preco = "<div style=\"font-weight: bold;color:gray;\"><small>R$ ".utf8_encode($preco)."</small><div style=\"font-weight: bold;color:".$corprom.";\">Ou ".str_replace("X","x R\$", $preco_parc)."</div></div>";
				} else {
					$preco = "<div style=\"font-weight: bold;color:".$corprec.";\">R$ ".utf8_encode($preco)."</div>";
					$parce='';
				}
				$promo='';
				break;
			case "Preco,Promo,Parce":
				if ($preco AND $promo != 0) {
					$preco = "<div style=\"font-weight: bold;color:gray;\"><small>De <strike>R$ ".utf8_encode($preco)."</strike></small><div style=\"font-weight: bold;color:".$corprom.";\">Por R$ ".utf8_encode($promo)."</div></div>";
					$promo = '';
				} else {
					$preco = "<div style=\"font-weight: bold;color:".$corprec.";\">R$ ".utf8_encode($preco)."</div>";
					$promo = '';
				}
				if ($preco_parc)
					$parce = "<div style=\"color:".$corparc.";\"><small>".str_replace("X","x R\$", $preco_parc)."</small></div>";
				else {
					$parce='';
				}
				break;
			case "Promo,Parce":
				if ($preco AND $preco_parc)
					$preco = "<div style=\"font-weight: bold;color:gray;\"><small>R$ ".utf8_encode($preco)."</small><div style=\"font-weight: bold;color:".$corparc.";\">Ou ".str_replace("X","x R\$", $preco_parc)."</div></div>";
				else {
					$preco = "<div style=\"font-weight: bold;color:".$corprec.";\">R$ ".utf8_encode($preco)."</div>";
					$parce='';
				}
				$promo='';		
				break;
			case "":
				$preco='';
				$promo='';
				$parce='';
				break;
				
		}

	
			$preco_show = $preco.$promo.$parce;

		
		switch ($vitrine) {
	
			case "contextual":
				// vitrine contextual
				if ($vs_options['ctx_tipo'] == "horizontal") {
					$td = 100 / $vs_options['ctx_show'];

					//mostra vitrine com produtos em uma unica linha (VITRINE HORIZONTAL)
					$lista_de_produtos .= '<td width="'. $td.'%'.'" style="background-color:'.$fundo.';text-align:center;padding:3px;font-size:11px;border:0px;"><a href="'.$link_prod.'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$img.'" alt="'.$nome.'" /></a><div style="font-size:12px;line-height:120%;color:'.$desc.';height:45px;overflow: hidden;">'.$nome.'</div><div><div style="font-weight: bold;font-size:12px;line-height:120%;"><a href="'.$link_prod.'" rel="nofollow"target="_blank"'.$tc.'>'.$vs_options["LP"].'</a></div><div>'.$preco_show.'</div><div>'.$compare_precos.'</div></div></td>';
				} else {
					
					//mostra vitrine com um produto por linha (VITRINE VERTICAL)
					$lista_de_produtos .= '<div style="color:'.$desc.';border:2px solid '.$borda.';height:104px;"><div style="background-color:'.$fundo.';padding:3px;"><table><tr><td style="border:0px;" ><a href="'.$link_prod.'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$img.'" alt="'.$nome.'" /></a></td><td style="border:0px;" >'.$nome.'<br /><b>'.$preco_show.'</b><br /><br /><a href="'.$link_prod.'" rel="nofollow" target="_blank"'.$tc.'>'.$vs_options["LP"].'</a><div>'.$compare_precos.'</div></td></tr></table></div></div>';
				}
	
				break;
	
			case "widget":
				$lista_de_produtos .= '<div style="color:'.$desc.';background-color:'.$fundo.';padding:3px;"><center><p><a href="'.$link_prod.'" rel="nofollow" target="_blank" '.$tc.'><img src="'.$img.'" alt="'.$nome.'" /></a></p>'.$nome.'<br /><a href="'.$link_prod.'" rel="nofollow" target="_blank"'.$tc.'><strong>'.$vs_options["LP"].'</strong></a><br />'.$preco_show.'<div>'.$compare_precos.'</div></center></div>';
				break;
	
		} //switch
	if ($i == $show) 
		break;
	else
		$i++;
	} //foreach	


	if (array_key_exists("oferta", $produtos['vitrine_submarino']) == FALSE) {
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
				case "BVD":
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
			} //if
		} //if
		return "<center>".$altcode."</center>";
	} else {
	
	$credits = "<div style=\"text-align:right;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>Vitrine Submarino ".$vs_options['version']."</a></small></div>";

	if (($vitrine == "contextual") AND  ($vs_options['ctx_tipo'] == "horizontal")) { 
		$lista_de_produtos .= "</tr></table>";
	}

	if (($vitrine == "contextual") AND ($vs_options['ctx_exib_auto'] == 'auto'))
		$titulo = $vs_options['ctx_titulo'];
	else
		$titulo = '';

	return $titulo."<div style=\"border:2px solid ".$borda.";background-color:".$fundo.";\">".$lista_de_produtos."</div>".$credits;
	}
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
        if (isset($_POST['chave'])) 
           $vs_options['chave'] = $_POST['chave'];
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

		if (isset($_POST['wid_procolor'])) 
			$vs_options['wid_procolor'] = strip_tags(stripslashes($_POST['wid_procolor']));

		if (isset($_POST['wid_prccolor'])) 
			$vs_options['wid_prccolor'] = strip_tags(stripslashes($_POST['wid_prccolor']));

		if (isset($_POST['wid_valores'])) 
			$vs_options['wid_valores'] = stripslashes(implode(",", $_POST['wid_valores']));
		else
			$vs_options['wid_valores'] = '';

		if (isset($_POST['wid_cats'])) 
			$vs_options['wid_cats'] = stripslashes(implode(",", $_POST['wid_cats']));
		else
			$vs_options['wid_cats'] = '';
		

		if (isset($_POST['wid_word'])) 
			$vs_options['wid_word'] = strip_tags(stripslashes($_POST['wid_word']));

		$vs_options['wid_altcode'] = strip_tags(stripslashes($_POST['wid_altcode']));
		$vs_options['wid_track'] = strip_tags(stripslashes($_POST['wid_track']));

		// Opções CONTEXTUAL
		$vs_options['ctx_orderby'] = strip_tags(stripslashes($_POST['ctx_orderby']));
		if (isset($_POST['ctx_fontcolor'])) 
			$vs_options['ctx_fontcolor'] = strip_tags(stripslashes($_POST['ctx_fontcolor']));

		if (isset($_POST['ctx_bgcolor'])) 
			$vs_options['ctx_bgcolor'] = strip_tags(stripslashes($_POST['ctx_bgcolor']));

		if (isset($_POST['ctx_brdcolor'])) 
			$vs_options['ctx_brdcolor'] = strip_tags(stripslashes($_POST['ctx_brdcolor']));

		if (isset($_POST['ctx_prcolor'])) 
			$vs_options['ctx_prcolor'] = strip_tags(stripslashes($_POST['ctx_prcolor']));

		if (isset($_POST['ctx_procolor'])) 
			$vs_options['ctx_procolor'] = strip_tags(stripslashes($_POST['ctx_procolor']));

		if (isset($_POST['ctx_prccolor'])) 
			$vs_options['ctx_prccolor'] = strip_tags(stripslashes($_POST['ctx_prccolor']));

		if (isset($_POST['ctx_valores'])) 
			$vs_options['ctx_valores'] = stripslashes(implode(",", $_POST['ctx_valores']));
		else
			$vs_options['ctx_valores'] = '';
		
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

	$cat_arr = array('Bebês', 'Beleza e Saúde', 'Brinquedos', 'Cama, Mesa e Banho', 'CD', 'Cine & Foto', 'DVD', 'Eletrodomésticos', 'Eletrônicos', 'Eletroportáteis', 'Esporte e Lazer', 'Ferramentas', 'Games', 'Informática', 'Instrumentos Musicais', 'Jóias e Relógios', 'Moda', 'Papelaria', 'Perfumaria', 'Telefonia', 'Utilidades Domésticas', 'Vinhos e Cia');
    
    if ( stripos($vs_options['ctx_valores'], "Preco") !== FALSE) 
		$ctx_showpreco = 'checked';

    if ( stripos($vs_options['ctx_valores'], "Promo") !== FALSE) 
		$ctx_showpromo = 'checked';

    if ( stripos($vs_options['ctx_valores'], "Parce") !== FALSE) 
		$ctx_showparce = 'checked';


    if ( stripos($vs_options['wid_valores'], "Preco") !== FALSE) 
		$wid_showpreco = 'checked';

    if ( stripos($vs_options['wid_valores'], "Promo") !== FALSE) 
		$wid_showpromo = 'checked';

    if ( stripos($vs_options['wid_valores'], "Parce") !== FALSE) 
		$wid_showparce = 'checked';


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

    if ( $vs_options['ctx_tipo'] == 'horizontal') {
		$horizontal = 'checked=\"checked\"';
    } else {
    	$vertical = 'checked=\"checked\"';
    }

    if ( $vs_options['remover'] == 'nao') {
		$remover_nao = 'checked=\"checked\"';
    } else {
    	$remover_sim = 'checked=\"checked\"';
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
		case "precoA":
			$wid_precoA = 'checked=\"checked\"';
			break;
		case "precoD":
			$wid_precoD = 'checked=\"checked\"';
			break;
		case "produtoA":
			$wid_alfaA = 'checked=\"checked\"';
			break;
		case "produtoZ":
			$wid_alfaZ = 'checked=\"checked\"';
			break;
		default:
			$wid_precoD = 'checked=\"checked\"';
			break;
	}		


	switch ($vs_options['ctx_orderby']) {
		case "precoA":
			$ctx_precoA = 'checked=\"checked\"';
			break;
		case "precoD":
			$ctx_precoD = 'checked=\"checked\"';
			break;
		case "produtoA":
			$ctx_alfaA = 'checked=\"checked\"';
			break;
		case "produtoZ":
			$ctx_alfaZ = 'checked=\"checked\"';
			break;
		default:
			$ctx_preco = 'checked=\"checked\"';
			break;
	}		
        //pede a chave
        if (isset($_POST['genkey'])) {
        		$dados  = "genkey=".get_option('admin_email')."#".get_option('siteurl');
        		$dados .= "&f=".$vs_options['codafil'];
        		$dados .= "&i=".$_SERVER['SERVER_ADDR'];
        		$dados .= "&h=".$_SERVER['HTTP_HOST'];
				vs_http_post($dados, "wp.bernabauer.com", "/vs/index.php");
				?>
				<div id="message" class="updated fade"><p><strong>ALERTA Vitrine Submarino</strong>: A chave foi solicitada, por favor, verifique sua caixa de entrada e a caixa de SPAM dentro dos próximos minutos.</p></div>
				<?php
		}

	
?>

	<div class=wrap>

<p>Você pode buscar ajuda para este plugin no <a href="http://forum.bernabauer.com">fórum</a> do <a href="http://www.bernabauer.com">bernabauer.com</a>.</p>

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
		<th scope="row" valign="top">Chave</th>
		<td>
		
			<input type="textbox" name="chave" size="40" value="<?php echo $vs_options['chave']; ?>">
		
			<br />Sem a chave a vitrine não funciona.<br />
			<input type="checkbox" id="genkey" name="genkey" value="gerar" <?php if ($vs_options['chave'] != '') echo "DISABLED"; ?>> <label for="genkey">Gerar chave</label>
		
			<br />Você precisa gerar uma chave para mostrar produtos do submarino no seu blog. <strong>ATENÇÃO!</strong> Gerar uma chave para o seu blog quer dizer que você autoriza que o código do autor do plugin será usado em algumas exibições. Isto ajudará a pagar os custos de manter o plugin funcionando. Para gerar a chave habilite a opção e clique em atualizar. Você receberá mais informações através do email <strong><?php echo get_option('admin_email');?></strong>. Para gerar a chave serão enviados apenas o seu endereço de email, endereço e o nome do blog . Assim que você receber a chave, informe na caixa de texto acima.
			
		</td>
	 </tr>
	</table>
	<br />

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
			<input type="radio" id="PCP_BP" name="PCP" value="BP" <?php echo $PCP_BP; ?> /><label for="PCP_BP"> BuscaPé</label>
			<br />
			<input type="radio" id="PCP_ML" name="PCP" value="ML" <?php echo $PCP_ML; ?> /> <label for="PCP_ML">Mercado Livre</label>
			<br />
			<input type="radio" id="PCP_JC" name="PCP" value="JC" <?php echo $PCP_JC; ?> /> <label for="PCP_JC">Jacotei</label>
			<br />
			<input type="radio" id="PCP_NS" name="PCP" value="NS" <?php echo $PCP_NS; ?> /> <label for="PCP_NS">Não mostrar nada</label>
			<br />
			<input type="radio" id="PCP_BB" name="PCP" value="BB" <?php echo $PCP_BB; ?> /> <label for="PCP_BB">Shopping bernabauer.com</label>
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
		<th scope="row" valign="top">Cores</th>
		<td>
  				Você pode digitar "red", "blue", "green" de acordo com a correspondencia de cores de HTML. Lista completa <a href="http://www.w3schools.com/Html/html_colornames.asp" target="_blank">aqui</a>.<br /><br />
  				<table><td style="border-width:2px;"><strong>Texto:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="ctx_fontcolor" name="ctx_fontcolor" type="text" value="<?php echo $vs_options['ctx_fontcolor']; ?>" /><label for="ctx_fontcolor"> Cor do texto de descrição dos produtos. A melhor cor é preta (#000000 ou BLACK). </label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Fundo:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="ctx_bgcolor" name="ctx_bgcolor" type="text" value="<?php echo $vs_options['ctx_bgcolor']; ?>" /><label for="ctx_bgcolor"> Cor de fundo dos produtos. A melhor cor é branca (#FFFFFF).</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Borda:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="ctx_brdcolor" name="ctx_brdcolor" type="text" value="<?php echo $vs_options['ctx_brdcolor']; ?>" /><label for="ctx_brdcolor"> Cor da borda da vitrine. A melhor cor é cinza (#DDDDDD). </label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Preço:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="ctx_prcolor" name="ctx_prcolor" type="text" value="<?php echo $vs_options['ctx_prcolor']; ?>" /><label for="ctx_prcolor"> Cor do preço dos produtos.</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Promoção:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="ctx_procolor" name="ctx_procolor" type="text" value="<?php echo $vs_options['ctx_procolor']; ?>" /><label for="ctx_procolor"> Cor do valor promocional.</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Parcela:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="ctx_prccolor" name="ctx_prccolor" type="text" value="<?php echo $vs_options['ctx_prccolor']; ?>" /><label for="ctx_prccolor"> Cor do valor da parcela. </label></td></tr>
  				</table>
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Produto Padrão</th>
		<td>
 				<input style="width: 30%;" id="ctx_word" name="ctx_word" type="text" value="<?php echo $vs_options['ctx_word']; ?>" /><br />
 				Informe a palavra para popular a vitrine. Evite utilização de acentos. Você pode definir multiplas palavras, basta separar por vírgulas. A escolha da palavra para exibir produtos na vitrine é aleatória.<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Valores</th>
		<td>
				<input type="checkbox" id="Valpre" name="ctx_valores[]" value="Preco" <?php echo $ctx_showpreco; ?>> <label for="Valpre">Preço</label><br />
				<input type="checkbox" id="Valpro" name="ctx_valores[]" value="Promo" <?php echo $ctx_showpromo; ?>> <label for="Valpro">Promoção</label><br />
				<input type="checkbox" id="Valpar" name="ctx_valores[]" value="Parce" <?php echo $ctx_showparce; ?>> <label for="Valpar">Parcela</label><br />

				<label for="Submarino-show">Informe quais valores deverão ser mostrados na vitrine.</label><br />
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
			 O código deve ser inserido dentro do <a href="http://codex.wordpress.org/The_Loop" target="_blank">Loop</a>.
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Tipo de Vitrine</th>
		<td>
			<input type="radio" id="vithor" name="ctx_tipo" value="horizontal" <?php echo $horizontal; ?> /> <label for="vithor">Horizontal (produtos em uma única linha)</label>
			<br />
			<input type="radio" if="vitver" name="ctx_tipo" value="vertical" <?php echo $vertical; ?> /> <label for="vitver">Vertical (um produto por linha)</label>
			<br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Ordem dos Produtos</th>
		<td>
			<input type="radio" name="ctx_orderby" id="precoD" value="precoD" <?php echo $ctx_precoD; ?> /> <label for="precoD">Maior Preço</label>
			<br />
			<input type="radio" name="ctx_orderby" id="precoA" value="precoA" <?php echo $ctx_precoA; ?> /> <label for="precoA">Menor Preço</label>
			<br />
			<input type="radio" name="ctx_orderby" id="produtoA" value="produtoA" <?php echo $ctx_alfaA; ?> /> <label for="produtoA">Alfabética (A-Z)</label>
			<br />
			<input type="radio" name="ctx_orderby" id="produtoZ" value="produtoZ" <?php echo $ctx_alfaZ; ?> /> <label for="produtoZ">Alfabética (Z-A)</label>
			<br />
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
			Código HTML para ser mostrado caso não sejam encontrados produtos com a palavra acima.
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
		<th scope="row" valign="top">Cores</th>
		<td>
  				Você pode digitar "red", "blue", "green" de acordo com a correspondencia de cores de HTML. Lista completa <a href="http://www.w3schools.com/Html/html_colornames.asp" target="_blank">aqui</a>.<br /><br />
  				<table><td style="border-width:2px;"><strong>Texto:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_fontcolor" name="wid_fontcolor" type="text" value="<?php echo $vs_options['wid_fontcolor']; ?>" /><label for="wid_fontcolor"> Cor do texto de descrição dos produtos. A melhor cor é preta (#000000 ou BLACK). </label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Fundo:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_bgcolor" name="wid_bgcolor" type="text" value="<?php echo $vs_options['wid_bgcolor']; ?>" /><label for="wid_bgcolor"> Cor de fundo dos produtos. A melhor cor é branca (#FFFFFF).</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Borda:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_brdcolor" name="wid_brdcolor" type="text" value="<?php echo $vs_options['wid_brdcolor']; ?>" /><label for="wid_brdcolor"> Cor da borda da vitrine. A melhor cor é cinza (#DDDDDD). </label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Preço:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_prcolor" name="wid_prcolor" type="text" value="<?php echo $vs_options['wid_prcolor']; ?>" /><label for="wid_prcolor"> Cor do preço dos produtos.</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Promoção:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_procolor" name="wid_procolor" type="text" value="<?php echo $vs_options['wid_procolor']; ?>" /><label for="wid_procolor"> Cor do valor promocional.</label></td></tr>
  				<tr><td style="border-width:2px;"><strong>Parcela:</strong> </td><td style="border-width:2px;"><input style="width: 60px;" id="wid_prccolor" name="wid_prccolor" type="text" value="<?php echo $vs_options['wid_prccolor']; ?>" /><label for="wid_prccolor"> Cor do valor da parcela. </label></td></tr>
  				</table>
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Produto Padrão</th>
		<td>
 				<input style="width: 30%;" id="wid_word" name="wid_word" type="text" value="<?php echo $vs_options['wid_word']; ?>" /><br />
 				<label for="wid_word">Informe a palavra para popular a vitrine. Evite utilização de acentos. Você pode definir multiplas palavras, basta separar por vírgulas. A escolha da palavra para exibir produtos na vitrine é aleatória.</label><br /><br />

				<strong>Selecione as categorias relevantes às palavras informadas na caixa de texto acima. A busca será restrita as categorias aqui informadas. Se você não escolher categorias, todas as categorias serão utilizadas, ou seja, escolher todas ou nenhuma dá na mesma. ;-)</strong>
  				<table>
  				<tr>
  				
  				<?php 

  				for($i=1;$i<23;$i++) {
  					$cats = explode(",", $vs_options['wid_cats']);
  					foreach ($cats as $cat) {
  					    if ( stripos($cat, $cat_arr[$i-1]) !== FALSE) {
							$wid_cat_checked = 'checked';
							break;
						} else
							$wid_cat_checked = '';
					}							
					echo '<td style="border-width:2px;"><input type="checkbox" id="wid_cat'.$i.'" name="wid_cats[]" value="'.$cat_arr[$i-1].'" '.$wid_cat_checked.'> <label for="wid_cat'.$i.'">'.$cat_arr[$i-1].'</label></td>';
					if ($i % 5 == 0) {
						echo "</tr><tr>";
					}
				}
				?>
				</tr>
  				</table>



		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Valores</th>
		<td>
				<input type="checkbox" id="Valpre" name="wid_valores[]" value="Preco" <?php echo $wid_showpreco; ?>> <label for="Valpre">Preço</label><br />
				<input type="checkbox" id="Valpro" name="wid_valores[]" value="Promo" <?php echo $wid_showpromo; ?>> <label for="Valpro">Promoção</label><br />
				<input type="checkbox" id="Valpar" name="wid_valores[]" value="Parce" <?php echo $wid_showparce; ?>> <label for="Valpar">Parcela</label><br />

				<label for="Submarino-show">Informe quais valores deverão ser mostrados na vitrine.</label><br />
		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Ordem dos Produtos</th>
		<td>
			<input type="radio" name="wid_orderby" id="wprecod" value="precoD" <?php echo $wid_precoD; ?> /> <label for="wprecod">Maior Preço</label>
			<br />
			<input type="radio" name="wid_orderby" id ="wprecoa" value="precoA" <?php echo $wid_precoA; ?> /> <label for="wprecoa">Menor Preço</label>
			<br />
			<input type="radio" name="wid_orderby" id="wprodutoa" value="produtoA" <?php echo $wid_alfaA; ?> /> <label for="wprodutoa">Alfabética (A-Z)</label>
			<br />
			<input type="radio" name="wid_orderby" id="wprodutoz" value="produtoZ" <?php echo $wid_alfaZ; ?> /> <label for="wprodutoz">Alfabética (Z-A)</label>
			<br />
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
			Código HTML para ser mostrado caso não sejam encontrados produtos com a palavra acima.
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

/***********************************************************************/
// Returns array with headers in $response[0] and body in $response[1]

function vs_http_post($request, $host, $path, $port = 80) {

	global $wp_version;

	$http_request  = "POST $path HTTP/1.0\r\n";
	$http_request .= "Host: $host\r\n";
	$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
	$http_request .= "Content-Length: " . strlen($request) . "\r\n";
    $http_request .= "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) Gecko/20021204\r\n";
    $http_request .= "Referer: ".get_option('siteurl')."\r\n";
	$http_request .= "\r\n";
	$http_request .= $request;

	$response = '';
	if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
		fwrite($fs, $http_request);

		while ( !feof($fs) )
			$response .= fgets($fs, 1160); // One TCP-IP packet
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);
	}
	return $response;
}

?>
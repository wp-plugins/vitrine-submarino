<?php
/*
Plugin Name: Vitrine Submarino
Plugin URI: http://www.bernabauer.com/wp-plugins/
Description: Mostre vitrines de produtos do Submarino em seu blog. Com o <a href="http://wordpress.org/extend/plugins/palavras-de-monetizacao/">Palavras de Monetização</a> você pode contextualizar manualmente os produtos. Para usar widgets é neecessário um tema compatível.
Version: 3.1
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

$vs_version = "3.1";
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

add_action('vs_cron', 'vs_pegaprodutos_rss' );

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
			'remover'=>			'nao',
			'wid_show'=>		'3',
			'wid_fontcolor'=>	'#000000',
			'wid_bgcolor'=>		'#FFFFFF',
			'wid_brdcolor'=>	'#DDDDDD',
			'wid_prcolor'=>		'#3982C6',
			'wid_title'=>		'Ofertas Submarino',
			'wid_track'=>		'nao',
			'wid_rss_source'=>	'lan_Geral',
			'wid_altcode'=>		'BVD',
			'ctx_prcolor'=>		'#3982C6',
			'ctx_tipo'=>		'horizontal',
			'ctx_local'=>		'depois',
			'ctx_show'=>		'4',
			'ctx_exib_auto'=>	'auto',
			'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
			'ctx_track'=>		'nao',
			'ctx_rss_source'=>	'lan_Geral',
			'ctx_altcode'=>		'ctx_FBD',
		);
		add_option('vs_options', $vs_options);
		
		$sql = 'CREATE TABLE wp_vitrinesubmarino (
				nomep varchar(255) NOT NULL,
				linkp varchar(255) NOT NULL,
				imagemp varchar(255) NOT NULL,
				precop varchar(10) NOT NULL,
				rss_source varchar(15) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
	} else {
		if ($vs_options['version'] != $vs_version) {
			delete_option('vs_options');
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
			'wid_show'=>		'3',
			'wid_fontcolor'=>	'#000000',
			'wid_bgcolor'=>		'#FFFFFF',
			'wid_brdcolor'=>	'#DDDDDD',
			'wid_prcolor'=>		'#3982C6',
			'wid_title'=>		'Ofertas Submarino',
			'wid_track'=>		'nao',
			'wid_rss_source'=>	'lan_Geral',
			'wid_altcode'=>		'BVD',
			'ctx_prcolor'=>		'#3982C6',
			'ctx_tipo'=>		'horizontal',
			'ctx_local'=>		'depois',
			'ctx_show'=>		'4',
			'ctx_exib_auto'=>	'auto',
			'ctx_titulo'=>		'<h3>Ofertas Submarino</h3>',
			'ctx_track'=>		'nao',
			'ctx_rss_source'=>	'lan_Geral',
			'ctx_altcode'=>		'ctx_FBD',
		);
		add_option('vs_options', $vs_options);
		
		$sql = 'CREATE TABLE wp_vitrinesubmarino (
				nomep varchar(255) NOT NULL,
				linkp varchar(255) NOT NULL,
				imagemp varchar(255) NOT NULL,
				precop varchar(10) NOT NULL,
				rss_source varchar(15) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		}
#		$vs_options['version'] = $vs_version;
#		update_option('vs_options', $vs_options);
	}

	if (!wp_next_scheduled('vs_cron')) {
		wp_schedule_event( time(), 'daily', 'vs_cron' );
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

		$vitrine = vs_core ( $vs_options["ctx_show"], $word, "contextual", $vs_options['ctx_bgcolor'], $vs_options['ctx_brdcolor'], $vs_options['ctx_fontcolor'], $vs_options['ctx_prcolor']) ;

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
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
 */
function vs_xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();
        
        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;
                    
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    
                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                        
                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }
    
    return($xml_array);
}  
/***************************************************************************************************
 *  http://www.sourcerally.net/Scripts/39-Convert-HTML-Entities-to-XML-Entities
 */

function vs_xmlEntities($str)
{
    $xml = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;');
    $html = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
    $str = str_replace($html,$xml,$str);
    $str = str_ireplace($html,$xml,$str);
    return $str;
} 


/***************************************************************************************************
 *  Funcao principal
 */
function vs_pegaprodutos_rss($rss_source) {

	global $wpdb;
	global $vs_options;

	switch ($rss_source) {
	
		case "mv_Bebes":
			$rss_link = "/rss/rss-mais-vendidos?rss=30";
			break;
		case "mv_Beleza":
			$rss_link = "/rss/rss-mais-vendidos?rss=24";
			break;
		case "mv_Brinquedos":
			$rss_link = "/rss/rss-mais-vendidos?rss=3";
			break;
		case "mv_Cama":
			$rss_link = "/rss/rss-mais-vendidos?rss=36";
			break;
		case "mv_Cameras":
			$rss_link = "/rss/rss-mais-vendidos?rss=26";
			break;
		case "mv_Celulares":
			$rss_link = "/rss/rss-mais-vendidos?rss=11";
			break;
		case "mv_CDs":
			$rss_link = "/rss/rss-mais-vendidos?rss=2";
			break;
		case "mv_DVD":
			$rss_link = "/rss/rss-mais-vendidos?rss=6";
			break;
		case "mv_Esporte":
			$rss_link = "/rss/rss-mais-vendidos?rss=28";
			break;
		case "mv_Eletrodomesticos":
			$rss_link = "/rss/rss-mais-vendidos?rss=27";
			break;
		case "mv_Eletronicos":
			$rss_link = "/rss/rss-mais-vendidos?rss=13";
			break;
		case "mv_Eletroportateis":
			$rss_link = "/rss/rss-mais-vendidos?rss=34";
			break;
		case "mv_Ferramentas":
			$rss_link = "/rss/rss-mais-vendidos?rss=15";
			break;
		case "mv_Games":
			$rss_link = "/rss/rss-mais-vendidos?rss=12";
			break;
		case "mv_Informatica":
			$rss_link = "/rss/rss-mais-vendidos?rss=10";
			break;
		case "mv_Instrumentos":
			$rss_link = "/rss/rss-mais-vendidos?rss=32";
			break;
		case "mv_Livros":
			$rss_link = "/rss/rss-mais-vendidos?rss=1";
			break;
		case "mv_livrosImportados":
			$rss_link = "/rss/rss-mais-vendidos?rss=9";
			break;
		case "mv_Papelaria":
			$rss_link = "/rss/rss-mais-vendidos?rss=37";
			break;
		case "mv_Perfumaria":
			$rss_link = "/rss/rss-mais-vendidos?rss=33";
			break;
		case "mv_Relogios":
			$rss_link = "/rss/rss-mais-vendidos?rss=25";
			break;
		case "mv_Utilidades":
			$rss_link = "/rss/rss-mais-vendidos?rss=18";
			break;
		case "mv_Vestuario":
			$rss_link = "/rss/rss-mais-vendidos?rss=31";
			break;
		case "mv_vinhos":
			$rss_link = "/rss/rss-mais-vendidos?rss=35";
			break;
		case "lan_Geral":
			$rss_link = "/rss/rss-lancamentos";
			break;
		case "lan_Bebes":
			$rss_link = "/rss/rss-lancamentos?rss=30";
			break;
		case "lan_Beleza":
			$rss_link = "/rss/rss-lancamentos?rss=24";
			break;
		case "lan_Brinquedos":
			$rss_link = "/rss/rss-lancamentos?rss=3";
			break;
		case "lan_Cama":
			$rss_link = "/rss/rss-lancamentos?rss=36";
			break;
		case "lan_Cameras":
			$rss_link = "/rss/rss-lancamentos?rss=26";
			break;
		case "lan_Celulares":
			$rss_link = "/rss/rss-lancamentos?rss=11";
			break;
		case "lan_CDs":
			$rss_link = "/rss/rss-lancamentos?rss=2";
			break;
		case "lan_DVD":
			$rss_link = "/rss/rss-lancamentos?rss=6";
			break;
		case "lan_Esporte":
			$rss_link = "/rss/rss-lancamentos?rss=28";
			break;
		case "lan_Eletrodomesticos":
			$rss_link = "/rss/rss-lancamentos?rss=27";
			break;
		case "lan_Eletronicos":
			$rss_link = "/rss/rss-lancamentos?rss=13";
			break;
		case "lan_Eletroportateis":
			$rss_link = "/rss/rss-lancamentos?rss=34";
			break;
		case "lan_Ferramentas":
			$rss_link = "/rss/rss-lancamentos?rss=15";
			break;
		case "lan_Games":
			$rss_link = "/rss/rss-lancamentos?rss=12";
			break;
		case "lan_Informatica":
			$rss_link = "/rss/rss-lancamentos?rss=10";
			break;
		case "lan_Instrumentos":
			$rss_link = "/rss/rss-lancamentos?rss=32";
			break;
		case "lan_Livros":
			$rss_link = "/rss/rss-lancamentos?rss=1";
			break;
		case "lan_livrosImportados":
			$rss_link = "/rss/rss-lancamentos?rss=9";
			break;
		case "lan_Papelaria":
			$rss_link = "/rss/rss-lancamentos?rss=37";
			break;
		case "lan_Perfumaria":
			$rss_link = "/rss/rss-lancamentos?rss=33";
			break;
		case "lan_Relogios":
			$rss_link = "/rss/rss-lancamentos?rss=25";
			break;
		case "lan_Utilidades":
			$rss_link = "/rss/rss-lancamentos?rss=18";
			break;
		case "lan_Vestuario":
			$rss_link = "/rss/rss-lancamentos?rss=31";
			break;
		case "lan_vinhos":
			$rss_link = "/rss/rss-lancamentos?rss=35";
			break;
		case "prom_Geral":
			$rss_link = "/rss/rss-base";
			break;
		case "prom_Bebes":
			$rss_link = "/rss/rss-base?rss=30";
			break;
		case "prom_Beleza":
			$rss_link = "/rss/rss-base?rss=24";
			break;
		case "prom_Brinquedos":
			$rss_link = "/rss/rss-base?rss=3";
			break;
		case "prom_Cama":
			$rss_link = "/rss/rss-base?rss=36";
			break;
		case "prom_Cameras":
			$rss_link = "/rss/rss-base?rss=26";
			break;
		case "prom_Celulares":
			$rss_link = "/rss/rss-base?rss=11";
			break;
		case "prom_CDs":
			$rss_link = "/rss/rss-base?rss=2";
			break;
		case "prom_DVD":
			$rss_link = "/rss/rss-base?rss=6";
			break;
		case "prom_Esporte":
			$rss_link = "/rss/rss-base?rss=28";
			break;
		case "prom_Eletrodomesticos":
			$rss_link = "/rss/rss-base?rss=27";
			break;
		case "prom_Eletronicos":
			$rss_link = "/rss/rss-base?rss=13";
			break;
		case "prom_Eletroportateis":
			$rss_link = "/rss/rss-base?rss=34";
			break;
		case "prom_Ferramentas":
			$rss_link = "/rss/rss-base?rss=15";
			break;
		case "prom_Games":
			$rss_link = "/rss/rss-base?rss=12";
			break;
		case "prom_Informatica":
			$rss_link = "/rss/rss-base?rss=10";
			break;
		case "prom_Instrumentos":
			$rss_link = "/rss/rss-base?rss=32";
			break;
		case "prom_Livros":
			$rss_link = "/rss/rss-base?rss=1";
			break;
		case "prom_livrosImportados":
			$rss_link = "/rss/rss-base?rss=9";
			break;
		case "prom_Papelaria":
			$rss_link = "/rss/rss-base?rss=37";
			break;
		case "prom_Perfumaria":
			$rss_link = "/rss/rss-base?rss=33";
			break;
		case "prom_Relogios":
			$rss_link = "/rss/rss-base?rss=25";
			break;
		case "prom_Utilidades":
			$rss_link = "/rss/rss-base?rss=18";
			break;
		case "prom_Vestuario":
			$rss_link = "/rss/rss-base?rss=31";
			break;
		case "prom_vinhos":
			$rss_link = "/rss/rss-base?rss=35";
			break;
	}

	$response = vs_http_get($request, "www.submarino.com.br", $rss_link);
	$data = vs_xmlEntities($response[1]);
	$produtos = vs_xml2array($data,0);
	
	$produtos2 = $produtos["rss"]["channel"]['item'];

	foreach ($produtos2 as $produto) {

		$nome = $produto["title"];
		$diz = $produto["description"];

		if (stripos($produto["link"], "?") === FALSE) {
			$link_prod = $produto["link"]."?franq=".$vs_options['codafil'];
		} else {
			$link_prod = $produto["link"]."&franq=".$vs_options['codafil'];
		}
		
		preg_match_all('/<img[^>]+>/i',$diz, $result); 
		$imagem = $result[0][0];

		$preco = strip_tags("R".strrchr($diz, "$"));

		if ($preco == "R")
			$preco = '';

		$lprod .= "('" . $wpdb->escape($nome) . "','" . $wpdb->escape($link_prod) . "','" . $wpdb->escape($imagem) . "','" . $wpdb->escape($preco) . "', '". $wpdb->escape($rss_source) ."'), ";
	} //foreach	
	
		//inclui $nome $link_prod $imagem e $preco no BD
		$insert = "INSERT INTO wp_vitrinesubmarino (nomep, linkp, imagemp, precop, rss_source) VALUES " . rtrim($lprod, ", ");
		$results = $wpdb->query( $insert );

}

/***************************************************************************************************
 *  pega produtos da base de dados
 */
function vs_pegaprodutos($vitrine){ 

	global $wpdb;
	global $vs_options;
	
	if ($vitrine != "widget") {
		$rss_source = $vs_options['ctx_rss_source'];
	} else {
		$rss_source = $vs_options['wid_rss_source'];
	}

	$select = "SELECT * FROM wp_vitrinesubmarino WHERE rss_source = '". $rss_source ."'";
	
	$results = $wpdb->get_results( $select , ARRAY_A);
	
	return $results;
}
/***************************************************************************************************
 *  atualiza o cache
 */
function vs_atualiza_produtos(){ 

	global $wpdb;
	global $vs_options;

	$truncate = "TRUNCATE TABLE wp_vitrinesubmarino";
	$results = $wpdb->query( $truncate );
	
	vs_pegaprodutos_rss($vs_options['ctx_rss_source']);
	vs_pegaprodutos_rss($vs_options['wid_rss_source']);
}

/***************************************************************************************************
 *  Funcao principal
 */
function vs_core ($show, $word, $vitrine, $fundo, $borda, $desc, $corprec) {
	global $wpdb;

	error_reporting( 0 );

	global $vs_options;
	global $vs_version;
	
	if ($vs_options['codafil'] == '')
		return "ERRO: Código de Afiliado não informado.";

	if ($vs_options['version'] != $vs_version)
		return "ERRO: Atualização necessária.";

	$idsubmarino = $vs_options['codafil'];			// Define codigo de afiliado para o script funcionar

//pega produtos da BD (devolve um array)

$produtos = vs_pegaprodutos($vitrine);
shuffle($produtos);
if (is_array($produtos)) {

	foreach ($produtos as $produto) {


		$nome = $produto['nomep'];
		$link_prod = $produto['linkp'];
		$imagem = $produto['imagemp'];
		$preco = $produto['precop'];

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

		switch ($vitrine) {
	
			case "contextual":
				// vitrine contextual
				if ($vs_options['ctx_tipo'] == "horizontal") {
					$td = 100 / $vs_options['ctx_show'];
					//mostra vitrine com produtos em uma unica linha (VITRINE HORIZONTAL)
					$lista_de_produtos .= '<div style="width:'. $td.'%;background-color:white;text-align:center;padding:0px;font-size:12px;border:0px;float:left;"><a href="'.$link_prod.'">'.$imagem.'</a><br /><a href="'.$link_prod.'" target="_blank">'.$nome.'</a><br /><div style="color:'.$corprec.';">'.$preco.'</div>'.$compare_precos.'</div>';
				} else {
					$imagem = str_replace("<img ", "<img style=\" display: inline; float: left; margin: 0 10px 10px 0;\" alt=\"".$nome."\"", $imagem);
					//mostra vitrine com um produto por linha (VITRINE VERTICAL)
					$lista_de_produtos .= '<div style="height:130px;background-color:white;padding:3px;"><a href="'.$link_prod.'">'.$imagem.'</a><a href="'.$link_prod.'" target="_blank">'.$nome.'</a><br /><div style="color:'.$corprec.';">'.$preco.'</div>'.$compare_precos.'</div>';
				}
				break;
	
			case "widget":
				$lista_de_produtos .= '<div style="color:'.$desc.';background-color:'.$fundo.';text-align:center;padding:3px;"><a href="'.$link_prod.'" target="_blank">'.$imagem.'<br />'.$nome.'</a><br /><div style="color:'.$corprec.';">'.$preco.'</div>'.$compare_precos.'</div>';
				break;
	
		} //switch
	if ($i == $show-1) 
		break;
	else
		$i++;
	} //foreach	


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
			} //switch
		} //if
		return "<center>".$altcode."</center>";
	}# else {
	
	$credits = "<div style=\"text-align:right;\"><small><a href='http://www.bernabauer.com/wp-plugins/'>Vitrine Submarino ".$vs_options['version']."</a></small></div>";

	if (($vitrine == "contextual") AND ($vs_options['ctx_exib_auto'] == 'auto'))
		$titulo = $vs_options['ctx_titulo'];
	else
		$titulo = '';

	if ($vitrine == "widget") {
		$combordas = "border:2px solid ".$borda.";background-color:".$fundo.";";
	} else
		$combordas = '';

	return "<br clear=both />".$titulo."<div style=\"".$combordas."\">".$lista_de_produtos."</div>".$credits;
	#}
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

		$vs_options['wid_rss_source'] = strip_tags(stripslashes($_POST['wid_rss_source']));
		$vs_options['wid_altcode'] = strip_tags(stripslashes($_POST['wid_altcode']));
		$vs_options['wid_track'] = strip_tags(stripslashes($_POST['wid_track']));

		// Opções CONTEXTUAL
		if (isset($_POST['ctx_prcolor'])) 
			$vs_options['ctx_prcolor'] = strip_tags(stripslashes($_POST['ctx_prcolor']));
		
		if (isset($_POST['ctx_titulo'])) 
			$vs_options['ctx_titulo'] = stripslashes($_POST['ctx_titulo']);

		if (isset($_POST['ctx_show'])) 
			$vs_options['ctx_show'] = strip_tags(stripslashes($_POST['ctx_show']));


		$vs_options['ctx_rss_source'] = strip_tags(stripslashes($_POST['ctx_rss_source']));
		$vs_options['ctx_altcode'] = strip_tags(stripslashes($_POST['ctx_altcode']));
		$vs_options['ctx_exib_auto'] = strip_tags(stripslashes($_POST['ctx_exib_auto']));
		$vs_options['ctx_local'] = strip_tags(stripslashes($_POST['ctx_local']));
		$vs_options['ctx_track'] = strip_tags(stripslashes($_POST['ctx_track']));
		$vs_options['ctx_tipo'] = strip_tags(stripslashes($_POST['ctx_tipo']));
		$vs_options['ctx_style'] = strip_tags(stripslashes($_POST['ctx_style']));
		$vs_options['ctx_alt'] = strip_tags(stripslashes($_POST['ctx_alt']));

		//atualiza base de dados com informacaoes do formulario		
		update_option('vs_options',$vs_options);

		//atualiza o cache local de produtos com a nova configuracao
		vs_atualiza_produtos();
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

	switch ($vs_options['wid_rss_source']) {
	
		case "mv_Bebes":
			$wid_mv_Bebes = 'checked=\"checked\"';
			break;
		case "mv_Beleza":
			$wid_mv_Beleza = 'checked=\"checked\"';
			break;
		case "mv_Brinquedos":
			$wid_mv_Brinquedos = 'checked=\"checked\"';
			break;
		case "mv_Cama":
			$wid_mv_Cama = 'checked=\"checked\"';
			break;
		case "mv_Cameras":
			$wid_mv_Cameras = 'checked=\"checked\"';
			break;
		case "mv_Celulares":
			$wid_mv_Celulares = 'checked=\"checked\"';
			break;
		case "mv_CDs":
			$wid_mv_CDs = 'checked=\"checked\"';
			break;
		case "mv_DVD":
			$wid_mv_DVD = 'checked=\"checked\"';
			break;
		case "mv_Esporte":
			$wid_mv_Esporte = 'checked=\"checked\"';
			break;
		case "mv_Eletrodomesticos":
			$wid_mv_Eletrodomesticos = 'checked=\"checked\"';
			break;
		case "mv_Eletronicos":
			$wid_mv_Eletronicos = 'checked=\"checked\"';
			break;
		case "mv_Eletroportateis":
			$wid_mv_Eletroportateis = 'checked=\"checked\"';
			break;
		case "mv_Ferramentas":
			$wid_mv_Ferramentas = 'checked=\"checked\"';
			break;
		case "mv_Games":
			$wid_mv_Games = 'checked=\"checked\"';
			break;
		case "mv_Informatica":
			$wid_mv_Informatica = 'checked=\"checked\"';
			break;
		case "mv_Instrumentos":
			$wid_mv_Instrumentos = 'checked=\"checked\"';
			break;
		case "mv_Livros":
			$wid_mv_Livros = 'checked=\"checked\"';
			break;
		case "mv_livrosImportados":
			$wid_mv_livrosImportados = 'checked=\"checked\"';
			break;
		case "mv_Papelaria":
			$wid_mv_Papelaria = 'checked=\"checked\"';
			break;
		case "mv_Perfumaria":
			$wid_mv_Perfumaria = 'checked=\"checked\"';
			break;
		case "mv_Relogios":
			$wid_mv_Relogios = 'checked=\"checked\"';
			break;
		case "mv_Utilidades":
			$wid_mv_Utilidades = 'checked=\"checked\"';
			break;
		case "mv_Vestuario":
			$wid_mv_Vestuario = 'checked=\"checked\"';
			break;
		case "mv_vinhos":
			$wid_mv_vinhos = 'checked=\"checked\"';
			break;
		case "lan_Geral":
			$wid_lan_Geral = 'checked=\"checked\"';
			break;
		case "lan_Bebes":
			$wid_lan_Bebes = 'checked=\"checked\"';
			break;
		case "lan_Beleza":
			$wid_lan_Beleza = 'checked=\"checked\"';
			break;
		case "lan_Brinquedos":
			$wid_lan_Brinquedos = 'checked=\"checked\"';
			break;
		case "lan_Cama":
			$wid_lan_Cama = 'checked=\"checked\"';
			break;
		case "lan_Cameras":
			$wid_lan_Cameras = 'checked=\"checked\"';
			break;
		case "lan_Celulares":
			$wid_lan_Celulares = 'checked=\"checked\"';
			break;
		case "lan_CDs":
			$wid_lan_CDs = 'checked=\"checked\"';
			break;
		case "lan_DVD":
			$wid_lan_DVD = 'checked=\"checked\"';
			break;
		case "lan_Esporte":
			$wid_lan_Esporte = 'checked=\"checked\"';
			break;
		case "lan_Eletrodomesticos":
			$wid_lan_Eletrodomesticos = 'checked=\"checked\"';
			break;
		case "lan_Eletronicos":
			$wid_lan_Eletronicos = 'checked=\"checked\"';
			break;
		case "lan_Eletroportateis":
			$wid_lan_Eletroportateis = 'checked=\"checked\"';
			break;
		case "lan_Ferramentas":
			$wid_lan_Ferramentas = 'checked=\"checked\"';
			break;
		case "lan_Games":
			$wid_lan_Games = 'checked=\"checked\"';
			break;
		case "lan_Informatica":
			$wid_lan_Informatica = 'checked=\"checked\"';
			break;
		case "lan_Instrumentos":
			$wid_lan_Instrumentos = 'checked=\"checked\"';
			break;
		case "lan_Livros":
			$wid_lan_Livros = 'checked=\"checked\"';
			break;
		case "lan_Papelaria":
			$wid_lan_Papelaria = 'checked=\"checked\"';
			break;
		case "lan_Perfumaria":
			$wid_lan_Perfumaria = 'checked=\"checked\"';
			break;
		case "lan_Relogios":
			$wid_lan_Relogios = 'checked=\"checked\"';
			break;
		case "lan_Utilidades":
			$wid_lan_Utilidades = 'checked=\"checked\"';
			break;
		case "lan_vinhos":
			$wid_lan_vinhos = 'checked=\"checked\"';
			break;
		case "prom_Geral":
			$wid_prom_Geral = 'checked=\"checked\"';
			break;
		case "prom_Beleza":
			$wid_prom_Beleza = 'checked=\"checked\"';
			break;
		case "prom_Brinquedos":
			$wid_prom_Brinquedos = 'checked=\"checked\"';
			break;
		case "prom_Cameras":
			$wid_prom_Cameras = 'checked=\"checked\"';
			break;
		case "prom_Celulares":
			$wid_prom_Celulares = 'checked=\"checked\"';
			break;
		case "prom_CDs":
			$wid_prom_CDs = 'checked=\"checked\"';
			break;
		case "prom_DVD":
			$wid_prom_DVD = 'checked=\"checked\"';
			break;
		case "prom_Esporte":
			$wid_prom_Esporte = 'checked=\"checked\"';
			break;
		case "prom_Eletrodomesticos":
			$wid_prom_Eletrodomesticos = 'checked=\"checked\"';
			break;
		case "prom_Eletronicos":
			$wid_prom_Eletronicos = 'checked=\"checked\"';
			break;
		case "prom_Games":
			$wid_prom_Games = 'checked=\"checked\"';
			break;
		case "prom_Livros":
			$wid_prom_Livros = 'checked=\"checked\"';
			break;
	}
	
	switch ($vs_options['ctx_rss_source']) {
	
		case "mv_Bebes":
			$ctx_mv_Bebes = 'checked=\"checked\"';
			break;
		case "mv_Beleza":
			$ctx_mv_Beleza = 'checked=\"checked\"';
			break;
		case "mv_Brinquedos":
			$ctx_mv_Brinquedos = 'checked=\"checked\"';
			break;
		case "mv_Cama":
			$ctx_mv_Cama = 'checked=\"checked\"';
			break;
		case "mv_Cameras":
			$ctx_mv_Cameras = 'checked=\"checked\"';
			break;
		case "mv_Celulares":
			$ctx_mv_Celulares = 'checked=\"checked\"';
			break;
		case "mv_CDs":
			$ctx_mv_CDs = 'checked=\"checked\"';
			break;
		case "mv_DVD":
			$ctx_mv_DVD = 'checked=\"checked\"';
			break;
		case "mv_Esporte":
			$ctx_mv_Esporte = 'checked=\"checked\"';
			break;
		case "mv_Eletrodomesticos":
			$ctx_mv_Eletrodomesticos = 'checked=\"checked\"';
			break;
		case "mv_Eletronicos":
			$ctx_mv_Eletronicos = 'checked=\"checked\"';
			break;
		case "mv_Eletroportateis":
			$ctx_mv_Eletroportateis = 'checked=\"checked\"';
			break;
		case "mv_Ferramentas":
			$ctx_mv_Ferramentas = 'checked=\"checked\"';
			break;
		case "mv_Games":
			$ctx_mv_Games = 'checked=\"checked\"';
			break;
		case "mv_Informatica":
			$ctx_mv_Informatica = 'checked=\"checked\"';
			break;
		case "mv_Instrumentos":
			$ctx_mv_Instrumentos = 'checked=\"checked\"';
			break;
		case "mv_Livros":
			$ctx_mv_Livros = 'checked=\"checked\"';
			break;
		case "mv_livrosImportados":
			$ctx_mv_livrosImportados = 'checked=\"checked\"';
			break;
		case "mv_Papelaria":
			$ctx_mv_Papelaria = 'checked=\"checked\"';
			break;
		case "mv_Perfumaria":
			$ctx_mv_Perfumaria = 'checked=\"checked\"';
			break;
		case "mv_Relogios":
			$ctx_mv_Relogios = 'checked=\"checked\"';
			break;
		case "mv_Utilidades":
			$ctx_mv_Utilidades = 'checked=\"checked\"';
			break;
		case "mv_Vestuario":
			$ctx_mv_Vestuario = 'checked=\"checked\"';
			break;
		case "mv_vinhos":
			$ctx_mv_vinhos = 'checked=\"checked\"';
			break;
		case "lan_Geral":
			$ctx_lan_Geral = 'checked=\"checked\"';
			break;
		case "lan_Bebes":
			$ctx_lan_Bebes = 'checked=\"checked\"';
			break;
		case "lan_Beleza":
			$ctx_lan_Beleza = 'checked=\"checked\"';
			break;
		case "lan_Brinquedos":
			$ctx_lan_Brinquedos = 'checked=\"checked\"';
			break;
		case "lan_Cama":
			$ctx_lan_Cama = 'checked=\"checked\"';
			break;
		case "lan_Cameras":
			$ctx_lan_Cameras = 'checked=\"checked\"';
			break;
		case "lan_Celulares":
			$ctx_lan_Celulares = 'checked=\"checked\"';
			break;
		case "lan_CDs":
			$ctx_lan_CDs = 'checked=\"checked\"';
			break;
		case "lan_DVD":
			$ctx_lan_DVD = 'checked=\"checked\"';
			break;
		case "lan_Esporte":
			$ctx_lan_Esporte = 'checked=\"checked\"';
			break;
		case "lan_Eletrodomesticos":
			$ctx_lan_Eletrodomesticos = 'checked=\"checked\"';
			break;
		case "lan_Eletronicos":
			$ctx_lan_Eletronicos = 'checked=\"checked\"';
			break;
		case "lan_Eletroportateis":
			$ctx_lan_Eletroportateis = 'checked=\"checked\"';
			break;
		case "lan_Ferramentas":
			$ctx_lan_Ferramentas = 'checked=\"checked\"';
			break;
		case "lan_Games":
			$ctx_lan_Games = 'checked=\"checked\"';
			break;
		case "lan_Informatica":
			$ctx_lan_Informatica = 'checked=\"checked\"';
			break;
		case "lan_Instrumentos":
			$ctx_lan_Instrumentos = 'checked=\"checked\"';
			break;
		case "lan_Livros":
			$ctx_lan_Livros = 'checked=\"checked\"';
			break;
		case "lan_Papelaria":
			$ctx_lan_Papelaria = 'checked=\"checked\"';
			break;
		case "lan_Perfumaria":
			$ctx_lan_Perfumaria = 'checked=\"checked\"';
			break;
		case "lan_Relogios":
			$ctx_lan_Relogios = 'checked=\"checked\"';
			break;
		case "lan_Utilidades":
			$ctx_lan_Utilidades = 'checked=\"checked\"';
			break;
		case "lan_vinhos":
			$ctx_lan_vinhos = 'checked=\"checked\"';
			break;
		case "prom_Geral":
			$ctx_prom_Geral = 'checked=\"checked\"';
			break;
		case "prom_Beleza":
			$ctx_prom_Beleza = 'checked=\"checked\"';
			break;
		case "prom_Brinquedos":
			$ctx_prom_Brinquedos = 'checked=\"checked\"';
			break;
		case "prom_Cameras":
			$ctx_prom_Cameras = 'checked=\"checked\"';
			break;
		case "prom_Celulares":
			$ctx_prom_Celulares = 'checked=\"checked\"';
			break;
		case "prom_CDs":
			$ctx_prom_CDs = 'checked=\"checked\"';
			break;
		case "prom_DVD":
			$ctx_prom_DVD = 'checked=\"checked\"';
			break;
		case "prom_Esporte":
			$ctx_prom_Esporte = 'checked=\"checked\"';
			break;
		case "prom_Eletrodomesticos":
			$ctx_prom_Eletrodomesticos = 'checked=\"checked\"';
			break;
		case "prom_Eletronicos":
			$ctx_prom_Eletronicos = 'checked=\"checked\"';
			break;
		case "prom_Games":
			$ctx_prom_Games = 'checked=\"checked\"';
			break;
		case "prom_Livros":
			$ctx_prom_Livros = 'checked=\"checked\"';
			break;
	}


?>

	<div class=wrap>

    <h2>Configurações</h2>
  <form method="post">

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Aviso</th>
		<td>
			 Este plugin pega os produtos para serem mostrados em seu blog fornecidos através da <a href="http://www.submarino.com.br/portal/central-rss/?WT.mc_id=rss_left&WT.mc_ev=click" target=_blank>fonte de RSS que o Submarino fornece</a>. Para mostrar os produtos nas vitrines contextual e widget o plugin efetua um cache local que é atualizado uma vez por dia. Fique atento ao seguinte: A página de configuração do plugin pode demorar um pouco para carregar quando as configurações são alteradas. Isto ocorre por que neste momento é forçada uma atualização do cache local a partir da fonte RSS.<br /><br />Próxima atualização de produtos via RSS: 
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
    <h2>Vitrine Simples</h2>
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
			<input type="radio" id="vitver" name="ctx_tipo" value="vertical" <?php echo $vertical; ?> /> <label for="vitver">Vertical (um produto por linha)</label>
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
		<th scope="row" valign="top">Fonte RSS de Produtos<br />Veja mais informações <a href="http://www.submarino.com.br/portal/central-rss/">aqui</a>.</th>
		<td VALIGN="TOP">
			<strong>Mais Vendidos</strong><br />
			<input type="radio" name="ctx_rss_source" value="mv_Bebes" id="ctx_mv_Bebes" <?php echo $ctx_mv_Bebes; ?> /> <label for="ctx_mv_Bebes">Bebês</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Beleza" id="ctx_mv_Beleza" <?php echo $ctx_mv_Beleza; ?> /> <label for="ctx_mv_Beleza">Beleza & Saúde</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Brinquedos" id="ctx_mv_Brinquedos" <?php echo $ctx_mv_Brinquedos; ?> /> <label for="ctx_mv_Brinquedos">Brinquedos</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Cama" id="ctx_mv_Cama" <?php echo $ctx_mv_Cama; ?> /> <label for="ctx_mv_Cama">Cama, Mesa & banho</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Cameras" id="ctx_mv_Cameras" <?php echo $ctx_mv_Cameras; ?> /> <label for="ctx_mv_Cameras">Câmeras Digitais & Filmadoras</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Celulares" id="ctx_mv_Celulares" <?php echo $ctx_mv_Celulares; ?> /> <label for="ctx_mv_Celulares">Celulares & Telefonia Fixa</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_CDs" id="ctx_mv_CDs" <?php echo $ctx_mv_CDs; ?> /> <label for="ctx_mv_CDs">CDs</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_DVD" id="ctx_mv_DVD" <?php echo $ctx_mv_DVD; ?> /> <label for="ctx_mv_DVD">DVDs & Blu-ray</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Esporte" id="ctx_mv_Esporte" <?php echo $ctx_mv_Esporte; ?> /> <label for="ctx_mv_Esporte">Esporte & Lazer</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Eletrodomesticos" id="ctx_mv_Eletrodomesticos" <?php echo $ctx_mv_Eletrodomesticos; ?> /> <label for="ctx_mv_Eletrodomesticos">Eletrodomésticos</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Eletronicos" id="ctx_mv_Eletronicos" <?php echo $ctx_mv_Eletronicos; ?> /> <label for="ctx_mv_Eletronicos">Eletrônicos Áudio & Vídeo</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Eletroportateis" id="ctx_mv_Eletroportateis" <?php echo $ctx_mv_Eletroportateis; ?> /> <label for="ctx_mv_Eletroportateis">Eletroportáteis</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Ferramentas" id="ctx_mv_Ferramentas" <?php echo $ctx_mv_Ferramentas; ?> /> <label for="ctx_mv_Ferramentas">Ferramentas</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Games" id="ctx_mv_Games" <?php echo $ctx_mv_Games; ?> /> <label for="ctx_mv_Games">Games</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Informatica" id="ctx_mv_Informatica" <?php echo $ctx_mv_Informatica; ?> /> <label for="ctx_mv_Informatica">Informática & Acessórios</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Instrumentos" id="ctx_mv_Instrumentos" <?php echo $ctx_mv_Instrumentos; ?> /> <label for="ctx_mv_Instrumentos">Instrumentos Musicais</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Livros" id="ctx_mv_Livros" <?php echo $ctx_mv_Livros; ?> /> <label for="ctx_mv_Livros">Livros</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_livrosImportados" id="ctx_mv_livrosImportados" <?php echo $ctx_mv_livrosImportados; ?> /> <label for="ctx_mv_livrosImportados">Livros Importados</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Papelaria" id="ctx_mv_Papelaria" <?php echo $ctx_mv_Papelaria; ?> /> <label for="ctx_mv_Papelaria">Papelaria</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Perfumaria" id="ctx_mv_Perfumaria" <?php echo $ctx_mv_Perfumaria; ?> /> <label for="ctx_mv_Perfumaria">Perfumaria</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Relogios" id="ctx_mv_Relogios" <?php echo $ctx_mv_Relogios; ?> /> <label for="ctx_mv_Relogios">Relógios & Presentes</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Utilidades" id="ctx_mv_Utilidades" <?php echo $ctx_mv_Utilidades; ?> /> <label for="ctx_mv_Utilidades">Utilidades Domésticas</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_Vestuario" id="ctx_mv_Vestuario" <?php echo $ctx_mv_Vestuario; ?> /> <label for="ctx_mv_Vestuario">Vestuário</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="mv_vinhos" id="ctx_mv_vinhos" <?php echo $ctx_mv_vinhos; ?> /> <label for="ctx_mv_vinhos">Vinhos & Bebidas</label>
			<br />
			</td>
			<td VALIGN="TOP">
			<strong>Lançamento</strong><br />
			<input type="radio" name="ctx_rss_source" value="lan_Geral" id="ctx_lan_Geral" <?php echo $ctx_lan_Geral; ?> /> <label for="ctx_lan_Geral">Geral (Todo o Site)</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Bebes" id="ctx_lan_Bebes" <?php echo $ctx_lan_Bebes; ?> /> <label for="ctx_lan_Bebes">Bebês</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Beleza" id="ctx_lan_Beleza" <?php echo $ctx_lan_Beleza; ?> /> <label for="ctx_lan_Beleza">Beleza & Saúde</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Brinquedos" id="ctx_lan_Brinquedos" <?php echo $ctx_lan_Brinquedos; ?> /> <label for="ctx_lan_Brinquedos">Brinquedos</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Cama" id="ctx_lan_Cama" <?php echo $ctx_lan_Cama; ?> /> <label for="ctx_lan_Cama">Cama, Mesa & banho</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Cameras" id="ctx_lan_Cameras" <?php echo $ctx_lan_Cameras; ?> /> <label for="ctx_lan_Cameras">Câmeras Digitais & Filmadoras</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Celulares" id="ctx_lan_Celulares" <?php echo $ctx_lan_Celulares; ?> /> <label for="ctx_lan_Celulares">Celulares & Telefonia Fixa</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_CDs" id="ctx_lan_CDs" <?php echo $ctx_lan_CDs; ?> /> <label for="ctx_lan_CDs">CDs</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_DVD" id="ctx_lan_DVD" <?php echo $ctx_lan_DVD; ?> /> <label for="ctx_lan_DVD">DVDs & Blu-ray</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Esporte" id="ctx_lan_Esporte" <?php echo $ctx_lan_Esporte; ?> /> <label for="ctx_lan_Esporte">Esporte & Lazer</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Eletrodomesticos" id="ctx_lan_Eletrodomesticos" <?php echo $ctx_lan_Eletrodomesticos; ?> /> <label for="ctx_lan_Eletrodomesticos">Eletrodomésticos</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Eletronicos" id="ctx_lan_Eletronicos" <?php echo $ctx_lan_Eletronicos; ?> /> <label for="ctx_lan_Eletronicos">Eletrônicos Áudio & Vídeo</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Eletroportateis" id="ctx_lan_Eletroportateis" <?php echo $ctx_lan_Eletroportateis; ?> /> <label for="ctx_lan_Eletroportateis">Eletroportáteis</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Ferramentas" id="ctx_lan_Ferramentas" <?php echo $ctx_lan_Ferramentas; ?> /> <label for="ctx_lan_Ferramentas">Ferramentas</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Games" id="ctx_lan_Games" <?php echo $ctx_lan_Games; ?> /> <label for="ctx_lan_Games">Games</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Informatica" id="ctx_lan_Informatica" <?php echo $ctx_lan_Informatica; ?> /> <label for="ctx_lan_Informatica">Informática & Acessórios</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Instrumentos" id="ctx_lan_Instrumentos" <?php echo $ctx_lan_Instrumentos; ?> /> <label for="ctx_lan_Instrumentos">Instrumentos Musicais</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Livros" id="ctx_lan_Livros" <?php echo $ctx_lan_Livros; ?> /> <label for="ctx_lan_Livros">Livros</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Papelaria" id="ctx_lan_Papelaria" <?php echo $ctx_lan_Papelaria; ?> /> <label for="ctx_lan_Papelaria">Papelaria</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Perfumaria" id="ctx_lan_Perfumaria" <?php echo $ctx_lan_Perfumaria; ?> /> <label for="ctx_lan_Perfumaria">Perfumaria</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Relogios" id="ctx_lan_Relogios" <?php echo $ctx_lan_Relogios; ?> /> <label for="ctx_lan_Relogios">Relógios & Presentes</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_Utilidades" id="ctx_lan_Utilidades" <?php echo $ctx_lan_Utilidades; ?> /> <label for="ctx_lan_Utilidades">Utilidades Domésticas</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="lan_vinhos" id="ctx_lan_vinhos" <?php echo $ctx_lan_vinhos; ?> /> <label for="ctx_lan_vinhos">Vinhos & Bebidas</label>
			<br />
			</td>
			<td VALIGN="TOP">
			<strong>Promoção</strong><br />
			<input type="radio" name="ctx_rss_source" value="prom_Geral" id="ctx_prom_Geral" <?php echo $ctx_prom_Geral; ?> /> <label for="ctx_prom_Geral">Geral (Todo o Site)</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Beleza" id="ctx_prom_Beleza" <?php echo $ctx_prom_Beleza; ?> /> <label for="ctx_prom_Beleza">Beleza & Saúde</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Brinquedos" id="ctx_prom_Brinquedos" <?php echo $ctx_prom_Brinquedos; ?> /> <label for="ctx_prom_Brinquedos">Brinquedos</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Cameras" id="ctx_prom_Cameras" <?php echo $ctx_prom_Cameras; ?> /> <label for="ctx_prom_Cameras">Câmeras Digitais & Filmadoras</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Celulares" id="ctx_prom_Celulares" <?php echo $ctx_prom_Celulares; ?> /> <label for="ctx_prom_Celulares">Celulares & Telefonia Fixa</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_CDs" id="ctx_prom_CDs" <?php echo $ctx_prom_CDs; ?> /> <label for="ctx_prom_CDs">CDs</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_DVD" id="ctx_prom_DVD" <?php echo $ctx_prom_DVD; ?> /> <label for="ctx_prom_DVD">DVDs & Blu-ray</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Esporte" id="ctx_prom_Esporte" <?php echo $ctx_prom_Esporte; ?> /> <label for="ctx_prom_Esporte">Esporte & Lazer</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Eletrodomesticos" id="ctx_prom_Eletrodomesticos" <?php echo $ctx_prom_Eletrodomesticos; ?> /> <label for="ctx_prom_Eletrodomesticos">Eletrodomésticos</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Eletronicos" id="ctx_prom_Eletronicos" <?php echo $ctx_prom_Eletronicos; ?> /> <label for="ctx_prom_Eletronicos">Eletrônicos Áudio & Vídeo</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Games" id="ctx_prom_Games" <?php echo $ctx_prom_Games; ?> /> <label for="ctx_prom_Games">Games</label>
			<br />
			<input type="radio" name="ctx_rss_source" value="prom_Livros" id="ctx_prom_Livros" <?php echo $ctx_prom_Livros; ?> /> <label for="ctx_prom_Livros">Livros</label>
			<br />
		</td>
	 </tr>
	</table>
<br />

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
  				</table>
 		</td>
	 </tr>
	</table>

    <table class="form-table">
	 <tr>
		<th scope="row" valign="top">Fonte RSS de Produtos<br />Veja mais informações <a href="http://www.submarino.com.br/portal/central-rss/">aqui</a>.</th>
		<td VALIGN="TOP">
			<strong>Mais Vendidos</strong><br />
			<input type="radio" name="wid_rss_source" value="mv_Bebes" id="wid_mv_Bebes" <?php echo $wid_mv_Bebes; ?> /> <label for="wid_mv_Bebes">Bebês</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Beleza" id="wid_mv_Beleza" <?php echo $wid_mv_Beleza; ?> /> <label for="wid_mv_Beleza">Beleza & Saúde</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Brinquedos" id="wid_mv_Brinquedos" <?php echo $wid_mv_Brinquedos; ?> /> <label for="wid_mv_Brinquedos">Brinquedos</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Cama" id="wid_mv_Cama" <?php echo $wid_mv_Cama; ?> /> <label for="wid_mv_Cama">Cama, Mesa & banho</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Cameras" id="wid_mv_Cameras" <?php echo $wid_mv_Cameras; ?> /> <label for="wid_mv_Cameras">Câmeras Digitais & Filmadoras</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Celulares" id="wid_mv_Celulares" <?php echo $wid_mv_Celulares; ?> /> <label for="wid_mv_Celulares">Celulares & Telefonia Fixa</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_CDs" id="wid_mv_CDs" <?php echo $wid_mv_CDs; ?> /> <label for="wid_mv_CDs">CDs</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_DVD" id="wid_mv_DVD" <?php echo $wid_mv_DVD; ?> /> <label for="wid_mv_DVD">DVDs & Blu-ray</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Esporte" id="wid_mv_Esporte" <?php echo $wid_mv_Esporte; ?> /> <label for="wid_mv_Esporte">Esporte & Lazer</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Eletrodomesticos" id="wid_mv_Eletrodomesticos" <?php echo $wid_mv_Eletrodomesticos; ?> /> <label for="wid_mv_Eletrodomesticos">Eletrodomésticos</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Eletronicos" id="wid_mv_Eletronicos" <?php echo $wid_mv_Eletronicos; ?> /> <label for="wid_mv_Eletronicos">Eletrônicos Áudio & Vídeo</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Eletroportateis" id="wid_mv_Eletroportateis" <?php echo $wid_mv_Eletroportateis; ?> /> <label for="wid_mv_Eletroportateis">Eletroportáteis</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Ferramentas" id="wid_mv_Ferramentas" <?php echo $wid_mv_Ferramentas; ?> /> <label for="wid_mv_Ferramentas">Ferramentas</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Games" id="wid_mv_Games" <?php echo $wid_mv_Games; ?> /> <label for="wid_mv_Games">Games</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Informatica" id="wid_mv_Informatica" <?php echo $wid_mv_Informatica; ?> /> <label for="wid_mv_Informatica">Informática & Acessórios</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Instrumentos" id="wid_mv_Instrumentos" <?php echo $wid_mv_Instrumentos; ?> /> <label for="wid_mv_Instrumentos">Instrumentos Musicais</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Livros" id="wid_mv_Livros" <?php echo $wid_mv_Livros; ?> /> <label for="wid_mv_Livros">Livros</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_livrosImportados" id="wid_mv_livrosImportados" <?php echo $wid_mv_livrosImportados; ?> /> <label for="wid_mv_livrosImportados">Livros Importados</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Papelaria" id="wid_mv_Papelaria" <?php echo $wid_mv_Papelaria; ?> /> <label for="wid_mv_Papelaria">Papelaria</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Perfumaria" id="wid_mv_Perfumaria" <?php echo $wid_mv_Perfumaria; ?> /> <label for="wid_mv_Perfumaria">Perfumaria</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Relogios" id="wid_mv_Relogios" <?php echo $wid_mv_Relogios; ?> /> <label for="wid_mv_Relogios">Relógios & Presentes</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Utilidades" id="wid_mv_Utilidades" <?php echo $wid_mv_Utilidades; ?> /> <label for="wid_mv_Utilidades">Utilidades Domésticas</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_Vestuario" id="wid_mv_Vestuario" <?php echo $wid_mv_Vestuario; ?> /> <label for="wid_mv_Vestuario">Vestuário</label>
			<br />
			<input type="radio" name="wid_rss_source" value="mv_vinhos" id="wid_mv_vinhos" <?php echo $wid_mv_vinhos; ?> /> <label for="wid_mv_vinhos">Vinhos & Bebidas</label>
			<br />
			</td>
			<td VALIGN="TOP">
			<strong>Lançamento</strong><br />
			<input type="radio" name="wid_rss_source" value="lan_Geral" id="wid_lan_Geral" <?php echo $wid_lan_Geral; ?> /> <label for="wid_lan_Geral">Geral (Todo o Site)</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Bebes" id="wid_lan_Bebes" <?php echo $wid_lan_Bebes; ?> /> <label for="wid_lan_Bebes">Bebês</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Beleza" id="wid_lan_Beleza" <?php echo $wid_lan_Beleza; ?> /> <label for="wid_lan_Beleza">Beleza & Saúde</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Brinquedos" id="wid_lan_Brinquedos" <?php echo $wid_lan_Brinquedos; ?> /> <label for="wid_lan_Brinquedos">Brinquedos</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Cama" id="wid_lan_Cama" <?php echo $wid_lan_Cama; ?> /> <label for="wid_lan_Cama">Cama, Mesa & banho</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Cameras" id="wid_lan_Cameras" <?php echo $wid_lan_Cameras; ?> /> <label for="wid_lan_Cameras">Câmeras Digitais & Filmadoras</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Celulares" id="wid_lan_Celulares" <?php echo $wid_lan_Celulares; ?> /> <label for="wid_lan_Celulares">Celulares & Telefonia Fixa</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_CDs" id="wid_lan_CDs" <?php echo $wid_lan_CDs; ?> /> <label for="wid_lan_CDs">CDs</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_DVD" id="wid_lan_DVD" <?php echo $wid_lan_DVD; ?> /> <label for="wid_lan_DVD">DVDs & Blu-ray</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Esporte" id="wid_lan_Esporte" <?php echo $wid_lan_Esporte; ?> /> <label for="wid_lan_Esporte">Esporte & Lazer</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Eletrodomesticos" id="wid_lan_Eletrodomesticos" <?php echo $wid_lan_Eletrodomesticos; ?> /> <label for="wid_lan_Eletrodomesticos">Eletrodomésticos</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Eletronicos" id="wid_lan_Eletronicos" <?php echo $wid_lan_Eletronicos; ?> /> <label for="wid_lan_Eletronicos">Eletrônicos Áudio & Vídeo</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Eletroportateis" id="wid_lan_Eletroportateis" <?php echo $wid_lan_Eletroportateis; ?> /> <label for="wid_lan_Eletroportateis">Eletroportáteis</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Ferramentas" id="wid_lan_Ferramentas" <?php echo $wid_lan_Ferramentas; ?> /> <label for="wid_lan_Ferramentas">Ferramentas</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Games" id="wid_lan_Games" <?php echo $wid_lan_Games; ?> /> <label for="wid_lan_Games">Games</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Informatica" id="wid_lan_Informatica" <?php echo $wid_lan_Informatica; ?> /> <label for="wid_lan_Informatica">Informática & Acessórios</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Instrumentos" id="wid_lan_Instrumentos" <?php echo $wid_lan_Instrumentos; ?> /> <label for="wid_lan_Instrumentos">Instrumentos Musicais</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Livros" id="wid_lan_Livros" <?php echo $wid_lan_Livros; ?> /> <label for="wid_lan_Livros">Livros</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Papelaria" id="wid_lan_Papelaria" <?php echo $wid_lan_Papelaria; ?> /> <label for="wid_lan_Papelaria">Papelaria</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Perfumaria" id="wid_lan_Perfumaria" <?php echo $wid_lan_Perfumaria; ?> /> <label for="wid_lan_Perfumaria">Perfumaria</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Relogios" id="wid_lan_Relogios" <?php echo $wid_lan_Relogios; ?> /> <label for="wid_lan_Relogios">Relógios & Presentes</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_Utilidades" id="wid_lan_Utilidades" <?php echo $wid_lan_Utilidades; ?> /> <label for="wid_lan_Utilidades">Utilidades Domésticas</label>
			<br />
			<input type="radio" name="wid_rss_source" value="lan_vinhos" id="wid_lan_vinhos" <?php echo $wid_lan_vinhos; ?> /> <label for="wid_lan_vinhos">Vinhos & Bebidas</label>
			<br />
			</td>
			<td VALIGN="TOP">
			<strong>Promoção</strong><br />
			<input type="radio" name="wid_rss_source" value="prom_Geral" id="wid_prom_Geral" <?php echo $wid_prom_Geral; ?> /> <label for="wid_prom_Geral">Geral (Todo o Site)</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Beleza" id="wid_prom_Beleza" <?php echo $wid_prom_Beleza; ?> /> <label for="wid_prom_Beleza">Beleza & Saúde</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Brinquedos" id="wid_prom_Brinquedos" <?php echo $wid_prom_Brinquedos; ?> /> <label for="wid_prom_Brinquedos">Brinquedos</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Cameras" id="wid_prom_Cameras" <?php echo $wid_prom_Cameras; ?> /> <label for="wid_prom_Cameras">Câmeras Digitais & Filmadoras</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Celulares" id="wid_prom_Celulares" <?php echo $wid_prom_Celulares; ?> /> <label for="wid_prom_Celulares">Celulares & Telefonia Fixa</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_CDs" id="wid_prom_CDs" <?php echo $wid_prom_CDs; ?> /> <label for="wid_prom_CDs">CDs</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_DVD" id="wid_prom_DVD" <?php echo $wid_prom_DVD; ?> /> <label for="wid_prom_DVD">DVDs & Blu-ray</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Esporte" id="wid_prom_Esporte" <?php echo $wid_prom_Esporte; ?> /> <label for="wid_prom_Esporte">Esporte & Lazer</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Eletrodomesticos" id="wid_prom_Eletrodomesticos" <?php echo $wid_prom_Eletrodomesticos; ?> /> <label for="wid_prom_Eletrodomesticos">Eletrodomésticos</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Eletronicos" id="wid_prom_Eletronicos" <?php echo $wid_prom_Eletronicos; ?> /> <label for="wid_prom_Eletronicos">Eletrônicos Áudio & Vídeo</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Games" id="wid_prom_Games" <?php echo $wid_prom_Games; ?> /> <label for="wid_prom_Games">Games</label>
			<br />
			<input type="radio" name="wid_rss_source" value="prom_Livros" id="wid_prom_Livros" <?php echo $wid_prom_Livros; ?> /> <label for="wid_prom_Livros">Livros</label>
			<br />
		</td>
	 </tr>
	</table>
<br />

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
function vs_http_get($request, $host, $path, $port = 80) {

	global $wp_version;
	global $vs_options;	

	$http_request  = "GET $path HTTP/1.0\r\n";
	$http_request .= "Host: $host\r\n";
	$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
	$http_request .= "Content-Length: " . strlen($request) . "\r\n";
	$http_request .= "User-Agent: WordPress/$wp_version | vitrine-submarino/".$vs_options['version']."\r\n";
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
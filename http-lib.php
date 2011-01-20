<?php 

/****************************************************************************************
* Decodificador de respostas enviadas através de Content-transfer-encoding: chunked
* http://www.dreamincode.net/code/snippet2408.htm
****************************************************************************************/
if (!function_exists('transfer_encoding_chunked_decode')) {
	function transfer_encoding_chunked_decode($in) {
	
		$out = '';
		while($in != '') {
			$lf_pos = strpos($in, "\012");
			if($lf_pos === false) {
				$out .= $in;
				break;
			}
			$chunk_hex = trim(substr($in, 0, $lf_pos));
			$sc_pos = strpos($chunk_hex, ';');
			if($sc_pos !== false)
				$chunk_hex = substr($chunk_hex, 0, $sc_pos);
			if($chunk_hex == '') {
				$out .= substr($in, 0, $lf_pos);
				$in = substr($in, $lf_pos + 1);
				continue;
			}
			$chunk_len = hexdec($chunk_hex);
			if($chunk_len) {
				$out .= substr($in, $lf_pos + 1, $chunk_len);
				$in = substr($in, $lf_pos + 2 + $chunk_len);
			} else {
				$in = '';
			}
		}
		return $out;
	}
}
/****************************************************************************************
* Decodificador do header de GET e POST HTTP
* http://google-apps-provisioning-toolkit.googlecode.com/svn-history/r13/trunk/google-apps-provisioning-toolkit/selfprovisioning/acctfunctions.php
****************************************************************************************/
if (!function_exists('decode_header')) {
	function decode_header ( $str )
	{
		$part = preg_split ( "/\r?\n/", $str, -1, PREG_SPLIT_NO_EMPTY );
		$out = array ();
		for ( $h = 0; $h < sizeof ( $part ); $h++ )
		{
			if ( $h != 0 )
			{
				$pos = strpos ( $part[$h], ':' );
				$k = strtolower ( str_replace ( ' ', '', substr ( $part[$h], 0, $pos ) ) );
				$v = trim ( substr ( $part[$h], ( $pos + 1 ) ) );
			}
			else
			{
				$k = 'status';
				$v = explode ( ' ', $part[$h] );
				$v = $v[1];
			}
	
			if ( $k == 'set-cookie' )
			{
					$out['cookies'][] = $v;
			}
			else if ( $k == 'content-type' )
			{
				if ( ( $cs = strpos ( $v, ';' ) ) !== false )
				{
					$out[$k] = substr ( $v, 0, $cs );
				}
				else
				{
					$out[$k] = $v;
				}
			}
			else
			{
				$out[$k] = $v;
			}
		}
		return $out;
	}
}

/*******************************************************************
* safe_mode and open_basedir workaround by http://www.edmondscommerce.co.uk/blog/curl/php-curl-curlopt_followlocation-and-open_basedir-or-safe-mode/
*/
//follow on location problems workaround
if (!function_exists('curl_redir_exec')) {
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
}
if (!function_exists('curl')) {
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
}
/***************************************************************************************************
* This function has been copyed from Akismet 
*  Returns array with headers in $response[0] and body in $response[1]
*/
if (!function_exists('vs_http_post')) {
	function vs_http_post($request, $host, $path, $port = 80) {
	
		global $vs_version;
		global $vs_options;	
		global $wp_version;
	
		$http_request  = "POST $path HTTP/1.0\r\n";
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
}
/***************************************************************************************************
* This function has been copyed from Akismet 
*  Returns array with headers in $response[0] and body in $response[1]
*/
if (!function_exists('vs_http_get')) {
	function vs_http_get($host, $path, $cookie, $port = 80) {
	
		global $vs_version;
		global $vs_options;	
		global $wp_version;
	
		$http_request  = "GET ".$path." HTTP/1.0\r\n";
		$http_request .= "Host: ".$host."\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
		$http_request .= "User-Agent: WordPress/$wp_version | vitrine-submarino/".$vs_options['version']."\r\n";
		$http_request .= $cookie;
	
		$http_request .= "\r\n";
	
		$response = '';
		if( false != ( $fs = @fsockopen('afiliados.submarino.com.br', 80, $errno, $errstr, 10) ) ) {
			fwrite($fs, $http_request);
	
			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
			fclose($fs);
			$response = explode("\r\n\r\n", $response, 2);
		}
	
		return $response;
	}
}

?>
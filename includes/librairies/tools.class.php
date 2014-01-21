<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Define the different tools for the entire plugin
*
*	Define the different tools for the entire plugin
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
 * Define the different tools for the entire plugin
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_tools {

	/**
	 *	Define the tools main page
	 */
	function main_page() {
		echo wpshop_display::display_template_element('wpshop_admin_tools_main_page', array(), array(), 'admin');
	}

	/**
	 *	Return a variable with some basic treatment
	 *
	 *	@param mixed $varToSanitize The variable we want to treat for future use
	 *	@param mixed $varDefaultValue The default value to set to the variable if the different test are not successfull
	 *	@param string $varType optionnal The type of the var for better verification
	 *
	 *	@return mixed $sanitizedVar The var after treatment
	 */
	function varSanitizer($varToSanitize, $varDefaultValue = '', $varType = '') {
		$sanitizedVar = (trim(strip_tags(stripslashes($varToSanitize))) != '') ? trim(strip_tags(stripslashes(($varToSanitize)))) : $varDefaultValue ;

		return $sanitizedVar;
	}

	function forceDownload($Fichier_a_telecharger, $delete_after_download = false) {

		$nom_fichier = basename($Fichier_a_telecharger);
		switch(strrchr($nom_fichier, ".")) {
			case ".gz": $type = "application/x-gzip"; break;
			case ".tgz": $type = "application/x-gzip"; break;
			case ".zip": $type = "application/zip"; break;
			case ".pdf": $type = "application/pdf"; break;
			case ".png": $type = "image/png"; break;
			case ".gif": $type = "image/gif"; break;
			case ".jpg": $type = "image/jpeg"; break;
			case ".txt": $type = "text/plain"; break;
			case ".htm": $type = "text/html"; break;
			case ".html": $type = "text/html"; break;
			default: $type = "application/octet-stream"; break;
		}

		header("Content-disposition: attachment; filename=$nom_fichier");
		header("Content-Type: application/force-download");
		header("Content-Transfer-Encoding: $type\n"); // Surtout ne pas enlever le \n
		header("Content-Length: ".filesize($Fichier_a_telecharger));
		header("Pragma: no-cache");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
		header("Expires: 0");
		readfile($Fichier_a_telecharger);
		if ( $delete_after_download ) {
			unlink( $Fichier_a_telecharger );
		}
		exit;
	}

	function is_sendsms_actived() {
		if(is_plugin_active('wordpress-send-sms/Send-SMS.php')) {
			$configOption = get_option('sendsms_config', '');
			$ligne = unserialize($configOption);
			$nicOVH = $ligne['nicOVH'];
			$passOVH = $ligne['passOVH'];
			$compteSMS = $ligne['compteSMS'];
			$tel_admin = $ligne['tel_admin'];
			return !empty($nicOVH) && !empty($passOVH) && !empty($compteSMS) && !empty($tel_admin);
		}
		return false;
	}

	function search_all_possibilities( $input ) {
		$result = array();

		while (list($key, $values) = each($input)) {
			if (empty($values)) {
				continue;
			}

			if (empty($result)) {
				foreach($values as $value) {
					$result[] = array($key => $value);
				}
			}
			else {
				$append = array();
				foreach($result as &$product) {
					$product[$key] = array_shift($values);
					$copy = $product;

					foreach($values as $item) {
						$copy[$key] = $item;
						$append[] = $copy;
					}

					array_unshift($values, $product[$key]);
				}

				$result = array_merge($result, $append);
			}
		}

		return $result;
	}

	/** Return the shop currency */
	function wpshop_get_currency($code=false) {
		// Currency
		global $wpdb;
		$current_currency = get_option('wpshop_shop_default_currency');
		$query = $wpdb->prepare('SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id =%d ', $current_currency );
		$currency_infos = $wpdb->get_row( $query );
		if ( !empty($currency_infos) ) {
			$code = ($code) ?  $currency_infos->name : $currency_infos->unit;
			return $code;
		}
		else {
			return '';
		}
	}

	/** Return the shop currency */
	function wpshop_get_sigle($code, $column_to_return = "unit") {
		$tmp_code = (int)$code;
		$key_to_get = 'name';
		if ( is_int($tmp_code) && !empty($tmp_code) ) {
			$key_to_get = 'id';
		}
		$old_way_currencies = unserialize(WPSHOP_SHOP_CURRENCIES);
		if ( array_key_exists( $code, $old_way_currencies)) {
			$code = $old_way_currencies[$code];
			$key_to_get = 'name';
		}

		$current_currency = wpshop_attributes_unit::getElement($code, "'valid'", $key_to_get);

		return $current_currency->$column_to_return;
	}

	/**
	* Clean variables
	**/
	function wpshop_clean( $var ) {
		return trim(strip_tags(stripslashes($var)));
	}

	/**
	 * Validates a phone number using a regular expression
	 *
	 * @param   string	phone number
	 * @return  boolean
	 */
	function is_phone( $phone ) {
		if (strlen(trim(preg_replace('/[\s\#0-9_\-\+\(\)]/', '', $phone)))>0) return false;
		else return true;
	}

	/**
	 * Checks for a valid postcode
	 *
	 * @param   string	postcode
	 * @return  boolean
	 */
	function is_postcode($postcode) {
		if (strlen(trim(preg_replace('/[\s\-A-Za-z0-9]/', '', $postcode)))>0) return false;
		else return true;
	}

	/**
	*	Return a form field type from a database field type
	*
	*	@param string $dataFieldType The database field type we want to get the form field type for
	*
	*	@return string $type The form input type to use for the given field
	*/
	function defineFieldType($dataFieldType, $input_type, $frontend_verification){
		$type = 'text';

		if ( $dataFieldType == 'datetime' ) {
			$type = 'text';
		}
		else {
			switch ( $frontend_verification ) {
				case 'phone':
					$type = 'tel';
					break;
				case 'email':
					$type = 'email';
					break;
				default:
					$type = $input_type;
				break;
			}
		}
// 		if( ($dataFieldType == 'char') || ($dataFieldType == 'varchar') || ($dataFieldType == 'int') ){
// 			$type = 'text';
// 			if($input_type == 'password'){
// 				$type = 'password';
// 			}
// 			elseif($input_type == 'hidden') {
// 				$type = 'hidden';
// 			}
// 			elseif( $input_type == 'country' ){
// 				$type = 'country';
// 			}
// 		}
// 		elseif($dataFieldType == 'text'){
// 			$type = 'textarea';
// 		}
// 		elseif($dataFieldType == 'enum'){
// 				$type = 'select';
// 		}

		return $type;
	}



	/**
	 * Get the method through which the data are transferred (POST OR GET)
	 *
	 * @return array The different element send by request method
	 */
	function getMethode(){
		if ($_SERVER["REQUEST_METHOD"] == "GET")
			return $_GET;
		if ($_SERVER["REQUEST_METHOD"] == "POST")
			return $_POST;
		die ('Invalid REQUEST_METHOD (not GET, not POST).');
	}



	/**
	*	Transform a given text with a specific pattern, send by the second parameter
	*
	*	@param string $toSlugify The string we want to "clean" for future use
	*	@param array|string $slugifyType The type of cleaning we are going to do on the input text
	*
	*	@return string $slugified The input string that was slugified with the selected method
	*/
	function slugify($toSlugify, $slugifyType)
	{
		$slugified = '';

		if($toSlugify != '')
		{
			$slugified = $toSlugify;
			foreach($slugifyType as $type)
			{
				if($type == 'noAccent')
				{
					$pattern = array("/&eacute;/", "/&egrave;/", "/&ecirc;/", "/&ccedil;/", "/&agrave;/", "/&acirc;/", "/&icirc;/", "/&iuml;/", "/&ucirc;/", "/&ocirc;/", "/&Egrave;/", "/&Eacute;/", "/&Ecirc;/", "/&Euml;/", "/&Igrave;/", "/&Iacute;/", "/&Icirc;/", "/&Iuml;/", "/&Ouml;/", "/&Ugrave;/", "/&Ucirc;/", "/&Uuml;/","/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/", "/�/");
					$rep_pat = array("e", "e", "e", "c", "a", "a", "i", "i", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U","e", "e", "e", "c", "a", "a", "i", "i", "u", "o", "E", "E", "E", "E", "I", "I", "I", "I", "O", "U", "U", "U");
				}
				elseif($type == 'noSpaces')
				{
					$pattern = array('/\s/');
					$rep_pat = array('_');
					$slugified = trim($slugified);
				}
				elseif($type == 'lowerCase')
				{
					$slugified = strtolower($slugified);
				}
				elseif($type == 'noPunctuation')
				{
					$pattern = array("/#/", "/\{/", "/\[/", "/\(/", "/\)/", "/\]/", "/\}/", "/&/", "/~/", "/�/", "/`/", "/\^/", "/@/", "/=/", "/�/", "/�/", "/%/", "/�/", "/!/", "/�/", "/:/", "/\$/", "/;/", "/\./", "/,/", "/\?/", "/\\\/", "/\//");
					$rep_pat = array("_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_", "_");
				}

				if(is_array($pattern) && is_array($rep_pat))
				{
					$slugified = preg_replace($pattern, $rep_pat, utf8_decode($slugified));
				}
			}
	  }

	  return $slugified;
	}

	/**
	*	Trunk a string too long
	*
	*	@param string $string The string we want to "trunk"
	*	@param int $maxlength The max length of the result string
	*
	*	@return string $string The output string that was trunk if necessary
	*/
	function trunk($string, $maxlength) {
		if(strlen($string)>$maxlength+3)
			return substr($string,0,$maxlength).'...';
		else return $string;
	}

	/**
	 * Run a safe redirect in javascript
	 */
	function wpshop_safe_redirect($url='') {
		$url = empty($url) ? admin_url('admin.php?page='.WPSHOP_URL_SLUG_DASHBOARD) : $url;
		echo '<script type="text/javascript">window.top.location.href = "'.$url.'"</script>';
		exit;
	}

	/**
	 * Format a number before displaying it
	 * @deprecated
	 *
	 */
	function price( $price ) {
		return $price;
	}

	function create_custom_hook ($hook_name, $args = '') {
		ob_start();
		if ( !empty($args) ) {
			do_action($hook_name, $args);
		}
		else {
			do_action($hook_name);
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}


	/**
	 * Return a plug-in activation code
	 * @param string $plugin_name
	 * @param string $encrypt_base_attribute
	 * @return string
	 */
	function get_plugin_validation_code($plugin_name, $encrypt_base_attribute) {
		$code = '';
		$plug = get_plugin_data( WP_PLUGIN_DIR . '/' . WPSHOP_PLUGIN_DIR . '/wpshop.php' );
		$code_part = array();
		$code_part[] = substr(hash ( "sha256" , $plugin_name ), WPSHOP_ADDONS_KEY_IS, 5);
		$code_part[] = substr(hash ( "sha256" , $plug['Name'] ), WPSHOP_ADDONS_KEY_IS, 5);
		$code_part[] = substr(hash ( "sha256" , 'addons' ), WPSHOP_ADDONS_KEY_IS, 5);
		$code = $code_part[1] . '-' . $code_part[2] . '-' . $code_part[0];
		$att = $encrypt_base_attribute;
		$code .= '-' . substr(hash ( "sha256" , $att ),  WPSHOP_ADDONS_KEY_IS, 5);

		return $code;
	}


	function check_plugin_activation_code( $plugin_name, $encrypt_base_attribute) {
		$is_valid = false;
		$valid_activation_code = self::get_plugin_validation_code($plugin_name, $encrypt_base_attribute);
		$activation_code_filename = WP_PLUGIN_DIR .'/'. $plugin_name.'/encoder.txt';
		if ( is_file($activation_code_filename) ) {
			$activation_code_file = fopen($activation_code_filename, 'r' );
			if ( $activation_code_file !== false) {
				$activation_code = fread( $activation_code_file, filesize($activation_code_filename));
				if ( $activation_code == $valid_activation_code ) {
					$is_valid = true;
				}
			}
		}
		return $is_valid;
	}

}

/* Others tools functions */
function number_format_hack($n) {
	return number_format($n, 5, '.', '');
}
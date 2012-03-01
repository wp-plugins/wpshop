<?php
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
class wpshop_tools
{
	/**
	*	Define the tools main page
	*/
	function main_page(){
		echo wpshop_display::displayPageHeader(__('Outils du logiciel WP-Shop', 'wpshop'), '', __('Outils du logiciel', 'wpshop'), __('Outils du logiciel', 'wpshop'), false, '', '', '');
?>
<div id="wpshop_configurations_container" class="clear" >
	<div id="tools_tabs" >
		<ul>
			<li><a href="<?php echo WPSHOP_AJAX_FILE_URL; ?>?post=true&amp;elementCode=tools&amp;action=db_manager" title="wpshop_tools_tab_container" ><?php _e('V&eacute;rification de la base de donn&eacute;es', 'wpshop'); ?></a></li>
		</ul>
		<div id="wpshop_tools_tab_container" >&nbsp;</div>
	</div>
</div>
<script type="text/javascript" >
	wpshop(document).ready(function(){
		jQuery("#wpshop_tools_tab_container").html(jQuery("#round_loading_img").html());
		jQuery("#tools_tabs").tabs({
      select: function(event, ui){
				jQuery("#wpshop_tools_tab_container").html(jQuery("#round_loading_img").html());
				var url = jQuery.data(ui.tab, "load.tabs");
				jQuery("#wpshop_tools_tab_container").load(url);
				jQuery("#tools_tabs ul li").each(function(){
					jQuery(this).removeClass("ui-tabs-selected ui-state-active");
				});
				jQuery("#tools_tabs ul li:eq(" + ui.index + ")").addClass("ui-tabs-selected ui-state-active");
				return false;
      }
		});
	});
</script>
<?php
		echo wpshop_display::displayPageFooter();
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
	function varSanitizer($varToSanitize, $varDefaultValue = '', $varType = '')
	{
		$sanitizedVar = (trim(strip_tags(stripslashes($varToSanitize))) != '') ? trim(strip_tags(stripslashes(($varToSanitize)))) : $varDefaultValue ;

		return $sanitizedVar;
	}
	
	/** Return the shop currency */
	function wpshop_get_currency($code=false) {
		// Currency
		$wpshop_shop_currency = get_option('wpshop_shop_default_currency', WPSHOP_SHOP_DEFAULT_CURRENCY);
		$wpshop_shop_currencies = get_option('wpshop_shop_currencies', unserialize(WPSHOP_SHOP_CURRENCIES));
		return $code ? $wpshop_shop_currency : $wpshop_shop_currencies[$wpshop_shop_currency];
	}
	
	/** Return the shop currency */
	function wpshop_get_sigle($code) {
		// Currencies
		$wpshop_shop_currencies = get_option('wpshop_shop_currencies', unserialize(WPSHOP_SHOP_CURRENCIES));
		return $wpshop_shop_currencies[$code];
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
	*	Allows to copy an entire directory to another path
	*
	*	@param string $sourceDirectory The complete path we want to copy in an another path
	*	@param string $destinationDirectory The destination path that will receive the cpied content
	*
	*/
	function copyEntireDirectory($sourceDirectory, $destinationDirectory){
		if(is_dir($sourceDirectory)){
			if(!is_dir($destinationDirectory)){
				mkdir($destinationDirectory, 0755, true);
				exec('chmod -R 755 '.$destinationDirectory);
			}
			$hdir = opendir($sourceDirectory);
			while($item = readdir($hdir)){
				if(is_dir($sourceDirectory . '/' . $item) && ($item != '.') && ($item != '..')  && ($item != '.svn') ){
					self::copyEntireDirectory($sourceDirectory . '/' . $item, $destinationDirectory . '/' . $item);
				}
				elseif(is_file($sourceDirectory . '/' . $item)){
					@copy($sourceDirectory . '/' . $item, $destinationDirectory . '/' . $item);
				}
			}
			closedir( $hdir );
		}
	}

	/**
	*	Return a form field type from a database field type
	*
	*	@param string $dataFieldType The database field type we want to get the form field type for
	*
	*	@return string $type The form input type to use for the given field
	*/
	function defineFieldType($dataFieldType){
		$type = 'text';
		if(($dataFieldType == 'char') || ($dataFieldType == 'varchar') || ($dataFieldType == 'int')){
			$type = 'text';
		}
		elseif($dataFieldType == 'text'){
			$type = 'textarea';
		}
		elseif($dataFieldType == 'enum'){
			$type = 'select';
		}

		return $type;
	}
	
	/** Create un cutom message with $data array */
	function customMessage($string, $data) {
		$avant = array();
		$apres = array();
		foreach($data as $key => $value) {
			$avant[] = '['.$key.']';
			$apres[] = $value;
		}
		return str_replace($avant, $apres, $string);
	}
	
	/** Envoie un email personnalisé */
	function wpshop_prepared_email($email, $code_message, $data=array()) {
		$title = get_option($code_message.'_OBJECT', null);
		$title = empty($title) ? constant($code_message._OBJECT) : $title;
		$title = self::customMessage($title, $data);
		$message = get_option($code_message, null);
		$message = empty($message) ? constant($code_message) : $message;
		$message = self::customMessage($message, $data);
		/* On envoie le mail */
		self::wpshop_email($email, $title, $message, $save=true);
	}
	
	/** Envoie un mail */
	function wpshop_email($email, $title, $message, $save=true) {
		global $wpdb;
		
		// Sauvegarde
		if($save) {
			//$user = get_user_by('email', $email);
			$user = $wpdb->get_row('SELECT ID FROM '.$wpdb->users.' WHERE user_email="'.$email.'";');
			$user_id = $user ? $user->ID : 0;
			wpshop_messages::add_message($user_id, $email, $title, $message);
		}
		
		$emails = get_option('wpshop_emails', array());
		$noreply_email = $emails['noreply_email'];
		// Headers du mail
	    //$headers = 'From: '.get_bloginfo('name').' <'.$noreply_email.'>' . "\r\n";
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		//$headers .= "To: $vers_nom <$vers_mail>\r\n";
		$headers .= 'From: '.get_bloginfo('name').' <'.$noreply_email.'>' . "\r\n";
		// Mail en HTML
		return @mail($email, $title, nl2br($message), $headers);
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
					$pattern = array("/&eacute;/", "/&egrave;/", "/&ecirc;/", "/&ccedil;/", "/&agrave;/", "/&acirc;/", "/&icirc;/", "/&iuml;/", "/&ucirc;/", "/&ocirc;/", "/&Egrave;/", "/&Eacute;/", "/&Ecirc;/", "/&Euml;/", "/&Igrave;/", "/&Iacute;/", "/&Icirc;/", "/&Iuml;/", "/&Ouml;/", "/&Ugrave;/", "/&Ucirc;/", "/&Uuml;/","/é/", "/è/", "/ê/", "/ç/", "/à/", "/â/", "/î/", "/ï/", "/ù/", "/ô/", "/È/", "/É/", "/Ê/", "/Ë/", "/Ì/", "/Í/", "/Î/", "/Ï/", "/Ö/", "/Ù/", "/Û/", "/Ü/");
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
					$pattern = array("/#/", "/\{/", "/\[/", "/\(/", "/\)/", "/\]/", "/\}/", "/&/", "/~/", "/¤/", "/`/", "/\^/", "/@/", "/=/", "/£/", "/¨/", "/%/", "/µ/", "/!/", "/§/", "/:/", "/\$/", "/;/", "/\./", "/,/", "/\?/", "/\\\/", "/\//");
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
	
	function price($price) {
		return number_format($price,2,',',' ');
	}
	
	function wpshop_safe_redirect($url) {
		echo '<script type="text/javascript">window.top.location.href = "'.$url.'"</script>';
		exit;
	}

}

/* Others tools functions */
function number_format_hack($n) {
	return number_format($n, 5, '.', '');
}
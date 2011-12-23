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
	function defineFieldType($dataFieldType)
	{
		$type = 'text';
		if(($dataFieldType == 'char') || ($dataFieldType == 'varchar') || ($dataFieldType == 'int'))
		{
			$type = 'text';
		}
		elseif($dataFieldType == 'text')
		{
			$type = 'textarea';
		}
		elseif($dataFieldType == 'enum')
		{
			$type = 'select';
		}

		return $type;
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
	
	function price($price) {
		return number_format($price,2,',',' ');
	}

}
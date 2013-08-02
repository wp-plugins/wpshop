<?php

/**
 * Plugin Name: Google_map
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: This plug-in can be used for managed the inclusion and the display of google map api
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * Google map bootstrap file
 * @author Mabileau Clément - Eoxia dev team trainee <dev@eoxia.com>
 * @version 0.1
 * @package includesimage_path
 * @subpackage modules
 * 
 */

if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}
/*	Check if file is include. No direct access possible with file url	*/
if ( !class_exists("wps_google_map") ) {
	class wps_google_map{
		function __construct() {
			/**	Add custom template for current module	*/
			wp_enqueue_script('wp_google_map_js', plugins_url('/templates/backend/js/wpshop_google_map.js', __FILE__) );
			add_action('init' , array($this,'enqueue_style') );
		}

		function enqueue_style () {
			wp_register_style('map_style', plugins_url('templates/backend/css/wpshop_google_map.css', __FILE__) );
			wp_enqueue_style('map_style');
		}


		/**
		 * Generate the GPS coord. from an address
		 * @param unknown_type $adresse
		 * @return array $adresse:
		 */

		function return_coord_from_address($address)
		{
			$google_map_key = get_option('wpshop_google_map_api_key');
				$address = urlencode($address);
				$url = 'http://maps.google.com/maps/api/geocode/xml?address=' . $address . '&sensor=true';
				$page = file_get_contents($url);
				$xml_result = new SimpleXMLElement($page);
				if ($xml_result->status != 'OK') return array();
				else {
					$adresses = array();
					$adresses['lat'] = (string) $xml_result->result->geometry->location->lat;
					$adresses['lng'] = (string) $xml_result->result->geometry->location->lng;
				}
			return $adresses;
		}
    	function prepare_address_and_get_coord($user_info)
    	{
	    	$coord = $result = $error = $result_return = array();
	    	$index = 0;
	    	foreach ($user_info as $i => $user) {
		    	$main_address = (!empty($user->user_adress) ? $user->user_adress : '');
		    	$more_address = (!empty($user->user_more_adress) ? $user->user_more_adress : '');
		    	$code_p = (!empty($user->user_code_p) ? $user->user_code_p : '');
		    	$city = (!empty($user->user_city) ? $user->user_city : '');
		    	$country = (!empty($user->user_country) ? $user->user_country : 'France');
		    	if((!empty($code_p) || !empty($city)) && (!empty($main_address))) {
		    		$address = $main_address . " " . $more_address . " " . $code_p  . " " . $city . " " . $country;
			    	$coord =  $this->return_coord_from_address($address);
			    	//IMPORTANT ! Google have a limited amount of request by secondes
					usleep(5000);
					if(!empty($coord['lat']) && (!empty($coord['lng']))){
				    	$result[$index]['lat'] = (!empty($coord['lat']) ? $coord['lat'] : '');
				    	$result[$index]['lng'] = (!empty($coord['lng']) ? $coord['lng'] : '');
				    	$sanitize_address = addslashes($address);
				    	$result[$index]['address'] = $sanitize_address;
			    		$index++;
					}
		    	}
				else{
					$error[$i] = new stdClass();
					$error[$i]->id = $user->id;
					$error[$i]->name = $user->name;
				}
		    }
		    $moy = $this->get_center_localisation($result);
		    $result[$index]['moy']['lat'] = $moy['lat'];
		    $result[$index]['moy']['lng'] = $moy['lng'];
		    $result_return['array'] = $result;
		    $result_return['error'] = $error;
		    return $result_return;
    	}
    	function get_center_localisation($array){
    		$result = array();
    		$somme_lat = '';
    		$somme_lng = '';
    		$size = sizeof($array);
    		for($i = 0 ; $i < $size ; $i ++){
    			$somme_lat += $array[$i]['lat'];
    			$somme_lng += $array[$i]['lng'];
    		}
    		$result['lat'] = ($somme_lat / $size);
    		$result['lng'] = ($somme_lng / $size);
    		return $result;
    	}
    	function display_map_element($user_info){
    		$content = '
    		<div id="map" style="width:96%; height:400px">
					<div style="background:#888;">
						<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>';

			            	$user_info = json_encode($user_info);
			            	$user_info = preg_replace("~(\\\\)+~", '\\', $user_info);

			$content .= "<script type='text/javascript'>
							 display_google_map('".$user_info."');
						</script>
					</div>
				</div>";
			echo $content;
    	}
    	// All-in-one function
    	function make_map($user_info){
    		$coord = array();
    		$coord = $this->prepare_address_and_get_coord($user_info);
    		display_map_element($coord);
    	}
	}
}
if (class_exists("wps_google_map"))
{
	$inst_wps_google_map = new wps_google_map();
}	
?>
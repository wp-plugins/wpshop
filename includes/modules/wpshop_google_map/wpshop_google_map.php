<?php

/**
 * Plugin Name: wpshop_google_map
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
 * @package includes
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
			add_action('init' , array($this,'enqueue_style') );
			add_action('init', array($this,'include_js'));
		}

		function include_js(){
			wp_enqueue_script('wps_google_map',plugins_url('/templates/backend/js/wpshop_google_map.js', __FILE__));
		}
		function enqueue_style () {
			wp_register_style('map_style', plugins_url('templates/backend/css/wpshop_google_map.css', __FILE__) );
			wp_enqueue_style('map_style');
		}

		/**
		 * Generate the GPS coord. from an address using google API
		 * @param string $adresse
		 * @return array $adresse
		 */
		function return_coord_from_address( $address ) {
			$address = urlencode($address);
			$url = 'http://maps.google.com/maps/api/geocode/xml?address=' . $address . '&sensor=true';
			$page = file_get_contents($url);
			$xml_result = new SimpleXMLElement( $page );
			if ($xml_result->status != 'OK') {
				return array();
			}
			else {
				$adresses = array();
				$adresses['latitude'] = (string) $xml_result->result->geometry->location->lat;
				$adresses['longitude'] = (string) $xml_result->result->geometry->location->lng;
			}

			return $adresses;
		}
		/**
		* This function check and return gps coord if they exist
		*@param int $attachment_id, id of an attached address
		*@param int $attr_lat, The id of latitude attribute
		*@param int $attr_lng, The id of longitude attribute
		*@return array, desired latitude and longitude
		*/
		function check_existing_coord($attachment_id,$attr_lat,$attr_lng){
			global $wpdb;
			$results = null;
			$query = $wpdb->prepare("SELECT value, attribute_id
									 FROM ".$wpdb->prefix."wpshop__attribute_value_varchar
									 WHERE entity_id = %d
									 AND (attribute_id = %d
									 OR attribute_id = %d)
									 GROUP BY attribute_id",$attachment_id, $attr_lat, $attr_lng);
			$data = $wpdb->get_results($query);
			if(!empty($attr_lng) && !empty($attr_lat) && !empty($data)) {
				foreach ($data as $coord) {
					if($coord->attribute_id == $attr_lat && !empty($coord->value))
						$results['lat'] = $coord->value;
					if($coord->attribute_id == $attr_lng && !empty($coord->value))
						$results['lng'] = $coord->value;
				}
			}
			return $results;
		}

		/**
		* This function return the id of the wpshop_address based on his post_parent
		*@param int $post_id, id of the wp_post parent of desired wpshop_address post
		*@return int $id, id of the wpshop_address
		*/
		function get_attachement_id ($post_id) {
			global $wpdb;
			$query = $wpdb->prepare('SELECT ID FROM '.$wpdb->posts.' WHERE post_parent = %d and post_type = "wpshop_address"',$post_id);
			$id = $wpdb->get_var($query);
			return $id;
		}

		/**
		* This function update or create the gps coord in database
		*@param int $attachment_id, id of the entity to update
		*@param int $attribute_lat, id of attribute latitude
		*@param int $attribute_lng, id of attribute longitude
		*@param int $lat_value, latitude value to set
		*@param int $lng_value, longitude value to set
		*/
		function update_coord($attachment_id,$attribute_lat,$attribute_lng,$lat_value,$lng_value){
			global $wpdb;
			$wpdb->replace($wpdb->prefix."wpshop__attribute_value_varchar",
									array('value' => $lat_value, 'entity_id' => $attachment_id, 'attribute_id' =>$attribute_lat)
							);

			$wpdb->replace($wpdb->prefix."wpshop__attribute_value_varchar",
									array('value' =>$lng_value, 'entity_id' => $attachment_id, 'attribute_id'=>$attribute_lng)
							);
		}
		/**
		* Main function, treat users data, check and get their gps coord, put aside invalid address and prepare the data for js display function
		*@param array $user_info, containing stdClass $user, containing id, address, more_address, code_p, city, name, country..
		*@return array in the shape of js function display_google_map expectation
		*/
		function prepare_address_and_get_coord($user_info)
		{
			$coord = $result = $error = $result_return = array();
			$index = 0;
			$attr_latitude= wpshop_attributes::getElement('latitude',"'valid'","code");
			$attr_latitude_id = (!empty($attr_latitude->id) ? $attr_latitude->id : '');
			$attr_longitude = wpshop_attributes::getElement('longitude',"'valid'","code");
			$attr_longitude_id = (!empty($attr_longitude->id) ? $attr_longitude->id : '' );

			foreach ($user_info as $i => $user) {
				$attachment_id = $this->get_attachement_id($user->id);
				$main_address = (!empty($user->user_adress) ? $user->user_adress : '');
				$more_address = (!empty($user->user_more_adress) ? $user->user_more_adress : '');
				$code_p = (!empty($user->user_code_p) ? $user->user_code_p : '');
				$city = (!empty($user->user_city) ? $user->user_city : '');
				$country = (!empty($user->user_country) ? $user->user_country : 'France');
				if((!empty($code_p) || !empty($city)) && (!empty($main_address))) {
					$address = $main_address . " " . $more_address . " " . $code_p  . " " . $city . " " . $country;
					$coord = $this->check_existing_coord($attachment_id,$attr_latitude_id,$attr_longitude_id);
					if(empty($coord)){
						$coord =  $this->return_coord_from_address($address);
						if(!empty($coord['lat']) && !empty($coord['lng']))
							$this->update_coord($attachment_id,$attr_latitude_id,$attr_longitude_id,$coord['lat'],$coord['lng']);
						else{
							$error[$i] = new stdClass();
							$error[$i]->id = $user->id;
							$error[$i]->name = $user->name;
			    		}
					}
					if(!empty($coord['lat']) && (!empty($coord['lng']))){
				    	$result[$index]['lat'] = (!empty($coord['lat']) ? $coord['lat'] : '');
				    	$result[$index]['lng'] = (!empty($coord['lng']) ? $coord['lng'] : '');
				    	$sanitize_address = addslashes($user->name . "<br>" .$address);
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

		/**
		* This function make the average coords based on multiple user coords, with the aim of centering the map on them
		*@param array $array, containing a series of latitude and longitude
		*@return coord for centering the map
		*/
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
		/**
		*This function make the link with javascript function display_google_map by serializing the data
		*@param array $user_info, in the expected shape of js function
		*@return an echo of the js code required to display the map
		*/
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
		/**
		*One-call function, does all the job for display the map based on users data and previous functions
		*@param array $user_info
		*@return echo of the js code required to display the google map
		*/
		function make_map($user_info){
			$coord = array();
			$coord = $this->prepare_address_and_get_coord($user_info);
			$this->display_map_element($coord);
		}
	}
}
if (class_exists("wps_google_map"))
{
	$inst_wps_google_map = new wps_google_map();
}
?>
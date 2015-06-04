<?php

/**
 * Main controller file for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeologs
 * @subpackage controller
 */

/**
 * Main controller class for WP logs module
 *
 * @author Eoxia development team <dev@eoxia.com>
 * @version 1.0
 * @package wpeologs
 * @subpackage controller
 */

if(!class_exists("wpeologs_ctr")) {
	class wpeologs_ctr {

		/**	Define the global class variable for settings	*/
		public $wpeologs_settings = array();

		/**	Define the var containing directory name to logs	*/
		var $log_directory;

		static $file_separator = "!#logsep#!";

		public $array_criticality = array();

		/** Default size file 10 mo **/
		static $default_size_file = 10240;

		/**
		 * construct - Initialize array criticality, add filter for content save pre and admin_enqueue_scripts
		 */
		public function __construct() {
			$this->init_array_criticality();

			/**	Filter action on save post before saving, in order to log saved datas	*/
			 add_filter( 'content_save_pre', array( &$this, 'content_logger' ) );

			/**	Add metaboxes to custom post type	*/
			// add_action( 'add_meta_boxes', array( &$this, 'admin_metaboxes' ) );

			/**	Call administration style definition	*/
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );
			/** When switch theme */
			add_action( 'switch_theme', array( &$this, 'switch_theme' ) );
			/** get all plugins active */
// 			$array_plugins = get_option("active_plugins");
// 			foreach($array_plugins as $plugin) {
// 				/** register_activation_hook */
// 				register_activation_hook($plugin, array( &$this, "register_activation_hook" ) );
// 				/** register_deactivation_hook */
// 				register_deactivation_hook($plugin, array( &$this, "register_deactivation_hook" ) );
// 			}
			add_action( 'activate_plugin', array( &$this, 'activated_plugin' ), 10, 2 );
			add_action( 'deactivate_plugin', array( &$this, 'deactivated_plugin' ), 10, 2 );
			//echo"<pre>";print_r($array_plugins);echo"</pre>";
		}

		/**
		 * new_instance - initialize new instance and get settings, then return it
		 * @return wpeologs_ctr
		 */
		public static function new_instance() {
			$i = new wpeologs_ctr();
			$i->get_settings();

			return $i;
		}

		/**
		* init_array_criticality
		*/
		private function init_array_criticality() {
			/** init array */
			$this->array_criticality[0] = array(
				'dashicons' => 'info',
				'message' 	=> __('Information', 'wpeologs-i18n')
			);

			$this->array_criticality[1] = array(
				'dashicons' => 'carrot',
				'message' 	=> __('Warning', 'wpeologs-i18n')
			);

			$this->array_criticality[2] = array(
				'dashicons' => 'no',
				'message' 	=> __('Critical', 'wpeologs-i18n')
			);
		}

		/**
		 * WORDPRESS HOOK - ADMIN STYLES - Load the different css librairies
		 */
		function admin_scripts() {
			wp_register_style( 'wpeologs_backend_css', WPEO_LOGS_URL . '/assets/css/backend.css', '', WPEO_LOGS_VERSION);
			wp_enqueue_style( 'wpeologs_backend_css');

			wp_enqueue_style( 'dashicons' );

			wp_register_script( 'wpeologs_chart_js', WPEO_LOGS_URL . '/assets/js/chart.js');
			wp_enqueue_script( 'wpeologs_chart_js' );

			wp_register_script( 'wpeologs_backend_js', WPEO_LOGS_URL . '/assets/js/backend.js');
			wp_enqueue_script( 'wpeologs_backend_js' );
		}

		/**
		 * WORDPRESS HOOK - METABOXES CALL - Display metaboxes to selected element
		 */
		function admin_metaboxes() {

// 			/** Check if wpeo_log_select_post_type exist */
// 			if( !empty(	$this->wpeologs_settings['post_type'] ) ) {
// 				foreach ( $this->wpeologs_settings['post_type'] as $post_type ) {

// 					//add_meta_box( 'wpeologs_metabox_' . $post_type, __( 'Logs', 'wpeologs-i18n'), array( &$this, 'associated_post_type_metabox' ), $post_type, 'normal' );
// 				}
// 			}
		}

		/**
		 * DEBUG LOG - Save a file on the server with content for loggin different action sended
		 *
		 * @param string $service (Name module or post type)
		 * @param array $array_message ('object_id', 'message')
		 * @param int $criticality The message crit rate (0-2)
		 */
		public static function log_datas_in_files( $service, $array_message, $criticality ) {
			// backline
			$upload_dir = wp_upload_dir();

			wp_mkdir_p( $upload_dir[ 'basedir' ] . '/wpeologs/' );

			$message = "
	";
			$message .= current_time('mysql', 0) . self::$file_separator;
			$message .= get_current_user_id() . self::$file_separator;
			$message .= '"' . $service . '"' . self::$file_separator;
			$message .= $array_message['object_id'] . self::$file_separator;

			// For post type
			if(!empty($array_message['previous_element'])) {
				$message .= '"' . base64_encode(serialize($array_message['previous_element'])) . '"' . self::$file_separator;
				$message .= '"' . base64_encode(serialize($array_message['previous_element_metas'])) . '"' . self::$file_separator;
			}

			$message .= '"' . $array_message['message'] . '"' . self::$file_separator;
			$message .= $criticality . self::$file_separator . $service;

			$i = self::new_instance();

			if(empty($i->wpeologs_settings['my_services'][$service]) ||
				(!empty($i->wpeologs_settings['my_services'][$service]) && !empty($i->wpeologs_settings['my_services'][$service]['service_rotate']) && $i->wpeologs_settings['my_services'][$service]['service_rotate']))
				self::check_need_rotate($service, $message);

			$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeologs/' . $service . '.csv', 'a');
			fwrite($fp, $message);
			fclose($fp);

			if(2 <= $criticality) {
				$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeologs/_wpeo-critical.csv', 'a');
				fwrite($fp, $message);
				fclose($fp);
			}
			else if(1 == $criticality) {
				$fp = fopen( $upload_dir[ 'basedir' ] . '/wpeologs/_wpeo-warning.csv', 'a');
				fwrite($fp, $message);
				fclose($fp);
			}
		}

		/**
		* check_need_rotate  Checks if the file exceeds the maximum size
		*
		* @param string $file_link The file path to write
		*/
		public static function check_need_rotate( $service, $message ) {
			$upload_dir = wp_upload_dir();
			$i = self::new_instance();

			// Check if this service exist
			$max_size = !empty($i->wpeologs_settings['my_services'][$service]['service_size']) && $i->wpeologs_settings['my_services'][$service]['service_active'] ? $i->wpeologs_settings['my_services'][$service]['service_size'] : 8086;

			$file_link = $upload_dir[ 'basedir' ] . '/wpeologs/' . $service . '.csv';

			if( file_exists( $file_link ) ) {
				// Get full message
				$message = file_get_contents($file_link) . $message;

				$file_size = filesize($file_link);

				if($file_size >= $max_size)
					self::rename_current_file($service, $file_link);
				else if(strlen($message) >= $max_size)
					self::rename_current_file($service, $file_link);
					return $file_link;
			}

		}

		/**
		 * rename_current_file - Rename the current file
		 *
		 * @param string $service
		 * @param string $file_link
		 */
		public static function rename_current_file($service, $file_link) {
				$upload_dir = wp_upload_dir();
				$i = self::new_instance();

				$number_archive = !empty($i->wpeologs_settings['my_services'][$service]['service_file']) && $i->wpeologs_settings['my_services'][$service]['service_active'] ? $i->wpeologs_settings['my_services'][$service]['service_file'] : 3;
				if( file_exists ( $file_link ) ) {
					$file_explode = explode('.', $file_link);
					$get_all_file = glob($file_explode[0] . '*.csv');
					array_shift($get_all_file);
					arsort($get_all_file);

					foreach($get_all_file as $full_file) {
						$file = explode('/', $full_file);
						$file_name = $file[count($file) - 1];
						$file_name = explode('.', $file_name);

						$file_name[0]++;
						rename($full_file, $upload_dir[ 'basedir' ] . '/wpeologs/' . $file_name[0] . '.csv');

						// Check if not execeed the number archive
						$count = explode('_', $file_name[0]);

						if($count[1] > $number_archive && file_exists($upload_dir[ 'basedir' ] . '/wpeologs/' . $file_name[0] . '.csv')) {
							unlink($upload_dir[ 'basedir' ] . '/wpeologs/' . $file_name[0] . '.csv');
						}
					}
					rename( $file_link, $file_explode[0] . '_1.' . $file_explode[1]);

					//self::log_datas_in_files("rotate", array("object_id" => 1, "message" => "Rotate"), 0);
				}
		}

		/**
		*	SETTINGS - Retrieve settings for the module
		*/
		public function get_settings() {
			$my_option = get_option( '_wpeo_log_settings', array() );
			$this->wpeologs_settings = array_merge( $this->wpeologs_settings, (array)$my_option );
		}

		/**
		 * Log informations for setted type when post is saved
		 */
		function content_logger( $content ) {
			remove_filter('content_save_pre', array( &$this, 'content_logger' ) );
			if ( !empty( $_POST ) && !empty( $_POST[ 'post_type' ] ) && !empty($this->wpeologs_settings['my_services']) && !empty($this->wpeologs_settings['my_services'][$_POST['post_type']]) && $this->wpeologs_settings['my_services'][$_POST['post_type']]['service_active']) {
				 $previous_element = get_post( $_POST[ 'post_ID' ] );
				 $previous_element_metas = get_post_meta( $_POST[ 'post_ID' ] );

				if ( !empty( $previous_element ) ) {
					wpeologs_ctr::log_datas_in_files($_POST['post_type'],
						array('object_id' => $_POST['post_ID'],
								'previous_element' => $previous_element,
								'previous_element_metas' => $previous_element_metas ),
								$_POST['post_type'], WPEO_CRITICALITY_INFORMATIONS );
				}
			}

			return $content;
		}

		/**
		 * install_service - insert sql to option _wpeo_log_settings
		 */
		public function install_service() {
			$option_name = "_wpeo_log_settings";
			$option_value = 'a:2:{s:11:"my_services";a:4:{s:4:"post";a:6:{s:14:"service_active";i:1;s:12:"service_name";s:4:"post";s:12:"service_size";i:8192;s:19:"service_size_format";s:2:"ko";s:12:"service_file";s:1:"5";s:14:"service_rotate";i:1;}s:4:"page";a:6:{s:14:"service_active";i:1;s:12:"service_name";s:4:"page";s:12:"service_size";i:8192;s:19:"service_size_format";s:2:"ko";s:12:"service_file";s:1:"5";s:14:"service_rotate";i:1;}s:6:"themes";a:6:{s:14:"service_active";i:1;s:12:"service_name";s:6:"themes";s:12:"service_size";i:8192;s:19:"service_size_format";s:2:"ko";s:12:"service_file";s:1:"5";s:14:"service_rotate";i:1;}s:7:"plugins";a:6:{s:14:"service_active";i:1;s:12:"service_name";s:7:"plugins";s:12:"service_size";i:8192;s:19:"service_size_format";s:2:"ko";s:12:"service_file";s:1:"5";s:14:"service_rotate";i:1;}}s:9:"file_size";i:0;}';

			// Insert in wp_options
			$option = get_option($option_name);

			if(empty($option))
				update_option($option_name, unserialize($option_value));
		}

		/**
		 * switch_theme - When switch theme
		 * @param string $name_theme - The name of theme
		 */
		public function switch_theme($name_theme) {
			$i = self::new_instance();
			if(!empty($i->wpeologs_settings['my_services']["themes"]) && $i->wpeologs_settings['my_services']["themes"]['service_active'])
				self::log_datas_in_files("themes", array("object_id" => $name_theme, "message" => __('The theme ' . $name_theme . ' is activated')), 0);
		}
		/**
		 * activate_plugins_loaded - call log_datas_in_files for all plugins activate
		 */
		public function activated_plugin($plugin, $network_activation) {
			$i = self::new_instance();
			if(!empty($i->wpeologs_settings['my_services']["plugins"]) && $i->wpeologs_settings['my_services']["plugins"]['service_active'])
				self::log_datas_in_files("plugins", array("object_id" => $plugin, "message" => __('The plugin ' . $plugin . ' is activated')), 0);
		}

		public function deactivated_plugin($plugin, $network_activation) {
			$i = self::new_instance();
			if(!empty($i->wpeologs_settings['my_services']["plugins"]) && $i->wpeologs_settings['my_services']["plugins"]['service_active'])
				self::log_datas_in_files("plugins", array("object_id" => $plugin, "message" => __('The plugin ' . $plugin . ' is deactivated')), 0);
		}
	}
}

?>
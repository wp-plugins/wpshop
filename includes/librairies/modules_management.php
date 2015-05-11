<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

class wpshop_modules_management {
	function __construct() {

	}

	/**
	 * CORE - Install all extra-modules in "Modules" folder
	 */
	public static function core_utils() {
		/**	Define the directory containing all exrta-modules for current plugin	*/
		$module_folder = WPSHOP_DIR . '/core/';

		/**	Check if the defined directory exists for reading and including the different modules	*/
		if( is_dir( $module_folder ) ) {
			$parent_folder_content = scandir( $module_folder );
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr( $folder, 0, 1) != '.' ) {
					$child_folder_content = scandir( $module_folder . $folder );
					if ( file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
						$f =  $module_folder . $folder . '/' . $folder . '.php';
						include( $f );
					}
				}
			}
		}
	}

	/**
	 * This function is called to include wpshop's activated modules
	 */
	function include_activated_modules () {
		self::scan_modules_folder();
		$modules_option = get_option('wpshop_modules');
		if ( !empty($modules_option) ) {
			foreach ( $modules_option as $k => $module ) {
				if ($module['activated'] == 'on') {
					if ( file_exists(WPSHOP_MODULES_DIR.$k.'/'.$k.'.php') ) {
						include_once(WPSHOP_MODULES_DIR.$k.'/'.$k.'.php');
					}
				}
			}
		}
	}


	/**
	 * Scan the modules Folder and save all modules in options
	 */
	function scan_modules_folder () {
		$module_folder = WPSHOP_MODULES_DIR;
		$parent_folder_content = '';
		if ( file_exists($module_folder) ) {
			$parent_folder_content = scandir($module_folder);
			foreach ( $parent_folder_content as $folder ) {
				if ( $folder && substr(  $folder, 0, 1) != '.' ) {
					$child_folder_content = scandir( $module_folder.$folder );
					if ( file_exists($module_folder.$folder.'/'.$folder.'.php') ) {
						self::check_module_exist( $folder );
					}
				}
			}
		}
	}

	/**
	 * Check if the module is already register and register it if it isn't do
	 */
	function check_module_exist ( $module_name ) {
		$modules_option = get_option('wpshop_modules');
		if ( empty($modules_option) || ( !empty($modules_option) && !in_array($module_name, $modules_option) ) ) {
			$modules_option[ $module_name ] = array('activated' => 'on', 'date_on' => gmdate ( "Y-m-d H:i:s", time() ), 'date_off' => '');
			update_option('wpshop_modules', $modules_option);
		}
// 		elseif ( empty($modules_option) ) {
// 			$modules_option[ $module_name ] = array('activated' => 'on', 'date_on' => gmdate ( "Y-m-d H:i:s", time() ), 'date_off' => '');
// 			update_option('wpshop_modules', $modules_option);
// 		}
	}
}
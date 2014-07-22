<?php
/**
 * Main plugin configuration file
 *
 * @author Developpment team <dev@eoxia.com>
 * @version 1.0
 * @package config
 * @subpackage configuration
 */

/** Define config librairies directory */
DEFINE( 'WPWISHLIST_CONFIG_LIBS_DIR', WPSHOP_MODULES_DIR . '/' . WPWISHLIST_DIR . '/config/');

/** Define model librairies directory */
DEFINE( 'WPWISHLIST_MODEL_LIBS_DIR', WPSHOP_MODULES_DIR . '/' . WPWISHLIST_DIR . '/model/');

/** Define controller librairies directory */
DEFINE( 'WPWISHLIST_CONTROLLER_LIBS_DIR', WPSHOP_MODULES_DIR . '/' . WPWISHLIST_DIR . '/controller/');

/** Define frontend librairies directory */
DEFINE( 'WPWISHLIST_TEMPLATES_DIR', WPSHOP_MODULES_DIR  . WPWISHLIST_DIR . '/templates/');
DEFINE( 'WPWISHLIST_FRONTEND_LIBS_DIR', WPSHOP_MODULES_DIR  . WPWISHLIST_DIR . '/templates/frontend/');
DEFINE( 'WPWISHLIST_FRONTEND_LIBS_URL', WPSHOP_INCLUDES_URL . 'modules' . '/' . WPWISHLIST_DIR . '/templates/frontend/');

/** Define pictures directory */
DEFINE( 'WPWISHLIST_PICTURES_URL', WPWISHLIST_FRONTEND_LIBS_URL . 'pictures/');
DEFINE( 'WPWISHLIST_PICTURES_DIR', WPWISHLIST_FRONTEND_LIBS_DIR . 'pictures/');

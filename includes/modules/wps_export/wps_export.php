<?php 
/**
 Plugin Name: Export Client
 Description: Export your clients list
 */
/**
 * Base file
 * @author Sanchez Cédric
 * @version 0.1
 */
 
include( plugin_dir_path( __FILE__ ).'/controller/exportclientctr.php' );
include( plugin_dir_path( __FILE__ ).'/model/exportclientmdl.php' );
include ( plugin_dir_path( __FILE__ ).'/include/exportdisplay.php' );
$exportclientctr = new exportclientctr();
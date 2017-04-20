<?php
$a = 'high';
/*
Plugin Name: snax-mod
Plugin URI: http://ekojr.com/
Description: Extension to the Slax plugin.
Version: 1.6.1
Author: EkoJR
Author URI: https://profiles.wordpress.org/ekojr
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: snax-mod
Domain Path: /lang
*/

/*if ( ! class_exists( 'snax_mod_core' )) {
	require_once(plugin_dir_path(__FILE__) . 'includes/class-snax-mod-core.php');
	$advanced_post_list = new APL_Core(__FILE__);
}*/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

require_once( plugin_dir_path(__FILE__) . 'includes/class-snax-mod-core.php' );
snax_mod_core::get_instance();


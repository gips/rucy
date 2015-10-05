<?php
/*
 * Plugin Name: Rucy
 * Plugin URI: https://github.com/gips/rucy
 * Description: Reservation Update "Published" Content(Post & Page).
 * Version: 0.3.0
 * Author: Nita
 * License: GPLv2 or later
 * Text Domain: rucy
 * Domain Path: /lang
 */
define( 'RC_PLUGIN_URL',  plugin_dir_url(__FILE__) );
define( 'RC_PLUGIN_DIR',  untrailingslashit( dirname( __FILE__ ) ) );
define( 'RC_SETTING_OPTION_KEY', 'rucy_post_type' );
define( 'RC_TXT_DOMAIN', 'rucy' );
define( 'RC_CRON_HOOK', 'rucy_update_reserved_content' );
load_plugin_textdomain( RC_TXT_DOMAIN, false, 'rucy/lang' );

class Rucy_Class {
    public $support_post_type = array();

    public function __construct() {
        register_activation_hook( plugin_basename(__FILE__), array( $this, 'activate_plugin' ) );
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_style_script' ));
        add_action( 'wp_admin', array( $this, 'enqueue_pointer_menu' ) );
    }
    
    public function activate_plugin() {
        $this->support_post_type = $this->get_support_post_type();
    }
    
    public function get_support_post_type() {
        $res = get_option( RC_SETTING_OPTION_KEY );
        if( !$res ) {
            $res = "post,page";
            update_option(RC_SETTING_OPTION_KEY, $res);
        }
        $this->support_post_type = explode(",", $res);
        return $res;
    }
    
    /**
     * load css and js for this plugin.
     * 
     * @global type $hook_suffix
     */
    public function enqueue_style_script() {
        global $hook_suffix;
        if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) {
            wp_register_style('rucy.css', RC_PLUGIN_URL . 'css/rucy.css',array(),'0.1.0');
            wp_register_script('rucy.js', RC_PLUGIN_URL . 'js/rucy.js', array('jquery'), '0.1.0');
            wp_enqueue_style('rucy.css');
            wp_enqueue_script('rucy.js');
        }
    }
    
    /**
     * load enqueue script and style for pointer
     */
    public function enqueue_pointer_menu() {
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'wp-pointer' );
    }
}
new Rucy_Class();
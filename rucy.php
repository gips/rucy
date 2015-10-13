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
define( 'RC_SETTING_UPDATE', 'rucy_setting_update' );
define( 'RC_SETTING_ERROR', 'rucy_setting_error' );
load_plugin_textdomain( RC_TXT_DOMAIN, false, 'rucy/lang' );

require_once RC_PLUGIN_DIR . '/inc/class-rucy-component.php';
require_once RC_PLUGIN_DIR . '/inc/class-rucy-setting.php';
require_once RC_PLUGIN_DIR . '/inc/class-rucy-editor.php';

class Rucy_Class {
    public $support_post_type = array();
    
    public function __construct() {
        register_activation_hook( plugin_basename(__FILE__), array( $this, 'activate_plugin' ) );
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_style_script' ));
        add_action( 'admin_menu', array( $this, 'enqueue_pointer_menu' ) );
        $setting = new Class_Rucy_Setting();
        add_action( 'admin_menu', array( $setting, 'set_admin_menu' ) );
        add_action( 'admin_notices', array( $setting, 'set_admin_notices' ) );
        $editor = new Class_Rucy_Editer();
        add_action( 'admin_menu', array( $editor, 'add_rucy_metabox' ) );
        add_action( 'save_post', array( $editor, 'save_rc_post_meta' ) );
    }
    
    public function activate_plugin() {
        $this->init_support_post_type();
    }
    
    public function init_support_post_type() {
        $component = new Class_Rucy_Component();
        $this->support_post_type = $component->get_support_post_type();
        $component->update_support_post_type( $this->support_post_type );
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
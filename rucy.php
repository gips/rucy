<?php
/*
 * Plugin Name: Rucy
 * Plugin URI: https://github.com/gips/rucy
 * Description: Reservation Update "Published" Content(Post & Page).
 * Version: 0.4.4
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
define( 'RC_VERSION', '0.4.0' );
load_plugin_textdomain( RC_TXT_DOMAIN, false, 'rucy/lang' );

require_once RC_PLUGIN_DIR . '/inc/class-rucy-component.php';
require_once RC_PLUGIN_DIR . '/inc/class-rucy-setting.php';
require_once RC_PLUGIN_DIR . '/inc/class-rucy-editor.php';
require_once RC_PLUGIN_DIR . '/inc/class-rucy-cron.php';

class Rucy_Class {
    public $support_post_type = array();
    
    public function __construct() {
        register_activation_hook( plugin_basename(__FILE__), array( $this, 'activate_plugin' ) );
        add_action( 'admin_init', array( $this, 'rucy_upgrade' ) );
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_style_script' ));
        add_action( 'admin_menu', array( $this, 'enqueue_pointer_menu' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_setting_link' ) );
        $setting = new Class_Rucy_Setting();
        add_action( 'admin_menu', array( $setting, 'set_admin_menu' ) );
        $editor = new Class_Rucy_Editer();
        add_action( 'admin_menu', array( $editor, 'add_rucy_metabox' ) );
        add_action( 'save_post', array( $editor, 'save_rc_post_meta' ) );
        add_filter( 'post_updated_messages', array( $editor, 'add_reservation_message' ) );
        $component = new Class_Rucy_Component();
        $accept_post_types = $component->get_support_post_type();
        foreach ( $accept_post_types as $p ) {
            if ( in_array( $p, array( 'page', 'post' ) ) ) {
                $p .= 's';
            }
            add_filter('manage_'.$p.'_columns', array( $this, 'manage_rucy_cols' ) );
            add_action('manage_'.$p.'_custom_column', array( $this, 'add_rucy_col' ), 10, 2); 
        }
        // update content to reserved content
        $cron = new Class_Rucy_Cron();
        add_action( RC_CRON_HOOK, array( $cron, 'update_rc_reserved_content' ) );
        // deactivation this plugin
        register_deactivation_hook( __FILE__ , array( $this, 'uninstall_rucy' ) );
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
        if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php', 'settings_page_rucy' ) ) ) {
            wp_register_style('rucy.css', RC_PLUGIN_URL . 'css/rucy.css',array(),'0.1.0');
            wp_register_script('rucy.js', RC_PLUGIN_URL . 'js/rucy.js', array('jquery'), '0.1.1');
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
    
    public function add_setting_link( $links ) {
        $links[] = '<a href="' . get_admin_url( null, 'options-general.php?page=rucy' ) . '">' . __('Settings') . '</a>';
        return $links;
    }
    
    public function manage_rucy_cols( $columns ) {
        $columns['rucy_reservation_date'] = __( "Reservation Update DateTime", RC_TXT_DOMAIN );
        $columns['rucy_rollback_date'] = __( "Rollback DateTime", RC_TXT_DOMAIN );
        return $columns;
    }
    
    public function add_rucy_col( $column_name, $post_id ) {
        $component = new Class_Rucy_Component();
        $post_metas = $component->get_post_rc_meta( $post_id );
        if ( $column_name == 'rucy_reservation_date' ) {
            if ( $post_metas->accept == "1" ) {
                echo $post_metas->date;
            } else {
                _e( 'None' );
            }
        }
        if ( $column_name == 'rucy_rollback_date' ) {
            if ( $post_metas->accept_rollback == "1" ) {
                echo $post_metas->rollback_date;
            } else {
                _e( 'None' );
            }
        }
    }
    
    public function uninstall_rucy() {
        wp_clear_scheduled_hook( RC_CRON_HOOK );
        delete_option( RC_SETTING_OPTION_KEY );
        delete_option( 'rucy_version' );
        $all_posts = get_posts( 'numberposts=-1&post_status=' );
        $component = new Class_Rucy_Component();
        $post_meta_keys = $component->get_post_meta_keys();
        foreach ( $all_posts as $post_info ) {
            foreach ( $post_meta_keys as $key => $value ) {
                delete_post_meta( $post_info->ID, $value );
            }
        }
    }
    
    public function rucy_upgrade() {
        $rc_version = get_option( 'rucy_version' );
        $rc_support_types = get_option( RC_SETTING_OPTION_KEY );
        $is_old = false;
        if ( $rc_version ) {
            // compare
            $new_version = RC_VERSION;
            if ( $rc_version != $new_version ) {
                $is_old = true;
            }
        } else {
            // old version lessthan 0.3.0
            $is_old = true;
            if ( !is_array( $rc_support_types ) ) {
                update_option( RC_SETTING_OPTION_KEY, explode( ',', $rc_support_types ) );
            }
        }
        if ( $is_old ) {
            update_option( 'rucy_version' , RC_VERSION );
        }
    }
}
new Rucy_Class();
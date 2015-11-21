<?php
/**
 * 
 *
 * @author Nita
 */
require_once RC_PLUGIN_DIR . '/inc/class-rucy-component.php';

class Class_Rucy_Setting {
    private $options;
    //
    public function set_admin_menu() {
        add_options_page( 'Rucy', 'Rucy', 'manage_options', 'rucy', array( $this, 'add_setting_menu') );
    }
    
    public function add_setting_menu() {
        $component = new Class_Rucy_Component();
        $post_types = $component->get_post_types();
        if( ( isset( $_POST['page_options'] ) && $_POST['page_options'] === RC_SETTING_OPTION_KEY )  
           && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'update-options' ) )
          ) {
            // update post
            $post_support_posts = ( isset($_POST['rc_support_post_types']) ) ? $_POST['rc_support_post_types'] : array();
            $is_update = $component->update_support_post_type( $post_support_posts );
            if ( $is_update ) {
                add_settings_error( 'rucy', 'update', __( 'Success to setting update.', RC_TXT_DOMAIN ), 'updated');
            }
            $this->options = $component->get_support_post_type();
            wp_safe_redirect( menu_page_url( 'rucy', false ) );
        }
        $support_post_type = $component->get_support_post_type();
?>
<div class="wrap">
    <h2><?php _e( 'Rucy Settings', RC_TXT_DOMAIN ); ?></h2>
    <?php 
    if ( get_settings_errors( 'rucy' ) ) {
        settings_errors( 'rucy' );
    }
    ?>
    <p><?php _e( 'Configure content types reservation update.', RC_TXT_DOMAIN ); ?></p>
    <div class="rc-donation">
        <p><?php _e( 'Your contribution will continue to better this plugin.', RC_TXT_DOMAIN ); ?> <a href="http://www.amazon.co.jp/registry/wishlist/27FDI6LJU0X1O" class="button"><?php _e( 'Donate', RC_TXT_DOMAIN ); ?></a></p>
    </div>
    <form method="post" action="options-general.php?page=rucy">
    <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr class="">
                <th><?php _e( 'post type', RC_TXT_DOMAIN ); ?></th>
                <td>
                    <ul>
                    <?php 
                    foreach ( $post_types as $key => $post_type ) {
                        $checked = ( in_array( $key, $support_post_type ) ) ? 'checked' : '';
                        ?>
                    <li><label for="rc_<?php echo $key; ?>"><input type="checkbox" id="rc_<?php echo $key; ?>" name="rc_support_post_types[]" value="<?php echo $key; ?>" <?php echo $checked; ?> /><?php echo $post_type->labels->name; ?></label></li>
                    <?php }  ?>
                    </ul>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="<?php echo RC_SETTING_OPTION_KEY ?>"/>
        <p class="submit"><input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" /></p>
    </form>
</div>
<?php 
    }
}

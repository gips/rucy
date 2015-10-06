<?php
/**
 * 
 *
 * @author Nita
 */
require_once RC_PLUGIN_DIR . '/inc/class-rucy-component.php';

class Class_Rucy_Setting {
    //
    public function set_admin_menu() {
        add_options_page( 'Rucy', 'Rucy', 'manage_options', 'rucy', array( $this, 'add_setting_menu') );
    }
    
    public function add_setting_menu() {
        $component = new Class_Rucy_Component();
        $post_types = $component->get_post_types();
        $support_post_type = $component->get_support_post_type();
        $is_different = false;
        if( ( isset( $_POST['page_options'] ) && $_POST['page_options'] === RC_SETTING_OPTION_KEY )  
           && ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'update-options' ) )
          ) {
            // update post
            if( count( array_diff($support_post_type, $_POST['rc_support_post_types']) ) ) {
                // different data before post
                $is_different = true;
            }
            $is_update = $component->update_support_post_type( $_POST['rc_support_post_types'] );
            if ( $is_different && $is_update ) {
                // 更新完了
            } else if ( $is_different && !$is_update ) {
                // 更新失敗
            } else if ( !$is_different && !$is_update ) {
                // 同じ値で更新した
            }
            $support_post_type = $component->get_support_post_type();
        }
?>
<div class="wrap">
    <h2><?php _e( 'Rucy Settings', RC_TXT_DOMAIN ); ?></h2>
    <p><?php _e( 'Configure content types reservation update.', RC_TXT_DOMAIN ); ?></p>
    <form method="post" action="#">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr class="">
                <th><?php _e( 'post type', RC_TXT_DOMAIN ); ?><br><small>*<?php _e( 'Require', RC_TXT_DOMAIN ); ?></small></th>
                <td>
                    <ul>
                        <?php foreach ( $post_types as $key => $post_type ) { 
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
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
        </p>
    </form>
</div>
<?php 
    }
}

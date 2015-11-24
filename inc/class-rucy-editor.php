<?php
/**
 * 
 * 
 * @author Nita
 */
require_once RC_PLUGIN_DIR . '/inc/class-rucy-component.php';

class Class_Rucy_Editer {
    public $post_metas;
    public $post_meta_keys;
    public $support_post_types;
    public $current_post_content;

    public function add_rucy_metabox() {
        $component = new Class_Rucy_Component();
        $support_post_types = $component->get_support_post_type();
        foreach ( $support_post_types as $post_type ) {
            add_meta_box( 'rucy_metabox', 'Rucy - Reservation Update Content -', array( $this, 'add_rucy_editor_box' ), $post_type, 'normal', 'high' );
        }
    }
    
    public function add_rucy_editor_box() {
        global $post;
        $component = new Class_Rucy_Component();
        $current_year = date_i18n( 'Y' );
        $support_post_types = $component->get_support_post_type();
        $this->support_post_types = $support_post_types;
        $this->post_meta_keys = $component->get_post_meta_keys();
        $this->post_metas = $component->get_post_rc_meta( $post->ID );
        $this->current_post_content = $post->post_content;
        $dismissed = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
        $reserve_date = ( $this->post_metas->date == "" ) ? date_i18n( 'Y-m-d H:i:s' ) : $this->post_metas->date;
        $arr_reserve_date = getdate(strtotime( $reserve_date ) );
        $arr_date = array(
            'rc_year' => date_i18n( 'Y', $arr_reserve_date[0] ),
            'rc_month' => date_i18n( 'm', $arr_reserve_date[0] ),
            'rc_day' => date_i18n( 'd', $arr_reserve_date[0] ),
            'rc_hour' => date_i18n( 'H', $arr_reserve_date[0] ),
            'rc_minutes' => date_i18n( 'i', $arr_reserve_date[0] ),
                );
        $reserve_content = ( $this->post_metas->content == "" ) ? $this->current_post_content : $this->post_metas->content;
        // rollback settings
        $rollback_date = ( $this->post_metas->rollback_date == "" ) ? date_i18n( 'Y-m-d H:i:s' ) : $this->post_metas->rollback_date;
        $arr_rollback_date = getdate(strtotime( $rollback_date ) );
        $arr_rb_date = array(
            'rc_rb_year' => date_i18n( 'Y', $arr_rollback_date[0] ),
            'rc_rb_month' => date_i18n( 'm', $arr_rollback_date[0] ),
            'rc_rb_day' => date_i18n( 'd', $arr_rollback_date[0] ),
            'rc_rb_hour' => date_i18n( 'H', $arr_rollback_date[0] ),
            'rc_rb_minutes' => date_i18n( 'i', $arr_rollback_date[0] ),
        );
?>
<div id="rc-post-wrap" class="curtime">
    <input type="hidden" id="schroeder" name="schroeder" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />
    <label for="<?php echo $this->post_meta_keys->accept; ?>" class="rc_accept"><input type="checkbox" id="<?php echo $this->post_meta_keys->accept; ?>" name="<?php echo $this->post_meta_keys->accept; ?>" value="1" <?php echo ( $this->post_metas->accept == "1" ) ? "checked" : ""; ?> /> <?php _e('Accept reserve update content.',RC_TXT_DOMAIN) ?></label>
    <div class="rc-datetime" id="timestamp">
        <?php _e( 'UpdateTime', RC_TXT_DOMAIN ); ?> : <strong><?php echo date_i18n( 'Y/m/d @ H:i', strtotime( $reserve_date ) ); ?></strong>
    </div>
    <a href="#edit-reservdate" class="edit-timestamp rc-datetime-edit"><?php _e( 'Edit' ); ?></a>
    <div class="rc-datetime-wrap">
        <select name="rc_year" id="">
        <?php  for( $y = $current_year; $y <= ( $current_year + 3); $y++ ): 
            $selected_y = ( $y == date_i18n( 'Y', $arr_reserve_date[0] ) ) ? "selected" : ""; ?>
            <option value="<?php echo $y; ?>" <?php echo $selected_y; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
        </select> / 
        <select name="rc_month" id="">
        <?php for( $i = 1; $i <= 12; $i++ ):
            $m = sprintf( "%02d", $i );
            $selected_m = ( $m == date_i18n( 'm', $arr_reserve_date[0] ) ) ? "selected" : "";
        ?>
            <option value="<?php echo $m; ?>" <?php echo $selected_m; ?>><?php echo $m; ?></option>
        <?php endfor; ?>
        </select> / 
        <select name="rc_day" id="">
        <?php  for( $d = 1; $d<=31; $d++ ):
            $d = sprintf( "%02d", $d );
            $selected_d = ( $d == date_i18n( 'd', $arr_reserve_date[0] ) ) ? "selected" : "";
        ?>
            <option value="<?php echo $d; ?>" <?php echo $selected_d; ?>><?php echo $d; ?></option>
        <?php endfor; ?>
        </select>@
        <select name="rc_hour" id="">
        <?php for( $h = 0; $h <= 23; $h++ ): 
            $h = sprintf("%02d",$h);
            $selected_h = ( $h == date_i18n( 'H', $arr_reserve_date[0] ) ) ? "selected" : "";
        ?>
            <option value="<?php echo $h; ?>" <?php echo $selected_h; ?>><?php echo $h; ?></option>
        <?php endfor; ?>
        </select>:
        <select name="rc_minutes" id="">
        <?php for( $min = 0; $min <= 59; $min++ ): 
            $min = sprintf( "%02d", $min );
            $selected_min = ( $min == date_i18n( 'i', $arr_reserve_date[0] ) ) ? "selected" : "";
            ?>
            <option value="<?php echo $min; ?>" <?php echo $selected_min; ?>><?php echo $min; ?></option>
        <?php endfor; ?>
        </select>
        <a href="#edit-reservdate" class="rc-datetime-update button"><?php _e('OK',RC_TXT_DOMAIN) ?></a>
        <a href="#edit-reservdate" class="rc-datetime-cancel"><?php _e('Cancel',RC_TXT_DOMAIN) ?></a>
    </div>
    <?php foreach ( $arr_date as $k => $v ): ?>
    <input type="hidden" name="<?php echo $k; ?>_cr" id="<?php echo $k; ?>_cr" value="<?php echo $v; ?>" />
    <?php endforeach; ?>
    <div id="rc-accept-update-update">
        <label for="rc-accept-update-postdate">
            <input type="checkbox" name="<?php echo $this->post_meta_keys->accept_update; ?>" value="1" id="rc-accept-update-postdate" class="rc-accept-update-postdate" <?php echo ( $this->post_metas->accept_update == "1" ) ? "checked" : "";  ?> /> <?php _e('Accept reserve update post date.', RC_TXT_DOMAIN); ?>
        </label>
    </div>
<?php if( array_search( 'rc_update_postdate', $dismissed ) === false ): 
    $pointer_content = '<h3>' . __( 'Attention - reservation update UpdateTime', RC_TXT_DOMAIN ) . '</h3>';
    $pointer_content .= '<p>' . __( "If update UpdateTime, this post\'s permalink is changed by permalink settings.", RC_TXT_DOMAIN ) . '</p>';
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            // show notice pointer update postdate.
            jQuery('#rc-accept-update-update').pointer({
                content : '<?php echo $pointer_content; ?>',
                buttons : function(e, t){
                    return jQuery('<a class="close" href="#"><?php _e( 'Do not show future', RC_TXT_DOMAIN ) ?></a>').bind('click.pointer',function(e){
                        e.preventDefault();
                        t.element.pointer('close');
                    });
                },
                position : { edge : "top", align : "left"},
                close : function(){
                    jQuery.post("<?php echo admin_url( 'admin-ajax.php' ); ?>", {
                        action : 'dismiss-wp-pointer',
                        pointer : 'rc_update_postdate'
                    });
                }
            }).pointer('open');
        });
    </script>
<?php endif; ?>
</div>
<?php 
 wp_editor( $reserve_content, $this->post_meta_keys->content );
 /*
  * support feature image reservation.
  */
 if( current_theme_supports('post-thumbnails') ):
?>
<fieldset>
<h3><?php echo __( 'Featured Image for Reservation Update', RC_TXT_DOMAIN ); ?></h3>
<label class="rc_feature_accept">
    <input type="checkbox" name="<?php echo $this->post_meta_keys->accept_feature_img; ?>" value="1" <?php echo ( $this->post_metas->accept_feature_img  == "1") ? "checked" : ""; ?>> <?php _e( 'Accept reserve update feature image.', RC_TXT_DOMAIN ); ?>
</label>
<div class="rc_feature_image_uploader">
    <p><button id="rc_feature_image_upload" class="button rc-feature-uploader-button <?php echo ( $this->post_metas->feature_img != '' ) ? ' has_image' : ''; ?>"><?php  _e( 'Set featured image for Reservation', RC_TXT_DOMAIN ); ?></button></p>
<div class="rc-feature-image-uploader__ctrl">
    <div class="rc-feature-image-preview">
        <?php
        if ( ! empty( $this->post_metas->feature_img ) ) {
           echo $this->post_metas->feature_img;
        }
        ?> 
    </div>
</div>
<p><button class="button rc_remove_feature_image"><?php _e( 'Remove Featured image for Reservation', RC_TXT_DOMAIN ); ?></button></p>
<input type="hidden" id="rc_feature_image" name="<?php echo $this->post_meta_keys->feature_img; ?>" value="<?php echo $this->post_metas->feature_img; ?>" />
</div>
</fieldset>
<fieldset id="rc-rollback-container" class="curtime">
    <h3><?php _e( 'Setting Rollback post content.', RC_TXT_DOMAIN ); ?></h3>
    <label for="rc-accept-rollback-content">
        <input type="checkbox" name="<?php echo $this->post_meta_keys->accept_rollback ?>" value="1" id="rc-accept-rollback-content" class="rc-accept-rollback-content" <?php echo ( $this->post_metas->accept_rollback == "1" ) ? "checked" : ""; ?> > <?php _e( 'Accept rollback content.', RC_TXT_DOMAIN ); ?>
    </label>
    <div class="rc-rollback-datetime" id="timestamp">
        <?php _e( 'Rollback DateTime', RC_TXT_DOMAIN ); ?> : <strong><?php echo date_i18n("Y/m/d @ H:i", strtotime( $rollback_date ) ); ?></strong>
    </div>
    <a href="#edit-rollback-datetime" class="edit-timestamp rc-rollback-datetime-edit"><?php _e('Edit'); ?></a>
    <div class="rc-rollback-datetime-wrap">
        <select name="rc_rb_year" id="">
    <?php  for( $y = $current_year; $y <= ( $current_year + 3); $y++ ): 
        $selected_y = ( $y == date_i18n( 'Y', $arr_rollback_date[0] ) ) ? "selected" : ""; ?>
        <option value="<?php echo $y; ?>" <?php echo $selected_y; ?>><?php echo $y; ?></option>
    <?php endfor; ?>
    </select> / 
    <select name="rc_rb_month" id="">
    <?php for( $i = 1; $i <= 12; $i++ ):
        $m = sprintf( "%02d", $i );
        $selected_m = ( $m == date_i18n( 'm', $arr_rollback_date[0] ) ) ? "selected" : "";
    ?>
        <option value="<?php echo $m; ?>" <?php echo $selected_m; ?>><?php echo $m; ?></option>
    <?php endfor; ?>
    </select> / 
    <select name="rc_rb_day" id="">
    <?php  for( $d = 1; $d<=31; $d++ ):
        $d = sprintf( "%02d", $d );
        $selected_d = ( $d == date_i18n( 'd', $arr_rollback_date[0] ) ) ? "selected" : "";
    ?>
        <option value="<?php echo $d; ?>" <?php echo $selected_d; ?>><?php echo $d; ?></option>
    <?php endfor; ?>
    </select>@
    <select name="rc_rb_hour" id="">
    <?php for( $h = 0; $h <= 23; $h++ ): 
        $h = sprintf("%02d",$h);
        $selected_h = ( $h == date_i18n( 'H', $arr_rollback_date[0] ) ) ? "selected" : "";
    ?>
        <option value="<?php echo $h; ?>" <?php echo $selected_h; ?>><?php echo $h; ?></option>
    <?php endfor; ?>
    </select>:
    <select name="rc_rb_minutes" id="">
    <?php for( $min = 0; $min <= 59; $min++ ): 
        $min = sprintf( "%02d", $min );
        $selected_min = ( $min == date_i18n( 'i', $arr_rollback_date[0] ) ) ? "selected" : "";
        ?>
        <option value="<?php echo $min; ?>" <?php echo $selected_min; ?>><?php echo $min; ?></option>
    <?php endfor; ?>
    </select>
    <a href="#edit-rollback-datetime" class="rc-rollback-datetime-update button"><?php _e('OK',RC_TXT_DOMAIN) ?></a>
    <a href="#edit-rollback-datetime" class="rc-rollback-datetime-cancel"><?php _e('Cancel',RC_TXT_DOMAIN) ?></a>
    </div>
    <?php foreach ( $arr_rb_date as $k => $v ):  ?>
    <input type="hidden" name="<?php echo $k; ?>_cr" id="<?php echo $k; ?>_cr" value="<?php echo $v; ?>"/>
    <?php endforeach; ?>
    <div id="rc-accept-rollback-updatetime-wrap">
        <label for="rc-accept-rollback-updatetime">
            <input id="rc-accept-rollback-updatetime" type="checkbox" value="1" name="<?php echo $this->post_meta_keys->accept_rollback_update; ?>" <?php echo ( $this->post_metas->accept_rollback_update == "1" ) ? "checked" : ""; ?>> <?php _e( 'Accept Rollback post date.', RC_TXT_DOMAIN ); ?>
        </label>
    </div>
    <?php if( current_theme_supports( 'post-thumbnails' ) ):  ?>
    <div id="rc-accept-rollback-feature-image-wrap">
        <label for="rc-accept-rollback-feature-image">
            <input id="rc-accept-rollback-feature-image" type="checkbox" value="1" name="<?php echo $this->post_meta_keys->accept_rollback_feature_img; ?>" <?php echo ( $this->post_metas->accept_rollback_feature_img == "1" ) ? "checked" : ""; ?>> <?php _e( 'Accept Rollback feature image.', RC_TXT_DOMAIN ); ?>
        </label>
    </div>
    <?php endif; ?>
</fieldset>
<?php 
 endif;
    }
    
// save post meta
    public function save_rc_post_meta( $post_id ) {
        $component = new Class_Rucy_Component();
        if( !isset( $_POST ) && !isset( $_POST['post_type'] ) ) {
            return;
        }
        if(isset( $_POST['schroeder'] ) && !wp_verify_nonce( $_POST['schroeder'], plugin_basename( __FILE__ ) ) ) {
            return;
        }
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !isset( $_POST['post_type'] ) ) {
            return;
        }
        $accept_post_types = $component->get_support_post_type();
        if ( !in_array( $_POST['post_type'], $accept_post_types ) ) {
            return;
        }
        if (
           !array_key_exists( 'rc_hour', $_POST )
        && !array_key_exists( 'rc_minutes', $_POST )
        && !array_key_exists( 'rc_month', $_POST )
        && !array_key_exists( 'rc_day', $_POST )
        && !array_key_exists( 'rc_year', $_POST )
        && !array_key_exists( 'rc_rb_hour', $_POST )
        && !array_key_exists( 'rc_rb_minutes', $_POST )
        && !array_key_exists( 'rc_rb_month', $_POST )
        && !array_key_exists( 'rc_rb_day', $_POST )
        && !array_key_exists( 'rc_rb_year', $_POST )
        ) {
            return;
        }

        $post_meta_keys = $component->get_post_meta_keys();
        $date = mktime( $_POST['rc_hour'], $_POST['rc_minutes'], 00, $_POST['rc_month'], $_POST['rc_day'], $_POST['rc_year'] );
        if( $date ) {
            $_POST[$post_meta_keys->date] = date_i18n( 'Y-m-d H:i:s', $date );
        } else {
            $_POST[$post_meta_keys->date] = "";
        }
        if( !isset( $_POST[$post_meta_keys->accept] ) || $_POST[$post_meta_keys->accept] != "1" ){
                $_POST[$post_meta_keys->accept]  = "0";
        }
        // rollback setting
        $rdate = mktime( $_POST['rc_rb_hour'], $_POST['rc_rb_minutes'], 00, $_POST['rc_rb_month'], $_POST['rc_rb_day'], $_POST['rc_rb_year'] );
        if ( $rdate ) {
            $_POST[$post_meta_keys->rollback_date] = date_i18n( 'Y-m-d H:i:s', $rdate );
        } else {
            $_POST[$post_meta_keys->rollback_date] = "";
        }
        if( !isset( $_POST[$post_meta_keys->accept_rollback] ) || $_POST[$post_meta_keys->accept_rollback] != "1" ){
            $_POST[$post_meta_keys->accept_rollback] = "0";
        }
        if( !isset( $_POST[$post_meta_keys->accept_rollback_update] ) || $_POST[$post_meta_keys->accept_rollback_update] != "1" ){
            $_POST[$post_meta_keys->accept_rollback_update] = "0";
        }
        if( !isset( $_POST[$post_meta_keys->accept_rollback_feature_img] ) || $_POST[$post_meta_keys->accept_rollback_feature_img] != "1" ){
            $_POST[$post_meta_keys->accept_rollback_feature_img] = "0";
        }
        // save post meta 
        foreach ( $post_meta_keys as $key => $value ) {
            $component->save_rc_post_meta_base( $post_id, $value, $_POST );
        }
        // regist reserve update content
        if ( $_POST[$post_meta_keys->accept] == "1" ) {
            $reserve_date = strtotime( get_gmt_from_date( $_POST[$post_meta_keys->date] ) . " GMT" );
            if ( in_array( $_POST['post_type'], $accept_post_types ) ) {
                wp_schedule_single_event( $reserve_date, RC_CRON_HOOK, array( $post_id ) );
            }
        } else if ( $_POST[$post_meta_keys->accept] == "0" || !isset ( $_POST[$post_meta_keys->accept] ) ) {
            // delete schedule
            wp_clear_scheduled_hook(RC_CRON_HOOK, array($post_id));
        }
    }
    
    // add update message
    public function add_reservation_message( $messages ) {
        global $post, $post_ID;
        $component = new Class_Rucy_Component();
        $accept_post_types = $component->get_support_post_type();
        $post_type = get_post_type( $post );
        
        if ( !in_array( $post_type, $accept_post_types ) ) {
            return $messages;
        }
        
        $post_metas = $component->get_post_rc_meta( $post_ID );
        if ( $post_metas->accept != "1" ) {
            return $messages;
        }
        
        $add_message_date = date_i18n( 'Y/m/d @ H:i', strtotime( $post_metas->date ) );
        $base_str = __( 'registered reservation update content _RC_DATETIME_', RC_TXT_DOMAIN );
        $add_message = '<br>' . strtr( $base_str, array( '_RC_DATETIME_' => $add_message_date ) );
        
        if( $post_metas->accept_rollback == "1" ) {
            $rollback_date = date_i18n( 'Y/m/d @ H:i', strtotime( $post_metas->rollback_date ) );
            $rollback_base_str = __( 'registered rollback content _RC_ROLLBACK_DATETIME_ ', RC_TXT_DOMAIN );
            $add_message .= '<br>' . strtr( $rollback_base_str , array( '_RC_ROLLBACK_DATETIME_' => $rollback_date ) );
         }
        // published
        $messages[$post_type][1] .= $add_message;
        $messages[$post_type][4] .= $add_message;
        $messages[$post_type][6] .= $add_message;
        // saved
        $messages[$post_type][7] .= $add_message;
        // submited
        $messages[$post_type][8] .= $add_message;
        // scheduled
        $messages[$post_type][9] .= $add_message;
        
        return $messages;
    }
}

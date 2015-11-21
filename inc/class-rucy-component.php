<?php
/**
 * Description of class-rucy-componet
 *
 * @author Nita
 */
class Class_Rucy_Component {
    /**
     * 
     * 
     * @return bool 
     */
    public function update_support_post_type( array $post_types ) {
        return update_option( RC_SETTING_OPTION_KEY, $post_types );
    }
    public function get_support_post_type() {
        $res = get_option( RC_SETTING_OPTION_KEY );
        if( !$res ) {
            $res = array();
        } else if ( !is_array( $res ) ) {
            $res = explode( ',' , $res );
        }
        return $res;
    }
    public function get_post_types( $type = 'objects' ) {
        $args = array( 'public' => true, );
        $post_types = get_post_types( $args, $type );
        unset( $post_types['attachment'], $post_types['revision'], $post_types['nav_menu_item'] );
        return $post_types;
    }
    
    public function get_post_meta_keys() {
        $post_meta_keys = new stdClass();
        $post_meta_keys->accept = 'rc_reserv_accept';
        $post_meta_keys->content = 'rc_reserv_content';
        $post_meta_keys->date = 'rc_reserv_date';
        $post_meta_keys->feature_img = 'rc_reserv_feature_image';
        $post_meta_keys->accept_feature_img = 'rc_reserv_accept_feature_image';
        $post_meta_keys->accept_update = 'rc_reserv_accept_post_update';
        $post_meta_keys->accept_rollback = 'rc_rollback_accept';
        $post_meta_keys->rollback_date = 'rc_rollback_date';
        $post_meta_keys->accept_rollback_update = 'rc_rollback_accept_update_date';
        $post_meta_keys->accept_rollback_feature_img = 'rc_rollback_accept_feature_image';
        return $post_meta_keys;
    }
    
    public function get_post_rc_meta( $post_id = "" ) {
        $base = $this->get_post_meta_keys();
        $res = new stdClass();
        if( $post_id > 0 ) {
            foreach ( $base as $key => $value ) {
                    $res->$key = get_post_meta( $post_id, $value, true );
            }
        }
        return $res;
    }
    
    public function save_rc_post_meta_base( $post_id, $post_meta_key, array $post ) {
        if ( is_array( $post ) ) {
            $post_data = "";
            if ( isset( $post[$post_meta_key] ) ) {
                $post_data = $post[$post_meta_key];
            }
            $meta = get_post_meta( $post_id, $post_meta_key, true );
            if ( $meta != $post_data ) {
                update_post_meta( $post_id, $post_meta_key, $post_data, $meta );
            } elseif ( $post_data == "" ) {
                delete_post_meta( $post_id, $post_meta_key );
            }
        }
    }
}

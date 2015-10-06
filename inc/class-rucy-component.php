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
        if( !is_array( $res ) ) {
            $res = array( 'post', 'page' );
        }
        return $res;
    }
    public function get_post_types( $type = 'objects' ) {
        $args = array( 'public' => true, );
        $post_types = get_post_types( $args, $type );
        unset( $post_types['attachment'], $post_types['revision'], $post_types['nav_menu_item'] );
        return $post_types;
    }
}

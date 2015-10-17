<?php
/**
 * Description of class-rucy-cron
 *
 * @author Nita
 */
require_once RC_PLUGIN_DIR . '/inc/class-rucy-component.php';

class Class_Rucy_Cron {
    
    public function update_rc_reserved_content( $post_id ) {
        $component = new Class_Rucy_Component();
        $post_metas = $component->get_post_rc_meta( (int)$post_id );
        if ( $post_metas->accept != "1" ) {
            return;
        }
        // set update content
        $updates = array( 
            'ID' => (int)$post_id,    
            'post_content' => apply_filters( 'the_content', $post_metas->content ),
        );
        wp_update_post( $updates, true );
        // set update post date
        if ( $post_metas->accept_update == "1" ) {
            $updates['post_date'] = $post_metas->date;
            $updates['post_date_gmt'] = get_gmt_from_date( $post_metas->date );
        }
        // update post
        remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
        remove_filter( 'content_filtered_save_pre', 'wp_filter_post_kses' );
        add_filter( 'content_save_pre', array( $this, 'rc_content_allow_iframe' ) );
        wp_update_post( $updates, true );
        remove_filter( 'content_save_pre', array( $this, 'rc_content_allow_iframe' ) );
        add_filter( 'content_save_pre', 'wp_filter_post_kses' );
        add_filter( 'content_filtered_save_pre', 'wp_filter_post_kses' );
        // update feature image
        if ( $post_metas->accept_feature_img == "1" && $post_metas->feature_img != "" ) {
            $this->update_post_thumbnail( $post_id, $post_metas->feature_img );
        }
        // delete post metas
        $post_meta_keys = $component->get_post_meta_keys();
        foreach ( $post_meta_keys as $key => $value ) {
            delete_post_meta( $post_id, $value );
        }
        // clear schedule on wp_cron
        wp_clear_scheduled_hook( RC_CRON_HOOK, array( $post_id ) );
    }
    
    public function rc_content_allow_iframe( $content ) {
        global $allowedposttags;
        // iframe and attribute in iframe
        $allowedposttags['iframe'] = array(
            'class' => array(), 'src' => array(),
            'width' => array(), 'height' => array(),
            'frameborder' => array(), 'scrolling' => array(),
            'marginheight' => array(), 'marginwidth' => array(),
            'srcdoc' => array(), 'sandbox' => array(),
            'seamless' => array(), 'name' => array(),
        );
        return $content;
    }
    
    private function update_post_thumbnail( $post_id, $reserved_post_thumb_path ) {
        include_once(ABSPATH . 'wp-admin/includes/image.php');
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents( $reserved_post_thumb_path );
        $file_name = basename( $reserved_post_thumb_path );
        
        if( wp_mkdir_p( $upload_dir['path'] ) ) {
           $file = $upload_dir['path'] . '/' . $file_name; 
        } else {
           $file = $upload_dir['basedir'] . '/' . $file_name;
        }
        file_put_contents( $file, $image_data );
        $wp_file_type = wp_check_filetype( $file_name, null );
        $attachment = array(
        'post_mime_type' => $wp_file_type['type'],
        'post_title'     => sanitize_file_name( $file_name ),
        'post_content'   => '',
        'post_status'    => 'inherit',
        );
        delete_post_thumbnail( $post_id );
        $attachment_id = wp_insert_attachment( $attachment, $file, $post_id );
        $attach_data = wp_generate_attachment_metadata( $attachment_id, $file );
        if( !empty( $attach_data ) && !is_wp_error( $attach_data ) ){
            $res = wp_update_attachment_metadata( $attachment_id, $attach_data );
            set_post_thumbnail( $post_id, $attachment_id );

            return $res;
        }
    }
}

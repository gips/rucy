<?php
/**
 * uninstall this plugin.
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

wp_clear_scheduled_hook( 'rucy_update_reserved_content' );
delete_option( 'rucy_post_type' );
delete_option( 'rucy_version' );
$all_posts = get_posts( 'numberposts=-1&post_status=' );
$rc_post_meta_keys = array(
    'rc_reserv_accept',
    'rc_reserv_content',
    'rc_reserv_date',
    'rc_reserv_feature_image',
    'rc_reserv_accept_feature_image',
    'rc_reserv_accept_post_update',
    'rc_rollback_accept',
    'rc_rollback_date',
    'rc_rollback_accept_update_date',
    'rc_rollback_accept_feature_image',
);
foreach ( $all_posts as $post_info ) {
    foreach ( $rc_post_meta_keys as $key ) {
        delete_post_meta( $post_info->ID, $key );
    }
}
<?php
/*
 * Plugin Name: Rucy
 * Plugin URI: https://github.com/gips/rucy
 * Description: Reservation Update (Published) Content.
 * Version: 0.2.0
 * Author: Nita
 * License: GPLv2 or later
 * Text Domain: rucy
 * Domain Path: /lang
 */
define('RC_PLUGIN_URL',  plugin_dir_url(__FILE__));
define('RC_SETTING_OPTION_KEY', 'rucy_post_type');
define('RC_TXT_DOMAIN', 'rucy');
define('RC_POSTTYPE_DEFAULT','post,page');
define('RC_CRON_HOOK', 'rucy_update_reserved_content');
load_plugin_textdomain( RC_TXT_DOMAIN, false, 'rucy/lang');

add_action('admin_enqueue_scripts','load_rc_jscss');
function load_rc_jscss()
{
    global $hook_suffix;
    if(in_array($hook_suffix, array('post.php','post-new.php',)))
    {
        wp_register_style('rucy.css', RC_PLUGIN_URL . '/css/rucy.css',array(),'0.0.1');
        wp_enqueue_style('rucy.css');
        wp_register_script('rucy.js', RC_PLUGIN_URL . '/js/rucy.js', array('jquery'), '0.0.1');
        wp_enqueue_script('rucy.js');
    }
}

/**
 * get rucy post_metas or post_meta keys.
 * @param int $post_id
 * @return array 
 */
function get_rc_metas($post_id = "")
{
    $base = array(
        'accept' => 'rc_reserv_accept',
        'content' => 'rc_reserv_content',
        'date' => 'rc_reserv_date',
        'feature_img' => 'rc_reserv_feature_image',
        'accept_feature_img' => 'rc_reserv_accept_feature_image',
            );
    if($post_id > 0)
    {
        foreach ($base as $key => $value)
        {
            $res[$key] = get_post_meta($post_id, $value, true);
        }
    } else {
        $res = $base;
    }
    return $res;
}

// add reserv_metabox
add_action('admin_menu', 'add_rc_metabox_out');
function add_rc_metabox_out()
{
    $accept_post_type = get_rc_setting();
    foreach ($accept_post_type as $post_type)
    {
        add_meta_box('rucy_metabox','Rucy - Reservation Update Content -','add_rucy_metabox_inside',$post_type,'normal','high');
    }
}
function add_rucy_metabox_inside()
{
    global $post;
    $rc_keys = get_rc_metas();
    $rc_content_name = $rc_keys['content'];
    $rc_accept_name = $rc_keys['accept'];
    $rc_metas = get_rc_metas($post->ID);
    $reserv_accept = $rc_metas['accept'];
    $reserv_date = $rc_metas['date'];
    $reserv_feature_name = $rc_keys['feature_img'];
    $reserv_feature = $rc_metas['feature_img'];
    $reserv_accept_feature_name = $rc_keys['accept_feature_img'];
    $reserv_accept_feature = $rc_metas['accept_feature_img'];
    $thumb_id = get_post_thumbnail_id($post->ID);
    $reserv_feature_width = "255";
    $reserv_feature_height = "255";
    if($thumb_id){
        $image = wp_get_attachment_image_src($thumb_id, 'thumbnail');
        $reserv_feature_width = $image[1];
        $reserv_feature_height = $image[2];
    }
    $reserv_feature_image = ($reserv_feature != '') ? '<img width="'.$reserv_feature_width.'" height="'.$reserv_feature_height.'" class="rc_feature_image" src="'.$reserv_feature.'" />' : '';
    if("" == $reserv_date)
    {
        $reserv_date = date_i18n('Y-m-d H:i:s');
    }
    $reserv_date_arr = getdate(strtotime($reserv_date));
    $current_y = date_i18n('Y');
    $reserv_content = $rc_metas['content'];
    if("" == $reserv_content)
    {
        $reserv_content = $post->post_content;
    }
?>
<div id="rc-post-wrap" class="curtime">
    <input type="hidden" name="schroeder" id="schroeder" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>"/>
    <label class="rc_accept">
        <input type="checkbox" name="<?php echo $rc_accept_name; ?>" value="1" <?php echo ($reserv_accept == "1") ? "checked" : ""; ?>> <?php _e('Accept reserve update content.',RC_TXT_DOMAIN) ?>
    </label>
    <div class="rc-datetime" id="timestamp">
        <?php _e('UpdateTime',RC_TXT_DOMAIN) ?>:<b><?php echo date_i18n("Y/m/d @ H:i", strtotime($reserv_date)); ?></b>
    </div>
    <a href="#edit-reservdate" class="edit-timestamp rc-datetime-edit"><?php _e('Edit') ?></a>
    <div class="rc-datetime-wrap">
        <select name="rc_year">
        <?php 
        for($y = $current_y; $y <= ($current_y + 3); $y++){
            $y_selected = ($y == date_i18n('Y',$reserv_date_arr[0])) ? "selected" : "";
            echo '<option value="'.$y.'" '.$y_selected.'>'.$y.'</option>';
        }
        ?>
        </select>
        <?php echo '/' ?>
        <select name="rc_month">
        <?php
        for($i = 1; $i <= 12; $i++)
        {
            $m = sprintf("%02d",$i);
            $selected = ($m == date_i18n('m',$reserv_date_arr[0])) ? "selected" : "";
            echo '<option value="'.$m.'" '.$selected.'>'.$m.'</option>';
        }
        ?>
        </select><?php echo '/' ?>
        <select name="rc_day">
        <?php 
        for($d = 1; $d<=31; $d++){
            $d = sprintf("%02d",$d);
            $d_selected = ($d == date_i18n('d',$reserv_date_arr[0])) ? "selected" : "";
            echo '<option value="'.$d.'" '.$d_selected.'>'.$d.'</option>';
        }
        ?>
        </select>    
        @
        <select name="rc_hour">
        <?php 
        for($h = 0; $h <= 23; $h++){
            $h = sprintf("%02d",$h);
            $h_selected = ($h == date_i18n('H',$reserv_date_arr[0])) ? "selected" : "";
            echo '<option value="'.$h.'" '.$h_selected.'>'.$h.'</option>';
        }
        ?>
        </select>
        :
        <select name="rc_minutes">
        <?php 
        for($min = 0; $min <= 59; $min++){
            $min = sprintf("%02d",$min);
            $min_selected = ($min == date_i18n('i',$reserv_date_arr[0])) ? "selected" : "";
            echo '<option value="'.$min.'" '.$min_selected.'>'.$min.'</option>';
        }
        ?>
        </select>
        <a href="#edit-reservdate" class="rc-datetime-update button"><?php _e('OK',RC_TXT_DOMAIN) ?></a>
        <a href="#edit-reservdate" class="rc-datetime-cancel"><?php _e('Cancel',RC_TXT_DOMAIN) ?></a>
    </div>
    <?php
        $date_arr = array(
            'rc_year' => date_i18n('Y',$reserv_date_arr[0]),
            'rc_month' => date_i18n('m',$reserv_date_arr[0]),
            'rc_day' => date_i18n('d',$reserv_date_arr[0]),
            'rc_hour' => date_i18n('H',$reserv_date_arr[0]),
            'rc_minutes' => date_i18n('i',$reserv_date_arr[0])
        );
        foreach ($date_arr as $k => $v)
        {
            echo '<input type="hidden" name="'.$k.'_cr" id="'.$k.'_cr" value="'.$v.'">';
        }
    ?>
</div>
<?php 
    wp_editor($reserv_content, $rc_content_name);
/*
 * support feature image reservation.
 */
    if( current_theme_supports('post-thumbnails') ) {
?>
<fieldset>
<h3><?php echo __('Featured Image for Reservation Update', RC_TXT_DOMAIN); ?></h3>
<label class="rc_feature_accept">
    <input type="checkbox" name="<?php echo $reserv_accept_feature_name; ?>" value="1" <?php echo ($reserv_accept_feature == "1") ? "checked" : ""; ?>> <?php _e('Accept reserve update feature image.', RC_TXT_DOMAIN); ?>
</label>
<p><a href="media-upload.php?type=image&TB_iframe=1&width=753&height=522&post_id=<?php echo $post->ID ?>" class="thickbox<?php echo ($reserv_feature_image != '') ? ' has_image' : ''; ?>" id="rc_feature_image_upload" title="<?php _e('Set featured image Reservation', RC_TXT_DOMAIN) ?>"><?php echo ($reserv_feature_image != '') ? $reserv_feature_image : __('Set featured image for Reservation', RC_TXT_DOMAIN); ?></a></p>
<p><a href="#" class="rc_remove_feature_image"><?php _e('Remove Featured image for Reservation', RC_TXT_DOMAIN) ?></a></p>
<input type="hidden" id="rc_feature_image" name="<?php echo $reserv_feature_name ?>" value="<?php echo $reserv_feature ?>" />
</fieldset>
<?php 
    }
}

// save post meta
add_action('save_post','save_rc_post_meta');
function save_rc_post_meta($post_id)
{
    if(isset($_POST) && isset($_POST['post_type']))
    {
        if(isset($_POST['schroeder']) && !wp_verify_nonce($_POST['schroeder'], plugin_basename(__FILE__))){
            return;
        }
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return;
        }
        $rc_keys = get_rc_metas();
        $accept_post_type = get_rc_setting();
        if(in_array($_POST['post_type'], $accept_post_type))
        {
            $date = mktime($_POST['rc_hour'], $_POST['rc_minutes'], 00, $_POST['rc_month'], $_POST['rc_day'], $_POST['rc_year']);
            if($date)
            {
                $_POST[$rc_keys['date']] = date_i18n('Y-m-d H:i:s',$date);
            } else {
                $_POST[$rc_keys['date']] = "";
            }
            if(!isset($_POST[$rc_keys['accept']]) || $_POST[$rc_keys['accept']] != "1"){
                $_POST[$rc_keys['accept']]  = "0";
            }
        }
        foreach ($rc_keys as $key => $val)
        {
            save_rc_post_meta_base($post_id, $val);
        }
        if($_POST[$rc_keys['accept']] == "1")
        {
            $reserv_date = strtotime(get_gmt_from_date($_POST[$rc_keys['date']]) . " GMT");
            if(in_array($_POST['post_type'], $accept_post_type) )
            {
                wp_schedule_single_event($reserv_date, RC_CRON_HOOK, array($post_id));
            }
        } else if($_POST[$rc_keys['accept']] == "0" || !isset ($_POST[$rc_keys['accept']])) {
            // delete schedule
            wp_clear_scheduled_hook(RC_CRON_HOOK, array($post_id));
        }
    }
}

/**
 * save, update, delete post_meta
 * 
 * @param int $post_id
 * @param string $post_metakey
 */
function save_rc_post_meta_base($post_id, $post_metakey)
{
    if(isset($_POST))
    {
        $post_data = "";
        if(isset($_POST[$post_metakey]))
        {
            $post_data = $_POST[$post_metakey];
        }
        $meta = get_post_meta($post_id, $post_metakey,true);
       if ($post_data != $meta) {
            update_post_meta($post_id, $post_metakey, $post_data, $meta);
        } elseif("" == $post_data) {
            delete_post_meta($post_id, $post_metakey);
        }
    }
}

// update post for wp-cron
add_action('rucy_update_reserved_content', 'update_rc_reserved_content', 10, 1);
function update_rc_reserved_content($post_id)
{
    $rc_metas = get_rc_metas((int)$post_id);
    if("1" == $rc_metas['accept'])
    {
        $updates = array(
            'ID' => (int)$post_id,
            'post_content' => $rc_metas['content'],
        );
        // feature_image
        if(isset($rc_metas['accept_feature_img']) && "1" == $rc_metas['accept_feature_img'] &&
           isset($rc_metas['feature_img']) && $rc_metas['feature_img'] != '') {
            update_rc_post_thumbnail($post_id, $rc_metas['feature_img']);
        }
       $upp = wp_update_post($updates,true);
    }
    $dels = get_rc_metas();
    foreach ($dels as $key => $del)
    {
        delete_post_meta($post_id, $del);
    }
    wp_clear_scheduled_hook(RC_CRON_HOOK, array($post_id));
}

function update_rc_post_thumbnail($post_id, $reserved_post_thumb_path)
{
    include_once(ABSPATH . 'wp-admin/includes/image.php');
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($reserved_post_thumb_path);
    $file_name = basename($reserved_post_thumb_path);
    if(wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $file_name;
    } else {
        $file = $upload_dir['basedir'] . '/' . $file_name;
    }
    file_put_contents($file, $image_data);
    $wp_file_type = wp_check_filetype($file_name, null);
    $attachment = array(
        'post_mime_type' => $wp_file_type['type'],
        'post_title' => sanitize_file_name($file_name),
        'post_content' => '',
        'post_status' => 'inherit',
    );
    delete_post_thumbnail($post_id);
    $attachment_id = wp_insert_attachment($attachment, $file, $post_id);
    $attach_data = wp_generate_attachment_metadata($attachment_id, $file);
    if(!empty($attach_data) && !is_wp_error($attach_data)){
        $res = wp_update_attachment_metadata($attachment_id, $attach_data);
        set_post_thumbnail($post_id, $attachment_id);
        
        return $res;
    }
}

// add update message
add_filter('post_updated_messages','add_rc_message');
function add_rc_message($messages)
{
    global  $post, $post_ID;
    $arr_post_types = get_rc_setting(true);
    $post_type = get_post_type($post);
    if(in_array($post_type, $arr_post_types))
    {
        $rc_metas = get_rc_metas($post_ID);
        if("1" == $rc_metas['accept'])
        {
            $add_message_date = date_i18n('Y/m/d @ H:i',  strtotime($rc_metas['date']));
            $str = __('registered reservation update content _RC_DATETIME_',RC_TXT_DOMAIN);
            $add_message = '<br>' . strtr($str, array('_RC_DATETIME_' => $add_message_date));
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
        }
    }
    return $messages;
}

// add reservation info at postlist
function manage_rucy_cols($columns)
{
    $columns['rucy_reservation_date'] = __("Reservation Update DateTime", RC_TXT_DOMAIN);
    return $columns;
}

function add_rucy_col($column_name, $post_id)
{
    $rc_metas = get_rc_metas($post_id);
    $s = "";
    if($column_name == 'rucy_reservation_date')
    {
        $s = $rc_metas['accept'];
        if($s == "1")
        {
            echo $rc_metas['date'];
        } else {
            echo __('None');
        }
    }
}

$accept_post_type = get_rc_setting();
foreach ($accept_post_type as $p){
    if(in_array($p, array('page', 'post'))) {
        $p .= 's';
    }
    add_filter('manage_'.$p.'_columns', 'manage_rucy_cols');
    add_action('manage_'.$p.'_custom_column', 'add_rucy_col', 10, 2);    
}

// setting page
add_action('admin_menu','admin_menu_rucy');
function admin_menu_rucy()
{
    add_options_page('Rucy', 'Rucy', 'manage_options',  'rucy', 'add_rc_setting');
}
function add_rc_setting()
{
    $post = $_POST;
    $is_checked_post = "";
    $is_checked_page = "";
    $custom_post_types = "";
    $error_class = "form-invalid";
    $basic_post_types = array('page','post');
    $invalid_post_types = array('attachment','revision');
    $message = array();
    $error = 0;
    if(isset($post['page_options']))
    {
        $res = "";
        if(isset($post['rc_post']) && $post['rc_post'] == 'post')
        {
            $res .= $post['rc_post'];
            $is_checked_post = "checked";
        } else {
            $error += 1;
        }
        if(isset($post['rc_page']) && $post['rc_page'] == 'page') {
            $res .= "," . $post['rc_page'];
            $is_checked_page = "checked";
        } else {
            $error += 1;
        }
        if($error == 2){
            $message['post_page'] = __("post or page is not allow.", RC_TXT_DOMAIN);
        }
        if(isset($post['rc_custom_post']) && $post['rc_custom_post'] != "") {
            $custom_check = explode(',', $post['rc_custom_post']);
            foreach ($custom_check as $check)
            {
                if(in_array($check, $basic_post_types))
                {
                    $message['custom_post'] = __('Do not input "post" or "page". ', RC_TXT_DOMAIN);
                } else if(!preg_match('/[a-zA-Z0-9_-]/', $check)) {
                    $message['custom_post'] = __("Please input alphabet or numeric. And do not input sequencial commas.", RC_TXT_DOMAIN);
                } else if(in_array($check, $invalid_post_types)){
                    $message['custom_post'] = __('Do not input "attachment" or "revision". ',RC_TXT_DOMAIN);
                }
            }
            $res .= "," . $post['rc_custom_post'];
            $custom_post_types = $post['rc_custom_post'];
        }
        if($res != "" && count($message) == 0)
        {
            update_option(RC_SETTING_OPTION_KEY, $res);
        }
    } else {
        $message = array();
        $arr_setting = get_rc_setting();
        $is_checked_post = (in_array('post', $arr_setting) == TRUE) ? 'checked' : "";
        $is_checked_page = (in_array('page', $arr_setting) == TRUE) ? 'checked' : "";
        $arr_custom_post_types = array();
        foreach ($arr_setting as $v)
        {
            if(!in_array($v, $basic_post_types))
            {
                array_push($arr_custom_post_types, $v);
            }
        }
        $custom_post_types = implode(',', $arr_custom_post_types);
    }
?>
<div class="wrap">
    <h2><?php _e('Rucy Settings', RC_TXT_DOMAIN); ?></h2>
    <p><?php _e('Configure content types reservation update.',RC_TXT_DOMAIN); ?></p>
    <form method="post" action="#">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr class="<?php echo (isset($message['post_page']) == true) ? $error_class : ""; ?>">
                <th><?php _e('post type',RC_TXT_DOMAIN) ?><br><small>*<?php _e('Require',RC_TXT_DOMAIN) ?></small></th>
                <td>
                    <ul>
                        <li><label for="rc_post"><input type="checkbox" id="rc_post" name="rc_post" value="post" <?php echo $is_checked_post ?>><?php _e('post',RC_TXT_DOMAIN) ?></label></li>
                        <li><label for="rc_page"><input type="checkbox" id="rc_page" name="rc_page" value="page" <?php echo $is_checked_page ?>><?php _e('page',RC_TXT_DOMAIN) ?></label></li>
                    </ul>
                    <?php 
                        if(isset($message['post_page']))
                        {
                            echo '<p>'.$message['post_page'].'</p>';
                        }
                    ?>
                </td>
            </tr>
            <tr class="<?php echo (isset($message['custom_post']) == true) ? $error_class : ""; ?>">
                <th><?php _e('custom post type',RC_TXT_DOMAIN) ?></th>
                <td>
                    <input type="text" value="<?php echo $custom_post_types ?>" name="rc_custom_post" placeholder="<?php _e('Separated by commas',RC_TXT_DOMAIN) ?>">
                    <?php 
                        if(isset($message['custom_post']))
                        {
                            echo '<p>'.$message['custom_post'].'</p>';
                        }
                    ?>
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="<?php echo RC_SETTING_OPTION_KEY ?>"/>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>
<?php
}
/**
 * get post type allowed rucy
 * 
 * @param boolean $is_array
 * @return string
 */
function get_rc_setting($is_array = true)
{
    $rc_setting = get_option(RC_SETTING_OPTION_KEY);
    $res = "";
    if(!$rc_setting)
    {
        $rc_setting = RC_POSTTYPE_DEFAULT;
    }
    if($is_array)
    {
        $res = explode(',', $rc_setting);
    } else {
        $res = $rc_setting;
    }
    return $res;
}

// uninstall
if(function_exists('register_uninstall_hook'))
{
    register_uninstall_hook(__FILE__, 'uninstall_rucy');
}
// deactivation
if(function_exists('register_deactivation_hook'))
{
    register_deactivation_hook(__FILE__, 'uninstall_rucy');
}

function uninstall_rucy()
{
    wp_clear_scheduled_hook(RC_CRON_HOOK);
    delete_option(RC_SETTING_OPTION_KEY);
    $allposts = get_posts('numberposts=-1&post_status=');
    $meta_keys = get_rc_metas();
    foreach ($allposts as $postinfo)
    {
        foreach ($meta_keys as $k => $val)
        {
            delete_post_meta($postinfo->ID, $val);
        }
    }
}

// link to setting
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_rc_setting_link');
function add_rc_setting_link($links)
{
    $links[] = '<a href="' . get_admin_url(null, 'options-general.php?page=rucy') . '">' . __('Settings') . '</a>';
    return $links;
}

// activate plugin action
register_activation_hook(plugin_basename(__FILE__), 'install_rucy');
function install_rucy()
{
    $rc_setting = get_option(RC_SETTING_OPTION_KEY);
    if(!$rc_setting)
    {
        $basic_post_types = RC_POSTTYPE_DEFAULT;
        update_option(RC_SETTING_OPTION_KEY, $basic_post_types);
    }
}

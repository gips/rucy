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

add_action('admin_enqueue_scripts','rc_load_jscss');
function rc_load_jscss()
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
function getRcMetas($post_id = "")
{
    $base = array(
        'accept' => 'rc_reserv_accept',
        'content' => 'rc_reserv_content',
        'date' => 'rc_reserv_date'
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
add_action('admin_menu', 'add_rucy_metabox_out');
function add_rucy_metabox_out()
{
    $acceptPostType = getRcSetting();
    foreach ($acceptPostType as $postType)
    {
        add_meta_box('rucy_metabox','Rucy - Reservation Update Content -','add_rucy_metabox_inside',$postType,'normal','high');
    }
    function add_rucy_metabox_inside()
    {
        global $post;
        $rcKeys = getRcMetas();
        $rc_content_name = $rcKeys['content'];
        $rc_accept_name = $rcKeys['accept'];
        $rcMetas = getRcMetas($post->ID);
        $reserv_accept = $rcMetas['accept'];
        $reserv_date = $rcMetas['date'];
        if("" == $reserv_date)
        {
            $reserv_date = date_i18n('Y-m-d H:i:s');
        }
        $reserv_date_arr = getdate(strtotime($reserv_date));
        $current_y = date_i18n('Y');
        $reserv_content = $rcMetas['content'];
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
            for($y = $current_y; $y <= ($current_y + 3); $y++)
            {
                $ySelected = ($y == date_i18n('Y',$reserv_date_arr[0])) ? "selected" : "";
                echo '<option value="'.$y.'" '.$ySelected.'>'.$y.'</option>';
            }
            ?>
            </select>
            <?php echo '/' ?>
            <select name="rc_month">
            <?php
            for($i=1;$i<=12;$i++)
            {
                $m = sprintf("%02d",$i);
                $selected = ($m == date_i18n('m',$reserv_date_arr[0])) ? "selected" : "";
                echo '<option value="'.$m.'" '.$selected.'>'.$m.'</option>';
            }
            ?>
            </select><?php echo '/' ?>
            <select name="rc_day">
            <?php 
            for($d=1;$d<=31;$d++){
                $d = sprintf("%02d",$d);
                $dSelected = ($d == date_i18n('d',$reserv_date_arr[0])) ? "selected" : "";
                echo '<option value="'.$d.'" '.$dSelected.'>'.$d.'</option>';
            }
            ?>
            </select>    
            @
            <select name="rc_hour">
            <?php 
            for($h=0;$h<=23;$h++){
                $h = sprintf("%02d",$h);
                $hSelected = ($h == date_i18n('H',$reserv_date_arr[0])) ? "selected" : "";
                echo '<option value="'.$h.'" '.$hSelected.'>'.$h.'</option>';
            }
            ?>
            </select>
            :
            <select name="rc_minutes">
            <?php 
            for($min=0;$min<=59;$min++){
                $min = sprintf("%02d",$min);
                $minSelected = ($min == date_i18n('i',$reserv_date_arr[0])) ? "selected" : "";
                echo '<option value="'.$min.'" '.$minSelected.'>'.$min.'</option>';
            }
            ?>
            </select>
            <a href="#edit-reservdate" class="rc-datetime-update button"><?php _e('OK',RC_TXT_DOMAIN) ?></a>
            <a href="#edit-reservdate" class="rc-datetime-cancel"><?php _e('Cancel',RC_TXT_DOMAIN) ?></a>
        </div>
        <?php
            $dateArr = array(
                'rc_year' => date_i18n('Y',$reserv_date_arr[0]),
                'rc_month' => date_i18n('m',$reserv_date_arr[0]),
                'rc_day' => date_i18n('d',$reserv_date_arr[0]),
                'rc_hour' => date_i18n('H',$reserv_date_arr[0]),
                'rc_minutes' => date_i18n('i',$reserv_date_arr[0])
            );
            foreach ($dateArr as $k => $v)
            {
                echo '<input type="hidden" name="'.$k.'_cr" id="'.$k.'_cr" value="'.$v.'">';
            }
        ?>
    </div>
<?php 
    wp_editor($reserv_content, $rc_content_name);
    }
}

// save post meta
add_action('save_post','savePostmeta');
function savePostmeta($post_id)
{
    if(isset($_POST) && isset($_POST['post_type']))
    {
        if(isset($_POST['schroeder']) && !wp_verify_nonce($_POST['schroeder'], plugin_basename(__FILE__))){
            return;
        }
        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return;
        }
        $rcKeys = getRcMetas();
        $acceptPostType = getRcSetting();
        if(in_array($_POST['post_type'], $acceptPostType))
        {
            $date = mktime($_POST['rc_hour'], $_POST['rc_minutes'], 00, $_POST['rc_month'], $_POST['rc_day'], $_POST['rc_year']);
            if($date)
            {
                $_POST[$rcKeys['date']] = date_i18n('Y-m-d H:i:s',$date);
            } else {
                $_POST[$rcKeys['date']] = "";
            }
            if(!isset($_POST[$rcKeys['accept']]) || $_POST[$rcKeys['accept']] != "1"){
                $_POST[$rcKeys['accept']]  = "0";
            }
        }
        foreach ($rcKeys as $key => $val)
        {
            savePostMetaBase($post_id, $val);
        }
        if($_POST[$rcKeys['accept']] == "1")
        {
            $reservDate = strtotime(get_gmt_from_date($_POST[$rcKeys['date']]) . " GMT");
            if(in_array($_POST['post_type'], $acceptPostType) || $_POST['post_type'] != 'revision')
            {
                wp_schedule_single_event($reservDate, RC_CRON_HOOK, array($post_id));
            }
        } else if($_POST[$rcKeys['accept']] == "0" || !isset ($_POST[$rcKeys['accept']])) {
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
function savePostMetaBase($post_id, $post_metakey)
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
add_action('rucy_update_reserved_content', 'updateReservedContent','10',1);
function updateReservedContent($post_id)
{
    $rcMetas = getRcMetas($post_id);
    if("1" == $rcMetas['accept'])
    {
        $updates = array(
            'ID' => $post_id,
            'post_content' => $rcMetas['content'],
        );
       wp_update_post($updates,true);
    }
    $dels = getRcMetas();
    foreach ($dels as $key => $del)
    {
        delete_post_meta($post_id, $del);
    }
    wp_clear_scheduled_hook(RC_CRON_HOOK, array($post_id));
}

// add update message
add_filter('post_updated_messages','addRcMessage');
function addRcMessage($messages)
{
    global  $post, $post_ID;
    $arrPostTypes = getRcSetting(true);
    $postType = get_post_type($post);
    if(in_array($postType, $arrPostTypes))
    {
        $rcMetas = getRcMetas($post_ID);
        if("1" == $rcMetas['accept'])
        {
            $addMessageDate = date_i18n('Y/m/d @ H:i',  strtotime($rcMetas['date']));
            $str = __('registered reservation update content _RC_DATETIME_',RC_TXT_DOMAIN);
            $addMessage = '<br>' . strtr($str, array('_RC_DATETIME_' => $addMessageDate));
            // published
            $messages[$postType][1] .= $addMessage;
            $messages[$postType][4] .= $addMessage;
            $messages[$postType][6] .= $addMessage;
            // saved
            $messages[$postType][7] .= $addMessage;
            // submited
            $messages[$postType][8] .= $addMessage;
            // scheduled
            $messages[$postType][9] .= $addMessage;
        }
    }
    return $messages;
}

// add reservation info at postlist
function manageRucyCols($columns) {
    $columns['subtitle'] = __("Reservation Update DateTime", RC_TXT_DOMAIN);
    return $columns;
}
function addRucyCol($column_name, $post_id) {
    $rcMetas = getRcMetas($post_id);
    $s = "";
    if($column_name == 'subtitle')
    {
        $s = $rcMetas['accept'];
    }
    if($s == "1")
    {
        echo $rcMetas['date'];
    } else {
        echo __('None');
    }
}

foreach (array('pages','posts') as $p){
    add_filter('manage_'.$p.'_columns', 'manageRucyCols');
    add_action('manage_'.$p.'_custom_column', 'addRucyCol', 10, 2);    
}

// setting page
add_action('admin_menu','admin_menu_rucy');
function admin_menu_rucy()
{
    add_options_page('Rucy', 'Rucy', 'manage_options',  'rucy', 'addRcSetting');
}
function addRcSetting()
{
    $post = $_POST;
    $isCheckedPost = "";
    $isCheckedPage = "";
    $customPostTypes = "";
    $errorClass = "form-invalid";
    $basicPostTypes = array('page','post');
    $invalidPostTypes = array('attachment','revision');
    $message = array();
    $error = 0;
    if(isset($post['page_options']))
    {
        $res = "";
        if(isset($post['rc_post']) && $post['rc_post'] == 'post')
        {
            $res .= $post['rc_post'];
            $isCheckedPost = "checked";
        } else {
            $error += 1;
        }
        if(isset($post['rc_page']) && $post['rc_page'] == 'page') {
            $res .= "," . $post['rc_page'];
            $isCheckedPage = "checked";
        } else {
            $error += 1;
        }
        if($error == 2){
            $message['post_page'] = __("post or page is not allow.", RC_TXT_DOMAIN);
        }
        if(isset($post['rc_custom_post']) && $post['rc_custom_post'] != "") {
            $customCheck = explode(',', $post['rc_custom_post']);
            foreach ($customCheck as $check)
            {
                if(in_array($check, $basicPostTypes))
                {
                    $message['custom_post'] = __('Do not input "post" or "page". ', RC_TXT_DOMAIN);
                } else if(!preg_match('/[a-zA-Z0-9_-]/', $check)) {
                    $message['custom_post'] = __("Please input alphabet or numeric. And do not input sequencial commas.", RC_TXT_DOMAIN);
                } else if(in_array($check, $invalidPostTypes)){
                    $message['custom_post'] = __('Do not input "attachment" or "revision". ',RC_TXT_DOMAIN);
                }
            }
            $res .= "," . $post['rc_custom_post'];
            $customPostTypes = $post['rc_custom_post'];
        }
        if($res != "" && count($message) == 0)
        {
            update_option(RC_SETTING_OPTION_KEY, $res);
        }
    } else {
        $message = array();
        $arrSetting = getRcSetting();
        $isCheckedPost = (in_array('post', $arrSetting) == TRUE) ? 'checked' : "";
        $isCheckedPage = (in_array('page', $arrSetting) == TRUE) ? 'checked' : "";
        $arrCustomPostTypes = array();
        foreach ($arrSetting as $v)
        {
            if(!in_array($v, $basicPostTypes))
            {
                array_push($arrCustomPostTypes, $v);
            }
        }
        $customPostTypes = implode(',', $arrCustomPostTypes);
    }
?>
<div class="wrap">
    <h2>Rucy Settings</h2>
    <p><?php _e('Configure content types reservation update.',RC_TXT_DOMAIN); ?></p>
    <form method="post" action="#">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr class="<?php echo (isset($message['post_page']) == true) ? $errorClass : ""; ?>">
                <th><?php _e('post type',RC_TXT_DOMAIN) ?><br><small>*<?php _e('Require',RC_TXT_DOMAIN) ?></small></th>
                <td>
                    <ul>
                        <li><label for="rc_post"><input type="checkbox" id="rc_post" name="rc_post" value="post" <?php echo $isCheckedPost ?>><?php _e('post',RC_TXT_DOMAIN) ?></label></li>
                        <li><label for="rc_page"><input type="checkbox" id="rc_page" name="rc_page" value="page" <?php echo $isCheckedPage ?>><?php _e('page',RC_TXT_DOMAIN) ?></label></li>
                    </ul>
                    <?php 
                        if(isset($message['post_page']))
                        {
                            echo '<p>'.$message['post_page'].'</p>';
                        }
                    ?>
                </td>
            </tr>
            <tr class="<?php echo (isset($message['custom_post']) == true) ? $errorClass : ""; ?>">
                <th><?php _e('custom post type',RC_TXT_DOMAIN) ?></th>
                <td>
                    <input type="text" value="<?php echo $customPostTypes ?>" name="rc_custom_post" placeholder="<?php _e('Separated by commas',RC_TXT_DOMAIN) ?>">
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
    <h3><?php _e("Contact",RC_TXT_DOMAIN) ?></h3>
    <p>
    <?php _e('Rucy is maintained by <a href="http://profiles.wordpress.org/gips-nita/">nita</a>.<br>', RC_TXT_DOMAIN) ?>
    <?php _e('If you have found a bug or would like to make a suggestion or contribution why not join the <a href="https://wordpress.org/support/plugin/rucy">Support Form @ wordpress.org</a><br />',RC_TXT_DOMAIN);?>
    <?php _e("Please consider donating if you like this plugin. I've put a lot of my free time into this plugin and donations help to justify it.<br />",RC_TXT_DOMAIN); ?>
    </p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_donations">
        <input type="hidden" name="business" value="J2GV73KGN5MKN">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"border="0" name="submit"alt="PayPal - The safer, easier way to pay online!">
    </form>
</div>
<?php
}
/**
 * get post type allowed rucy
 * 
 * @param boolean $isArray
 * @return string
 */
function getRcSetting($isArray = true)
{
    $rc_setting = get_option(RC_SETTING_OPTION_KEY);
    $res = "";
    if(!$rc_setting)
    {
        $rc_setting = RC_POSTTYPE_DEFAULT;
    }
    if($isArray)
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
    register_uninstall_hook(__FILE__, 'goodbyeRucy');
}
// deactivation
if(function_exists('register_deactivation_hook'))
{
    register_deactivation_hook(__FILE__, 'goodbyeRucy');
}

function goodbyeRucy()
{
    wp_clear_scheduled_hook(RC_CRON_HOOK);
    delete_option(RC_SETTING_OPTION_KEY);
    $allposts = get_posts('numberposts=-1&post_status=');
    $meta_keys = getRcMetas();
    foreach ($allposts as $postinfo)
    {
        foreach ($meta_keys as $k => $val)
        {
            delete_post_meta($postinfo->ID, $val);
        }
    }
}

// link to setting
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'hiRucy');
function hiRucy($links)
{
    $links[] = '<a href="' . get_admin_url(null, 'options-general.php?page=rucy') . '">' . __('Settings') . '</a>';
    return $links;
}

// activate plugin action
register_activation_hook(plugin_basename(__FILE__), 'helloRucy');
function helloRucy()
{
    $rc_setting = get_option(RC_SETTING_OPTION_KEY);
    if(!$rc_setting)
    {
        $basicPostTypes = RC_POSTTYPE_DEFAULT;
        update_option(RC_SETTING_OPTION_KEY, $basicPostTypes);
    }
}
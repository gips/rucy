<?php
/*
 * Plugin Name: Rucy
 * Plugin URI: https://github.com/gips/rucy
 * Description: update published content.
 * Version: 0.0.1
 * Author: Nita
 * License: GPLv2 or later
 * Text Domain: rucy
 * Domain Path: /lang
 */
define('RC_PLUGIN_URL',  plugin_dir_url(__FILE__));
define('RC_SETTING_OPTION_KEY', 'rucy_post_type');
define('RC_TXT_DOMAIN', 'rucy');
define('RC_POSTTYPE_DEFAULT','post,page');

// load_plugin_textdomain( RP_TXT_DOMAIN, false, 'rucy/lang');
add_action('admin_enqueue_scripts','rc_load_jscss');
function rc_load_jscss()
{
    global $hook_suffix;
    $files = array('post.php','post-new.php');
    if(in_array($hook_suffix, $files))
    {
        wp_register_style('rucy.css', RC_PLUGIN_URL . 'rucy.css',array(),'0.0.1');
        wp_enqueue_style('rucy.css');
        wp_register_script('rucy.js', RC_PLUGIN_URL . 'rucy.js', array('jquery'), '0.0.1');
        wp_enqueue_script('rucy.js');
    }
}

// setting page
add_action('admin_menu','admin_menu_rucy');
function admin_menu_rucy()
{
    add_options_page('Rucy', 'Rucy', 'manage_options',  'rucy', 'addRcSetting');
}
function addRcSetting()
{
    $rc_opt_key = RC_SETTING_OPTION_KEY;
    $post = $_POST;
    $isCheckedPost = "";
    $isCheckedPage = "";
    $arrCustomPostTypes = "";
    $customPostTypes = "";
    $error = false;
    if(isset($post['page_options']))
    {
        $res = "";
        //TODO: validate
        if(isset($post['rc_post']) && $post['rc_post'] == 'post')
        {
            $res .= $post['rc_post'];
            $isCheckedPost = "checked";
        }
        if(isset($post['rc_page']) && $post['rc_page'] == 'page') {
            $res .= "," . $post['rc_page'];
            $isCheckedPage = "checked";
        }
        if(isset($post['rc_custom_post']) && $post['rc_custom_post'] != "") {
            $customCheck = explode(',', $post['rc_custom_post']);
            $i = 0;
            foreach ($customCheck as $check){
                // TODO: invalid post
                if(!preg_match('/^page$/', $check)
                && !preg_match('/^post$/', $check)
                && !preg_match('/[a-zA-Z0-9_-]/', $check))
                {
                   $i++;
                }
            }
            if($i > 0)
            {
                $error = true;
            }
            $res .= "," . $post['rc_custom_post'];
            $customPostTypes = $post['rc_custom_post'];
        }
        if($res != "" && $error == false)
        {
            update_option($rc_opt_key, $res);
        }
    } else {
        $arrSetting = getRcSetting();
        $isCheckedPost = (in_array('post', $arrSetting) == TRUE) ? 'checked' : "";
        $isCheckedPage = (in_array('page', $arrSetting) == TRUE) ? 'checked' : "";
        $arrCustomPostTypes = array();
        foreach ($arrSetting as $v)
        {
            if($v !== "post" && $v !== "page")
            {
                array_push($arrCustomPostTypes, $v);
            }
        }
        $customPostTypes = implode(',', $arrCustomPostTypes);
    }
?>
<div class="wrap">
    <h2>Rucy Settings</h2>
    <p>Configure content types reservation update.</p>
        <?php
        if($error)
        {
            echo '<div class="form-invalid"><b>'._e('error','reserv-post').'!</b>'._e('post or page is require.','reserv-post').'</div>';
        }
        ?>
    <form method="post" action="#">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr>
                <th><?php _e('post type','reserv-post') ?><br><small>*<?php _e('Require','reserv-post') ?></small></th>
                <td>
                    <ul>
                        <li><label for="rc_post"><input type="checkbox" name="rc_post" value="post" <?php echo $isCheckedPost ?>><?php _e('post','reserv-post') ?></label></li>
                        <li><label for="rc_page"><input type="checkbox" name="rc_page" value="page" <?php echo $isCheckedPage ?>><?php _e('page','reserv-post') ?></label></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <th><?php _e('custom post type','reserv-post') ?></th>
                <td>
                    <input type="text" value="<?php echo $customPostTypes ?>" name="rc_custom_post" placeholder="<?php _e('Separated by commas','reserv-post') ?>">
                </td>
            </tr>
        </table>
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="<?php echo $rc_opt_key ?>"/>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>
<?php
}

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


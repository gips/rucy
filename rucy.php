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
    $post = $_POST;
    $isCheckedPost = "";
    $isCheckedPage = "";
    $arrCustomPostTypes = "";
    $customPostTypes = "";
    $errorClass = "form-invalid";
    $message = array();
    $error = 0;
    if(isset($post['page_options']))
    {
        $res = "";
        //TODO: validate
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
        }  else {
            $error += 1;
        }
        if($error == 2){
            $message['post_page'] = __("post or page is not allow.", RC_TXT_DOMAIN);
        }
        if(isset($post['rc_custom_post']) && $post['rc_custom_post'] != "") {
            $customCheck = explode(',', $post['rc_custom_post']);
            var_dump($customCheck);
            foreach ($customCheck as $check){
                if(preg_match('/^page$/', $check) || preg_match('/^post$/', $check))
                {
                    $message['custom_post'] = __('Do not input "post" or "page". ', RC_TXT_DOMAIN);
                } else if(!preg_match('/[a-zA-Z0-9_-]/', $check)) {
                    $message['custom_post'] = __("Please input alphabet or numeric. And do not input sequencial commas.", RC_TXT_DOMAIN);
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
            if(!in_array($v, array('post','page')))
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
    <form method="post" action="#">
        <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr class="<?php echo (isset($message['post_page']) == true) ? $errorClass : ""; ?>">
                <th><?php _e('post type','reserv-post') ?><br><small>*<?php _e('Require','reserv-post') ?></small></th>
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


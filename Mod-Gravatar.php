<?php
/**
 * Project: Gravatar 4 SMF
 * Version: 1.0
 * File: Mod-Gravatar.php
 * Author: digger
 * Date: 06.09.12
 * License: CC BY-NC-ND http://creativecommons.org/licenses/by-nc-nd/3.0/
 */

if (!defined('SMF'))
    die('Hacking attempt...');

function addGravatarAdminArea(&$admin_areas)
{
    global $txt;
    loadLanguage('Gravatar/');

    $admin_areas['config']['areas']['modsettings']['subsections']['gravatar'] = array($txt['gravatar_admin_menu']);
}

function loadGravatarAdminJS()
{
    global $context, $modSettings;

    $context['insert_after_template'] .= '
                <script type="text/javascript"><!-- // --><![CDATA[
                function updateGravatar(){
                var gravatarType = document.getElementById("gravatar_style").value;
		            document.getElementById("gravatar_example").src="http://gravatar.com/avatar/00000000000000000000000000000000?d=" + gravatarType + "&amp;s=65";
		            };
                // ]]></script>';

}

function addGravatarAdminAction(&$subActions)
{
    $subActions['gravatar'] = 'addGravatarAdminSettings';
}

function addGravatarAdminSettings($return_config = false)
{
    global $txt, $scripturl, $context, $modSettings;
    loadLanguage('Gravatar/');
    loadGravatarAdminJS();

    $context['page_title'] = $context['settings_title'] = $txt['gravatar_admin_menu'];
    $context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=gravatar';

    $config_vars = array(
        array('check', 'gravatar_enabled'),
        array('select', 'gravatar_rating',
            array(
                'g' => $txt['gravatar_rating_g'],
                'pg' => $txt['gravatar_rating_pg'],
                'r' => $txt['gravatar_rating_r'],
                'x' => $txt['gravatar_rating_x'],
            ),
            'subtext' => $txt['gravatar_rating_help'],
        ),
        array('select', 'gravatar_style',
            array(
                'wavatar' => $txt['gravatar_style_wavatar'],
                'identicon' => $txt['gravatar_style_identicon'],
                'monsterid' => $txt['gravatar_style_monsterid'],
                'retro' => $txt['gravatar_style_retro'],
                'mm' => $txt['gravatar_style_mm'],
            ),
            'subtext' => $txt['gravatar_style_help'],
            'postinput' => '<div style="margin-top: 3px;"><img id="gravatar_example" src="http://gravatar.com/avatar/00000000000000000000000000000000?d=' . $modSettings['gravatar_style'] . '&amp;s=65" alt="" /></div>',
            'javascript' => 'onchange="updateGravatar()"',
        ),
    );

    if ($return_config)
        return $config_vars;

    if (isset($_GET['save'])) {
        checkSession();
        $save_vars = $config_vars;
        saveDBSettings($save_vars);
        redirectexit('action=admin;area=modsettings;sa=gravatar');
    }

    prepareDBSettingContext($config_vars);
}

function getGravatar($email = '')
{
    global $modSettings;

    $gravatarHash = md5(strtolower($email));
    $gravatarStyle = !empty($modSettings['gravatar_style']) ? $modSettings['gravatar_style'] : 'wavatar';
    $gravatarRating = !empty($modSettings['gravatar_rating']) ? $modSettings['gravatar_rating'] : 'g';
    $gravatarWidth = !empty($modSettings['avatar_max_width_external']) ? $modSettings['avatar_max_width_external'] : 65;
    $gravatarHeight = !empty($modSettings['avatar_max_height_external']) ? $modSettings['avatar_max_height_external'] : 65;
    $gravatarSize = $gravatarWidth < $gravatarHeight ? $gravatarWidth : $gravatarHeight;
    $gravatar = 'http://gravatar.com/avatar/' . $gravatarHash . '?d=' . $gravatarStyle . '&amp;s=' . $gravatarSize . '&amp;r=' . $gravatarRating;

    return $gravatar;
}

function addGravatarForCurrentUser()
{
    global $modSettings, $user_info;

    if (!empty($modSettings['gravatar_enabled']) && empty($user_info['avatar']['url']) && empty($user_info['avatar']['filename'])) {
        $user_info['avatar']['url'] = getGravatar($user_info['email']);
    } else return false;
}

function addGravatarsForUsers()
{
    global $modSettings, $user_profile;

    if (empty($modSettings['gravatar_enabled']) || empty($user_profile)) return false;

    foreach (array_keys($user_profile) as $user_id) {
        if (empty($user_profile[$user_id]['avatar']) && empty($user_profile[$user_id]['filename'])) {
            $user_profile[$user_id]['avatar'] = getGravatar($user_profile[$user_id]['email_address']);
        }
    }
}

function addGravatarCopyright()
{
    global $context;

    if ($context['current_action'] == 'credits')
        $context['copyrights']['mods'][] = 'Gravatar 4 SMF</a> &copy; 2012, digger';
}
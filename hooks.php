<?php
/**
 * Project: Gravatar 4 SMF
 * Version: 1.0
 * File: hooks.php
 * Author: digger
 * Date: 06.09.12
 * License: CC BY-NC-ND http://creativecommons.org/licenses/by-nc-nd/3.0/
 *
 * To run this install manually please make sure you place this
 * in the same place and SSI.php and index.php
 */

global $context, $user_info;

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
    require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
    die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
    die('Admin privileges required.');

if (!empty($context['uninstalling']))
    $call = 'remove_integration_function';
else
    $call = 'add_integration_function';

$hooks = array(
    'integrate_pre_include' => '$sourcedir/Mod-Gravatar.php',
    'integrate_admin_areas' => 'addGravatarAdminArea',
    'integrate_modify_modifications' => 'addGravatarAdminAction',
    'integrate_load_theme' => 'addGravatarForCurrentUser',
    'integrate_menu_buttons' => 'addGravatarsForUsers',
);

$call('integrate_menu_buttons', 'addGravatarCopyright');

foreach ($hooks as $hook => $function)
    $call($hook, $function);

if (SMF == 'SSI')
    echo 'Database changes are complete! <a href="/">Return to the main page</a>.';
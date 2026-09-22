<?php
/**
 * Plugin Name: Inline Image Upload for BBPress
 * Description: Upload inline images to BBPress forum topics and replies.
 * Version: 1.2.0
 * Author: BerryPress
 * Author URI: https://berrypress.com/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-plugin-author-uri
 * License: GNU General Public License version 3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 6.5.0
 * Tested up to:      7.1
 * Requires PHP:      7.0
 */

/*

Image Upload for bbPress plugin
Copyright (C) 2026 BerryPress

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

========

Credits:

This plugin includes code based on and/or copied from parts of WordPress
by Automattic, released under the GNU General Public License (GPL) version 2 or later,
licensed under GPL version 3 or later (see wp-license.txt in the license directory
for the license, copyright, and additional credits applicable to WordPress, and the
license.txt file in the license directory for GPL version 3 text).

This plugin includes code based on and/or copied from parts of bbPress,
copyright The bbPress Contributors, released under the GNU General Public License (GPL)
version 2 or later, licensed under GPL version 3 or later (see the license.txt file in
the license directory for GPL version 3 text).

*/

defined('ABSPATH') || exit;

/*
 * Image storage engine
 *
 * Image storage is handled by pluggable engine classes (includes/FilesystemStorage.php,
 * includes/InfiniteUploadsStorage.php) that are shared, unchanged, with Image Upload for bbPress
 * Pro. Those classes call a handful of helper functions and a filter by fixed name
 * (hm_bbpui_get_option(), hm_bbpui_set_options(), hm_bbpui_check_storage_dir(),
 * hm_bbpui_get_temp_storage_dir(), hm_bbpui_filter_existing_images(), and the
 * 'hm_bbpuip_storage_engines' filter) so this plugin provides them under those same names -
 * this whole block is written to stay copy/paste-compatible with Pro's equivalent code.
 */

define('HM_BBPUI_VERSION', '1.2.0');

function hm_bbpui_option_defaults()
{
    return array(
        'storage_dir' => '{uploads}/hm_bbpui',
        'storage_url' => '{uploads}/hm_bbpui',
        'storage_engine' => 'filesystem',
        'temp_storage_dir' => '{uploads}/hm_bbpui_temp',
        'temp_storage_url' => '{uploads}/hm_bbpui_temp',
    );
}

function hm_bbpui_get_option($option)
{
    global $hm_bbpui_options;
    if (!isset($hm_bbpui_options)) {
        $hm_bbpui_options = get_option('hm_bbpuip_options');
        if (empty($hm_bbpui_options)) {
            $hm_bbpui_options = hm_bbpui_option_defaults();
            update_option('hm_bbpuip_options', $hm_bbpui_options);
        }
    }
    if (isset($hm_bbpui_options[$option]) && $hm_bbpui_options[$option] !== '') {
        return $hm_bbpui_options[$option];
    }
    $defaults = hm_bbpui_option_defaults();
    return isset($defaults[$option]) ? $defaults[$option] : null;
}

function hm_bbpui_set_options($options)
{
    global $hm_bbpui_options;
    if (!isset($hm_bbpui_options)) {
        $hm_bbpui_options = get_option('hm_bbpuip_options');
        if (empty($hm_bbpui_options)) {
            $hm_bbpui_options = hm_bbpui_option_defaults();
        }
    }
    $hm_bbpui_options = array_merge($hm_bbpui_options, array_intersect_key($options, hm_bbpui_option_defaults()));
    update_option('hm_bbpuip_options', $hm_bbpui_options);
}

add_filter('infinite_uploads_sync_exclusions', 'hm_bbpui_iu_exclude_temp_dir');
function hm_bbpui_iu_exclude_temp_dir($excl)
{
	$storageDir = hm_bbpui_get_option('temp_storage_dir');
	
	if (strpos($storageDir, '{uploads}/') === 0) {
		$excl[] = substr($storageDir, 9);
	}
	
	return $excl;
}

function hm_bbpui_check_storage_dir($dir)
{
    if (strpos($dir, '{uploads}') !== false) {
        $uploadDir = HM_BBPUI_Filesystem_Storage::getUploadDir();
        $dir = str_replace('{uploads}', $uploadDir['basedir'], $dir);
    }

	require_once(ABSPATH.'/wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
		
    if (!$wp_filesystem->is_dir($dir) && !wp_mkdir_p($dir))
        return false;
    if (!$wp_filesystem->is_dir($dir . '/0') && !wp_mkdir_p($dir . '/0'))
        return false;
    if (!file_put_contents($dir . '/0/test', 'test'))
        return false;
    $wp_filesystem->delete($dir . '/0', true);
    return true;
}

function hm_bbpui_get_temp_storage_dir()
{
    $storageDir = hm_bbpui_get_option('temp_storage_dir');
    if (strpos($storageDir, '{uploads}') !== false) {
        $uploadDir = HM_BBPUI_Filesystem_Storage::getUploadDir();
        $storageDir = str_replace('{uploads}', $uploadDir['basedir'], $storageDir);
    }
	
	require_once(ABSPATH.'/wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
		
    if (!$wp_filesystem->is_dir($storageDir) && !wp_mkdir_p($storageDir))
        return false;
    return $storageDir;
}

function hm_bbpui_get_temp_storage_url()
{
    $storageUrl = hm_bbpui_get_option('temp_storage_url');
    if (strpos($storageUrl, '{uploads}') !== false) {
        $uploadDir = HM_BBPUI_Filesystem_Storage::getUploadDir();
        $storageUrl = str_replace('{uploads}', $uploadDir['baseurl'], $storageUrl);
    }
    return $storageUrl;
}

function hm_bbpui_filter_existing_images($postId, $images)
{
    $post = get_post($postId);
    if (empty($post)) {
        return array();
    }
    foreach ($images as $i => $image) {
        if (strpos($post->post_content, $postId . '/' . $image) !== false || strpos($post->post_content, $postId . '/T' . $image) !== false) {
            unset($images[$i]);
        }
    }
    return $images;
}

function hm_bbpui_get_storage_engines()
{
    global $hm_bbpuip_storage_engines;
    if (!isset($hm_bbpuip_storage_engines)) {
        $hm_bbpuip_storage_engines = apply_filters('hm_bbpuip_storage_engines', array());
    }
    return $hm_bbpuip_storage_engines;
}

function hm_bbpui_get_storage_class()
{
    $engines = hm_bbpui_get_storage_engines();
    $enabledEngine = hm_bbpui_get_option('storage_engine');
    return $engines[$enabledEngine == 'filesystem' || (isset($engines[$enabledEngine]) && $engines[$enabledEngine]['class']::isAvailable()) ? $enabledEngine : 'filesystem']['class'];
}

function hm_bbpui_get_storage($postId, $create = true)
{
    $engine = hm_bbpui_get_storage_class();
    return new $engine($postId, $create);
}

add_action('plugins_loaded', function() {
	$includesDir = __DIR__ . '/includes/';
	
	// Include default filesystem storage engine
	include($includesDir . 'FilesystemStorage.php');
	// Infinite Uploads (https://infiniteuploads.com) offloads whatever the Filesystem engine already
	// wrote to disk - not a separate storage backend, so this just gates the option on the companion
	// plugin being active.
	include($includesDir . 'InfiniteUploadsStorage.php');
	$storageClass = hm_bbpui_get_storage_class();
	if (method_exists($storageClass, 'init')) {
		$storageClass::init();
	}
});

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'hm_bbpui_action_links');
function hm_bbpui_action_links($links) {
	$upgradeLink = '<a href="https://berrypress.com/product/bbpress/image-upload-for-bbpress-pro/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-row-link" target="_blank">'.esc_html__('Upgrade to Pro', 'image-upload-for-bbpress').'</a>';
	array_unshift($links, $upgradeLink);
	array_unshift($links, '<a href="'.esc_url(get_admin_url(null, 'admin.php?page=hm_bbpui')).'">'.esc_html__('Settings', 'image-upload-for-bbpress').'</a>');
	return $links;
}

add_action('admin_menu', 'hm_bbpui_admin_menu');
function hm_bbpui_admin_menu() {
	add_submenu_page('berrypress', 'Image Upload for BBPress', 'Forum Images', 'activate_plugins', 'hm_bbpui', 'hm_bbpui_admin_page');
}

if (is_admin() && !class_exists('BerryPressBbpImageUpload\\Admin\\Page')) {
	require_once(__DIR__.'/includes/berrypress-admin-framework/Page.php');
	\BerryPressBbpImageUpload\Admin\Page::maybeRegisterBerryPressAdminMenu();
}

add_action('admin_enqueue_scripts', 'hm_bbpui_admin_scripts');
function hm_bbpui_admin_scripts() {
	$screen = get_current_screen();
	if ($screen && $screen->id === 'berrypress_page_hm_bbpui') {
		wp_enqueue_style('berrypress-bbpui-admin-page', plugins_url('includes/berrypress-admin-framework/assets/css/global-admin-page.min.css', __FILE__), ['berrypress-bbpui-admin-global'], HM_BBPUI_VERSION);
		wp_enqueue_script('berrypress-bbpui-admin-page', plugins_url('includes/berrypress-admin-framework/assets/js/global-admin-page.js', __FILE__), ['jquery'], HM_BBPUI_VERSION, true);
		wp_enqueue_script('hm-bbpui-tooltips', plugins_url('js/bp-tooltip.js', __FILE__), [], HM_BBPUI_VERSION, true);
		wp_enqueue_style('hm_bbpui_admin', plugins_url('css/admin.min.css', __FILE__), ['berrypress-bbpui-admin-page'], HM_BBPUI_VERSION);
		add_filter('admin_body_class', 'hm_bbpui_admin_body_class');
	}
	wp_enqueue_style('berrypress-bbpui-admin-global', plugins_url('includes/berrypress-admin-framework/assets/css/global-admin.min.css', __FILE__), [], HM_BBPUI_VERSION);
}

function hm_bbpui_admin_body_class($classes) {
	$classes .= ' berrypress-page ';
	return $classes;
}

function hm_bbpui_admin_page() {
	require_once(__DIR__.'/includes/admin-page.php');
	$page = new HmBbpuiAdminPage();
	$page->render();
}

add_filter('bbp_after_get_the_content_parse_args', 'hm_bbpiu_modify_editor', 9999);
function hm_bbpiu_modify_editor($args = array()) {
	if (!isset($args['tinymce']) || !is_array($args['tinymce']))
		$args['tinymce'] = array();
	$args['tinymce']['paste_as_text'] = true;
	$args['tinymce']['file_browser_callback'] = 'function(field_id){hm_bbpui_file_upload(field_id);}';
	return $args;
}

add_filter('mce_buttons', 'hm_bbpui_mce_buttons', 99);
function hm_bbpui_mce_buttons($buttons) {
	if (function_exists('is_bbpress') && is_bbpress() && !in_array('image', $buttons)) {
		$buttons[] = 'image';
	}
	return $buttons;
}

add_filter('bbp_get_tiny_mce_plugins', 'hhm_bbpui_tinymce_plugins');
function hhm_bbpui_tinymce_plugins($plugins = array()) {
	$plugins[] = 'paste';
	return $plugins;
}

add_action('init', 'hm_bbpui_handle_upload');
function hm_bbpui_handle_upload() {
	// phpcs:disable WordPress.Security.NonceVerification -- nonces are not required for image upload because this does not pose a CSRF risk.
	// 														 Uploaded images are validated and placed in a temporary directory under a random
	//														 filename, where they are automatically deleted if not used in a forum post. They
	//														 cannot be used in a post without knowing the random filename that was assigned. If
	// 														 a file is uploaded via CSRF, it will simply be deleted when the cleanup function is
	//														 run and it is old enough.
	
	if (empty($_GET['hm_bbpui_do_upload']))
		return;
	
	// Check capabilities
	if (!(
			current_user_can('publish_topics') || current_user_can('publish_replies')
			|| (!is_user_logged_in() && get_option('_bbp_allow_anonymous', false))
		))
		hm_bbpui_upload_error();
	
	// Check file upload
	if (!isset($_FILES['hm_bbpui_file']['tmp_name']) || !empty($_FILES['hm_bbpui_file']['error']))
		hm_bbpui_upload_error();
	
	$uploadedFile = sanitize_text_field($_FILES['hm_bbpui_file']['tmp_name']);
	if (!is_uploaded_file($uploadedFile)) {
		hm_bbpui_upload_error();
	}
	
	// Get/create temp directory
	$tempUploadDir = hm_bbpui_get_temp_storage_dir();
	if (!$tempUploadDir)
		hm_bbpui_upload_error();

	require_once(ABSPATH.'/wp-admin/includes/file.php');
	WP_Filesystem();

	global $wp_filesystem;

	// Get temp filename
	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$maxChar = strlen($chars) - 1;
	do {
		$tempName = '';
		for ($i = 0; $i < 32; ++$i)
			$tempName .= $chars[random_int(0, $maxChar)];
	} while ($wp_filesystem->exists($tempUploadDir.'/'.$tempName));
	
	// Try to increase memory limit
	// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
	@ini_set('memory_limit', '256M');
	
	// Save as an image file (for security reasons)
	switch (strtolower( sanitize_text_field($_FILES['hm_bbpui_file']['type'] ?? '') )) {
		case 'image/jpeg':
			$img = imagecreatefromjpeg($uploadedFile) or hm_bbpui_upload_error();
			$img = bbpui_apply_exif_rotation($img, $uploadedFile) or hm_bbpui_upload_error();
			$tempName .= '.jpg';
			imagejpeg($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			break;
		case 'image/png':
			$img = imagecreatefrompng($uploadedFile) or hm_bbpui_upload_error();
			imagesavealpha($img, true) or hm_bbpui_upload_error();
			imagealphablending($img, false) or hm_bbpui_upload_error();
			$tempName .= '.png';
			imagepng($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			break;
		case 'image/gif':
			$img = imagecreatefromgif($uploadedFile) or hm_bbpui_upload_error();
			$tempName .= '.gif';
			imagegif($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			break;
		default:
			if ($img = @imagecreatefromjpeg($uploadedFile)) {
				$img = bbpui_apply_exif_rotation($img, $uploadedFile) or hm_bbpui_upload_error();
				$tempName .= '.jpg';
				imagejpeg($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			} else if ($img = @imagecreatefrompng($uploadedFile)) {
				imagesavealpha($img, true) or hm_bbpui_upload_error();
				imagealphablending($img, false) or hm_bbpui_upload_error();
				$tempName .= '.png';
				imagepng($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			} else if ($img = @imagecreatefromgif($uploadedFile)) {
				$tempName .= '.gif';
				imagegif($img, $tempUploadDir.'/'.$tempName) or hm_bbpui_upload_error();
			} else {
				hm_bbpui_upload_error();
			}
	}
	
	@$wp_filesystem->delete($uploadedFile);
	echo(esc_url(hm_bbpui_get_temp_storage_url().'/'.$tempName));
	exit;
	
	// phpcs:enable WordPress.Security.NonceVerification
}

function hm_bbpui_upload_error() {
	echo('Error');
	exit;
}

function bbpui_apply_exif_rotation($img, $sourceFile) {
	if (function_exists('exif_read_data')) {
		$exifData = @exif_read_data($sourceFile);
		if (!empty($exifData['Orientation'])) {
			switch ($exifData['Orientation']) {
				case 8:
					$img = imagerotate($img, 90, 0);
					break;
				case 3:
					$img = imagerotate($img, 180, 0);
					break;
				case 6:
					$img = imagerotate($img, 270, 0);
					break;
			}
		}
	}
	return $img;
}

add_action('wp_enqueue_scripts', 'hm_bbpui_enqueue_scripts');
function hm_bbpui_enqueue_scripts() {
	wp_enqueue_script('hm_bbpui', plugins_url('js/bbp-image-upload.js', __FILE__), array('jquery'), HM_BBPUI_VERSION, true);
	wp_enqueue_style('hm_bbpui', plugins_url('css/bbp-image-upload.css', __FILE__), [], HM_BBPUI_VERSION);
}

add_action('wp_insert_post', 'hm_bbpui_insert_post', 10, 3);
function hm_bbpui_insert_post($postId, $post, $isUpdate) {
	if ($post->post_type != 'topic' && $post->post_type != 'reply')
		return;

	$tempUrl = hm_bbpui_get_temp_storage_url();
	$storage = hm_bbpui_get_storage($postId);

	// For some reason the passed $post may have empty content in some cases - then we need to retrieve the post again
	if ( empty($post->post_content) ) {
		hm_bbpui_force_clear_post_cache($postId);
		$post = get_post($postId);
	}

	// If this was an update, clean up any images that were removed
	if ($isUpdate && method_exists($storage, 'cleanImages')) {
		$storage->cleanImages();
	}

	preg_match_all('/' . preg_quote($tempUrl, '/') . '\/(.+)["\']/iU', $post->post_content, $matches);

	if (!empty($matches[1])) {

		$tempDir = hm_bbpui_get_temp_storage_dir();

		foreach(array_unique($matches[1]) as $match) {
			if (strpos($match, '/') !== false || strpos($match, '\\') !== false)
				continue;
			try {
				$imageUrl = $storage->put($tempDir.'/'.$match);
				$post->post_content = str_replace($tempUrl.'/'.$match, $imageUrl, $post->post_content);
			} catch (Exception $ex) {
				@wp_delete_file($tempDir.'/'.$match);
			}
		}

		remove_action('wp_insert_post', 'hm_bbpui_insert_post', 10);

		// Temporarily disable revisioning
		if (($saveRevisionPriority = has_action('post_updated', 'wp_save_post_revision'))) {
			remove_action('post_updated', 'wp_save_post_revision', $saveRevisionPriority);
		}
		// Temporarily remove edit_post actions (deals with BuddyPress activity feed problem)
		global $wp_filter;
		if (isset($wp_filter['edit_post'])) {
			$edit_post_temp = $wp_filter['edit_post'];
			unset($wp_filter['edit_post']);
		}
		wp_update_post($post);

		// Restore edit_post actions
		if (isset($edit_post_temp)) {
			$wp_filter['edit_post'] = $edit_post_temp;
		}

		add_action('post_updated', 'wp_save_post_revision', $saveRevisionPriority);
		add_action('wp_insert_post', 'hm_bbpui_insert_post', 10, 3);

		hm_bbpui_force_clear_post_cache($postId);
	}
}

function hm_bbpui_force_clear_post_cache($postId)
{
	$wasSuspended = wp_suspend_cache_invalidation(false);
	clean_post_cache($postId);
	if ($wasSuspended !== false) {
		wp_suspend_cache_invalidation($wasSuspended);
	}
}

add_action('delete_post', 'hm_bbpui_delete_post');
function hm_bbpui_delete_post($postId) {
	$post = get_post($postId);
	if (empty($post) || ($post->post_type != 'topic' && $post->post_type != 'reply'))
		return;

	try {
		hm_bbpui_get_storage($postId, false)->deleteStorage();
	} catch (Exception $ex) {
		// No storage exists for this post (e.g. it never had any images uploaded) - nothing to do.
	}
}

add_action('hm_bbpui_clean_temp_dir', 'hm_bbpui_clean_temp_dir');
function hm_bbpui_clean_temp_dir() {
	$tempDir = hm_bbpui_get_temp_storage_dir();
	$timeThreshold = time() - 86400;

	require_once(ABSPATH.'/wp-admin/includes/file.php');
	WP_Filesystem();

	global $wp_filesystem;
	foreach ($wp_filesystem->dirlist($tempDir) as $dirItem) {
		if ($dirItem['lastmodunix'] < $timeThreshold)
			@$wp_filesystem->delete($tempDir.'/'.$dirItem['name']);
	}
}

function hm_bbpui_cleanup() {
	$storageClass = hm_bbpui_get_storage_class();

	// Delete storage for non-existent posts
	try {
		foreach ($storageClass::getStorageIds() as $storageId) {
			if (get_post_status($storageId) === false) {
				try {
					hm_bbpui_get_storage($storageId, false)->deleteStorage();
				} catch (Exception $ex) {
				}
			}
		}
	} catch (Exception $ex) {
	}

	// Delete images that were removed from existing posts
	if (method_exists($storageClass, 'cleanAllImages')) {
		try {
			$storageClass::cleanAllImages(false);
		} catch (Exception $ex) {
		}
	}

	// Clean temp directory
	hm_bbpui_clean_temp_dir();
}

register_activation_hook(__FILE__, 'hm_bbpui_activate');
function hm_bbpui_activate() {
	// Schedule temp dir cleaning
	wp_schedule_event(time(), 'daily', 'hm_bbpui_clean_temp_dir');
}

register_deactivation_hook(__FILE__, 'hm_bbpui_deactivate');
function hm_bbpui_deactivate() {
	// Unschedule temp dir cleaning
	wp_clear_scheduled_hook('hm_bbpui_clean_temp_dir');
}

/* Review/donate notice */

register_activation_hook(__FILE__, 'hm_bbpui_first_activate');
function hm_bbpui_first_activate() {
	$pre = 'hm_bbpui';
	$firstActivate = get_option($pre.'_first_activate');
	if (empty($firstActivate)) {
		update_option($pre.'_first_activate', time());
	}
}
if (is_admin() && get_option('hm_bbpui_rd_notice_hidden') != 1 && time() - get_option('hm_bbpui_first_activate') >= (14*86400)) {
	add_action('admin_notices', 'hm_bbpui_rd_notice');
	add_action('wp_ajax_hm_bbpui_rd_notice_hide', 'hm_bbpui_rd_notice_hide');
}
function hm_bbpui_rd_notice() {
	echo('
		<div id="hm_bbpui_rd_notice" class="updated notice is-dismissible"><p>Do you use the <strong>Image Upload for BBPress</strong> plugin?
		Please support our free plugin by <a href="https://wordpress.org/support/view/plugin-reviews/image-upload-for-bbpress" target="_blank">writing a review</a> and/or <a href="https://potentplugins.com/donate/?utm_source=image-upload-for-bbpress'.'&amp;utm_medium=link&amp;utm_campaign=wp-plugin-notice-donate-link" target="_blank">making a donation</a>!
		Thanks!</p></div>
		<script>jQuery(document).ready(function($){$(\'#hm_bbpui_rd_notice\').on(\'click\', \'.notice-dismiss\', function(){jQuery.post(ajaxurl, {action:\'hm_bbpui_rd_notice_hide\'})});});</script>
	');
}
function hm_bbpui_rd_notice_hide() {
	$pre = 'hm_bbpui';
	update_option($pre.'_rd_notice_hidden', 1);
}


?>

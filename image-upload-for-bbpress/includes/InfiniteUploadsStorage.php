<?php
defined('ABSPATH') || exit;

class HM_BBPUI_InfiniteUploads_Storage extends HM_BBPUI_Filesystem_Storage
{
    public static $id = 'infinite_uploads';

    public static function adminDisplay()
    {
        if (self::isAvailable()) { ?>
            <p class="description">
                <i class="berrypress-icon-check_circle" style="color:#098f43" aria-hidden="true"></i>
                <?php echo esc_html(sprintf(
                    /* translators: %s: storage directory path */
                    __('Infinite Uploads is active. Images are stored in the same directory as the Filesystem engine (%s) and automatically offloaded to the cloud and optimized - no extra setup required.', 'image-upload-for-bbpress'),
                    hm_bbpui_get_option('storage_dir')
                )); ?>
            </p>
        <?php } else { ?>
            <div class="bbpuip-partner-note">
                <p><strong><?php esc_html_e('Infinite Uploads', 'image-upload-for-bbpress'); ?></strong> <?php esc_html_e('offloads your WordPress media library to cloud storage and serves it through a global CDN, freeing up server space and speeding up image delivery. It also includes automatic image optimization to shrink file sizes without a visible quality loss. Images uploaded to forum topics and replies through this plugin are automatically detected and offloaded (and optimized) as well - no extra setup required.', 'image-upload-for-bbpress'); ?></p>
                <div class="berrypress-coupon berrypress-fs-14">
                    <?php esc_html_e('Coupon code:', 'image-upload-for-bbpress'); ?> <strong>BerryPress</strong>
                    <span class="berrypress-text-secondary">(<?php esc_html_e('10% off all payments', 'image-upload-for-bbpress'); ?>)</span>
                </div>
                <p><a href="https://infiniteuploads.com?via=berrypress" target="_blank" rel="noopener sponsored"
                      class="berrypress-btn berrypress-btn-secondary"><?php esc_html_e('Learn more about Infinite Uploads', 'image-upload-for-bbpress'); ?></a></p>
                <p class="berrypress-text-secondary berrypress-fs-13"><?php esc_html_e('This is an affiliate link - we may earn a commission if you sign up, at no extra cost to you.', 'image-upload-for-bbpress'); ?></p>
            </div>
        <?php }
    }

    public static function adminSave()
    {
        return true;
    }

    public static function isAvailable()
    {
        return defined('INFINITE_UPLOADS_VERSION');
    }
	
    public static function init()
    {
		
	}
	
	public static function getUploadDir()
	{
		return wp_upload_dir();
	}
}

add_filter('hm_bbp'.'uip_storage_engines', 'hm_bbpui_register_infinite_uploads_storage');
function hm_bbpui_register_infinite_uploads_storage($engines)
{
    $engines['infinite_uploads'] = array(
        'name' => 'Infinite Uploads',
        'class' => 'HM_BBPUI_InfiniteUploads_Storage'
    );
    return $engines;
}
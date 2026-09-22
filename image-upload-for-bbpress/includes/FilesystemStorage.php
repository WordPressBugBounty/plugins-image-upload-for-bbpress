<?php
defined('ABSPATH') || exit;
	
class HM_BBPUI_Filesystem_Storage
{

    private $postId, $storageDir, $storageUrl;
    public static $id = 'filesystem';

    public function __construct($postId, $create = true)
    {
        $this->postId = $postId;
		
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		
        $this->storageDir = self::getStorageDirParent() . '/' . $postId . '/';
        if (!$wp_filesystem->is_dir($this->storageDir) && (!$create || !wp_mkdir_p($this->storageDir)))
            throw new Exception();
    }

    public static function adminDisplay()
    { ?>

        <div class="berrypress-field berrypress-field-flex berrypress-field-align-center">
            <label for="hm_bbpui_field_storage_dir"><?php esc_html_e('Image Storage Directory:', 'image-upload-for-bbpress'); ?></label>
            <input type="text" name="storage_dir" id="hm_bbpui_field_storage_dir" size="50"
                   value="<?php echo(esc_html(hm_bbpui_get_option('storage_dir'))); ?>"/>
            <button type="button" class="berrypress-doc-note"
                    data-bp-tooltip="<?php esc_attr_e('Enter the path to the filesystem directory where forum images should be stored. {uploads} is replaced with the path to the WordPress uploads directory. Changing this will only affect future uploads.', 'image-upload-for-bbpress'); ?>"
                    data-bp-tooltip-position="top">
                <span class="berrypress-visually-hidden"><?php esc_html_e('Help', 'image-upload-for-bbpress'); ?></span>
                <i class="berrypress-icon-help" aria-hidden="true"></i>
            </button>
        </div>

        <div class="berrypress-field berrypress-field-flex berrypress-field-align-center">
            <label for="hm_bbpui_field_storage_url"><?php esc_html_e('Image Storage URL:', 'image-upload-for-bbpress'); ?></label>
            <input type="text" name="storage_url" id="hm_bbpui_field_storage_url" size="50"
                   value="<?php echo(esc_html(hm_bbpui_get_option('storage_url'))); ?>"
                   placeholder="http://"/>
            <button type="button" class="berrypress-doc-note"
                    data-bp-tooltip="<?php esc_attr_e('Enter the URL to the directory where forum images should be stored. {uploads} is replaced with the URL to the WordPress uploads directory. Changing this will only affect future uploads.', 'image-upload-for-bbpress'); ?>"
                    data-bp-tooltip-position="top">
                <span class="berrypress-visually-hidden"><?php esc_html_e('Help', 'image-upload-for-bbpress'); ?></span>
                <i class="berrypress-icon-help" aria-hidden="true"></i>
            </button>
        </div>

    <?php }

    public static function adminSave()
    {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked before adminSave() is called
        $storageDir = isset($_POST['storage_dir']) ? sanitize_text_field(wp_unslash($_POST['storage_dir'])) : '';
        $storageUrl = isset($_POST['storage_url']) ? sanitize_text_field(wp_unslash($_POST['storage_url'])) : '';
        if (!hm_bbpui_check_storage_dir($storageDir)) {
            return 'The storage directory path you provided is invalid. Please ensure that it exists or can be created by WordPress, and that WordPress can create files in it.';
        }
        hm_bbpui_set_options(array('storage_dir' => $storageDir, 'storage_url' => $storageUrl));
        return true;
		// phpcs:enable WordPress.Security.NonceVerification.Missing
    }
	
    public static function init()
    {
		add_filter(
			'infinite_uploads_sync_exclusions',
			function($excl) {
				$storageDir = hm_bbpui_get_option('storage_dir');
				
				if (strpos($storageDir, '{uploads}/') === 0) {
					$excl[] = substr($storageDir, 9);
				}
				
				return $excl;
			}
		);
	}
	
	public static function getUploadDir()
	{
		if (class_exists('ClikIT\InfiniteUploads\InfiniteUploads')) {
			$iu = ClikIT\InfiniteUploads\InfiniteUploads::get_instance();
			$hadFilter = has_filter( 'upload_dir', [$iu, 'filter_upload_dir'] );
			if ($hadFilter !== false) {
				remove_filter( 'upload_dir', [$iu, 'filter_upload_dir'], $hadFilter );
			}
		}
		$uploadDir = wp_upload_dir();
		if (($hadFilter ?? false) !== false) {
			add_filter( 'upload_dir', [$iu, 'filter_upload_dir'], $hadFilter );
		}
		return $uploadDir;
	}

    public static function getStorageDirParent()
    {
        $storageDir = hm_bbpui_get_option('storage_dir');
        if (strpos($storageDir, '{uploads}') !== false) {
            $uploadDir = static::getUploadDir();
            $storageDir = str_replace('{uploads}', $uploadDir['basedir'], $storageDir);
        }
		
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		
        if (!$wp_filesystem->is_dir($storageDir) && !wp_mkdir_p($storageDir))
            throw new Exception();
        return $storageDir;
    }

    public function put($file)
    {
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		
        $fileName = basename($file);
		
		if (!$wp_filesystem->put_contents(
			$this->storageDir . $fileName,
			$wp_filesystem->get_contents($file)
		)) {
			throw new Exception();
		}
		
		$wp_filesystem->delete($file);

        return $this->getStorageUrl() . $fileName;
    }

    private function getStorageUrl()
    {
        if (!isset($this->storageUrl)) {
            $this->storageUrl = hm_bbpui_get_option('storage_url') . '/' . $this->postId . '/';
			
            if (strpos($this->storageUrl, '{uploads}') !== false) {
                $uploadDir = static::getUploadDir();
                $this->storageUrl = str_replace('{uploads}', $uploadDir['baseurl'], $this->storageUrl);
            }
        }
		
        return $this->storageUrl;
    }

    public function deleteStorage()
    {
		// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        set_time_limit(300);
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
        if (!@$wp_filesystem->rmdir($this->storageDir, true)) {
            throw new Exception();
        }
    }

    public function getImageIds()
    {
		// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        set_time_limit(600);
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
        $ids = array();
        foreach (array_keys($wp_filesystem->dirlist($this->storageDir)) as $dirItem) {
            if ($wp_filesystem->is_file($this->storageDir . $dirItem)) {
                $ids[] = $dirItem;
            }
        }
        return $ids;
    }

    public function cleanImages($dry = false, $imageUrls = null)
    {
		// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        set_time_limit(300);
        $images = hm_bbpui_filter_existing_images($this->postId, $this->getImageIds());
        if ($dry) {
            $storageUrl = $this->getStorageUrl();
            foreach ($images as $i => $image) {
                $images[$i] = $storageUrl . $image;
            }
            return $images;
        }

        $result = true;
        if ($imageUrls !== null) {
            $storageUrl = $this->getStorageUrl();
        }
		
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		
        foreach ($images as $image) {
            if ($imageUrls !== null && !in_array($storageUrl . $image, $imageUrls)) {
                continue;
            }
            $result = @$wp_filesystem->delete($this->storageDir . $image) && $result;
        }
        return $result;
    }

    public static function getStats()
    {
        $storageDir = self::getStorageDirParent();
        $tempDir = hm_bbpui_get_temp_storage_dir();
        $count = 0;
        $size = 0;
		
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		
        foreach (array_keys($wp_filesystem->dirlist($storageDir)) as $dirItem) {
            if ($wp_filesystem->is_dir($storageDir . '/' . $dirItem)
                && self::safeRealpath($storageDir . '/' . $dirItem) != self::safeRealpath($tempDir)) {
                foreach (array_keys($wp_filesystem->dirlist($storageDir . '/' . $dirItem)) as $dirItem2) {
                    if ($wp_filesystem->is_file($storageDir . '/' . $dirItem . '/' . $dirItem2)) {
                        $count += 1;
                        $size += $wp_filesystem->size($storageDir . '/' . $dirItem . '/' . $dirItem2);
                    }
                }
            }
        }

        return array(
            __('Number of Images', 'image-upload-for-bbpress') => $count,
            __('Image Storage Size', 'image-upload-for-bbpress') => round($size / 1048576, 2) . 'MB',
        );
    }

    public static function isAvailable()
    {
        return true;
    }

    public static function getStorageIds()
    {
		// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        set_time_limit(600);
		
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		
        $storageDir = self::getStorageDirParent();
        $ids = array();
        foreach (array_keys($wp_filesystem->dirlist($storageDir)) as $dirItem) {
            if (is_numeric($dirItem) && $wp_filesystem->is_dir($storageDir . '/' . $dirItem)) {
                $ids[] = $dirItem;
            }
        }
		
        return $ids;
    }

    public static function cleanAllImages($dry = false, $imageUrls = null)
    {
        if ($dry) {
            $result = array();
            foreach (self::getStorageIds() as $storageId) {
                $storage = new self($storageId, false);
                foreach ($storage->cleanImages(true) as $image) {
                    $result[$image] = $storageId;
                }
            }
        } else {
			$result = true;
            foreach (self::getStorageIds() as $storageId) {
                $storage = new self($storageId, false);
                $result = $storage->cleanImages(false, $imageUrls) && $result;
            }
        }
        return $result;
    }

    private static function safeRealpath($path)
    {
        $realpath = realpath($path);
        if ($realpath !== false) {
            return $realpath;
        }
		
        $path = str_replace('\\', '/', $path);

        if (!preg_match('#^(?:[A-Za-z]:)?/#', $path)) {
            $path = rtrim(str_replace('\\', '/', getcwd()), '/') . '/' . $path;
        }

        $prefix = '';
        if (preg_match('#^([A-Za-z]:)(/.*)$#', $path, $matches)) {
            $prefix = $matches[1];
            $path = $matches[2];
        }

        $resolvedParts = array();
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($resolvedParts);
                continue;
            }
            $resolvedParts[] = $part;
        }

        return $prefix . '/' . implode('/', $resolvedParts);
    }
}
	
add_filter('hm_bbp'.'uip_storage_engines', 'hm_bbpui_register_filesystem_storage');
function hm_bbpui_register_filesystem_storage($engines)
{
    $engines['filesystem'] = array(
        'name' => 'Filesystem',
        'class' => 'HM_BBPUI_Filesystem_Storage'
    );
    return $engines;
}
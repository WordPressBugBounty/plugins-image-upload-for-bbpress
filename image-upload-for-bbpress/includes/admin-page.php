<?php

use BerryPressBbpImageUpload\Admin\Page as BerryPressPage;

defined( 'ABSPATH' ) || exit;

class HmBbpuiAdminPage extends BerryPressPage {

	protected $reviewUrl = 'https://wordpress.org/support/plugin/image-upload-for-bbpress/reviews/', $supportUrl = 'https://wordpress.org/support/plugin/image-upload-for-bbpress/';
	// Documentation currently points to the Pro docs; set $docsUrl once free-version docs exist.

	function __construct() {
		add_filter( 'berrypress_admin_page_display_sidebar', function() {
			return false;
		} );
	}

	public static function getUrl( array $args = [] ) {
		return add_query_arg( $args, admin_url( 'admin.php?page=hm_bbpui' ) );
	}

	function getNav() {
		return self::getTopNav();
	}

	function getTopNav() {
		return [
			[
				'link'   => self::getUrl(),
				'icon'   => 'berrypress-icon-settings',
				'title'  => __( 'Settings', 'image-upload-for-bbpress' ),
				'active' => true,
			],
		];
	}

	function getLogoUrl() {
		return plugins_url( 'images/image-upload-for-bbpress-2x.png', dirname( __DIR__ ) . '/bbp-image-upload.php' );
	}

	function getProductUrl() {
		return 'https://wordpress.org/plugins/image-upload-for-bbpress/';
	}

	function getHeaderText() {
		return __( 'Image Upload for bbPress', 'image-upload-for-bbpress' );
	}

	function getAboveHeaderHtml() {
		$product_url = 'https://berrypress.com/product/bbpress/image-upload-for-bbpress/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-plugin-upgrade-link';

		$black_friday_notice = BerryPressPage::get_black_friday_notice( 'https://berrypress.com/shop/', 'Image Upload for bbPress' );
		if ( ! empty( $black_friday_notice ) ) {
			return $black_friday_notice;
		}

		return sprintf(
			'<div class="berrypress-top-bar"><h2>%s <a class="berrypress-link" href="%s">%s<i class="berrypress-icon-filled berrypress-icon-keyboard_double_arrow_right"></i></a></h2></div>',
			esc_html__( 'Upgrade to Image Upload for bbPress Pro for multi-image drag-and-drop uploads, S3 storage (with addon), a lightbox, and more.', 'image-upload-for-bbpress' ),
			esc_url( $product_url ),
			esc_html__( 'Upgrade', 'image-upload-for-bbpress' )
		);
	}

	function body() {
		if ( isset( $_POST['cleanup'] ) ) {
			check_admin_referer( 'hm_bbpui_cleanup', 'cleanup' );
			hm_bbpui_cleanup();
			?>
			<div class="berrypress-notice berrypress-notice-success">
				<span class="berrypress-notice-icon"><i class="berrypress-icon-check"></i></span>
				<div><p><?php esc_html_e( 'Unnecessary files have been cleaned up.', 'image-upload-for-bbpress' ); ?></p></div>
			</div>
			<?php
		}

		$storageSaveNotices = array();
		if ( isset( $_POST['hm_bbpui_storage_nonce'] ) ) {
			check_admin_referer( 'hm_bbpui_save_storage', 'hm_bbpui_storage_nonce' );
			hm_bbpui_set_options( $_POST );
			foreach ( hm_bbpui_get_storage_engines() as $engine ) {
				$saveResult = $engine['class']::adminSave();
				if ( $saveResult !== true ) {
					$storageSaveNotices[] = $saveResult;
				}
			}
			?>
			<div class="berrypress-notice berrypress-notice-<?php echo empty( $storageSaveNotices ) ? 'success' : 'error'; ?>">
				<span class="berrypress-notice-icon"><i class="berrypress-icon-<?php echo empty( $storageSaveNotices ) ? 'check' : 'error_outline'; ?>"></i></span>
				<div>
					<?php if ( empty( $storageSaveNotices ) ) : ?>
						<p><?php esc_html_e( 'The settings have been saved.', 'image-upload-for-bbpress' ); ?></p>
					<?php else : foreach ( $storageSaveNotices as $notice ) : ?>
						<p><?php echo esc_html( $notice ); ?></p>
					<?php endforeach; endif; ?>
				</div>
			</div>
			<?php
		}
		?>
		<div class="wrap">
			<div class="hm-bbpui-reports-container">
			<div class="hm-bbpui-col-1">

			<div class="berrypress-card berrypress-mb-4">
				<div class="berrypress-card-header">
					<h2><?php esc_html_e( 'Image Storage', 'image-upload-for-bbpress' ); ?></h2>
				</div>
				<div class="berrypress-card-content">
					<?php
					$engines = hm_bbpui_get_storage_engines();
					$currentStorageClass = hm_bbpui_get_storage_class();
					$hasAzs3 = isset($engines['azs3']);
					// Built-in engine names are registered too early (plugin load, before 'init') to translate at
					// the source without tripping WP's JIT textdomain notice, so translate the known ones here instead;
					// names from third-party engines (e.g. the S3 add-on) pass through as-is, since they aren't ours to translate.
					$engineNames = array(
						'Filesystem'       => __( 'Filesystem', 'image-upload-for-bbpress' ),
						'Infinite Uploads' => __( 'Infinite Uploads', 'image-upload-for-bbpress' ),
					);
					?>
					<form action="" method="post">
						<?php wp_nonce_field('hm_bbpui_save_storage', 'hm_bbpui_storage_nonce'); ?>
						<div class="berrypress-field berrypress-field-flex berrypress-field-align-center">
							<label for="hm_bbpui_field_storage_engine"><?php esc_html_e( 'Storage Engine:', 'image-upload-for-bbpress' ); ?></label>
							<select name="storage_engine" id="hm_bbpui_field_storage_engine">
								<?php foreach ( $engines as $engineId => $engine ) : ?>
									<option value="<?php echo esc_attr( $engineId ); ?>"<?php selected( $engine['class'] === $currentStorageClass ); ?><?php disabled( !$engine['class']::isAvailable() ); ?>><?php echo esc_html( $engineNames[ $engine['name'] ] ?? $engine['name'] ); ?></option>
								<?php endforeach; ?>
								<?php
									if (!$hasAzs3) {
										echo('<option value="azs3" disabled="disabled">' . esc_html__('Amazon S3™', 'image-upload-for-bbpress') . '</option>');
									}
								?>
							</select>
						</div>
						<div class="berrypress-settings-subnav">
							<ul class="berrypress-settings-subnav-list" id="bbpui-storage-engines-tabs">
								<?php foreach ($engines as $engineId => $engine) {
									echo('<li><a href="#' . urlencode($engineId) . '">' . esc_html($engineNames[$engine['name']] ?? $engine['name']) . '</a></li>');
								}
								if (!$hasAzs3) { ?>
								<li><a href="#azs3"><?php esc_html_e('Amazon S3™', 'image-upload-for-bbpress'); ?></a></li>
								<?php } ?>
							</ul>
						</div>
						<div id="bbpui-storage-engines-admin">
							<?php foreach ($engines as $engineId => $engine) {
								echo('<div id="bbpui-storage-engines-admin-' . esc_attr($engineId) . '">');
								$engine['class']::adminDisplay();
								echo('</div>');
							}
							if (!$hasAzs3) { ?>
								<div id="bbpui-storage-engines-admin-azs3">
									<div class="bbpuip-partner-note">
										<p><strong><?php esc_html_e('S3 Image Storage for bbPress', 'image-upload-for-bbpress'); ?></strong> <?php esc_html_e('is a companion add-on from BerryPress that stores forum images on Amazon S3 instead of your server, reducing disk usage and bandwidth costs.', 'image-upload-for-bbpress'); ?></p>
										<div class="berrypress-coupon berrypress-fs-14">
											<?php esc_html_e('Coupon code:', 'image-upload-for-bbpress'); ?> <strong>S3Images10</strong>
											<span class="berrypress-text-secondary">(<?php esc_html_e('10% off', 'image-upload-for-bbpress'); ?>)</span>
										</div>
										<p><a href="https://berrypress.com/product/bbpress/s3-image-storage-for-bbpress/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-storage-engine-tab"
											  target="_blank" class="berrypress-btn berrypress-btn-secondary"><?php esc_html_e('Learn more about S3 Image Storage', 'image-upload-for-bbpress'); ?></a></p>
									</div>
								</div>
							<?php } ?>
						</div>
						<script type="text/javascript">
							jQuery(document).ready(function ($) {
								$(window).on('hashchange', function () {
									var panel = $('#bbpui-storage-engines-admin-' + location.hash.substr(1));
									if (panel.length) {
										$('#bbpui-storage-engines-admin .panel-active').removeClass('panel-active');
										$('#bbpui-storage-engines-tabs .active').removeClass('active');
										panel.addClass('panel-active');
										$('#bbpui-storage-engines-tabs').find('a[href="' + location.hash + '"]').addClass('active');
									}
									if (!$('#bbpui-storage-engines-admin .panel-active').length) {
										location.hash = $('#hm_bbpui_field_storage_engine').val();
									}
								});
								$(window).trigger('hashchange');
							});
						</script>
						<button type="submit" class="berrypress-btn berrypress-btn-primary"><?php esc_html_e('Save Settings', 'image-upload-for-bbpress'); ?></button>
					</form>
				</div>
			</div>

			<div class="berrypress-card berrypress-mb-4">
				<div class="berrypress-card-header">
					<h2><?php esc_html_e( 'Usage Instructions', 'image-upload-for-bbpress' ); ?></h2>
				</div>
				<div class="berrypress-card-content">
					<p>1. <?php esc_html_e( 'To upload an image to a forum topic or reply, click the Insert/edit image button in the editor toolbar:', 'image-upload-for-bbpress' ); ?></p>
					<img class="hm-bbpui-screenshot berrypress-mb-3" src="<?php echo esc_url( plugins_url( 'images/bbpui-screenshot-toolbar.png', dirname( __DIR__ ) . '/bbp-image-upload.php' ) ); ?>" alt="<?php esc_attr_e( 'Toolbar screenshot', 'image-upload-for-bbpress' ); ?>" />
					<p>2. <?php esc_html_e( 'Then click the Browse button in the image dialog to select and upload an image:', 'image-upload-for-bbpress' ); ?></p>
					<img class="hm-bbpui-screenshot berrypress-mb-4" src="<?php echo esc_url( plugins_url( 'images/bbpui-screenshot-dialog.png', dirname( __DIR__ ) . '/bbp-image-upload.php' ) ); ?>" alt="<?php esc_attr_e( 'Dialog screenshot', 'image-upload-for-bbpress' ); ?>" />

                    <h3><?php esc_html_e( 'Supported File Formats and File Size', 'image-upload-for-bbpress' ); ?></h3>

                    <p><?php esc_html_e( 'This plugin supports images in JPEG, PNG, and GIF format. The maximum file size is determined by the following settings in your server\'s PHP configuration:', 'image-upload-for-bbpress' ); ?></p>
                    <div class="berrypress-table-wrapper hm_bbpui-table-wrapper-support">
                        <table class="berrypress-table">
                            <tbody>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'upload_max_filesize', 'image-upload-for-bbpress' ); ?></th>
                                <td><?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'post_max_size', 'image-upload-for-bbpress' ); ?></th>
                                <td><?php echo esc_html( ini_get( 'post_max_size' ) ); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>

			<div class="berrypress-card berrypress-mb-4">
				<div class="berrypress-card-header">
					<h2><?php esc_html_e( 'Maintenance', 'image-upload-for-bbpress' ); ?></h2>
				</div>
				<div class="berrypress-card-content">
					<p><?php esc_html_e( 'Remove temporary image files that were uploaded but never used in a forum post.', 'image-upload-for-bbpress' ); ?></p>
					<form action="" method="post">
						<button type="submit" name="cleanup" value="<?php echo esc_attr( wp_create_nonce( 'hm_bbpui_cleanup' ) ); ?>" class="berrypress-btn berrypress-btn-primary">
							<?php esc_html_e( 'Cleanup Unnecessary Files', 'image-upload-for-bbpress' ); ?>
						</button>
					</form>
				</div>
			</div>

			</div><!-- .hm-bbpui-col-1 -->

			<div class="berrypress-upgrade-box hm-bbpui-col-2">
				<div>
					<div>
						<i class="berrypress-upgrade-box-icon berrypress-icon-filled berrypress-icon-lock"></i>
						<h4>
							<?php
							printf(
								/* translators: %s: pro product name */
								esc_html__( 'Upgrade to %s', 'image-upload-for-bbpress' ),
								'<span class="brand">' . esc_html__( 'Image Upload for bbPress Pro', 'image-upload-for-bbpress' ) . '</span>'
							);
							?>
						</h4>
						<p><?php esc_html_e( 'Get more features and options for uploading images to your forum.', 'image-upload-for-bbpress' ); ?></p>
					</div>

					<div class="berrypress-upgrade-box-content berrypress-mb-3">
						<h5 class="berrypress-fw-bold berrypress-fs-14"><?php esc_html_e( "What's inside Pro:", 'image-upload-for-bbpress' ); ?></h5>
						<ul class="berrypress-upgrade-box-list">
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Upload multiple images at once with the responsive drag-and-drop uploader', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Limit which user roles are permitted to upload images', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Limit the number of uploaded images allowed per post', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Automatically downsize images to fit specified maximum dimensions', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Convert all uploaded images to the same image format, if desired', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Set PNG and JPEG compression levels so images take up less disk space', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Allow users to view enlarged images in a lightbox by clicking on them within the post', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'View total image count and file size statistics', 'image-upload-for-bbpress' ); ?></li>
							<li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Allow users to upload animated GIFs while preserving animation', 'image-upload-for-bbpress' ); ?></li>
						</ul>
						<p><em><?php esc_html_e( 'Plus new features added regularly!', 'image-upload-for-bbpress' ); ?></em></p>
					</div>

					<div class="berrypress-upgrade-box-footer">
						<div class="berrypress-coupon berrypress-fs-14 berrypress-mb-2">
							<?php esc_html_e( 'Coupon code 10% OFF:', 'image-upload-for-bbpress' ); ?>
							<strong>BBPIMAGES10</strong>
						</div>
						<a href="https://berrypress.com/product/bbpress/image-upload-for-bbpress/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank" class="berrypress-btn berrypress-btn-primary">
							<?php esc_html_e( 'View Plans', 'image-upload-for-bbpress' ); ?>
						</a>
					</div>
				</div>
			</div><!-- .hm-bbpui-col-2 -->

			</div><!-- .hm-bbpui-reports-container -->

			<div class="berrypress-mt-4 berrypress-text-center">
				<p class="berrypress-text-secondary berrypress-fs-13">
					<a class="berrypress-link" href="https://berrypress.com/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-footer-link" target="_blank"><?php esc_html_e( 'BerryPress', 'image-upload-for-bbpress' ); ?></a>
					&nbsp;&middot;&nbsp;
					<a class="berrypress-link" href="https://berrypress.com/product/bbpress/image-upload-for-bbpress/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-footer-link" target="_blank"><?php esc_html_e( 'About the Pro version', 'image-upload-for-bbpress' ); ?></a>
					&nbsp;&middot;&nbsp;
					<a class="berrypress-link" href="https://berrypress.com/#subscribe" target="_blank"><?php esc_html_e( 'Sign up to BerryPress Newsletter', 'image-upload-for-bbpress' ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}
}

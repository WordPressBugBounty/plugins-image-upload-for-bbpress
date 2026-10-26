<?php
namespace BerryPressBbpImageUpload\Admin;

defined( 'ABSPATH' ) || exit;

abstract class Page {

	protected $showExternalLinks = true;
	protected $reviewUrl, $docsUrl, $supportUrl;

	public static function maybeRegisterBerryPressAdminMenu() {
		add_action('admin_menu', function() {
			if (empty($GLOBALS['_registered_pages']['toplevel_page_berrypress'])) {
				$bpLabel = __('BerryPress', 'image-upload-for-bbpress');

				add_menu_page(
				   $bpLabel,
					__('BerryPress', 'image-upload-for-bbpress'),
					'read',
					'berrypress',
					'',
					'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iV2Fyc3R3YV8xIiBkYXRhLW5hbWU9IldhcnN0d2EgMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgNTguNjkgNTkuOTYiPgogIDxwYXRoIGQ9Ik0xOC43Myw1Mi40NnYtNy4zN2MwLTIuMzgtLjY3LTQuNzEtMS44NC02LjM3bC0yLjg2LTQuMDktMy4yNS00LjY0LDMuMjUtNC42NCwyLjg2LTQuMDljMS4xNy0xLjY3LDEuODQtMy45OSwxLjg0LTYuMzd2LTcuMzdjMC0yLjcyLjYzLTUuMjcsMS43Mi03LjVoLTUuMzljLTIuOSwwLTUuMjQsMi4zNS01LjI0LDUuMjR2Ny43NGMwLDEuOTQtLjYsMy44NC0xLjcxLDUuNDNMMCwyOS45OGw4LjEsMTEuNTZjMS4xMiwxLjU5LDEuNzEsMy40OSwxLjcxLDUuNDN2Ny43NGMwLDIuOSwyLjM1LDUuMjQsNS4yNCw1LjI0aDUuMzljLTEuMS0yLjIzLTEuNzItNC43OC0xLjcyLTcuNVoiIHN0eWxlPSJmaWxsOiAjRkZGRkZGOyIvPgogIDxwYXRoIGQ9Ik01MS4wOSwyOWMzLjE5LTMuMDgsNS4xNy03LjMyLDUuMTctMTEuOTlDNTYuMjUsNy42Myw0OC4zMSwwLDM4LjU0LDBoLTUuODVjLTMuMjQsMC01Ljg2LDMuMzYtNS44Niw3LjV2Ny4zN2MwLDQuMDYtMS4xNyw3Ljk4LTMuMywxMS4wMmwtMi44Niw0LjA5LDIuODYsNC4wOWMyLjEzLDMuMDQsMy4zLDYuOTYsMy4zLDExLjAydjcuMzdjMCw0LjE0LDIuNjIsNy41LDUuODYsNy41aDguMjhjOS43NywwLDE3LjcxLTcuNjMsMTcuNzEtMTcuMDEsMC01Ljc3LTMuMDEtMTAuODgtNy42LTEzLjk1WiIgc3R5bGU9ImZpbGw6ICNGRkZGRkY7Ii8+Cjwvc3ZnPg==',
					59
				);


				add_action('admin_menu', function() {
					remove_submenu_page('berrypress', 'berrypress');
					if ( apply_filters( 'berrypress_admin_menu_external_links', true ) ) {
						$docsLabel = __('Documentation', 'image-upload-for-bbpress');
						add_submenu_page(
							'berrypress',
							$docsLabel,
							$docsLabel,
							'read',
							'https://berrypress.com/docs/',
							''
						);
					}
				}, 999);

				add_action('admin_footer', function() {
					echo('<script>(function(l) {l && (l.target=\'_blank\');})(document.querySelector(\'#toplevel_page_berrypress > ul a[href^="https://berrypress.com/"]\'));</script>');
				});
			}
		}, 1);
	}

	public static function should_show_friday_notice() {
		$current_date = current_time('Y-m-d');
		$black_friday_start = '2026-11-19'; // Black Friday start date
		$black_friday_end = '2026-11-30';   // End date
		return $current_date >= $black_friday_start && $current_date <= $black_friday_end;
	}

	/**
	 * Check if we're in Black Friday promotional period and return appropriate notice
	 *
	 * @param string $product_url URL to the product page
	 * @param string $product_name Name of the product (optional)
	 * @return string HTML for the promotional notice or empty string
	 */
	public static function get_black_friday_notice( $product_url, $product_name = 'Pro' ) {
		if ( self::should_show_friday_notice() ) {
			return sprintf(
				'<div class="berrypress-top-bar berrypress-top-bar-promo"><h2><span class="berrypress-fw-bold">%s</span> %s <a target="_blank" class="berrypress-btn berrypress-btn-secondary" href="%s">%s<i class="berrypress-icon-filled berrypress-icon-keyboard_double_arrow_right"></i></a></h2></div>',
				esc_html__( 'Our Black Friday sale is live!', 'image-upload-for-bbpress' ),
				sprintf(
				/* translators: %s is the product name */
					esc_html__( 'Get %s 25%% OFF — plus discounts on all BerryPress products! ', 'image-upload-for-bbpress' ),
					esc_html( $product_name )
				),
				esc_html( $product_url ),
				esc_html__( 'Get 25% OFF Now', 'image-upload-for-bbpress' )
			);
		}


		return '';
	}

	function render() {
		$this->header();
		if (!apply_filters('berrypress_admin_page_replace_body', false, $this)) {
			$this->body();
		}
		$this->footer();
	}

	abstract function getNav();

	abstract function getTopNav();

	abstract function getLogoUrl();

	abstract function getProductUrl();

	abstract function getHeaderText();

	abstract function getAboveHeaderHtml();

function header() {

	$top_nav              = $this->getTopNav();
	$nav                  = $this->getNav();
	$logo                 = $this->getLogoUrl();
	$product_url          = $this->getProductUrl();
	$header_text          = $this->getHeaderText();
	$display_above_header = $this->getAboveHeaderHtml();
	// Separate dismissal cookies for the Black Friday bar and the default upgrade bar.
	$top_bar_cookie       = 'image_upload_for_bbpress_top_bar_dismissed' . ( self::should_show_friday_notice() ? '_bf' : '' );
	$top_bar_dismissed    = !empty( $_COOKIE[ $top_bar_cookie ] );

	$display_sidebar         = apply_filters( 'berrypress_admin_page_display_sidebar', true, '' );
	$display_top_nav         = apply_filters( 'berrypress_admin_page_display_top_nav', true, '' );
	$display_top_right_nav   = apply_filters( 'berrypress_admin_page_display_top_right_nav', true, '' );

	?>
    <div class="berrypress-settings-container<?php echo $top_bar_dismissed ? ' berrypress-top-bar-dismissed' : ''; ?>">
		<?php echo(wp_kses_post($display_above_header)); ?>

        <header class="berrypress-header">
            <button id="berrypress-toggle-menu-mobile" class="berrypress-btn berrypress-btn-icon" aria-label="Toggle Sidebar"><i class="berrypress-icon-menu" aria-hidden="true"></i></button>
            <a href="<?php echo esc_url( $product_url ); ?>" target="_blank" class="berrypress-header-left">
                <img src="<?php
                // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                echo esc_url($logo);
                ?>" alt="<?php esc_attr_e("Logo", "image-upload-for-bbpress"); ?>">
                <span class="berrypress-logo"><?php echo esc_html( $header_text ); ?></span>
            </a>

			<?php if ($display_top_nav) { ?>
                <nav class="berrypress-nav">
					<?php foreach ($top_nav as $navItem) { ?>
                        <a href="<?php echo(esc_url($navItem['link'])); ?>" <?php if ($navItem['active'] ?? false) { ?> class="active" <?php } ?>
						   <?php if (!empty($navItem['link_id'])) : ?>  id="<?php echo esc_attr($navItem['link_id']); ?>" <?php endif; ?>>
							<?php if (!empty($navItem['icon'])) : ?> <i class="<?php echo(esc_attr($navItem['icon'])); ?>"></i> <?php endif; ?>
							<?php echo(esc_html($navItem['title'])); ?>
                        </a>
					<?php } ?>
                </nav>
			<?php } ?>

			<?php if ($display_top_right_nav && ($this->reviewUrl || $this->docsUrl || $this->supportUrl)) { ?>
                <div class="berrypress-header-right">
					<?php if ($this->reviewUrl) { ?>
                        <a href="<?php echo(esc_url($this->reviewUrl)); ?>" target="_blank"><i class="berrypress-icon-star"></i><?php esc_html_e( 'Leave a Review', 'image-upload-for-bbpress' ); ?></a>
					<?php } ?>
					<?php if ($this->docsUrl) { ?>
                        <a href="<?php echo(esc_url($this->docsUrl)); ?>" target="_blank"><i class="berrypress-icon-library_books"></i><?php esc_html_e( 'Documentation', 'image-upload-for-bbpress' ); ?></a>
					<?php } ?>
					<?php if ($this->supportUrl) { ?>
                        <a href="<?php echo(esc_url($this->supportUrl)); ?>" target="_blank"><i class="berrypress-icon-help"></i><?php esc_html_e( 'Support', 'image-upload-for-bbpress' ); ?></a>
					<?php } ?>
                </div>
			<?php } ?>
        </header>

        <!--  Sidebar + Content -->
        <div class="berrypress-main">
            <!-- Sidebar -->
            <?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- non-persistent use of page slug for cookie
            $page_slug = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
            $cookie_name = $page_slug ? 'berrypress_sidebar_collapsed_' . $page_slug : '';
            $sidebar_collapsed = !$display_sidebar || ( $cookie_name && isset( $_COOKIE[ $cookie_name ] ) && $_COOKIE[ $cookie_name ] === '1' );
            ?>
            <aside id="berrypress-sidebar" class="berrypress-sidebar<?php echo $sidebar_collapsed ? ' collapsed' : ''; ?>">
                <div class="berrypress-sidebar-header">
                    <h2><?php esc_html_e( 'Menu', 'image-upload-for-bbpress' ); ?></h2>
                    <button class="berrypress-toggle-sidebar berrypress-btn berrypress-btn-icon" aria-label="Toggle Sidebar"><i class="berrypress-icon-first_page" aria-hidden="true"></i></button>
                </div>

                <ul class="berrypress-menu">
                    <?php foreach ($nav as $navItem) { ?>
                        <li <?php if ($navItem['active'] ?? false) { ?> class="active"<?php } ?>>
                            <a href="<?php echo(esc_url($navItem['link'])); ?>"
	                            <?php if (!empty($navItem['link_id'])) : ?>  id="<?php echo esc_attr($navItem['link_id']); ?>" <?php endif; ?>>
                                <i class="<?php echo(esc_attr($navItem['icon'])); ?>"></i>
					            <?php echo(esc_html($navItem['title'])); ?>
                            </a></li>
		            <?php } ?>
                </ul>

                <?php if ($this->showExternalLinks) { ?>
				<ul class="berrypress-menu">
                    <li><h3><?php esc_html_e( 'External Links', 'image-upload-for-bbpress' ); ?></h3></li>

                    <li><a href="https://berrypress.com/" target="_blank"><i class="berrypress-icon-home"></i><?php esc_html_e( 'BerryPress Website', 'image-upload-for-bbpress' ); ?></a></li>
                    <li><a href="https://wordpress.org/support/plugin/image-upload-for-bbpress/reviews/" target="_blank"><i class="berrypress-icon-star"></i><?php esc_html_e( 'Leave a Review', 'image-upload-for-bbpress' ); ?></a></li>
                    <?php /* Documentation currently points to the Pro docs; re-enable once free-version docs exist.
                    <li><a href="https://berrypress.com/docs/image-upload-for-bbpress/" target="_blank"><i class="berrypress-icon-library_books"></i><?php esc_html_e( 'Documentation', 'image-upload-for-bbpress' ); ?></a></li>
                    */ ?>
                    <li><a href="https://wordpress.org/support/plugin/image-upload-for-bbpress/" target="_blank"><i class="berrypress-icon-help"></i><?php esc_html_e( 'Support', 'image-upload-for-bbpress' ); ?></a></li>
                </ul>
				<?php } ?>

                <div class="berrypress-upgrade-box">
                    <div>
                        <div>
                            <i class="berrypress-icon-filled berrypress-icon-lock"></i>
                            <h4><?php
                            /* translators: %s is the name of the Pro version (e.g., 'Pro') */
                            echo wp_kses_post( sprintf( __( 'Upgrade to <strong>%s</strong>', 'image-upload-for-bbpress' ), 'Pro' ) ); ?></h4>
                        </div>
                        <div class="berrypress-upgrade-box-content berrypress-mb-3">
                            <ul class="berrypress-upgrade-box-list">
                                <li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Multi-image drag-and-drop uploads', 'image-upload-for-bbpress' ); ?></li>
                                <li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Lightbox for enlarged images', 'image-upload-for-bbpress' ); ?></li>
                                <li><i class="berrypress-icon-filled berrypress-icon-check"></i><?php esc_html_e( 'Role and upload limits', 'image-upload-for-bbpress' ); ?></li>
                            </ul>
                            <p><em><?php esc_html_e( '...plus new features added regularly!', 'image-upload-for-bbpress' ); ?></em></p>
                        </div>
                        <div class="berrypress-upgrade-box-footer">
                            <a href="https://berrypress.com/product/bbpress/image-upload-for-bbpress-pro/?utm_source=image-upload-for-bbpress&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank" class="berrypress-btn berrypress-btn-primary">
					            <?php esc_html_e( 'View Plans', 'image-upload-for-bbpress' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main -->
            <main id="berrypress-content" class="berrypress-content">
<?php
	}

	function footer() {
?>
            </main>

     </div>
</div>
<?php
	}

	abstract function body();

}

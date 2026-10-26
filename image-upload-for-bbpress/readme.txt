=== Inline Image Upload for BBPress ===
Contributors: berrypress
Tags: bbpress, image, images, inline, media
Requires at least: 6.5.0
Tested up to: 7.1
Requires PHP: 7.0
Stable tag: 1.2.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Upload inline images to BBPress forum topics and replies.

== Description ==

This plugin enables the TinyMCE WYSIWYG editor for BBPress forum topics and replies and adds a button to the editor's "Insert/edit image" dialog that allows forum users to upload images from their computer and insert them inline into their posts.

= Image storage and compatibility =

Uploaded images can be stored using a pluggable storage engine, selectable under Forum Images in the BerryPress menu:

* **Filesystem** (default) - images are stored in your WordPress uploads directory. You can change the storage directory path and URL in the settings (use `{uploads}` as a placeholder for the uploads directory). Changing it only affects future uploads; existing images stay where they are.
* **[Infinite Uploads](https://infiniteuploads.com?via=berrypress)** - when the Infinite Uploads plugin is active, forum images are automatically offloaded to cloud storage, served through its CDN and optimized. No extra setup is required, and the "Cleanup Unnecessary Files" tool also removes unused images from the cloud.
* **Amazon S3™** - available with the optional [S3 Image Storage for bbPress](https://berrypress.com/product/bbpress/s3-image-storage-for-bbpress/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-repo-upgrade-link) add-on (paid).

The plugin also works with BuddyPress.

= Maintenance =

The "Cleanup Unnecessary Files" button removes temporary uploads older than 24 hours, images that were removed from existing topics and replies, and image folders of deleted posts, on whichever storage engine is active.

A [pro version](https://berrypress.com/product/bbpress/image-upload-for-bbpress-pro/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-repo-upgrade-link) with the following additional features is also available:

* Limit which user roles are permitted to upload images.
* Limit the number of uploaded images allowed per post.
* Automatically downsize images to fit specified maximum dimensions.
* Convert all uploaded images to the same image format, if desired.
* Set PNG and JPEG compression levels so images take up less disk space.
* Allow users to view enlarged images in a lightbox by clicking on them within the post.
* View total image count and file size statistics.
* Use [Amazon S3™](https://aws.amazon.com/s3/) to store and serve uploaded images in submitted forum posts (optional; requires [add-on plugin](https://berrypress.com/product/bbpress/image-upload-for-bbpress-pro/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-repo-upgrade-link) purchase).

Amazon Web Services, the "Powered by Amazon Web Services" logo, AWS, Amazon Simple Storage Service, and Amazon S3 are trademarks of Amazon.com, Inc. or its affiliates in the United States and/or other countries. Potent Plugins is not affiliated with Amazon.com, Inc. or Amazon Web Services.

If you like this plugin, please consider leaving a comment or review.

## You may also like these plugins
[BerryPress](https://berrypress.com/) has built a range of powerful plugins for WooCommerce and WordPress. Check out some of our favorites below - and don’t forget to leave a ⭐⭐⭐⭐⭐ review to help others in the community discover them!

* [Ninjalytics](https://berrypress.com/product/woocommerce/ninjalytics/) - Report, analyze, and visualize your store's data to gain actionable insights for growth, support operations, and satisfy customers!
* [Live Carts for WooCommerce](https://wordpress.org/plugins/live-carts-for-woocommerce/) - Free plugin to view and analyze live shopping carts in real time to understand user behavior and optimize conversions ([Pro version](https://berrypress.com/product/woocommerce/live-carts/) also available). **Integrates with Ninjalytics** to add 4 specialized cart reporting templates.
* [Export Order Items for WooCommerce](https://wordpress.org/plugins/export-order-items-for-woocommerce/) - Export detailed order data for every sale in your WooCommerce store. Simplify fulfillment, create accounting reports in just a few clicks, and download CSV files for easy sharing and compatibility.
* [PhotoBerry Studio](https://wordpress.org/plugins/photoberry-studio/) - A complete solution to enhance and streamline your photography workflow, with tools for image organization, gallery management, and client proofing.
* [Automatic Product Categories](https://berrypress.com/product/woocommerce/automatic-product-categories/) - Available as a free version and Pro. Automatically assign products to categories and tags, based on custom rules, saving you time and keeping your store organized.
* [Advanced Order Editor for WooCommerce](https://wordpress.org/plugins/advanced-order-editor-for-woocommerce/) - Add editable Paid date, Completed date and Payment method fields in the WooCommerce order admin screen.
* [LoginBerry](https://wordpress.org/plugins/loginberry/) - Bundles account verification, two-factor authentication (2FA), passwordless login, and login logs. Each feature can be enabled or disabled independently.
* [Customer Address Change Notification for WooCommerce](https://wordpress.org/plugins/customer-address-change-notification-for-woocommerce/) - Get instant notifications when customers update their shipping or billing address, helping you stay on top of changes and prevent delivery issues.

To view BerryPress's premium WordPress plugins and themes, visit our [WordPress products catalog page](https://berrypress.com/shop/).

== Installation ==

1. Click "Plugins" > "Add New" in the WordPress admin menu.
2. Search for "Image Upload for BBPress".
3. Click "Install Now".
4. Click "Activate Plugin".

Alternatively, you can manually upload the plugin to your wp-content/plugins directory.

== Frequently Asked Questions ==

== Screenshots ==

1. The Image toolbar icon in the TinyMCE editor for forum topics and replies.
2. The Browse button in the Image dialog, which allows the user to select and upload an image from their computer for inline insertion into their forum topic or reply.

== Changelog ==

= 1.2.2 =
- Admin styling fixes

= 1.2.1 =
- Fix: Cleanup Unnecessary Files could use the wrong storage engine, and now works correctly with Infinite Uploads
- Readme: added storage engine and Infinite Uploads compatibility information

= 1.2.0 =
- Added a pluggable image storage engine, matching the Pro version's architecture
- Added native Infinite Uploads support as a selectable storage engine, with its own settings panel
- Cleanup now also removes images that were deleted from existing posts, not just orphaned post directories
- Plugin redesign

= 1.1.26 =
- Redesigned the settings page with the BerryPress admin framework
- Added a Compatible Plugins section

= 1.1.25 =
- Fix: broken logo path

= 1.1.24 =
- Improvement: Better caching support

= 1.1.23 =
- Fix duplicate image button, e.g. in BuddyPress

= 1.1.22 =
* Fix potential fatal error

= 1.1.21 =
* Change plugin author to BerryPress, update links.

= 1.1.20 =
* Plugin check fixes

= 1.1.19 =
* Minor security improvement

= 1.1.18 =
* Updated links, author, changed branding
* Updated tested up to
* Removed donation links
* Updated banner and icon

= 1.1.17 =
* Fix: incompatibility with jQuery 3

= 1.1.13 =
* Fix: forum reply editor crashes after clicking the Reply link on a forum topic or reply in recent version(s) of bbPress
* Change license to GPLv3+

= 1.1.12 =
* Fixed duplicate BuddyPress activity entries

= 1.1.11 =
* Fixed unnecessary creation of revision when saving forum post

= 1.1.10 =
* Improved compatibility with other plugins and themes that activate the visual editor in bbPress

= 1.1.7 =
* Added image button to full TinyMCE editor
* Fixed tabbing issue in image dialog

= 1.1.1 =
* Fixed problem with reply threading in IE

= 1.1.0 =
* Added support for rotations based on EXIF orientation data in JPEG images
* Added cleanup feature to remove unnecessary files
* Added plain text conversion when pasting formatted text into the WYSIWYG editor
* Added attempt to increase PHP memory limit before image processing

= 1.0.8 =
* Fixed bug affecting multi-domain sites

= 1.0.7 =
* Fixed bug with non-root-URL WordPress installations

= 1.0.5 =
* Hide image caption field

= 1.0.4 =
* Fixed bug with uploads by anonymous users

= 1.0 =
* Initial release

== Upgrade Notice ==

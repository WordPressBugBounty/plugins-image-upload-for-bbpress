=== Inline Image Upload for BBPress ===
Contributors: berrypress
Tags: bbpress, image, images, inline, media
Requires at least: 3.5
Tested up to: 7.0
Stable tag: 1.1.25
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Upload inline images to BBPress forum topics and replies.

== Description ==

This plugin enables the TinyMCE WYSIWYG editor for BBPress forum topics and replies and adds a button to the editor's "Insert/edit image" dialog that allows forum users to upload images from their computer and insert them inline into their posts.

A [pro version](https://berrypress.com/product/bbpress/image-upload-for-bbpress-pro/?utm_source=image-upload-for-bbpress&utm_medium=link&utm_campaign=wp-repo-upgrade-link) with the following additional features is also available:

* Change the directory where uploaded images are stored.
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
[BerryPress](https://berrypress.com/) has built a bunch of plugins. Check out other favorites here on the repository and don’t forget to leave a 5-star review to help others in the community decide.

* [Product Sales Report for WooCommerce](https://wordpress.org/plugins/product-sales-report-for-woocommerce/) - setup a custom sales report for the products in your WooCommerce store with toggle sorting options. Including or excluding items based on date range, sale status, product category and id, define display order, choose what fields to include, and generate your report with a click.
* [Export Order Items for WooCommerce](https://wordpress.org/plugins/export-order-items-for-woocommerce/) - export the order details for each sale in your WooCommerce store. Simplify order fulfillment, generate accounting reports in a few clicks, and download into CSV format for readability and universal compatibility with Export Order Items.

To view BerryPress's premium WordPress plugins, visit our [WordPress products catalog page](https://berrypress.com/shop/).

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

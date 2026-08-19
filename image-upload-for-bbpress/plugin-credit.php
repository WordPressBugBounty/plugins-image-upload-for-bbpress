<?php
/**
 * Author: BerryPress
 * License: GNU General Public License version 3 or later
 */
 ?>
<div style="background-color: #F4F7FC; border: 1px solid #0078FF; border-radius: 30px; max-width: 500px; text-align: center; padding: 20px; color: #000;">
    <a href="https://berrypress.com/?utm_source=plugin-<?php echo(esc_attr($potent_slug)); ?>&amp;utm_medium=plugin-footer&amp;utm_campaign=wp-plugin-credit-link" target="_blank">
        <img src="<?php echo(esc_url(plugins_url('/assets/img/berrypress_logo.svg', __FILE__))); ?>" alt="BerryPress" style="max-width: 220px; margin-bottom: 20px;" />
    </a>
    <div style="margin-bottom: 20px; font-size: 1.2em;">
        <strong>If you like our free plugin, please:</strong>
    </div>
    <div style="margin-bottom: 10px;">
        <a href="https://wordpress.org/support/view/plugin-reviews/<?php echo(esc_attr($potent_slug)); ?>" target="_blank" class="button-primary">Write a Review</a>
    </div>
    <div style="margin-bottom: 10px;">
        <a href="https://www.facebook.com/berrypresscom" target="_blank" class="button-primary">Like us on Facebook</a>
        <a href="https://www.youtube.com/@berrypresscom" target="_blank" class="button-primary">Subscribe our YouTube Channel</a>
    </div>
</div>
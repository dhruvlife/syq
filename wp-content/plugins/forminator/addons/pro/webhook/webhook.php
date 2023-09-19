<?php

/**
 * Addon Name: Webhook
 * Version: 1.0
 * Plugin URI:  https://wpmudev.com/
 * Description: Integrate Forminator Custom Forms with Webhook to execute various action you like
 * Author: WPMU DEV
 * Author URI: http://wpmudev.com
 */
define( 'FORMINATOR_ADDON_WEBHOOK_VERSION', '1.0' );

function forminator_addon_webhook_url() {
	return trailingslashit( forminator_plugin_url() . 'addons/pro/webhook' );
}

function forminator_addon_webhook_assets_url() {
	return trailingslashit( forminator_addon_webhook_url() . 'assets' );
}

function forminator_addon_webhook_dir() {
	return trailingslashit( dirname( __FILE__ ) );
}

require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook.php';

require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-form-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-form-hooks.php';

require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-poll-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-poll-hooks.php';

require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-quiz-settings.php';
require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-quiz-hooks.php';

// Direct Load.
Forminator_Addon_Loader::get_instance()->register( 'Forminator_Addon_Webhook' );

<?php

namespace PrestoPlayer\Integrations\Divi;

use PrestoPlayer\Plugin;
use PrestoPlayer\WPackio\Enqueue;
use PrestoPlayer\Models\ReusableVideo;

/**
 * Handles divi-related functionality
 */
class Divi
{
  public function register()
  {
    add_action('divi_extensions_init', [$this, 'init']);
    add_action('wp_ajax_presto_get_media_attributes', [$this, 'getMediaItemAttributes']);
  }

  public function init()
  {
    // require our module
    require_once 'includes/PrestoDiviExtension.php';

    // enqueue the scripts
    add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);

    // fix rankmath conflict.
    add_filter('script_loader_tag', [$this, 'rankMathFix'], 11, 3);
  }

  /**
   * Fixes rankmath excluding wp-i18n script from iframe.
   *
   * @param string $tag The <script> tag for the enqueued script.
   * @param string $handle The script's registered handle.
   * @param string $src The script's source URL.
   *
   * @return string
   */
  public function rankMathFix($tag, $handle, $src)
  {
    if ('wp-i18n' === $handle) {
      return '<script type="text/javascript" src="' . $src . '"></script>' . "\n"; // phpcs:ignore
    }
    return $tag;
  }

  /**
   * Get attributes to inject into JSX component
   *
   * @param integer $id
   * @return void
   */
  public function getMediaItemAttributes($id)
  {
    \check_ajax_referer('et_admin_load_nonce');

    $id = (int) $_POST['id'] ?? 0;

    if (!$id) {
      return new \WP_Error('invalid', 'You must provide an id', ['status' => 400]);
    }

    if (!$video = new ReusableVideo($id)) {
      return false;
    }

    return wp_send_json_success($video->getAttributes());
  }

  public function enqueueScripts()
  {
    if (!et_core_is_fb_enabled()) {
      return;
    }

    $assets = include trailingslashit(PRESTO_PLAYER_PLUGIN_DIR) . 'dist/divi.asset.php';
    wp_enqueue_script(
      'surecart/divi/admin',
      trailingslashit(PRESTO_PLAYER_PLUGIN_URL) . 'dist/divi.js',
      array_merge(['react-dom', 'jquery', 'hls.js'], $assets['dependencies']),
      $assets['version'],
      true
    );
    wp_enqueue_style('surecart/divi/admin', trailingslashit(PRESTO_PLAYER_PLUGIN_URL) . 'dist/divi.css', [], $assets['version']);

    if (function_exists('wp_set_script_translations')) {
      wp_set_script_translations('surecart/divi/admin', 'presto-player');
    }
  }
}

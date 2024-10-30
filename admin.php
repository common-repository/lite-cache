<?php

class LiteCacheAdmin {
    
      const AGENTS = 'up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom';
      
    static $instance;
    
     function __construct() {
         self::$instance = $this;
         
      add_action('admin_init', array($this, 'hook_admin_init'));
      add_action('admin_head', array($this, 'hook_admin_head'));
      add_action('admin_menu', array($this, 'hook_admin_menu'));

    register_activation_hook('lite-cache/plugin.php', array($this, 'hook_activate'));
    register_deactivation_hook('lite-cache/plugin.php', array($this, 'hook_deactivate'));
  }
  
    function hook_activate() {
    $options = get_option('lite-cache', array());
    if (empty($options['agents'])) {
      $options['agents'] = LiteCacheAdmin::AGENTS;
    }
    if (!isset($options['mobile'])) {
      $options['mobile'] = 0;
    }
    update_option('lite-cache', $options);

    // Adjusts the file timestamp
    // It seems to not be working
    @touch(LITE_CACHE_DIR . '/advanced-cache.php');
//    $advanced_cache = file_get_contents(dirname(__FILE__) . '/advanced-cache.php');
//    $advanced_cache = str_replace('_AGENTS_', $options['agents'], $advanced_cache);
//    $advanced_cache = str_replace('_MOBILE_', $options['mobile'], $advanced_cache);
//
//    file_put_contents(WP_CONTENT_DIR . '/advanced-cache.php', $advanced_cache);
//    @touch(WP_CONTENT_DIR . '/advanced-cache.php');

    wp_mkdir_p(WP_CONTENT_DIR . '/cache/lite-cache');
  }
  
    function hook_deactivate() {
    file_put_contents(WP_CONTENT_DIR . '/advanced-cache.php', '');
  }
  
    function hook_admin_init() {
    if (isset($_GET['page']) && strpos($_GET['page'], 'lite-cache/') === 0) {
      wp_enqueue_script('jquery-ui-tabs');
    }
  }

  function hook_admin_head() {
    if (isset($_GET['page']) && strpos($_GET['page'], 'lite-cache/') === 0) {
      echo '<link type="text/css" rel="stylesheet" href="' . LITE_CACHE_URL . '/admin.css?' . LITE_CACHE_VERSION . '"/>';
      echo '<script src="' . LITE_CACHE_URL . '/admin.js?' . LITE_CACHE_VERSION . '"></script>';
    }
  }

  function hook_admin_menu() {
    add_options_page('Lite Cache', 'Lite Cache', 'manage_options', 'lite-cache/options.php');
  }
}

new LiteCacheAdmin();
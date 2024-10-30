<?php

/*
  Plugin Name: Lite Cache
  Plugin URI: http://www.satollo.net/plugins/lite-cache
  Description: A lite and efficient cache. More on <a href="http://www.satollo.net/plugins/lite-cache" target="_blank">Lite Cache</a> official page.
  Version: 2.3.4
  Author: Stefano Lissa
  Author URI: http://www.satollo.net
  Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

define('LITE_CACHE_VERSION', '2.3.4');
define('LITE_CACHE_DIR', WP_PLUGIN_DIR . '/lite-cache');
define('LITE_CACHE_URL', WP_PLUGIN_URL . '/lite-cache');


if (isset($_GET['cache'])) {
    if ($_GET['cache'] === '0') {
        setcookie('cache_disable', 1, time() + 3600 * 24 * 365);
        $x = strpos($_SERVER['REQUEST_URI'], '?');
        header('Location:' . substr($_SERVER['REQUEST_URI'], 0, $x));
        die();
    }

    if ($_GET['cache'] === '1') {
        setcookie('cache_disable', 1, 0);
        $x = strpos($_SERVER['REQUEST_URI'], '?');
        header('Location:' . substr($_SERVER['REQUEST_URI'], 0, $x));
        die();
    }
}


$lite_cache = new LiteCache();

global $cache_stop;

class LiteCache {

    var $post_id;

    function __construct() {
        add_action('edit_post', array($this, 'hook_edit_post'), 1);
        add_action('comment_post', array($this, 'hook_comment_post'), 1, 2);
        add_action('wp_update_comment_count', array($this, 'hook_wp_update_comment_count'), 1);
        add_action('bbp_new_reply', array($this, 'hook_bbp_new_reply'));
        add_action('bbp_new_topic', array($this, 'hook_bbp_new_topic'));
        $options = get_option('lite-cache', array());

        if ($options['mobile'] > 0) {
            add_filter('stylesheet', array($this, 'hook_get_stylesheet'));
            add_filter('template', array($this, 'hook_get_template'));
        }

        if (!isset($_COOKIE['cache_disable'])) {
            add_action('template_redirect', array($this, 'hook_template_redirect'), 0);
            if (isset($options['user']) && $options['user'] == 1) {
                add_filter('show_admin_bar', array($this, 'hook_show_admin_bar'));
            }
        }
    }

    function hook_bbp_new_reply($reply_id) {
        $topic_id = bbp_get_reply_topic_id($reply_id);
        $topic_url = bbp_get_topic_permalink($topic_id);
        //$dir = $this->get_folder() . '' . substr($topic_url, strlen(get_option('home'))) . '/';
        $dir = $this->get_folder() . '/' . substr($topic_url, strpos($topic_url, '://') + 3) . '/';
        $this->remove_dir($dir);

        $forum_id = bbp_get_reply_forum_id($reply_id);
        $forum_url = bbp_get_forum_permalink($forum_id);
        //$dir = $this->get_folder() . '' . substr($forum_url, strlen(get_option('home'))) . '/';
        $dir = $this->get_folder() . '/' . substr($topic_url, strpos($forum_url, '://') + 3) . '/';
        $this->remove_dir($dir);
    }

    function hook_bbp_new_topic($topic_id) {
        $topic_url = bbp_get_topic_permalink($topic_id);
        //$dir = $this->get_folder() . '' . substr($topic_url, strlen(get_option('home'))) . '/';
        $dir = $this->get_folder() . '/' . substr($topic_url, strpos($topic_url, '://') + 3) . '/';
        $this->remove_dir($dir);

        $forum_id = bbp_get_topic_forum_id($topic_id);
        $forum_url = bbp_get_forum_permalink($forum_id);
        $dir = $this->get_folder() . '/' . substr($topic_url, strpos($forum_url, '://') + 3) . '/';
        //$dir = $this->get_folder() . '' . substr($forum_url, strlen(get_option('home'))) . '/';
        $this->remove_dir($dir);
    }

    function hook_show_admin_bar($show_admin_bar) {
        //if (get_current_user_id() == 1) return true;
        return false;
    }

    function hook_get_stylesheet($stylesheet = '') {
        $options = get_option('lite-cache');
        if (!$this->is_mobile())
            return $stylesheet;
        $theme = get_theme($options['theme']);
        if ($theme == null)
            return $stylesheet;
        return $theme['Stylesheet'];
    }

    function hook_get_template($template) {
        $options = get_option('lite-cache');
        if (!$this->is_mobile())
            return $template;
        $theme = get_theme($options['theme']);
        if ($theme == null)
            return $template;
        return $theme['Template'];
    }

    function hook_template_redirect() {
        global $cache_stop;

        if ($cache_stop)
            return;

        $options = get_option('lite-cache');

        if ((!isset($options['user']) || $options['user'] != 1) && is_user_logged_in())
            return;
        // Never cache pages generated for administrator (to be patched to see if the user is an administrator)
        //if (get_current_user_id() == 1) return;

        if (is_404())
            return;
        if (is_trackback())
            return;
        //if ($options['feed'] != 1 && is_feed()) return;
        if (is_robots())
            return;
        if (defined('SID') && SID != '')
            return;

        // Already checked on advanced-cache
        //if (isset($_COOKIE['wp-postpass_' . COOKIEHASH]))
        //  return;

        $home_root = parse_url(get_option('home'), PHP_URL_PATH);
        if (substr($_SERVER['REQUEST_URI'], 0, 4) == ($home_root . '/wp-'))
            return;

        // Compatibility with XML Sitemap 4.x
        if (substr($_SERVER['REQUEST_URI'], 0, 8) == ($home_root . '/sitemap'))
            return;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return;
        }
        if (!empty($_SERVER['QUERY_STRING'])) {
            return;
        }

        // URLs to reject
        if (is_array($options['parsed_urls'])) {
            foreach ($options['parsed_urls'] as &$uri) {
                if (strpos($_SERVER['REQUEST_URI'], $uri) === 0)
                    return;
            }
        }

        foreach ($_COOKIE as $n => $v) {
            if (substr($n, 0, 14) == 'comment_author')
                if (isset($options['nocommentator'])) {
                    return;
                }
            unset($_COOKIE[$n]);
        }

        if (!empty($options['newer_than_days']) && is_single()) {
            global $post;
            if (strtotime($post->post_date_gmt) < time() - 86400 * $options['newer_than_days'])
                return;
        }

        ob_start('lc_callback');
    }

    function hook_comment_post($comment_id, $status) {
        if ($status === 1) {
            $comment = get_comment($comment_id);
            $this->hook_edit_post($comment->comment_post_ID);
        }
    }

    function hook_edit_post($post_id) {
        if ($this->post_id == $post_id)
            return;

        $options = get_option('lite-cache');

        $this->post_id = $post_id;
        $folder = $this->get_folder();
        $url = get_permalink($post_id);
        $dir = $folder . '/' . substr($url, strpos($url, '://') + 3) . '/';
        $this->remove_dir($dir);

        if ($options['last_posts'] != 0) {
            $posts = get_posts(array('numberposts' => $options['last_posts']));
            foreach ($posts as &$post) {
                $url = get_permalink($post->ID);
                $dir = $folder . '/' . substr($url, strpos($url, '://') + 3) . '/';
                $this->remove_dir($dir);
            }
        }

        $dir = $folder . '/' . substr(get_option('home'), strpos($url, '://') + 3);

        // The home
        @unlink($dir . '/index.html');
        @unlink($dir . '/index.html.gz');
        @unlink($dir . '/index-user.html');
        @unlink($dir . '/index-user.html.gz');
        @unlink($dir . '/index-mobile.html');
        @unlink($dir . '/index-mobile.html.gz');
        @unlink($dir . '/index-mobile-user.html');
        @unlink($dir . '/index-mobile-user.html.gz');

        $this->remove_dir($dir . '/feed/');
        // Home subpages
        $this->remove_dir($dir . '/page/');

        @unlink($dir . '/robots.txt');

        $base = get_option('category_base');
        if (empty($base))
            $base = 'category';
        $this->remove_dir($dir . '/' . $base . '/');

        $base = get_option('tag_base');
        if (empty($base))
            $base = 'tag';
        $this->remove_dir($dir . '/' . $base . '/');

        $this->remove_dir($dir . '/type/');

        $this->remove_dir($dir . '/' . date('Y') . '/');
    }

    function hook_wp_update_comment_count($post_id) {
        if ($this->post_id == $post_id) {
            return;
        }
        $this->hook_edit_post($post_id);
    }

    function remove_dir($dir) {
        $files = glob($dir . '*', GLOB_MARK);
        if (!empty($files)) {
            foreach ($files as &$file) {
                if (substr($file, -1) == DIRECTORY_SEPARATOR)
                    $this->remove_dir($file);
                else
                    @unlink($file);
            }
        }
        @rmdir($dir);
    }

    function remove_older_than($time) {
        $this->_remove_older_than($time, $this->get_folder() . '/');
    }

    function _remove_older_than($time, $dir) {
        $files = glob($dir . '*', GLOB_MARK);
        if (!empty($files)) {
            foreach ($files as &$file) {
                if (substr($file, -1) == '/')
                    $this->_remove_older_than($time, $file);
                else if (@filemtime($file) < $time)
                    @unlink($file);
            }
        }
    }

    function is_mobile() {
        if (function_exists('lc_is_mobile'))
            return lc_is_mobile();
        return false;
    }

    function get_folder() {
        $options = get_option('lite-cache', array());
        if (empty($options['folder']))
            return WP_CONTENT_DIR . '/cache/lite-cache';
        else
            return $options['folder'];
    }

}

function lc_callback($buffer) {
    global $cache_stop, $lite_cache, $hyper_cache_stop;

    if ($cache_stop || $hyper_cache_stop)
        return $buffer;
    if (strlen($buffer) == 0)
        return '';
    $uri = preg_replace('/[^a-zA-Z0-9\.\/\-_]+/', '_', $_SERVER['REQUEST_URI']);
    $uri = preg_replace('/\/+/', '/', $uri);
    if ($uri[0] != '/')
        $uri = '/' . $uri;
    $uri = rtrim($uri, '.-_/');

    $lc_dir = $lite_cache->get_folder() . '/' . strtolower($_SERVER['HTTP_HOST']) . $uri;

    $options = get_option('lite-cache');

    $lc_group = '';

    if ($lite_cache->is_mobile()) {
        // Bypass
        if ($options['mobile'] == 2)
            return $buffer;
        // Use the cache
        if ($options['mobile'] == 1)
            $lc_group = '-mobile';
    }

    if (isset($options['user']) && $options['user'] == 1 && is_user_logged_in())
        $lc_group .= '-user';

    $lc_file = $lc_dir . '/index' . $lc_group . '.html';

    if (!is_dir($lc_dir))
        wp_mkdir_p($lc_dir);

    if (!isset($options['nocommentator']) && is_singular() && !is_user_logged_in()) {
        $script = '<script>';
        $script .= 'function lc_get_cookie(name) {';
        $script .= 'var c = document.cookie;';
        $script .= 'if (c.indexOf(name) != -1) {';
        $script .= 'var x = c.indexOf(name)+name.length+1;';
        $script .= 'var y = c.indexOf(";",x);';
        $script .= 'if (y < 0) y = c.length;';
        $script .= 'return decodeURIComponent(c.substring(x,y));';
        $script .= '} else return "";}';
        $script .= 'if ((d = document.getElementById("commentform")) != null) { e = d.elements;';
        $script .= 'var z = lc_get_cookie("comment_author_email_' . COOKIEHASH . '");';
        $script .= 'if (z != "") e["email"].value = z;';
        $script .= 'z = lc_get_cookie("comment_author_' . COOKIEHASH . '");';
        $script .= 'if (z != "") e["author"].value = z;';
        $script .= 'z = lc_get_cookie("comment_author_url_' . COOKIEHASH . '");';
        $script .= 'if (z != "") e["url"].value = z;';
        $script .= '}';
        $script .= '</script>';
        $x = strrpos($buffer, '</body>');
        if ($x) {
            $buffer = substr($buffer, 0, $x) . $script . '</body></html>';
        } else {
            $buffer .= $script;
        }
    }

    $buffer = apply_filters('cache_buffer', $buffer);

    file_put_contents($lc_file, $buffer . '<!-- lite cache ' . date('Y-m-d h:i:s') . ' -->');
    if ($options['nogzip'] != 1) {
        $gzf = gzopen($lc_file . '.gz', 'wb9');
        gzwrite($gzf, $buffer . '<!-- lite cache ' . date('Y-m-d h:i:s') . ' -->');
        gzclose($gzf);
    }

    return $buffer;
}

if (is_admin()) {
    include dirname(__FILE__) . '/admin.php';
}
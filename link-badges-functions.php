<?php

function link_badges__get_icon_sets() {
  $sets = array(
    'default' => 'Icons',
    'dashicons' => 'WordPress Dashicons Glyphs',
    'font-awesome' => 'Font Awesome Glyphs',
    );
  $sets = apply_filters('link-badges-icon-sets', $sets);
  return $sets;
}

function link_badges__get_settings() {
  return wp_parse_args(get_option('link-badges'), array(
    'require_text' => true,
    'show_icon' => true,
    'icon_set' => 'default',
    'thumbnail' => true,
    'thumbnail_size' => 16,
    'units' => 'traditional',

    'show_type' => false,
    'show_size' => false,
    'remote_file_size' => false,
    'remote_download' => true,

    'show_external_icon' => false,
    'show_external_text' => false,
    'external_text' => '',

    'show_popup_icon' => false,
    'show_popup_text' => false,
    'popup_text' => '',
    ));
}


function link_badges__is_external_url($url) {
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Is external URL?', $url);
  $home = $_SERVER['SERVER_NAME'];
  if (preg_match('!^https?://([^/]+)!', $url, $match)) {
    $away = $match[1];
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Is external domain?', $away, $home);
    return $home != $away;
  }
  return false;
}

function link_badges__file_size($url) {
  // relative link
  if (substr($url, 0, 1) == '/') {
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Resolve relative URL', $url);
    $schema = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
    $domain = $_SERVER['SERVER_NAME'];
    $url = "$schema://$domain$url";
  }

  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Looking up file size', $url);

  $saved = get_option('_link_badges__file_sizes');
  if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Memory', $saved);
  if (!is_array($saved)) $saved = array();

  // skip expensive lookups, if we can
  $md5 = substr(md5($url), 16);
  if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: MD5', $md5);
  if (isset($saved[$md5])) {
    $bytes = $saved[$md5];
    if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Remembered size', $bytes);
    return $bytes;
  }

  // local URLs: check if it matches our base
  $url_http = preg_replace('!^https!', 'http', $url);

  $wp_content = wp_upload_dir();
  if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: $wp_content', $wp_content);
  $base_dir = $wp_content['basedir'];
  $base_url = $wp_content['baseurl'];
  $base_url = preg_replace('!^https!', 'http', $base_url);

  while (basename($base_dir) == basename($base_url)) {
    $base_dir = dirname($base_dir);
    $base_url = dirname($base_url);
  }
  if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Minimal URL = %s, dir = %s', $base_url, $base_dir);

  if (substr($url_http, 0, strlen($base_url)) == $base_url) {
    $url = str_replace('%20', ' ', $url);
    $local = substr($url_http, strlen($base_url));
    $filename = $base_dir.$local;
    if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Local file', $filename);
    if (file_exists($filename))
      $bytes = filesize($filename);
    else
      $bytes = 0;
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Local file size', $bytes);
  }

  // remote
  else {
    $url = str_replace(' ', '%20', $url);
    if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Remote file', $url);
    $settings = link_badges__get_settings();
    if ($settings['remote_file_size'] && is_callable('curl_init')) {
      if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Getting remote file size', $url);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_NOBODY, true);
      curl_setopt($ch, CURLOPT_HEADER, true);

      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);

      $data = curl_exec($ch);
      $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($status == 200) {
        $bytes = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Header size', $bytes);
      }
      curl_close($ch);


      if (!isset($bytes) && $settings['remote_download']) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $data = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status == 200) {
          $bytes = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
          if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Whole file size', $bytes);
        }
        curl_close($ch);
      }
    }
  }

  // save the result, if we can
  if (isset($bytes) && $bytes !== false) {
    if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Saving file size', $bytes);
    $bytes = (int) $bytes;
    $saved[$md5] = (int) $bytes;
    update_option('_link_badges__file_sizes', $saved);
    return $bytes;
  }
  return false;
}


function link_badges__file_thumbnail($url) {

}



/**
 * byte_format
 * Function courtesy of JR
 * http://www.if-not-true-then-false.com/2009/format-bytes-with-php-b-kb-mb-gb-tb-pb-eb-zb-yb-converter/
 */
function link_badges__byte_format($bytes, $decimals = 2) {
  if (LINK_BADGES_ALT_NAMES)
    $units = array('B' => 0, 'KiB' => 1, 'MiB' => 2, 'GiB' => 3, 'TiB' => 4, 'PiB' => 5, 'EiB' => 6, 'ZiB' => 7, 'YiB' => 8);
  else
    $units = array('B' => 0, 'kB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);
  $value = 0;
  $unit = "";
  if ($bytes > 0) {
    // Generate automatic prefix by bytes
    // If wrong prefix given
    $pow = floor(log($bytes)/log(LINK_BADGES_SCALE_FACTOR));
    $unit = array_search($pow, $units);

    // Calculate byte value by prefix
    $value = ($bytes/pow(LINK_BADGES_SCALE_FACTOR,floor($units[$unit])));
  }

  // If decimals is not numeric or decimals is less than 0
  // then set default value
  if (!is_numeric($decimals) || $decimals < 0) {
    $decimals = 2;
  }

  // Format output
  return sprintf('%.' . $decimals . 'f '.$unit, $value);
}



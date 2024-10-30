<?php
/*
Plugin Name: Link Badges
Plugin URI: http://www.bang-on.net/
Description: Adds classes to indicate links to PDF, DOC, XLS etc files
Version: 1.4
Author: Marcus Downing
Contributors: diddledan, marcusdowning
Author URI: http://www.bang-on.net
License: GPLv2
*/

/*  Copyright 2011  Marcus Downing  (email : marcus@bang-on.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('LINK_BADGES_DEBUG'))
  define('LINK_BADGES_DEBUG', false);

require_once('link-badges-functions.php');
require_once('link-badges-data.php');

//  settings

add_action('admin_menu', 'link_badges__add_settings');
function link_badges__add_settings() {
  //add_options_page('Bang Link Badges', 'Link Badges', 'administrator', basename(__FILE__), 'link_badges__show_settings');
  add_theme_page(__('Bang Link Badges', 'link-badges'), __('Link Badges', 'link-badges'), 'administrator', basename(__FILE__), 'link_badges__show_settings');
  wp_enqueue_style('link-badges', plugins_url('admin.css', __FILE__));
  wp_enqueue_script('link-badges', plugins_url('scripts/link-badges-admin.js', __FILE__), array('jquery'));
}


add_filter("plugin_action_links_".plugin_basename(__FILE__), 'link_badges__settings_link');
function link_badges__settings_link($links) {
  array_unshift($links, '<a href="themes.php?page=link-badges.php">Settings</a>');
  return $links;
}


function link_badges__show_settings() {
  require('link-badges-settings.php');
}

//  the active ingredient

if (!is_admin()) {
  add_filter('init', 'link_badges__init');
}

function link_badges__init() {
  $settings = link_badges__get_settings();

  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges scale units', $settings['units']);
  define('LINK_BADGES_SCALE_FACTOR', ($settings['units'] == "si") ? 1000 : 1024);
  define('LINK_BADGES_ALT_NAMES', $settings['units'] == "alt");

  wp_enqueue_style('link_badges_css', plugins_url('link-badges.css', __FILE__));

  //$settings = link_badges__get_settings();

  add_filter('link_badges', 'link_badges__the_content');
  add_filter('the_content', 'link_badges__the_content', 101);
  add_filter('widget_text', 'link_badges__the_content', 101);

  add_filter('link_badges__anchor', 'link_badges__anchor', 10, 2);
  add_filter('link_badges__anchor_affix', 'link_badges__anchor_affix', 10, 3);
  add_filter('link_badges__list_item', 'link_badges__list_item', 10, 2);
}

//  filter the content and feed the anchors and links through other hooks
function link_badges__the_content($content) {
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: filtering content', $content);
  //$content = preg_replace_callback('|<a [^>]*>|i', 'link_badges__anchor_cb', $content);
  $content = preg_replace_callback('|(<a [^>]*>)(.*?)(</a>)|is', 'link_badges__anchor_pair_cb', $content);
  $content = preg_replace_callback('|(<li[^>]*>)(.*?)(</li>)|is', 'link_badges__list_item_cb', $content);
  return $content;
}

function link_badges__list_item_cb($matches) {
  $open = $matches[1];
  $content = $matches[2];
  $close = $matches[3];
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: list item cb', $open, $content, $close);

  $open = apply_filters('link_badges__list_item', $open, $content);
  $content = apply_filters('link_badges__list_item_content', $content, $open);
  $close = apply_filters('link_badges__list_item_close', $close, $open, $content);
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: list item filtered', $open, $content, $close);
  return $open.$content.$close;
}

function link_badges__anchor_pair_cb($matches) {
  $open = $matches[1];
  $content = $matches[2];
  $close = $matches[3];

	if (strpos($content, 'link-badge') !== false)
		return $open.$content.$close;

  $open = apply_filters('link_badges__anchor', $open, $content);
  $content = apply_filters('link_badges__anchor_content', $content, $open);
  $affix = apply_filters('link_badges__anchor_affix', '', $content, $open);

  return $open.$content.$affix.$close;
}

//  default filters for anchors
function link_badges__anchor($anchor, $content) {
  $settings = link_badges__get_settings();

  if ($settings['show_icon']) {
    if (preg_match('|href\s*=\s*[\'"]([^\'"]*)[\'"]|i', $anchor, $matches)) {
      $href = $matches[1];
      $badge = link_badges__url2badge($href);
      if ($badge) {
        $content = preg_replace('|<img[^>]*>|is', '', $content);
        if (!empty($content)) {
          $i = $badge->i();
          $anchor = "$anchor$i";
        }
      }
    }
  }

  return $anchor;
}

function link_badges__anchor_affix($affix, $content, $anchor) {
  $settings = link_badges__get_settings();
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Anchor affix', $settings, $anchor);

  if ($settings['require_text']) {
    $content2 = preg_replace('!<[^>]*>!', '', $content);
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Testing content', $content, $content2);
    if (empty($content2))
      return $affix;
  }

  $badge = false;
  $external = false;
  $popup = false;

  if ($settings['show_type'] || $settings['show_size'] || $settings['show_external_icon'] || $settings['show_external_text']) {
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Checking for external link', $anchor);
    if (preg_match('|href\s*=\s*[\'"]([^\'"]*)[\'"]|i', $anchor, $matches)) {
      $url = $matches[1];
      $badge = link_badges__url2badge($url);
      if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Anchor affix url', $url, $badge);
      if (link_badges__is_external_url($url))
        $external = true;
    }
  }

  $mail = is_object($badge) && $badge->icon == "mail";

  if ($settings['show_popup_icon'] || $settings['show_popup_text']) {
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Checking for popup link', $anchor);
    if (preg_match('|target\s*=\s*[\'"]([a-z-_]*)[\'"]|i', $anchor, $matches)) {
      $target = $matches[1];
      if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: target = %s', $target);
      if (!empty($target) && $target != '_self') {
        if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: found popup');
        $popup = true;
      }
    }
  }

  if (($settings['show_type'] || $settings['show_size'] || $settings['show_external_text'] || $settings['show_popup_text']) && !$mail) {
    $parts = array();
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Writing affix');

    if (!empty($badge)) {
      if ($settings['show_type'] && !empty($badge->name)) {
        $typename = apply_filters('link_badges__badge_type', __($badge->name, 'link-badges'), $badge);
        if (!empty($typename))
          $parts[] = $typename;
      }
      if ($settings['show_size']) {
        $bytes = link_badges__file_size($url);
        if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Found size: %s bytes', $bytes);
        if ($bytes > 0) {
          $decimals = $settings['decimals'] ? 2 : 0;
          $parts[] = link_badges__byte_format($bytes, $decimals);
        }
      }
    }
    if ($settings['show_external_text'] && $external)
      $parts[] = empty($settings['external_text']) ? __('external link', 'link-badges') : $settings['external_text'];

    if ($settings['show_popup_text'] && $popup)
      $parts[] = empty($settings['popup_text']) ? __('new window', 'link-badges') : $settings['popup_text'];

    $parts = array_filter($parts);
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Affix parts', $parts);
    if (!empty($parts))
      $affix .= "<span class='link-badges-affix'> (<span class='link-badges-affix-inner'>".implode(", ", $parts)."</span>)</span>";
  }

  if ($settings['show_external_icon'] && $external) {
    $badge = link_badges__badge_data('external');
    $affix .= $badge->i();
  }

  if ($settings['show_popup_icon'] && $popup) {
    $badge = link_badges__badge_data('popup');
    $affix .= $badge->i();
  }
  return $affix;
}

//  default filter for list items
function link_badges__list_item($item, $content) {
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: list item filter', $item, $content);
  if (preg_match('|^<a[^>]*><i class=\'link-badge |is', $content)) {
    $more = apply_filters('link_badges__li_class', '', $item, $content);
    if (preg_match('|class\s*=\s*[\'"]([^\'"]*)|i', $item, $matches))
      $item = preg_replace('|class\s*=\s*[\'"]([^\'"]*)[\'"]|is', "class='\$1 link-badge$more'", $item);
    else
      $item = preg_replace('|>$|', " class='link-badge$more'>", $item);
  }
  return $item;
}

<?php

class LinkBadge {
	var $name;
	var $icon;
  var $glyph;
  var $dashicon;
	var $extensions;
  var $hide_name;

	function __construct($name, $icon, $glyph, $dashicon, $extensions, $hide_name = false) {
		$this->name = $name;
		$this->icon = $icon;
    $this->glyph = $glyph;
    $this->dashicon = $dashicon;
		$this->extensions = $extensions;
    $this->hide_name = $hide_name;
	}

	function matches($url) {
		return in_array($ext, $this->extensions);
	}

  function i() {
    if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: i', $this);
    $settings = link_badges__get_settings();
    if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: i: settings', $settings);
    $i = '';
    switch ($settings['icon_set']) {
      case 'font-awesome':
        $style = '';
        if ($this->icon == 'external' || $this->icon == 'popup')
          $style = 'affix';
        $i = "<i class='link-badge fa $style fa-$this->glyph'></i>";
        break;

      case 'dashicons':
        $style = '';
        if ($this->icon == 'external' || $this->icon == 'popup')
          $style = 'affix';
        $i = "<i class='link-badge $style dashicons dashicons-$this->dashicon'></i>";
        break;

      case 'default':
        if ($this->icon == 'external') {
          $i = "<i class='link-badge-external'></i>";
        } else if ($this->icon == 'popup') {
          $i = "<i class='link-badge-popup'></i>";
        } else {
          $i = "<i class='link-badge $this->icon'></i>";
        }
        break;
    }
    $i = apply_filters('link_badges__i', $i, $this, $settings);
    if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: badge content', $i);
    return $i;
  }
}


function the_link_badge($code) {
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: the_link_badge', $code);
  echo link_badges__i($code);
}

function link_badges__i($code) {
  $badge = link_badges__badge_data($code);
  if ($badge)
    return $badge->i();
  return '';
}


//  select the badge for a given url
function link_badges__url2badge($url) {
  if (empty($url)) return false;
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: URL to badge', $url);

  global $link_badges__url2badge__cache;
  if (isset($link_badges__url2badge__cache[$url]))
    return $link_badges__url2badge__cache[$url];

  $badge = link_badge__url2badge__inner($url);
  $link_badges__url2badge__cache[$url] = $badge;
  return $badge;
}

function link_badge__url2badge__inner($url) {
  //  mail links are special
  if (preg_match('!^mailto:!', $url))
  	return $link_badges__url2badge[$url] = link_badges__badge_data('mail');

  if (preg_match('!^https?://(www\.)?facebook.com/!', $url))
    return $link_badges__url2badge[$url] = link_badges__badge_data('facebook');
  if (preg_match('!^https?://(www\.)?twitter.com/!', $url))
    return link_badges__badge_data('twitter');
  if (preg_match('!^https?://(www\.)?youtube.com/!', $url) || preg_match('!^https?://(www\.)?youtu.be/!', $url))
    return link_badges__badge_data('youtube');
  if (preg_match('!^https?://(www\.)?linkedin.com/!', $url))
    return link_badges__badge_data('linkedin');
  if (preg_match('!^https?://(www\.)?tumblr.com/!', $url))
    return link_badges__badge_data('tumblr');
  if (preg_match('!^https?://(www\.)?vimeo.com/!', $url))
    return link_badges__badge_data('vimeo');
  if (preg_match('!^https?://(www\.)?flickr.com/!', $url))
    return link_badges__badge_data('flickr');
  if (preg_match('!^https?://(www\.)?pinterest.com/!', $url))
    return link_badges__badge_data('pinterest');
  if (preg_match('!^https?://(www\.)?instagram.com/!', $url))
    return link_badges__badge_data('instagram');
  if (preg_match('!^https?://plus.google.com/!', $url))
    return link_badges__badge_data('googleplus');

  $url = preg_replace('!^https?://[^/]*/?!', '', $url);
  if (empty($url)) return false;

  //  use the extension
  $info = (object) pathinfo($url);
  $extension = isset($info->extension) ? $info->extension : '';
  return link_badges__ext2badge($extension);
}

function link_badges__ext2badge($ext) {
  if (empty($ext))
    return false;

  //  use our own list
  $data = link_badges__data();
  foreach ($data as $datum)
    if (in_array($ext, $datum->extensions))
      return $datum;

  // try WordPress list, in case it knows something we don't
  $type = wp_ext2type($ext);
  if (!empty($type) && isset($data[$type]))
  	return $data[$type];

  return false;
}

function link_badges__badge_data($badge) {
  $data = link_badges__data();
  return $data[$badge];
}

function link_badges__data() {
	global $link_badges__data;
	if (!empty($link_badges__data))
		return $link_badges__data;

	$link_badges__data = array(
		'mail' => new LinkBadge('E-mail address', 'mail', 'envelope-o', 'email-alt', array()),
		'audio' => new LinkBadge('Audio', 'audio', 'file-audio-o', 'format-audio', array('mp3', 'wav', 'm4a', 'wma', 'ra', 'aif', 'aiff', 'mpa', 'flac', 'wv', 'mpa', 'm4p', 'ogg', 'oga', 'mka')),
		'video' => new LinkBadge('Video', 'video', 'file-video-o', 'editor-video', array('avi', 'divx', 'xvid', 'mov', 'qt', 'webm', 'mpg', 'mpeg', 'mp4', 'm4v', 'vob', 'ifo', 'mkv', 'avc', '264', 'h264', 'x264', 'flv', 'f4v', 'swf', 'asf', 'wmv', 'rm', 'srt', 'ssa', 'ass', 'ogv', 'ogx', '3gp', '3g2')),
		'document' => new LinkBadge('Document', 'doc', 'file-word-o', 'admin-page', array('doc', 'docx', 'rtf', 'odt', 'fodt', 'pages')),
		'spreadsheet' => new LinkBadge('Spreadsheet', 'xls', 'file-excel-o', 'analytics', array('xls', 'xlsx', 'ods', 'fods', 'csv')),
		'presentation' => new LinkBadge('Presentation', 'ppt', 'file-powerpoint-o', 'feedback', array('pps', 'ppsx', 'ppt', 'pptx', 'odp', 'fodp', 'key')),
		'ebook' => new LinkBadge('eBook', 'ebook', 'book', 'book-alt', array('epub', 'mobi', 'ibooks', 'aeh', 'lrf', 'lrx', 'chm', 'pdb', 'pdg', 'fb2', 'xeb', 'ceb', 'azw', 'kf8', 'lit', 'prc', 'tebr', 'tr2', 'tr3')),
		'comic' => new LinkBadge('Comics eBook', 'ebook', 'book', 'book-alt', array('cbr', 'cbz', 'cb7', 'cbt', 'cba')),
		'application' => new LinkBadge('Application', 'exe', 'desktop', 'format-video', array('exe', 'com', 'wsf', 'app', 'gadget', 'cgi', 'vb', 'jar', 'war')),
		'installer' => new LinkBadge('Application installer', 'exe', 'desktop', 'format-video', array('msi', 'pkg', 'deb', 'bundle')),
		'script' => new LinkBadge('Runnable script', 'exe', 'desktop', 'format-video', array('bat', 'sh', 'bash', 'ksh', 'csh', 'zsh')),
		'archive' => new LinkBadge('Archive', 'zip', 'file-archive-o', 'portfolio', array('zip', 'zipx', 'gzip', 'gz', 'bzip', 'bz', 'bz2', 'tar', '7z', 'rar', 'sit', 'sitx', 'sea', 'bin', 'hqz', 'mim', 'uue')),
		'pdf' => new LinkBadge('PDF document', 'pdf', 'file-pdf-o', 'admin-page', array('pdf')),
		'postscript' => new LinkBadge('Postscript document', 'pdf', 'file-o', 'admin-page', array('ps')),
		'vector' => new LinkBadge('Vector image', 'img', 'picture-o', 'format-image', array('eps', 'svg')),
		'image' => new LinkBadge('Image', 'img', 'picture-o', 'format-image', array('gif', 'jpeg', 'jpg', 'png', 'tif', 'tiff', 'bmp', 'yuv', 'tga', 'psd')),
    'feed' => new LinkBadge('Feed', 'feed', 'rss-square', 'rss', array('rss', 'atom')),
    'calendar' => new LinkBadge('Calendar', 'calendar', 'calendar', 'calendar', array('ical', 'ics', 'ifb', 'icalendar')),

    'facebook' => new LinkBadge('Facebook link', 'facebook', 'facebook-square', 'facebook', array(), true),
    'twitter' => new LinkBadge('Twitter link', 'twitter', 'twitter-square', 'twitter', array(), true),
    'youtube' => new LinkBadge('YouTube link', 'youtube', 'youtube-play', 'video-alt3', array(), true),
    'linkedin' => new LinkBadge('LinkedIn link', 'linkedin', 'linkedin-square', '', array(), true),
    'tumblr' => new LinkBadge('Tumblr link', 'tumblr', 'tumblr-square', '', array(), true),
    'vimeo' => new LinkBadge('Vimeo link', 'vimeo', 'vimeo-square', '', array(), true),
    'flickr' => new LinkBadge('Flickr link', 'flickr', 'flickr', '', array(), true),
    'pinterest' => new LinkBadge('Pinterest link', 'pinterest', 'pinterest-square', '', array(), true),
    'instagram' => new LinkBadge('Instagram link', 'instagram', 'instagram', '', array(), true),
    'googleplus' => new LinkBadge('Google+ link', 'googleplus', 'google-plus-square', 'googleplus', array(), true),

    'external' => new LinkBadge('External link', 'external', 'external-link', 'redo', array(), true),
    'popup' => new LinkBadge('New window', 'popup', 'expand', 'editor-distractionfree', array(), true),
		);
	$link_badges__data = apply_filters('link-badges-data', $link_badges__data);
  return $link_badges__data;
}

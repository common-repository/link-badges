=== Link Badges ===
Contributors: marcus.downing, diddledan
Tags: anchor
Requires at least: 3.0
Tested up to: 5.5.0
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add document icons and file sizes to links

== Description ==

Whenever your content contains a link to a file, this plugin can display:

* An icon for the link
* The type of file
* The size of the file

== Installation ==

1. Upload the `link-badges` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How does this plugin know the size of a file? =

If the file is on the same server, it looks at it directly on disk. If it's on another server somewhere on the internet, it tries to get the size by downloading the file.

The plugin has some memory of the file sizes, so it doesn't need to do this every time a page is loaded.

= Can I make it say 'Kb' rather than 'kB'? =

No. This plugin uses the correct units for file sizes.

There's a setting in the options page to use SI units (based on multiples of 1000) rather than traditional file size units (based on 1024).

= Can I use CSS to customise the way it's displayed? =

Yes.

`/* Icons */
i.link-badge {
  ...
}

/* File sizes and types */
span.link-badges-affix {
  ...
}`


== Changelog ==

= 1.4 =
* Improved cache of file sizes
* Don't show file sizes for mail links
* Options for showing decimals
* Spaces in local vs remote
* Resolve relative URLs

= 1.2 =
* Filling in missing icons in Font Awesome

= 1.1 =
* Add option to use icon fonts (Dashicons and Font Awesome)
* More file formats
* More CSS hook, filters and actions
* Better i18n
* Updated admin are to fit the WP 3.8 appearance

= 1.0 =
* First version

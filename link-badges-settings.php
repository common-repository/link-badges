<div id='bang-leftbar' class='link-badges'>
  <a href="http://www.bang-on.net">
    <img src="<?php echo plugins_url('images/bang-black-v.png', __FILE__); ?>" /></a>
  <div><h1><?php _e('Link Badges', 'link-badges'); ?></h1></div>
</div>

<div id='bang-main' class="wrap">

<?php screen_icon("themes"); ?><h2><?php _e('Link Badges', 'link-badges'); ?></h2>
<?php

// settings
$settings = link_badges__get_settings();
if (LINK_BADGES_DEBUG >= 2) do_action('log', 'Link badges: Settings', $settings);

if ($_POST['save-link-badges']) {
  $settings['require_text'] = isset($_POST['require_text']) && (boolean) $_POST['require_text'];
  $settings['show_icon'] = isset($_POST['show_icon']) && (boolean) $_POST['show_icon'];
  $settings['icon_set'] = stripslashes($_POST['icon_set']);
  $settings['thumbnail'] = isset($_POST['thumbnail']) && (boolean) $_POST['thumbnail'];
  $settings['thumbnail_size'] = isset($_POST['thumbnail_size']) ? (int) $_POST['thumbnail_size'] : 16;
  $settings['show_type'] = isset($_POST['show_type']) && (boolean) $_POST['show_type'];
  $settings['show_size'] = isset($_POST['show_size']) && (boolean) $_POST['show_size'];
  $settings['remote_file_size'] = isset($_POST['remote_file_size']) && (boolean) $_POST['remote_file_size'];
  $settings['remote_download'] = isset($_POST['remote_download']) && (boolean) $_POST['remote_download'];
  $settings['units'] = isset($_POST['units']) ? $_POST['units'] : "traditional";
  $settings['decimals'] = isset($_POST['decimals']) && (boolean) $_POST['decimals'] && $_POST['decimals'] != 'false';
  $settings['show_external_icon'] = isset($_POST['show_external_icon']) && (boolean) $_POST['show_external_icon'];
  $settings['show_external_text'] = isset($_POST['show_external_text']) && (boolean) $_POST['show_external_text'];
  $settings['external_text'] = stripslashes($_POST['external_text']);
  $settings['show_popup_icon'] = isset($_POST['show_popup_icon']) && (boolean) $_POST['show_popup_icon'];
  $settings['show_popup_text'] = isset($_POST['show_popup_text']) && (boolean) $_POST['show_popup_text'];
  $settings['popup_text'] = stripslashes($_POST['popup_text']);

  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Updating settings', $settings);
  update_option('link-badges', $settings);

  ?><div class='updated'>
      <p>Link badge settings svaed.</p>
  </div><?php
}

if ($_REQUEST['forget_sizes'] == 'yes') {
  if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Dropping file size cache');
  delete_option('_link_badges__file_sizes');

  ?><div class='updated error'>
      <p>Link badge memory dropped.</p>
  </div><?php
}

extract($settings);

?>
<p><?php _e('This plugin decorates links to files in the content with icons and other information about the link.', 'link-badges'); ?></p>

<div class='tabs-bar'><p>
  <a href='#settings-pane' class='tab current'><?php _e('Settings', 'link-badges'); ?></a>
  <a href='#formats-pane' class='tab'><?php _e('Formats', 'link-badges'); ?></a>
</p></div>

<div class="pane current" id="settings-pane">

  <p class='preview-pane'>
    <?php _e('Preview', 'link-badges'); ?>: &nbsp; &nbsp;
    <a href='#'><?php
        if ($show_icon)
          the_link_badge('video');
      ?>Link to a video file<?php
        $parts = array();
        if ($show_type)
          $parts[] = __('Video', 'link-badges');
        if ($show_size) {
          $parts[] = ($decimals ? '12.71 ' : '12 ').($units == "alt" ? "MiB" : "MB");
        }
        if ($show_external_text)
          $parts[] = empty($external_text) ? __('external link', 'link-badges') : $external_text;
        if ($show_popup_text)
          $parts[] = empty($popup_text) ? __('new window', 'link-badges') : $popup_text;

        if (!empty($parts)) {
          echo "<span> (";
          echo implode(", ", $parts);
          echo ")</span>";
        }

        if ($show_external_icon) {
          the_link_badge('external');
        }
        if ($show_popup_icon) {
          the_link_badge('popup');
        }
      ?></a>
    &nbsp; &nbsp;
  </p>

<form name='link-badges' action='themes.php?page=link-badges.php' method='post'>
  <div class='metabox-holder'>

    <style>
      table.link-flow-settings-table th {
        text-align: left;
        padding: 4px;
      }
      table.link-flow-settings-table td {
        padding: 5px 16px 5px 4px;
      }
    </style>

    <div class='postbox'>
      <h3><?php _e('When to show', 'link-badges'); ?></h3>
      <div class='inside'>
        <table class='link-flow-settings-table'>
          <tr>
            <?php $checked = $require_text ? 'checked' : ''; ?>
            <th><label for='require_text'><input type='checkbox' name='require_text' id='require_text' <?php echo $checked ?> /> <b><?php _e('Only modify links with text content', 'link-badges'); ?></b></label></th>
            <td><?php _e('(This will prevent link badges from appearing on image links)', 'link-badges'); ?></td>
          </tr>
        </table>
      </div>
    </div>

    <div class='postbox'>
      <h3><?php _e('View options', 'link-badges'); ?></h3>
      <div class='inside'>
        <input type='hidden' name='save-link-badges' value='on'/>

        <table class='link-flow-settings-table'>
          <tr>
            <?php $checked = $show_icon ? 'checked' : ''; ?>
            <td><label for='show_icon'><input type='checkbox' name='show_icon' id='show_icon' <?php echo $checked ?> /> <b><?php _e('Show icons', 'link-badges'); ?></b></label></td>
            <td><select name='icon_set' id='icon_set'><?php
              $icon_sets = link_badges__get_icon_sets();
              if (LINK_BADGES_DEBUG) do_action('log', 'Link badges: Icon set', $icon_set, $icon_sets);
              foreach ($icon_sets as $key => $name) {
                $selected = $icon_set == $key ? 'selected' : '';
                echo "<option value='$key' $selected>".esc_html(__($name), 'link-badges')."</option>\n";
              }
            ?></select></td>
            <td colspan='2'>
              <div class='icon-set <?php if ($icon_set == 'default') echo 'selected' ?>'  id='icon-set-default'>
                <i class='link-badge video'></i>
                <i class='link-badge img'></i>
                <i class='link-badge pdf'></i>
                <i class='link-badge doc'></i>
                <i class='link-badge xls'></i>
                <i class='link-badge ppt'></i>
                <i class='link-badge audio'></i>
                <i class='link-badge mail'></i>
              </div>
              <div class='icon-set <?php if ($icon_set == 'dashicons') echo 'selected' ?>' id='icon-set-dashicons'>
                <i class="dashicons dashicons-editor-video"></i> &nbsp;
                <i class="dashicons dashicons-format-image"></i> &nbsp;
                <i class="dashicons dashicons-admin-page"></i> &nbsp;
                <i class="dashicons dashicons-analytics"></i> &nbsp;
                <i class="dashicons dashicons-feedback"></i> &nbsp;
                <i class="dashicons dashicons-format-audio"></i> &nbsp;
                <i class="dashicons dashicons-email-alt"></i> &nbsp;
              </div>
              <div class='icon-set <?php if ($icon_set == 'font-awesome') echo 'selected' ?>' id='icon-set-font-awesome'>
                <i class="fa fa-lg fa-film"></i> &nbsp;
                <i class="fa fa-lg fa-picture-o"></i> &nbsp;
                <i class="fa fa-lg fa-file-o"></i> &nbsp;
                <i class="fa fa-lg fa-file-text-o"></i> &nbsp;
                <i class="fa fa-lg fa-table"></i> &nbsp;
                <i class="fa fa-lg fa-file-o"></i> &nbsp;
                <i class="fa fa-lg fa-volume-up"></i> &nbsp;
                <i class="fa fa-lg fa-envelope-o"></i> &nbsp;
              </div>
              <?php
                do_action('link-badges-preview-icon-sets');
              ?>
            </td>
          </tr>

          <tr>
            <?php $checked = $show_type ? 'checked' : ''; ?>
            <th><label for='show_type'><input type='checkbox' name='show_type' id='show_type' <?php echo $checked ?> /> <?php _e('Show file types', 'link-badges'); ?></label></th>
          </tr>

          <tr>
            <?php $checked = $show_size ? 'checked' : ''; ?>
            <?php $decimals = isset($decimals) && (boolean) $decimals; ?>
            <th><label for='show_size'><input type='checkbox' name='show_size' id='show_size' <?php echo $checked ?> /> <?php _e('Show file sizes', 'link-badges'); ?></label></th>
            <td><label for='decimals'>
              <input type='checkbox' name='decimals' id='decimals' <?php echo ($decimals ? 'checked' : '') ?>> Show decimals (14.37 kB)
            </label></td>
          </tr>

          <tr>
            <th>&nbsp; &nbsp; &nbsp; &nbsp; <?php _e('Units', 'link-badges'); ?>:</th>
            <?php
              $checked = $units == "traditional" || empty($units) ? 'checked' : '';
              echo "<td><label for='units-traditional'><input type='radio' name='units' id='units-traditional' value='traditional' $checked/> ".
                  __('Traditional', 'link-badges')." (1 kB = 1024 bytes)</label></td>";
              $checked = $units == "si" ? 'checked' : '';
              echo "<td><label for='units-si'><input type='radio' name='units' id='units-si' value='si' $checked/> ".
                  __('SI units', 'link-badges')." (1 kB = 1000 bytes)</label></td>";
              $checked = $units == "alt" ? 'checked' : '';
              echo "<td><label for='units-alt'><input type='radio' name='units' id='units-alt' value='alt' $checked/> ".
                  __('Alternative SI units', 'link-badges')." (1 KiB = 1024 bytes)</label></td>";
            ?></td>
          </tr>

          <tr>
            <?php $checked = $remote_file_size ? 'checked' : ''; ?>
            <th>&nbsp; &nbsp; &nbsp; &nbsp; <label for='remote_file_size' style='padding-right: 30px;'><input type='checkbox' name='remote_file_size' id='remote_file_size' <?php echo $checked ?> /> <?php _e('Including files on other servers', 'link-badges'); ?></th>
            <td>To determine file size:</td>
            <?php
              $checked = $remote_download ? '' : 'checked';
              echo "<td><label for='remote_download_no'><input type='radio' name='remote_download' value='' id='remote_download_no' $checked /> ".
                  __('Only ask for file size', 'link-badges')."</label></td>";

              $checked = $remote_download ? 'checked' : '';
              echo "<td><label for='remote_download_yes'><input type='radio' name='remote_download' value='on' id='remote_download_yes' $checked /> ".
                  __('Download whole file if necessary', 'link-badges')."</label></td>";
            ?>
            <?php if (!is_callable('curl_init')) { ?>
              <p>Warning: cURL module required for remote file size checks.</p>
            <?php } ?>
          </tr>

          <tr>
            <th><?php _e('External links', 'link-badges'); ?>:</th>
            <td>
              <?php $checked = $show_external_icon ? 'checked' : ''; ?>
              <label for='show_external_icon'><input type='checkbox' name='show_external_icon' id='show_external_icon' <?php echo $checked ?> /> <?php _e('Show icon for external links', 'link-badges'); ?></label> &nbsp; <?php the_link_badge('external'); ?>
            </td>
            <td>
              <?php $checked = $show_external_text ? 'checked ' : ''; ?>
              <label for='show_external_text'><input type='checkbox' name='show_external_text' id='show_external_text' <?php echo $checked ?> /> <?php _e('Show text for external links', 'link-badges'); ?>:</label>
            </td>
            <td>
              <input type='text' name='external_text' id='external_text' value='<?php echo esc_attr($external_text) ?>' placeholder='<?php _e('external link', 'link-badges'); ?>' />
            </td>
          </tr>

          <tr>
            <th><?php _e('Opens in new window', 'link-badges'); ?>:</th>
            <td>
              <?php $checked = $show_popup_icon ? 'checked' : ''; ?>
              <label for='show_popup_icon'><input type='checkbox' name='show_popup_icon' id='show_popup_icon' <?php echo $checked ?> /> <?php _e('Show icon for popups', 'link-badges'); ?></label> &nbsp; <?php the_link_badge('popup'); ?>
            </td>
            <td>
              <?php $checked = $show_popup_text ? 'checked ' : ''; ?>
              <label for='show_popup_text'><input type='checkbox' name='show_popup_text' id='show_popup_text' <?php echo $checked ?> /> <?php _e('Show text for popups', 'link-badges'); ?>:</label>
            </td>
            <td>
              <input type='text' name='popup_text' id='popup_text' value='<?php echo esc_attr($popup_text) ?>' placeholder='<?php _e('new window', 'link-badges'); ?>' />
            </td>
          </tr>

        </table>

        <input type='submit' value='<?php _e('Save changes', 'link-badges'); ?>' class='button-primary'/>
      </div>
    </div>

    <div class='postbox'>
      <h3><?php _e('Memory', 'link-badges'); ?></h3>
      <div class='inside'>

        <p><?php _e('For efficiency, this plugin remembers the size of files it\'s already downloaded, and caches thumbnail images. This information may become inaccurate if the destination of a link changes. Click here to make it forget everything.', 'link-badges'); ?></p>
        <p><a href='themes.php?page=link-badges.php&amp;forget_sizes=yes' class='button delete-button'><?php _e('Erase memory', 'link-badges'); ?></a></p>
      </div>
    </div>
  </div>
</form>

</div><div class="pane" id="formats-pane">

<table class='wp-list-table widefat'>
	<thead><tr><th colspan='3'>Icons</th><th><?php _e('Format', 'link-badges'); ?></th><th><?php _e('Extensions', 'link-badges'); ?></th></tr></thead>
  <tbody><?php

	$data = link_badges__data();
	foreach ($data as $badge) {
		$checked = 'checked';
    echo "<tr>";
		echo "<td><img src='".plugins_url('images/'.$badge->icon.'.png', __FILE__)."'></td>";
    echo "<td><i class='fa fa-lg fa-$badge->glyph'></i></td>";
    echo "<td><i class='dashicons dashicons-$badge->dashicon'></td>";
		echo "<td><p>&nbsp;<b>".esc_html(__($badge->name), 'link-badges')."</b>&nbsp;&nbsp;&nbsp;</p></td>";
		echo "<td><p>";
    if (!empty($badge->extensions))
      echo "<tt>.".implode('</tt>, <tt>.', array_map('esc_html', $badge->extensions))."</tt>";
    echo "</p></td>";
		echo "</tr>";
	}

	?></tbody>
</table>

</div>

</div>

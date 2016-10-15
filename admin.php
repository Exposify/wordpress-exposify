<?php

/**
 * Add an options page.
 */
function exposify_add_options_page()
{
  add_options_page('Exposify', 'Exposify', 'manage_options', 'exposify', 'exposify_settings_page');
}

/**
 * Initialize all settings.
 */
function exposify_init_settings()
{
  register_setting('exposify_settings', 'exposify_settings');

  // register general section and fields
  add_settings_section(
    'exposify_general_section',
    __('Allgemeines', 'exposify'),
    null,
    'exposify_settings'
  );

  add_settings_field(
    'exposify_api_url',
    __('Deine Team-URL von Exposify*', 'exposify'),
    'exposify_api_url_render',
    'exposify_settings',
    'exposify_general_section'
  );

  add_settings_field(
    'exposify_api_key',
    __('Dein API Schlüssel für Exposify*', 'exposify'),
    'exposify_api_key_render',
    'exposify_settings',
    'exposify_general_section'
  );

  add_settings_field(
    'exposify_site_title',
    __('Der angezeigte Titel für die Immobilienübersicht', 'exposify'),
    'exposify_site_title_render',
    'exposify_settings',
    'exposify_general_section'
  );

  add_settings_field(
    'exposify_site_slug',
    __('Der Slug für die Immobilienübersicht', 'exposify'),
    'exposify_site_slug_render',
    'exposify_settings',
    'exposify_general_section'
  );

  // register visual section and fields
  add_settings_section(
    'exposify_visual_section',
    __('Darstellung', 'exposify'),
    null,
    'exposify_settings'
  );

  add_settings_field(
    'exposify_template_overview',
    __('HTML Template für die Immobilien Übersicht', 'exposify'),
    'exposify_template_overview_render',
    'exposify_settings',
    'exposify_visual_section'
  );

  add_settings_field(
    'exposify_template_single',
    __('HTML Template für eine einzelne Immobilie', 'exposify'),
    'exposify_template_single_render',
    'exposify_settings',
    'exposify_visual_section'
  );

  add_settings_field(
    'exposify_css',
    __('CSS Template', 'exposify'),
    'exposify_css_render',
    'exposify_settings',
    'exposify_visual_section'
  );
}

/**
 * Display the field.
 */
function exposify_api_url_render()
{
  $options = get_option('exposify_settings');
  ?>
  <input class="regular-text" type="text" name="exposify_settings[exposify_api_url]" value="<?php echo $options['exposify_api_url']; ?>" placeholder="z.B. https://app.exposify.de/api/beta/foo-bar-team">
  <?php
}

/**
 * Display the field.
 */
function exposify_api_key_render()
{
  $options = get_option('exposify_settings');
  ?>
  <input class="regular-text" type="text" name="exposify_settings[exposify_api_key]" value="<?php echo $options['exposify_api_key']; ?>" placeholder="z.B. e7081hfnjhdf987341r8rq98exir8x73084rzneh">
  <?php
}

/**
 * Display the field.
 */
function exposify_site_title_render()
{
  $options = get_option('exposify_settings');
  ?>
  <input class="regular-text" type="text" name="exposify_settings[exposify_site_title]" value="<?php echo $options['exposify_site_title']; ?>" placeholder="z.B. Immobilien">
  <?php
}

/**
 * Display the field.
 */
function exposify_site_slug_render()
{
  $options = get_option('exposify_settings');
  ?>
  <input class="regular-text" type="text" name="exposify_settings[exposify_site_slug]" value="<?php echo $options['exposify_site_slug']; ?>" placeholder="z.B. immobilien">
  <?php
}

/**
 * Display the field.
 */
function exposify_template_overview_render()
{
  $options = get_option('exposify_settings');
  ?>
  <p>
    <?php echo __('Das HTML Template für die Übersicht aller Immobilien. Über das Array <code>$properties</code> kann auf alle Immobilien zugegriffen werden. Über die Variable <code>$search_query</code> kann auf den aktuellen Such-String zugegriffen werden.', 'exposify'); ?>
  </p>
  <textarea class="large-text" type="text" name="exposify_settings[exposify_template_overview]" rows="15"><?php echo $options['exposify_template_overview']; ?></textarea>
  <?php
}

/**
 * Display the field.
 */
function exposify_template_single_render()
{
  $options = get_option('exposify_settings');
  ?>
  <p>
    <?php echo __('Das HTML Template für eine einzelne Immobilie. Über die Variable <code>$property</code> kann auf die Immobilie zugegriffen werden.', 'exposify'); ?>
  </p>
  <textarea class="large-text" type="text" name="exposify_settings[exposify_template_single]" rows="15"><?php echo $options['exposify_template_single']; ?></textarea>
  <?php
}

/**
 * Display the field.
 */
function exposify_css_render()
{
  $options = get_option('exposify_settings');
  ?>
  <textarea class="large-text" type="text" name="exposify_settings[exposify_css]" rows="15"><?php echo $options['exposify_css']; ?></textarea>
  <?php
}

/**
 * Display the options page.
 */
function exposify_settings_page()
{
  ?>
  <form action="options.php" method="post">
    <h2>Exposify</h2>
    <?php
    settings_fields('exposify_settings');
    do_settings_sections('exposify_settings');
    submit_button();
    ?>
  </form>
  <?php
}

/**
 * Hook for updated settings. Update the page and rules when necessary.
 * @param $old_settings Array The old settings
 * @param $new_settings Array The new settings
 */
function exposify_settings_updated($old_settings, $new_settings)
{
  $changed_settings = array_keys(array_diff_assoc($new_settings, $old_settings));
  if (
    in_array('exposify_site_title', $changed_settings) ||
    in_array('exposify_site_slug',  $changed_settings)
  ) {
    wp_update_post([
      'ID'         => get_option('exposify_properties_page_id'),
      'post_title' => $new_settings['exposify_site_title'] ? $new_settings['exposify_site_title'] : 'Immobilien',
      'post_name'  => $new_settings['exposify_site_slug'] ? $new_settings['exposify_site_slug'] : 'immobilien'
    ]);
  }

  if (in_array('exposify_site_slug',  $changed_settings)) {
    exposify_rewrite();
    flush_rewrite_rules();
  }
}

/**
 * Remove the properties page from the overview.
 * @param  [type] $query [description]
 * @return [type]        [description]
 */
function exposify_remove_page_from_overview($query)
{
  global $pagenow, $post_type;

  if ($pagenow == 'edit.php' && $post_type == 'page') {
    $query->query_vars['post__not_in'] = [get_option('exposify_properties_page_id')];
  }
}

add_filter('parse_query', 'exposify_remove_page_from_overview');
add_action('admin_menu', 'exposify_add_options_page');
add_action('admin_init', 'exposify_init_settings');
add_action('update_option_exposify_settings', 'exposify_settings_updated', 10, 2);

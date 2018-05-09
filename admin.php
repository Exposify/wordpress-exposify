<?php

/**
 * Autoload all classes in the 'Settings' directory.
 *
 * @return void
 */
function autoloadSettingClasses()
{
  spl_autoload_register(function ($className) {
    if (strpos($className, 'Exposify') === false) return;
    $path = __DIR__ . '/Settings/' . $className . '.php';
    if (!file_exists($path)) return;
    require($path);
  });
}

/**
 * Add an options page.
 *
 * @return void
 */
function exposify_add_options_page()
{
  add_options_page('Exposify', 'Exposify', 'manage_options', 'exposify', 'exposify_settings_page');
}

/**
 * Initialize all settings.
 *
 * @return void
 */
function exposify_init_settings()
{
  // Register the setting which will be stored in the database.
  //   'exposify' is both group and page (http://bit.ly/2FWfcHr)
  //   'exposify_settings' is the database key
  //   'exposify_sanitize_settings' is the callback to - drum-roll - sanitize the setting
  register_setting('exposify', 'exposify_settings', 'exposify_sanitize_settings');

  // Register a general section.
  $generalSection = new ExposifyGeneralSection($page = 'exposify');
  $generalSection->addField(new ExposifyApiKeyField());
  $generalSection->addField(new ExposifySiteTitleField());
  $generalSection->addField(new ExposifySiteSlugField());
  $generalSection->register();

  $generalSection = new ExposifyVisualSection($page = 'exposify');
  $generalSection->addField(new ExposifyThemeTemplateField());
  $generalSection->register();
}

/**
 * Sanitize settings which aren't used as template.
 *
 * @param  array  $option
 * @return array
 */
function exposify_sanitize_settings($option)
{
  $option['exposify_api_key']        = sanitize_text_field($option['exposify_api_key']);
  $option['exposify_site_title']     = sanitize_text_field($option['exposify_site_title']);
  $option['exposify_site_slug']      = sanitize_text_field($option['exposify_site_slug']);
  $option['exposify_theme_template'] = sanitize_text_field($option['exposify_theme_template']);

  return $option;
}

/**
 * Display the options page.
 *
 * @return void
 */
function exposify_settings_page()
{
  ?>
  <form action="options.php" method="post">
    <h2>Exposify</h2>
    <?php
    echo_settings_form_meta_fields('exposify');
    echo_settings_form_sections('exposify');
    submit_button();
    ?>
  </form>
  <?php
}

/**
 * Hook for updated settings. Update the page and rules when necessary.
 *
 * @param  array  $old_settings
 * @param  array  $new_settings
 * @return void
 */
function exposify_settings_updated($old_settings, $new_settings)
{
  $changed_settings = array_keys(array_diff_assoc($new_settings, $old_settings));
  if (
    in_array('exposify_site_title', $changed_settings) ||
    in_array('exposify_site_slug',  $changed_settings)
  ) {
    wp_update_post([
      'ID'         => get_option('exposify_overview_page_id'),
      'post_title' => $new_settings['exposify_site_title'] ? $new_settings['exposify_site_title'] : 'Immobilien',
      'post_name'  => $new_settings['exposify_site_slug']  ? $new_settings['exposify_site_slug']  : 'immobilien'
    ]);
  }

  if (in_array('exposify_site_slug',  $changed_settings)) {
    exposify_rewrite();
    flush_rewrite_rules();
  }
}

/**
 * Remove the properties page from the overview.
 *
 * @param  WP_Query $query
 * @return void
 */
function exposify_remove_pages_from_admin_interfaces($query)
{
  global $pagenow, $post_type;

  // remove detail page from every possible place
  $query->query_vars['post__not_in'] = [
    get_option('exposify_property_page_id')
  ];

  // remove overview page only from wordpress page overview
  if ($pagenow == 'edit.php' && $post_type == 'page') {
    $query->query_vars['post__not_in'][] = get_option('exposify_overview_page_id');
  }
}

require(__DIR__ . '/settings_wrapper.php');
autoloadSettingClasses();
add_action('parse_query', 'exposify_remove_pages_from_admin_interfaces');
add_action('admin_menu', 'exposify_add_options_page');
add_action('admin_init', 'exposify_init_settings');
add_action('update_option_exposify_settings', 'exposify_settings_updated', 10, 2);
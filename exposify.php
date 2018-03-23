<?php

/*
Plugin Name: Exposify
Plugin URI: https://exposify.de
Description: Zeigt alle eigenen Immobilienangebote von Exposify.
Version: 1.8.2
Author: Exposify
Author URI: https://exposify.de
License: GPL2
*/

/**
 * Add the page and the rewrite rules.
 *
 * @return void
 */
function exposify_activate_plugin()
{
  exposify_add_all_pages();
  exposify_rewrite();
  flush_rewrite_rules();
}

/**
 * Remove the pages. Remove the old option from the previous plugin version.
 *
 * @return void
 */
function exposify_deactivate_plugin()
{
  exposify_delete_page('exposify_overview_page_id');
  exposify_delete_page('exposify_property_page_id');

  delete_option('exposify_properties_page_id');

  flush_rewrite_rules();
}

/**
 * Rewrite the URLs for single properties. Add 404 redirect for detail page.
 *
 * @return void
 */
function exposify_rewrite()
{
  $options = get_option('exposify_settings');
  $slug = $options['exposify_site_slug'] ? $options['exposify_site_slug'] : 'immobilien';
  add_rewrite_rule('^' . $slug . '/(.+)/?$', 'index.php?page_id=' . get_option('exposify_property_page_id') . '&slug=$matches[1]', 'top');
  add_rewrite_tag('%slug%', '([^&]+)');
  add_rewrite_rule('^exposify-detail$', 'index.php?error=404', 'top');
}

/**
 * Add a properties page and store its id. If there is already a saved id, delete it and add a new one.
 *
 * @return void
 */
function exposify_add_all_pages()
{
  $options = get_option('exposify_settings');
  $overviewTitle = ($options['exposify_site_title']) ? $options['exposify_site_title'] : 'Immobilien';
  $overviewSlug  = ($options['exposify_site_slug'])  ? $options['exposify_site_slug']  : 'immobilien';

  exposify_add_page($overviewTitle, $overviewSlug, 'exposify_overview_page_id');
  exposify_add_page('Exposify Detail', 'exposify-detail', 'exposify_property_page_id');
}

/**
 * Add an Exposify page with title, slug and an option to store its id in the database.
 *
 * @param  string  $pageTitle
 * @param  string  $pageSlug
 * @param  string  $pageDatabaseOptionName
 * @return void
 */
function exposify_add_page($pageTitle, $pageSlug, $pageDatabaseOptionName)
{
  $page = [
    'post_content' => '',
    'post_title'   => $pageTitle,
    'post_name'    => $pageSlug,
    'post_status'  => 'publish',
    'post_type'    => 'page'
  ];

  $pageId = get_option($pageDatabaseOptionName);
  if ($pageId) {
    wp_delete_post($pageId, true);
  }

  $pageId = wp_insert_post($page);
  update_option($pageDatabaseOptionName, $pageId);
}

/**
 * Delete an Exposify page with the option where its id is stored in the database.
 *
 * @param  string  $pageDatabaseOptionName
 * @return void
 */
function exposify_delete_page($pageDatabaseOptionName)
{
  $pageId = get_option($pageDatabaseOptionName);
  if ($pageId) {
    wp_delete_post($pageId, true);
  }
}

register_activation_hook(  __FILE__, 'exposify_activate_plugin');
register_deactivation_hook(__FILE__, 'exposify_deactivate_plugin');

add_action('init', 'exposify_rewrite');

if (is_admin()) {
  require(__DIR__ . '/admin.php');
} else {
  require(__DIR__ . '/handler.php');
  require(__DIR__ . '/public.php');
}

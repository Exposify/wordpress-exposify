<?php

/*
Plugin Name: Exposify
Plugin URI: https://exposify.de
Description: Zeigt alle eigenen Immobilienangebote von Exposify.
Version: 1.1.1
Author: Exposify
Author URI: https://exposify.de
License: GPL2
*/

/**
 * Add the page and the rewrite rules.
 */
function exposify_activate_plugin()
{
  exposify_add_properties_page();
  exposify_rewrite();
  flush_rewrite_rules();
}

/**
 * Remove the page.
 */
function exposify_deactivate_plugin()
{
  $pageID = get_option('exposify_properties_page_id');
  if ($pageID) {
    wp_delete_post($pageID, true);
  }
  flush_rewrite_rules();
}

/**
 * Rewrite the URLs for single properties
 */
function exposify_rewrite()
{
  $options = get_option('exposify_settings');
  $slug = $options['exposify_site_slug'] ? $options['exposify_site_slug'] : 'immobilien';
  add_rewrite_rule('^' . $slug . '/(.+)/?$', 'index.php?page_id=' . get_option('exposify_properties_page_id') . '&slug=$matches[1]', 'top');
  add_rewrite_tag('%slug%', '([^&]+)');
}

/**
 * Add a properties page and store its id. If there is already a saved id, delete it and add a new one.
 */
function exposify_add_properties_page()
{
  $options = get_option('exposify_settings');
  $pageTitle = ($options['exposify_site_title']) ? $options['exposify_site_title'] : 'Immobilien';
  $pageName  = ($options['exposify_site_slug'])  ? $options['exposify_site_slug']  : 'immobilien';

  $page = [
    'post_content' => '',
    'post_title'   => $pageTitle,
    'post_name'    => $pageName,
    'post_status'  => 'publish',
    'post_type'    => 'page'
  ];

  $pageID = get_option('exposify_properties_page_id');
  if ($pageID) {
    wp_delete_post($pageID, true);
  }

  $pageID = wp_insert_post($page);
  update_option('exposify_properties_page_id', $pageID);
}

register_activation_hook(  __FILE__, 'exposify_activate_plugin');
register_deactivation_hook(__FILE__, 'exposify_deactivate_plugin');

add_action('init', 'exposify_rewrite');

if (is_admin()) {
  require(__DIR__ . '/admin.php');
}

require(__DIR__ . '/public.php');

<?php

/**
 * Evaluate the HTML Template from the database for a single property and display it.
 * @param  String $api_url
 * @param  String $api_key
 * @param  String $slug
 */
function exposify_show_single_property($api_url, $api_key, $slug)
{
  $api_json = file_get_contents($api_url . '/' . $slug . '?api_token=' . $api_key);
  $property = json_decode($api_json, true);
  // evaluate the single Template
  $visual_settings = get_option('exposify_settings');
  eval(' ?>' . $visual_settings['exposify_template_single'] . '<?php ');
}

/**
 * Evaluate the HTML Template from the database for the properties overview and display it.
 * @param  String $api_url
 * @param  String $api_key
 * @param  String $search_query
 */
function exposify_show_properties_overview($api_url, $api_key, $search_query='')
{
  // make the request
  $api_json   = file_get_contents($api_url . '?api_token=' . $api_key . '&query=' . $search_query);
  $api_array  = json_decode($api_json, true);
  $properties = $api_array['properties'];
  // evaluate the overview Template
  $visual_settings = get_option('exposify_settings');
  eval(' ?>' . $visual_settings['exposify_template_overview'] . '<?php ');
}

/**
 * Alter the content of the properties page and replace it with the templates.
 * @param  String $content
 * @return String $content
 */
function exposify_change_properties_page_content($content)
{
  if (get_the_ID() == get_option('exposify_properties_page_id')) {
    // get the credentials
    $credentials = get_option('exposify_settings');
    $immoapiurl = $credentials['exposify_api_url'];
    $immoapikey = $credentials['exposify_api_key'];

    if (filter_var($immoapiurl, FILTER_VALIDATE_URL) === false || !$immoapikey) {
      return __('Die Immobilienübersicht ist noch nicht fertig eingerichtet.', 'exposify');
    }

    if (get_query_var('slug')) {
      exposify_show_single_property($immoapiurl, $immoapikey, get_query_var('slug'));
    } else {
      exposify_show_properties_overview($immoapiurl, $immoapikey, get_query_var('search'));
    }
  }
  return $content;
}

/**
 * Alter the title of the properties page.
 * @param  String $content
 * @return String $content
 */
function exposify_change_properties_page_title($title, $id)
{
  if ($id == get_option('exposify_properties_page_id')) {
    if (get_query_var('slug')) {
      $credentials = get_option('exposify_settings');
      $immoapiurl = $credentials['exposify_api_url'];
      $immoapikey = $credentials['exposify_api_key'];
      $api_json = file_get_contents($immoapiurl . '/' . get_query_var('slug') . '?api_token=' . $immoapikey);
      $property = json_decode($api_json, true);
      return $property['name'];
    }
  }
  return $title;
}

/**
 * Display the custom css. Use wp_enqueue_style later.
 */
function exposify_get_css()
{
  if (get_the_ID() == get_option('exposify_properties_page_id')) {
    $data = get_option('exposify_settings');
    $css = "<style id='exposify-css'>" . $data['exposify_css'] . "</style>\r";
    echo $css;
  }
}

add_filter('the_content', 'exposify_change_properties_page_content');
add_filter('the_title', 'exposify_change_properties_page_title', 10, 2);
add_action('wp_head', 'exposify_get_css', 99999999);

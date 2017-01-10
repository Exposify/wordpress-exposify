<?php

class ExposifyViewer {

  /**
  * An instance of the Exposify handler.
  * @var Exposify
  */
  public $exposify;

  /**
  * Construct the class.
  * @param String $apiKey
  * @param String $baseUrl
  */
  public function __construct($apiKey, $baseUrl = 'https://app.exposify.de')
  {
    $this->exposify = new Exposify($apiKey, $baseUrl);

    add_filter('the_content',        [$this, 'changePageContent']);
    add_filter('page_template',      [$this, 'changePageTemplate']);
    add_action('wp_enqueue_scripts', [$this, 'insertLinks']);
  }

  /**
  * Request the property/properties, if there isn't a result yet.
  * @return Void
  */
  public function attemptRequest()
  {
    if (empty($this->exposify->html->getResult())) {
      if (get_query_var('slug')) {
        $this->exposify->html->requestSingleProperty(get_query_var('slug'));
      } else {
        $this->exposify->html->requestAllProperties(get_query_var('search', ''));
      }
    }
  }

  /**
  * Change the page template to the specified one.
  * @param  String $oldTemplate
  * @return String
  */
  public function changePageTemplate($oldTemplate)
  {
    if (get_the_ID() != get_option('exposify_properties_page_id')) {
      return $oldTemplate;
    }

    $new_template = get_option('exposify_settings')['exposify_theme_template'];
    if (locate_template($new_template) != '' && $new_template != 'default') {
      return get_template_directory() . '/' . $new_template;
    }

    return $oldTemplate;
  }

  /**
  * Insert the properties into the page.
  * @param  String $oldContent
  * @return String
  */
  public function changePageContent($oldContent)
  {
    if (get_the_ID() != get_option('exposify_properties_page_id')) {
      return $oldContent;
    }
    $this->attemptRequest();

    return $this->exposify->html->getContent();
  }

  /**
  * Change the page title to the property name.
  * @param  String $oldTitle
  * @return String
  */
  public function changePageTitle($oldTitle)
  {
    if (
      get_the_ID() != get_option('exposify_properties_page_id') ||
      !in_the_loop()
    ) {
      return $oldTitle;
    }

    $this->attemptRequest();

    return $this->exposify->html->getTitle();
  }

  /**
  * Insert all external CSS and JS files in the page.
  * @return Void
  */
  public function insertLinks()
  {
    if (get_the_ID() != get_option('exposify_properties_page_id')) {
      return;
    }

    $this->attemptRequest();

    if (isset($this->exposify->html->getError()['css']))  {
      $css = $this->exposify->html->getError()['js'];
    }
    if (isset($this->exposify->html->getResult()['css'])) {
      $css = $this->exposify->html->getResult()['css'];
    }
    if (isset($css) && is_array($css)) {
      foreach ($css as $css_src) {
        wp_enqueue_style('exposify', $css_src);
      }
    }

    if (isset($this->exposify->html->getError()['js']))  {
      $js = $this->exposify->html->getError()['js'];
    }
    if (isset($this->exposify->html->getResult()['js'])) {
      $js = $this->exposify->html->getResult()['js'];
    }
    if (isset($js) && is_array($js)) {
      foreach ($js as $js_src) {
        wp_enqueue_script('exposify', $js_src);
      }
    }
  }
}

$viewer = new ExposifyViewer(get_option('exposify_settings')['exposify_api_key']);

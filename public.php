<?php

class ExposifyViewer {

  /**
  * An instance of the Exposify handler.
  *
  * @var Exposify
  */
  public $exposify;

  /**
  * Construct the class.
  *
  * @param  string  $apiKey
  * @param  string  $baseUrl
  * @return void
  */
  public function __construct($apiKey, $baseUrl = 'https://app.exposify.de')
  {
    $this->exposify = new Exposify($apiKey, $baseUrl);

    add_filter('the_content',             [$this, 'changePageContent']);
    add_filter('page_template',           [$this, 'changePageTemplate']);
    add_action('wp_enqueue_scripts',      [$this, 'insertLinks']);
    add_filter('the_title',               [$this, 'changePageTitle'], 10, 2);
    add_filter('pre_get_document_title',  [$this, 'changeSiteTitle']);
    // this is a YOAST frontend filter, it will only be applied if YOAST is installed
    add_filter('wpseo_metadesc',          [$this, 'changeMetaDescription']);
    // this is a wpSEO frontend filter, it will only be applied if wpSEO is installed
    add_filter('wpseo_set_desc',          [$this, 'changeMetaDescription']);
    // this is where we insert new head resources for SSR
    add_action('wp_head',                 [$this, 'insertSSRHead'], 9999);
    // this is where we insert new head resources for SSR
    add_action('wp_print_footer_scripts', [$this, 'insertSSRBody'], 9999);
  }

  /**
  * Request the property/properties, if there isn't a result yet.
  *
  * @return void
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
  *
  * @param  string  $oldTemplate
  * @return string
  */
  public function changePageTemplate($oldTemplate)
  {
    if (
      get_the_ID() != get_option('exposify_overview_page_id') &&
      get_the_ID() != get_option('exposify_property_page_id')
    ) {
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
  *
  * @param  string  $oldContent
  * @return string
  */
  public function changePageContent($oldContent)
  {
    if (
      get_the_ID() != get_option('exposify_overview_page_id') &&
      get_the_ID() != get_option('exposify_property_page_id')
    ) {
      return $oldContent;
    }
    $this->attemptRequest();

    return $this->exposify->html->getContent();
  }

  /**
  * Change the page title to the property name. We need to use the passed $pageId
  * here instead of the get_the_ID() method because it would return the same ID
  * for all titles. But we only want to change the main title of the detail page,
  * not of the other menu items.
  *
  * @param  string  $oldTitle
  * @return string
  */
  public function changePageTitle($oldTitle, $pageId)
  {
    if (
      !get_query_var('slug') ||
      $pageId != get_option('exposify_property_page_id')
    ) {
      return $oldTitle;
    }

    $this->attemptRequest();

    return $this->exposify->html->getTitle();
  }

  /**
  * Change the site title to the property name.
  *
  * @param  string  $oldTitle
  * @return string
  */
  public function changeSiteTitle($oldTitle)
  {
    if (get_query_var('slug')) {
      $this->attemptRequest();
      return $this->exposify->html->getTitle();
    }

    return $oldTitle;
  }

  /**
   * Change the meta description of the site if YOAST is installed.
   *
   * @param  string  $oldDescription
   * @return string|bool
   */
  public function changeMetaDescription($oldDescription)
  {
    $this->attemptRequest();
    if ($description = $this->exposify->html->getDescription()) {
      return $description;
    }

    return false;
  }

  /**
  * Insert all external CSS and JS files in the page.
  *
  * @return void
  */
  public function insertLinks()
  {
    if (
      get_the_ID() != get_option('exposify_overview_page_id') &&
      get_the_ID() != get_option('exposify_property_page_id')
    ) {
      return;
    }

    $this->attemptRequest();

    if (isset($this->exposify->html->getError()['attributes']['css']))  {
      $css = $this->exposify->html->getError()['attributes']['css'];
    }
    if (isset($this->exposify->html->getResult()['attributes']['css'])) {
      $css = $this->exposify->html->getResult()['attributes']['css'];
    }
    if (isset($css) && is_array($css)) {
      $i = 1;
      foreach ($css as $css_src) {
        wp_enqueue_style('exposify-' . $i, $css_src);
        $i++;
      }
    }

    if (isset($this->exposify->html->getError()['attributes']['js']))  {
      $js = $this->exposify->html->getError()['attributes']['js'];
    }
    if (isset($this->exposify->html->getResult()['attributes']['js'])) {
      $js = $this->exposify->html->getResult()['attributes']['js'];
    }

    if (isset($js) && is_array($js)) {
      $i = 1;
      foreach ($js as $js_src) {
        wp_enqueue_script('exposify-' . $i, $js_src, ['jquery'], false, true);
        $i++;
      }
    }
  }

  /**
   * Insert SRR Head End Resources if there are any.
   *
   * @return void
   */
  public function insertSSRHead()
  {
    $apiResponse = $this->exposify->html->getResult();
    if (isset($apiResponse['attributes']['endHead'])) {
      echo $apiResponse['attributes']['endHead'];
    }
  }

  /**
   * Insert SRR Body End Resources if there are any.
   *
   * @return void
   */
  public function insertSSRBody()
  {
    $apiResponse = $this->exposify->html->getResult();
    if (isset($apiResponse['attributes']['endBody'])) {
      echo $apiResponse['attributes']['endBody'];
    }
  }
}

$viewer = new ExposifyViewer(get_option('exposify_settings')['exposify_api_key']);

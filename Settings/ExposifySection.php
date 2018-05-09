<?php

abstract class ExposifySection
{
  /**
   * @var string
   */
  protected $page;

  /**
   * @var array
   */
  protected $fields = [];

  /**
   * Construct the section.
   *
   * @param  string  $page
   * @return void
   */
  public function __construct($page)
  {
    $this->page = $page;
  }

  /**
   * Add a field to the section.
   *
   * @param  ExposifyField  $field
   * @return void
   */
  public function addField(ExposifyField $field)
  {
    $field->setPage($this->page);
    $field->setSection($this->getName());
    $this->fields[] = $field;
  }

  /**
   * Register the section and its fields with the WP Settings API.
   *
   * @return void
   */
  public function register()
  {
    add_settings_section(
      $this->getName(),
      __($this->getTitle(), 'exposify'),
      null,
      $this->page
    );

    foreach ($this->fields as $field) {
      $field->register();
    }
  }

  /**
   * Get the name of the section.
   *
   * @return string
   */
  abstract public function getName();

  /**
   * Get the title of the section.
   *
   * @return string
   */
  abstract public function getTitle();
}
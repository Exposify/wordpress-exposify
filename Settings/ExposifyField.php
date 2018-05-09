<?php

abstract class ExposifyField
{
  /**
   * @var string
   */
  protected $page;

  /**
   * @var string
   */
  protected $section;

  /**
   * Register this field with the WP Settings API.
   *
   * @return void
   */
  public function register()
  {
    add_settings_field(
      $this->getSettingName(),
      __($this->getSettingTitle(), 'exposify'),
      [$this, 'render'],
      $this->page,
      $this->section
    );
  }

  /**
   * Set the page of this field.
   *
   * @param  string  $page
   * @return void
   */
  public function setPage($page)
  {
    $this->page = $page;
  }

  /**
   * Set the section of this field.
   *
   * @param  string  $section
   * @return void
   */
  public function setSection($section)
  {
    $this->section = $section;
  }

  /**
   * Render the field.
   *
   * @return void
   */
  abstract public function render();

  /**
   * Get the name of the setting the field represents.
   *
   * @return string
   */
  abstract public function getSettingName();

  /**
   * Get the title of the field.
   *
   * @return string
   */
  abstract public function getSettingTitle();
}
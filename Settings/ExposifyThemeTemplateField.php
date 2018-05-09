<?php

class ExposifyThemeTemplateField extends ExposifyField
{
  /**
   * Get the name of the setting the field represents.
   *
   * @return string
   */
  public function getSettingName()
  {
    return 'exposify_theme_template';
  }

  /**
   * Get the title of the field.
   *
   * @return string
   */
  public function getSettingTitle()
  {
    return 'Page Template für die Anzeige der Immobilien';
  }

  /**
   * Render the field.
   *
   * @return void
   */
  public function render()
  {
    $options = get_option('exposify_settings');
    ?>
    <select name="exposify_settings[exposify_theme_template]">
      <?php
      echo '<option value="default" ' . ($options['exposify_theme_template'] == 'default' ? 'selected' : '') . '>Default</option>';
      foreach(wp_get_theme()->get_page_templates() as $path => $name) {
        echo '<option value="' . $path . '" ' . ($options['exposify_theme_template'] == $path ? 'selected' : '') . '>' . $name . '</option>';
      }
      ?>
    </select>
    <?php
  }
}
<?php

class ExposifyCustomTypesField extends ExposifyField
{
  /**
   * Get the name of the setting the field represents.
   *
   * @return string
   */
  public function getSettingName()
  {
    return 'exposify_custom_types';
  }

  /**
   * Get the title of the field.
   *
   * @return string
   */
  public function getSettingTitle()
  {
    return 'Eigene Objekttypen';
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
    <textarea class="code" name="exposify_settings[exposify_custom_types]" cols="40" rows="10"><?php
      echo json_encode($options['exposify_custom_types'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    ?></textarea>
    <div style="font-size: .9em;">JSON im Format {"foo": ["bar", 1, null]}</div>
    <?php
  }
}
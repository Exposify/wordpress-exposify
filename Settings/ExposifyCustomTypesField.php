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

    ?><textarea class="code" name="exposify_settings[exposify_custom_types]" cols="40" rows="10"><?php
      echo json_encode($options['exposify_custom_types'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    ?></textarea>
    <div style="font-size: .9em;">JSON im Format</div>
    <pre style="font-size: .9em;"><?php
      echo $this->getExample();
    ?></pre><?php
  }

  /**
   * Get a JSON string as example.
   *
   * @return string
   */
  protected function getExample()
  {
    return json_encode([
      [
        'title'     => 'Titel des Typs',
        'slug'      => 'Slug des Typs',
        'types'     => 'Liste der Exposify Typen',
        'marketing' => 'Liste der Vermarktungen'
      ]
    ], JSON_PRETTY_PRINT);
  }
}
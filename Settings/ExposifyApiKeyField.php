<?php

class ExposifyApiKeyField extends ExposifyField
{
  /**
   * Get the name of the setting the field represents.
   *
   * @return string
   */
  public function getSettingName()
  {
    return 'exposify_api_key';
  }

  /**
   * Get the title of the field.
   *
   * @return string
   */
  public function getSettingTitle()
  {
    return 'Dein API Schlüssel für Exposify*';
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
    <input class="regular-text" type="text" name="exposify_settings[exposify_api_key]" value="<?php echo $options['exposify_api_key']; ?>" placeholder="z.B. e7081hfnjhdf987341r8rq98exir8x73084rzneh">
    <?php
  }
}
<?php

class ExposifySiteTitleField extends ExposifyField
{
  /**
   * Get the name of the setting the field represents.
   *
   * @return string
   */
  public function getSettingName()
  {
    return 'exposify_site_title';
  }

  /**
   * Get the title of the field.
   *
   * @return string
   */
  public function getSettingTitle()
  {
    return 'Der angezeigte Titel für die Immobilienübersicht';
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
    <input class="regular-text" type="text" name="exposify_settings[exposify_site_title]" value="<?php echo $options['exposify_site_title']; ?>" placeholder="z.B. Immobilien">
    <?php
  }
}
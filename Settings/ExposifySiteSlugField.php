<?php

class ExposifySiteSlugField extends ExposifyField
{
  /**
   * Get the name of the setting the field represents.
   *
   * @return string
   */
  public function getSettingName()
  {
    return 'exposify_site_slug';
  }

  /**
   * Get the title of the field.
   *
   * @return string
   */
  public function getSettingTitle()
  {
    return 'Der Slug für die Immobilienübersicht';
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
    <input class="regular-text" type="text" name="exposify_settings[exposify_site_slug]" value="<?php echo $options['exposify_site_slug']; ?>" placeholder="z.B. immobilien">
    <?php
  }
}
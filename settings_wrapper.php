<?php

/**
 * Print the meta fields like 'action' or 'nonce' for the given page.
 *
 * @param  string  $page
 * @return void
 */
function echo_settings_form_meta_fields($page) {
  settings_fields($page);
}

/**
 * Print the sections and their fields for the given page.
 *
 * @param  string  $page
 * @return void
 */
function echo_settings_form_sections($page)
{
  do_settings_sections($page);
}
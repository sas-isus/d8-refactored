<?php

// Forcibly uninstall Lightning Dev, switch the current installation profile
// from Standard to Minimal, and delete defunct config objects.

Drupal::configFactory()
  ->getEditable('core.extension')
  ->clear('module.lightning_dev')
  // Uninstall Lightning Page so it can be correctly reinstalled during Behat
  // tests in FixtureContext::setUp().
  ->clear('module.lightning_page')
  ->clear('module.standard')
  ->set('module.minimal', 1000)
  ->set('profile', 'minimal')
  ->save();

Drupal::keyValue('system.schema')->deleteMultiple(['lightning_dev']);

Drupal::configFactory()->getEditable('lightning_api.settings')->delete();

Drupal::configFactory()
  ->getEditable('core.base_field_override.node.page.promote')
  ->delete();

Drupal::configFactory()
  ->getEditable('core.base_field_override.node.page.status')
  ->delete();

entity_get_form_display('node', 'page', 'default')->delete();
entity_get_display('node', 'page', 'default')->delete();
entity_get_display('node', 'page', 'teaser')->delete();

Drupal::configFactory()->getEditable('field.field.node.page.body')->delete();

Drupal::configFactory()->getEditable('node.type.page')->delete();

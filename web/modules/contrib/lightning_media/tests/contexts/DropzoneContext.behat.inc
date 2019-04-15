<?php

namespace Acquia\LightningExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Contains step definitions for interacting with DropzoneJS widgets.
 */
final class DropzoneContext extends DrupalSubContextBase {

  /**
   * Attaches a file to a dropzone.
   *
   * @param string $file
   *   The file to attach, relative to the file directory configured for Mink.
   *
   * @When I attach the file :file to the dropzone
   */
  public function attachFileToDropzone($file) {
    $this->getSession()
      ->executeScript('Dropzone.instances[0].hiddenFileInput.name = "file"');

    $this->getContext(MinkContext::class)->attachFileToField('file', $file);

    // @todo: React when the upload actually completes.
    sleep(3);
  }

  /**
   * Uploads multiple files into the media library using Dropzone.
   *
   * @param \Behat\Gherkin\Node\PyStringNode $files
   *
   * @When I upload the following files:
   */
  public function uploadMultiple(PyStringNode $files) {
    $assert = $this->assertSession();

    $this->visitPath('/admin/content/media');
    $assert->elementExists('named', ['link', 'Bulk upload'])->click();
    // Wait for the dropzone to be initialized.
    sleep(3);

    $files = $files->getStrings();

    foreach ($files as $file) {
      $this->attachFileToDropzone($file);
    }
    $assert->elementExists('named', ['button', 'Continue'])->press();

    for ($i = 0; $i < count($files); $i++) {
      $assert->elementExists('named', ['button', 'Save'])->press();
    }
  }

}

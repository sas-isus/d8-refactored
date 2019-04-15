@lightning @api @lightning_media @javascript
Feature: Bulk uploading media assets

  # This test may produce a false negative if the browser is on a different
  # host (i.e., the test is running a Docker container and communicating
  # with headless Chrome on the host system). The reason for this is that Mink
  # will compute an absolute path to the file *in the container*, but the
  # browser will not be able to load the file from that path (since it's not
  # running in the container), and the upload will fail. So, this test can
  # only really be run locally.
  @72286b5d
  Scenario: Bulk uploading media assets
    Given I am logged in as a user with the "access media overview, create media, update media, dropzone upload files, view the administration theme" permissions
    When I upload the following files:
    """
    test.jpg
    test.mp3
    test.mp4
    test.pdf
    """
    Then I should see "test.jpg" in the media library
    And I should see "test.mp3" in the media library
    And I should see "test.mp4" in the media library
    And I should see "test.pdf" in the media library

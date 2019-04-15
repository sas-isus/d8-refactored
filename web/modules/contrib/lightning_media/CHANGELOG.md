## 3.8.0
* Lightning Media is now compatible with Lightning Core 4.x (Drupal core 8.7.x).

## 3.7.0
* Updated Entity Browser to 2.1.
* Added a link to the settings form on the module list page. (Issue #3033650)
* Added descriptions to administrative links. (Issue #3034042)

## 3.6.0
* The media browser is now displayed in a modal dialog by default, which is
  activated by pressing the "Add media" button. When embedding media in the
  WYSIWYG editor, the media browser is unchanged (the entity browser used for
  WYSIWYG has been split out into a completely separate entity browser
  configuration in order to facilitate this). (GitHub #80)

## 3.5.0
* Updated Lightning Core to 3.5, which security updates Drupal core to 8.6.6.
* Added a configuration option to control whether newly-created media fields 
  (i.e., entity reference fields which reference media items) will be configured 
  to use the media browser by default. (Issue #2945153)
* The "Show in media library" field is no longer translatable by default in any
  media type included with Lightning Media. (Issue #3014913)
* Changes were made to the internal testing infrastructure, but nothing that
  will affect users of Lightning Media.

## 3.4.0
* Many changes to internal testing infrastructure, but nothing that affects
  users of Lightning Media.

## 3.3.0
* Behat test now check for existence of FixtureContext prior to invoking its
  methods. (Issue #3020990)

## 3.2.0
* Updated Lightning Core to 3.4.
* Changed the testing infrastructure, sealed all subcontexts, refactored most
  of the Behat tests into PHPUnit.
* Updated Media Entity Instagram to 2.0-alpha2.
* Added namespaces to dependencies.
* Warning messages are not double escaped anymore.
* Updated Entity Browser to 2.0.

## 3.1.0
* Added a new component, Media Slideshow, which allows you to create
  slideshows and carousels of assets from your media library using the
  Slick JavaScript library. (#52)
* Lightning Media now has the Media Library module as an explicit
  dependency.
* In Quick Edit, it's now possible to remove images selected with the
  media browser. (#53)
* Entity reference fields that use the media browser now allow you to
  explicitly select the media type to use when creating or uploading new
  media items. This works in CKEditor as well. (#55 and issue #2969541)

## 3.0.0
* Updated Lightning Core to 3.0, which requires Drupal core 8.6.0.

## 2.4.0
* Locally hosted audio and video files are now supported. Audio support is
  provided by a new component. (Issue #2965767)
* Documents are now stored in folders based on the current date (YYYY-MM).
  (Issue #2958909)
* Fixed a bug where administrator roles provided by Lightning Media had a
  null value for the 'is_admin' flag. (Issue #2882197)
* The "Save to media library" checkbox is now labeled "Show in media library".
  (Issue #2990935)
* All bundled media types now have out-of-the-box support for Pathauto. (#38)

## 2.3.0
No changes since last release.

## 2.2.0
* Updated to Video Embed Field 2.0.

## 2.1.0
* Behat contexts used for testing were moved into the
  `Acquia\LightningExtension\Context` namespace.

## 2.0.0
* Provided an optional update to rename the "Source" filter on the Media
  overview page to "Type".
* Updated Crop API to RC1 and no longer pin it to a specific release.
* Media Entity is no longer used, provided, or patched by Lightning Media.
* In keeping with recent changes in Drupal core, Lightning Media provides an
  update hook that modifies any configured Media-related actions to use the
  new, generic action plugins provided by core.

## 1.0.0-rc3
* Lightning Media will only set up developer-specific settings when our
  internal developer tools are installed.

## 1.0.0-rc2
* Removed legacy update code.

## 1.0.0-rc1
* Allow Media types to be configured without a Source field. (Issue #2928658)

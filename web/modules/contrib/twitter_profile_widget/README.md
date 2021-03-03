CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Theming
 * Maintainers


INTRODUCTION
------------
This module provides read-only requests via Twitter API. Tweets from any user account, timelines, user-favorited tweets, and Twitter-wide search results may be displayed.

After registering API credentials, create any number of widgets, each of which can display different tweets. Per-widget configuration includes whether to display replies & retweets and how many tweets should display.

By design, the theming is minimal. Developers are expected to customize the display via their chosen theme.


INSTALLATION
------------
1. Install as you would normally install a contributed drupal module. See https://www.drupal.org/documentation/install/modules-themes/modules-8 for further information.

2. Go to 'Manage > Extend', and enable the Twitter Profile Widget module (drush en twitter_profile_widget).

CONFIGURATION
-------------
1. You first need a Twitter App. This is different from the Twitter widget ID, and allows you to connect to the Twitter API. To get a Twitter App, sign in to Twitter and go to https://apps.twitter.com/ . Copy the "key" and "secret" associated with your new app.

2. After installing the module in Drupal, go to Configuration > Media > Twitter Widgets (admin/config/media/twitter_profile_widget). Enter the Twitter App key and secret you created in step #1.

3. Go to Admin > Structure > Block Layout > Add custom block (/admin/structure/block/block-content) and create one or more widgets. The "description" is internal, to identify different widgets.

4. Now that you have Twitter widgets set up, you can display them in any part of your site via block display.


THEMING
-------
By design, the display of tweets provided by this module is minimal. Developers can copy the twitter_widget.html.twig file from the /templates directory and add it to their theme and customize as necessary.

To negate the CSS provided by this module, remove the {{ attach_library('twitter_profile_widget/twitter-profile-widget') }} from the twig file, or point it to your own defined library.


MAINTAINERS
-----------
Current maintainers:
 * Mark Fullmer (mark_fullmer) - https://www.drupal.org/u/mark_fullmer

<?php

/**
 * NEW SITES:
 * - rename this file to settings.local.php
 * - update the primary domain setting below
 */

// Initialize only, DO NOT SET!
// TODO: TEST THIS
global $primary_domain;

/**
 * If the site will be proxied by our proxy server then
 *   uncomment the following settings['...'] lines
 */
//$settings['reverse_proxy'] = TRUE;
//$settings['reverse_proxy_addresses'] = array('128.91.219.96');

/**
 * Set simplesamlphp library directory
 */
if (isset($_ENV['HOME'])) {
    $settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/web/private/simplesamlphp';
}

/**
 * Pantheon recommended settings.
 * -
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  // Redirect to https://$primary_domain in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    /**
     * If the site is not being proxied
     *   replace example-pan-site.sas.upenn.edu with the registered domain name
     * If the site IS being proxied
     *   replace with the pan-site.sas.upenn.edu domain anem
     */
    $primary_domain = 'example-pan-site.sas.upenn.edu';
  }
  else {
    // Redirect to HTTPS on every Pantheon environment except live.
    $primary_domain = $_SERVER['HTTP_HOST'];
  }

  if ($_SERVER['HTTP_HOST'] != $primary_domain
      || !isset($_SERVER['HTTP_USER_AGENT_HTTPS'])
      || $_SERVER['HTTP_USER_AGENT_HTTPS'] != 'ON' ) {

    # Name transaction "redirect" in New Relic for improved reporting (optional)
    if (extension_loaded('newrelic')) {
      newrelic_name_transaction("redirect");
    }

    header('HTTP/1.0 301 Moved Permanently');
    header('Location: https://'. $primary_domain . $_SERVER['REQUEST_nURI']);
    exit();
  }
}

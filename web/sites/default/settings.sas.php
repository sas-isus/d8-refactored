<?php

/*
 * settings.sas.php
 *
 * Include settings that are used across all sites
 */


/**
 * Set simplesamlphp library directory
 */
if (isset($_ENV['HOME'])) {
    $settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/web/private/simplesamlphp';
}


/**
 * Require HTTPS across all Pantheon environments
 * Check if Drupal is running via command line
 *
 * https://pantheon.io/docs/redirects/
 *
 * We probably don't need this but leaving here for the time being.
 */
//if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['HTTPS'] === 'OFF') && (php_sapi_name() != "cli")) {
//    if (!isset($_SERVER['HTTP_USER_AGENT_HTTPS']) || (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) 
//        && $_SERVER['HTTP_USER_AGENT_HTTPS'] != 'ON')) {
//
//        // Name transaction "redirect" in New Relic for improved reporting (optional)
//        if (extension_loaded('newrelic')) {
//            newrelic_name_transaction("redirect");
//        }
//
//        header('HTTP/1.1 301 Moved Permanently');
//        header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//        exit();
//    }
//}


/*
 * The following configuration will redirect HTTP to HTTPS and enforce use of a
 * primary domain.
 *
 * If a site is being proxied then the hostname will be different from the name
 * registered with the IdP for shib to work.
 * 
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  // Redirect to https://$primary_domain in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    /* Replace www.example.com with your registered domain name    */
    /* Proxied sites should use pan-sitename or something similar  */ 
    $primary_domain = 'pan-site.sas.upenn.edu';
  }
  else {
    // Redirect to HTTPS on every Pantheon environment.
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
    header('Location: https://'. $primary_domain . $_SERVER['REQUEST_URI']);
    exit();
  }
}


/*
 * settings.redirects-site.php sets
 *  $ReWriteMap
 */

/**
 * Implement RewriteMap code
 *
 * TODO: - why/where would be run this using the php command?
 *
 * What's the purpose in checking for $_SERVER['argv'][1] ?
 * What's the purpose in checking for $_SERVER['REQUEST_URI'] ?
 *
 * Current logic below, both sides of the and must be true
 *
 * if Rewritemap is set and (argv is set or request uri is set)
 *   then do stuff
 */
if (isset($RewriteMap) && (isset($_SERVER['argv'][1]) || isset($_SERVER['REQUEST_URI']))) {
    // run as:
    // php settings.redirects-allsites.php /uniconn

    $oldurl = (php_sapi_name() == "cli") ? $_SERVER['argv'][1] : $_SERVER['REQUEST_URI'];

    foreach ($RewriteMap as $key => $value) {
        if (preg_match($key, $oldurl)) {
            $newurl = preg_replace($key, $value, $oldurl);
            if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
                if (extension_loaded('newrelic')) {
		            newrelic_name_transaction("redirect");
	            }
                header('HTTP/1.2 301 Moved Permanently');
	            header('Location: '. $newurl);
            }
            else {
                print("$oldurl => $newurl\n");
            }
            exit();
        }
    }
}

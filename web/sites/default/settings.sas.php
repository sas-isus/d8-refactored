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
 */
if (isset($_SERVER['PANTHEON_ENVIRONMENT']) && ($_SERVER['HTTPS'] === 'OFF') && (php_sapi_name() != "cli")) {
    if (!isset($_SERVER['HTTP_USER_AGENT_HTTPS']) || (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) && $_SERVER['HTTP_USER_AGENT_HTTPS'] != 'ON')) {

        // Name transaction "redirect" in New Relic for improved reporting (optional)
        if (extension_loaded('newrelic')) {
            newrelic_name_transaction("redirect");
        }

        header('HTTP/1.1 301 Moved Permanently');
        header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
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

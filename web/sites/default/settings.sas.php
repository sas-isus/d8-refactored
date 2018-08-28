<?php

/*
 * settings.sas.php
 *
 * Include settings that are used across all sites
 *
 * NOTE: We could just drop this inside of settings.local.php and call it a day
 * TODO: should we just add to settings.local ???
 */


/**
 * Set simplesamlphp library directory
 */
if (isset($_ENV['HOME'])) {
    $settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/web/private/simplesamlphp';
}

/**
 * Implement RewriteMap code
 *
 * TODO: - why/where would be run this using the php command?
 *
 * What's the purpose in checking for $_SERVER['argv'][1] ?
 * What's the purpose in checking for $_SERVER['REQUEST_URI'] ?
 *
 * Current logic below, both sides of the && must be true..
 *   if Rewritemap is set and (argv is set or request uri is set)
 *     then do stuff
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

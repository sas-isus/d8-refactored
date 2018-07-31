<?php
/*
 * settings.redirects-site.php sets
 *  $ReWriteMap
 */


/**
 * Implement RewriteMap code
 *
 * TODO: - why/where would be run this using the php command?
 * TODO: - has this been tested?
 *
 * What's the purpose in checking for $_SERVER['argv'][1] ?
 * What's the purpose in checking for $_SERVER['REQUEST_URI'] ?
 */
if (isset($RewriteMap) && (isset($_SERVER['argv'][1]) || isset($_SERVER['REQUEST_URI']))) {
    // run as:
    // php settings.redirects-allsites.php /uniconn

    $oldurl = (php_sapi_name() == "cli") ? $_SERVER['argv'][1] : $_SERVER['REQUEST_URI'];

    foreach ($RewriteMap as $key => $value) {
        if (preg_match($key, $oldurl)) {
            $newurl = preg_replace($key, $value, $oldurl);
            if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
		        redirectTo($newurl);
            }
            else {
                print("$oldurl => $newurl\n");
            }
            exit();
        }
    }
}

<?php

/*
 * NOTE: Don't put any custom code in settings.php
 * - settings.php only includes this file so we must use it
 *
 * NEW SITES:
 * - rename this file to settings.local.php
 * - modify any settings and hostnames below (SEE COMMENTS BELOW)
 */

/**
 * TODO: VERIFY THIS REQUIREMENT
 * If the site will be proxied by our proxy server then
 *   uncomment the following settings['...'] lines
 */
//$settings['reverse_proxy'] = TRUE;
//$settings['reverse_proxy_addresses'] = array('128.91.219.96');

/*
 * Include SAS specific settings (for all sites)
 *
 * NOTE: We could just drop this inside of settings.local.php and call it a day
 */
//if (file_exists(__DIR__ . "/settings.sas.php")) {
//    include __DIR__ . "/settings.sas.php";
//}

/**
 * Set simplesamlphp library directory
 */
if (isset($_ENV['HOME'])) {
    $settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/web/private/simplesamlphp';
}

/******************************************************************************
 * Site specific settings should be included below
 * YOU MUST UPDATE THE FOLLOWING:
 * - the is_proxied variable
 * - the hostname returned in setCanonicalHost()
 * - the primary_domain variable
 */

global $is_proxied = TRUE;

/*
 * THIS IS ONLY USED BY/FOR SHIB FOR THE LIVE environment
 * Set canonical_host for shib if we're on the live environment.
 * If the live site is being proxied then we need this set
 * If the live site is NOT being proxied then we're fine.
 */
setCanonicalHost() {
    if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
        // In the Live environment return the hostname registered with the IdP
        if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
            /** Replace www.example.com with your registered domain name */
            return 'site.sas.upenn.edu';
        }
    }
}

/**
 * UPDATE PRIMARY DOMAIN below
 *
 * The following configuration will redirect HTTP to HTTPS and enforce use of a
 * primary domain.
 *
 * If a site is being proxied then the hostname will be different from the name
 * registered with the IdP for shib to work.
 *
 */
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
    // Redirect to https://$primary_domain in the Live environment
    if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
        /**
         * Replace primary_domain with your registered domain name
         * If being proxied use the pan-site domain name
         */
        $primary_domain = 'PAN-SITE.sas.upenn.edu';
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

/******************************************************************************
 * Site specific redirects.
 * This must be set before sas_settings is processed.
 * Including yet another file just adds cruft
 */

/**
 * Uncomment when site specific redirects are required
 */
// global $RewriteMap;
// $RewriteMap = array('@^/foo/bar.htm$@'        => '/node/1',
//                     '@^/foo/index.html$@'     => '/node/1',
// );
//

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

<?php

/******************************************************************************
 * NOTES:
 * Don't put any custom code in settings.php
 * - settings.php only includes this file so we must use it
 * Order of variable settings and includes matters!!!
 *
 * NEW SITES: site specific settings should be included below
 *
 * YOU MUST UPDATE THE FOLLOWING:
 * - RENAME this file to settings.local.php
 * - SET $pan_domain in getCanonicalHost()
 * - SET $primary_domain (around line 100)
 * 
 *****************************************************************************/

/**
 * TODO: VERIFY THIS REQUIREMENT
 * If the site will be proxied by our proxy server then
 *   uncomment the following settings['...'] lines
 */
//$settings['reverse_proxy'] = TRUE;
//$settings['reverse_proxy_addresses'] = array('128.91.219.96');


/**
 * Set simplesamlphp library directory
 */
echo "settings.local: setting simplesamlphp_dir<br>";
if (isset($_ENV['HOME'])) {
    $settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/web/private/simplesamlphp';
}


/**
 * Place the config directory outside of the Drupal root.
 * This overrides the setting in settings.pantheon.php
 */
$config_directories = array(
    CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config',
);


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

if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
    // Redirect to https://$primary_domain in the Live environment
    if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
        /**
         * Replace primary_domain with your registered domain name
         * If being proxied use the pan-site domain name
         * If NOT being proxied use the appropriate domain name
         */
        $primary_domain = 'CHANGE.ME.upenn.edu';
    }
    else {
        echo "settings.local: setting primary domain to " . $_SERVER['HTTP_HOST'] . "<br>";
        $primary_domain = $_SERVER['HTTP_HOST'];
    }

    // This should be refactored
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


/**
 * THIS IS ONLY USED BY/FOR SHIB FOR THE LIVE and proxied environment
 * Set canonical_host for shib if we're on the live environment.
 * If the live site is being proxied then we need this set
 * If the live site is NOT being proxied then we're fine.
 *
 */
function isPantheonSite () {
    return(preg_match('@pantheonsite.io@',$_SERVER['HTTP_HOST']));
}

function isProxied() {
    // HTTP_HOST == HTTP_X_FORWARDED_HOST for direct access to pantheon + via CDN
    // via proxy, HTTP_HOST -> 'live-sas-school.pantheonsite.io' while
    //   HTTP_X_FORWARDED_HOST -> 'www.sas.upenn.edu, live-sas-school.pantheonsite.io'
    if (isset($SERVER['HTTP_X_FORWARDED_HOST'])) {
        return 1;
    } else {
        return 0;
    }

}

function getCanonicalHost() {
    global $primary_domain;
    
    $CanonicalHost = $_SERVER['HTTP_HOST']; 
    
    if ((isset($primary_domain)) && (isProxied())) {  
        $CanonicalHost = $primary_domain;
    }

    return $CanonicalHost;
}


/*****************************************************************************
 * SITE SPECIFIC REDIRECTS
 * 
 * RewriteMap example
 * Uncomment the section below to enable site specific redirects
 */

 // $RewriteMap;
 // $RewriteMap = array('@^/foo/bar.htm$@'        => '/node/1',
 //                     '@^/foo/index.html$@'     => '/node/1',
 //                    );

// NOTE: The above $RewriteMap must be enabled above in order for the code below to run.
// TODO: Can we test this?
if (isset($RewriteMap) 
    && (isset($_SERVER['argv'][1]) || isset($_SERVER['REQUEST_URI']))) {
    // run as:
    // php settings.redirects-allsites.php /uniconn

    $oldurl = (php_sapi_name() == "cli") ? $_SERVER['argv'][1] : $_SERVER['REQUEST_URI'];

    foreach ($RewriteMap as $key => $value) {
        if (preg_match($key, $oldurl)) {
            $newurl = preg_replace($key,$value,$oldurl);
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
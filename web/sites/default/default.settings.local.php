<?php

/**
 * Include site specific 'data'
 * 
 * New sites must copy default.settings.site.php to settings.site.php and edit.
 */
$site_data = __DIR__ . "/settings.site.php";
if (file_exists($site_data)) {
    require_once('settings.site.php');
}


/**
 * Set simplesamlphp library directory
 */
//echo "settings.local: setting simplesamlphp_dir<br>";
if (isset($_ENV['HOME'])) {
    $settings['simplesamlphp_dir'] = $_ENV['HOME'] . '/code/web/private/simplesamlphp';
}


/**
 * The following configuration will redirect HTTP to HTTPS and enforce use of a
 * primary domain.
 *
 * https://pantheon.io/docs/domains/
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
    // primary_domain is already set so this code has been refactored.
    if (! $_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
        //echo "settings.local: setting primary domain to " . $_SERVER['HTTP_HOST'] . "<br>";
        $primary_domain = $_SERVER['HTTP_HOST'];
    }

    /** 
     * It's a best practice for SEO and security to standardize all traffic on
     * HTTPS and choose a primary domain.
     */
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
    if ($_SERVER['HTTP_HOST'] != $_SERVER['HTTP_X_FORWARDED_HOST']) {
        return 1;
    } else {
        return 0;
    }

}

/**
 * This function is only called from config.php for use with shib.
 */
function getCanonicalHost() {
    global $proxied_domain;
    // This hadn't been set anywhere yet and is only called from config.php for shib
    // In the if block below this won't be set therefore the if block will never
    // evaluate as true and CanonicalHost will always be set to HTTP_HOST.
    // This needs to be refactored. 
    // - removing the check to see if primary domain is set
    // All we should be concerned about is whether or not this site is being proxied
    $CanonicalHost = $_SERVER['HTTP_HOST'];
    
    if (isProxied()) {
        $CanonicalHost = $proxied_domain;
    }

    return $CanonicalHost;
}


/*****************************************************************************
 * SITE SPECIFIC REDIRECTS
 * 
 * RewriteMap example
 * Uncomment the section below to enable site specific redirects
 */

// NOTE: The above $RewriteMap must be enabled above in order for the code below to run.
if (isset($RewriteMap) 
    && (isset($_SERVER['argv'][1]) || isset($_SERVER['REQUEST_URI']))) {
    // run as:
    // php settings.redirects-allsites.php /uniconn

    $oldurl = (php_sapi_name() == "cli") ? $_SERVER['argv'][1] : $_SERVER['REQUEST_URI'];

    foreach ($RewriteMap as $key => $value) {
        if (preg_match($key, $oldurl)) {
            $newurl = preg_replace($key,$value,$oldurl);
            if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
                if (extension_loaded('newrelic')) {
                    newrelic_name_transaction("redirect");
                }
            
                header('HTTP/1.0 301 Moved Permanently');
                header('Location: '. $newurl);
            }
            else {
                print("$oldurl => $newurl\n");
            }

            exit();
        }
    }
}

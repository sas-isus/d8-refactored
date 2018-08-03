<?php

/*
 * Don't put any custom code in settings.php
 * - settings.php only includes this file so we must use it
 *
 * New sites:
 * - rename this file to settings.local.php
 * - modify any settings below, e.g. reverse_proxy
 */

/**
 * If the site will be proxied by our proxy server then
 *   uncomment the following settings['...'] lines
 */
//$settings['reverse_proxy'] = TRUE;
//$settings['reverse_proxy_addresses'] = array('128.91.219.96');


/**
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

/*
 * Include SAS specific settings (for all sites)
 * - this is how settings.php does it so following suit
 */
$sas_settings = __DIR__ . "/settings.sas.php";
if (file_exists($sas_settings)) {
    include $sas_settings;
}


/******************************************************************************
 * Site specific settings should be included below
 */

/*
 * Set canonical_host for shib if we're on the live environment.
 * If the live site is being proxied then we need this set
 * If the live site is NOT being proxied then we're fine, just set it
 */
setCanonicalHost() {
    if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
        // Redirect to https://$primary_domain in the Live environment
        if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
            /** Replace www.example.com with your registered domain name */
            $canonical_host = 'site.sas.upenn.edu';
        }
    }
}


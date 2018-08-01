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
 * Set canonical_host for shib. We can do this here and not worry about whether
 * or not this site is being proxied by SAS or not. Just set it and forget it.
 */
setCanonicalHost() {
    // just return what's being asked for
    $canonical_host = 'www.site.upenn.edu';
    return $canonical_host;
}


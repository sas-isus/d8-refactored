<?php

/**
 * Rename to settings.site.php
 *
 * Site specific 'data'.
 */

/** 
 * If a pantheon site is being proxied then primary_domain and proxied_domain
 * need to be set to the appropriate CNAMES. For example
 * PAN-SCHOOL.SAS.upenn.edu. 30	IN	CNAME	live-sas-school.pantheonsite.io.
 * WWW.SAS.UPENN.EDU.	      300	IN	CNAME	proxy1.sas.upenn.edu.
 * 
 * If a site is NOT be proxied then set both of these to the same value, e.g.
 * www.site.upenn.edu
 */
global $primary_domain;
$primary_domain = 'pan-site.sas.upenn.edu';

global $proxied_domain;
$proxied_domain = 'site.sas.upenn.edu';


if (isset($_SERVER['HTTP_HOST'])) {
    echo "_SERVER['HTTP_HOST]': " . $_SERVER['HTTP_HOST'] . "<br>";
}

if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    echo "_SERVER['HTTP_X_FORWARDED_HOST']: " . $_SERVER['HTTP_X_FORWARDED_HOST'] . "<br>";
}


/**
 * RewriteMap settings
 */
 
// global $RewriteMap = array('@^/foo/bar.htm$@'        => '/node/1',
//                     '@^/foo/index.html$@'     => '/node/1',
//                    );



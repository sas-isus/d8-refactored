<?php
// This file is for Bitbucket pipelines.

$file = 'composer.json';
$data = json_decode(file_get_contents($file), true);
$data["autoload-dev"]["psr-4"] = array("Drupal\\Tests\\permissions_by_term\\Behat\\Context\\" => "web/modules/permissions_by_term/tests/src/Behat/Context");
file_put_contents('composer.json', json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

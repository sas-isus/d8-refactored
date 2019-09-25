
# GatherContent REST client

[![Build Status](https://travis-ci.org/Cheppers/gathercontent-client.svg?branch=master)](https://travis-ci.org/Cheppers/gathercontent-client)
[![codecov](https://codecov.io/gh/Cheppers/gathercontent-client/branch/master/graph/badge.svg)](https://codecov.io/gh/Cheppers/gathercontent-client)

Compatible with `application/vnd.gathercontent.v0.5+json`


## Supported endpoints

- [GET:  /me](https://docs.gathercontent.com/reference#get-me) `$gc->meGet()`
- [GET:  /accounts](https://docs.gathercontent.com/reference#get-accounts) `$gc->accountsGet()`
- [GET:  /accounts/:account_id](https://docs.gathercontent.com/reference#get-accountsaccount_id) `$gc->accountGet()`
- [GET:  /projects](https://docs.gathercontent.com/reference#get-projects) `$gc->projectsGet()`
- [GET:  /projects/:project_id](https://docs.gathercontent.com/reference#get-project-by-id) `$gc->projectGet()`
- [POST: /projects](https://docs.gathercontent.com/reference#post-projects) `$gc->projectsPost()`
- [GET:  /projects/:project_id/statuses](https://docs.gathercontent.com/reference#get-project-statuses) `$gc->projectStatusesGet()`
- [GET:  /projects/:project_id/statuses/:status_id](https://docs.gathercontent.com/reference#get-project-statuses-by-id) `$gc->projectStatusGet()`
- [GET:  /items](https://docs.gathercontent.com/reference#get-items) `$gc->itemsGet()`
- [GET:  /items/:item_id](https://docs.gathercontent.com/reference#get-items-by-id) `$gc->itemGet()`
- [POST: /items](https://docs.gathercontent.com/reference#post-items) `$gc->itemsPost()`
- [POST: /items/:item_id/save](https://docs.gathercontent.com/reference#post-item-save) `$gc->itemSavePost()`
- [POST: /items/:item_id/apply_template](https://docs.gathercontent.com/reference#post-item-apply_template) `$gc->itemApplyTemplatePost()`
- [POST: /items/:item_id/choose_status](https://docs.gathercontent.com/reference#post-item-choose_status) `$gc->itemChooseStatusPost()`
- [GET:  /items/:item_id/files](https://docs.gathercontent.com/reference#get-item-files) `$gc->itemFilesGet()`
- [GET:  /templates](https://docs.gathercontent.com/reference#get-templates) `$gc->templatesGet()`
- [GET:  /templates/:template_id](https://docs.gathercontent.com/reference#get-template-by-id) `$gc->templateGet()`
- [GET:  /folders](https://docs.gathercontent.com/reference#get-folders) `$gc->foldersGet()`


## Basic usage

To create the GatherContentClient simply pass in a Guzzle client in the constructor.

You will need:

- your e-mail address to log into GatherContent
- your [API key](https://docs.gathercontent.com/reference#authentication) from GatherContent

```php
<?php
$email = 'YOUR_GATHERCONTENT_EMAIL';
$apiKey = 'YOUR_GATHERCONTENT_API_KEY';
$client = new \GuzzleHttp\Client();
$gc = new \Cheppers\GatherContent\GatherContentClient($client);
$gc
  ->setEmail($email)
  ->setApiKey($apiKey);

try {
    $me = $gc->meGet();
}
catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    
    exit(1);
}
echo "Email = {$me->email}" . PHP_EOL;
echo "First name = {$me->firstName}" . PHP_EOL;
echo "Last name = {$me->lastName}" . PHP_EOL;
```

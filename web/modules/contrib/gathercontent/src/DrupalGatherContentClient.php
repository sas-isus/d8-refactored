<?php

namespace Drupal\gathercontent;

use Cheppers\GatherContent\GatherContentClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;

/**
 * Extends the GatherContentClient class with Drupal specific functionality.
 */
class DrupalGatherContentClient extends GatherContentClient {

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $client) {
    parent::__construct($client);
    $this->setCredentials();
  }

  /**
   * Put the authentication config into client.
   */
  public function setCredentials() {
    $config = \Drupal::config('gathercontent.settings');
    $this->setEmail($config->get('gathercontent_username') ?: '');
    $this->setApiKey($config->get('gathercontent_api_key') ?: '');
  }

  /**
   * Retrieve the account id of the given account.
   *
   * If none given, retrieve the first account by default.
   */
  public static function getAccountId($account_name = NULL) {
    $account = \Drupal::config('gathercontent.settings')
      ->get('gathercontent_account');
    $account = unserialize($account);
    if (!is_array($account)) {
      return NULL;
    }

    if (!$account_name) {
      if (reset($account)) {
        return key($account);
      }
    }

    foreach ($account as $id => $name) {
      if ($name === $account_name) {
        return $id;
      }
    }

    return NULL;
  }

  /**
   * Retrieve all the active projects.
   */
  public function getActiveProjects($account_id) {
    $projects = $this->projectsGet($account_id);

    foreach ($projects as $id => $project) {
      if (!$project->active) {
        unset($projects[$id]);
      }
    }

    return $projects;
  }

  /**
   * Returns a formatted array with the template ID's as a key.
   *
   * @param int $project_id
   *   Project ID.
   *
   * @return array
   *   Return array.
   */
  public function getTemplatesOptionArray($project_id) {
    $formatted = [];
    $templates = $this->templatesGet($project_id);

    foreach ($templates as $id => $template) {
      $formatted[$id] = $template->name;
    }

    return $formatted;
  }

  /**
   * Returns the response body.
   *
   * @param bool $json_decoded
   *   If TRUE the method will return the body json_decoded.
   *
   * @return \Psr\Http\Message\StreamInterface
   *   Response body.
   */
  public function getBody($json_decoded = FALSE) {
    $body = $this->getResponse()->getBody();

    if ($json_decoded) {
      return \GuzzleHttp\json_decode($body);
    }

    return $body;
  }

  /**
   * Downloads all files asynchronously.
   *
   * @param array $files
   *   Files object array.
   * @param string $directory
   *   Destination directory.
   * @param string $language
   *   Language string.
   *
   * @return array
   *   Imported files array.
   */
  public function downloadFiles(array $files, $directory, $language) {
    /** @var \GuzzleHttp\Client $httpClient */
    $httpClient = $this->client;
    $files = array_values($files);
    $importedFiles = [];

    $requests = function () use ($httpClient, $files) {
      foreach ($files as $file) {
        $url = $file->url;

        yield function () use ($httpClient, $url) {
          return $httpClient->getAsync($url);
        };
      }
    };

    $pool = new Pool(
      $httpClient,
      $requests(),
      [
        'fulfilled' => function ($response, $index) use ($files, $directory, $language, &$importedFiles) {
          if ($response->getStatusCode() === 200) {
            $path = $directory . '/' . $files[$index]->fileName;

            $importedFile = file_save_data($response->getBody(), $path);

            if ($importedFile) {
              $importedFile
                ->set('gc_id', $files[$index]->id)
                ->set('langcode', $language)
                ->set('filesize', $files[$index]->size)
                ->save();

              $importedFiles[$index] = $importedFile->id();
            }
          }
        },
      ]
    );

    $promise = $pool->promise();
    $promise->wait();

    ksort($importedFiles);
    return $importedFiles;
  }

}

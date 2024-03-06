<?php

/**
 * @file
 * Contains site specific overrides.
 */

$additionalEnvVars = [
  'AZURE_BLOB_STORAGE_SAS_TOKEN|BLOBSTORAGE_SAS_TOKEN',
  'AZURE_BLOB_STORAGE_NAME',
  'AZURE_BLOB_STORAGE_CONTAINER',
  'DRUPAL_VARNISH_HOST',
  'DRUPAL_VARNISH_PORT',
  'PROJECT_NAME',
  'DRUPAL_API_ACCOUNTS',
  'DRUPAL_VAULT_ACCOUNTS',
  'REDIS_HOST',
  'REDIS_PORT',
  'REDIS_PASSWORD',
];
foreach ($additionalEnvVars as $var) {
  $preflight_checks['environmentVariables'][] = $var;
}

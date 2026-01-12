<?php

return [
  'debug' => true,
  'api' => [
    'basicAuth' => false,        // Disable API auth for local dev
    'allowInsecure' => true      // Allow HTTP
  ],
  'kql' => [
    'auth' => false,             // Allow KQL without login
    'intercept' => function ($type, $key, $value) {
      return true;  // Allow all queries in dev
    }
  ],
  'plugins' => [
    'tobimori/seo' => false,
  ],
];

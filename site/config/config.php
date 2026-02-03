<?php

return [
  'debug' => true,
  'api' => [
    'basicAuth' => false,        // Disable API auth for local dev
    'allowInsecure' => true      // Allow HTTP
  ],
  'kql' => [
    'auth' => false,             // Allow KQL without login
    // Autoriser les méthodes personnalisées des page models
    'methods' => [
      'Kirby\Cms\Page' => [
        'layoutWithResolvedFiles'
      ]
    ]
  ],
  'plugins' => [
    'tobimori/seo' => false,
  ],
];

<?php

return [
  'debug' => true,
  'api' => [
    'basicAuth' => false,        // Disable API auth for local dev
    'allowInsecure' => true      // Allow HTTP
  ],
  'kql' => [
    'auth' => false,             // Allow KQL without login
    'methods' => [
      // Liste des méthodes explicitement autorisées en KQL
      // Format : 'namespace\class::method' (insensible à la casse)
      // Note: les page models custom (LieuPage, etc.) sont aussi couverts
      // grâce au @kql-allowed docblock sur les closures du plugin.
      'allowed' => [
        'kirby\cms\file::historiaimage',
        'kirby\cms\page::responsiveimage',
        // Page models custom (sans namespace → nom de classe brut)
        'lieupage::responsiveimage',
      ]
    ]
  ],
  'plugins' => [
    'tobimori/seo' => false,
  ],

  // ─── Presets images responsive (plugin historia/images) ─────────
  // Chaque preset définit : max (largeur max en px), widths (breakpoints srcset),
  // fallbackQuality (jpeg/png), webpQuality, avifQuality, defaultSizes (attribut sizes).
  // On ne sert JAMAIS le fichier original (souvent 5–12 Mo) au frontend.
  'historia.images.presets' => [
    'cover' => [
      'max'              => 2500,
      'widths'           => [640, 960, 1280, 1600, 2000, 2500],
      'fallbackQuality'  => 82,
      'webpQuality'      => 75,
      'defaultSizes'     => '(min-width: 2500px) 2500px, 100vw',
    ],
    'podcast' => [
      'max'              => 480,
      'widths'           => [240, 480],
      'fallbackQuality'  => 82,
      'webpQuality'      => 75,
      'defaultSizes'     => '240px',
    ],
    'column' => [
      'max'              => 800,
      'widths'           => [320, 480, 640, 800],
      'fallbackQuality'  => 82,
      'webpQuality'      => 75,
      'defaultSizes'     => '(min-width: 1024px) 800px, 100vw',
    ],
  ],
];

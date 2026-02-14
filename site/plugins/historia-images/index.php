<?php

/**
 * Plugin Historia Images
 * 
 * Système d'images responsive : génère des variantes optimisées (fallback JPEG/PNG + WebP)
 * pour chaque preset d'image. Ne retourne JAMAIS l'URL originale du fichier brut
 * (souvent 5–12 Mo) ; toujours un thumb au format max du preset.
 *
 * Presets configurables dans config.php via `historia.images.presets.*`
 *
 * Usage KQL :  page.responsiveImage("cover", "cover")
 * Usage PHP :  $file->historiaImage('cover')
 */

// ─── Constantes par défaut des presets ──────────────────────────────
const HISTORIA_IMAGE_DEFAULTS = [
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
];


Kirby::plugin('historia/images', [

    'fileMethods' => [

        // Génère la structure responsive d'une image pour un preset donné.
        // @param string $preset  Nom du preset : 'cover', 'podcast', 'column'
        // @param array  $overrides  Surcharges ponctuelles (ex: ['defaultSizes' => '50vw'])
        // @return array  Structure JSON-ready pour le frontend <picture>
        'historiaImage' => /** @kql-allowed */ function (string $preset = 'column', array $overrides = []): array {

            // ── 1. Lire la config du preset ─────────────────────────────
            $config = array_merge(
                HISTORIA_IMAGE_DEFAULTS[$preset] ?? HISTORIA_IMAGE_DEFAULTS['column'],
                kirby()->option("historia.images.presets.{$preset}", []),
                $overrides
            );

            $maxWidth         = (int) $config['max'];
            $widths           = (array) $config['widths'];
            $fallbackQuality  = (int) $config['fallbackQuality'];
            $webpQuality      = (int) $config['webpQuality'];
            $defaultSizes     = (string) $config['defaultSizes'];

            // ── 2. Métadonnées de base ──────────────────────────────────
            $alt            = $this->alt()->value() ?? '';
            $originalWidth  = $this->width();
            $originalHeight = $this->height();
            $mime           = $this->mime();

            // ── 3. Fichiers non redimensionnables (SVG, GIF, PDF) ───────
            $nonResizable = ['image/svg+xml', 'image/gif', 'application/pdf'];

            if (in_array($mime, $nonResizable)) {
                return [
                    'alt'      => $alt,
                    'sizes'    => $defaultSizes,
                    'original' => [
                        'width'  => $originalWidth,
                        'height' => $originalHeight,
                        'mime'   => $mime,
                    ],
                    'fallback' => [
                        'src'    => $this->url(),
                        'width'  => $originalWidth,
                        'height' => $originalHeight,
                    ],
                ];
            }

            // ── 4. Filtrer les largeurs > original ──────────────────────
            $widths = array_values(array_filter($widths, fn($w) => $w <= $originalWidth));

            // Si l'image est plus petite que tous les breakpoints,
            // on utilise sa largeur native comme unique variante
            if (empty($widths)) {
                $widths = [$originalWidth];
            }

            // Toujours trier
            sort($widths);

            // Largeur du fallback = plus grande variante disponible
            $fallbackWidth = max($widths);

            // ── 5. Thumb fallback (JPEG/PNG selon l'original) ───────────
            $fallbackThumb = $this->thumb([
                'width'   => $fallbackWidth,
                'quality' => $fallbackQuality,
            ]);

            // Calculer la hauteur proportionnelle
            $ratio          = $originalWidth > 0 ? $originalHeight / $originalWidth : 1;
            $fallbackHeight = (int) round($fallbackWidth * $ratio);

            // ── 6. Fallback srcset ──────────────────────────────────────
            $fallbackSrcset = [];
            foreach ($widths as $w) {
                $thumb = $this->thumb([
                    'width'   => $w,
                    'quality' => $fallbackQuality,
                ]);
                $fallbackSrcset[] = $thumb->url() . ' ' . $w . 'w';
            }

            // ── 7. WebP srcset ──────────────────────────────────────────
            $webpSrcset = [];
            foreach ($widths as $w) {
                $thumb = $this->thumb([
                    'width'   => $w,
                    'format'  => 'webp',
                    'quality' => $webpQuality,
                ]);
                $webpSrcset[] = $thumb->url() . ' ' . $w . 'w';
            }

            // ── 8. Construire le résultat (JPEG/PNG + WebP, pas d'AVIF) ──
            return [
                'alt'      => $alt,
                'sizes'    => $defaultSizes,
                'original' => [
                    'width'  => $originalWidth,
                    'height' => $originalHeight,
                    'mime'   => $mime,
                ],
                'fallback' => [
                    'src'    => $fallbackThumb->url(),
                    'srcset' => implode(', ', $fallbackSrcset),
                    'width'  => $fallbackWidth,
                    'height' => $fallbackHeight,
                ],
                'webp' => [
                    'srcset' => implode(', ', $webpSrcset),
                ],
            ];
        },
    ],

    // ─── Méthode sur les pages : wrapper null-safe pour KQL ─────────
    'pageMethods' => [

        // Retourne l'image responsive d'un champ fichier de la page.
        // Gère le cas où le fichier n'existe pas (retourne null au lieu de casser).
        // Usage KQL : page.responsiveImage("cover", "cover")
        //             page.responsiveImage("imagepodcast", "podcast")
        'responsiveImage' => /** @kql-allowed */ function (string $field, string $preset = 'column'): ?array {
            $file = $this->content()->get($field)->toFile();
            return $file ? $file->historiaImage($preset) : null;
        },
    ],
]);

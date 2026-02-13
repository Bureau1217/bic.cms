<?php

/**
 * Page model pour les pages "lieu"
 * Ajoute une méthode pour récupérer le layout avec les fichiers résolus
 */
class LieuPage extends Page
{
    /**
     * Retourne le layout avec les URLs des fichiers résolues
     * Utilisable dans KQL: page.layoutWithResolvedFiles
     * 
     * @kql-allowed
     */
    public function layoutWithResolvedFiles(): array
    {
        $layouts = $this->layout()->toLayouts();
        $result = [];

        foreach ($layouts as $layout) {
            $row = [
                'id' => $layout->id(),
                'columns' => []
            ];

            foreach ($layout->columns() as $column) {
                $col = [
                    'id' => $column->id(),
                    'width' => $column->width(),
                    'blocks' => []
                ];

                foreach ($column->blocks() as $block) {
                    $blockData = [
                        'id' => $block->id(),
                        'type' => $block->type(),
                        'isHidden' => $block->isHidden(),
                        'content' => []
                    ];

                    // Traiter selon le type de bloc
                    switch ($block->type()) {
                        case 'heading':
                            $blockData['content'] = [
                                'level' => $block->level()->value(),
                                'text' => $block->text()->value()
                            ];
                            break;

                        case 'text':
                            $blockData['content'] = [
                                'text' => $block->text()->toBlocks()->toHtml()
                            ];
                            break;

                        case 'image':
                            $image = $block->image()->toFile();
                            $blockData['content'] = [
                                'image' => $image ? [
                                    'url' => $image->url(),
                                    'alt' => $block->alt()->value() ?: $image->alt()->value(),
                                    'width' => $image->width(),
                                    'height' => $image->height()
                                ] : null,
                                'caption' => $block->caption()->value(),
                                'alt' => $block->alt()->value()
                            ];
                            break;

                        case 'gallery':
                            $images = [];
                            foreach ($block->images()->toFiles() as $img) {
                                $images[] = [
                                    'url' => $img->url(),
                                    'alt' => $img->alt()->value(),
                                    'width' => $img->width(),
                                    'height' => $img->height()
                                ];
                            }
                            $blockData['content'] = [
                                'images' => $images,
                                'caption' => $block->caption()->value()
                            ];
                            break;

                        case 'quote':
                            $blockData['content'] = [
                                'text' => $block->text()->value(),
                                'citation' => $block->citation()->value()
                            ];
                            break;

                        default:
                            // Pour les autres types de blocs, on garde le contenu brut
                            $blockData['content'] = $block->content()->toArray();
                            break;
                    }

                    $col['blocks'][] = $blockData;
                }

                $row['columns'][] = $col;
            }

            $result[] = $row;
        }

        return $result;
    }
}

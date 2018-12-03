<?php # -*- coding: utf-8 -*-
declare(strict_types=1);

/*
 * This file is part of the twig-wordpress-light-example package.
 *
 * (c) Guido Scialfa <dev@guidoscialfa.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TwigWordPressLightExample\Model;

use WordPressModel\Model;

final class PostThumbnail implements Model
{
    const FILTER_DATA = 'twigwordpresslightexample.attachment_image';
    const FILTER_ALT = 'twigwordpresslightexample.attachment_image_alt';

    /**
     * @var
     */
    private $attachmentSize;

    /**
     * @var int
     */
    private $attachmentId;

    /**
     *
     * @param int $attachmentId
     * @param mixed $attachmentSize
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function __construct(int $attachmentId, $attachmentSize = 'thumbnail')
    {
        // phpcs:enable

        $this->attachmentId = $attachmentId;
        $this->attachmentSize = $attachmentSize;
    }

    /**
     * @return array
     */
    public function data(): array
    {
        $imageSource = $this->attachmentSource();

        /**
         * Figure Image Data
         *
         * @param array $data The data arguments for the template.
         */
        return apply_filters(self::FILTER_DATA, [
            'image' => [
                'attributes' => [
                    'url' => $imageSource->src,
                    'class' => 'thumbnail',
                    'alt' => $this->alt(),
                    'width' => $imageSource->width,
                    'height' => $imageSource->height,
                ],
            ],
        ]);
    }

    /**
     * @return \stdClass
     *
     * @throws \InvalidArgumentException If the attachment isn't an image.
     */
    private function attachmentSource(): \stdClass
    {
        if (!wp_attachment_is_image($this->attachmentId)) {
            throw new \InvalidArgumentException('Attachment must be an image.');
        }

        $imageSource = wp_get_attachment_image_src(
            $this->attachmentId,
            $this->attachmentSize
        );

        if (!$imageSource) {
            return (object)[
                'src' => '',
                'width' => '',
                'height' => '',
                'icon' => false,
            ];
        }

        $imageSource = array_combine(
            ['src', 'width', 'height', 'icon'],
            $imageSource
        );

        return (object)$imageSource;
    }

    /**
     * @return string
     */
    private function alt(): string
    {
        $alt = get_post_meta($this->attachmentId, '_wp_attachment_image_alt', true);

        /**
         * Filter Alt Attribute Value
         *
         * @param string $alt The alternative text.
         * @param int $attachmentId The id of the attachment from which the alt text is retrieved.
         */
        $alt = apply_filters(self::FILTER_ALT, $alt, $this->attachmentId);

        return (string)$alt;
    }
}

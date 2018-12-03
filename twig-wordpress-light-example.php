<?php # -*- coding: utf-8 -*-

/**
 * Plugin Name: Twig WordPress Light Example
 * Author: Guido Scialfa
 * Author URI: guidoscialfa.com
 * Description: A light example of Twig for WordPress
 * Version: 0.1
 * Text Domain: twig-wordpress-light-example
 */

/*
 * This file is part of the twig-wordpress-light-example package.
 *
 * (c) Guido Scialfa <dev@guidoscialfa.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace TwigWordPressLightExample;

use Twig\Loader\FilesystemLoader;
use TwigWordPressLightExample\Model;
use TwigWordPressView\TwigController;
use TwigWordPressView\TwigData;
use TwigWp\Factory;

add_action('plugins_loaded', function () {

    function adminNotice(string $message, string $noticeType, array $allowedMarkup = []): void
    {
        add_action('admin_notices', function () use ($message, $noticeType, $allowedMarkup) {
            ?>
            <div class="notice notice-<?= esc_attr($noticeType) ?>">
                <p><?= wp_kses($message, $allowedMarkup) ?></p>
            </div>
            <?php
        });
    }

    if (is_admin()) {
        return;
    }

    if (!\file_exists(__DIR__ . '/vendor/autoload.php')) {
        adminNotice(
            esc_html__(
                'Twig WordPress Light Example: Autoloader not found, please read the README file to know how to create one.',
                'twig-wordpress-light-example'
            ),
            'error'
        );
        return;
    }

    require_once __DIR__ . '/vendor/autoload.php';

    $twig = new Factory(new FilesystemLoader(__DIR__ . '/views/'), []);
    $twigController = new TwigController($twig->create());

    add_filter('the_content', function ($content) use ($twigController) {
        if (!is_singular()) {
            return $content;
        }

        ob_start();

        $postThumbnailId = (int)get_post_thumbnail_id();
        if ($postThumbnailId < 1) {
            return $content;
        }

        $model = new Model\PostThumbnail($postThumbnailId, 'post-thumbnail');
        $viewData = new TwigData($model, 'thumbnail.twig');
        $twigController->render($viewData);

        return ob_get_clean() . $content;
    });
});

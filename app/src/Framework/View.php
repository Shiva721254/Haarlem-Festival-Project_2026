<?php

namespace App\Framework;

/**
 * Minimal view renderer that wraps a content template in the shared
 * header/footer partials, giving every page a single, consistent layout
 * instead of each view requiring the partials by hand.
 */
class View
{
    private const VIEWS = __DIR__ . '/../Views/';

    /**
     * Render a content view inside the shared layout.
     *
     * @param string              $view  Path under Views/ without extension, e.g. 'Auth/register'.
     * @param array<string,mixed> $data  Variables made available to the view.
     * @param string              $title Document title.
     */
    public static function render(string $view, array $data = [], string $title = 'Visit Haarlem'): void
    {
        $data['title'] = $title;
        extract($data, EXTR_OVERWRITE);

        require self::VIEWS . 'Partials/header.php';
        require self::VIEWS . rtrim($view, '/') . '.php';
        require self::VIEWS . 'Partials/footer.php';
    }

    /**
     * Render an admin view inside the admin panel layout (sidebar shell).
     *
     * @param string              $view  Path under Views/ without extension, e.g. 'Admin/events/index'.
     * @param array<string,mixed> $data
     */
    public static function renderAdmin(string $view, array $data = [], string $title = 'Admin'): void
    {
        $data['title'] = $title;
        extract($data, EXTR_OVERWRITE);

        require self::VIEWS . 'Admin/partials/header.php';
        require self::VIEWS . rtrim($view, '/') . '.php';
        require self::VIEWS . 'Admin/partials/footer.php';
    }
}

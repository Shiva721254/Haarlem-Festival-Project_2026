<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Repositories\ContentBlockRepository;
use App\Services\ContentBlockService;

final class AdminContentController
{
    public function edit(array $vars = []): void
    {
       

        $svc = new ContentBlockService(new ContentBlockRepository());

        $page = (string)($vars['page'] ?? ($_GET['page'] ?? 'home'));
        $page = trim($page);
        if ($page === '') $page = 'home';

        $blocks = $svc->getPageBlocks($page);

        require __DIR__ . '/../views/Admin/Edit.php';

    }

    public function save(array $vars = []): void
    {
        AuthMiddleware::requireAdmin();

        $page = (string)($_POST['page'] ?? 'home');
        $page = trim($page);
        if ($page === '') $page = 'home';

        $this->savePage('/admin/edit?page=' . urlencode($page));
    }

    private function savePage(string $redirectUrl): void
    {
        $svc = new ContentBlockService(new ContentBlockRepository());

        $values = $_POST['value'] ?? [];
        if (!is_array($values)) $values = [];

        $json = $_POST['json'] ?? [];
        if (is_array($json)) {
            foreach ($json as $id => $flat) {
                if (!is_array($flat)) continue;

                $built = [];
                foreach ($flat as $path => $val) {
                    $path = (string)$path;
                    $parts = array_values(array_filter(explode('.', $path), fn($p) => $p !== ''));
                    if (!$parts) continue;

                    $ref = &$built;
                    $last = count($parts) - 1;

                    foreach ($parts as $i => $p) {
                        $p = (string)$p;

                        if ($i === $last) {
                            $ref[$p] = (string)$val;
                        } else {
                            if (!isset($ref[$p]) || !is_array($ref[$p])) {
                                $ref[$p] = [];
                            }
                            $ref = &$ref[$p];
                        }
                    }
                    unset($ref);
                }

                $encoded = json_encode($built, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if (is_string($encoded)) {
                    $values[(int)$id] = $encoded;
                }
            }
        }

        $svc->saveValues($values);

        header('Location: ' . $redirectUrl);
        exit;
    }
}

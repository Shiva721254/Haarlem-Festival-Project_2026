<?php

namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\ContentService;

class AdminContentController
{
    private ContentService $contentService;

    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    public function edit(): void
    {
        AuthMiddleware::requireAdmin();
        View::renderAdmin('Admin/edit', [
            'pageSlug' => 'home',
            'blocks' => $this->contentService->getPageBlocks('home'),
        ], 'Edit homepage');
    }

    public function save(): void
    {
        AuthMiddleware::requireAdmin();

        $html = $_POST['blocks'] ?? [];
        if (!is_array($html)) {
            Flash::error('Invalid content submission.');
            header('Location: /admin/edit');
            exit();
        }

        $files = $this->groupUploadedFiles('images');

        try {
            $this->contentService->savePage('home', $html, $files, (int)($_SESSION['UserId'] ?? 0));
            Flash::success('Homepage content saved.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        header('Location: /admin/edit');
        exit();
    }

    /**
     * Normalise $_FILES for inputs named images[block_key].
     *
     * @return array<string,array<string,mixed>>
     */
    private function groupUploadedFiles(string $field): array
    {
        if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
            return [];
        }

        $files = [];
        foreach ($_FILES[$field]['name'] as $key => $name) {
            $files[$key] = [
                'name' => $name,
                'type' => $_FILES[$field]['type'][$key] ?? '',
                'tmp_name' => $_FILES[$field]['tmp_name'][$key] ?? '',
                'error' => $_FILES[$field]['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                'size' => $_FILES[$field]['size'][$key] ?? 0,
            ];
        }
        return $files;
    }
}

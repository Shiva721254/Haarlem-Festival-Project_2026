<?php

namespace App\Controllers;

use App\Framework\Flash;
use App\Framework\Redirect;
use App\Framework\View;
use App\Middleware\AuthMiddleware;
use App\Services\Interfaces\IContentService;

class AdminContentController
{
    private IContentService $contentService;

    public function __construct(IContentService $contentService)
    {
        $this->contentService = $contentService;
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
            Redirect::to('/admin/edit');
        }
        $this->saveHomepage($html);
        Redirect::to('/admin/edit');
    }

    /** Persist the homepage blocks, flashing the outcome. */
    private function saveHomepage(array $html): void
    {
        try {
            $this->contentService->savePageFromUploadField('home', $html, $_FILES['images'] ?? [], (int)($_SESSION['UserId'] ?? 0));
            Flash::success('Homepage content saved.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }
    }
}

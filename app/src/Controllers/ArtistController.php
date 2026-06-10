<?php

namespace App\Controllers;

use App\Framework\View;
use App\Services\ArtistService;
use App\Services\Interfaces\IArtistService;

class ArtistController
{
    private IArtistService $artistService;

    public function __construct()
    {
        $this->artistService = new ArtistService();
    }

    // GET: /artist/{id}
    public function show(array $vars = []): void
    {
        $artist = $this->artistService->getById((int)($vars['id'] ?? 0));
        if ($artist === null) {
            http_response_code(404);
            echo 'Artist not found';
            return;
        }

        View::render('Artists/detail', [
            'artist'   => $artist,
            'schedule' => $this->artistService->getSchedule($artist->id),
        ], $artist->name);
    }
}

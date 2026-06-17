<?php

namespace App\Controllers;

use App\Framework\Http;
use App\Framework\View;
use App\Services\Interfaces\IArtistService;

class ArtistController
{
    private IArtistService $artistService;

    public function __construct(IArtistService $artistService)
    {
        $this->artistService = $artistService;
    }

    // GET: /artist/{id}
    public function show(array $vars = []): void
    {
        $artist = $this->artistService->getById((int)($vars['id'] ?? 0));
        if ($artist === null) {
            Http::notFound('Artist not found');
        }
        View::render('Artists/detail', [
            'artist'   => $artist,
            'schedule' => $this->artistService->getSchedule($artist->id),
        ], $artist->name);
    }
}

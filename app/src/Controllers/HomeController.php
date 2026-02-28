<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContentBlockRepository;
use App\Services\ContentBlockService;

final class HomeController
{
    public function index(array $vars = []): void
    {
        $svc = new ContentBlockService(new ContentBlockRepository());

        $hero = $svc->getJson('home', 'hero', 'main', 0);
        $experience = $svc->getJson('home', 'experience', 'main', 0);
        $experienceCards = $svc->getJsonList('home', 'experience_cards', 'card');
        $restaurantsHeader = $svc->getJson('home', 'restaurants', 'main', 0);
        $programs = $svc->getJson('home', 'programs', 'main', 0);
        $programTiles = $svc->getJsonList('home', 'program_tiles', 'tile');
        $footer = $svc->getJson('global', 'footer', 'main', 0);

        require __DIR__ . '/../Views/Admin/Index.php';
    }

    public function ratatouille(array $vars = []): void
    {
        $svc = new ContentBlockService(new ContentBlockRepository());

        $page = 'ratatouille';

        $hero       = $svc->getJson($page, 'hero', 'main', 0);
        $about      = $svc->getJson($page, 'about', 'main', 0);
        $chef       = $svc->getJson($page, 'chef', 'main', 0);

        $menuHeader = $svc->getJson($page, 'festival_menu', 'main', 0);
        $menuCards  = $svc->getJsonList($page, 'festival_menu', 'card');

        $galleryHdr = $svc->getJson($page, 'gallery', 'main', 0);
        $gallery    = $svc->getJsonList($page, 'gallery', 'tile');

        $footer = $svc->getJson('global', 'footer', 'main', 0);

        require __DIR__ . '/../Views/Admin/Ratatouille.php';
    }

    public function ml(array $vars = []): void
{
    $svc = new ContentBlockService(new ContentBlockRepository());

    $page = 'ml';

    $hero = $svc->getJson($page, 'hero', 'main', 0);
    $philosophy = $svc->getJson($page, 'philosophy', 'main', 0);

    $featuredHeader = $svc->getJson($page, 'featured', 'main', 0);
    $featuredCards = $svc->getJsonList($page, 'featured', 'card');

    $vision = $svc->getJson($page, 'vision', 'main', 0);

    $experienceHeader = $svc->getJson($page, 'experience', 'main', 0);
    $experienceTiles = $svc->getJsonList($page, 'experience', 'tile');

    $footer = $svc->getJson('global', 'footer', 'main', 0);

    require __DIR__ . '/../views/Admin/ML.php';
}

}

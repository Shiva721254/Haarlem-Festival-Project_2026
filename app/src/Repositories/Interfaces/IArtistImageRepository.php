<?php 
namespace App\Repositories\Interfaces;

interface IArtistImageRepository
{
    public function addImage(int $artist_id, string $image_path): void;
}
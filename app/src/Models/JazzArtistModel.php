<?php
namespace App\Models;

class JazzArtistModel 
{
    public int $artist_id;
    public string $artist_name;
    public string $description;
    public string $venue_name = '';
    public string $session_room = '';

    public static function fromDb(array $data): self
    {
        $artist = new self();
        $artist->artist_id = (int)$data['artist_id'];
        $artist->artist_name = $data['artist_name'];
        $artist->description = $data['description'] ?? '';
        return $artist;
    }

    public static function fromPost(): JazzArtistModel
    {
        $artist = new JazzArtistModel();
        $artist->artist_id = isset($_POST['artist_id']) ? (int)$_POST['artist_id'] : 0;
        $artist->artist_name = $_POST['artist_name'] ?? '';
        $artist->description = $_POST['description'] ?? '';
        return $artist;
    }
}
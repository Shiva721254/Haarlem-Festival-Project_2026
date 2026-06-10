<?php
namespace App\Models;

class ArtistModel
{
    public int $id;
    public string $name;
    public ?string $genre = null;
    public ?string $bio = null;
    public ?string $image = null;
    public ?string $career_highlights = null;
    public ?string $tracks = null;
    public ?string $audio_url = null;

    /** @var string[] gallery image paths */
    public array $images = [];

    public static function fromDb(array $data): self
    {
        $a = new self();
        $a->id = (int)$data['id'];
        $a->name = $data['name'];
        $a->genre = $data['genre'] ?? null;
        $a->bio = $data['bio'] ?? null;
        $a->image = $data['image'] ?? null;
        $a->career_highlights = $data['career_highlights'] ?? null;
        $a->tracks = $data['tracks'] ?? null;
        $a->audio_url = $data['audio_url'] ?? null;
        return $a;
    }

    /**
     * Tracks as a trimmed list, split on ';' or newlines.
     *
     * @return string[]
     */
    public function trackList(): array
    {
        if ($this->tracks === null || trim($this->tracks) === '') {
            return [];
        }
        $parts = preg_split('/[;\n]+/', $this->tracks) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static fn($t) => $t !== ''));
    }
}

<?php
namespace App\Models;

class DanceActorModel
{
    public int $actor_id = 0;
    public string $actor_name = '';
    public string $description = '';
    public string $image_path = '';

    public static function fromDb(array $data): self
    {
        $actor = new self();
        $actor->actor_id = (int)($data['actor_id'] ?? $data['DanceActorId'] ?? 0);
        $actor->actor_name = $data['actor_name'] ?? $data['ActorName'] ?? '';
        $actor->description = $data['description'] ?? $data['Description'] ?? '';
        $actor->image_path = $data['image_path'] ?? $data['ImagePath'] ?? '';

        return $actor;
    }

    public static function fromPost(): self
    {
        $actor = new self();
        $actor->actor_id = isset($_POST['actor_id']) ? (int)$_POST['actor_id'] : 0;
        $actor->actor_name = $_POST['actor_name'] ?? '';
        $actor->description = $_POST['description'] ?? '';
        $actor->image_path = $_POST['image_path'] ?? '';

        return $actor;
    }
}

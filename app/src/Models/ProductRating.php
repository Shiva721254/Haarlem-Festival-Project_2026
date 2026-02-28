<?php
namespace App\Models;

class ProductRating 
{
    public int $ProductId;
    public int $UserId;
    public ?int $Rating; 
    public ?string $Review;
    public ?string $CreatedAt;

    public ?string $FirstName = null;
    public ?string $LastName = null;

    public static function fromDb(array $data): self
    {
        $rating = new self();
        $rating->ProductId = (int)$data['ProductId'];
        $rating->UserId = (int)$data['UserId'];
        $rating->Rating = $data['Rating'] !== null ? (int)$data['Rating'] : null;
        $rating->Review = $data['Review'] ?? null;
        $rating->CreatedAt = $data['CreatedAt'] ?? null;
        $rating->FirstName = $data['FirstName'] ?? null;
        $rating->LastName = $data['LastName'] ?? null;
        
        return $rating;
    }

    public static function fromArray(array $data): self
    {
        $rating = new self();
        $rating->ProductId = (int)$data['ProductId'];
        $rating->UserId = (int)$data['UserId']  ;
        $rating->Rating = $data['Rating'] !== null ? (int)$data['Rating'] : null;
        $rating->Review = $data['Review'] ?? null;
        $rating->CreatedAt = $data['CreatedAt'] ?? null;
        $rating->FirstName = $data['FirstName'] ?? null;
        $rating->LastName = $data['LastName'] ?? null;
        
        return $rating;
    }

    public function fromPost(): self
    {
        $this->ProductId = isset($_POST['ProductId']) ? (int)$_POST['ProductId'] : 0;
        $this->UserId = isset($_POST['UserId']) ? (int)$_POST['UserId'] : 0;
        $this->Rating = isset($_POST['Rating']) ? (int)$_POST['Rating'] : null;
        $this->Review = $_POST['Review'] ?? null;
        $this->CreatedAt = $_POST['CreatedAt'] ?? null;

        $this->FirstName = $_POST['FirstName'] ?? null;
        $this->LastName = $_POST['LastName'] ?? null;
        
        return $this;
    }
}
<?php
namespace App\Models;

class shoppingCartModel
{
    public int $UserId;
    public int $ProductId;
    public int $Quantity;

    public static function fromDb(array $data): self
    {

        $cartItem = new self();
        $cartItem->UserId = (int)$data['UserId'];
        $cartItem->ProductId = (int)$data['ProductId'];
        $cartItem->Quantity = (int)$data['Quantity'];
        
        return $cartItem;
    }

    public static function fromArray(array $data): self
    {
            
        $cartItem = new self();
        $cartItem->UserId = (int)$data['UserId'];
        $cartItem->ProductId = (int)$data['ProductId'];
        $cartItem->Quantity = (int)$data['Quantity'];
        
        return $cartItem;
    }

    public function fromPost(): self
    {
        $this->UserId = isset($_POST['UserId']) ? (int)$_POST['UserId'] : 0;
        $this->ProductId = isset($_POST['ProductId']) ? (int)$_POST['ProductId'] : 0;
        $this->Quantity = isset($_POST['Quantity']) ? (int)$_POST['Quantity'] : 1;
        
        return $this;
    }
} 
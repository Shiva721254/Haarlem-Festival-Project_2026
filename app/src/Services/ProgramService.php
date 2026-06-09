<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\Interfaces\IOrderRepository;
use App\Services\Interfaces\IProgramService;

class ProgramService implements IProgramService
{
    private IOrderRepository $orderRepo;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
    }

    public function getForUser(int $userId): array
    {
        return $this->orderRepo->getProgramEvents($userId);
    }
}

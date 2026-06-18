<?php
namespace App\Services;

use App\Repositories\Interfaces\IProgramRepository;
use App\Services\Interfaces\IProgramService;

class ProgramService implements IProgramService
{
    private IProgramRepository $programRepository;

    public function __construct(IProgramRepository $programRepository)
    {
        $this->programRepository = $programRepository;
    }

    public function getForUser(int $userId): array
    {
        return $this->programRepository->getForUser($userId);
    }
}

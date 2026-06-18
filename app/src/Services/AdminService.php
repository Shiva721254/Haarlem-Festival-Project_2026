<?php
namespace App\Services;

use App\Repositories\Interfaces\IAdminRepository;
use App\Services\Interfaces\IAdminService;

class AdminService implements IAdminService
{
    private IAdminRepository $adminRepository;

    public function __construct(IAdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function getDashboardStats(): array
    {
        return $this->adminRepository->dashboardCounts();
    }
}

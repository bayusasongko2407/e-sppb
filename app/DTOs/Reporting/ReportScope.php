<?php

declare(strict_types=1);

namespace App\DTOs\Reporting;

class ReportScope
{
    /**
     * @param  array<string>  $allowedModules
     * @param  array<int>  $allowedPlants
     * @param  array<int>  $allowedDepartments
     */
    public function __construct(
        public readonly array $allowedModules,
        public readonly array $allowedPlants,
        public readonly array $allowedDepartments,
        public readonly bool $canPreview,
        public readonly bool $canExport,
        public readonly bool $canPrint,
    ) {}

    /**
     * Check if a specific module is allowed.
     */
    public function hasModuleAccess(string $module): bool
    {
        return in_array($module, $this->allowedModules, true);
    }
}

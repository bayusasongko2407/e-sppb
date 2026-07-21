<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Contracts\Reporting\ReportInterface;
use InvalidArgumentException;

class ReportRegistry
{
    /**
     * @var array<string, ReportInterface>
     */
    protected array $reports = [];

    /**
     * Register a new report.
     */
    public function register(ReportInterface $report): void
    {
        $this->reports[$report->getIdentifier()] = $report;
    }

    /**
     * Get a specific report by its identifier.
     *
     * @throws InvalidArgumentException
     */
    public function get(string $identifier): ReportInterface
    {
        if (! $this->has($identifier)) {
            throw new InvalidArgumentException("Report [{$identifier}] is not registered.");
        }

        return $this->reports[$identifier];
    }

    /**
     * Check if a report exists in the registry.
     */
    public function has(string $identifier): bool
    {
        return array_key_exists($identifier, $this->reports);
    }

    /**
     * Get all registered reports.
     *
     * @return array<string, ReportInterface>
     */
    public function all(): array
    {
        return $this->reports;
    }

    /**
     * Get options array for select inputs (identifier => name).
     *
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        $options = [];
        foreach ($this->reports as $identifier => $report) {
            $options[$identifier] = $report->getName();
        }

        return $options;
    }
}

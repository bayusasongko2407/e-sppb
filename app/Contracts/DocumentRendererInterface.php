<?php

declare(strict_types=1);

namespace App\Contracts;

interface DocumentRendererInterface
{
    /**
     * Render the given template and data into a PDF.
     *
     * @param  string  $templatePath  The path or identifier of the template.
     * @param  array<string, mixed>  $data  The data to inject into the template.
     * @param  array<string, mixed>  $options  Additional rendering options.
     * @return string The raw PDF content.
     */
    public function renderToPdf(string $templatePath, array $data, array $options = []): string;
}

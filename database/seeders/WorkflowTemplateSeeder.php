<?php

namespace Database\Seeders;

use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;

class WorkflowTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkflowTemplate::factory()->count(5)->create();
    }
}

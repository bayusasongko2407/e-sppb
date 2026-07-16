<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Recompute verification_token_hash for all existing document_pages to use
     * a deterministic SHA256 derived from generation_uuid + page_number.
     * This replaces the previous random hash approach and enables a secure,
     * SHA256-based QR verification URL.
     */
    public function up(): void
    {
        // Recompute all existing verification_token_hash values
        $pages = DB::table('document_pages')
            ->join('document_generations', 'document_pages.document_generation_id', '=', 'document_generations.id')
            ->select('document_pages.id', 'document_generations.uuid as gen_uuid', 'document_pages.page_number')
            ->get();

        foreach ($pages as $page) {
            $sha256Token = hash('sha256', $page->gen_uuid.'-page-'.$page->page_number);
            DB::table('document_pages')
                ->where('id', $page->id)
                ->update(['verification_token_hash' => $sha256Token]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot deterministically reverse; no-op
    }
};

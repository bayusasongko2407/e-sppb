<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function viewer(Attachment $attachment): Response
    {
        $disk = $attachment->disk ?? config('filesystems.default', 'private');
        $path = $attachment->path;

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Generate temporary signed URLs for stream and download
        $streamUrl = URL::temporarySignedRoute(
            'attachments.preview',
            now()->addMinutes(15),
            ['attachment' => $attachment->uuid]
        );

        $downloadUrl = URL::temporarySignedRoute(
            'attachments.download',
            now()->addMinutes(15),
            ['attachment' => $attachment->uuid]
        );

        return response()->view('attachments.viewer', [
            'attachment' => $attachment,
            'streamUrl' => $streamUrl,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    public function preview(Attachment $attachment): StreamedResponse|Response
    {
        $disk = $attachment->disk ?? config('filesystems.default', 'private');
        $path = $attachment->path;

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $contentType = Storage::disk($disk)->mimeType($path) ?? $attachment->mime_type ?? 'application/octet-stream';

        return response()->stream(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="'.basename($attachment->original_name).'"',
        ]);
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $disk = $attachment->disk ?? config('filesystems.default', 'private');
        $path = $attachment->path;

        if (! Storage::disk($disk)->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path, $attachment->original_name);
    }

    public function delete(Attachment $attachment): RedirectResponse
    {
        $disk = $attachment->disk ?? config('filesystems.default', 'private');
        $path = $attachment->path;
        $sppbHeader = $attachment->sppbHeader;

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        $attachment->delete();

        // Flash Filament notification if possible, or simple session flash
        session()->flash('notification', [
            'title' => 'Lampiran dihapus',
            'status' => 'success',
        ]);

        $editUrl = route('filament.admin.resources.sppb-headers.edit', ['record' => $sppbHeader]);

        return redirect()->to($editUrl);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Models\Attachment;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AttachmentViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_access_viewer_without_signature(): void
    {
        $plant = Plant::factory()->create();
        $user = User::factory()->create(['plant_id' => $plant->id]);
        $sppb = SppbHeader::factory()->create(['plant_id' => $plant->id, 'requester_id' => $user->id]);

        $attachment = Attachment::factory()->create([
            'sppb_header_id' => $sppb->id,
            'uploader_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Access route without a signed URL
        $url = route('attachments.viewer', ['attachment' => $attachment->uuid]);
        $response = $this->get($url);

        $response->assertStatus(403);
    }

    public function test_can_access_viewer_with_valid_signature_if_file_exists(): void
    {
        $plant = Plant::factory()->create();
        $user = User::factory()->create(['plant_id' => $plant->id]);
        $sppb = SppbHeader::factory()->create(['plant_id' => $plant->id, 'requester_id' => $user->id]);

        $disk = 'private';
        $path = 'sppb-attachments/test.pdf';
        Storage::fake($disk);
        Storage::disk($disk)->put($path, 'dummy content');

        $attachment = Attachment::factory()->create([
            'sppb_header_id' => $sppb->id,
            'uploader_id' => $user->id,
            'disk' => $disk,
            'path' => $path,
            'extension' => 'pdf',
            'original_name' => 'test.pdf',
        ]);

        $this->actingAs($user);

        // Access using signed URL
        $signedUrl = URL::signedRoute('attachments.viewer', ['attachment' => $attachment->uuid]);
        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertViewIs('attachments.viewer');
        $response->assertViewHas('attachment', $attachment);

        // Assert view has generated short-lived signed preview and download URLs
        $response->assertViewHas('streamUrl');
        $response->assertViewHas('downloadUrl');
    }

    public function test_viewer_aborts_404_if_file_does_not_exist_in_storage(): void
    {
        $plant = Plant::factory()->create();
        $user = User::factory()->create(['plant_id' => $plant->id]);
        $sppb = SppbHeader::factory()->create(['plant_id' => $plant->id, 'requester_id' => $user->id]);

        $disk = 'private';
        Storage::fake($disk); // Empty disk, file test.pdf doesn't exist

        $attachment = Attachment::factory()->create([
            'sppb_header_id' => $sppb->id,
            'uploader_id' => $user->id,
            'disk' => $disk,
            'path' => 'sppb-attachments/test.pdf',
        ]);

        $this->actingAs($user);

        $signedUrl = URL::signedRoute('attachments.viewer', ['attachment' => $attachment->uuid]);
        $response = $this->get($signedUrl);

        $response->assertStatus(404);
    }
}

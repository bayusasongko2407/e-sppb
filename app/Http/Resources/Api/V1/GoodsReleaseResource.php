<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReleaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['release_date'] = $this->release_date ? $this->release_date->toIso8601String() : ($this->created_at ? $this->created_at->toIso8601String() : null);
        $data['qr_code_url'] = url("/v1/verify/document/{$this->verification_hash}");
        $data['signature'] = $this->recipient_signature; // compatibility alias

        if ($this->relationLoaded('goodsReleaseItems')) {
            $data['items'] = $this->goodsReleaseItems;
        } elseif (isset($data['goods_release_items'])) {
            $data['items'] = $data['goods_release_items'];
        }

        if ($this->relationLoaded('sppbHeader') && $this->sppbHeader) {
            $data['sppb_header'] = [
                'id' => $this->sppbHeader->id,
                'uuid' => $this->sppbHeader->uuid,
                'document_number' => $this->sppbHeader->document_number,
                'status' => $this->sppbHeader->status,
                'purpose' => $this->sppbHeader->purpose,
                'plant' => $this->sppbHeader->plant ? [
                    'id' => $this->sppbHeader->plant->id,
                    'code' => $this->sppbHeader->plant->code,
                    'name' => $this->sppbHeader->plant->name,
                ] : null,
                'destination_location' => $this->sppbHeader->destinationLocation ? [
                    'id' => $this->sppbHeader->destinationLocation->id,
                    'code' => $this->sppbHeader->destinationLocation->code,
                    'name' => $this->sppbHeader->destinationLocation->name,
                ] : null,
            ];
        }

        return $data;
    }
}

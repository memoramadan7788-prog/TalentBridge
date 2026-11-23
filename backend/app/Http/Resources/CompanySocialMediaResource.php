<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySocialMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'company_id'     => $this->company_id,
            'platform_id'    => $this->platform_id,
            'platform_name'  => $this->platform?->name,
            'platform_icon'  => $this->platform?->icon,
            'base_domain'    => $this->platform?->base_domain,
            'url'            => $this->url,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}

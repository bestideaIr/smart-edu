<?php

namespace App\Http\Resources\Curriculum;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumVersionOverviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'status'       => (string) $this->status,
            'published_at' => $this->published_at,
            'archived_at'  => $this->archived_at,
            'created_at'   => $this->created_at,
        ];
    }
}

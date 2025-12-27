<?php

namespace App\Http\Resources\Curriculum;

use Illuminate\Http\Resources\Json\JsonResource;

class CompetencyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'code'   => $this->code ?? null,
            'title'  => $this->title ?? null,

            'role'   => $this->pivot?->role,
            'weight' => $this->pivot?->weight,
        ];
    }
}

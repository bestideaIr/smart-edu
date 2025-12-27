<?php

namespace App\Http\Resources\Curriculum;

use Illuminate\Http\Resources\Json\JsonResource;

class LearningUnitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title ?? null,
            'content_type' => $this->content_type ?? null,
            'difficulty'   => $this->difficulty ?? null,
            'duration'     => $this->duration_minutes ?? null,

            'order_index' => $this->pivot?->order_index,

            'competencies' => CompetencyResource::collection(
                $this->whenLoaded('competencies')
            ),
        ];
    }
}

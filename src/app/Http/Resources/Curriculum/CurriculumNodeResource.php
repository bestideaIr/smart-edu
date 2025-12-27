<?php

namespace App\Http\Resources\Curriculum;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumNodeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'parent_id'   => $this->parent_id,
            'type'        => $this->type ?? null,
            'depth'       => $this->depth ?? null,
            'order_index' => $this->order_index,

            'learning_units' => LearningUnitResource::collection(
                $this->whenLoaded('learningUnits')
            ),

            'children' => CurriculumNodeResource::collection(
                $this->whenLoaded('children')
            ),
        ];
    }
}

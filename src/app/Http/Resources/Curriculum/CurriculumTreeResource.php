<?php

namespace App\Http\Resources\Curriculum;

use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumTreeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'nodes' => CurriculumNodeResource::collection($this),
        ];
    }
}

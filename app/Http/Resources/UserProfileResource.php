<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bio'    => $this->bio,
            'avatar' => $this->avatar_url,
            'created_at' => $this->created_at,
        ];
    }
}



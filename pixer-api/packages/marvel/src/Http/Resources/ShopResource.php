<?php

namespace Marvel\Http\Resources;

use Illuminate\Http\Request;

class ShopResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'owner' => $this->when($this->needToInclude($request, 'shop.owner'), function () {
                return new UserResource($this->owner);
            }),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'logo' => $this->logo,
            'is_active' => $this->is_active,
            'address' => $this->address,
            'settings' => $this->settings,
            'notifications' => $this->notifications,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

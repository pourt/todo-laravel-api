<?php

namespace PERP\User\Responses\Collection;

use Illuminate\Http\Resources\Json\ResourceCollection;
use PERP\User\Responses\Resource\UserResource;

class UserCollection extends ResourceCollection
{
    public $preserveKeys = true;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (request()->returnAll) {
            return $this->collection
                ->map(function ($data) {
                    return (new UserResource($data));
                });
        }

        return [
            'users' => $this->collection
                ->map(function ($data) {
                    return (new UserResource($data));
                }),
            'pagination' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
            ]
        ];
    }
}

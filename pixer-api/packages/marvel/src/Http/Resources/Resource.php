<?php

namespace Marvel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{
    /**
     * check it needs include an optional value
     *
     * @param Request $request
     * @param string $key
     * @return bool
     */
    public function needToInclude(Request $request, string $key): bool
    {
        return in_array($key, explode(',', $request->get('include')));
    }
}

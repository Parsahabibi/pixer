<?php

namespace Marvel\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Repositories\AttributeValueRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\AttributeValueRequest;
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AttributeValueController extends CoreController
{
    public $repository;

    public function __construct(AttributeValueRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        return $this->repository->with('attribute')->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AttributeValueRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(AttributeValueRequest $request)
    {
        try {
            if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
                $validatedData = $request->validated();
                return $this->repository->create($validatedData);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $th) {
            throw new MarvelException(SOMETHING_WENT_WRONG);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            return $this->repository->with('attribute')->findOrFail($id);
        } catch (MarvelException $th) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AttributeValueRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(AttributeValueRequest $request, $id)
    {
        try {
            $request->id = $id;
            return $this->updateAttributeValues($request);
        } catch (MarvelException $th) {
            throw new MarvelException(COULD_NOT_UPDATE_THE_RESOURCE);
        }
    }
    public function updateAttributeValues(AttributeValueRequest $request)
    {
        if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
            try {
                $validatedData = $request->except('id');
                return $this->repository->findOrFail($request->id)->update($validatedData);
            } catch (\Exception $e) {
                throw new ModelNotFoundException(NOT_FOUND);
            }
        }
        throw new AuthorizationException(NOT_AUTHORIZED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id, Request $request)
    {
        try {
            $request->id = $id;
            return $this->destroyAttributeValues($request);
        } catch (MarvelException $th) {
            throw new MarvelException(COULD_NOT_DELETE_THE_RESOURCE);
        }
    }
    /**
     * It deletes an attribute from the database
     * 
     * @param Request request The request object.
     * @return JsonResponse
     */
    public function destroyAttributeValues(Request $request)
    {
        $shop_id = $this->repository->findOrFail($request->id)->attribute->shop_id;
        if ($this->repository->hasPermission($request->user(), $shop_id)) {
            $attributesValue =  $this->repository->findOrFail($request->id);
            $attributesValue->delete();
            return $attributesValue;
        }
        throw new AuthorizationException(NOT_AUTHORIZED);
    }
}

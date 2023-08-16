<?php

namespace Marvel\Http\Controllers;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Marvel\Database\Models\Product;
use Marvel\Database\Repositories\AuthorRepository;
use Marvel\Enums\Permission;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\AuthorRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthorController extends CoreController
{
    public $repository;

    public function __construct(AuthorRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?   $request->limit : 15;
        return $this->fetchAuthors($request)->paginate($limit);
    }

    public function fetchAuthors(Request $request)
    {
        $language = $request->language ?? DEFAULT_LANGUAGE;
        return $this->repository->where('language', $language);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AuthorRequest $request
     * @return mixed
     */
    public function store(AuthorRequest $request)
    {
        try {
            if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
                return $this->repository->storeAuthor($request);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(COULD_NOT_CREATE_THE_RESOURCE);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function show(Request $request, $slug)
    {
        try {
            $request->slug = $slug;
            return $this->fetchAuthor($request);
        } catch (MarvelException $th) {
            throw new MarvelException(NOT_FOUND);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $slug
     * @return JsonResponse
     */
    public function fetchAuthor(Request $request)
    {
        $slug = $request->slug;
        $language = $request->language ?? DEFAULT_LANGUAGE;
        try {
            $author = $this->repository->where('slug', $slug)->where('language', $language)->firstOrFail();
        } catch (\Exception $e) {
            throw new ModelNotFoundException(NOT_FOUND);
        }
        return $author;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AuthorRequest $request
     * @param int $id
     * @return array
     */
    public function update(AuthorRequest $request, $id)
    {
        try {
            $request->id = $id;
            return $this->updateAuthor($request);
        } catch (MarvelException $th) {
            throw new MarvelException(COULD_NOT_UPDATE_THE_RESOURCE);
        }
    }

    public function updateAuthor(Request $request)
    {
        if ($this->repository->hasPermission($request->user(), $request->shop_id)) {
            try {
                $author = $this->repository->findOrFail($request->id);
            } catch (\Exception $e) {
                throw new ModelNotFoundException(NOT_FOUND);
            }
            return $this->repository->updateAuthor($request, $author);
        }
        throw new AuthorizationException(NOT_AUTHORIZED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id, Request $request)
    {
       try {
        $request['id'] = $id;
        return $this->deleteAuthor($request);
       } catch (MarvelException $th) {
        throw new MarvelException(COULD_NOT_DELETE_THE_RESOURCE);
    }
    }
    public function deleteAuthor(Request $request)
    {
        if ($request->user()->hasPermissionTo(Permission::SUPER_ADMIN)) {
            $author = $this->repository->findOrFail($request->id);
            $author->delete();
            return $author;
        }
        throw new MarvelException(NOT_AUTHORIZED);
    }

    public function topAuthor(Request $request)
    {
        $language = $request->language ?? DEFAULT_LANGUAGE;
        $limit = $request->limit ? $request->limit : 10;
        return $this->repository->where('language', $language)->withCount('products')->orderBy('products_count', 'desc')->take($limit)->get();
    }
}

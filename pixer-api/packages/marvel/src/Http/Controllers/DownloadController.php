<?php

namespace Marvel\Http\Controllers;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Marvel\Database\Models\DownloadToken;
use Marvel\Database\Models\OrderedFile;
use Marvel\Database\Models\Product;
use Marvel\Database\Models\Variation;
use Marvel\Database\Repositories\DownloadRepository;
use Marvel\Exceptions\MarvelException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadController extends CoreController
{
    public $repository;

    public function __construct(DownloadRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * fetchDownloadableFiles
     *
     * @param mixed $request
     * @return void
     * @throws MarvelException
     */
    public function fetchDownloadableFiles(Request $request)
    {
        $limit = isset($request->limit) ? $request->limit : 15;
        return $this->fetchFiles($request)->paginate($limit)->loadMorph('file.fileable', [
            Product::class => ['shop'],
            Variation::class => ['product.shop'],
        ])->withQueryString();
    }

    /**
     * fetchFiles
     *
     * @param mixed $request
     * @return mixed
     * @throws MarvelException
     */
    public function fetchFiles(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                return OrderedFile::where('customer_id', $user->id)->with(['order']);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $th) {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    /**
     * generateDownloadableUrl
     *
     * @param mixed $request
     * @return void
     * @throws MarvelException
     */
    public function generateDownloadableUrl(Request $request)
    {
        try {
            $user = $request->user();
            $orderedFiles = OrderedFile::where('digital_file_id', $request->digital_file_id)->where('customer_id', $user->id)->get();
            if (count($orderedFiles)) {
                $dataArray = [
                    'user_id' => $user->id,
                    'token' => Str::random(16),
                    'digital_file_id' => $request->digital_file_id
                ];
                $newToken = DownloadToken::create($dataArray);
                return route('download_url.token', ['token' => $newToken->token]);
            }
            throw new AuthorizationException(NOT_AUTHORIZED);
        } catch (MarvelException $e) {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    /**
     * generateFreeDigitalDownloadableUrl -> Pixer-laravel
     *
     * @param mixed $request
     * @return void
     * @throws MarvelException
     */
    public function generateFreeDigitalDownloadableUrl(Request $request)
    {
        $product_id = $request->product_id;
        try {
            $product = Product::with('digital_file')->findOrFail($product_id);
        } catch (\Throwable $th) {
            throw new MarvelException(NOT_FOUND);
        }
        if ($product->price == 0 ||  (isset($product->sale_price) && $product->sale_price == 0)) {
            if ($product->digital_file->id) {
                $dataArray = [
                    'token' => Str::random(16),
                    'digital_file_id' => $product->digital_file->id
                ];
                $newToken = DownloadToken::create($dataArray);
                return route('download_url.token', ['token' => $newToken->token]);
            } else {
                throw new MarvelException(NOT_FOUND);
            }
        } else {
            throw new MarvelException(NOT_A_FREE_PRODUCT);
        }
    }

    /**
     * downloadFile
     *
     * @param mixed $token
     * @return void
     * @throws MarvelException
     */
    public function downloadFile($token)
    {
        try {
            $downloadToken = DownloadToken::with('file')->where('token', $token)->where('downloaded', 0)->first();
            if ($downloadToken) {
                $downloadToken->downloaded = 1;
                $downloadToken->save();
            } else {
                return ['message' => TOKEN_NOT_FOUND];
            }
        } catch (Exception $e) {
            throw new MarvelException(TOKEN_NOT_FOUND);
        }
        try {
            $mediaItem = Media::where('model_id', $downloadToken->file->attachment_id)->firstOrFail();
        } catch (Exception $e) {
            return ['message' => NOT_FOUND];
        }
        return response()->streamDownload(function () use ($downloadToken) {
            echo file_get_contents($downloadToken->file->url);
        }, $mediaItem->file_name);
    }


    // TODO : PB laravel dev er code, but need to be checked in Pixer which controller function is applicable
    // public function downloadFile($token)
    // {
    //     try {
    //         try {
    //             $downloadToken = DownloadToken::with('file')->where('token', $token)->first();
    //             if ($downloadToken) {
    //                 $downloadToken->delete();
    //             } else {
    //                 return ['message' => TOKEN_NOT_FOUND];
    //             }
    //         } catch (Exception $e) {
    //             throw new HttpException(404, TOKEN_NOT_FOUND);
    //         }
    //         try {
    //             $mediaItem = Media::findOrFail($downloadToken->file->attachment_id);
    //         } catch (Exception $e) {
    //             return ['message' => NOT_FOUND];
    //         }
    //         return $mediaItem;
    //     } catch (MarvelException $e) {
    //         throw new MarvelException(NOT_FOUND);
    //     }
    // }
}

<?php


namespace Marvel\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Conversation;
use Marvel\Database\Models\Shop;
use Marvel\Database\Repositories\ConversationRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\ConversationCreateRequest;
use Prettus\Validator\Exceptions\ValidatorException;


class ConversationController extends CoreController
{
    public $repository;

    public function __construct(ConversationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection|Conversation[]
     */
    public function index(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        $conversation = $this->fetchConversations($request);

        return $conversation->paginate($limit);

    }

    public function show($conversation_id)
    {
        $user = Auth::user();
        $conversation = $this->repository->with(['shop', 'user.profile'])->findOrFail($conversation_id);
        abort_unless($user->shop_id === $conversation->shop_id || in_array( $conversation->shop_id, $user->shops->pluck('id')->toArray()) || $user->id === $conversation->user_id, 404, 'Unauthorized');

        return $conversation;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Query|Conversation[]
     */
    public function fetchConversations(Request $request)
    {
        return $this->repository->where(function($query) {
            $user = Auth::user();
            $query->where('user_id', $user->id);
            $query->orWhereIn('shop_id', $user->shops->pluck('id'));
            $query->orWhere('shop_id', $user->shop_id);
            $query->orderBy('updated_at', 'desc');
        })->with(['user.profile', 'shop']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ConversationCreateRequest $request
     * @return mixed
     * @throws ValidatorException
     */
    public function store(ConversationCreateRequest $request)
    {
        $user = $request->user();
        if(empty($user)) {
            throw new MarvelException(NOT_AUTHORIZED);
        }

        $shop = Shop::findOrFail($request->shop_id);
        if($shop->owner_id === $request->user()->id) {
            throw new MarvelException(YOU_CAN_NOT_SEND_MESSAGE_TO_YOUR_OWN_SHOP);
        }
        if($request->shop_id === $request->user()->shop_id) {
            throw new MarvelException(YOU_CAN_NOT_SEND_MESSAGE_TO_YOUR_OWN_SHOP);
        }
        return $this->repository->firstOrCreate([
            'user_id' => $user->id,
            'shop_id' => $request->shop_id
        ]);
    }
}

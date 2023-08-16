<?php


namespace Marvel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Conversation;
use Marvel\Database\Models\Message;
use Marvel\Database\Models\Participant;
use Marvel\Database\Repositories\ConversationRepository;
use Marvel\Database\Repositories\MessageRepository;
use Marvel\Exceptions\MarvelException;
use Marvel\Http\Requests\MessageCreateRequest;
use Prettus\Validator\Exceptions\ValidatorException;


class MessageController extends CoreController
{
    public $repository;
    public $conversationRepository;

    public function __construct(MessageRepository $repository, ConversationRepository $conversationRepository)
    {
        $this->repository = $repository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param $conversation_id
     * @return Collection|Message[]
     */
    public function index(Request $request, $conversation_id)
    {
        $request->conversation_id = $conversation_id;

        $user = Auth::user();
        $conversation = $this->conversationRepository->findOrFail($conversation_id);
        abort_unless($user->shop_id === $conversation->shop_id || in_array( $conversation->shop_id, $user->shops->pluck('id')->toArray()) || $user->id === $conversation->user_id, 404, 'Unauthorized');

        $messages = $this->fetchMessages($request);

        $limit = $request->limit ? $request->limit : 15;
        return $messages->paginate($limit);

    }

    public function seenMessage(Request $request)
    {
        return $this->seen($request->conversation_id);
    }

    public function seen($conversation_id)
    {
        $participant = Participant::where('conversation_id', $conversation_id)
            ->whereNull('last_read')
            ->where(function($query){
                $query->where('user_id', auth()->user()->id);
                $query->where('type', 'user');
            })
            ->update(['last_read' => new Carbon()]);

        if(0 === $participant) {
            $participant = Participant::where('conversation_id', $conversation_id)
                ->whereNull('last_read')
                ->where(function($query){
                    $query->whereIn('shop_id', auth()->user()->shops->pluck('id'));
                    $query->orWhere('shop_id', auth()->user()->shop_id);
                    $query->where('type', 'shop');
                })
                ->update(['last_read' => new Carbon()]);
        }

        return $participant;
    }

    public function fetchMessages(Request $request)
    {

        $user = $request->user();
        $conversation_id = $request->conversation_id;

        try {
            $conversation = Conversation::where('id', $conversation_id)
                ->where('user_id', $user->id)
                ->orWhereIn('shop_id', $user->shops()->pluck('id'))
                ->orWhere('shop_id', $user->shop_id)
                ->with(['user', 'shop'])->first();

            if(empty($conversation)) {
                throw new MarvelException(NOT_AUTHORIZED);
            }

            return $this->repository->where('conversation_id', $conversation_id)
                ->with(['conversation.shop', 'conversation.user.profile'])
                ->orderBy('id', 'DESC');
        } catch (\Exception $e) {
            throw new MarvelException(NOT_AUTHORIZED);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MessageCreateRequest $request
     * @param $conversation_id
     * @return mixed
     * @throws ValidatorException
     */
    public function store(MessageCreateRequest $request, $conversation_id)
    {
        $request->conversation_id = $conversation_id;

        return $this->storeMessage($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return mixed
     * @throws ValidatorException
     */
    public function storeMessage(Request $request)
    {
        return $this->repository->storeMessage($request);
    }
}

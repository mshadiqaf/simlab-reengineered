<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function message(ChatMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->chatService->chat(
            userMessage: $data['message'],
            history: $data['history'] ?? [],
            user: $request->user(),
        );

        return response()->json([
            'reply'       => $result['reply'],
            'formData'    => $result['formData'],
            'suggestions' => $result['suggestions'],
        ]);
    }
}

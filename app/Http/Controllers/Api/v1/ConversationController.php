<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ConversationService;
use App\Http\Requests\Conversation\ConversationRequest;

class ConversationController extends Controller
{
    // Add Conversation 
    public function conversation(ConversationRequest $request, $id = null)
    {
        return (new ConversationService)->createConversation($request, $id);
    }

    // Detail Conversation
    public function conversationDetail(request $request, $id = null)
    {
        return (new ConversationService)->conversationDetail($request, $id);
    }

    // List Conversation
    public function allConversation(request $request, $id = null)
    {
        return (new ConversationService)->allConversation($request, $id);
    }

    // Conversation Sent Message
    public function conversationMessage(Request $request, $id = null)
    {
        return (new ConversationService)->sendMessage($request, $id);
    }

    // Show Conversation
    public function showConversation(request $request, $id = null)
    {
        return (new ConversationService)->showConversation($request, $id);
    }

    // Latest Message
    public function latestMessage(request $request, $id = null)
    {
        return (new ConversationService)->latestMessage($request, $id);
    }

    // Conversation Exists
    public function conversationExists(request $request, $id = null)
    {
        return (new ConversationService)->conversationExists($request, $id);
    }
}

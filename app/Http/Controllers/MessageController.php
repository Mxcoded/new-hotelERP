<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // List all messages
    public function index()
    {
        $messages = Message::with(['sender', 'receiver', 'property'])->get();
        return response()->json($messages);
    }

    // Show a single message with details
    public function show($id)
    {
        $message = Message::with(['sender', 'receiver', 'property'])->find($id);
        return response()->json($message);
    }

    // Send a new message
    public function store(Request $request)
    {
        $message = Message::create($request->all());
        // Additional logic for message delivery, notifications, etc.
        return response()->json($message, 201);
    }

    // Update a message (e.g., marking as read, adding a reply)
    public function update(Request $request, $id)
    {
        $message = Message::findOrFail($id);
        $message->update($request->all());
        // Additional logic for updating message status or content
        return response()->json($message, 200);
    }

    // Delete a message
    public function destroy($id)
    {
        Message::find($id)->delete();
        return response()->json(null, 204);
    }

    // Additional methods for specific operations...

    // Example: Fetching unread messages for a user
    public function unreadMessages($userId)
    {
        // Logic to retrieve all unread messages for a specific user
    }

    // Example: Sending a broadcast message to multiple recipients
    public function sendBroadcast(Request $request)
    {
        // Logic for sending a message to multiple staff members or departments
    }
}

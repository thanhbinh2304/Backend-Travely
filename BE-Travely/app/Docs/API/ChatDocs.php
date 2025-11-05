<?php

namespace App\Docs\API;

class ChatDocs
{
    /**
     * @OA\Get(
     *     path="/chat/conversations",
     *     summary="Get user's chat conversations",
     *     description="Get all chat conversations for authenticated user",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Conversations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="conversationID", type="integer"),
     *                     @OA\Property(property="userID", type="string"),
     *                     @OA\Property(property="subject", type="string", example="Question about Paris Tour"),
     *                     @OA\Property(property="status", type="string", example="open"),
     *                     @OA\Property(property="createdAt", type="string", format="date-time"),
     *                     @OA\Property(property="lastMessageAt", type="string", format="date-time"),
     *                     @OA\Property(property="unreadCount", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getConversations() {}

    /**
     * @OA\Post(
     *     path="/chat/conversations",
     *     summary="Create a new conversation",
     *     description="Start a new chat conversation",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject","message"},
     *             @OA\Property(property="subject", type="string", example="Question about booking", maxLength=255),
     *             @OA\Property(property="message", type="string", example="I have a question about tour availability", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Conversation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conversation created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="conversationID", type="integer"),
     *                 @OA\Property(property="subject", type="string"),
     *                 @OA\Property(property="status", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function createConversation() {}

    /**
     * @OA\Get(
     *     path="/chat/conversations/{id}",
     *     summary="Get conversation details",
     *     description="Get specific conversation with all messages",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Conversation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conversation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="conversationID", type="integer"),
     *                 @OA\Property(property="subject", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(
     *                     property="messages",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="messageID", type="integer"),
     *                         @OA\Property(property="senderID", type="string"),
     *                         @OA\Property(property="message", type="string"),
     *                         @OA\Property(property="sentAt", type="string", format="date-time"),
     *                         @OA\Property(property="isRead", type="boolean"),
     *                         @OA\Property(property="sender", type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conversation not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Can only view own conversations"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getConversation() {}

    /**
     * @OA\Post(
     *     path="/chat/conversations/{id}/messages",
     *     summary="Send a message",
     *     description="Send a new message in a conversation",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Conversation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Thank you for your help!", maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message sent successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="messageID", type="integer"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="sentAt", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conversation not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Conversation is closed"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function sendMessage() {}

    /**
     * @OA\Patch(
     *     path="/chat/conversations/{id}/close",
     *     summary="Close a conversation",
     *     description="Close/resolve a chat conversation",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Conversation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conversation closed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Conversation closed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conversation not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function closeConversation() {}

    /**
     * @OA\Patch(
     *     path="/chat/messages/{id}/read",
     *     summary="Mark message as read",
     *     description="Mark a specific message as read",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message marked as read")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function markAsRead() {}

    /**
     * @OA\Get(
     *     path="/admin/chat/conversations",
     *     summary="Get all conversations (Admin)",
     *     description="Get all chat conversations in the system (Admin only)",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"open", "closed"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All conversations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function adminGetConversations() {}
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\PostReport;
use App\Models\PostUseful;
use Illuminate\Support\Str;
use App\Models\PostReaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
        /**
     * *** NEW HELPER FUNCTION ***
     * Helper function to get the correct avatar URL for a user.
     */
    private function getCommunityUserAvatar($user)
    {
        if ($user && $user->avatar_url) {
            // 1. Use uploaded avatar if it exists
            $avatarUrl = $user->avatar_url;
            // Check if it's a relative path (stored by us) or a full URL (from seeder)
            if (!Str::startsWith($avatarUrl, 'http')) {
                return asset('storage/' . $avatarUrl);
            }
            // It's a full URL (like placehold.co from seeder), use it directly
            return $avatarUrl;
        }
        
        // 2. Fallback: Use placehold.co with initials
        $name = $user ? ($user->pen_name ?: $user->name) : '?';
        $initial = mb_substr($name, 0, 1) ?: '?';
        return "https://placehold.co/100x100/A9B4D9/121828?text=" . urlencode(strtoupper($initial));
    }

    /**
     * Display the community feed page.
     */
    // *** MODIFIED: Add Request $request parameter ***
    public function index(Request $request)
    {
        // Get the current user ID if logged in
        $authUserId = Auth::id(); // Returns null if guest

        // *** NEW: Get search query from request ***
        $searchQuery = $request->input('search');

        // Start building the query
        $postQuery = Post::with([
            'author', // Eager load the author
            'reactions', // Eager load users who reacted
            'usefuls',   // Eager load users who marked as useful
            // Eager load comments and their authors, applying block filtering
            'comments' => function ($query) use ($authUserId) {
                 $query->whereNotIn('user_id', function ($subQuery) {
                     $subQuery->select('blocked_id')
                              ->from('user_blocks')
                              ->join('posts', 'posts.id', '=', 'comments.post_id')
                              ->whereColumn('user_blocks.blocker_id', '=', 'posts.user_id');
                 })
                 ->with('author')
                 ->orderBy('created_at', 'asc');
             },
        ]);

        // *** NEW: Apply search condition if query exists ***
        if ($searchQuery) {
            $postQuery->where(function ($query) use ($searchQuery) {
                // Search in post content
                $query->where('content', 'LIKE', "%{$searchQuery}%")
                      // Search in author's name or pen_name using whereHas
                      ->orWhereHas('author', function ($authorQuery) use ($searchQuery) {
                          $authorQuery->where('name', 'LIKE', "%{$searchQuery}%")
                                      ->orWhere('pen_name', 'LIKE', "%{$searchQuery}%");
                      });
            });
        }


        // Continue with ordering and limiting
        $posts = $postQuery->orderBy('created_at', 'desc')
                           ->limit(30) // Still limit results for now
                           ->get();


        // Pass posts (filtered or not) to the view
        return view('community.index', compact('posts'));
    }


        /**
     * Display a single post.
     */

    /**
     * Display a single post.
     * *** THIS IS THE FIXED FUNCTION ***
     */
    public function show(Post $post)
    {
        // 1. Eager load necessary relationships
        $post->load([
            'author',
            'reactions',
            'usefuls',
            'comments' => function ($query) use ($post) {
                 $query->whereNotIn('user_id', function ($subQuery) use ($post) {
                     $subQuery->select('blocked_id')
                              ->from('user_blocks')
                              ->where('user_blocks.blocker_id', '=', $post->user_id);
                 })
                 ->with('author')
                 ->orderBy('created_at', 'asc');
             },
        ]);

        // 2. Prepare Current User Data (Using the helper)
        $currentUserJs = null;
        if (Auth::check()) {
            $user = Auth::user();
            $currentUserJs = [
                'id' => $user->id,
                'name' => $user->pen_name ?: $user->name,
                'avatar' => $this->getCommunityUserAvatar($user), // *** FIXED ***
                'is_writer' => ($user->type === 'writer' || $user->type === 'admin'),
            ];
        }

        // 3. Prepare Single Post Data (Using the helper)
        $author = $post->author;
        if(!$author) {
            abort(404, 'Post author not found.');
        }

        $authorName = $author->pen_name ?: $author->name;
        $authorAvatar = $this->getCommunityUserAvatar($author); // *** FIXED ***

        $reactionsData = [];
        $likerNames = [];
        if($post->relationLoaded('reactions')) {
            foreach ($post->reactions as $user) {
                if (isset($user->pivot)) {
                    $reactionsData[$user->id] = $user->pivot->reaction_type;
                    $likerNames[] = $user->pen_name ?: $user->name;
                }
            }
        }

        $usefulData = [];
        $usefulUserNames = [];
        if($post->relationLoaded('usefuls')) {
            foreach ($post->usefuls as $user) {
                $usefulData[$user->id] = true;
                $usefulUserNames[] = $user->pen_name ?: $user->name;
            }
        }

        $commentsData = [];
        if($post->relationLoaded('comments')) {
            foreach ($post->comments as $comment) {
                $commentAuthor = $comment->author;
                if(!$commentAuthor) continue;
                $commentAuthorName = $commentAuthor->pen_name ?: $commentAuthor->name;
                $commentAuthorAvatar = $this->getCommunityUserAvatar($commentAuthor); // *** FIXED ***
                
                $commentsData[] = [
                    'id' => $comment->id,
                    'author' => [
                        'name' => $commentAuthorName,
                        'avatar' => $commentAuthorAvatar,
                    ],
                    'text' => $comment->content,
                    'author_id' => $comment->user_id,
                ];
            }
        }

        $jsPost = [
            'id' => $post->id,
            'author' => $authorName,
            'avatar' => $authorAvatar, // *** FIXED ***
            'timestamp' => $post->created_at->diffForHumans(),
            'content' => $post->content,
            'reactions' => json_encode($reactionsData),
            'usefulUsers' => json_encode($usefulData),
            'likerNames' => $likerNames,
            'usefulUserNames' => $usefulUserNames,
            'comments' => $commentsData,
            'is_owner' => (Auth::check() && $post->user_id === Auth::id()),
            'user_id' => $post->user_id,
        ];

        // 4. Pass the prepared data to the view
        return view('community.single-post', [
            'jsPost' => $jsPost,
            'currentUserJs' => $currentUserJs
        ]);
    }


     /**
     * จัดเก็บโพสต์ใหม่ที่สร้างโดย User
     */
    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        // 1. Authorization is handled by middleware ('auth', 'writer')

        // 2. Validate request data
        $validated = $request->validate([
            'content' => 'required|string|max:5000', // Adjust max length as needed
        ]);

        // 3. Create the post
        $post = Post::create([
            'user_id' => Auth::id(), // Use the authenticated user's ID
            'content' => $validated['content'],
        ]);

        // 4. Eager load author details for the response
        $post->load('author');

        // 5. Prepare data in the same format JS expects
        $author = $post->author;
        $displayName = $author->pen_name ?: $author->name;

        $avatar = $this->getCommunityUserAvatar($author);

        // $commentAuthorAvatar = $this->getCommunityUserAvatar($commentAuthor); 

        $jsPost = [
            'id' => $post->id,
            'author' => $displayName,
            'avatar' => $avatar,
            'timestamp' => $post->created_at->diffForHumans(),
            'content' => $post->content,
            'reactions' => json_encode([]), // New post has no reactions yet
            'usefulUsers' => json_encode([]), // New post has no usefuls yet
            'likerNames' => [],
            'usefulUserNames' => [],
            'comments' => [], // New post has no comments yet
            // *** FIXED: Explicitly set is_owner to true for the response ***
            'is_owner' => true,
            'user_id' => $post->user_id,
        ];

        // 6. Return the newly created post data as JSON
        return response()->json($jsPost);
    }

        /**
     * *** NEW ***
     * อัปเดตโพสต์ (สำหรับฟังก์ชันแก้ไข)
     */
    public function update(Request $request, Post $post)
    {
        // 1. ตรวจสอบสิทธิ์ (Authorization) - เฉพาะเจ้าของโพสต์
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // 2. ตรวจสอบข้อมูล
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        // 3. อัปเดตโพสต์
        $post->update($validated);

        // 4. ส่ง response สำเร็จกลับไป
        return response()->json(['message' => 'Post updated successfully.']);
    }

    /**
     * *** NEW ***
     * ลบโพสต์
     */
    public function destroy(Post $post)
    {
        // 1. ตรวจสอบสิทธิ์ (Authorization) - เฉพาะเจ้าของโพสต์
        if (Auth::id() !== $post->user_id) {
            // (ในอนาคต อาจเพิ่มให้ admin ลบได้)
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // 2. ลบโพสต์
        // (ระบบจะลบ comments, reactions, usefuls ที่ผูกอยู่โดยอัตโนมัติ
        // หากตั้งค่า onDelete('cascade') ใน migration)
        $post->delete();

        // 3. ส่ง response สำเร็จกลับไป
        return response()->json(['message' => 'Post deleted successfully.'], 200);
    }

    // --- 
    // --- COMMENT FUNCTIONS ---
    // ---

    /**
     * สร้างคอมเมนต์ใหม่ในโพสต์
     * *** MODIFIED FOR BLOCK LOGIC ***
     */
    public function storeComment(Request $request, Post $post)
    {
        // 1. *** NEW ***: ตรวจสอบ Logic การบล็อก
        // $post->author คือ เจ้าของโพสต์
        // Auth::user() คือ คนที่กำลังจะคอมเมนต์
        // ตรวจสอบว่า "เจ้าของโพสต์" ได้ "บล็อก" "คนที่กำลังจะคอมเมนต์" หรือไม่
        $isBlocked = $post->author->blockedUsers()->where('users.id', Auth::id())->exists();

        if ($isBlocked) {
            return response()->json(['message' => 'คุณถูกบลอกจากผู้เขียนโพสต์นี้'], 403); // 403 Forbidden
        }

        // 2. ตรวจสอบข้อมูล
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // 3. สร้างคอมเมนต์
        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content']
        ]);

        // 4. คืนค่าเป็น JSON ที่ JS ต้องการ
        $user = Auth::user();
        $displayName = $user->pen_name ?: $user->name;
        $initial = mb_substr($displayName, 0, 1) ?: 'C';
        $avatar = 'https://placehold.co/100x100/5DD39E/FFFFFF?text=' . urlencode($initial);

        $jsComment = [
            'id' => $comment->id, // ID จริงของคอมเมนต์
            'author' => [
                'name' => $displayName,
                'avatar' => $avatar,
            ],
            'text' => $comment->content,
            'author_id' => $comment->user_id
        ];

        return response()->json($jsComment, 201); // 201 Created
    }

    /**
     * อัปเดตคอมเมนต์
     */
    public function updateComment(Request $request, Comment $comment)
    {
        // 1. ตรวจสอบสิทธิ์ (ต้องเป็นเจ้าของคอมเมนต์เท่านั้น)
        if ($comment->user_id !== Auth::id()) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. ตรวจสอบข้อมูล
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // 3. อัปเดต
        $comment->update($validated);

        return response()->json([
            'message' => 'Comment updated successfully',
            'content' => $comment->content
        ]);
    }

    /**
     * ลบคอมเมนต์
     */
    public function destroyComment(Comment $comment)
    {
        // 1. ตรวจสอบสิทธิ์ (ต้องเป็นเจ้าของคอมเมนต์ หรือ เจ้าของโพสต์)
        $isCommentOwner = $comment->user_id === Auth::id();
        $isPostOwner = $comment->post->user_id === Auth::id();

        if (!$isCommentOwner && !$isPostOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. ลบ
        $comment->delete();

        return response()->json(null, 204); // 204 No Content
    }

    // --- 
    // --- NEW BLOCK FUNCTION ---
    // ---

    /**
     * บล็อกผู้ใช้
     * @param User $user (User ที่จะถูกบล็อก)
     */
    public function blockUser(User $user)
    {
        $blocker = Auth::user(); // ผู้ใช้ที่กดบล็อก
        $blockedUser = $user;  // ผู้ใช้ที่จะถูกบล็อก

        // 1. ป้องกันการบล็อกตัวเอง
        if ($blocker->id === $blockedUser->id) {
            return response()->json(['message' => 'ไม่สามารถบล็อกตัวเองได้'], 422); // 422 Unprocessable Entity
        }

        // 2. บันทึกการบล็อก
        // ใช้ syncWithoutDetaching() เพื่อป้องกันการ duplicate
        $blocker->blockedUsers()->syncWithoutDetaching($blockedUser->id);

        return response()->json(['message' => 'บล็อกผู้ใช้สำเร็จ'], 200);
    }

    // --- NEW FUNCTIONS ---

    public function toggleReaction(Request $request, Post $post)
    {

        $user = Auth::user();
        if (!$user) {
             return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $reactionType = $request->input('reaction_type'); // e.g., '👍', '❤️', or null

        // Validate reaction type (allow null for unliking)
        $allowedEmojis = ['👍', '❤️', '😂', '😮', '😢', '😠'];
        if ($reactionType !== null && !in_array($reactionType, $allowedEmojis)) {
             return response()->json(['error' => 'Invalid reaction type.'], 400);
        }


        $userId = $user->id;
        $postId = $post->id;

        try {
             // *** MODIFIED: Get existing reaction type BEFORE deleting ***
             $existingReactionType = PostReaction::where('post_id', $postId)
                                       ->where('user_id', $userId)
                                       ->value('reaction_type'); // Get only the type

             // *** MODIFIED: Always delete the existing reaction first using the Model ***
             PostReaction::where('post_id', $postId)->where('user_id', $userId)->delete();

             // *** MODIFIED: Only attach if reactionType is new/different (and not null) ***
             // If reactionType is null, we just wanted to delete.
             // If reactionType is the same as the one we just deleted, we also just wanted to delete.
             if ($reactionType !== null && $reactionType !== $existingReactionType) {
                  // Use the relationship to attach the new one
                  $post->reactions()->attach($userId, ['reaction_type' => $reactionType]);
             }


        } catch (\Exception $e) {
              // Log error without sensitive data if needed, but keep existing log for debugging now
              Log::error("Error toggling reaction (direct delete/attach) for post {$postId} by user {$userId}: " . $e->getMessage(), [
                   'exception' => $e,
                   'reactionType' => $reactionType,
                   'existingReactionType' => $existingReactionType ?? 'N/A' // Log the type we found before deleting
              ]);
              return response()->json(['error' => 'Could not process reaction.'], 500);
        }


        // Reload relationship to get the correct current state after modification
        $post->load('reactions');
        $reactionsData = [];
        $likerNames = [];
          if($post->relationLoaded('reactions')) {
               $reactingUsers = $post->reactions;
               foreach ($reactingUsers as $reactingUser) {
                  // Double-check pivot exists after reload
                  if(isset($reactingUser->pivot)) {
                       $reactionsData[$reactingUser->id] = $reactingUser->pivot->reaction_type;
                       $likerNames[] = $reactingUser->pen_name ?: $reactingUser->name;
                  }
               }
          }


        return response()->json([
            'reactions' => json_encode($reactionsData),
            'likerNames' => $likerNames,
        ]);
    }
/**
     * Toggle useful mark for a post. (Using Direct Delete + Attach Logic)
     */
    public function toggleUseful(Request $request, Post $post)
    {
        $user = Auth::user();
         if (!$user) {
             return response()->json(['error' => 'Unauthenticated.'], 401);
         }

         $userId = $user->id;
         $postId = $post->id;

        try {
             // *** MODIFIED: Check if the user ALREADY marked it as useful ***
             $alreadyUseful = PostUseful::where('post_id', $postId)
                                       ->where('user_id', $userId)
                                       ->exists(); // Returns true or false

             // *** MODIFIED: Always delete the existing record first (if it exists) ***
             // This simplifies the logic: if it exists, delete it. If not, this does nothing.
             PostUseful::where('post_id', $postId)->where('user_id', $userId)->delete();

             // *** MODIFIED: Only attach if the user HADN'T marked it as useful before ***
             // If they had marked it, the delete() above handles the "toggle off".
             // If they hadn't marked it, we need to attach to "toggle on".
             if (!$alreadyUseful) {
                  // Use the relationship to attach
                  $post->usefuls()->attach($userId);
             }

         } catch (\Exception $e) {
             Log::error("Error toggling useful (direct delete/attach) for post {$postId} by user {$userId}: " . $e->getMessage(), ['exception' => $e]);
             return response()->json(['error' => 'Could not process useful action.'], 500);
         }


        // Return updated useful state for the post
        $post->load('usefuls'); // Reload usefuls relationship
        $usefulData = [];
        $usefulUserNames = [];
         if($post->relationLoaded('usefuls')) {
             $usefulUsersCollection = $post->usefuls;
             foreach ($usefulUsersCollection as $usefulUser) {
                 // No pivot data needed here, just the user ID and name
                 $usefulData[$usefulUser->id] = true;
                 $usefulUserNames[] = $usefulUser->pen_name ?: $usefulUser->name;
             }
         }

        return response()->json([
            'usefulUsers' => json_encode($usefulData),
            'usefulUserNames' => $usefulUserNames,
        ]);
    }

    /**
     * *** NEW FUNCTION ***
     * จัดเก็บการรายงานโพสต์
     */
    public function storeReport(Request $request, Post $post)
    {
        // 1. ตรวจสอบสิทธิ์ (ต้อง Login)
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        $userId = Auth::id();
        $postId = $post->id;
        
        // 2. ป้องกันการรายงานซ้ำ
        $existingReport = PostReport::where('post_id', $postId)
                                    ->where('user_id', $userId)
                                    ->first();

        if ($existingReport) {
            // ส่ง HTTP Status 409 Conflict เพื่อบอกว่ามีการรายงานไปแล้ว
            return response()->json(['message' => 'คุณเคยรายงานโพสต์นี้ไปแล้ว'], 409); 
        }
        
        // 3. บันทึกการรายงานด้วย Model ที่สร้างใหม่
        PostReport::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'status' => 'pending', // สถานะเริ่มต้น
            // หากมีเหตุผลหรือประเภทอื่น ๆ เพิ่มเติม ก็สามารถใส่ตรงนี้ได้
        ]);

        // 4. ส่ง Response
        return response()->json(['message' => 'รายงานถูกส่งไปยังผู้ดูแลระบบเรียบร้อยแล้ว'], 201);
    }
}
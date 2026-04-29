<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\DocumentTypeController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\MailerController;
use App\Http\Controllers\API\ActivityLogsController;
use App\Http\Controllers\API\JobDetailsController;
use App\Http\Controllers\API\AssignedPeopleController;
use App\Http\Controllers\API\ClientUserController;
use App\Http\Controllers\API\ClientUserDashboardController;
use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\API\AssigneeDashboardController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\ExpenseHeadController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\JobExpenseClaimController;
use App\Http\Controllers\API\FcmTokenController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('admin')->group(function () {
    Route::post('/login',  [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout'])->middleware('check.role:admin');
});

// -------------------- ASSIGNEE AUTH --------------------
Route::prefix('assignedpeople')->group(function () {
    Route::post('/login',  [AssignedPeopleController::class, 'login']);   // /api/assignedpeople/login
    Route::post('/logout', [AssignedPeopleController::class, 'logout']);  // /api/assignedpeople/logout
   Route::get('/me', [AssignedPeopleController::class, 'me'])->middleware('check.role:assignee,admin');
    // NEW: Assignee's own jobs
    // Route::get('/my-jobs', [JobDetailsController::class, 'myJobs']);

});

Route::prefix('client-users')->group(function () {
    Route::post('/login', [ClientUserController::class, 'login']);
    Route::post('/logout', [ClientUserController::class, 'logout'])->middleware('check.role:client_user');
    Route::get('/me', [ClientUserController::class, 'me'])->middleware('check.role:client_user');
});


// READ (admin or user)
Route::middleware('check.role:admin,assignee,client_user')->group(function () {
    Route::get('/clients',        [ClientController::class, 'index']);
    Route::get('/clients/all',    [ClientController::class, 'all']);
    Route::get('/clients/{slug}', [ClientController::class, 'show']);
    Route::get('/clients/by-id/{id}', [ClientController::class, 'showById'])->whereNumber('id');
});

// WRITE (admin only)
Route::middleware('check.role:admin,assignee')->group(function () {
    Route::post('/clients', [ClientController::class, 'store']);

    Route::put   ('/clients/{slug}',            [ClientController::class, 'update']);
    Route::patch ('/clients/{slug}/toggle',     [ClientController::class, 'toggle']);
    Route::delete('/clients/{slug}',            [ClientController::class, 'destroy'])
        ->where('slug', '[a-hjkmnpqrstuvwxyz23456789]{8,32}');

    Route::put   ('/clients/by-id/{id}',        [ClientController::class, 'updateById'])->whereNumber('id');
    Route::patch ('/clients/by-id/{id}/toggle', [ClientController::class, 'toggleById'])->whereNumber('id');
    Route::delete('/clients/by-id/{id}',        [ClientController::class, 'destroyById'])->whereNumber('id');
});


// READ (admin or user)
Route::prefix('doctypes')->middleware('check.role:admin,assignee')->group(function () {
    Route::get('/',    [DocumentTypeController::class, 'index']);
    Route::get('{id}', [DocumentTypeController::class, 'show']);
});

// WRITE (admin only)
Route::prefix('doctypes')->middleware('check.role:admin')->group(function () {
    Route::post('/',            [DocumentTypeController::class, 'store']);
    Route::put('{id}',          [DocumentTypeController::class, 'update']);
    Route::patch('{id}',        [DocumentTypeController::class, 'update']); // partial update
    Route::delete('{id}',       [DocumentTypeController::class, 'destroy']);
    Route::patch('{id}/toggle', [DocumentTypeController::class, 'toggleStatus']);
});

/**
 * Documents
 * - GET (index/show/slug) -> admin or user
 * - create/update/delete/upload -> admin only (tight by default)
 */

// READ (admin or user)
Route::prefix('documents')->middleware('check.role:admin,assignee')->group(function () {
    Route::get('/',             [DocumentController::class, 'index']);          // list + filter/sort/search
    Route::get('{id}',          [DocumentController::class, 'show']);           // read by id
    Route::get('slug/{slug}',   [DocumentController::class, 'showBySlug']);     // read by slug
});

// WRITE (admin only)
Route::prefix('documents')->middleware('check.role:admin,user,assignee')->group(function () {
    Route::post('/',            [DocumentController::class, 'store']);          // create
    Route::put('{id}',          [DocumentController::class, 'update']);         // update by id
    Route::patch('{id}',        [DocumentController::class, 'update']);         // partial update
    Route::delete('{id}',       [DocumentController::class, 'destroy']);        // delete by id
    Route::put('slug/{slug}',   [DocumentController::class, 'updateBySlug']);   // update by slug
    Route::patch('slug/{slug}', [DocumentController::class, 'updateBySlug']);
    Route::post('{id}/toggle-status', [DocumentController::class, 'toggleStatus']);
    Route::delete('slug/{slug}',[DocumentController::class, 'destroyBySlug']);  // delete by slug
    Route::post('/uploads',     [DocumentController::class, 'upload']);         // uploads restricted to admin
});

Route::middleware('check.role:admin,assignee')->group(function () {
    Route::get   ('/mailer',                 [MailerController::class, 'index']);
    Route::post  ('/mailer',                 [MailerController::class, 'store']);
    Route::get   ('/mailer/{id}',            [MailerController::class, 'show']);
    Route::put   ('/mailer/{id}',            [MailerController::class, 'update']);
    Route::put   ('/mailer/{id}/default',    [MailerController::class, 'setDefault']);
    Route::delete('/mailer/{id}',            [MailerController::class, 'destroy']);
});
Route::get('/activity-logs', [ActivityLogsController::class, 'index']);

Route::middleware('check.role:admin,assignee')->group(function () {
    Route::prefix('job-details')->group(function () {
        Route::get('/',                [JobDetailsController::class, 'index']);
        Route::get('/{id}',            [JobDetailsController::class, 'show'])->whereNumber('id');
 
        // 🔹 Added for UI stepper dropdowns
        Route::get('/enums',           [JobDetailsController::class, 'enums']);
 
        // 🔹 Parent typeahead
        Route::get('/parents/suggest', [JobDetailsController::class, 'suggestParents']);
 
        // 🔹 Media library (list + upload without binding to a job)
       Route::get('/media',  [JobDetailsController::class, 'listMedia']);
        Route::post('/media', [JobDetailsController::class, 'uploadLooseMedia']);
 
        // Legacy: upload media bound to a specific job (kept for edit screens)
        Route::post('/{id}/media',     [JobDetailsController::class, 'uploadDescriptionMedia'])->whereNumber('id');
    });
});
 
/* =========================================
|  WRITE OPS (admin only)
|  - Uses your CheckRole middleware alias: check.role
|========================================= */
Route::prefix('job-details')->middleware('check.role:admin,assignee')->group(function () {
    // CRUD writes
    Route::post('/',                 [JobDetailsController::class, 'store']);
    Route::put('/{id}',              [JobDetailsController::class, 'update'])->whereNumber('id');
    Route::patch('/{id}',            [JobDetailsController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}',           [JobDetailsController::class, 'destroy'])->whereNumber('id');
    Route::patch('/{id}/status',     [JobDetailsController::class, 'changeStatus'])->whereNumber('id');
    // Reorder siblings
    Route::post('/reorder',          [JobDetailsController::class, 'reorder']);

    // Media admin-only writes
    Route::delete('/media/{id}',     [JobDetailsController::class, 'deleteMedia'])->whereNumber('id');
    Route::patch('/media/{id}/attach',[JobDetailsController::class, 'attachMedia'])->whereNumber('id');
    Route::get('/{id}/document/view', [JobDetailsController::class, 'viewDocument']);

    // Assignee writes
    Route::post('/{job}/assign',     [JobDetailsController::class, 'assignPeople'])->whereNumber('job');
    Route::patch('/{job}/unassign',  [JobDetailsController::class, 'unassignPeople'])->whereNumber('job');
    Route::post('/assign-jobs-to-person/{person}', [JobDetailsController::class, 'assignJobsToPerson'])->whereNumber('person');

    // Messages admin-only delete
    Route::delete('/messages/{messageId}', [JobDetailsController::class, 'deleteMessage'])->whereNumber('messageId');

});

/* =========================================
|  READ / MIXED OPS (admin,user)
|========================================= */
Route::prefix('job-details')->middleware('check.role:admin,assignee')->group(function () {
    // Lists / reads
    Route::get('/',                 [JobDetailsController::class, 'index']);
    Route::get('/enums',            [JobDetailsController::class, 'enums']);
    Route::get('/{id}',             [JobDetailsController::class, 'show'])->whereNumber('id');

    // Parent suggest
    Route::get('/parents/suggest',  [JobDetailsController::class, 'suggestParents']);

    // Media (read + uploads that your controller allows to admin,user)
    Route::get('/media',            [JobDetailsController::class, 'listMedia']);
    Route::post('/media',           [JobDetailsController::class, 'uploadLooseMedia']);
    Route::post('/{id}/media',      [JobDetailsController::class, 'uploadDescriptionMedia'])->whereNumber('id');

    // Assignees (read)
    Route::get('/{job}/assignees',  [JobDetailsController::class, 'listAssignees'])->whereNumber('job');

    // Messages (read + create + inline image upload)
    Route::get('/{job}/messages',           [JobDetailsController::class, 'listMessages'])->whereNumber('job');
    Route::post('/{job}/messages',          [JobDetailsController::class, 'postMessage'])->whereNumber('job');
    Route::post('/{job}/messages/upload',   [JobDetailsController::class, 'uploadMessageImage'])->whereNumber('job');
          Route::get('messages/{messageId}/can-edit', [JobDetailsController::class, 'canEditMessage'])
         ->name('job-messages.can-edit');
    
    // Edit message (WhatsApp-style editing)
    Route::patch('messages/{messageId}', [JobDetailsController::class, 'editMessage'])
         ->name('job-messages.edit');
    // GET /api/job-details/{jobId}/export-chats?format=excel|pdf&rolewise=1
Route::get('/{job}/export-chats', [\App\Http\Controllers\API\JobDetailsController::class, 'exportChats']);
// export report
Route::get('/{job}/export-report', [JobDetailsController::class, 'exportJobReport']);

});

Route::prefix('job-details')->middleware('check.role:client_user')->group(function () {
    Route::get('/', [JobDetailsController::class, 'index']);
    Route::get('/enums', [JobDetailsController::class, 'enums']);
    Route::get('/{id}', [JobDetailsController::class, 'show'])->whereNumber('id');
    Route::get('/{job}/assignees', [JobDetailsController::class, 'listAssignees'])->whereNumber('job');
    Route::get('/{job}/messages', [JobDetailsController::class, 'listMessages'])->whereNumber('job');
    Route::get('messages/{messageId}/can-edit', [JobDetailsController::class, 'canEditMessage'])
        ->name('client-job-messages.can-edit');
    Route::get('/{job}/export-chats', [JobDetailsController::class, 'exportChats']);
    Route::get('/{job}/export-report', [JobDetailsController::class, 'exportJobReport']);
});

/* =========================================
|  ASSIGNEE "MY JOBS"
|  - allow user OR assignee per your controller
|========================================= */

// READ (admin or user)
Route::middleware('check.role:admin,assignee')->group(function () {
    Route::get('/assigned-people',        [AssignedPeopleController::class, 'index']);  // list + filter/sort/search
    Route::get('/assigned-people/{id}',   [AssignedPeopleController::class, 'show']);   // read by id
});

// WRITE (admin only)
Route::middleware('check.role:admin')->group(function () {
    Route::post('/assigned-people',       [AssignedPeopleController::class, 'store']);  // create
    Route::put('/assigned-people/{id}',   [AssignedPeopleController::class, 'update']); // update by id
    Route::patch('/assigned-people/{id}', [AssignedPeopleController::class, 'update']); // partial update
    Route::delete('/assigned-people/{id}',[AssignedPeopleController::class, 'destroy']); // delete by id
    Route::patch('/assigned-people/{id}/toggle', [AssignedPeopleController::class, 'toggle']); // toggle status

    Route::get('/client-users', [ClientUserController::class, 'index']);
    Route::get('/client-users/{id}', [ClientUserController::class, 'show'])->whereNumber('id');
    Route::post('/client-users', [ClientUserController::class, 'store']);
    Route::put('/client-users/{id}', [ClientUserController::class, 'update'])->whereNumber('id');
    Route::patch('/client-users/{id}', [ClientUserController::class, 'update'])->whereNumber('id');
    Route::patch('/client-users/{id}/toggle', [ClientUserController::class, 'toggle'])->whereNumber('id');
    Route::delete('/client-users/{id}', [ClientUserController::class, 'destroy'])->whereNumber('id');
});
// Admin dashboard (admin only)
Route::middleware('check.role:admin, assignee')->group(function () {
    Route::get('/admin/dashboard', \App\Http\Controllers\API\AdminDashboardController::class)
        ->name('api.admin.dashboard');
Route::get('/assignee-dashboard', [AdminDashboardController::class, 'assigneeDashboard']);
    // NEW: cursor-based recent activity for the scroller
    Route::get(
        '/admin/dashboard/recent-activity',
        [\App\Http\Controllers\API\AdminDashboardController::class, 'recentActivity']
    )->name('api.admin.dashboard.recent');
});
Route::middleware('check.role:client_user')->group(function () {
    Route::get('/client-users/dashboard', ClientUserDashboardController::class)
        ->name('api.client-users.dashboard');
});
// Route::middleware(['auth:sanctum'])->prefix('assignee')->group(function () {
//     Route::get('/dashboard', function () {
//         return view('assignee.dashboard');
//     })->name('assignee.dashboard');
    
//     Route::get('/tasks', function () {
//         return view('assignee.tasks');
//     })->name('assignee.tasks');
// });
Route::middleware(['check.role:assignee'])->group(function () {
    // My jobs for assignees
    Route::get('/assignedpeople/my-jobs', [AdminDashboardController::class, 'myJobs']);
        Route::get('/assignedpeople/my-completion-stats', [AdminDashboardController::class, 'myCompletionStats']);
        Route::get('/assignedpeople/status-stats', [AdminDashboardController::class, 'statusStats']);

});
/** ---------------- Notifications (admin & assignee) ---------------- */
Route::middleware(['check.role:admin,assignee,client_user'])->group(function () {
    // List of notifications for the actor with various filters: role, unread, type, priority, status, before_id, since_id, limit, page
    Route::get('/notifications/my', [NotificationController::class, 'my'])
        ->name('notifications.my');

    // Unread count for the actor with optional role/type/priority filters
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unreadCount');

    // Get single notification details
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])
        ->name('notifications.show');

    // Mark one notification as read/unread (body: { read: true|false, role?: string })
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.markRead');

    // Mark many notifications as read/unread (body: { ids:[], read?:bool, role?:string })
    Route::post('/notifications/mark-many-read', [NotificationController::class, 'markManyRead'])
        ->name('notifications.markManyRead');

    // Mark all notifications as read for the actor (optional role)
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');
});

Route::middleware(['check.role:admin,assignee'])->group(function () {
    Route::post('/notifications', [NotificationController::class, 'store'])
        ->name('notifications.store');
    Route::patch('/notifications/{id}/archive', [NotificationController::class, 'archive'])
        ->name('notifications.archive');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
});
Route::prefix('expense-heads')->middleware('check.role:admin,assignee')->group(function () {
        
        // GET all (list / search / filter)
        Route::get('/', [ExpenseHeadController::class, 'index'])
            ->name('api.expense-heads.index');
        
        Route::get('/all', [ExpenseHeadController::class, 'allExpenseHeads']);

        // Create new
        Route::post('/', [ExpenseHeadController::class, 'store'])
            ->name('api.expense-heads.store');

        // View single expense head
        Route::get('/{id}', [ExpenseHeadController::class, 'show'])
            ->name('api.expense-heads.show');

        // Update expense head
        Route::put('/{id}', [ExpenseHeadController::class, 'update'])
            ->name('api.expense-heads.update');

        // Delete expense head
        Route::delete('/{id}', [ExpenseHeadController::class, 'destroy'])
            ->name('api.expense-heads.destroy');

        // Toggle active/inactive status
        Route::patch('/{id}/toggle-status', [ExpenseHeadController::class, 'toggleStatus'])
            ->name('api.expense-heads.toggle');
    });
Route::prefix('job-details')->middleware('check.role:admin,assignee,client_user')->group(function () {
    Route::get('{job}/expenses', [ExpenseController::class, 'listExpenses'])
        ->name('job.expenses.list');
});

Route::prefix('job-details')->middleware('check.role:admin,assignee')->group(function () {
    Route::get('{job}/expenses/export', [ExpenseController::class, 'exportExpenses']);
    Route::post('{job}/expenses', [ExpenseController::class, 'postExpense'])
        ->name('job.expenses.create');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'updateExpense'])
        ->name('job.expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'deleteExpense'])
        ->name('job.expenses.delete');
});

Route::prefix('job-expense-claims')->middleware('check.role:admin,assignee')->group(function () {
    Route::post('/claim', [JobExpenseClaimController::class, 'claim']);
    Route::get('/my', [JobExpenseClaimController::class, 'index'])->defaults('scope', 'my');
    Route::get('/admin', [JobExpenseClaimController::class, 'index'])->defaults('scope', 'admin');
    Route::patch('/admin/{id}', [JobExpenseClaimController::class, 'adminUpdate']);
});

Route::middleware('check.role:admin,assignee')->group(function () {
    
    Route::post('/fcm/token', [FcmTokenController::class, 'store']);
    Route::post('/fcm/touch', [FcmTokenController::class, 'touch']);
    Route::post('/fcm/deactivate', [FcmTokenController::class, 'deactivate']);
    Route::delete('/fcm/token', [FcmTokenController::class, 'destroy']);
    Route::get('/fcm/my-tokens', [FcmTokenController::class, 'myTokens']);
});

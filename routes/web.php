<?php
 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

 
Route::get('/', function () {
    return view('welcome');
});


Route::get('/f/{path}', function (string $path) {
    if (str_contains($path, '..')) abort(404);
    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    $mime = 'application/octet-stream';
    try { $mime = $disk->mimeType($path) ?: $mime; } catch (\Throwable $e) {}

    $stream = $disk->readStream($path);
    return Response::stream(fn()=>fpassthru($stream), 200, [
        'Content-Type'        => $mime,
        'Cache-Control'       => 'public, max-age=31536000, immutable',
        'Content-Disposition' => 'inline; filename="'.basename($path).'"',
    ]);
})->where('path', '.*');

//login
Route::get('/admin/login', function () {
    return view('pages/users/admin/pages/common/login');
});
//dashboard
Route::get('/dashboard', function () {
    return view('pages/users/admin/pages/common/dashboard');
});
//Client
Route::get('/admin/client/add', function () {
    return view('pages/users/admin/pages/clients/addClients');
});
Route::get('/admin/client/manage', function () {
    return view('pages/users/admin/pages/clients/manageClients');
});
Route::get('/admin/client-users/manage', function () {
    return view('pages/users/admin/pages/clientUsers/manageClientUsers');
});
//Activity Logs
Route::get('/admin/logs', function () {
    return view('pages/users/admin/pages/logs/activityLogs');
});
 
// Documents
Route::get('/documents/upload', function () {
    return view('pages/users/admin/pages/documents/uploadDocuments');
});
Route::get('/admin/documents', function () {
    return view('pages/users/admin/pages/documents/manageDocuments');
})->name('admin.documents.index');
 
// Document Types
Route::get('/admin/document-types', function () {
    return view('pages/users/admin/pages/documentTypes/manage');
});
 
Route::get('/admin/document-types/create', function () {
    return view('pages/users/admin/pages/documentTypes/create');
});
 
Route::get('/admin/document-types/{id}/edit', function ($id) {
    return view('pages/users/admin/pages/documentTypes/edit', ['id' => $id]);
})->whereNumber('id');
//the below routes are working but not added in the sidebar - incase needed directly use the route url
Route::get('/admin/mailer', function () {
    return view('pages/users/admin/pages/mailer/manageMailers');
});
Route::get('/admin/jobs/add', function () {
    return view('pages/users/admin/pages/jobs/createJob');
});
Route::get('/admin/jobs/view', function () {
    return view('pages/users/admin/pages/jobs/viewJobs');
});
Route::get('/admin/jobs/edit/{id}', function ($id) {
    return view('pages.users.admin/pages/jobs/editJob', ['id' => $id]);
})->name('jobs.edit');


 Route::get('/admin/assignedpeople/manage', function () {
    return view('pages/users/admin/pages/assignedPeople/manageAssignedPeople');
});
Route::get('/assignee/login', function () {
    return view('pages/users/assignee/pages/common/login');
});
Route::get('/client-user/login', function () {
    return view('pages/users/clientUser/pages/common/login');
});
Route::get('/assignee/dashboard', function () {
    return view('pages/users/assignee/pages/common/dashboard');
});
Route::get('/client-user/dashboard', function () {
    return view('pages/users/clientUser/pages/common/dashboard');
});
 Route::get('/assignee/jobs/view', function () {
    return view('pages/users/assignee/pages/jobs/viewJobs');
});
Route::get('/client-user/jobs/view', function () {
    return view('pages/users/clientUser/pages/jobs/viewJobs');
});
// Route::get('assignee/documents/upload', function () {
//     return view('pages/users/assignee/pages/documents/uploadDocuments');
// });
Route::get('admin/notifications', function () {
    return view('pages/users/admin/pages/notifications/notificationPage');
});
Route::get('assignee/notifications', function () {
    return view('pages/users/assignee/pages/notifications/notificationPage');
});
Route::get('client-user/notifications', function () {
    return view('pages/users/clientUser/pages/notifications/notificationPage');
});
Route::get('admin/expenseHead/create', function () {
    return view('pages/users/admin/pages/expenseHead/createExpenseHead');
});
Route::get('admin/expenseHead/manage', function () {
    return view('pages/users/admin/pages/expenseHead/manageExpenseHead');
});
Route::get('job-expense/claim/manage', function () {
    return view('pages/users/admin/pages/jobs/jobExpenseClaim');
});
Route::get('job-expense/claim', function () {
    return view('pages/users/assignee/pages/jobs/jobExpenseClaim');
});

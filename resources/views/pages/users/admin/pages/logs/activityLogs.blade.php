{{-- resources/views/pages/users/admin/pages/documents/uploadDocuments.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'Activity Logs')
@section('content')
@include('modules.logs.activityLogs')
@endsection

@section('scripts')
<script>
  // On DOM ready, verify token; if missing, redirect home
  document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      // Redirect if no token found in either sessionStorage or localStorage
      window.location.href = '/';
    }
  });
</script>
@endsection
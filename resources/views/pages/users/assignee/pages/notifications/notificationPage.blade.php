{{-- resources/views/pages/users/assignee/pages/notifications/notificationPage.blade.php --}}
@extends('pages.users.assignee.layout.structure')

@section('title', 'Notifications History')
@section('content')
@include('modules.notifications.notificationPage')
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
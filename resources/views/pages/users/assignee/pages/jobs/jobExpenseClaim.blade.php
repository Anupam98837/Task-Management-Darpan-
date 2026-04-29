{{-- resources/views/pages/users/admin/pages/jobs/createJob.blade.php --}}
@extends('pages.users.assignee.layout.structure')
 
@section('title', 'Create Jobs')
@section('content')
@include('modules.jobs.jobExpenseClaim')
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
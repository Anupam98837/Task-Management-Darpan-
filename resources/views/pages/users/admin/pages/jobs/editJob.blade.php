{{-- resources/views/pages/users/admin/pages/jobs/editJob.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'Edit Job')
@section('content')
  @include('modules.jobs.editJob')
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

{{-- resources/views/pages/users/admin/pages/document-types/manage.blade.php --}}
@extends('pages.users.admin.layout.structure')
 
@section('title', 'Manage Document Types')
@section('content')
@include('modules.documentTypes.manage')
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
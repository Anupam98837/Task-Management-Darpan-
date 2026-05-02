{{-- resources/views/pages/users/admin/pages/clients/addClients.blade.php --}}
@extends('pages.users.admin.layout.structure')

@section('title', 'New Client')
@section('content')
@include('modules.clients.addClients')
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

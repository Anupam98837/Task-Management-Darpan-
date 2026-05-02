@extends('pages.users.admin.layout.structure')

@section('title', 'Manage Accountant Users')
@section('content')
@include('modules.accountantUsers.manageAccountantUsers')
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>
@endsection

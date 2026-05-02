@extends('pages.users.admin.layout.structure')

@section('title', 'Manage Client Bills')
@section('content')
@include('modules.accounting.manageClientBills')
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

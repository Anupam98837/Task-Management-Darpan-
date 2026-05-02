@extends('pages.users.admin.layout.structure')

@section('title', 'New Client Bill')
@section('content')
@include('modules.accounting.createClientBill')
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

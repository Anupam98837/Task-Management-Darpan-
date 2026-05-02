@extends('pages.users.admin.layout.structure')

@section('title', 'Create Client Bill Head')
@section('content')
@include('modules.accounting.createClientBillHead')
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

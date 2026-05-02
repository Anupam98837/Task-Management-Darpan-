@extends('pages.users.admin.layout.structure')

@section('title', 'Bill Heads')
@section('content')
@include('modules.accounting.manageClientBillHeads')
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

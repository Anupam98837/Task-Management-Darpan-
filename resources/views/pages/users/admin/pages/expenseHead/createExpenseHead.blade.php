{{-- resources/views/pages/users/admin/pages/expenseHead/createExpenseHead.blade.php --}}
@extends('pages.users.admin.layout.structure')
 
@section('title', 'Create Expense Head')
@section('content')
@include('modules.expenseHead.createExpenseHead')
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
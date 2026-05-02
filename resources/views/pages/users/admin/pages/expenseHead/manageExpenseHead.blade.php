{{-- resources/views/pages/users/admin/pages/expenseHead/manageExpenseHead.blade.php --}}
@extends('pages.users.admin.layout.structure')
 
@section('title', 'Expense Heads')
@section('content')
@include('modules.expenseHead.manageExpenseHead')
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

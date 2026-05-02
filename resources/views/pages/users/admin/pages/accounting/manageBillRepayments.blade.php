@extends('pages.users.admin.layout.structure')

@section('title', 'Repayments')

@section('content')
@php
  $repaymentRole = 'admin';
  $repaymentLoginUrl = '/admin/login';
  $repaymentBillsUrl = '/admin/accounting/client-bills';
@endphp
@include('modules.accounting.manageBillRepayments')
@endsection

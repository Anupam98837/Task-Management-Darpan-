@extends('pages.users.clientUser.layout.structure')

@php
  $portalPrefix = 'client-user';
  $portalDashboardUrl = '/client-user/dashboard';
  $portalJobsUrl = '/client-user/jobs/view';
  $portalBillsUrl = '/client-user/bills';
  $portalRepaymentsUrl = '/client-user/repayments';
  $portalDocumentsUrl = '/client-user/documents';
  $portalNotificationsUrl = '/client-user/notifications';
  $portalLoginUrl = '/client-user/login';
  $portalLogoutApi = '/api/client-users/logout';
  $portalThemeKey = 'theme:client-user';
  $portalRepaymentsLabel = 'Repayments';
  $portalRepaymentsIcon = 'fa-solid fa-money-bill-transfer';
@endphp

@section('title', 'Repayments')

@section('content')
@php
  $repaymentRole = 'client_user';
  $repaymentLoginUrl = '/client-user/login';
  $repaymentBillsUrl = '/client-user/bills';
@endphp
@include('modules.accounting.manageBillRepayments')
@endsection

@extends('pages.users.clientUser.layout.structure')

@php
  $portalPrefix = 'accountant-user';
  $portalDashboardUrl = '/accountant-user/dashboard';
  $portalJobsUrl = '/accountant-user/accounting/client-bills/create';
  $portalBillsUrl = '/accountant-user/accounting/client-bills';
  $portalRepaymentsUrl = '/accountant-user/accounting/repayments';
  $portalDocumentsUrl = '/accountant-user/accounting/bill-heads/manage';
  $portalNotificationsUrl = '/accountant-user/notifications';
  $portalLoginUrl = '/accountant-user/login';
  $portalLogoutApi = '/api/accountant-users/logout';
  $portalThemeKey = 'theme:accountant-user';
  $portalJobsLabel = 'Billing';
  $portalJobsItemLabel = 'New Bill';
  $portalJobsIcon = 'fa-solid fa-file-circle-plus';
  $portalBillsLabel = 'Bills';
  $portalBillsIcon = 'fa-solid fa-file-invoice-dollar';
  $portalRepaymentsLabel = 'Repayments';
  $portalRepaymentsIcon = 'fa-solid fa-money-bill-transfer';
  $portalDocumentsLabel = 'Bill Heads';
  $portalDocumentsIcon = 'fa-solid fa-layer-group';
  $billHeadCreateUrl = '/accountant-user/accounting/bill-heads/create';
@endphp

@section('title', 'Bill Heads')

@section('content')
@include('modules.accounting.manageClientBillHeads')
@endsection

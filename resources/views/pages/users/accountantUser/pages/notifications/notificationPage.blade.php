@extends('pages.users.clientUser.layout.structure')

@php
  $portalPrefix = 'accountant-user';
  $portalDashboardUrl = '/accountant-user/dashboard';
  $portalJobsUrl = '/accountant-user/accounting/client-bills/create';
  $portalNotificationsUrl = '/accountant-user/notifications';
  $portalLoginUrl = '/accountant-user/login';
  $portalLogoutApi = '/api/accountant-users/logout';
  $portalThemeKey = 'theme:accountant-user';
@endphp

@section('title', 'Notifications')

@section('content')
@include('modules.notifications.notificationPage')
@endsection

@extends('pages.users.assignee.layout.structure')

@php
  $portalPrefix = 'client-user';
  $portalDashboardUrl = '/client-user/dashboard';
  $portalJobsUrl = '/client-user/jobs/view';
  $portalNotificationsUrl = '/client-user/notifications';
  $portalLoginUrl = '/client-user/login';
  $portalLogoutApi = '/api/client-users/logout';
  $portalThemeKey = 'theme:client-user';
@endphp

@section('title', 'Notifications')

@section('content')
@include('modules.notifications.notificationPage')
@endsection

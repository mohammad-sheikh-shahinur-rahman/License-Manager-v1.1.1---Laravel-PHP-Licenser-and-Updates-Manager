@php
    $layout = Theme::getThemeNamespace('layouts.main');
@endphp

@extends('core/table::table')

@section('content')
    @parent
@stop

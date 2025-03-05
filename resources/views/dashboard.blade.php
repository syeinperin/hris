@extends('layouts.app')

@section('content')
    <h1>You are logged in as {{ Auth::user()->name }}.</h1>
@endsection

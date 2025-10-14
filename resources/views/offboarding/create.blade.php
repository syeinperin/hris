@extends('layouts.app')

@section('page_title','New Offboarding')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h5 class="mb-0"><i class="bi bi-plus-lg me-2"></i>New Offboarding</h5>
    </div>
    <form action="{{ route('offboarding.store') }}" method="POST">
      @csrf
      <div class="card-body">
        @include('offboarding._form', ['offboarding' => null, 'mode' => 'create'])
      </div>
      <div class="card-footer bg-white text-end">
        <a href="{{ route('offboarding.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button class="btn btn-primary"><i class="bi bi-save2 me-1"></i> Save Draft</button>
      </div>
    </form>
  </div>
</div>
@endsection

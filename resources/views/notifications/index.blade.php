@extends('layouts.app')

@section('page_title','My Notifications')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header bg-white">
          <h5 class="mb-0"><i class="bi bi-bell me-2"></i> My Notifications</h5>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush">
            @forelse($notifications as $n)
              <li class="list-group-item d-flex justify-content-between align-items-start
                             {{ is_null($n->read_at) ? 'list-group-item-warning' : '' }}">
                <div>
                  <a href="{{ route('notifications.show', $n->id) }}" class="fw-bold">
                    {{ $n->data['title'] }}
                  </a>
                  <div><small class="text-muted">{{ $n->created_at->diffForHumans() }}</small></div>
                  <div>{{ $n->data['message'] ?? '' }}</div>
                </div>
                @if(is_null($n->read_at))
                  <form method="POST" action="{{ route('notifications.markRead', $n->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link">Mark read</button>
                  </form>
                @endif
              </li>
            @empty
              <li class="list-group-item text-center text-muted">
                You have no notifications.
              </li>
            @endforelse
          </ul>
        </div>
      </div>
      <div class="mt-3">
        {{ $notifications->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

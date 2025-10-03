@extends('layouts.app')
@section('page_title','View Document')

@section('content')
<div class="container" style="max-width:960px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">{{ $document->title }}</h3>
      <small class="text-muted">
        Type: <span class="text-capitalize">{{ $document->doc_type }}</span> ·
        Version: v{{ $document->version }} ·
        Uploaded {{ $document->created_at->diffForHumans() }}
      </small>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="{{ route('mydocs.download',$document) }}"><i class="bi bi-download me-1"></i>Download</a>
      <a class="btn btn-outline-primary" href="{{ route('mydocs.edit',$document) }}"><i class="bi bi-pencil-square me-1"></i>Edit</a>
      <a class="btn btn-light" href="{{ route('mydocs.index') }}">Back</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body" style="min-height:480px">
          @php $ext = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION)); @endphp

          @if(in_array($ext,['pdf']))
            <iframe src="{{ route('public.files',$document->file_path) }}" width="100%" height="600" style="border:0;"></iframe>
          @elseif(in_array($ext,['jpg','jpeg','png']))
            <img src="{{ route('public.files',$document->file_path) }}" class="img-fluid rounded" alt="Document Preview">
          @else
            <div class="text-center text-muted py-5">
              <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
              Preview not available. Use Download instead.
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6 class="fw-semibold mb-2">Details</h6>
          <dl class="row mb-0 small">
            <dt class="col-5">Status</dt>
            <dd class="col-7">
              <span class="badge text-bg-{{ $document->status==='approved'?'success':($document->status==='rejected'?'danger':'secondary') }}">
                {{ ucfirst($document->status) }}
              </span>
            </dd>

            <dt class="col-5">Visibility</dt>
            <dd class="col-7">{{ str_replace('_',' ',ucfirst($document->visibility)) }}</dd>

            <dt class="col-5">Expires</dt>
            <dd class="col-7">
              @if($document->expires_at)
                <span class="{{ $document->isExpired() ? 'text-danger' : '' }}">
                  {{ $document->expires_at->format('M d, Y') }}
                </span>
              @else  @endif
            </dd>

            <dt class="col-5">Uploaded by</dt>
            <dd class="col-7">{{ $document->uploader?->name ?? '—' }}</dd>

            <dt class="col-5">Notes</dt>
            <dd class="col-7">{{ $document->notes ?: '—' }}</dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

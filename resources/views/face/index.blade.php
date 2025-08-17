@extends('layouts.app')

@section('page_title','Face Recognition')

@push('styles')
<style>
  .hero {
    background: linear-gradient(135deg, #26264e 0%, #3a3a84 100%);
    color:#fff; border-radius:16px; padding:26px 24px; margin-bottom:18px;
  }
  .hero h3 { margin:0 0 6px 0; font-weight:700 }
  .cards { display:grid; grid-template-columns:repeat(3,1fr); gap:16px }
  @media (max-width: 1100px){ .cards{grid-template-columns:repeat(2,1fr)} }
  @media (max-width: 700px){ .cards{grid-template-columns:1fr} }
  .cardx {
    border:1px solid #e9ecf5; border-radius:14px; padding:18px; background:#fff;
    display:flex; flex-direction:column; gap:12px; height:100%;
  }
  .cardx h5 { margin:0; font-weight:700 }
  .muted { color:#6b7380 }
  .btnx {
    display:inline-flex; align-items:center; justify-content:center; gap:10px;
    border-radius:12px; padding:12px 14px; font-weight:700; border:2px solid #3a3a84;
    background:#3a3a84; color:#fff; text-decoration:none;
  }
  .btnx.light { background:#fff; color:#3a3a84 }
  .pill { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px;
    background:#f2f4ff; color:#3a3a84; font-size:12px; font-weight:600 }
</style>
@endpush

@section('content')
<div class="container py-3">

  <div class="hero">
    <h3>Face Recognition</h3>
    <div class="muted">Enroll employees and record attendance via face matching.</div>
  </div>

  @php
    use Illuminate\Support\Facades\Route;
    $hasInternalAttendance = Route::has('face.attendance');
    $kioskUrl = route('kiosk.face');
  @endphp

  <div class="cards">
    {{-- Enrollment --}}
    <div class="cardx">
      <div class="pill"><i class="bi bi-person-plus"></i> Enrollment</div>
      <h5>Enroll Employee Faces</h5>
      <div class="muted">Capture a face template (128-D descriptor) per employee for reliable matching.</div>
      <div>
        <a class="btnx" href="{{ route('face.enroll') }}">
          <i class="bi bi-camera-video"></i> Open Enrollment
        </a>
      </div>
    </div>

    {{-- In-App Attendance (optional, if you kept /face/attendance) --}}
    <div class="cardx">
      <div class="pill"><i class="bi bi-person-badge"></i> In-App</div>
      <h5>In-App Face Attendance</h5>
      <div class="muted">Use the built-in attendance screen inside HRIS.</div>
      <div>
        @if($hasInternalAttendance)
          <a class="btnx light" href="{{ route('face.attendance') }}">
            <i class="bi bi-clipboard-check"></i> Open In-App Attendance
          </a>
        @else
          <span class="muted">In-app attendance view is disabled in this install.</span>
        @endif
      </div>
    </div>

    {{-- Public Kiosk --}}
    <div class="cardx">
      <div class="pill"><i class="bi bi-display"></i> Kiosk</div>
      <h5>Public Face Kiosk</h5>
      <div class="muted">Launch the standalone kiosk (no sidebar) for a lobby/tablet.</div>
      <div>
        <a class="btnx" href="{{ $kioskUrl }}" target="_blank" rel="noopener">
          <i class="bi bi-box-arrow-up-right"></i> Open Kiosk
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

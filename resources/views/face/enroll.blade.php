@extends('layouts.app')

@section('page_title','Face Enrollment')

@push('styles')
<style>
  :root{
    --brand:#26264e; --brand-2:#3a3a84;
    --ink:#1f2330; --muted:#6b7380;
  }
  .hero{
    background:linear-gradient(135deg,var(--brand) 0%,var(--brand-2) 100%);
    color:#fff;border-radius:16px;padding:22px 20px;margin-bottom:18px
  }
  .hero h3{margin:0 0 4px 0;font-weight:700}
  .hero .sub{opacity:.9;font-size:13px;margin:0}

  .panel{background:#fff;border:1px solid #eef0f6;border-radius:14px;padding:16px}
  .panel h5{margin:0 0 10px 0}
  .muted{color:var(--muted)}

  .stage{
    background:#f0f3f9;border-radius:14px;position:relative;overflow:hidden;
    border:1px dashed #dbe1ef;min-height:320px
  }
  .stage video,.stage canvas{width:100%;height:100%;object-fit:cover}
  .stage .overlay{
    position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(ellipse 60% 45% at 50% 45%, rgba(255,255,255,0) 60%, rgba(0,0,0,.20) 62%);
    mix-blend-mode:soft-light;
  }

  .buttons{display:flex;gap:10px;flex-wrap:wrap}
  .btn-k{display:inline-flex;align-items:center;gap:10px;font-weight:700;border-radius:12px;padding:10px 14px}
  .btn-k .spin{width:16px;height:16px;border-radius:50%;border:3px solid rgba(255,255,255,.5);border-top-color:#fff;animation:spin .8s linear infinite}
  .btn-outline-brand{border:2px solid var(--brand-2);color:var(--brand-2);background:#fff}
  .btn-brand{border:2px solid var(--brand-2);background:var(--brand-2);color:#fff}
  .btn-success-soft{border:2px solid #1e865d;background:#1e865d;color:#fff}
  .btn-k[disabled]{opacity:.6;cursor:not-allowed}
  @keyframes spin{to{transform:rotate(360deg)}}

  .chip{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600}
  .chip.info{background:rgba(58,58,132,.10);color:var(--brand-2)}
  .chip.ok{background:rgba(30,134,93,.12);color:#1e865d}
  .chip.bad{background:rgba(192,57,43,.10);color:#c0392b}

  .thumb{background:#f0f3f9;border:1px dashed #dbe1ef;border-radius:14px;height:260px;display:flex;align-items:center;justify-content:center}
  .table td,.table th{vertical-align:middle}
</style>
@endpush

@section('content')
<div class="container py-3">

  <div class="hero">
    <h3 class="mb-1">Face Enrollment</h3>
    <p class="sub mb-0">Capture a face template (128-D descriptor) and save it to an employee.</p>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form id="enrollForm" action="{{ route('face.enroll.store') }}" method="POST">
    @csrf
    <div class="row g-3 align-items-start">
      <div class="col-lg-7">
        <div class="panel mb-3">
          <h5 class="mb-2">Select Employee</h5>
          <select name="employee_id" class="form-select" required>
            <option value="">— choose —</option>
            @foreach($employees as $emp)
              <option value="{{ $emp->id }}">
                {{ $emp->employee_code }} — {{ $emp->last_name }}, {{ $emp->first_name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="panel">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-2">Live Camera</h5>
            <div id="stateChip" class="chip info">Idle</div>
          </div>

          <div class="stage">
            <video id="video" autoplay muted playsinline></video>
            <div class="overlay"></div>
          </div>

          <div id="camStatus" class="mt-2 muted">
            Click <strong>Start Camera</strong>, then center your face and press <strong>Capture Face</strong>.
          </div>

          <div class="buttons mt-2">
            <button type="button" class="btn-k btn-outline-brand" id="startCam">
              <span class="spin" id="camSpin" style="display:none"></span>
              <i class="bi bi-camera-video"></i> Start Camera
            </button>
            <button type="button" class="btn-k btn-brand" id="captureBtn" disabled>
              <span class="spin" id="capSpin" style="display:none"></span>
              <i class="bi bi-record-circle"></i> Capture Face
            </button>
            <button type="submit" class="btn-k btn-success-soft" id="saveBtn" disabled>
              <i class="bi bi-save"></i> Save Template
            </button>
          </div>

          <input type="hidden" name="descriptor" id="descriptor">
          <input type="hidden" name="image_base64" id="image_base64">
        </div>
      </div>

      <div class="col-lg-5">
        <div class="panel">
          <h5 class="mb-2">Preview</h5>
          <div class="thumb">
            <canvas id="capturePreview"></canvas>
          </div>
          <div class="small text-muted mt-2">
            Models load from <code>{{ asset('face-models') }}</code>.
          </div>
        </div>
      </div>
    </div>
  </form>

  <hr class="my-4">

  <h5 class="mb-2">Existing Templates</h5>
  <div class="table-responsive panel">
    <table class="table table-sm table-striped align-middle mb-0">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Code</th>
          <th>Enrolled At</th>
          <th>Preview</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($templates as $tpl)
        <tr>
          <td>{{ optional($tpl->employee)->last_name }}, {{ optional($tpl->employee)->first_name }}</td>
          <td>{{ optional($tpl->employee)->employee_code }}</td>
          <td>{{ $tpl->created_at->format('Y-m-d H:i') }}</td>
          <td>
            @if($tpl->image_path)
              <img src="{{ asset('storage/'.$tpl->image_path) }}" style="height:48px;border-radius:6px;">
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td class="text-end">
            <form action="{{ route('face.templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Delete this template?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i> Delete
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted">No templates yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
  const MODEL_URI = "{{ asset('face-models') }}";

  const video   = document.getElementById('video');
  const status  = document.getElementById('camStatus');
  const preview = document.getElementById('capturePreview');
  const captureBtn = document.getElementById('captureBtn');
  const startCam   = document.getElementById('startCam');
  const saveBtn    = document.getElementById('saveBtn');
  const descriptorInput = document.getElementById('descriptor');
  const imageInput = document.getElementById('image_base64');
  const stateChip  = document.getElementById('stateChip');
  const camSpin    = document.getElementById('camSpin');
  const capSpin    = document.getElementById('capSpin');

  let modelsLoaded = false;

  function setChip(kind, text){
    stateChip.className = 'chip ' + kind;
    stateChip.textContent = text;
  }

  async function loadModels() {
    if (modelsLoaded) return;
    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URI);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URI);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URI);
    modelsLoaded = true;
  }

  startCam.addEventListener('click', async () => {
    camSpin.style.display = 'inline-block';
    startCam.setAttribute('disabled','disabled');
    try {
      await loadModels();
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
      video.srcObject = stream;
      status.textContent = 'Camera ready. Center your face and click “Capture Face”.';
      setChip('info','Ready');
      captureBtn.removeAttribute('disabled');
    } catch (e) {
      status.textContent = 'Cannot access camera: ' + e.message;
      setChip('bad','Camera error');
      startCam.removeAttribute('disabled');
    } finally {
      camSpin.style.display = 'none';
    }
  });

  captureBtn.addEventListener('click', async () => {
    capSpin.style.display = 'inline-block';
    captureBtn.setAttribute('disabled','disabled');
    setChip('info','Capturing…');

    try {
      await loadModels();
      if (!video.srcObject) {
        status.textContent = 'Start the camera first.';
        setChip('bad','Camera not started');
        return;
      }

      const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.4 });
      const det = await faceapi.detectSingleFace(video, options).withFaceLandmarks().withFaceDescriptor();

      if (!det) {
        status.textContent = 'No face detected. Try better lighting and look at the camera.';
        setChip('bad','No face detected');
        saveBtn.disabled = true;
        captureBtn.removeAttribute('disabled');
        return;
      }

      // Draw preview
      const ctx = preview.getContext('2d');
      preview.width  = video.videoWidth;
      preview.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, preview.width, preview.height);

      // Save descriptor + snapshot
      descriptorInput.value = JSON.stringify(Array.from(det.descriptor));
      imageInput.value = preview.toDataURL('image/png');
      saveBtn.disabled = false;
      setChip('ok','Captured');
      status.textContent = 'Captured! Click “Save Template”.';
    } catch (e) {
      setChip('bad','Error while capturing');
      status.textContent = 'Error: ' + e.message;
    } finally {
      capSpin.style.display = 'none';
      captureBtn.removeAttribute('disabled');
    }
  });
</script>
@endpush

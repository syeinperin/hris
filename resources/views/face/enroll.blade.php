@extends('layouts.app')

@section('page_title','Face Enrollment')

@push('styles')
<style>
  :root{
    --brand:#26264e; --brand-2:#3a3a84;
    --ink:#1f2330; --muted:#6b7380; --box:#35b7ff;
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

  /* Camera stage keeps aspect and never overflows */
  .stage{
    background:#0b1527;border-radius:14px;position:relative;overflow:hidden;
    border:1px dashed #dbe1ef;min-height:320px;aspect-ratio:16/9;
  }
  .stage video,.stage canvas{width:100%;height:100%;object-fit:cover}
  #overlay{position:absolute;inset:0;pointer-events:none}

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

  /* Preview box is fixed height and hides overflow */
  .thumb{
    background:#f6f8fc;border:1px dashed #dbe1ef;border-radius:14px;
    height:280px;overflow:hidden;display:flex;align-items:center;justify-content:center
  }
  /* The only preview canvas we ever use */
  #capturePreview{width:100%;height:100%;object-fit:contain;display:block}

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
            <canvas id="overlay"></canvas>
          </div>

          <div id="camStatus" class="mt-2 muted">
            Click <strong>Start Camera</strong>, then center your face in the box and press <strong>Capture Face</strong>.
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
            <!-- We render into this SAME canvas every time -->
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
  const overlay = document.getElementById('overlay');
  const octx    = overlay.getContext('2d');

  const status  = document.getElementById('camStatus');
  const previewCanvas = document.getElementById('capturePreview');
  const pctx    = previewCanvas.getContext('2d');

  const captureBtn = document.getElementById('captureBtn');
  const startCam   = document.getElementById('startCam');
  const saveBtn    = document.getElementById('saveBtn');
  const descriptorInput = document.getElementById('descriptor');
  const imageInput = document.getElementById('image_base64');
  const stateChip  = document.getElementById('stateChip');
  const camSpin    = document.getElementById('camSpin');
  const capSpin    = document.getElementById('capSpin');

  let modelsLoaded = false;
  let trackLoop = null;
  let lastPrimary = null;
  let lastTooSmall = false;

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

  async function detectPrimaryFace(input) {
    const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 });
    const results = await faceapi
      .detectAllFaces(input, options)
      .withFaceLandmarks()
      .withFaceDescriptors();

    if (!results.length) return null;

    const w = input.videoWidth || 640;
    const h = input.videoHeight || 480;
    const cx = w/2, cy = h/2;

    let best = null, score = -Infinity;
    for (const r of results){
      const b=r.detection.box, area=b.width*b.height;
      const fx=b.x + b.width/2, fy=b.y + b.height/2;
      const dist=Math.hypot(fx-cx, fy-cy);
      const s=area - 2.5*dist;
      if (s>score){ score=s; best=r; }
    }

    const minArea = 0.10 * w * h;   // allow farther faces
    if (best.detection.box.width * best.detection.box.height < minArea) {
      best.tooSmall = true;
    }
    return best;
  }

  function roundedRect(ctx, x, y, w, h, r){
    ctx.moveTo(x+r, y);
    ctx.arcTo(x+w, y,   x+w, y+h, r);
    ctx.arcTo(x+w, y+h, x,   y+h, r);
    ctx.arcTo(x,   y+h, x,   y,   r);
    ctx.arcTo(x,   y,   x+w, y,   r);
    ctx.closePath();
  }
  function roundedOval(ctx, cx, cy, rx, ry, r){
    roundedRect(ctx, cx-rx, cy-ry, rx*2, ry*2, r);
  }

  function drawOverlay(face){
    const w = overlay.width, h = overlay.height;
    octx.clearRect(0,0,w,h);
    octx.fillStyle='rgba(0,0,0,.35)';
    octx.fillRect(0,0,w,h);

    if (!face) {
      const rx = Math.min(w,h)*0.28, ry = rx*1.25;
      octx.save();
      octx.globalCompositeOperation='destination-out';
      octx.beginPath(); roundedOval(octx, w/2, h*0.42, rx, ry, 24); octx.fill();
      octx.restore();
      octx.strokeStyle = '#ffffffaa'; octx.lineWidth=2; octx.setLineDash([8,6]);
      octx.beginPath(); roundedOval(octx, w/2, h*0.42, rx, ry, 24); octx.stroke(); octx.setLineDash([]);
      return;
    }

    const pad = Math.max(face.detection.box.width, face.detection.box.height)*0.18;
    const x = Math.max(0, face.detection.box.x - pad);
    const y = Math.max(0, face.detection.box.y - pad);
    const rw = Math.min(w - x, face.detection.box.width + pad*2);
    const rh = Math.min(h - y, face.detection.box.height + pad*2);
    const r  = Math.min(16, Math.min(rw, rh)*0.12);

    octx.save();
    octx.globalCompositeOperation='destination-out';
    octx.beginPath(); roundedRect(octx, x, y, rw, rh, r); octx.fill();
    octx.restore();

    octx.strokeStyle = face.tooSmall ? '#ff6b6b' : '#35b7ff';
    octx.lineWidth=3; octx.beginPath(); roundedRect(octx, x, y, rw, rh, r); octx.stroke();

    const label = face.tooSmall ? 'Move closer' : 'Align face';
    const tw = octx.measureText(label).width + 12, th = 22;
    octx.fillStyle = face.tooSmall ? 'rgba(255,107,107,.85)' : 'rgba(53,183,255,.85)';
    octx.fillRect(x, Math.max(0, y - th - 8), tw, th);
    octx.fillStyle = '#fff'; octx.font='600 13px ui-sans-serif, system-ui';
    octx.fillText(label, x + 6, Math.max(0, y - th - 8) + 15);
  }

  function startTracking(){
    if (trackLoop) return;
    trackLoop = setInterval(async () => {
      if (!video.srcObject) return;
      const face = await detectPrimaryFace(video);
      lastPrimary = face || null;
      lastTooSmall = !!(face && face.tooSmall);
      if (overlay.width !== (video.videoWidth||640)) {
        overlay.width  = video.videoWidth  || 640;
        overlay.height = video.videoHeight || 480;
      }
      drawOverlay(face && !face.tooSmall ? face : null);
      if (face && face.tooSmall) status.textContent = 'Move closer to the camera.';
    }, 120);
  }
  function stopTracking(){ if (trackLoop){ clearInterval(trackLoop); trackLoop=null; } octx.clearRect(0,0,overlay.width, overlay.height); }

  startCam.addEventListener('click', async () => {
    camSpin.style.display = 'inline-block';
    startCam.setAttribute('disabled','disabled');
    try {
      await loadModels();
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
        audio: false
      });
      video.srcObject = stream;
      video.onloadedmetadata = () => {
        overlay.width  = video.videoWidth  || 640;
        overlay.height = video.videoHeight || 480;
      };
      status.textContent = 'Camera ready. Center your face inside the box and click “Capture Face”.';
      setChip('info','Ready');
      captureBtn.removeAttribute('disabled');
      startTracking();
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

      // Prefer the last good detection; otherwise detect fresh
      let det = (lastPrimary && !lastTooSmall) ? lastPrimary : await detectPrimaryFace(video);
      if (!det || det.tooSmall) {
        status.textContent = det && det.tooSmall
          ? 'Move closer to the camera.'
          : 'No face detected. Try better lighting and look at the camera.';
        setChip('bad', det && det.tooSmall ? 'Too small / background' : 'No face detected');
        saveBtn.disabled = true;
        captureBtn.removeAttribute('disabled');
        return;
      }

      /* --------- SAFE PREVIEW (bounded) ---------- */
      // Draw into the fixed-size preview canvas (bounded by CSS)
      const pw = previewCanvas.clientWidth  || 480;
      const ph = previewCanvas.clientHeight || 280;
      previewCanvas.width  = pw;
      previewCanvas.height = ph;
      pctx.clearRect(0,0,pw,ph);
      pctx.drawImage(video, 0, 0, pw, ph);

      /* --------- COMPACT SNAPSHOT FOR STORAGE ---- */
      // Off-screen canvas: scaled capture (avoid giant Base64 & DOM overflow)
      const aspect = (video.videoHeight || 480) / (video.videoWidth || 640);
      const sw = 640;                        // fixed capture width
      const sh = Math.round(sw * aspect);    // keep aspect
      const off = document.createElement('canvas');
      off.width = sw; off.height = sh;
      off.getContext('2d').drawImage(video, 0, 0, sw, sh);

      // Save descriptor + compact snapshot
      descriptorInput.value = JSON.stringify(Array.from(det.descriptor));
      imageInput.value = off.toDataURL('image/jpeg', 0.9);

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

  window.addEventListener('beforeunload', stopTracking);
</script>
@endpush

@extends('layouts.app')

@section('page_title','Face Attendance')

@push('styles')
<style>
  :root{
    --brand:#26264e; --brand-2:#3a3a84;
    --ink:#1f2330; --muted:#6b7380;
    --ring:rgba(58,58,132,.22);
    --box:#35b7ff;
  }
  .hero{
    background:linear-gradient(135deg,var(--brand) 0%,var(--brand-2) 100%);
    color:#fff;border-radius:16px;padding:22px 20px;margin-bottom:18px
  }
  .hero h3{margin:0 0 4px 0;font-weight:700}
  .hero .sub{opacity:.9;font-size:13px;margin:0}
  .hero .clock{font-size:28px;font-weight:700;margin:6px 0 0 0}

  .panel{background:#fff;border:1px solid #eef0f6;border-radius:14px;padding:16px}
  .panel h5{margin:0 0 10px 0}
  .muted{color:var(--muted)}

  .stage{
    background:#0b1527;border-radius:14px;position:relative;overflow:hidden;
    border:1px dashed #dbe1ef;min-height:320px
  }
  .stage video,.stage canvas{width:100%;height:100%;object-fit:cover}
  #overlay{position:absolute;inset:0;pointer-events:none}

  .btn-inline{display:inline-flex;align-items:center;gap:10px;font-weight:700;border-radius:12px;padding:10px 14px}
  .btn-outline-brand{border:2px solid var(--brand-2);color:var(--brand-2);background:#fff}
  .btn-inline .spin{width:16px;height:16px;border-radius:50%;border:3px solid rgba(0,0,0,.15);border-top-color:var(--brand-2);animation:spin .8s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}

  .thumb{
    background:#f0f3f9;border:1px dashed #dbe1ef;border-radius:14px;
    height:220px;display:flex;align-items:center;justify-content:center;
    overflow:hidden;
  }
  /* Keep the preview canvas INSIDE the preview box */
  #attPreview{width:100%;height:100%;object-fit:contain;display:block}

  .chip{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600}
  .chip.info{background:rgba(58,58,132,.10);color:var(--brand-2)}
  .chip.ok{background:rgba(30,134,93,.12);color:#1e865d}
  .chip.bad{background:rgba(192,57,43,.10);color:#c0392b}

  .match-row{display:flex;align-items:center;gap:12px;margin-top:10px}
  .avatar{width:56px;height:56px;border-radius:50%;background:#f0f3f9;border:1px solid #e6ebf6;overflow:hidden}
  .emp .name{font-weight:700}
  .emp .meta{font-size:12px;color:var(--muted)}

  .cta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
  .btn-outline{background:#fff;color:var(--brand);border:2px solid var(--brand);font-weight:700;border-radius:12px;padding:10px 14px}
  .log{margin-top:14px;background:#fafbff;border:1px solid #eef0f6;border-radius:12px;padding:12px;min-height:130px;font-size:13px}
</style>
@endpush

@section('content')
@php
  use Illuminate\Support\Facades\Route;
  // Try your named routes; fall back to POST /attendance/log
  $attendanceAction = Route::has('attendance.logAttendance')
      ? route('attendance.logAttendance')
      : (Route::has('attendance.log')
          ? route('attendance.log')
          : url('/attendance/log'));
@endphp

<div class="container py-3">
  <div class="hero">
    <h3 class="mb-0">Face Attendance</h3>
    <p class="sub mb-1">Hold your face steady to auto-match, then record Time In/Out.</p>
    <div class="clock" id="clock">--:--:--</div>
  </div>

  <div class="row g-3">
    <!-- Camera & Snapshot -->
    <div class="col-lg-7">
      <div class="panel mb-3">
        <h5 class="mb-2">Live Camera</h5>
        <div class="stage">
          <video id="video" autoplay muted playsinline></video>
          <canvas id="overlay"></canvas>
        </div>

        <div id="camStatus" class="mt-2 muted">
          Initializing camera…
        </div>

        <!-- Fallback: only shown if autoplay camera was blocked -->
        <div id="fallbackControls" class="mt-2" style="display:none">
          <button id="enableCam" class="btn-inline btn-outline-brand">
            <span class="spin" id="camSpin" style="display:none"></span>
            <i class="bi bi-camera-video"></i> Enable Camera
          </button>
        </div>
      </div>

      <div class="panel">
        <h5 class="mb-2">Snapshot</h5>
        <div class="thumb" id="thumbBox">
          <canvas id="attPreview"></canvas>
        </div>
      </div>
    </div>

    <!-- Result & Actions -->
    <div class="col-lg-5">
      <div class="panel">
        <div id="stateChip" class="chip info">No scan yet</div>

        <div class="match-row" id="matchRow" style="display:none">
          <div class="avatar"><canvas id="avatarCanvas" width="56" height="56"></canvas></div>
          <div class="emp">
            <div class="name" id="empName"></div>
            <div class="meta" id="empMeta"></div>
          </div>
        </div>

        <div class="cta">
          {{-- Time In --}}
          <form id="timeInForm" action="{{ $attendanceAction }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_type" value="time_in">
            <input type="hidden" name="employee_code" id="empCodeIn">
            <button type="submit" class="btn-outline" id="timeInBtn" disabled>
              <i class="bi bi-box-arrow-in-right"></i> Time In
            </button>
          </form>

        {{-- Time Out --}}
          <form id="timeOutForm" action="{{ $attendanceAction }}" method="POST">
            @csrf
            <input type="hidden" name="attendance_type" value="time_out">
            <input type="hidden" name="employee_code" id="empCodeOut">
            <button type="submit" class="btn-outline" id="timeOutBtn" disabled>
              <i class="bi bi-box-arrow-right"></i> Time Out
            </button>
          </form>
        </div>

        <div class="log" id="logBox">
          <div class="text-muted">Buttons enable only after a positive face match (distance ≤ ~0.45).</div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
  // ---- Clock
  const clockEl = document.getElementById('clock');
  setInterval(() => clockEl.textContent = new Date().toLocaleTimeString(undefined,{hour12:true}), 500);

  // ---- Elements & constants
  const MODEL_URI = "{{ asset('face-models') }}";
  const video     = document.getElementById('video');
  const overlay   = document.getElementById('overlay');
  const octx      = overlay.getContext('2d');
  const preview   = document.getElementById('attPreview');
  const thumbBox  = document.getElementById('thumbBox');
  const avatarC   = document.getElementById('avatarCanvas');

  const camStatus = document.getElementById('camStatus');
  const stateChip = document.getElementById('stateChip');
  const matchRow  = document.getElementById('matchRow');
  const empName   = document.getElementById('empName');
  const empMeta   = document.getElementById('empMeta');
  const inBtn     = document.getElementById('timeInBtn');
  const outBtn    = document.getElementById('timeOutBtn');
  const empCodeIn = document.getElementById('empCodeIn');
  const empCodeOut= document.getElementById('empCodeOut');

  const logBox    = document.getElementById('logBox');

  // Fallback control (only shown if autoplay is blocked)
  const fallback  = document.getElementById('fallbackControls');
  const enableCam = document.getElementById('enableCam');
  const camSpin   = document.getElementById('camSpin');

  // ========================= Far-distance tuning =========================
  // Request higher camera resolution so smaller/far faces still have pixels.
  const CAMERA_CONSTRAINTS = {
    video: {
      facingMode: 'user',
      width:  { ideal: 1280, max: 1920 },
      height: { ideal: 720,  max: 1080 }
    },
    audio: false
  };

  // TinyFaceDetector sensitivity for small faces
  const TINY_INPUT_SIZE   = 608;   // 416 → 608 improves far-face detection
  const SCORE_THRESHOLD   = 0.35;  // lower = more sensitive

  // Accept faces down to 3% of the frame area (was 10%)
  const MIN_FACE_FRACTION = 0.03;

  // Optionally switch to SSD MobileNet (detects even smaller faces)
  // Put ssd_mobilenetv1 model files under public/face-models then set true.
  const USE_SSD = false;

  // Auto-scan behavior
  const STABILITY_FRAMES   = 6;           // frames face must remain valid
  const MATCH_COOLDOWN_MS  = 4000;        // min interval between matches
  const TRACK_FPS_MS       = 120;         // ~8 FPS tracker
  // ======================================================================

  let modelsLoaded = false;
  let trackLoop = null;             // interval id
  let stableFrames = 0;
  let lastMatchAt  = 0;
  let matchInFlight = false;

  function setChip(kind, text){
    stateChip.className = 'chip ' + kind;
    stateChip.textContent = text;
  }
  function log(line){
    const p = document.createElement('div');
    p.textContent = new Date().toLocaleTimeString() + ' — ' + line;
    logBox.appendChild(p);
    logBox.scrollTop = logBox.scrollHeight;
  }

  async function loadModels(){
    if (modelsLoaded) return;
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URI);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URI);
    if (USE_SSD) {
      await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URI);
    } else {
      await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URI);
    }
    modelsLoaded = true;
  }

  // Choose the "primary" face when several are visible
  async function detectPrimaryFace(input) {
    let results;
    if (USE_SSD) {
      const opts = new faceapi.SsdMobilenetv1Options({
        minConfidence: 0.35,  // sensitive for small faces
        maxResults: 5
      });
      results = await faceapi
        .detectAllFaces(input, opts)
        .withFaceLandmarks()
        .withFaceDescriptors();
    } else {
      const opts = new faceapi.TinyFaceDetectorOptions({
        inputSize: TINY_INPUT_SIZE,
        scoreThreshold: SCORE_THRESHOLD
      });
      results = await faceapi
        .detectAllFaces(input, opts)
        .withFaceLandmarks()
        .withFaceDescriptors();
    }

    if (!results.length) return null;

    // Prefer large + centered
    const w = input.videoWidth || input.width || 640;
    const h = input.videoHeight || input.height || 480;
    const cx = w / 2, cy = h / 2;
    let best = null; let bestScore = -Infinity;

    for (const r of results) {
      const b = r.detection.box;
      const area = b.width * b.height;
      const fx = b.x + b.width / 2;
      const fy = b.y + b.height / 2;
      const dist = Math.hypot(fx - cx, fy - cy);
      const score = area - 2.5 * dist;     // weighted center preference
      if (score > bestScore) { bestScore = score; best = r; }
    }

    // Ignore extremely small faces (background)
    const minArea = MIN_FACE_FRACTION * w * h;
    if (best.detection.box.width * best.detection.box.height < minArea) {
      best.tooSmall = true;
    }
    return best;
  }

  // Draw a “capture box” & vignette
  function drawOverlay(face) {
    const w = overlay.width, h = overlay.height;
    octx.clearRect(0,0,w,h);

    // Dim background
    octx.fillStyle = 'rgba(0,0,0,0.35)';
    octx.fillRect(0,0,w,h);

    // When no face: show centered oval guide
    if (!face) {
      const rx = Math.min(w, h) * 0.28;
      const ry = rx * 1.25;
      octx.save();
      octx.globalCompositeOperation = 'destination-out';
      octx.beginPath();
      roundedOval(octx, w/2, h*0.42, rx, ry, 24);
      octx.fill();
      octx.restore();

      // border
      octx.strokeStyle = '#ffffffaa';
      octx.lineWidth = 2;
      octx.setLineDash([8,6]);
      octx.beginPath();
      roundedOval(octx, w/2, h*0.42, rx, ry, 24);
      octx.stroke();
      octx.setLineDash([]);
      return;
    }

    // Face capture rectangle (rounded) with some padding
    const pad = Math.max(face.detection.box.width, face.detection.box.height) * 0.18;
    const x = Math.max(0, face.detection.box.x - pad);
    const y = Math.max(0, face.detection.box.y - pad);
    const rw = Math.min(w - x, face.detection.box.width + pad*2);
    const rh = Math.min(h - y, face.detection.box.height + pad*2);
    const r  = Math.min(16, Math.min(rw, rh) * 0.12);

    // Punch hole
    octx.save();
    octx.globalCompositeOperation = 'destination-out';
    octx.beginPath();
    roundedRect(octx, x, y, rw, rh, r);
    octx.fill();
    octx.restore();

    // Border
    octx.strokeStyle = face.tooSmall ? '#ff6b6b' : '#35b7ff';
    octx.lineWidth = 3;
    octx.beginPath();
    roundedRect(octx, x, y, rw, rh, r);
    octx.stroke();

    // Label
    octx.fillStyle = '#ffffff';
    octx.font = '600 13px ui-sans-serif, system-ui, -apple-system';
    const label = face.tooSmall ? 'Move closer' : 'Face detected';
    const tw = octx.measureText(label).width + 12;
    const th = 22;
    octx.fillStyle = face.tooSmall ? 'rgba(255, 107, 107, .85)' : 'rgba(53, 183, 255, .85)';
    octx.fillRect(x, Math.max(0, y - th - 8), tw, th);
    octx.fillStyle = '#fff';
    octx.fillText(label, x + 6, Math.max(0, y - th - 8) + 15);
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

  // Start a lightweight tracking loop (≈8–10 FPS)
  async function startTracking(){
    if (trackLoop) return;
    trackLoop = setInterval(async () => {
      if (!video.srcObject) return;

      const face = await detectPrimaryFace(video);

      // size overlay to video once we have dims
      const needW = video.videoWidth  || video.clientWidth  || 640;
      const needH = video.videoHeight || video.clientHeight || 480;
      if (overlay.width !== needW || overlay.height !== needH) {
        overlay.width  = needW;
        overlay.height = needH;
      }
      drawOverlay(face && !face.tooSmall ? face : null);

      // stability tracking for auto-match
      if (face && !face.tooSmall) {
        stableFrames++;
        if (!matchInFlight && (Date.now() - lastMatchAt) > MATCH_COOLDOWN_MS && stableFrames >= STABILITY_FRAMES) {
          autoMatch(face); // fire-and-forget
        }
      } else {
        stableFrames = 0;
      }
    }, TRACK_FPS_MS);
  }
  function stopTracking(){
    if (trackLoop) { clearInterval(trackLoop); trackLoop = null; }
    octx.clearRect(0,0,overlay.width, overlay.height);
  }

  // Draw the snapshot SCALED to the preview box to avoid layout overflow
  function drawSnapshotToThumb() {
    const ctx = preview.getContext('2d');
    const cw  = thumbBox.clientWidth  || 320;
    const ch  = thumbBox.clientHeight || 220;
    preview.width  = cw;
    preview.height = ch;
    ctx.drawImage(video, 0, 0, cw, ch);
  }

  async function autoMatch(det){
    try{
      matchInFlight = true;
      setChip('info','Matching…');

      // Snapshot for UI (scaled to preview box)
      drawSnapshotToThumb();

      // Avatar (center crop circle)
      const av = avatarC.getContext('2d');
      av.clearRect(0,0,avatarC.width, avatarC.height);
      av.save();
      av.beginPath(); av.arc(28,28,28,0,Math.PI*2); av.closePath(); av.clip();
      const size = Math.min(preview.width, preview.height);
      const sx = (preview.width - size)/2, sy = (preview.height - size)/2;
      av.drawImage(preview, sx, sy, size, size, 0, 0, 56, 56);
      av.restore();

      const descriptor = Array.from(det.descriptor);
      const res = await fetch('{{ route('face.match') }}', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ descriptor })
      });

      const ct = res.headers.get('content-type') || '';
      if (!res.ok || !ct.includes('application/json')) {
        const txt = await res.text();
        setChip('bad','Match error');
        log('Match error (non-JSON): ' + txt.slice(0,120) + '…');
        inBtn.disabled = outBtn.disabled = true;
        lastMatchAt = Date.now();
        return;
      }

      const data = await res.json();

      if (!data.matched){
        setChip('bad','No match');
        matchRow.style.display='none';
        inBtn.disabled = outBtn.disabled = true;
        log('No match. Distance: ' + (data.distance ?? '—'));
        lastMatchAt = Date.now();
        return;
      }

      setChip('ok','Match found');
      matchRow.style.display='flex';
      empName.textContent = data.employee.name;
      empMeta.textContent = `Code: ${data.employee.employee_code} • distance=${data.distance}`;
      empCodeIn.value  = data.employee.employee_code;
      empCodeOut.value = data.employee.employee_code;
      inBtn.disabled = outBtn.disabled = false;
      log(`Matched ${data.employee.name} (code ${data.employee.employee_code}) — distance ${data.distance}.`);
      lastMatchAt = Date.now();

    }catch(e){
      setChip('bad','Error during match');
      log('Match error: ' + e.message);
      lastMatchAt = Date.now();
    }finally{
      matchInFlight = false;
      // keep scanning; cooldown prevents spamming
    }
  }

  // Try to auto-start camera on load; if blocked, show a single button
  async function startCamera(){
    try{
      await loadModels();
      const stream = await navigator.mediaDevices.getUserMedia(CAMERA_CONSTRAINTS);
      video.srcObject = stream;
      video.onloadedmetadata = () => {
        // match overlay to actual video draw dimensions
        overlay.width  = video.videoWidth  || video.clientWidth  || 640;
        overlay.height = video.videoHeight || video.clientHeight || 480;
      };
      camStatus.textContent = 'Camera ready. Hold your face inside the box to auto-scan.';
      setChip('info','Ready to scan');
      log('Camera started.');
      startTracking();
    }catch(e){
      // Most browsers require a user gesture on HTTP. Show fallback button.
      camStatus.textContent = 'Click “Enable Camera” to begin.';
      fallback.style.display = '';
    }
  }

  // ---- Init
  document.addEventListener('DOMContentLoaded', startCamera);
  enableCam?.addEventListener('click', async () => {
    camSpin.style.display = 'inline-block';
    enableCam.setAttribute('disabled','disabled');
    try { await startCamera(); }
    finally { camSpin.style.display = 'none'; enableCam.removeAttribute('disabled'); }
  });

  // (Optional) Stop the tracking loop when leaving the page
  window.addEventListener('beforeunload', stopTracking);
</script>
@endpush

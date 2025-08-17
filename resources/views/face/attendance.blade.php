@extends('layouts.app')

@section('page_title','Face Attendance')

@push('styles')
<style>
  :root{
    --brand:#26264e; --brand-2:#3a3a84;
    --ink:#1f2330; --muted:#6b7380;
    --ring:rgba(58,58,132,.22);
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
    background:#f0f3f9;border-radius:14px;position:relative;overflow:hidden;
    border:1px dashed #dbe1ef;min-height:320px
  }
  .stage video,.stage canvas{width:100%;height:100%;object-fit:cover}
  .stage .overlay{
    position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(ellipse 60% 45% at 50% 45%, rgba(255,255,255,0) 60%, rgba(0,0,0,.20) 62%);
    mix-blend-mode:soft-light;
  }

  .buttons{display:flex;gap:10px}
  .btn-k{display:inline-flex;align-items:center;gap:10px;font-weight:700;border-radius:12px;padding:10px 14px}
  .btn-k .spin{width:16px;height:16px;border-radius:50%;border:3px solid rgba(255,255,255,.5);border-top-color:#fff;animation:spin .8s linear infinite}
  .btn-outline-brand{border:2px solid var(--brand-2);color:var(--brand-2);background:#fff}
  .btn-brand{border:2px solid var(--brand-2);background:var(--brand-2);color:#fff}
  .btn-k[disabled]{opacity:.6;cursor:not-allowed}
  @keyframes spin{to{transform:rotate(360deg)}}

  .thumb{background:#f0f3f9;border:1px dashed #dbe1ef;border-radius:14px;height:220px;display:flex;align-items:center;justify-content:center}

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
    <p class="sub mb-1">Use your camera to match an enrolled employee and record Time In/Out.</p>
    <div class="clock" id="clock">--:--:--</div>
  </div>

  <div class="row g-3">
    <!-- Camera & Snapshot -->
    <div class="col-lg-7">
      <div class="panel mb-3">
        <h5 class="mb-2">Live Camera</h5>
        <div class="stage">
          <video id="video" autoplay muted playsinline></video>
          <div class="overlay"></div>
        </div>
        <div id="camStatus" class="mt-2 muted">Click <strong>Start Camera</strong>, then <strong>Scan Face</strong>.</div>
        <div class="buttons mt-2">
          <button class="btn-k btn-outline-brand" id="startCam">
            <span class="spin" id="camSpin" style="display:none"></span>
            <i class="bi bi-camera-video"></i> Start Camera
          </button>
          <button class="btn-k btn-brand" id="scanBtn" disabled>
            <span class="spin" id="scanSpin" style="display:none"></span>
            <i class="bi bi-search"></i> Scan Face
          </button>
        </div>
      </div>

      <div class="panel">
        <h5 class="mb-2">Snapshot</h5>
        <div class="thumb">
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
  const preview   = document.getElementById('attPreview');
  const avatarC   = document.getElementById('avatarCanvas');

  const startCam  = document.getElementById('startCam');
  const scanBtn   = document.getElementById('scanBtn');
  const camStatus = document.getElementById('camStatus');

  const stateChip = document.getElementById('stateChip');
  const matchRow  = document.getElementById('matchRow');
  const empName   = document.getElementById('empName');
  const empMeta   = document.getElementById('empMeta');
  const inBtn     = document.getElementById('timeInBtn');
  const outBtn    = document.getElementById('timeOutBtn');
  const empCodeIn = document.getElementById('empCodeIn');
  const empCodeOut= document.getElementById('empCodeOut');

  const camSpin   = document.getElementById('camSpin');
  const scanSpin  = document.getElementById('scanSpin');
  const logBox    = document.getElementById('logBox');

  let modelsLoaded = false;

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
    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URI);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URI);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URI);
    modelsLoaded = true;
  }

  // ---- Camera start
  startCam.addEventListener('click', async () => {
    camSpin.style.display = 'inline-block';
    startCam.setAttribute('disabled','disabled');
    try{
      await loadModels();
      const stream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'user' }, audio:false });
      video.srcObject = stream;
      camStatus.textContent = 'Camera ready. Position your face, then press “Scan Face”.';
      scanBtn.removeAttribute('disabled');
      setChip('info','Ready to scan');
      log('Camera started.');
    }catch(e){
      camStatus.textContent = 'Cannot access camera: ' + e.message;
      log('Camera error: ' + e.message);
      startCam.removeAttribute('disabled');
    }finally{
      camSpin.style.display = 'none';
    }
  });

  // ---- Scan + match
  scanBtn.addEventListener('click', async () => {
    scanBtn.setAttribute('disabled','disabled');
    scanSpin.style.display = 'inline-block';
    setChip('info','Scanning…');
    try{
      await loadModels();
      if (!video.srcObject){ camStatus.textContent = 'Start the camera first.'; return; }

      const opts = new faceapi.TinyFaceDetectorOptions({ inputSize:416, scoreThreshold:0.4 });
      const det  = await faceapi.detectSingleFace(video, opts).withFaceLandmarks().withFaceDescriptor();
      if (!det){
        setChip('bad','No face detected');
        matchRow.style.display='none';
        inBtn.disabled = outBtn.disabled = true;
        log('No face detected.');
        return;
      }

      // Snapshot (full)
      const ctx = preview.getContext('2d');
      preview.width = video.videoWidth; preview.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, preview.width, preview.height);

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
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({ descriptor })
      });
      const data = await res.json();

      if (!data.matched){
        setChip('bad','No match');
        matchRow.style.display='none';
        inBtn.disabled = outBtn.disabled = true;
        log('No match. Distance: ' + (data.distance ?? '—'));
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

    }catch(e){
      setChip('bad','Error during scan');
      log('Scan error: ' + e.message);
    }finally{
      scanSpin.style.display = 'none';
      scanBtn.removeAttribute('disabled');
    }
  });
</script>
@endpush

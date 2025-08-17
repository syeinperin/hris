<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Face Attendance Kiosk</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Face API -->
  <script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f5f7fb;
      --card:#ffffff;
      --ink:#1f2330;
      --muted:#6b7380;
      --brand:#26264e;
      --brand-2:#3a3a84;
      --ok:#1e865d;
      --bad:#c0392b;
      --ring: rgba(56, 97, 251, .35);
      --shadow: 0 8px 30px rgba(27, 39, 94, .10);
      --radius: 16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;background:var(--bg);color:var(--ink);
      font-family:'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
    }

    .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px}
    .shell{width:100%;max-width:1100px;background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
    .bar{
      padding:22px 26px;
      background:linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
      color:#fff;text-align:center;
    }
    .bar .title{font-size:28px;font-weight:700;letter-spacing:.3px;margin:0}
    .bar .sub{opacity:.85;margin:6px 0 0 0;font-size:13px}
    .bar .clock{font-size:48px;font-weight:700;margin:6px 0 0 0}

    .content{padding:22px}
    .grid{
      display:grid;gap:22px;
      grid-template-columns: 1.1fr .9fr;
    }
    @media (max-width: 960px){
      .grid{grid-template-columns:1fr}
    }

    .panel{background:#fff;border:1px solid #eef0f6;border-radius:14px;padding:16px}
    .panel h4{margin:0 0 8px 0;font-size:16px}
    .muted{color:var(--muted)}

    .stage{
      background:#f0f3f9;border-radius:14px;display:flex;align-items:center;justify-content:center;
      position:relative;overflow:hidden;border:1px dashed #dbe1ef;
      min-height:300px;
    }
    .stage video, .stage canvas{width:100%;height:100%;object-fit:cover}
    .stage .overlay{
      position:absolute;inset:0;pointer-events:none;
      background:
        radial-gradient(ellipse 60% 45% at 50% 45%, rgba(255,255,255,.0) 60%, rgba(0,0,0,.25) 62%) center/cover no-repeat;
      mix-blend-mode:soft-light;
    }

    .buttons{display:flex;gap:12px;margin-top:12px}
    .btn{
      flex:1 1 auto;display:inline-flex;align-items:center;justify-content:center;
      gap:10px;padding:13px 16px;border-radius:12px;border:2px solid var(--brand-2);
      background:var(--brand-2);color:#fff;font-weight:700;cursor:pointer;font-size:15px;
      box-shadow:0 6px 18px rgba(58,58,132,.18);transition:transform .05s ease, box-shadow .2s ease, opacity .2s ease;
      user-select:none;-webkit-user-select:none;
    }
    .btn.secondary{background:#fff;color:var(--brand-2)}
    .btn:active{transform:translateY(1px)}
    .btn[disabled]{opacity:.55;cursor:not-allowed;box-shadow:none}

    .thumb{
      background:#f0f3f9;border:1px dashed #dbe1ef;border-radius:14px;height:220px;display:flex;align-items:center;justify-content:center
    }
    .thumb canvas{width:100%;height:100%;object-fit:cover}

    .result{
      background:#fff;border:1px solid #eef0f6;border-radius:14px;padding:16px;min-height:120px
    }
    .chip{
      display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600
    }
    .chip.ok{background:rgba(30,134,93,.1);color:var(--ok)}
    .chip.bad{background:rgba(192,57,43,.08);color:var(--bad)}
    .chip.info{background:rgba(58,58,132,.10);color:var(--brand-2)}

    .match{
      display:flex;align-items:center;gap:14px;margin-top:10px
    }
    .avatar{
      width:56px;height:56px;border-radius:50%;background:#f0f3f9;border:1px solid #e6ebf6;overflow:hidden
    }
    .emp{
      display:flex;flex-direction:column
    }
    .emp .name{font-weight:700}
    .emp .meta{font-size:12px;color:var(--muted)}

    .cta{margin-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .btn.outline{
      background:#fff;color:var(--brand);border-color:var(--brand);
    }

    .log{margin-top:16px;background:#fafbff;border:1px solid #eef0f6;border-radius:12px;padding:14px;min-height:160px}
    .log .hint{color:var(--muted);font-size:13px}

    /* spinner */
    .spin{width:18px;height:18px;border-radius:50%;border:3px solid rgba(255,255,255,.6);border-top-color:#fff;animation:spin .8s linear infinite}
    .spin.dark{border:3px solid rgba(58,58,132,.35);border-top-color:var(--brand-2)}
    @keyframes spin{to{transform:rotate(360deg)}}
  </style>
</head>
<body>
<div class="wrap">
  <div class="shell">
    <div class="bar">
      <p class="title">Face Attendance Kiosk</p>
      <p class="sub" id="date"></p>
      <p class="clock" id="clock">00:00:00</p>
    </div>

    @php
      use Illuminate\Support\Facades\Route;
      $attendanceAction = Route::has('attendance.logAttendance')
          ? route('attendance.logAttendance')
          : (Route::has('attendance.log')
              ? route('attendance.log')
              : url('/attendance/log'));
    @endphp

    <div class="content">
      <div class="grid">
        <!-- LEFT: Camera -->
        <div>
          <div class="panel">
            <h4>Live Camera</h4>
            <div class="stage" id="stage">
              <video id="video" autoplay muted playsinline></video>
              <div class="overlay"></div>
            </div>
            <div class="muted" id="camStatus" style="margin-top:8px">Click <strong>Start Camera</strong>, then <strong>Scan Face</strong>.</div>
            <div class="buttons">
              <button class="btn secondary" id="startCam"><span class="spin dark" id="camSpin" style="display:none"></span> Start Camera</button>
              <button class="btn" id="scanBtn" disabled><span class="spin" id="scanSpin" style="display:none"></span> Scan Face</button>
            </div>
          </div>

          <div class="panel" style="margin-top:16px">
            <h4>Snapshot</h4>
            <div class="thumb"><canvas id="preview"></canvas></div>
          </div>
        </div>

        <!-- RIGHT: Result + Actions -->
        <div>
          <div class="panel result">
            <div id="stateChip" class="chip info">No match yet</div>

            <div class="match" id="matchRow" style="display:none">
              <div class="avatar"><canvas id="avatarCanvas" width="56" height="56"></canvas></div>
              <div class="emp">
                <div class="name" id="empName"></div>
                <div class="meta" id="empMeta"></div>
              </div>
            </div>

            <div class="cta">
              <form id="timeInForm" action="{{ $attendanceAction }}" method="POST">
                @csrf
                <input type="hidden" name="attendance_type" value="time_in">
                <input type="hidden" name="employee_code" id="empCodeIn">
                <button type="submit" class="btn outline" id="timeInBtn" disabled>Time In</button>
              </form>

              <form id="timeOutForm" action="{{ $attendanceAction }}" method="POST">
                @csrf
                <input type="hidden" name="attendance_type" value="time_out">
                <input type="hidden" name="employee_code" id="empCodeOut">
                <button type="submit" class="btn outline" id="timeOutBtn" disabled>Time Out</button>
              </form>
            </div>

            <div class="log" id="logBox">
              <div class="hint">Buttons enable only after a positive face match. Distance threshold tuned to ~0.45 for accuracy.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Clock
  const dateEl = document.getElementById('date');
  const clockEl = document.getElementById('clock');
  function tick(){
    const now = new Date();
    dateEl.textContent = now.toLocaleDateString(undefined,{weekday:'long', year:'numeric', month:'long', day:'numeric'});
    clockEl.textContent = now.toLocaleTimeString(undefined,{hour12:true});
  }
  setInterval(tick, 500); tick();

  // Elements
  const MODEL_URI = "{{ asset('face-models') }}";
  const video   = document.getElementById('video');
  const preview = document.getElementById('preview');
  const avatarC = document.getElementById('avatarCanvas');
  const startCam= document.getElementById('startCam');
  const scanBtn = document.getElementById('scanBtn');
  const camStatus = document.getElementById('camStatus');
  const stateChip = document.getElementById('stateChip');
  const matchRow  = document.getElementById('matchRow');
  const empName   = document.getElementById('empName');
  const empMeta   = document.getElementById('empMeta');
  const timeInBtn = document.getElementById('timeInBtn');
  const timeOutBtn= document.getElementById('timeOutBtn');
  const empCodeIn = document.getElementById('empCodeIn');
  const empCodeOut= document.getElementById('empCodeOut');
  const logBox    = document.getElementById('logBox');
  const camSpin   = document.getElementById('camSpin');
  const scanSpin  = document.getElementById('scanSpin');

  let modelsLoaded = false;

  function setChip(type, text){
    stateChip.className = 'chip ' + type;
    stateChip.textContent = text;
  }
  function log(line){
    const p = document.createElement('div');
    p.style.fontSize = '13px';
    p.textContent = `${new Date().toLocaleTimeString()} — ${line}`;
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

  startCam.addEventListener('click', async () => {
    camSpin.style.display = 'inline-block';
    startCam.setAttribute('disabled', 'disabled');
    try{
      await loadModels();
      const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode:'user' }, audio:false });
      video.srcObject = stream;
      camStatus.textContent = 'Camera ready. Position your face inside the guide, then press “Scan Face”.';
      scanBtn.removeAttribute('disabled');
      setChip('info', 'Ready to scan');
      log('Camera started.');
    }catch(e){
      camStatus.textContent = 'Cannot access camera: ' + e.message;
      log('Camera error: ' + e.message);
      startCam.removeAttribute('disabled');
    }finally{
      camSpin.style.display = 'none';
    }
  });

  scanBtn.addEventListener('click', async () => {
    scanBtn.setAttribute('disabled','disabled');
    scanSpin.style.display = 'inline-block';
    setChip('info', 'Scanning…');
    try{
      await loadModels();
      if (!video.srcObject){ camStatus.textContent = 'Start the camera first.'; return; }

      const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.4 });
      const det  = await faceapi.detectSingleFace(video, opts).withFaceLandmarks().withFaceDescriptor();
      if (!det){
        setChip('bad', 'No face detected');
        matchRow.style.display='none';
        timeInBtn.disabled = timeOutBtn.disabled = true;
        log('No face detected.');
        return;
      }

      // Snapshot (full)
      const ctx = preview.getContext('2d');
      preview.width = video.videoWidth; preview.height = video.videoHeight;
      ctx.drawImage(video, 0, 0, preview.width, preview.height);

      // Avatar (crop center)
      const av = avatarC.getContext('2d');
      av.clearRect(0,0,avatarC.width, avatarC.height);
      av.save();
      av.beginPath(); av.arc(28,28,28,0,Math.PI*2); av.closePath(); av.clip();
      const size = Math.min(preview.width, preview.height);
      const sx = (preview.width - size)/2, sy = (preview.height - size)/2;
      av.drawImage(preview, sx, sy, size, size, 0, 0, 56, 56);
      av.restore();

      const descriptor = Array.from(det.descriptor);

      // Call public match endpoint
      const res = await fetch('{{ route('kiosk.face.match') }}', {
        method:'POST',
        headers:{
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ descriptor })
      });
      const data = await res.json();

      if (!data.matched){
        setChip('bad', 'No match');
        matchRow.style.display='none';
        timeInBtn.disabled = timeOutBtn.disabled = true;
        log(`No match. Distance: ${data.distance ?? '—'}`);
        return;
      }

      // Success
      setChip('ok', 'Match found');
      matchRow.style.display='flex';
      empName.textContent = data.employee.name;
      empMeta.textContent = `Code: ${data.employee.employee_code} • distance=${data.distance}`;
      empCodeIn.value = data.employee.employee_code;
      empCodeOut.value = data.employee.employee_code;
      timeInBtn.disabled = timeOutBtn.disabled = false;
      log(`Matched ${data.employee.name} (code ${data.employee.employee_code}) — distance ${data.distance}.`);

    }catch(e){
      setChip('bad','Error');
      log('Scan error: ' + e.message);
    }finally{
      scanSpin.style.display = 'none';
      scanBtn.removeAttribute('disabled');
    }
  });
</script>
</body>
</html>

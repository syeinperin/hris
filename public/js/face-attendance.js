;(async () => {
  console.log('👉 face-attendance.js starting up');

  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/weights';

  // Load models
  await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
  await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
  await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

  const video      = document.getElementById('video'),
        statusEl   = document.getElementById('status'),
        scanEl     = document.getElementById('scanIndicator'),
        distEl     = document.getElementById('distance');

  // Start camera
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;
    await video.play();
  } catch (err) {
    console.error('🚨 camera error', err);
    statusEl.textContent = '❌ Cannot access camera';
    return;
  }

  function resetUI() {
    video.classList.remove('match','nomatch');
    statusEl.className = 'fs-4 text-muted';
    statusEl.textContent = 'Position your face in front of the camera…';
    scanEl.style.visibility = 'hidden';
    distEl.textContent = '–';
  }

  // Main loop
  setInterval(async () => {
    scanEl.style.visibility = 'visible';
    statusEl.textContent     = 'Scanning…';
    statusEl.className       = 'fs-4';

    const det = await faceapi
      .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!det) return resetUI();

    let json, ok;
    try {
      const res = await fetch(window.ATT_VALIDATE, {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': window.CSRF_TOKEN,
          'Accept':'application/json'
        },
        body: JSON.stringify({ descriptor: det.descriptor })
      });
      ok   = res.ok;
      json = await res.json();
    } catch {
      return resetUI();
    }

    const distance = json.distance ?? NaN;
    distEl.textContent = isNaN(distance)
      ? '–'
      : distance.toFixed(4);

    if (!ok) {
      video.classList.add('nomatch');
      statusEl.textContent = `❌ No match`;
      statusEl.className   = 'fs-4 text-danger';
    } else {
      video.classList.add('match');
      statusEl.textContent =
        `✅ ${json.employee} – ${json.status.toUpperCase()}`;
      statusEl.className   = 'fs-4 text-success';
    }
    scanEl.style.visibility = 'hidden';
  }, 3000);

})();

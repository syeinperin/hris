document.addEventListener('DOMContentLoaded', async () => {
  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/weights';

  // 1) Load models
  await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
  await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
  await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

  // 2) Grab elements
  const video   = document.getElementById('video'),
        btn     = document.getElementById('captureBtn'),
        desc    = document.getElementById('descriptor'),
        preview = document.getElementById('preview'),
        status  = document.getElementById('enrollStatus');

  // 3) Start camera
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;
    await video.play();
  } catch (e) {
    console.error('🚨 camera error', e);
    status.textContent = '❌ Cannot access camera';
    status.classList.add('text-danger');
    return;
  }

  // 4) Capture on click
  btn.addEventListener('click', async () => {
    status.textContent = '🔍 Scanning…';
    status.classList.remove('text-muted','text-danger','text-success');

    const detection = await faceapi
      .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      status.textContent = '❌ No face detected';
      status.classList.add('text-danger');
      return;
    }

    // Draw preview
    preview.innerHTML = '';
    const canvas = faceapi.createCanvasFromMedia(video);
    canvas.getContext('2d')
          .drawImage(video, 0, 0, video.width, video.height);
    preview.appendChild(canvas);

    // Save descriptor and update UI
    desc.value = JSON.stringify(detection.descriptor);
    status.textContent = '✅ Face captured';
    status.classList.add('text-success');
  });
});

document.addEventListener('DOMContentLoaded', async () => {
  const MODEL_URL = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights';
  await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
  await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
  await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

  const video = document.getElementById('video'),
        btn   = document.getElementById('captureBtn'),
        desc  = document.getElementById('descriptor'),
        prev  = document.getElementById('preview');

  try {
    const stream = await navigator.mediaDevices.getUserMedia({video:{}});
    video.srcObject = stream;
  } catch {
    return alert('Cannot access camera');
  }

  btn.addEventListener('click', async () => {
    const det = await faceapi
      .detectSingleFace(video,new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();
    if (!det) return alert('No face detected');

    desc.value = JSON.stringify(det.descriptor);
    const canvas = faceapi.createCanvasFromMedia(video);
    canvas.getContext('2d').drawImage(video,0,0,video.width,video.height);
    prev.innerHTML = '';
    prev.appendChild(canvas);
  });
});

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Ovoz yozish (Voice2)</title>
</head>
<body>
    <h2>Ovoz yozish</h2>
    <button id="startBtn">Yozishni boshlash</button>
    <button id="stopBtn" disabled>To‘xtatish</button>
    <p id="result">Transkriptsiya natijasi shu yerda chiqadi</p>

    <script>
        let recorder;
        let chunks = [];

        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const result = document.getElementById('result');

        startBtn.onclick = async () => {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            recorder = new MediaRecorder(stream);
            chunks = [];

            recorder.ondataavailable = e => chunks.push(e.data);

            recorder.onstop = async () => {
                const blob = new Blob(chunks, { type: 'audio/webm' });
                const formData = new FormData();
                formData.append('audio', blob, 'recorded_audio.webm');

                result.textContent = 'Yuklanmoqda...';

                const response = await fetch('/voice2/upload', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                result.textContent = text;
            };

            recorder.start();
            startBtn.disabled = true;
            stopBtn.disabled = false;
        };

        stopBtn.onclick = () => {
            recorder.stop();
            startBtn.disabled = false;
            stopBtn.disabled = true;
        };
    </script>
</body>
</html>

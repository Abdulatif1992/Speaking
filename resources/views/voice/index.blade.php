@extends('layouts.app')

@section('title', 'Welcome Page')

@section('content')
    <h2>Audio Yozish</h2>
    <button id="record">🎙 Yozishni boshlash</button>
    <button id="stop" disabled>⏹ To‘xtatish</button>
    <p><strong>Natija:</strong> <span id="result"></span></p>

    <script>
        let mediaRecorder;
        let audioChunks = [];

        document.getElementById("record").onclick = async () => {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
            mediaRecorder.onstop = async () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/m4a' });
                const formData = new FormData();
                formData.append('audio', audioBlob, 'voice.m4a');

                const response = await fetch('http://127.0.0.1:5000/transcribe', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                document.getElementById('result').innerText = result.text || result.error;
            };

            mediaRecorder.start();
            document.getElementById("record").disabled = true;
            document.getElementById("stop").disabled = false;
        };

        document.getElementById("stop").onclick = () => {
            mediaRecorder.stop();
            document.getElementById("record").disabled = false;
            document.getElementById("stop").disabled = true;
        };
    </script>

@endsection
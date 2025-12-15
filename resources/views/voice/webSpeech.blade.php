@extends('layouts.app')

@section('title', 'Welcome Page')

@section('content')
    <button id="btn">🎙️ Boshlash / To‘xtatish</button>
    <div id="status">tayyor</div>
    <pre id="out" style="white-space:pre-wrap;"></pre>

    <script>
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) {
            document.getElementById('status').textContent =
            'Bu brauzer Web Speech API-ni qo‘llab-quvvatlamaydi. Iltimos Chrome/Edge ishlating.';
        } else {
            const recog = new SR();
            recog.lang = 'ja-JP';            // kerakli til: 'uz-UZ', 'ru-RU', 'en-US', 'ja-JP', ...
            recog.interimResults = false;      // oraliq natijalarni ham ko‘rsatish
            recog.continuous = true;          // uzluksiz tinglash (Chrome-da qo‘llanadi)

            let finalText = '';

            recog.onstart = () => document.getElementById('status').textContent = 'Tinglanmoqda…';
            recog.onerror = (e) => document.getElementById('status').textContent = 'Xato: ' + e.error;
            recog.onend = () => document.getElementById('status').textContent = 'to‘xtadi';

            recog.onresult = (e) => {
            let interim = '';
            for (let i = e.resultIndex; i < e.results.length; i++) {
                const res = e.results[i];
                if (res.isFinal) finalText += res[0].transcript + ' ';
                else interim += res[0].transcript + ' ';
            }
            document.getElementById('out').textContent = finalText + (interim ? '\n['+interim+']' : '');
            };

            const btn = document.getElementById('btn');
            let listening = false;
            btn.onclick = () => {
            if (!listening) { recog.start(); listening = true; btn.textContent = '⏹️ To‘xtatish'; }
            else { recog.stop(); listening = false; btn.textContent = '🎙️ Boshlash'; }
            };
        }
    </script>

@endsection
@extends('layouts.app')

@section('title', 'Assembly AI Page')

@section('content')
    <input id="file" type="file" accept="audio/*,video/*">
    <button id="send">Yuborish</button>
    <pre id="log"></pre>
    
    
    <script>
        const log = (m)=>document.getElementById('log').textContent += m+'\n';
        document.getElementById('send').onclick = async ()=> {
            const f = document.getElementById('file').files[0];
            if (!f) return alert('Audio tanlang');
            const fd = new FormData();
            fd.append('audio', f, f.name);
            // tilni ham yuboramiz (ja = yaponcha)
            fd.append('language_code', 'ja');
            const r = await fetch('/api/assemblyai/transcribe', { method:'POST', body: fd });
            const j = await r.json();
            console.log(j);
            log('transcript_id: ' + j.id);
            // polling (oddiy demo): status tekshirish
            let status = 'processing';
            while (status === 'queued' || status === 'processing') {
                await new Promise(res=>setTimeout(res, 3000));
                const rr = await fetch('/api/assemblyai/status/'+j.id);
                const jj = await rr.json();
                status = jj.status;
                log('status: '+status);
                if (status === 'completed') { log('\n=== TEXT ===\n'+jj.text); break; }
                if (status === 'error') { log('Xato: '+jj.error); break; }
            }
        };
    </script>

@endsection
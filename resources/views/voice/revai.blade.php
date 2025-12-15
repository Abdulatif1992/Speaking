<!doctype html>
<html lang="uz">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Rev AI — Test UI</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 24px; }
    .card { max-width: 720px; margin:auto; border:1px solid #e5e7eb; border-radius:16px; padding:20px; box-shadow: 0 4px 18px rgba(0,0,0,0.06);}
    .row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
    .row > * { margin: 6px 0; }
    button { padding:10px 16px; border-radius:10px; border:0; background:#111827; color:#fff; cursor:pointer; }
    button[disabled] { opacity:.5; cursor:not-allowed; }
    input[type="file"]{ padding:8px; border:1px solid #e5e7eb; border-radius:10px; background:#fff; }
    select, input[type="url"] { padding:10px; border:1px solid #e5e7eb; border-radius:10px; min-width:180px; }
    pre, textarea { width:100%; min-height:140px; padding:12px; border:1px solid #e5e7eb; border-radius:12px; background:#fafafa; white-space:pre-wrap; }
    .muted { color:#6b7280; font-size:13px; }
    .pill { display:inline-block; padding:4px 10px; border-radius:999px; background:#f3f4f6; margin-right:8px; font-size:12px; }
  </style>
</head>
<body>
  <div class="card">
    <h2>Rev AI — Laravel + Blade demo</h2>
    <p class="muted">Oddiy audio → matn (asynchronous). Avval faylni yuboramiz, so‘ngra job holatini kuzatamiz, tayyor bo‘lsa matnni olamiz.</p>

    <div class="row">
      <label class="pill">Til</label>
      <select id="language">
        <option value="ja" selected>ja — 日本語</option>
        <option value="en">en — English</option>
        <option value="uz">uz — O‘zbek</option>
        <option value="ru">ru — Русский</option>
      </select>
    </div>

    <h3>1) Fayldan transkripsiya</h3>
    <div class="row">
      <input id="file" type="file" accept="audio/*,video/*">
      <button id="btn-upload">Yuborish & Transcribe</button>
    </div>

    <h3>2) Yoki URL orqali</h3>
    <div class="row">
      <input id="media_url" type="url" placeholder="https://... (audio/video URL)">
      <button id="btn-url">URL → Transcribe</button>
    </div>

    <h3>Holat</h3>
    <pre id="statusBox">—</pre>

    <h3>Matn</h3>
    <textarea id="textBox" placeholder="Tayyor bo‘lganda shu yerda ko‘rinadi"></textarea>

    <div class="row">
      <button id="copyBtn" disabled>Nusxa olish</button>
      <button id="textBtn" onclick="fetchOnlyText()">Text olish</button>
      <span id="jobId" class="muted"></span>
    </div>
  </div>

<script>
    const el = (id) => document.getElementById(id);
    const sleep = (ms) => new Promise(r => setTimeout(r, ms));
    const logStatus = (msg) => el('statusBox').textContent = msg;

    async function createJobFromFile() {
        const f = el('file').files[0];
        if (!f) { alert('Audio tanlang'); return; }
        const fd = new FormData();
        fd.append('audio', f, f.name);
        fd.append('language', el('language').value);
        const r = await fetch('/api/revai/transcribe', { method: 'POST', body: fd });
        const j = await r.json();
        if (!r.ok) { logStatus('Xato: ' + JSON.stringify(j)); return; }
        console.log(j.id, "createJob")
        return j.id;
    }

    async function createJobFromUrl() {
        const url = el('media_url').value.trim();
        if (!url) { alert('URL kiriting'); return; }
        const r = await fetch('/api/revai/transcribe-url', {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ media_url: url, language: el('language').value })
        });
        const j = await r.json();
        if (!r.ok) { logStatus('Xato: ' + JSON.stringify(j)); return; }
        return j.id;
    }

    async function pollStatus(id) {
        el('jobId').textContent = 'Job ID: ' + id;
        let tries = 0;
        while (tries < 200) { // ~10 daqiqagacha (200 * 3s)
            const r = await fetch('/api/revai/status/' + encodeURIComponent(id));
            const j = await r.json();
            if (!r.ok) { logStatus('Status xato: ' + JSON.stringify(j)); return null; }

            const s = j.status;
            logStatus('Status: ' + s + (j.failure || j.error ? (' | ' + (j.failure || j.error)) : ''));

            if (s === 'transcribed') return id;
            if (s === 'failed' || s === 'expired' || s === 'cancelled') return null;

            await sleep(3000);
            tries++;
        }
        logStatus('Kutish vaqti tugadi');
        return null;
    }

    async function fetchText(id) {
        console.log(id, "fetchText")
        const r = await fetch('/api/revai/text/' + encodeURIComponent(id));
        const j = await r.json();
        if (!r.ok) { logStatus('Matnni olishda xato: ' + JSON.stringify(j)); return; }        
        el('textBox').value = j.text || '';
        el('copyBtn').disabled = !j.text;
    }

    el('btn-upload').onclick = async () => {
        el('textBox').value = ''; el('copyBtn').disabled = true; logStatus('Yuborilmoqda...');
        const id = await createJobFromFile();
        if (!id) return;
        logStatus('Job yaratildi: ' + id + '\nStatus kutilmoqda...');
        const ok = await pollStatus(id);
        if (ok) { await fetchText(id); logStatus('Tayyor!'); }
    };

    el('btn-url').onclick = async () => {
        el('textBox').value = ''; el('copyBtn').disabled = true; logStatus('URL yuborilmoqda...');
        const id = await createJobFromUrl();
        if (!id) return;
        logStatus('Job yaratildi: ' + id + '\nStatus kutilmoqda...');
        const ok = await pollStatus(id);
        if (ok) { await fetchText(id); logStatus('Tayyor!'); }
    };

    el('copyBtn').onclick = async () => {
        const t = el('textBox').value;
        if (!t) return;
        try { await navigator.clipboard.writeText(t); el('copyBtn').textContent = 'Nusxalandi!'; setTimeout(()=>el('copyBtn').textContent='Nusxa olish',1200); }
        catch { alert('Clipboardga yozib bo‘lmadi'); }
    };


    async function fetchOnlyText() {
        id = "U9gelrXMIwCI964V";
        console.log("kirdi")
        const r = await fetch('/api/revai/text/' + encodeURIComponent(id));
        const j = await r.json();
        console.log(r);
        console.log(j);
        if (!r.ok) { logStatus('Matnni olishda xato: ' + JSON.stringify(j)); return; }        
        el('textBox').value = j.text || '';
        el('copyBtn').disabled = !j.text;
    }

</script>
</body>
</html>

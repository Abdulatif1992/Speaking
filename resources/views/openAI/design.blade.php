<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Speaking test</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Minimal custom CSS: faqat glass + mic animatsiya uchun */
    .glass-card{
      background: rgba(15,23,42,0.55);
      border:1px solid rgba(255,255,255,0.12);
      border-radius: 22px;
      backdrop-filter: blur(10px);
      box-shadow:0 18px 50px rgba(0,0,0,0.35);
    }

    .audio-btn{
      width:48px;height:48px;border-radius:14px;
      display:grid;place-items:center;
      background: rgba(139,92,246,0.15);
      border:1px solid rgba(139,92,246,0.5);
      transition:.2s ease;
    }
    .audio-btn:hover{ transform: translateY(-2px); }

    .mic-btn{
      width:62px;height:62px;border-radius:18px;border:none;
      display:grid;place-items:center;color:white;font-size:26px;
      background: linear-gradient(135deg, #6d28d9, #0ea5e9);
      box-shadow:0 10px 26px rgba(14,165,233,.35);
      position:relative; overflow:hidden;
      transition:.2s ease;
    }
    .mic-btn:hover{ transform: translateY(-2px) scale(1.02); }

    .mic-btn.recording::before,
    .mic-btn.recording::after{
      content:"";
      position:absolute; inset:-10px;
      border-radius:22px;
      border:2px solid rgba(34,211,238,.8);
      animation: ring 1.4s ease-out infinite;
    }
    .mic-btn.recording::after{
      inset:-20px; opacity:.6; animation-delay:.55s;
    }
    @keyframes ring{
      0%{ transform:scale(.85); opacity:.95; }
      100%{ transform:scale(1.2); opacity:0; }
    }

    .wave{ display:flex; gap:4px; height:18px; align-items:flex-end; }
    .wave span{
      width:4px; height:6px; border-radius:4px;
      background: linear-gradient(180deg, #22d3ee, #8b5cf6);
      animation: wave 1s infinite ease-in-out;
      transform-origin: bottom;
    }
    .wave span:nth-child(2){ animation-delay:.1s}
    .wave span:nth-child(3){ animation-delay:.2s}
    .wave span:nth-child(4){ animation-delay:.3s}
    .wave span:nth-child(5){ animation-delay:.4s}
    @keyframes wave{
      0%,100%{ height:6px; opacity:.6; }
      50%{ height:18px; opacity:1; }
    }

    .answer-input{
      background: transparent !important;
      border:0 !important;
      min-height:110px;
      resize: vertical;
    }
  </style>
</head>

<body class="text-white">

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-9">

        <div class="glass-card bg-dark bg-opacity-75 p-3 p-md-4"">

          <!-- Top header -->
          <div class="d-flex flex-wrap align-items-start align-items-md-center justify-content-between gap-3 mb-3">
            <div>
              <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge rounded-pill text-bg-light bg-opacity-10 border border-light border-opacity-10 text-white-50 px-3 py-2">
                  <i class="bi bi-lightning-charge me-1"></i> Question
                </span>
                <span class="badge rounded-pill text-bg-light bg-opacity-10 border border-light border-opacity-10 text-white-50 px-3 py-2">
                  <i class="bi bi-clock-history me-1"></i> 1/10
                </span>                
              </div>

              <h5 class="fw-semibold mb-1">
                Answer the following question by voice:
              </h5>
              <div class="small text-white-50">
                Press the microphone to speak. When you press stop, the text will automatically be entered.
              </div>
            </div>
          </div>

          <!-- Or let Bootstrap automatically handle the layout -->
          <div class="row">
            <div class="col">
              <!-- Question box -->
              <div class="p-3 rounded-4 bg-black bg-opacity-25 border border-light border-opacity-10 mb-3">
                <div class="fw-semibold small text-white-50 mb-1">Question</div>
                <div class="lh-lg">
                  あなたの名前は何ですか？
                </div>
              </div>
            </div>
            <div class="col-1">
              <!-- audio play -->
              <button class="audio-btn text-white" type="button" aria-label="Savolni eshitish">
                <i class="bi bi-volume-up fs-4"></i>
              </button>
            </div>
          </div>

          <!-- Recording panel -->
          <div class="d-flex align-items-center justify-content-between gap-3 p-3 rounded-4 bg-black bg-opacity-25 border border-light border-opacity-10 mb-3">
            <div class="d-flex align-items-center gap-3">
              <button id="micBtn" class="mic-btn" type="button" aria-label="Mikrofon">
                <i class="bi bi-mic-fill"></i>
              </button>

              <div>
                <div class="fw-semibold">Voice recorder</div>                
                <div id="wave" class="wave d-none mt-2">
                  <span></span><span></span><span></span><span></span><span></span>
                </div>
              </div>
            </div>

            <button id="stopBtn"
              class="btn btn-outline-danger rounded-3 fw-semibold px-3">
              <i class="bi bi-stop-fill me-1"></i> Stop
            </button>
          </div>

          <!-- Answer textarea -->
          <div class="p-3 rounded-4 bg-dark bg-opacity-75 border border-light border-opacity-10">
            <form id="qaScoreForm" action="{{ route('score.qa2') }}" method="POST">
              @csrf
              <label class="form-label small text-white-50 mb-2">Your answer</label>

              <input type="hidden" name="question" value="あなたの名前は何ですか？">

              <textarea id="answerInput" name="answer"
                class="form-control answer-input text-white"
                placeholder=""></textarea>

              <div class="d-flex flex-wrap gap-2 justify-content-between mt-3">
                <div class="d-flex gap-2">
                  <button type="button" id = "eraser"
                    class="btn btn-secondary bg-opacity-10 border border-light border-opacity-10 text-white rounded-3 fw-semibold">
                    <i class="bi bi-eraser me-1"></i> Clear
                  </button>
                </div>

                <button type="submit" id="submit"
                  class="btn btn-success fw-bold rounded-4 px-4 py-2 shadow">
                  <i class="bi bi-send-fill me-2"></i> Send
                </button>
              </div>
            </form>
          </div>

        </div>

      </div>
    </div>
  </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>

    <!-- OPTIONAL DEMO JS -->
    <script>
        const micBtn = document.getElementById('micBtn');
        const stopBtn = document.getElementById('stopBtn');
        const wave = document.getElementById('wave');
        const answerInput = document.getElementById('answerInput');
        const eraser = document.getElementById('eraser');
        const submitBtn = document.getElementById('submit');
        const form = document.getElementById('qaScoreForm');

        // Web Speech API
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            hint.textContent = "Brauzeringiz WebSpeech ni qo‘llab-quvvatlamaydi (Chrome tavsiya).";
            micBtn.disabled = true;
        } else {
            const recognition = new SpeechRecognition();

            // Til (xohlaganingizcha o'zgartiring)
            recognition.lang = "ja-JP"; // 
            recognition.continuous = true;     // uzluksiz eshitsın
            recognition.interimResults = true; // vaqtinchalik natija ham chiqaradi

            let finalTranscript = "";
            let recording = false;

            micBtn.addEventListener('click', () => {
                recognition.lang = "ja-JP";   // har safar startdan oldin
                if(recording) return;
                recording = true;

                finalTranscript = "";
                answerInput.value = ""; // xohlasangiz o'chirmang

                micBtn.classList.add('recording');
                wave.classList.remove('d-none');

                recognition.start();
            });

            eraser.addEventListener('click', () => {
                answerInput.value = "";
            });

            stopBtn.addEventListener('click', () => {
            if(!recording) return;
            recording = false;

            recognition.stop();

            micBtn.classList.remove('recording');
            wave.classList.add('d-none');
            });

            recognition.onresult = (event) => {
            let interim = "";

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                finalTranscript += transcript + " ";
                } else {
                interim += transcript;
                }
            }

            // textarea ga final + interim holatni chiqaramiz
            answerInput.value = finalTranscript + interim;
            };

            recognition.onerror = (e) => {
            console.log("Speech error:", e.error);

            let msg = "Xatolik yuz berdi.";
            if(e.error === "not-allowed") msg = "Mikrofonga ruxsat berilmadi.";
            if(e.error === "no-speech") msg = "Ovoz eshitilmadi. Qayta urinib ko‘ring.";
            if(e.error === "audio-capture") msg = "Mikrofon topilmadi.";

            hint.textContent = msg;

            micBtn.classList.remove('recording');
            wave.classList.add('d-none');
            recording = false;
            };

            recognition.onend = () => {
            // stop bosilmasdan o'zi tugab qolsa
            if(recording){
                // avtomatik qayta yoqish (xohlamasangiz o‘chirib qo‘ying)
                recognition.start();
            }
            };
        }

        // submitBtn.addEventListener('click', function () {
        //   console.log("clicked")
        //     if (answerInput.value.trim() !== '') {
        //         $.ajax({
        //             url: form.action,
        //             type: "POST",
        //             data: $(form).serialize(),
        //             dataType: "json",
        //             success: function (response) {
        //                 console.log("success", response);
        //             },
        //             error: function (xhr) {
        //                 console.log("error", xhr.responseText);
        //             }
        //         });

        //     }
        // });


    </script>

</body>
</html>

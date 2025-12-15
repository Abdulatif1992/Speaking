<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gapirish imtihoni</title>
    <meta name="description" content="Practice speaking exam questions by recording your answers. Look at pictures, read questions, and improve your speaking skills.">
    
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background: linear-gradient(180deg, #f8f9fb 0%, #f0f2f5 100%);
            min-height: 100vh;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            padding: 2.5rem 1rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.75rem;
        }

        .header-subtitle {
            font-size: 1rem;
            text-align: center;
            opacity: 0.95;
            max-width: 42rem;
            margin: 0 auto;
        }

        /* Alert Styles */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            margin: 2rem 0;
            background: rgba(37, 99, 235, 0.05);
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: 1rem;
        }

        .alert-icon {
            color: #2563eb;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .alert-content {
            font-size: 0.875rem;
            color: #4b5563;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 1024px) {
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Question Card Styles */
        .question-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.2s ease;
        }

        .question-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .question-card.recording {
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
        }

        .card-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .card-image-container {
            flex-shrink: 0;
        }

        .card-image {
            width: 8rem;
            height: 8rem;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .question-card:hover .card-image img {
            transform: scale(1.1);
        }

        .card-text {
            flex: 1;
            min-width: 0;
        }

        .question-badge {
            display: inline-flex;
            align-items: center;
            background: #99b0e0;
            color: white;
            padding: 0.775rem 0.75rem;
            border-radius: 5px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            margin-right: 15px;
        }

        .audio-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;                  /* Icon va text orasidagi masofa */
            background: #0b9e1e;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
            cursor: pointer;
            transition: background 0.25s ease;
        }

        /* Play icon (CSS uchburchak) */
        .audio-badge::before {
            content: "";
            width: 0;
            height: 0;
            border-left: 8px solid white;     /* Play icon rangi */
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            transition: transform 0.25s ease, border-left-color 0.25s ease;
        }

        /* Hover effekti */
        .audio-badge:hover {
            background: #0fc12a;              /* Yangi color */
        }

        .audio-badge:hover::before {
            transform: scale(1.25);           /* Icon kattalashadi */
            border-left-color: #ffffff;       /* Icon rangi o‘zgartirish mumkin */
        }

        .question-text {
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #1a1a1a;
        }

        /* Controls */
        .card-controls {
            margin-top: 1.5rem;
        }

        .buttons-container {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 9999px;
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            font-family: inherit;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn:disabled:hover {
            transform: none;
        }

        .btn-record {
            background: #ef4444;
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .btn-record:hover:not(:disabled) {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            transform: scale(1.02);
        }

        .btn-stop {
            background: #ef4444;
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .btn-stop:hover {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            transform: scale(1.02);
        }

        .btn-rerecord {
            background: #f3f4f6;
            color: #1f2937;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-rerecord:hover {
            background: #e5e7eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transform: scale(1.02);
        }

        .btn-play {
            background: #2563eb;
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-play:hover:not(:disabled) {
            background: #1d4ed8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            transform: scale(1.02);
        }

        .btn-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Status Display */
        .status-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
        }

        .status-text {
            font-size: 0.875rem;
            font-weight: 500;
            text-align: center;
        }

        .status-ready { color: #6b7280; }
        .status-recording { color: #ef4444; }
        .status-recorded { color: #10b981; }
        .status-playing { color: #2563eb; }
        .status-error { color: #ef4444; }

        /* Recording Indicator */
        .recording-indicator {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .pulse-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
        }

        .pulse-outer {
            position: absolute;
            width: 100%;
            height: 100%;
            background: #ef4444;
            opacity: 0.2;
            border-radius: 50%;
            animation: pulse-recording 1.5s ease-in-out infinite;
        }

        .pulse-inner {
            width: 0.75rem;
            height: 0.75rem;
            background: #ef4444;
            border-radius: 50%;
        }

        .equalizer {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            height: 1.5rem;
        }

        .equalizer-bar {
            width: 0.25rem;
            background: #ef4444;
            border-radius: 9999px;
            animation: equalizer-bar 0.8s ease-in-out infinite;
        }

        .equalizer-bar:nth-child(2) { animation-delay: 0.15s; }
        .equalizer-bar:nth-child(3) { animation-delay: 0.3s; }
        .equalizer-bar:nth-child(4) { animation-delay: 0.45s; }

        /* Animations */
        @keyframes pulse-recording {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.3);
                opacity: 0.6;
            }
        }

        @keyframes equalizer-bar {
            0%, 100% {
                height: 20%;
            }
            50% {
                height: 100%;
            }
        }

        /* Footer Info */
        .footer-info {
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 3rem;
            padding-bottom: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-title {
                font-size: 2rem;
            }
            
            .header-subtitle {
                font-size: 0.875rem;
            }
            
            .card-content {
                flex-direction: column;
            }
            
            .card-image {
                width: 100%;
                height: 10rem;
            }
            
            .buttons-container {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        @media (min-width: 768px) {
            .header {
                padding: 3.5rem 1rem;
            }
            
            .header-title {
                font-size: 3rem;
            }
            
            .header-subtitle {
                font-size: 1.125rem;
            }
        }

        
    </style>

</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1 class="header-title">Suhbat imtihoni</h1>
            <p class="header-subtitle">Look at the picture, read the question, and record your answer.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Info Alert -->
            <div class="alert">
                <svg class="alert-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div class="alert-content">
                    <strong>Note:</strong> Microphone recording works best on HTTPS or localhost. Make sure to allow microphone access when prompted.
                </div>
            </div>

            <!-- Question Cards Grid -->            
            <div class="cards-grid" id="cardsContainer">
                <!-- Cards will be generated by JavaScript -->
            </div>

            <!-- Footer Info -->
            <div class="footer-info">
                <p>Practice as many times as you need. Each recording is saved independently.</p>
            </div>
        </div>
    </main>

    <script>
        // Question data
        const questions = [
            {
                id: 1,
                imageUrl: "https://picsum.photos/seed/speaking1/800/600",
                imageUrlWaiting: "gif/animation.gif",
                voiceUrl: "voice/voice1.mp3",
                question: "Look at this picture carefully. Describe what is happening, who the people are, and what they might be doing. Then explain how this scene makes you feel and whether you have ever experienced a similar situation in your own life."
            },
            {
                id: 2,
                imageUrl: "https://picsum.photos/seed/speaking2/800/600",
                imageUrlWaiting: "gif/animation.gif",
                voiceUrl: "voice/voice12.mp3",
                question: "Examine the image and tell me what emotions you can identify. What do you think the people in the picture are feeling and why? Try to support your answer with specific details you notice in the image."
            },
            {
                id: 3,
                imageUrl: "https://picsum.photos/seed/speaking3/800/600",
                imageUrlWaiting: "gif/animation.gif",
                voiceUrl: "voice/voice3.mp3",
                question: "Imagine yourself in this location. Would you like to be in this place? Explain your reasons in detail, discussing what aspects appeal to you or what concerns you might have. Include any personal experiences that relate to this type of environment."
            },
            {
                id: 4,
                imageUrl: "https://picsum.photos/seed/speaking4/800/600",
                imageUrlWaiting: "gif/animation.gif",
                voiceUrl: "voice/voice1.mp3",
                question: "Based on what you see in the picture, predict what might happen next in this situation. Use your imagination to create a short story about the moments following this scene, and explain what led you to this conclusion."
            }
        ];

        // Question card state management
        class QuestionCard {
            constructor(question, containerId) {
                this.question = question;
                this.containerId = containerId;
                this.status = 'ready'; // ready, recording, recorded, playing, error    
                this.voiceStatus = 'ready'; // ready, playing      
                this.mediaRecorder = null;
                this.audioChunks = [];
                this.audioUrl = null;
                this.audioElement = null;
                this.recordingTime = 0;
                this.timerInterval = null;                
            }
            
            render() {
                const card = document.createElement('div');
                card.className = 'question-card';
                card.id = this.containerId;
                
                card.innerHTML = `
                    <div class="card-content">
                        <div class="card-image-container">
                            <div class="card-image">
                                <img src="${this.question.imageUrl}" alt="Question ${this.question.id}" loading="lazy">
                            </div>
                        </div>
                        <div class="card-text">
                            <div class="question-badge" style="float: left;">Question ${this.question.id}</div>
                            <div class="buttons-container">${this.renderAudioButtons()}</div>
                            <p class="question-text">${this.question.question}</p>
                        </div>
                    </div>
                    <div class="card-controls">
                        <div class="buttons-container">
                            ${this.renderButtons()}
                        </div>
                        <div class="status-container">
                            ${this.renderStatus()}
                        </div>
                    </div>
                `;
                
                return card;
            }
            
            renderAudioButtons(){
                if(this.voiceStatus ==='ready'){
                    return`  
                    <div style="float: right;">
                        <button class="btn btn-play2" data-action="start-audio" ${!this.question.imageUrl ? 'disabled' : ''}>
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                            <span>Play</span>
                        </button>
                    <div/>   
                    `
                }
                else{
                    return`  
                    <div style="float: right;">
                        <button class="btn btn-stop2" data-action="stop-audio" ${!this.question.imageUrl ? 'disabled' : ''}>
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="6" y="4" width="4" height="16"></rect>
                                <rect x="14" y="4" width="4" height="16"></rect>
                            </svg>
                            <span>Pause</span>
                        </button>
                    <div/>   
                    `
                }
                
            }
            
            renderButtons() {
                if (this.status === 'ready' || this.status === 'error') {
                    return `
                        <button class="btn btn-record" data-action="start-recording" ${this.status === 'error' ? 'disabled' : ''}>
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="22"></line>
                            </svg>
                            <span>Record Answer</span>
                        </button>
                    `;
                } else if (this.status === 'recording') {
                    return `
                        <button class="btn btn-stop" data-action="stop-recording">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="6" y="6" width="12" height="12" rx="2"></rect>
                            </svg>
                            <span>Stop Recording</span>
                        </button>
                    `;
                } else {
                    return `
                        <button class="btn btn-rerecord" data-action="start-recording">
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="22"></line>
                            </svg>
                            <span>Re-record</span>
                        </button>
                        <button class="btn btn-play" data-action="toggle-playback" ${!this.audioUrl ? 'disabled' : ''}>
                            ${this.status === 'playing' 
                                ? `<svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="6" y="4" width="4" height="16"></rect>
                                    <rect x="14" y="4" width="4" height="16"></rect>
                                </svg>
                                <span>Pause</span>`
                                : `<svg class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                                <span>Play</span>`
                            }
                        </button>
                    `;
                }
            }
            
            renderStatus() {
                if (this.status === 'recording') {
                    return `
                        <div class="recording-indicator">
                            <div class="pulse-container">
                                <div class="pulse-outer"></div>
                                <div class="pulse-inner"></div>
                            </div>
                            <div class="equalizer">
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                                <div class="equalizer-bar"></div>
                            </div>
                            <span class="status-text status-recording">Recording... ${this.recordingTime}s</span>
                        </div>
                    `;
                }
                
                let statusText = '';
                let statusClass = '';
                
                switch (this.status) {
                    case 'ready':
                        statusText = 'Ready to record';
                        statusClass = 'status-ready';
                        break;
                    case 'recorded':
                        statusText = `Recorded (${this.recordingTime}s)`;
                        statusClass = 'status-recorded';
                        break;
                    case 'playing':
                        statusText = 'Playing...';
                        statusClass = 'status-playing';
                        break;
                    case 'error':
                        statusText = 'Microphone access denied';
                        statusClass = 'status-error';
                        break;
                }
                
                return `<div class="status-text ${statusClass}">${statusText}</div>`;
            }
            
            updateUI() {
                const card = document.getElementById(this.containerId);
                if (!card) return;
                
                // Update recording class
                if (this.status === 'recording') {
                    card.classList.add('recording');
                } else {
                    card.classList.remove('recording');
                }
                
                // Update buttons
                const buttonsContainer = card.querySelector('.buttons-container');
                buttonsContainer.innerHTML = this.renderButtons();
                
                // Update status
                const statusContainer = card.querySelector('.status-container');
                statusContainer.innerHTML = this.renderStatus();
                
                // Re-attach event listeners
                this.attachEventListeners();
            }
            
            attachEventListeners() {
                console.log("listener");
                const card = document.getElementById(this.containerId);
                if (!card) return;
                
                const buttons = card.querySelectorAll('[data-action]');
                buttons.forEach(button => {
                    const action = button.getAttribute('data-action');
                    button.addEventListener('click', () => this.handleAction(action));
                });
            }
            
            handleAction(action) {
                console.log("handleAction");
                console.log(action);
                switch (action) {
                    case 'start-recording':                        
                        this.startRecording();
                        this.listeningAnimation();
                        break;
                    case 'stop-recording':
                        this.resetImage();
                        this.stopRecording();                        
                        break;
                    case 'toggle-playback':
                        this.togglePlayback();
                        break;
                }
            }
            
            async startRecording() {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.mediaRecorder = new MediaRecorder(stream);
                    this.audioChunks = [];
                    
                    this.mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            this.audioChunks.push(event.data);
                        }
                    };
                    
                    this.mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                        
                        if (this.audioUrl) {
                            URL.revokeObjectURL(this.audioUrl);
                        }
                        
                        this.audioUrl = URL.createObjectURL(audioBlob);
                        this.audioElement = new Audio(this.audioUrl);
                        
                        this.audioElement.onended = () => {
                            this.status = 'recorded';
                            this.updateUI();
                        };
                        
                        stream.getTracks().forEach(track => track.stop());
                        this.status = 'recorded';
                        this.updateUI();
                    };
                    
                    this.mediaRecorder.start();
                    this.status = 'recording';
                    this.recordingTime = 0;
                    
                    this.timerInterval = setInterval(() => {
                        this.recordingTime++;
                        this.updateUI();
                    }, 1000);
                    
                    this.updateUI();
                } catch (error) {
                    console.error('Error accessing microphone:', error);
                    this.status = 'error';
                    this.updateUI();
                }
            }

            listeningAnimation(){
                const card = document.getElementById(this.containerId);
                if (!card) return;

                const img = card.querySelector('.card-image img');
                if (!img) return;

                // Asl srcni saqlab qo'yish (agar hali saqlanmagan bo'lsa)
                if (!img.dataset.originalSrc) {
                    img.dataset.originalSrc = img.src;
                }

                // Recording gifga almashtirish
                img.src = this.question.imageUrlWaiting;
            }

            resetImage() {
                const card = document.getElementById(this.containerId);
                if (!card) return;

                const img = card.querySelector('.card-image img');
                if (!img) return;

                img.src = this.question.imageUrl; 
            }
            
            stopRecording() {
                if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                    this.mediaRecorder.stop();
                    
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                        this.timerInterval = null;
                    }
                }
            }
            
            togglePlayback() {
                if (!this.audioElement) return;
                
                if (this.status === 'playing') {
                    this.audioElement.pause();
                    this.status = 'recorded';
                } else {
                    this.audioElement.play();
                    this.status = 'playing';
                }
                
                this.updateUI();
            }
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('cardsContainer');
            
            questions.forEach((question) => {
                const cardId = `question-card-${question.id}`;
                const questionCard = new QuestionCard(question, cardId);
                // 1) card elementini yaratamiz
                const cardElement = questionCard.render();

                // 2) DOMga qo‘shamiz
                container.appendChild(cardElement);

                // 3) endi DOMda bor – event listenerlarni ulaymiz
                questionCard.attachEventListeners();
            });
        });

    </script>
</body>
</html>

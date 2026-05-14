// Validation client-side
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[data-validate]');
    
    // Clear previous errors
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    
    inputs.forEach(input => {
        const rules = input.dataset.validate.split('|');
        
        for (let rule of rules) {
            if (rule === 'required' && !input.value.trim()) {
                showError(input, 'Ce champ est requis');
                isValid = false;
            }
            if (rule.startsWith('minlength:')) {
                const min = parseInt(rule.split(':')[1]);
                if (input.value.length < min) {
                    showError(input, `Minimum ${min} caractères`);
                    isValid = false;
                }
            }
            if (rule.startsWith('maxlength:')) {
                const max = parseInt(rule.split(':')[1]);
                if (input.value.length > max) {
                    showError(input, `Maximum ${max} caractères`);
                    isValid = false;
                }
            }
            if (rule === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    showError(input, 'Email invalide');
                    isValid = false;
                }
            }
        }
    });
    
    return isValid;
}

function showError(element, message) {
    let errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    element.parentNode.insertBefore(errorDiv, element.nextSibling);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Handle like/dislike buttons
    const reactionButtons = document.querySelectorAll('.btn-reaction');
    reactionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.dataset.postId;
            const type = this.dataset.type;
            
            const formData = new FormData();
            formData.append('id_post', postId);
            formData.append('type', type);
            
            fetch('index.php?action=toggle-like', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the reaction count
                    const likeButtons = document.querySelectorAll(`[data-post-id="${postId}"]`);
                    
                    likeButtons.forEach(btn => {
                        if (btn.dataset.type === 'like') {
                            btn.querySelector('.reaction-count').textContent = data.stats.likes;
                            if (data.user_reaction === 'like') {
                                btn.classList.add('active');
                            } else {
                                btn.classList.remove('active');
                            }
                        } else if (btn.dataset.type === 'dislike') {
                            btn.querySelector('.reaction-count').textContent = data.stats.dislikes;
                            if (data.user_reaction === 'dislike') {
                                btn.classList.add('active');
                            } else {
                                btn.classList.remove('active');
                            }
                        }
                    });
                } else {
                    console.error('Error:', data.errors);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});


// ============================================================
// VOICE RECORDER
// ============================================================
(function () {
    'use strict';

    // Map of recorder id -> state object
    const recorders = {};

    function getState(id) {
        if (!recorders[id]) {
            recorders[id] = {
                mediaRecorder: null,
                chunks: [],
                timerInterval: null,
                seconds: 0,
                stream: null,
                analyser: null,
                animFrame: null,
                blob: null,
            };
        }
        return recorders[id];
    }

    function initRecorder(wrapper) {
        const id = wrapper.id;
        const idle   = wrapper.querySelector('.recorder-idle');
        const active = wrapper.querySelector('.recorder-active');
        const preview = wrapper.querySelector('.recorder-preview');
        const audioEl = wrapper.querySelector('.recorder-audio-preview');
        const canvas  = wrapper.querySelector('.waveform-canvas');
        const timerEl = wrapper.querySelector('.rec-timer');
        const hiddenInput = wrapper.querySelector('.recorder-hidden-input');

        // Start recording
        wrapper.querySelector('.btn-record').addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                const state = getState(id);
                state.stream = stream;
                state.chunks = [];
                state.seconds = 0;

                // Waveform analyser
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const source = audioCtx.createMediaStreamSource(stream);
                const analyser = audioCtx.createAnalyser();
                analyser.fftSize = 256;
                source.connect(analyser);
                state.analyser = analyser;

                // MediaRecorder
                const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                    ? 'audio/webm;codecs=opus'
                    : 'audio/webm';
                const mr = new MediaRecorder(stream, { mimeType });
                state.mediaRecorder = mr;

                mr.ondataavailable = e => { if (e.data.size > 0) state.chunks.push(e.data); };
                mr.onstop = () => {
                    const blob = new Blob(state.chunks, { type: mimeType });
                    state.blob = blob;
                    const url = URL.createObjectURL(blob);
                    audioEl.src = url;

                    // Inject blob into hidden file input as a File
                    const file = new File([blob], 'voice-message.webm', { type: mimeType });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    hiddenInput.files = dt.files;

                    active.style.display = 'none';
                    preview.style.display = '';
                    idle.style.display = 'none';
                };
                mr.start(100);

                // Timer
                timerEl.textContent = '0:00';
                state.timerInterval = setInterval(() => {
                    state.seconds++;
                    const m = Math.floor(state.seconds / 60);
                    const s = state.seconds % 60;
                    timerEl.textContent = `${m}:${s.toString().padStart(2, '0')}`;
                }, 1000);

                // Waveform draw
                const ctx = canvas.getContext('2d');
                function draw() {
                    state.animFrame = requestAnimationFrame(draw);
                    const bufLen = analyser.frequencyBinCount;
                    const data = new Uint8Array(bufLen);
                    analyser.getByteTimeDomainData(data);
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.lineWidth = 2;
                    ctx.strokeStyle = '#2f8a45';
                    ctx.beginPath();
                    const sliceW = canvas.width / bufLen;
                    let x = 0;
                    for (let i = 0; i < bufLen; i++) {
                        const v = data[i] / 128;
                        const y = (v * canvas.height) / 2;
                        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                        x += sliceW;
                    }
                    ctx.lineTo(canvas.width, canvas.height / 2);
                    ctx.stroke();
                }
                draw();

                idle.style.display = 'none';
                active.style.display = '';

            } catch (err) {
                alert('Impossible d\'accéder au microphone : ' + err.message);
            }
        });

        // Stop recording
        wrapper.querySelector('.btn-stop-record').addEventListener('click', () => {
            const state = getState(id);
            if (state.mediaRecorder && state.mediaRecorder.state !== 'inactive') {
                state.mediaRecorder.stop();
            }
            state.stream && state.stream.getTracks().forEach(t => t.stop());
            clearInterval(state.timerInterval);
            cancelAnimationFrame(state.animFrame);
        });

        // Discard & restart
        wrapper.querySelector('.btn-discard-record').addEventListener('click', () => {
            const state = getState(id);
            state.blob = null;
            // Clear hidden input
            hiddenInput.value = '';
            audioEl.src = '';
            preview.style.display = 'none';
            active.style.display = 'none';
            idle.style.display = '';
        });
    }

    function initAll() {
        document.querySelectorAll('.voice-recorder').forEach(initRecorder);
    }

    // script.js loads at bottom of <body> — DOM is already parsed.
    // DOMContentLoaded may have already fired, so check readyState first.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();

<?php

$titulo = "Chatbot";
include_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/sidebar.php';

?>
    <div class="container chatbot-shell">
        <section class="chatbot">
            <header class="chatbot__header">
                <div class="chatbot__identity">
                    <img src="view/layout/img/Logo-AURA.jpg" alt="Logo A.U.R.A" class="chatbot__brand-logo">
                    <div>
                        <h1 class="chatbot__title">A.U.R.A</h1>
                        <p class="chatbot__status"><span></span> Sistema en línea</p>
                    </div>
                </div>
            </header>

            <main class="chatbot__window">
                
                <div class="chatbot__messages" id="chatMessages">
                    <div class="chatbot__message chatbot__message--bot">
                        <div class="chatbot__message-content">
                            <div class="chatbot__bubble">
                                <span class="chatbot__author">A.U.R.A</span>
                                <p class="chatbot__text">Hola, ¿en qué puedo ayudarte?</p>
                            </div>
                            <div class="chatbot__speech-controls" aria-label="Controles de voz de la respuesta">
                                <button type="button" class="chatbot__speech-toggle" aria-label="Escuchar respuesta" aria-pressed="false">
                                    <img src="assets/icons/volume-1.svg" alt="" aria-hidden="true">
                                </button>
                                <span class="chatbot__speech-status" role="status" aria-live="polite"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <form class="chatbot__form" id="chatForm" action="index.php?controller=chat&action=enviar" method="post">
                <div class="chatbot__field">
                    <div class="chatbot__composer">
                        <label class="visually-hidden" for="message">Consulta para A.U.R.A</label>
                        <input type="text" name="message" id="message" placeholder="Escribe tu pregunta..." autocomplete="off" required>
                        <button type="button" id="voiceInputButton" class="btn chatbot__voice-input" aria-label="Activar micrófono para dictar" aria-pressed="false">
                            <img src="assets/icons/mic.svg" alt="" aria-hidden="true">
                        </button>
                        <button type="submit" class="btn btn-primary" aria-label="Enviar mensaje">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 20.5V3.5c0-.83.67-1.5 1.5-1.5.28 0 .56.08.8.23l14.8 8.5a1.5 1.5 0 0 1 0 2.54L5.3 18.77a1.5 1.5 0 0 1-.8.23A1.5 1.5 0 0 1 3 17.5v3z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <p id="voiceStatus" class="chatbot__voice-status" role="status" aria-live="polite">Puedes escribir tu consulta o dictarla.</p>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('message');
            const chatMessages = document.getElementById('chatMessages');
            const voiceInputButton = document.getElementById('voiceInputButton');
            const voiceStatus = document.getElementById('voiceStatus');
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            let recognition = null;
            let isListening = false;
            let activeSpeechToggle = null;
            let voiceStatusTimeout = null;

            function setVoiceStatus(message, temporary) {
                if (voiceStatusTimeout) window.clearTimeout(voiceStatusTimeout);
                voiceStatus.textContent = message;
                if (temporary) {
                    voiceStatusTimeout = window.setTimeout(function () {
                        voiceStatus.textContent = '';
                    }, 10000);
                }
            }

            function updateSpeechControls(controls, isPlaying) {
                controls.toggleButton.setAttribute('aria-pressed', String(isPlaying));
                controls.toggleButton.setAttribute('aria-label', isPlaying ? 'Detener lectura' : 'Escuchar respuesta');
                
                if (isPlaying) {
                    controls.toggleButton.classList.add('is-playing');
                } else {
                    controls.toggleButton.classList.remove('is-playing');
                }
            }

            function addSpeechControls(messageContentEl, text) {
                const controlsEl = document.createElement('div');
                controlsEl.className = 'chatbot__speech-controls';
                controlsEl.setAttribute('aria-label', 'Controles de voz de la respuesta');

                const toggleButton = document.createElement('button');
                toggleButton.type = 'button';
                toggleButton.className = 'chatbot__speech-toggle';
                toggleButton.setAttribute('aria-label', 'Escuchar respuesta');
                toggleButton.setAttribute('aria-pressed', 'false');

                const icon = document.createElement('img');
                icon.src = 'assets/icons/volume-1.svg';
                icon.alt = '';
                icon.setAttribute('aria-hidden', 'true');
                toggleButton.appendChild(icon);

                const status = document.createElement('span');
                status.className = 'chatbot__speech-status';
                status.setAttribute('role', 'status');
                status.setAttribute('aria-live', 'polite');

                const controls = { toggleButton, status };
                toggleButton.addEventListener('click', function () {
                    if (!('speechSynthesis' in window)) return;

                    if (activeSpeechToggle === toggleButton) {
                        window.speechSynthesis.cancel();
                        updateSpeechControls(controls, false);
                        activeSpeechToggle = null;
                        return;
                    }

                    if (activeSpeechToggle) {
                        window.speechSynthesis.cancel();
                        activeSpeechToggle.setAttribute('aria-pressed', 'false');
                        activeSpeechToggle.setAttribute('aria-label', 'Escuchar respuesta');
                    }

                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'es-AR';
                    utterance.onend = function () {
                        updateSpeechControls(controls, false);
                        if (activeSpeechToggle === toggleButton) activeSpeechToggle = null;
                    };
                    utterance.onerror = function () {
                        updateSpeechControls(controls, false);
                        status.textContent = 'No se pudo reproducir';
                        window.setTimeout(function () {
                            if (status.textContent === 'No se pudo reproducir') status.textContent = '';
                        }, 10000);
                        if (activeSpeechToggle === toggleButton) activeSpeechToggle = null;
                    };

                    activeSpeechToggle = toggleButton;
                    updateSpeechControls(controls, true);
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.speak(utterance);
                });

                controlsEl.appendChild(toggleButton);
                controlsEl.appendChild(status);
                messageContentEl.appendChild(controlsEl);

                if (!('speechSynthesis' in window)) {
                    toggleButton.disabled = true;
                    status.textContent = 'Voz no disponible en este navegador';
                    window.setTimeout(function () {
                        if (status.textContent === 'Voz no disponible en este navegador') status.textContent = '';
                    }, 10000);
                }
            }

            function setupExistingSpeechControls() {
                const existingControls = chatMessages.querySelector('.chatbot__speech-controls');
                if (!existingControls || !('speechSynthesis' in window)) {
                    if (existingControls) existingControls.querySelector('.chatbot__speech-toggle').disabled = true;
                    return;
                }

                const messageContent = existingControls.parentElement;
                const text = messageContent.querySelector('.chatbot__text').textContent;
                addSpeechControls(messageContent, text);
                existingControls.remove();
            }

            function appendMessage(author, text, modifier, avatarType) {
                const messageEl = document.createElement('div');
                messageEl.className = 'chatbot__message ' + modifier;

                const bubbleEl = document.createElement('div');
                bubbleEl.className = 'chatbot__bubble';

                const authorEl = document.createElement('span');
                authorEl.className = 'chatbot__author';
                authorEl.textContent = author;

                const textEl = document.createElement('p');
                textEl.className = 'chatbot__text';
                textEl.textContent = text;

                bubbleEl.appendChild(authorEl);
                bubbleEl.appendChild(textEl);

                if (modifier === 'chatbot__message--bot') {
                    const messageContentEl = document.createElement('div');
                    messageContentEl.className = 'chatbot__message-content';
                    messageContentEl.appendChild(bubbleEl);
                    addSpeechControls(messageContentEl, text);
                    messageEl.appendChild(messageContentEl);
                } else {
                    messageEl.appendChild(bubbleEl);
                }

                chatMessages.appendChild(messageEl);
                chatMessages.parentElement.scrollTop = chatMessages.parentElement.scrollHeight;
            }

            setupExistingSpeechControls();

            if (!SpeechRecognition) {
                voiceInputButton.disabled = true;
                setVoiceStatus('El dictado no está disponible en este navegador. Puedes escribir la consulta.', true);
            } else {
                recognition = new SpeechRecognition();
                recognition.lang = 'es-AR';
                recognition.continuous = false;
                recognition.interimResults = true;

                recognition.onstart = function () {
                    isListening = true;
                    voiceInputButton.setAttribute('aria-pressed', 'true');
                    voiceInputButton.classList.add('is-listening');
                    setVoiceStatus('Escuchando... habla ahora.');
                };

                recognition.onresult = function (event) {
                    const transcript = Array.from(event.results)
                        .map(result => result[0].transcript)
                        .join(' ')
                        .trim();
                    messageInput.value = transcript;
                };

                recognition.onerror = function (event) {
                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        setVoiceStatus('Permiso de micrófono rechazado. Puedes escribir la consulta.', true);
                    } else {
                        setVoiceStatus('No se pudo reconocer la voz. Puedes escribir la consulta.', true);
                    }
                };

                recognition.onend = function () {
                    isListening = false;
                    voiceInputButton.setAttribute('aria-pressed', 'false');
                    voiceInputButton.classList.remove('is-listening');
                    if (voiceStatus.textContent === 'Escuchando... habla ahora.') {
                        setVoiceStatus('Dictado finalizado. Revisa el texto y envíalo.');
                    }
                };

                voiceInputButton.addEventListener('click', function () {
                    if (isListening) {
                        recognition.stop();
                        return;
                    }

                    try {
                        recognition.start();
                    } catch (error) {
                        setVoiceStatus('No se pudo activar el micrófono. Puedes escribir la consulta.', true);
                    }
                });
            }

            chatForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const message = messageInput.value.trim();
                if (!message) return;

                appendMessage('Tú', message, 'chatbot__message--user', 'user');
                messageInput.value = '';
                messageInput.focus();

                try {
                    const response = await fetch('index.php?controller=chat&action=enviar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message })
                    });

                    if (!response.ok) {
                        throw new Error('Error en la solicitud');
                    }

                    const data = await response.json();
                    const botText = Array.isArray(data.rasa)
                        ? data.rasa.map(item => item.text).filter(Boolean).join('\n')
                        : data.text || 'No se recibió respuesta del servidor.';
                    appendMessage('A.U.R.A', botText || 'No se recibió respuesta del servidor.', 'chatbot__message--bot', 'bot');
                } catch (error) {
                    appendMessage('A.U.R.A', 'No se pudo enviar el mensaje. Intenta de nuevo.', 'chatbot__message--bot', 'bot');
                    console.error(error);
                }
            });
        });
    </script>

<?php

include_once __DIR__ . '/../layout/footer.php';

?> 
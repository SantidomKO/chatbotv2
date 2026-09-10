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
                <div class="chatbot__header-tag">NEURAL LINK // 01</div>
            </header>

            <main class="chatbot__window">
                <div class="chatbot__window-bar">
                    <span>CANAL DE CONVERSACIÓN</span>
                    <span class="chatbot__signal">● SEÑAL ESTABLE</span>
                </div>
                <div class="chatbot__messages" id="chatMessages">
                    <div class="chatbot__message chatbot__message--bot">
                        <div class="chatbot__avatar chatbot__avatar--bot" aria-hidden="true"></div>
                        <div class="chatbot__bubble">
                            <span class="chatbot__author">A.U.R.A</span>
                            <p class="chatbot__text">Hola, ¿en qué puedo ayudarte?</p>
                        </div>
                    </div>
                </div>
            </main>

            <form class="chatbot__form" id="chatForm" action="index.php?controller=chat&action=enviar" method="post">
                <div class="chatbot__composer-label">TRANSMITIR MENSAJE</div>
                <div class="chatbot__field">
                    <div class="chatbot__composer">
                        <input type="text" name="message" id="message" placeholder="Escribe tu pregunta..." autocomplete="off" required>
                        <button type="submit" class="btn btn-primary" aria-label="Enviar mensaje">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M3 20.5V3.5c0-.83.67-1.5 1.5-1.5.28 0 .56.08.8.23l14.8 8.5a1.5 1.5 0 0 1 0 2.54L5.3 18.77a1.5 1.5 0 0 1-.8.23A1.5 1.5 0 0 1 3 17.5v3z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('message');
            const chatMessages = document.getElementById('chatMessages');

            function appendMessage(author, text, modifier, avatarType) {
                const messageEl = document.createElement('div');
                messageEl.className = 'chatbot__message ' + modifier;

                const avatarEl = document.createElement('div');
                avatarEl.className = 'chatbot__avatar chatbot__avatar--' + avatarType;
                avatarEl.setAttribute('aria-hidden', 'true');

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

                if (modifier === 'chatbot__message--user') {
                    messageEl.appendChild(bubbleEl);
                    messageEl.appendChild(avatarEl);
                } else {
                    messageEl.appendChild(avatarEl);
                    messageEl.appendChild(bubbleEl);
                }

                chatMessages.appendChild(messageEl);
                chatMessages.parentElement.scrollTop = chatMessages.parentElement.scrollHeight;
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
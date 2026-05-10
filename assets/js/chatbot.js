document.addEventListener('DOMContentLoaded', function () {

    const toggle   = document.getElementById('chatbot-toggle');
    const window_  = document.getElementById('chatbot-window');
    const close    = document.getElementById('chatbot-close');
    const input    = document.getElementById('chatbot-input');
    const send     = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');

    if (!toggle) return; 

    const basePath = document.getElementById('base-path')?.value || '/traventa';

    toggle.addEventListener('click', () => {
        window_.classList.toggle('d-none');
        if (!window_.classList.contains('d-none')) {
            input.focus();
        }
    });

    close.addEventListener('click', () => {
        window_.classList.add('d-none');
    });

    send.addEventListener('click', sendMessage);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') sendMessage();
    });

    function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        appendMessage(text, 'user');
        input.value = '';

        const typing = appendMessage('typing...', 'typing');

        fetch(`${basePath}/chat/message`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `message=${encodeURIComponent(text)}`
        })
        .then(r => r.json())
        .then(data => {
            typing.remove();
            if (data.reply) {
                appendMessage(data.reply, 'bot');
            } else {
                appendMessage('Sorry, I could not get a response. Please try again.', 'bot');
            }
        })
        .catch(() => {
            typing.remove();
            appendMessage('Something went wrong. Please try again.', 'bot');
        });
    }

    function appendMessage(text, type) {
        const msg = document.createElement('div');
        msg.className = `chat-msg ${type}`;
        msg.textContent = text;
        messages.appendChild(msg);
        messages.scrollTop = messages.scrollHeight;
        return msg;
    }

});
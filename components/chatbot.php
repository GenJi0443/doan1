<!-- Chat Widget -->
<div id="chat-widget" class="chat-widget">
    <div class="chat-toggle" onclick="toggleChat()">
        <i class="icon-message-circle"></i>
        <span>Hỗ trợ trực tuyến</span>
    </div>

    <div class="chat-box" style="display:none">
        <div class="chat-header">
            <h4>Chat với Anna</h4>
            <button onclick="toggleChat()">✕</button>
        </div>
        <div id="chat-messages" class="chat-messages"></div>
        <div class="chat-input">
            <input type="text" id="chat-input" placeholder="Nhập tin nhắn...">
            <button onclick="sendMessage()">
                <i class="icon-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .chat-widget {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        font-family: 'Poppins', Arial, sans-serif;
    }

    .chat-toggle {
        background: #2f89fc;
        color: white;
        padding: 12px 24px;
        border-radius: 30px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 12px rgba(47, 137, 252, 0.3);
    }

    .chat-toggle i {
        font-size: 20px;
    }

    .chat-box {
        position: absolute;
        bottom: 70px;
        right: 0;
        width: 350px;
        height: 500px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        padding: 15px 20px;
        background: #2f89fc;
        color: white;
        border-radius: 10px 10px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 500;
    }

    .chat-header button {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
    }

    .message {
        margin-bottom: 15px;
        max-width: 85%;
        clear: both;
        word-wrap: break-word;
        position: relative;
        padding: 10px 15px;
    }

    .user-message {
        float: right;
        background: #2f89fc;
        color: white;
        border-radius: 15px 15px 0 15px;
        margin-left: 15%;
    }

    .bot-message {
        float: left;
        background: #f1f3f8;
        color: #333;
        border-radius: 15px 15px 15px 0;
        margin-right: 15%;
    }

    .bot-message.formatted {
        white-space: pre-line;
        background: #f8f9fa;
        border-left: 3px solid #2f89fc;
    }

    .bot-message h3 {
        margin: 5px 0;
        color: #2f89fc;
        font-size: 16px;
    }

    .bot-message ul {
        margin: 5px 0;
        padding-left: 20px;
    }

    .bot-message .price {
        color: #28a745;
        font-weight: bold;
    }

    .bot-message .rating {
        color: #ffc107;
    }

    .chat-input {
        padding: 15px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
    }

    .chat-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 25px;
        outline: none;
        font-size: 14px;
    }

    .chat-input button {
        width: 40px;
        height: 40px;
        background: #2f89fc;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chat-input button i {
        font-size: 16px;
    }

    .typing-indicator {
        padding: 10px 15px;
        background: #f1f3f8;
        border-radius: 15px;
        display: inline-block;
        float: left;
        margin-bottom: 15px;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        background: #90a4ae;
        display: inline-block;
        border-radius: 50%;
        margin: 0 2px;
        animation: typing 1s infinite;
    }

    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }
</style>

<script>
    let isTyping = false;

    function toggleChat() {
        const chatBox = document.querySelector('.chat-box');
        const isHidden = chatBox.style.display === 'none';
        chatBox.style.display = isHidden ? 'flex' : 'none';

        if (isHidden && !chatBox.hasAttribute('data-initialized')) {
            addMessage('Xin chào! Tôi là Anna, trợ lý du lịch của DirEngine. Tôi có thể giúp gì cho bạn?', 'bot');
            chatBox.setAttribute('data-initialized', 'true');
        }
    }

    function addMessage(text, sender) {
        const messagesDiv = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;

        if (sender === 'bot') {
            // Format tin nhắn bot
            if (text.includes('🌟') || text.includes('🏨')) {
                messageDiv.className += ' formatted';
            }
            // Chuyển đổi emoji và định dạng
            text = text.replace(/\n/g, '<br>');
            text = text.replace(/💰/g, '<span class="price">💰</span>');
            text = text.replace(/⭐/g, '<span class="rating">⭐</span>');
        }

        messageDiv.innerHTML = text;
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function showTypingIndicator() {
        if (!isTyping) {
            isTyping = true;
            const messagesDiv = document.getElementById('chat-messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'typing-indicator';
            typingDiv.innerHTML = '<span></span><span></span><span></span>';
            typingDiv.id = 'typing-indicator';
            messagesDiv.appendChild(typingDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    }

    function hideTypingIndicator() {
        isTyping = false;
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    async function sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();

        if (!message) return;

        // Clear input and add user message
        input.value = '';
        addMessage(message, 'user');

        // Show typing indicator
        showTypingIndicator();

        try {
            const response = await fetch('/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message
                })
            });

            const data = await response.json();

            // Hide typing indicator
            hideTypingIndicator();

            if (data.response) {
                addMessage(data.response, 'bot');
            } else {
                throw new Error('Invalid response');
            }
        } catch (error) {
            hideTypingIndicator();
            addMessage('Xin lỗi, đã có lỗi xảy ra. Vui lòng thử lại sau.', 'bot');
        }
    }

    // Handle Enter key
    document.getElementById('chat-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
</script>
<!-- NightBot Chatbot Widget -->
<div class="chatbot-container" id="chatbot-container">
    <!-- Chat Trigger Bubble Button -->
    <button class="chatbot-trigger" id="chatbot-trigger" onclick="toggleChatbot()">
        <span class="chatbot-trigger-icon">🍰</span>
        <span class="chatbot-trigger-text">คุยกับร้าน</span>
        <span class="chatbot-badge" id="chatbot-badge">1</span>
    </button>

    <!-- Chat Window Panel -->
    <div class="chatbot-window" id="chatbot-window">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-header-profile">
                <div class="chatbot-avatar">🍰</div>
                <div>
                    <h4 class="chatbot-bot-name">น้องไนท์บอท (NightBot)</h4>
                    <span class="chatbot-status"><span class="status-dot"></span> พร้อมช่วยเหลือค่ะ</span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.8rem;">
                <button class="chatbot-clear-btn" onclick="clearChatConversation()" title="ล้างประวัติแชท" style="background: transparent; border: none; color: #fff; opacity: 0.75; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: var(--transition); padding: 0.2rem;">🗑️</button>
                <button class="chatbot-close-btn" onclick="toggleChatbot()">&times;</button>
            </div>
        </div>

        <!-- Messages Log Container -->
        <div class="chatbot-body" id="chatbot-messages">
            <div class="chat-message bot">
                <div class="message-bubble">
                    สวัสดีค่ะพี่ๆ! ยินดีต้อนรับสู่ร้าน NightCake นะคะ 🍰 ยินดีให้บริการค่ะ ต้องการสอบถามเกี่ยวกับเค้กวันเกิด เค้กมินิมอล หรือการจัดส่ง ถามน้องไนท์บอทได้เลยน้าาา 🥰✨
                </div>
                <span class="message-time">เมื่อสักครู่</span>
            </div>
        </div>

        <!-- Quick Suggestions -->
        <div class="chatbot-suggestions" id="chatbot-suggestions">
            <button class="suggestion-btn" onclick="sendQuickReply('แนะนำเค้กขายดีหน่อยค่ะ 🎂')">🎂 เค้กขายดี</button>
            <button class="suggestion-btn" onclick="sendQuickReply('บริการจัดส่งยังไงคะ? 🚚')">🚚 การจัดส่ง</button>
            <button class="suggestion-btn" onclick="sendQuickReply('ติดต่อร้านค้าทางไหนได้บ้าง? 📞')">📞 ติดต่อร้าน</button>
            <button class="suggestion-btn" onclick="sendQuickReply('ร้านเปิด-ปิดกี่โมงคะ? ⏰')">⏰ เวลาเปิด-ปิด</button>
        </div>

        <!-- Input Field Container -->
        <div class="chatbot-footer">
            <input type="text" id="chatbot-input" placeholder="พิมพ์ข้อความคุยกับน้องไนท์บอทที่นี่..." onkeypress="handleChatEnter(event)">
            <button id="chatbot-send-btn" onclick="sendChatMessage()">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
/* Chatbot Container Styles */
.chatbot-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
    font-family: var(--font-thai);
}

/* Floating Trigger Button */
.chatbot-trigger {
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 56px;
    height: 56px;
    cursor: pointer;
    box-shadow: 0 6px 20px color-mix(in srgb, var(--primary-color) 40%, transparent);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), width 0.3s ease, border-radius 0.3s ease, padding 0.3s ease;
    position: relative;
    user-select: none;
    overflow: hidden;
}
.chatbot-trigger:hover {
    width: 145px; /* Smooth expand to show 'คุยกับร้าน' */
    border-radius: 50px;
    padding: 0 1.2rem;
    justify-content: flex-start;
    transform: scale(1.05) translateY(-3px);
    background-color: var(--primary-hover);
    box-shadow: 0 8px 25px color-mix(in srgb, var(--primary-color) 50%, transparent);
}
.chatbot-trigger-icon {
    font-size: 1.4rem;
    animation: shakePulse 3s infinite;
    flex-shrink: 0;
}
.chatbot-trigger-text {
    max-width: 0;
    opacity: 0;
    white-space: nowrap;
    overflow: hidden;
    transition: max-width 0.3s ease, opacity 0.25s ease;
    font-family: var(--font-thai);
    font-size: 0.9rem;
    font-weight: 600;
}
.chatbot-trigger:hover .chatbot-trigger-text {
    max-width: 90px;
    opacity: 1;
    margin-left: 0.5rem;
}
.chatbot-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background-color: #ff3b30;
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    animation: bounceBadge 2s infinite;
    z-index: 5;
}

/* Chatbot Window Panel (Glassmorphic look) */
.chatbot-window {
    position: absolute;
    bottom: 4.5rem;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 4rem);
    height: 520px;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid color-mix(in srgb, var(--primary-color) 25%, transparent);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px) scale(0.95);
    transform-origin: bottom right;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.chatbot-window.open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

/* Header */
.chatbot-header {
    background-color: var(--primary-color);
    padding: 1rem 1.25rem;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.chatbot-header-profile {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.chatbot-avatar {
    width: 36px;
    height: 36px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    border: 1.5px solid rgba(255, 255, 255, 0.6);
}
.chatbot-bot-name {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
}
.chatbot-status {
    font-size: 0.75rem;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.status-dot {
    width: 7px;
    height: 7px;
    background-color: #2ec946;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 5px #2ec946;
    animation: blinkDot 1.5s infinite;
}
.chatbot-close-btn {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.8rem;
    cursor: pointer;
    opacity: 0.8;
    transition: var(--transition);
}
.chatbot-close-btn:hover {
    opacity: 1;
    transform: scale(1.1);
}
.chatbot-clear-btn:hover {
    opacity: 1 !important;
    transform: scale(1.15);
}

/* Message Log */
.chatbot-body {
    flex-grow: 1;
    padding: 1.2rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background-color: rgba(252, 246, 245, 0.5);
    scroll-behavior: smooth;
}

.chat-message {
    display: flex;
    flex-direction: column;
    max-width: 80%;
}
.chat-message.bot {
    align-self: flex-start;
}
.chat-message.user {
    align-self: flex-end;
}
.message-bubble {
    padding: 0.75rem 1rem;
    border-radius: 16px;
    font-size: 0.88rem;
    line-height: 1.5;
    word-break: break-word;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
}
.bot .message-bubble {
    background-color: #fff;
    color: var(--text-main);
    border-top-left-radius: 4px;
    border: 1px solid var(--border-color);
}
.user .message-bubble {
    background-color: var(--primary-color);
    color: #fff;
    border-top-right-radius: 4px;
    box-shadow: 0 4px 12px color-mix(in srgb, var(--primary-color) 20%, transparent);
}
.message-time {
    font-size: 0.7rem;
    color: var(--text-light);
    margin-top: 0.25rem;
    padding: 0 0.3rem;
}
.bot .message-time {
    align-self: flex-start;
}
.user .message-time {
    align-self: flex-end;
}

/* Suggestions Container */
.chatbot-suggestions {
    padding: 0.5rem 1rem;
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    background-color: rgba(255, 255, 255, 0.8);
    border-top: 1px solid var(--border-color);
    white-space: nowrap;
    scrollbar-width: none; /* Hide scrollbar for Firefox */
    align-items: center;
}
.chatbot-suggestions::-webkit-scrollbar {
    display: none; /* Hide scrollbar for Chrome, Safari and Opera */
}
.suggestion-btn {
    background-color: #fff;
    border: 1px solid var(--border-color);
    padding: 0 0.9rem;
    border-radius: 50px;
    font-size: 0.8rem;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
    flex-shrink: 0;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
    height: 32px;
}
.suggestion-btn:hover {
    border-color: var(--primary-color);
    color: var(--primary-hover);
    background-color: color-mix(in srgb, var(--primary-color) 5%, transparent);
    transform: translateY(-1px);
}

/* Typing indicator */
.typing-indicator {
    display: flex;
    gap: 0.3rem;
    align-items: center;
    padding: 0.5rem 0.8rem;
}
.typing-dot {
    width: 6px;
    height: 6px;
    background-color: var(--text-light);
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out both;
}
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

/* Input Footer */
.chatbot-footer {
    display: flex;
    padding: 0.75rem 1rem;
    background-color: #fff;
    border-top: 1px solid var(--border-color);
    gap: 0.5rem;
    align-items: center;
}
.chatbot-footer input {
    flex-grow: 1;
    border: 1px solid var(--border-color);
    border-radius: 50px;
    padding: 0.65rem 1.2rem;
    font-family: inherit;
    font-size: 0.88rem;
    outline: none;
    transition: var(--transition);
    background-color: var(--secondary-color);
}
.chatbot-footer input:focus {
    border-color: var(--primary-color);
    background-color: #fff;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary-color) 10%, transparent);
}
#chatbot-send-btn {
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    box-shadow: 0 3px 10px color-mix(in srgb, var(--primary-color) 20%, transparent);
}
#chatbot-send-btn:hover {
    background-color: var(--primary-hover);
    transform: scale(1.05);
}

/* Animations */
@keyframes shakePulse {
    0%, 100% { transform: rotate(0deg) scale(1); }
    5% { transform: rotate(-8deg) scale(1.1); }
    10% { transform: rotate(8deg) scale(1.1); }
    15% { transform: rotate(-5deg) scale(1.1); }
    20% { transform: rotate(5deg) scale(1.1); }
    25% { transform: rotate(0deg) scale(1.1); }
}
@keyframes bounceBadge {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
@keyframes blinkDot {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
}
@keyframes typingBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

@media (max-width: 480px) {
    .chatbot-container {
        bottom: 1rem;
        right: 1rem;
    }
    .chatbot-window {
        bottom: 4rem;
        right: 0;
        width: calc(100vw - 2rem);
        height: 480px;
    }
}
</style>

<script>
let chatHistory = []; // Keep session history

function clearChatConversation() {
    if (confirm('คุณต้องการล้างประวัติการสนทนาทั้งหมดเพื่อเริ่มคุยใหม่ ใช่หรือไม่?')) {
        chatHistory = [];
        const messagesContainer = document.getElementById('chatbot-messages');
        if (messagesContainer) {
            messagesContainer.innerHTML = `
                <div class="chat-message bot">
                    <div class="message-bubble">
                        ล้างประวัติการสนทนาเสร็จสิ้นแล้วค่ะ! พี่ๆ มีอะไรให้น้องไนท์ช่วยเหลือเพิ่มเติม ถามมาได้เลยนะคะ 🥰🍰
                    </div>
                    <span class="message-time">เมื่อสักครู่</span>
                </div>
            `;
        }
    }
}

function toggleChatbot() {
    const chatWin = document.getElementById('chatbot-window');
    const badge = document.getElementById('chatbot-badge');
    
    if (chatWin.classList.contains('open')) {
        chatWin.classList.remove('open');
    } else {
        chatWin.classList.add('open');
        // Hide badge upon opening
        if (badge) {
            badge.style.display = 'none';
        }
        // Focus input
        setTimeout(() => {
            document.getElementById('chatbot-input').focus();
        }, 300);
    }
}

function handleChatEnter(e) {
    if (e.key === 'Enter') {
        sendChatMessage();
    }
}

function sendQuickReply(text) {
    // Hide suggestions container briefly to prevent spam
    const suggestions = document.getElementById('chatbot-suggestions');
    suggestions.style.opacity = '0.5';
    suggestions.style.pointerEvents = 'none';
    
    setTimeout(() => {
        suggestions.style.opacity = '1';
        suggestions.style.pointerEvents = 'auto';
    }, 2000);
    
    executeSendMessage(text);
}

function sendChatMessage() {
    const input = document.getElementById('chatbot-input');
    const text = input.value.trim();
    if (!text) return;
    
    input.value = '';
    executeSendMessage(text);
}

function executeSendMessage(text) {
    const messagesContainer = document.getElementById('chatbot-messages');
    
    // 1. Append User Message to Log
    const userMsgHTML = `
        <div class="chat-message user">
            <div class="message-bubble">${escapeHTML(text)}</div>
            <span class="message-time">${getCurrentTime()}</span>
        </div>
    `;
    messagesContainer.insertAdjacentHTML('beforeend', userMsgHTML);
    scrollChatToBottom();

    // 2. Append Typing Indicator
    const typingIndicatorHTML = `
        <div class="chat-message bot" id="typing-indicator-msg">
            <div class="message-bubble typing-indicator">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        </div>
    `;
    messagesContainer.insertAdjacentHTML('beforeend', typingIndicatorHTML);
    scrollChatToBottom();
    
    // 3. Make AJAX Post to chatbot_api.php
    fetch('chatbot_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            message: text,
            history: chatHistory
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response error');
        }
        return response.json();
    })
    .then(data => {
        // Remove typing indicator
        removeTypingIndicator();
        
        const replyText = data.reply || 'ขออภัยด้วยนะคะ เกิดเหตุขัดข้องทางเทคนิคเล็กน้อยค่ะ 🥺🍰';
        
        // Append Bot Reply to Log
        const botMsgHTML = `
            <div class="chat-message bot">
                <div class="message-bubble">${linkify(escapeHTML(replyText))}</div>
                <span class="message-time">${getCurrentTime()}</span>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', botMsgHTML);
        scrollChatToBottom();
        
        // Save to Session History
        chatHistory.push({ role: 'user', content: text });
        chatHistory.push({ role: 'assistant', content: replyText });
    })
    .catch(error => {
        removeTypingIndicator();
        // Append Fallback Bot Reply
        const botMsgHTML = `
            <div class="chat-message bot">
                <div class="message-bubble">ขออภัยด้วยนะคะ เกิดปัญหาในการต่อสัญญาณติดต่อสักครู่ค่ะ รบกวนแอดไลน์ร้าน @NIGHTCAKE เพื่อติดต่อเจ้าหน้าที่นะคะ 💖🎂</div>
                <span class="message-time">${getCurrentTime()}</span>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', botMsgHTML);
        scrollChatToBottom();
    });
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typing-indicator-msg');
    if (indicator) {
        indicator.remove();
    }
}

function scrollChatToBottom() {
    const container = document.getElementById('chatbot-messages');
    container.scrollTop = container.scrollHeight;
}

function getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

// Convert line breaks and simple text links to HTML tags
function linkify(text) {
    // Convert newlines to breaks
    let formatted = text.replace(/\n/g, '<br>');
    // Simple URL regex to match http/https/www links
    const urlPattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    formatted = formatted.replace(urlPattern, '<a href="$1" target="_blank" style="color: var(--primary-color); text-decoration: underline;">$1</a>');
    return formatted;
}
</script>

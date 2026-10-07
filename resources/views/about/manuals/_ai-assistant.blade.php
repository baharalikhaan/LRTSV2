<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>

{{-- AI Assistant Chat Widget (hidden entirely when the admin switches it off in settings) --}}
@if(\App\Models\AiSetting::get('assistant_enabled', '1') === '1')
<div id="aiChatFab" onclick="toggleAiChat()" title="Ask AI Assistant">
    <i class="fas fa-robot"></i>
</div>

<div id="aiChatWindow" style="display:none;">
    <div class="ai-chat-header">
        <div style="display:flex; align-items:center; gap:8px;">
            <i class="fas fa-robot" style="font-size:16px;"></i>
            <div>
                <div style="font-weight:600; font-size:13px;">RTS Assistant</div>
                <div style="font-size:10px; opacity:.8;">Powered by Gemini AI</div>
            </div>
        </div>
        <button onclick="toggleAiChat()" style="background:none;border:none;color:#fff;font-size:16px;cursor:pointer;padding:4px;">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div id="aiChatMessages" class="ai-chat-body">
        <div class="ai-msg ai-msg-bot">
            <div class="ai-msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="ai-msg-content">Hi! I'm the RTS assistant. Ask me anything about how to use the system — dashboards, workflows, grading, or any feature.</div>
        </div>
    </div>
    <form id="aiChatForm" onsubmit="sendAiMessage(event)" class="ai-chat-footer">
        <input type="text" id="aiChatInput" placeholder="Ask about RTS..." autocomplete="off" maxlength="2000">
        <button type="submit" id="aiChatSend" title="Send"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>
@endif

@push('styles')
<style>
/* AI Chat Widget — QU Theme */
#aiChatFab {
    position:fixed; bottom:24px; right:24px; z-index:99999;
    width:56px; height:56px; border-radius:50%;
    background:linear-gradient(135deg, var(--brand-600), var(--brand-800));
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-size:24px; cursor:pointer;
    box-shadow:0 4px 24px rgba(141,27,61,.4);
    transition:transform .2s, box-shadow .2s;
    border:2px solid rgba(255,255,255,.15);
}
#aiChatFab i { color:#fff !important; }
#aiChatFab:hover { transform:scale(1.08); box-shadow:0 6px 28px rgba(141,27,61,.5); }

#aiChatWindow {
    position:fixed; bottom:90px; right:24px; z-index:99999;
    width:500px; max-height:520px;
    background:#fff; border-radius:14px;
    box-shadow:0 12px 48px rgba(0,0,0,.2);
    display:flex; flex-direction:column; overflow:hidden;
    border:1px solid var(--ink-200);
}
.ai-chat-header {
    background:linear-gradient(135deg, var(--brand-700), var(--brand-900));
    color:var(--sand-50); padding:14px 16px;
    display:flex; align-items:center; justify-content:space-between;
}
.ai-chat-header i { color:var(--sand-50) !important; }
.ai-chat-body {
    flex:1; overflow-y:auto; padding:14px; max-height:360px;
    display:flex; flex-direction:column; gap:12px;
    background:var(--sand-50);
}
.ai-chat-footer {
    display:flex; gap:8px; padding:10px 12px;
    border-top:1px solid var(--ink-100); background:#fff;
}
.ai-chat-footer input {
    flex:1; border:1px solid var(--ink-200); border-radius:8px;
    padding:10px 14px; font-size:13px; outline:none; background:#fff;
    color:var(--ink-800); transition:border-color .15s;
}
.ai-chat-footer input:focus { border-color:var(--brand-500); box-shadow:0 0 0 2px rgba(141,27,61,.08); }
.ai-chat-footer input::placeholder { color:var(--ink-400); }
.ai-chat-footer button[type="submit"] {
    width:42px; height:42px; border-radius:8px; border:none;
    background:var(--brand-500); color:#fff;
    font-size:16px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background .15s; flex-shrink:0;
    box-shadow:0 2px 8px rgba(141,27,61,.25);
}
.ai-chat-footer button[type="submit"] i { color:#fff !important; }
.ai-chat-footer button[type="submit"]:hover { background:var(--brand-600); }
.ai-chat-footer button[type="submit"]:disabled { opacity:.5; cursor:not-allowed; background:var(--ink-300); box-shadow:none; }

.ai-msg { display:flex; gap:8px; max-width:92%; }
.ai-msg-bot { align-self:flex-start; }
.ai-msg-user { align-self:flex-end; flex-direction:row-reverse; }
.ai-msg-avatar {
    width:30px; height:30px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:13px;
}
.ai-msg-bot .ai-msg-avatar { background:var(--brand-50); color:var(--brand-600); }
.ai-msg-user .ai-msg-avatar { background:var(--brand-500); color:#fff; }
.ai-msg-avatar i { color:inherit !important; }
.ai-msg-content {
    padding:10px 14px; border-radius:12px; font-size:13px; line-height:1.55;
}
.ai-msg-bot .ai-msg-content {
    background:#fff; color:var(--ink-700);
    border:1px solid var(--ink-100);
    border-top-left-radius:4px;
}
.ai-msg-user .ai-msg-content {
    background:var(--brand-500); color:#fff;
    border-top-right-radius:4px;
}
.ai-msg-typing .ai-msg-content { padding:12px 18px; }
.ai-typing-dots span {
    display:inline-block; width:6px; height:6px; border-radius:50%;
    background:var(--ink-300); margin:0 2px;
    animation:aiDotPulse 1.2s infinite;
}
.ai-typing-dots span:nth-child(2) { animation-delay:.2s; }
.ai-typing-dots span:nth-child(3) { animation-delay:.4s; }
@keyframes aiDotPulse {
    0%,60%,100% { opacity:.3; transform:translateY(0); }
    30% { opacity:1; transform:translateY(-3px); }
}

@media (max-width:480px) {
    #aiChatWindow { width:calc(100vw - 32px); right:16px; bottom:80px; }
}

/* Markdown inside chat messages */
.ai-msg-content h1, .ai-msg-content h2, .ai-msg-content h3, .ai-msg-content h4 {
    font-size:13px; font-weight:700; color:var(--ink-800);
    margin:10px 0 4px; line-height:1.4;
}
.ai-msg-content h1 { font-size:14px; }
.ai-msg-content h2 { font-size:13.5px; }
.ai-msg-content p { margin:0 0 6px; }
.ai-msg-content p:last-child { margin-bottom:0; }
.ai-msg-content ul, .ai-msg-content ol {
    margin:4px 0 6px; padding-left:18px;
}
.ai-msg-content li { margin-bottom:2px; }
.ai-msg-content strong { font-weight:600; color:var(--ink-800); }
.ai-msg-content em { font-style:italic; color:var(--ink-600); }
.ai-msg-content code {
    font-family:'SF Mono', 'Consolas', monospace;
    font-size:11.5px; background:var(--sand-100);
    padding:1px 5px; border-radius:4px; color:var(--brand-700);
}
.ai-msg-content pre {
    background:var(--ink-800); color:#e2e8f0;
    padding:10px 12px; border-radius:8px; margin:6px 0;
    overflow-x:auto; font-size:11.5px; line-height:1.5;
}
.ai-msg-content pre code {
    background:none; padding:0; color:inherit; font-size:inherit;
}
.ai-msg-content table {
    width:100%; border-collapse:collapse; margin:6px 0; font-size:12px;
}
.ai-msg-content th, .ai-msg-content td {
    border:1px solid var(--ink-200); padding:5px 8px; text-align:left;
}
.ai-msg-content th {
    background:var(--sand-100); font-weight:600; color:var(--ink-700);
    font-size:11.5px;
}
.ai-msg-content td { color:var(--ink-600); }
.ai-msg-content blockquote {
    border-left:3px solid var(--brand-400); margin:6px 0;
    padding:4px 10px; background:var(--sand-50);
    color:var(--ink-600); font-size:12.5px; border-radius:0 4px 4px 0;
}
.ai-msg-content hr {
    border:none; border-top:1px solid var(--ink-200); margin:8px 0;
}
.ai-msg-content a {
    color:var(--brand-600); text-decoration:underline;
}
.ai-msg-content .code-lang {
    font-size:10px; color:var(--ink-400); text-transform:uppercase;
    margin-bottom:2px; display:block;
}
</style>
@endpush

@push('scripts')
<script>
// ─── AI Chat Widget ────────────────────────────────────────────────
var aiChatHistory = [];
var aiChatOpen = false;

function toggleAiChat() {
    aiChatOpen = !aiChatOpen;
    var win = document.getElementById('aiChatWindow');
    var fab = document.getElementById('aiChatFab');
    if (aiChatOpen) {
        win.style.display = 'flex';
        fab.innerHTML = '<i class="fas fa-xmark"></i>';
        document.getElementById('aiChatInput').focus();
    } else {
        win.style.display = 'none';
        fab.innerHTML = '<i class="fas fa-robot"></i>';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function sendAiMessage(e) {
    e.preventDefault();
    var input = document.getElementById('aiChatInput');
    var msg = input.value.trim();
    if (!msg) return;

    var messagesEl = document.getElementById('aiChatMessages');
    var sendBtn = document.getElementById('aiChatSend');

    // Add user message
    var userDiv = document.createElement('div');
    userDiv.className = 'ai-msg ai-msg-user';
    userDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-user"></i></div><div class="ai-msg-content">' + escapeHtml(msg) + '</div>';
    messagesEl.appendChild(userDiv);

    aiChatHistory.push({ role: 'user', text: msg });
    input.value = '';
    sendBtn.disabled = true;

    // Typing indicator
    var typingDiv = document.createElement('div');
    typingDiv.className = 'ai-msg ai-msg-bot ai-msg-typing';
    typingDiv.id = 'aiTyping';
    typingDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="ai-msg-content"><span class="ai-typing-dots"><span></span><span></span><span></span></span></div>';
    messagesEl.appendChild(typingDiv);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    fetch('{{ route("ai.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ message: msg, history: aiChatHistory.slice(-10) })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var typing = document.getElementById('aiTyping');
        if (typing) typing.remove();

        var reply = data.reply || data.error || 'Sorry, something went wrong.';
        var botDiv = document.createElement('div');
        botDiv.className = 'ai-msg ai-msg-bot';
        botDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="ai-msg-content">' + escapeAiHtml(reply) + '</div>';
        messagesEl.appendChild(botDiv);

        aiChatHistory.push({ role: 'assistant', text: reply });
        messagesEl.scrollTop = messagesEl.scrollHeight;
        sendBtn.disabled = false;
        input.focus();
    })
    .catch(function() {
        var typing = document.getElementById('aiTyping');
        if (typing) typing.remove();

        var errDiv = document.createElement('div');
        errDiv.className = 'ai-msg ai-msg-bot';
        errDiv.innerHTML = '<div class="ai-msg-avatar"><i class="fas fa-robot"></i></div><div class="ai-msg-content" style="color:var(--color-danger);">Failed to connect. Please try again.</div>';
        messagesEl.appendChild(errDiv);
        messagesEl.scrollTop = messagesEl.scrollHeight;
        sendBtn.disabled = false;
    });
}

function escapeAiHtml(str) {
    if (!str) return '';
    if (typeof marked !== 'undefined' && typeof DOMPurify !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
        var raw = marked.parse(str);
        return DOMPurify.sanitize(raw, { ADD_ATTR: ['target'] });
    }
    // Fallback if CDN fails
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    var html = div.innerHTML;
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\n/g, '<br>');
    return html;
}
</script>
@endpush

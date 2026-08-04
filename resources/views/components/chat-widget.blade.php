{{-- Floating WhatsApp/Telegram-style chat widget (polling-based). Blade users only. --}}
<div x-data="chatWidget()" x-init="init()">
    {{-- Launcher button --}}
    <button type="button" @click="toggle()"
        class="fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg flex items-center justify-center transition">
        <svg x-show="!open" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3 20l1.5-4A8.96 8.96 0 013 12"/></svg>
        <svg x-show="open" x-cloak class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span x-show="unreadTotal > 0" x-cloak
            class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center"
            x-text="unreadTotal > 99 ? '99+' : unreadTotal"></span>
    </button>

    {{-- Panel --}}
    <div x-show="open" x-transition x-cloak
        class="fixed bottom-24 right-5 z-40 w-[370px] max-w-[92vw] h-[540px] max-h-[75vh] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center gap-2 px-4 py-3 bg-indigo-600 text-white shrink-0">
            <button type="button" x-show="view !== 'list'" @click="back()" class="p-1 -ml-1 rounded hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <h3 class="font-semibold text-sm truncate flex-1" x-text="headerTitle()"></h3>
            <button type="button" x-show="view === 'list'" @click="openNew()" title="New chat" class="p-1 rounded hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
            <button type="button" @click="toggleOsNotify()" :title="osNotify ? 'Desktop popups on — click to mute' : 'Desktop popups muted — click to enable'" class="p-1 rounded hover:bg-white/10">
                <svg x-show="osNotify" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <svg x-show="!osNotify" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.73 21a2 2 0 01-3.46 0M18.63 13A17.9 17.9 0 0118 8M6.26 6.26A5.97 5.97 0 006 8c0 3.09-.79 4.9-1.6 5.8L3 15h11M3 3l18 18"/></svg>
            </button>
            <button type="button" @click="open=false" class="p-1 rounded hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- LIST view --}}
        <div x-show="view === 'list'" class="flex-1 min-h-0 overflow-y-auto chat-scroll">
            <template x-if="conversations.length === 0">
                <div class="p-6 text-center text-sm text-gray-400">No chats yet. Tap + to start one.</div>
            </template>
            <template x-for="c in conversations" :key="c.id">
                <button type="button" @click="openConversation(c)"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-50 dark:border-gray-700/60 text-left">
                    <span class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold shrink-0" x-text="c.avatar"></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="font-medium text-gray-900 dark:text-gray-100 truncate text-sm" x-text="c.title"></span>
                            <span class="text-[11px] text-gray-400 shrink-0" x-text="c.last_at"></span>
                        </span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 truncate" x-text="c.last_message || 'No messages yet'"></span>
                    </span>
                    <span x-show="c.unread > 0" class="min-w-5 h-5 px-1 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center shrink-0" x-text="c.unread"></span>
                </button>
            </template>
        </div>

        {{-- CHAT view --}}
        <div x-show="view === 'chat'" class="flex-1 flex flex-col min-h-0">
            <div x-ref="msgs" class="flex-1 min-h-0 overflow-y-auto chat-scroll p-3 space-y-2 bg-gray-50 dark:bg-gray-900/40">
                <template x-for="m in messages" :key="m.id">
                    <div class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[78%] rounded-2xl px-3 py-1.5 text-sm"
                            :class="m.mine ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-sm shadow-sm'">
                            <template x-if="!m.mine && active && active.is_group">
                                <span class="block text-[11px] font-semibold text-indigo-500 dark:text-indigo-300" x-text="m.sender_name"></span>
                            </template>
                            <span class="whitespace-pre-wrap break-words" x-text="m.body"></span>
                            <span class="block text-[10px] mt-0.5 text-right" :class="m.mine ? 'text-indigo-200' : 'text-gray-400'" x-text="m.at"></span>
                        </div>
                    </div>
                </template>
            </div>
            <form @submit.prevent="send()" class="flex items-center gap-2 p-2 border-t border-gray-100 dark:border-gray-700 shrink-0">
                <input x-model="body" type="text" placeholder="Type a message…" autocomplete="off"
                    class="flex-1 rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm px-4">
                <button type="submit" class="w-10 h-10 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>

        {{-- NEW CHAT view --}}
        <div x-show="view === 'new'" class="flex-1 flex flex-col min-h-0">
            <div class="p-2 border-b border-gray-100 dark:border-gray-700 shrink-0">
                <input x-model="recipientSearch" type="text" placeholder="Search people…"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto chat-scroll">
                <template x-if="filteredRecipients.length === 0">
                    <div class="p-6 text-center text-sm text-gray-400">No people available.</div>
                </template>
                <template x-for="r in filteredRecipients" :key="r.id">
                    <button type="button" @click="toggleSelect(r.id)"
                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-left">
                        <span class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-200 flex items-center justify-center text-xs font-bold shrink-0" x-text="r.name.charAt(0).toUpperCase()"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm text-gray-900 dark:text-gray-100 truncate" x-text="r.name"></span>
                            <span class="block text-xs text-gray-400 truncate" x-text="r.role + (r.dept ? ' · ' + r.dept : '')"></span>
                        </span>
                        <span class="w-5 h-5 rounded-md border flex items-center justify-center shrink-0"
                            :class="selected.includes(r.id) ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-300 dark:border-gray-500'">
                            <svg x-show="selected.includes(r.id)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    </button>
                </template>
            </div>
            <div class="p-3 border-t border-gray-100 dark:border-gray-700 shrink-0 space-y-2">
                <input x-show="selected.length > 1" x-model="groupName" type="text" placeholder="Group name (optional)"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                <button type="button" @click="startChat()" :disabled="selected.length === 0"
                    class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white text-sm font-medium">
                    <span x-text="selected.length > 1 ? ('Start group (' + selected.length + ')') : 'Start chat'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Privacy toast: announces a new message, but blurs who/what until hover --}}
    <div x-show="toast" x-transition.opacity.scale x-cloak @click="openToast()"
        class="fixed top-4 right-4 z-50 w-[320px] max-w-[88vw] flex items-center gap-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md shadow-2xl p-3 cursor-pointer group ring-1 ring-black/5">
        <span class="w-11 h-11 rounded-full bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-300">New message</div>
            <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate select-none blur-[5px] group-hover:blur-none transition-all duration-200"
                x-text="(toast ? toast.title : '') + (toast && toast.last_message ? (': ' + toast.last_message) : '')"></div>
            <div class="text-[10px] text-gray-400 mt-0.5">Hover to preview · click to open</div>
        </div>
        <button type="button" @click.stop="dismissToast()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0 text-xl leading-none px-1">&times;</button>
    </div>

    <style>
        [x-cloak]{display:none !important;}
        /* Scrollable but no visible scrollbar (WhatsApp-style) */
        .chat-scroll{scrollbar-width:none;-ms-overflow-style:none;}
        .chat-scroll::-webkit-scrollbar{display:none;width:0;height:0;}
    </style>
    <script>
        window.__chatUrls = {
            conversations: @js(route('chat.conversations')),
            start: @js(route('chat.start')),
            recipients: @js(route('chat.recipients')),
            messages: @js(route('chat.messages', ['conversation' => '__ID__'])),
            send: @js(route('chat.send', ['conversation' => '__ID__'])),
        };
        window.__chatCsrf = @js(csrf_token());
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatWidget', () => ({
                open: false, view: 'list', conversations: [], unreadTotal: 0,
                active: null, messages: [], lastId: 0, body: '',
                recipients: [], recipientSearch: '', selected: [], groupName: '',
                urls: window.__chatUrls, csrf: window.__chatCsrf,
                prevUnread: null, baseTitle: '', toast: null, toastTimer: null, osNotify: true,

                init() {
                    this.baseTitle = document.title;
                    try { this.osNotify = localStorage.getItem('chatOsNotify') !== 'off'; } catch (e) {}
                    if (this.osNotify) this.requestNotifyPermission();
                    this.refresh();
                    setInterval(() => this.poll(), 4000);
                },
                headers() { return { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf }; },
                headerTitle() { return this.view === 'chat' ? (this.active?.title || 'Chat') : (this.view === 'new' ? 'New chat' : 'Chats'); },
                toggle() { this.open = !this.open; this.requestNotifyPermission(); if (this.open) this.refresh(); },

                async refresh() {
                    try {
                        const d = await (await fetch(this.urls.conversations, { headers: this.headers() })).json();
                        const convos = d.conversations || [];
                        this.detectNewMessages(convos);
                        this.conversations = convos;
                        this.unreadTotal = d.unread_total || 0;
                        this.updateTitle();
                    } catch (e) {}
                },

                // --- Missed-message notifications (works even on another tab) ---
                requestNotifyPermission() {
                    if ('Notification' in window && Notification.permission === 'default') {
                        Notification.requestPermission().catch(() => {});
                    }
                },
                detectNewMessages(convos) {
                    const map = {};
                    convos.forEach(c => { map[c.id] = c.unread; });
                    if (this.prevUnread !== null) {
                        convos.forEach(c => {
                            const prev = this.prevUnread[c.id] || 0;
                            if (c.unread > prev) {
                                const viewing = this.open && this.view === 'chat' && this.active && this.active.id === c.id && !document.hidden;
                                if (!viewing) this.notify(c);
                            }
                        });
                    }
                    this.prevUnread = map;
                },
                notify(c) {
                    this.beep();
                    this.showToast(c);
                    // OS notifications can't be blurred, so keep them generic for privacy.
                    if (this.osNotify && document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                        try {
                            const n = new Notification('New message', { body: 'You have a new chat message.', tag: 'chat-new', renotify: true });
                            n.onclick = () => { window.focus(); this.open = true; this.openConversation(c); n.close(); };
                            setTimeout(() => n.close(), 8000);
                        } catch (e) {}
                    }
                },
                showToast(c) {
                    this.toast = c;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => { this.toast = null; }, 6000);
                },
                openToast() {
                    const c = this.toast;
                    this.toast = null;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    if (c) { this.open = true; this.openConversation(c); }
                },
                dismissToast() { this.toast = null; if (this.toastTimer) clearTimeout(this.toastTimer); },
                toggleOsNotify() {
                    this.osNotify = !this.osNotify;
                    try { localStorage.setItem('chatOsNotify', this.osNotify ? 'on' : 'off'); } catch (e) {}
                    if (this.osNotify) this.requestNotifyPermission();
                },
                updateTitle() {
                    if (!this.baseTitle) return;
                    document.title = this.unreadTotal > 0 ? ('(' + this.unreadTotal + ') ' + this.baseTitle) : this.baseTitle;
                },
                beep() {
                    try {
                        const ctx = window.__chatAudio || (window.__chatAudio = new (window.AudioContext || window.webkitAudioContext)());
                        if (ctx.state === 'suspended') ctx.resume();
                        const o = ctx.createOscillator(), g = ctx.createGain();
                        o.connect(g); g.connect(ctx.destination);
                        o.type = 'sine'; o.frequency.value = 680; g.gain.value = 0.04;
                        o.start(); o.stop(ctx.currentTime + 0.12);
                    } catch (e) {}
                },
                poll() {
                    this.refresh();
                    if (this.open && this.view === 'chat' && this.active) this.loadMessages();
                },
                async openConversation(c) {
                    this.active = c; this.view = 'chat'; this.messages = []; this.lastId = 0;
                    await this.loadMessages(true);
                },
                async loadMessages(scroll = false) {
                    if (!this.active) return;
                    try {
                        const url = this.urls.messages.replace('__ID__', this.active.id) + (this.lastId ? ('?after=' + this.lastId) : '');
                        const d = await (await fetch(url, { headers: this.headers() })).json();
                        if (d.messages && d.messages.length) {
                            this.messages.push(...d.messages);
                            this.lastId = this.messages[this.messages.length - 1].id;
                            this.$nextTick(() => this.scrollBottom());
                            this.refresh();
                        } else if (scroll) {
                            this.$nextTick(() => this.scrollBottom());
                        }
                    } catch (e) {}
                },
                scrollBottom() { const el = this.$refs.msgs; if (el) el.scrollTop = el.scrollHeight; },
                async send() {
                    const text = this.body.trim();
                    if (!text || !this.active) return;
                    this.body = '';
                    try {
                        const url = this.urls.send.replace('__ID__', this.active.id);
                        const d = await (await fetch(url, { method: 'POST', headers: this.headers(), body: JSON.stringify({ body: text }) })).json();
                        if (d.message) { this.messages.push(d.message); this.lastId = d.message.id; this.$nextTick(() => this.scrollBottom()); this.refresh(); }
                    } catch (e) {}
                },
                async openNew() {
                    this.view = 'new'; this.selected = []; this.groupName = ''; this.recipientSearch = '';
                    try { const d = await (await fetch(this.urls.recipients, { headers: this.headers() })).json(); this.recipients = d.recipients || []; } catch (e) {}
                },
                toggleSelect(id) { const i = this.selected.indexOf(id); if (i >= 0) this.selected.splice(i, 1); else this.selected.push(id); },
                get filteredRecipients() {
                    const q = this.recipientSearch.toLowerCase().trim();
                    return q ? this.recipients.filter(r => r.name.toLowerCase().includes(q) || (r.dept || '').toLowerCase().includes(q)) : this.recipients;
                },
                async startChat() {
                    if (!this.selected.length) return;
                    try {
                        const d = await (await fetch(this.urls.start, { method: 'POST', headers: this.headers(), body: JSON.stringify({ user_ids: this.selected, name: this.groupName }) })).json();
                        if (d.conversation_id) {
                            await this.refresh();
                            const c = this.conversations.find(x => x.id === d.conversation_id) || { id: d.conversation_id, title: this.groupName || 'Chat', is_group: this.selected.length > 1 };
                            this.openConversation(c);
                        } else if (d.error) { alert(d.error); }
                    } catch (e) {}
                },
                back() { this.view = 'list'; this.active = null; this.refresh(); },
            }));
        });
    </script>
</div>

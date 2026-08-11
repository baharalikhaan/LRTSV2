@extends('layouts.app')

@section('title', 'Send Email - RTS')

@section('content')
<div class="email-page">

    {{-- ============================================================ --}}
    {{-- LEFT: Compose Form (70%)                                    --}}
    {{-- ============================================================ --}}
    <div class="email-compose">

        {{-- Page Header --}}
        <div class="email-page-header">
            <div>
                <h2 class="email-page-title">Send Email</h2>
                <p class="email-page-sub">Compose and send emails to users</p>
            </div>
        </div>

        {{-- Success / Error Messages --}}
        @if(session('success'))
        <div class="email-alert email-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="email-alert email-alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        {{-- Email Form --}}
        <form method="POST" action="{{ route('admin.send-email.store') }}" id="emailForm" enctype="multipart/form-data">
            @csrf

            {{-- Recipients --}}
            <div class="email-card">
                <div class="email-card-header">
                    <i class="fas fa-users"></i> Recipients
                </div>
                <div class="email-card-body">
                    <div class="email-group-btns">
                        <button type="button" class="group-btn" onclick="selectGroup('all')">
                            <i class="fas fa-globe"></i> All Users
                        </button>
                        <button type="button" class="group-btn" onclick="selectGroup('lpi')">
                            <i class="fas fa-user-graduate"></i> All LPIs
                        </button>
                        <button type="button" class="group-btn" onclick="selectGroup('reviewer')">
                            <i class="fas fa-user-check"></i> All Reviewers
                        </button>
                        <button type="button" class="group-btn" onclick="selectGroup('admin')">
                            <i class="fas fa-user-shield"></i> All Admins
                        </button>
                        <button type="button" class="group-btn group-btn-clear" onclick="clearRecipients()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                    <select name="recipients[]" id="recipientsSelect" multiple class="email-select-multi">
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" data-type="{{ $user->type }}">
                            {{ $user->name }} ({{ $user->email }}) — {{ $user->type }}
                        </option>
                        @endforeach
                    </select>
                    <div class="email-select-hint">
                        Hold Ctrl/Cmd to select multiple users.
                        <span id="selectedCount" class="email-select-count">0 selected</span>
                    </div>
                    @error('recipients')
                    <div class="email-field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- CC --}}
            <div class="email-card">
                <div class="email-card-header">
                    <i class="fas fa-copy"></i> CC
                </div>
                <div class="email-card-body">
                    <input type="text" name="cc" value="{{ old('cc') }}" class="email-input"
                        placeholder="email1@example.com, email2@example.com">
                    <div class="email-input-hint">Separate multiple emails with commas</div>
                </div>
            </div>

            {{-- Subject --}}
            <div class="email-card">
                <div class="email-card-header">
                    <i class="fas fa-heading"></i> Subject
                </div>
                <div class="email-card-body">
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="email-input"
                        placeholder="Enter email subject">
                    @error('subject')
                    <div class="email-field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Body --}}
            <div class="email-card">
                <div class="email-card-header">
                    <i class="fas fa-align-left"></i> Message
                </div>
                <div class="email-card-body">
                    <textarea name="body" required rows="10" class="email-textarea"
                        placeholder="Type your message here...">{{ old('body') }}</textarea>
                    @error('body')
                    <div class="email-field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Attachment --}}
            <div class="email-card">
                <div class="email-card-header">
                    <i class="fas fa-paperclip"></i> Attachment
                    <span class="email-label-optional">(Optional)</span>
                </div>
                <div class="email-card-body">
                    <div class="email-file-upload" id="fileUploadZone">
                        <input type="file" name="attachment" id="attachmentInput" class="email-file-input"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                        <div class="email-file-placeholder" id="filePlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div>Click to browse or drag file here</div>
                            <div class="email-file-types">PDF, DOC, XLS, PPT, JPG, PNG, ZIP — Max 10MB</div>
                        </div>
                        <div class="email-file-selected" id="fileSelected" style="display:none;">
                            <i class="fas fa-file"></i>
                            <span id="fileName"></span>
                            <button type="button" onclick="clearFile()" class="email-file-remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @error('attachment')
                    <div class="email-field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Submit --}}
            <div class="email-submit-bar">
                <a href="{{ route('home') }}" class="email-btn email-btn-cancel">Cancel</a>
                <button type="submit" id="sendBtn" class="email-btn email-btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Email
                </button>
            </div>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{-- RIGHT: Email Send Status Log (50%)                          --}}
    {{-- ============================================================ --}}
    <div class="email-sidebar">
        <div class="email-sidebar-header">
            <i class="fas fa-history"></i> Send Status
        </div>

        {{-- Search + Filter Controls --}}
        <div class="email-sidebar-controls">
            <div class="email-search-wrap">
                <i class="fas fa-search email-search-icon"></i>
                <input type="text" id="logSearch" class="email-search-input" placeholder="Search subject, recipient...">
            </div>
            <div class="email-filter-btns">
                <button type="button" class="filter-btn filter-btn-active" data-filter="all" onclick="filterLogs('all', this)">All</button>
                <button type="button" class="filter-btn" data-filter="queued" onclick="filterLogs('queued', this)">
                    <i class="fas fa-clock"></i> Queued
                </button>
                <button type="button" class="filter-btn" data-filter="sent" onclick="filterLogs('sent', this)">
                    <i class="fas fa-check"></i> Sent
                </button>
                <button type="button" class="filter-btn" data-filter="failed" onclick="filterLogs('failed', this)">
                    <i class="fas fa-exclamation-triangle"></i> Failed
                </button>
            </div>
        </div>

        <div class="email-sidebar-body" id="emailLogList">
            @forelse($emailLogs as $log)
            <div class="email-log-item" id="log-{{ $log->id }}"
                data-status="{{ $log->status }}"
                data-search="{{ strtolower($log->subject . ' ' . $log->recipient_name . ' ' . $log->recipient_email . ' ' . $log->cc) }}">
                {{-- Row 1: Badge + Time --}}
                <div class="email-log-row-top">
                    <span class="email-log-badge email-log-{{ $log->status }}">
                        @if($log->status === 'queued')
                            <i class="fas fa-clock"></i> Queued
                        @elseif($log->status === 'sent')
                            <i class="fas fa-check"></i> Sent
                        @else
                            <i class="fas fa-exclamation-triangle"></i> Failed
                        @endif
                    </span>
                    <span class="email-log-time">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                </div>

                {{-- Row 2: Subject (bold heading) --}}
                <div class="email-log-subject" title="{{ $log->subject }}">{{ $log->subject }}</div>

                {{-- Row 3: To + CC --}}
                <div class="email-log-row-middle">
                    <span class="email-log-label">To:</span>
                    <span class="email-log-recipient" title="{{ $log->recipient_email }}">{{ $log->recipient_name ?: $log->recipient_email }}</span>
                    @if($log->cc)
                    <span class="email-log-label">CC:</span>
                    <span class="email-log-recipient" title="{{ $log->cc }}">{{ Str::limit($log->cc, 30) }}</span>
                    @endif
                </div>

                {{-- Row 4: Attachment --}}
                @if($log->attachment_original_name)
                <div class="email-log-attachment">
                    <i class="fas fa-paperclip"></i> {{ Str::limit($log->attachment_original_name, 35) }}
                </div>
                @endif

                {{-- Row 5: Error (failed only) --}}
                @if($log->status === 'failed')
                <div class="email-log-error" title="{{ $log->error_message }}">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ Str::limit($log->error_message, 100) }}</span>
                </div>
                @endif

                {{-- Row 6: Sender + Retry button --}}
                <div class="email-log-row-bottom">
                    <span class="email-log-sender">
                        by {{ $log->sender ? $log->sender->name : 'System' }}
                    </span>
                    @if($log->status === 'failed')
                    <button type="button" class="email-retry-btn" onclick="retryEmail({{ $log->id }})">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="email-log-empty" id="logEmpty">
                <i class="fas fa-inbox"></i>
                <div>No emails sent yet</div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
/* =============================================================
   Email Page Layout
   ============================================================= */
.email-page {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: start;
}
.email-compose { min-width: 0; }
.email-sidebar {
    background: #fff;
    border: 1px solid var(--ink-100, #eeedf0);
    border-radius: var(--fluent-radius-md, 6px);
    box-shadow: var(--fluent-depth-2);
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* ---- Page Header ---- */
.email-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.email-page-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--ink-900, #16131a);
    margin: 0;
}
.email-page-sub {
    font-size: 12px;
    color: var(--ink-400, #8b8592);
    margin: 2px 0 0;
}

/* ---- Alerts ---- */
.email-alert {
    padding: 10px 14px;
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 12px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.email-alert-success { background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32; }
.email-alert-error { background: #ffebee; border: 1px solid #ef9a9a; color: #c62828; }

/* ---- Cards ---- */
.email-card {
    background: #fff;
    border: 1px solid var(--ink-100, #eeedf0);
    border-radius: var(--fluent-radius-md, 6px);
    margin-bottom: 14px;
    box-shadow: var(--fluent-depth-2);
}
.email-card-header {
    padding: 10px 16px;
    font-size: 12px;
    font-weight: 600;
    color: var(--ink-700, #38333e);
    border-bottom: 1px solid var(--ink-100, #eeedf0);
    background: var(--sand-50, #faf7f0);
    display: flex;
    align-items: center;
    gap: 6px;
}
.email-card-body { padding: 14px 16px; }
.email-label-optional {
    font-size: 10px;
    font-weight: 400;
    color: var(--ink-400, #8b8592);
    margin-left: 4px;
}

/* ---- Group Buttons ---- */
.email-group-btns {
    display: flex;
    gap: 6px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.group-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 12px;
    background: #fff;
    color: var(--brand-500, #8d1b3d);
    border: 1px solid var(--brand-500, #8d1b3d);
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 11px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.15s ease;
}
.group-btn:hover {
    background: var(--brand-500, #8d1b3d);
    color: #fff;
}
.group-btn-clear {
    color: var(--ink-500, #675f6e);
    border-color: var(--ink-200, #d8d6dc);
}
.group-btn-clear:hover {
    background: var(--ink-100, #eeedf0);
    color: var(--ink-700, #38333e);
    border-color: var(--ink-300, #b4b0ba);
}

/* ---- Form Controls ---- */
.email-select-multi {
    width: 100%;
    min-height: 140px;
    padding: 6px;
    border: 1px solid var(--ink-200, #d8d6dc);
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 12px;
    font-family: inherit;
    color: var(--ink-700, #38333e);
    background: #fff;
}
.email-select-hint {
    font-size: 10px;
    color: var(--ink-400, #8b8592);
    margin-top: 4px;
}
.email-select-count {
    font-weight: 600;
    color: var(--brand-500, #8d1b3d);
}
.email-input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid var(--ink-200, #d8d6dc);
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 12px;
    font-family: inherit;
    color: var(--ink-700, #38333e);
    transition: border-color 0.15s;
}
.email-input:focus {
    outline: none;
    border-color: var(--brand-500, #8d1b3d);
    box-shadow: 0 0 0 2px rgba(141, 27, 61, 0.1);
}
.email-input-hint {
    font-size: 10px;
    color: var(--ink-400, #8b8592);
    margin-top: 3px;
}
.email-textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid var(--ink-200, #d8d6dc);
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 12px;
    font-family: inherit;
    color: var(--ink-700, #38333e);
    resize: vertical;
    line-height: 1.5;
    transition: border-color 0.15s;
}
.email-textarea:focus {
    outline: none;
    border-color: var(--brand-500, #8d1b3d);
    box-shadow: 0 0 0 2px rgba(141, 27, 61, 0.1);
}
.email-field-error {
    font-size: 10px;
    color: #c62828;
    margin-top: 4px;
}

/* ---- File Upload ---- */
.email-file-upload {
    border: 2px dashed var(--ink-200, #d8d6dc);
    border-radius: var(--fluent-radius-sm, 4px);
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s;
    position: relative;
}
.email-file-upload:hover {
    border-color: var(--brand-500, #8d1b3d);
    background: var(--brand-50, #fbeef1);
}
.email-file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.email-file-placeholder {
    color: var(--ink-400, #8b8592);
    font-size: 12px;
}
.email-file-placeholder i {
    font-size: 24px;
    color: var(--ink-300, #b4b0ba);
    margin-bottom: 6px;
    display: block;
}
.email-file-types {
    font-size: 10px;
    color: var(--ink-300, #b4b0ba);
    margin-top: 4px;
}
.email-file-selected {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--ink-700, #38333e);
    font-weight: 500;
}
.email-file-selected i { color: var(--brand-500, #8d1b3d); }
.email-file-remove {
    margin-left: auto;
    background: none;
    border: none;
    color: var(--ink-400, #8b8592);
    cursor: pointer;
    padding: 2px 4px;
}
.email-file-remove:hover { color: #c62828; }

/* ---- Submit Bar ---- */
.email-submit-bar {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 4px;
}
.email-btn {
    padding: 8px 18px;
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 12px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.15s;
    border: none;
}
.email-btn-cancel {
    background: #fff;
    color: var(--ink-600, #4c4553);
    border: 1px solid var(--ink-200, #d8d6dc);
}
.email-btn-cancel:hover { background: var(--ink-50, #f7f7f8); }
.email-btn-primary {
    background: var(--brand-500, #8d1b3d);
    color: #fff;
}
.email-btn-primary:hover { background: var(--brand-600, #7a1636); }
.email-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

/* =============================================================
   Sidebar — Email Status Log
   ============================================================= */
.email-sidebar-header {
    padding: 12px 14px;
    font-size: 12px;
    font-weight: 700;
    color: var(--ink-700, #38333e);
    border-bottom: 1px solid var(--ink-100, #eeedf0);
    background: var(--sand-50, #faf7f0);
    display: flex;
    align-items: center;
    gap: 6px;
}
.email-sidebar-controls {
    padding: 10px 14px;
    border-bottom: 1px solid var(--ink-100, #eeedf0);
    background: #fff;
}
.email-search-wrap {
    position: relative;
    margin-bottom: 8px;
}
.email-search-icon {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 10px;
    color: var(--ink-300, #b4b0ba);
}
.email-search-input {
    width: 100%;
    padding: 6px 8px 6px 26px;
    border: 1px solid var(--ink-200, #d8d6dc);
    border-radius: var(--fluent-radius-sm, 4px);
    font-size: 11px;
    font-family: inherit;
    color: var(--ink-700, #38333e);
    background: var(--ink-50, #f7f7f8);
    transition: border-color 0.15s;
}
.email-search-input:focus {
    outline: none;
    border-color: var(--brand-500, #8d1b3d);
    background: #fff;
}
.email-filter-btns {
    display: flex;
    gap: 4px;
}
.filter-btn {
    padding: 3px 8px;
    border: 1px solid var(--ink-200, #d8d6dc);
    border-radius: 3px;
    background: #fff;
    color: var(--ink-600, #4c4553);
    font-size: 9px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.filter-btn:hover {
    background: var(--ink-50, #f7f7f8);
    border-color: var(--ink-300, #b4b0ba);
}
.filter-btn-active {
    background: var(--brand-500, #8d1b3d);
    color: #fff;
    border-color: var(--brand-500, #8d1b3d);
}
.filter-btn-active:hover {
    background: var(--brand-600, #7a1636);
}
.email-sidebar-body {
    overflow-y: auto;
    flex: 1;
    max-height: calc(100vh - 120px);
}
.email-log-item {
    padding: 12px 14px;
    border-bottom: 1px solid var(--ink-100, #eeedf0);
    font-size: 11px;
    transition: background 0.1s;
}
.email-log-item:hover { background: var(--sand-50, #faf7f0); }
.email-log-item:last-child { border-bottom: none; }
.email-log-row-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}
.email-log-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 7px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.email-log-queued { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
.email-log-sent { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
.email-log-failed { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
.email-log-time { font-size: 9px; color: var(--ink-400, #8b8592); }
.email-log-subject {
    font-weight: 600;
    font-size: 11px;
    color: var(--ink-800, #241f2a);
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.email-log-row-middle {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    font-size: 10px;
}
.email-log-label { color: var(--ink-400, #8b8592); flex-shrink: 0; }
.email-log-recipient { color: var(--ink-700, #38333e); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.email-log-attachment {
    font-size: 10px;
    color: var(--brand-500, #8d1b3d);
    display: flex;
    align-items: center;
    gap: 3px;
}
.email-log-error {
    font-size: 10px;
    color: #c62828;
    background: #ffebee;
    padding: 4px 8px;
    border-radius: 3px;
    margin: 4px 0;
    display: flex;
    align-items: center;
    gap: 4px;
}
.email-log-row-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 6px;
}
.email-log-sender { font-size: 9px; color: var(--ink-400, #8b8592); }
.email-retry-btn {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 10px;
    background: #fff;
    color: var(--brand-500, #8d1b3d);
    border: 1px solid var(--brand-500, #8d1b3d);
    border-radius: 3px;
    font-size: 9px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.15s;
}
.email-retry-btn:hover {
    background: var(--brand-500, #8d1b3d);
    color: #fff;
}
.email-retry-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.email-log-empty {
    padding: 30px 14px;
    text-align: center;
    color: var(--ink-300, #b4b0ba);
    font-size: 12px;
}
.email-log-empty i { font-size: 20px; margin-bottom: 6px; display: block; }

/* ---- Responsive ---- */
@media (max-width: 900px) {
    .email-page { grid-template-columns: 1fr; }
    .email-sidebar { position: static; max-height: none; }
}
</style>

@push('scripts')
<script>
const USERS_URL = '{{ route("admin.get-users") }}';
const CSRF = '{{ csrf_token() }}';

const select = document.getElementById('recipientsSelect');
const countEl = document.getElementById('selectedCount');
const fileInput = document.getElementById('attachmentInput');
const filePlaceholder = document.getElementById('filePlaceholder');
const fileSelected = document.getElementById('fileSelected');
const fileName = document.getElementById('fileName');

function updateCount() {
    countEl.textContent = select.selectedOptions.length + ' selected';
}
select.addEventListener('change', updateCount);

function selectGroup(role) {
    fetch(USERS_URL + '?role=' + role, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(users => {
        for (let opt of select.options) { opt.selected = false; }
        const ids = users.map(u => u.id);
        for (let opt of select.options) {
            if (ids.includes(parseInt(opt.value))) { opt.selected = true; }
        }
        updateCount();
    });
}

function clearRecipients() {
    for (let opt of select.options) { opt.selected = false; }
    updateCount();
}

fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        fileName.textContent = this.files[0].name;
        filePlaceholder.style.display = 'none';
        fileSelected.style.display = 'flex';
    }
});

function clearFile() {
    fileInput.value = '';
    filePlaceholder.style.display = '';
    fileSelected.style.display = 'none';
}

function retryEmail(logId) {
    const btn = document.querySelector(`#log-${logId} .email-retry-btn`);
    if (!btn || btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('{{ route("admin.retry-email", ":id") }}'.replace(':id', logId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const badge = document.querySelector(`#log-${logId} .email-log-badge`);
            if (badge) {
                badge.className = 'email-log-badge email-log-queued';
                badge.innerHTML = '<i class="fas fa-clock"></i> Queued';
            }
            btn.innerHTML = '<i class="fas fa-check"></i> Re-queued';
            btn.style.color = 'var(--success, #1f8a5f)';
            btn.style.borderColor = 'var(--success, #1f8a5f)';
            const errEl = document.querySelector(`#log-${logId} .email-log-error`);
            if (errEl) errEl.style.display = 'none';
        } else {
            btn.innerHTML = '<i class="fas fa-redo"></i> Retry';
            btn.disabled = false;
            alert(data.message || 'Retry failed.');
        }
    })
    .catch(() => {
        btn.innerHTML = '<i class="fas fa-redo"></i> Retry';
        btn.disabled = false;
        alert('An error occurred.');
    });
}

document.getElementById('emailForm').addEventListener('submit', function(e) {
    if (select.selectedOptions.length === 0) {
        e.preventDefault();
        alert('Please select at least one recipient.');
        return;
    }
    const btn = document.getElementById('sendBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
});

// ---- Email Log Search & Filter ----
let currentFilter = 'all';
const searchInput = document.getElementById('logSearch');

function filterLogs(status, btn) {
    currentFilter = status;
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('filter-btn-active'));
    if (btn) btn.classList.add('filter-btn-active');
    applyFilters();
}

searchInput.addEventListener('input', function() {
    applyFilters();
});

function applyFilters() {
    const query = searchInput.value.toLowerCase().trim();
    const items = document.querySelectorAll('.email-log-item');
    let visible = 0;

    items.forEach(item => {
        const itemStatus = item.dataset.status;
        const itemSearch = item.dataset.search || '';

        const matchesFilter = currentFilter === 'all' || itemStatus === currentFilter;
        const matchesSearch = !query || itemSearch.includes(query);

        if (matchesFilter && matchesSearch) {
            item.style.display = '';
            visible++;
        } else {
            item.style.display = 'none';
        }
    });

    // Show/hide empty state
    const emptyEl = document.getElementById('logEmpty');
    if (emptyEl) {
        emptyEl.style.display = visible === 0 ? '' : 'none';
    }
}
</script>
@endpush
@endsection

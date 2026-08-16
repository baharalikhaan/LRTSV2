@extends('layouts.app')

@section('title', 'Research Calls - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-sync-alt"></i> Research Calls</h1>
        <p>Manage research calls, deadlines, and submissions.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
                <div class="filter-group">
                    <label>Grant Type:</label>
                    <select name="grant_type" onchange="this.form.submit();" id="grantTypeFilter">
                        <option value="">All Types</option>
                        <option value="regular" {{ request('grant_type') == 'regular' ? 'selected' : '' }}>Regular Grant</option>
                        <option value="student" {{ request('grant_type') == 'student' ? 'selected' : '' }}>Student Grant</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Grant:</label>
                    <select name="grant" onchange="this.form.submit();" id="grantFilter">
                        <option value="">All Grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->id }}" {{ (request('grant') == $grant->id) ? 'selected' : '' }}>{{ $grant->grant_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle" onchange="this.form.submit();" id="cycleFilter">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cc)
                            <option value="{{ $cc->id }}" {{ (request('cycle') == $cc->id) ? 'selected' : '' }}>{{ $cc->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Visibility:</label>
                    <select name="visibility" onchange="this.form.submit();" id="visibilityFilter">
                        <option value="">All</option>
                        <option value="visible" {{ request('visibility') == 'visible' ? 'selected' : '' }}>Visible</option>
                        <option value="hidden" {{ request('visibility') == 'hidden' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            @auth
            @if(auth()->user()->isAdmin())
            <button type="button" class="btn-primary btn-sm" data-modal-create="programModal" style="flex-shrink:0;">
                <i class="fas fa-plus"></i> New Research Call
            </button>
            @endif
            @endauth
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="programsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Research Call Title</th>
                    <th>Grant</th>
                    <th>Prog. Report Deadline</th>
                    <th>Final Report Deadline</th>
                    <th class="text-center">Status
                        <i class="fas fa-question-circle" style="color:var(--color-ink-300);font-size:11px;margin-left:4px;cursor:help;"
                            data-bs-toggle="tooltip" data-bs-html="true"
                            title="When the deadlines are not passed, the status is <strong>Active</strong>.<br>When all deadlines have passed, the status becomes <strong>Inactive</strong>."></i>
                    </th>
                    <th class="text-center" style="min-width:80px;">Visibility
                        <i class="fas fa-question-circle" style="color:var(--color-ink-300);font-size:11px;margin-left:4px;cursor:help;"
                            data-bs-toggle="tooltip" data-bs-html="true"
                            title="When the admin marks it <strong>Visible</strong>, it is visible to reviewers.<br>Otherwise it will be <strong>Hidden</strong> from reviewers."></i>
                    </th>
                    <th class="text-center" style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr class="{{ $program->is_visible ? '' : 'hidden-row' }}">
                    <td><code>{{ $program->id }}</code></td>
                    <td><span style="font-weight:500;">{{ $program->program_title }}</span></td>
                    <td>
                        @if($program->grant)
                            <span class="pill primary">{{ $program->grant->grant_code }}</span>
                        @else
                            <span class="pill primary">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($program->prog_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $program->prog_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($program->final_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $program->final_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($program->isActive())
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive"><i class="fas fa-lock" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="pill {{ $program->is_visible ? 'success' : 'inactive' }}" style="font-size:10px;">
                            {{ $program->is_visible ? 'Visible' : 'Hidden' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="dropdown" style="position:relative;display:inline-block;">
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleActionMenu(this)" style="font-size:11px;">Actions ▾</button>
                            <div class="action-menu" style="display:none;position:fixed;z-index:10000;background:#fff;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);min-width:220px;padding:4px 0;">
                                <a class="dropdown-item" href="{{ route('programs.show', $program->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                    <i class="fas fa-eye" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> View Details
                                </a>
                                @auth
                                @if(auth()->user()->isAdmin())
                                <div style="border-top:1px solid #eee;margin:4px 0;"></div>
                                <a class="dropdown-item" href="#" data-modal-edit=""
                                    data-field-id="{{ $program->id }}"
                                    data-field-prog_rpt_deadline="{{ $program->prog_rpt_deadline ? $program->prog_rpt_deadline->format('Y-m-d') : '' }}"
                                    data-field-prog_rpt2_deadline="{{ $program->prog_rpt2_deadline ? $program->prog_rpt2_deadline->format('Y-m-d') : '' }}"
                                    data-field-final_rpt_deadline="{{ $program->final_rpt_deadline ? $program->final_rpt_deadline->format('Y-m-d') : '' }}"
                                    data-field-description="{{ $program->description }}"
                                    onclick="closeAllMenus();" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                    <i class="fas fa-edit" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> Edit Research Call
                                </a>
                                <a class="dropdown-item" href="#" onclick="closeAllMenus();openBulkUploadModalForProgram({{ $program->id }})" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                    <i class="fas fa-file-archive" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> Upload Proposals ZIP
                                </a>
                                <a class="dropdown-item" href="#" onclick="closeAllMenus();toggleVisibility({{ $program->id }})" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                    <i class="fas {{ $program->is_visible ? 'fa-eye-slash' : 'fa-eye' }}" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $program->is_visible ? 'Hide' : 'Show' }} Research Call
                                </a>
                                <div style="border-top:1px solid #eee;margin:4px 0;"></div>
                                <a class="dropdown-item" href="#" onclick="closeAllMenus();showDeleteModal({{ $program->id }}, '{{ addslashes($program->program_title) }}', {{ $program->projects()->count() }})" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#dc3545;">
                                    <i class="fas fa-trash" style="width:16px;text-align:center;font-size:11px;"></i> Delete Research Call
                                </a>
                                @endif
                                @endauth
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state py-4">
                            <i class="fas fa-sync-alt"></i>
                            <h5>No Research Calls Found</h5>
                            <p>Use the <strong>New Research Call</strong> button at the top right to create one.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteProgramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div style="padding:28px 28px 20px;text-align:center;">
                <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#fee2e2,#fecaca);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-trash-alt" style="color:#dc2626;font-size:22px;"></i>
                </div>
                <h5 style="margin:0 0 8px;font-weight:700;font-size:17px;color:#1e1b4b;">Delete Research Call?</h5>
                <p style="margin:0 0 12px;font-size:13px;color:#64748b;line-height:1.5;">
                    You are about to delete <strong id="deleteProgramTitle"></strong>
                </p>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;text-align:left;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-exclamation-triangle" style="color:#dc2626;font-size:14px;"></i>
                        <strong style="font-size:13px;color:#991b1b;">Warning</strong>
                    </div>
                    <p style="margin:0;font-size:12px;color:#991b1b;">
                        This will permanently delete:
                    </p>
                    <ul style="margin:6px 0 0;padding-left:20px;font-size:12px;color:#991b1b;">
                        <li>The research call</li>
                        <li><strong id="deleteProjectCount"></strong> associated project(s)</li>
                        <li>All related data (submissions, gradings, etc.)</li>
                    </ul>
                </div>
                <div style="margin-bottom:16px;text-align:left;">
                    <label style="font-size:11px;font-weight:600;display:block;margin-bottom:6px;color:#475569;text-transform:uppercase;letter-spacing:.04em;">
                        Type <strong>DELETE</strong> to confirm
                    </label>
                    <input type="text" id="deleteConfirmInput" class="form-control form-control-sm" 
                           style="border-radius:8px;" placeholder="Type DELETE to confirm"
                           oninput="document.getElementById('deleteConfirmBtn').disabled = (this.value !== 'DELETE');">
                </div>
                <div id="deleteError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#991b1b;margin-bottom:12px;text-align:left;"></div>
            </div>
            <div style="border-top:1px solid #e2e8f0;padding:16px 28px;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-secondary btn-sm" data-bs-dismiss="modal" style="padding:10px 20px;border-radius:8px;font-weight:600;font-size:13px;">Cancel</button>
                <button type="button" class="btn-sm" id="deleteConfirmBtn" disabled
                        style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:13px;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;color:#fff;box-shadow:0 4px 12px rgba(239,68,68,.3);"
                        onclick="confirmDeleteProgram()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Import Success Modal --}}
<div class="modal fade" id="importResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div style="padding:28px 28px 0;text-align:center;">
                <div id="importResultIcon" style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-check-circle" style="font-size:28px;"></i>
                </div>
                <h5 style="margin:0 0 4px;font-weight:700;font-size:17px;color:#1e1b4b;" id="importResultTitle">Import Successful</h5>
                <p style="margin:0;font-size:13px;color:#64748b;" id="importResultProgramName"></p>
            </div>
            <div style="padding:16px 28px;">
                {{-- Statistics Cards --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
                    <div style="background:linear-gradient(135deg,#ede9fe,#ddd6fe);border-radius:10px;padding:14px 12px;text-align:center;">
                        <div style="font-size:22px;font-weight:700;color:#6d28d9;" id="statTotalInExcel">0</div>
                        <div style="font-size:10px;font-weight:600;color:#7c3aed;text-transform:uppercase;letter-spacing:.04em;">In Excel</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-radius:10px;padding:14px 12px;text-align:center;">
                        <div style="font-size:22px;font-weight:700;color:#059669;" id="statImported">0</div>
                        <div style="font-size:10px;font-weight:600;color:#047857;text-transform:uppercase;letter-spacing:.04em;">Imported</div>
                    </div>
                    <div id="statMissingCard" style="background:linear-gradient(135deg,#fee2e2,#fecaca);border-radius:10px;padding:14px 12px;text-align:center;">
                        <div style="font-size:22px;font-weight:700;color:#dc2626;" id="statMissingPdf">0</div>
                        <div style="font-size:10px;font-weight:600;color:#b91c1c;text-transform:uppercase;letter-spacing:.04em;">Missing PDF</div>
                    </div>
                </div>

                {{-- Missing PDF List --}}
                <div id="missingPdfSection" style="display:none;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:13px;"></i>
                        <span style="font-size:12px;font-weight:600;color:#92400e;">Projects Missing Proposal PDF</span>
                        <button type="button" class="btn-sm" id="bulkUploadBtn" onclick="openBulkUploadModal()"
                            style="margin-left:auto;padding:4px 10px;font-size:10px;font-weight:600;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border:none;border-radius:6px;cursor:pointer;">
                            <i class="fas fa-upload" style="margin-right:3px;"></i> Upload PDFs
                        </button>
                    </div>
                    <div id="missingPdfList" style="max-height:200px;overflow-y:auto;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 0;"></div>
                </div>

                {{-- Errors --}}
                <div id="importErrorsSection" style="display:none;margin-top:12px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <i class="fas fa-exclamation-circle" style="color:#dc2626;font-size:13px;"></i>
                        <span style="font-size:12px;font-weight:600;color:#991b1b;">Import Errors</span>
                    </div>
                    <div id="importErrorsList" style="max-height:150px;overflow-y:auto;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 0;"></div>
                </div>
            </div>
            <div style="padding:12px 28px 24px;display:flex;justify-content:center;gap:10px;">
                <button type="button" class="btn-primary btn-sm" onclick="closeModalAndReload('importResultModal')" style="padding:10px 28px;border-radius:8px;font-weight:600;font-size:13px;">
                    <i class="fas fa-sync-alt" style="margin-right:4px;"></i> Refresh Page
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Upload PDFs Modal --}}
<div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width:700px;">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #e2e8f0;padding:16px 24px;">
                <h5 style="margin:0;font-weight:700;font-size:16px;color:#1e1b4b;">
                    <i class="fas fa-file-archive" style="color:var(--color-brand-500);margin-right:8px;"></i>
                    Upload Proposals (ZIP/RAR)
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding:20px 24px;">
                <p style="font-size:13px;color:#64748b;margin:0 0 16px;">
                    Upload a ZIP or RAR archive containing proposal PDFs. Files must be named as 
                    <code>{project_id}_Application.pdf</code> or <code>{project_id}.pdf</code>.
                </p>
                
                <div id="bulkUploadProgramId" style="display:none;"></div>
                
                <div id="uploadDropZone" style="border:2px dashed #d1d5db;border-radius:12px;padding:40px 20px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa;"
                    onmouseover="this.style.borderColor='#6366f1';this.style.background='#f5f3ff'"
                    onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fafafa'"
                    onclick="document.getElementById('archiveFileInput').click();"
                    ondragover="event.preventDefault();this.style.borderColor='#6366f1';this.style.background='#f5f3ff'"
                    ondragleave="this.style.borderColor='#d1d5db';this.style.background='#fafafa'"
                    ondrop="event.preventDefault();handleArchiveDrop(event);">
                    <i class="fas fa-file-archive" style="font-size:32px;color:#9ca3b8;margin-bottom:8px;display:block;"></i>
                    <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#374151;">Click or drag ZIP/RAR file here</p>
                    <p style="margin:0;font-size:11px;color:#9ca3b8;">Supports .zip and .rar archives</p>
                    <input type="file" id="archiveFileInput" accept=".zip,.rar" style="display:none;" onchange="handleArchiveFile(this.files)">
                </div>

                <div id="uploadFileList" style="margin-top:12px;display:none;">
                    <div style="font-size:11px;font-weight:600;color:#475569;margin-bottom:6px;">Selected File:</div>
                    <div id="uploadFileItems" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;display:flex;align-items:center;gap:8px;font-size:12px;">
                        <i class="fas fa-file-archive" style="color:#6366f1;font-size:14px;"></i>
                        <span id="archiveFileName" style="flex:1;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                        <span id="archiveFileSize" style="color:#9ca3b8;font-size:10px;"></span>
                        <button type="button" onclick="removeArchiveFile()" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:12px;"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <div id="uploadProgress" style="display:none;margin-top:12px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <i class="fas fa-spinner fa-spin" style="color:var(--color-brand-500);font-size:14px;"></i>
                        <span style="font-size:12px;font-weight:500;color:#374151;" id="uploadProgressText">Uploading...</span>
                    </div>
                    <div style="height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;">
                        <div id="uploadProgressBar" style="height:100%;background:linear-gradient(135deg,#6366f1,#8b5cf6);width:0%;transition:width .3s;border-radius:3px;"></div>
                    </div>
                </div>

                <div id="uploadResult" style="display:none;margin-top:12px;"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:12px 24px;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn-secondary btn-sm" data-bs-dismiss="modal" style="padding:8px 16px;border-radius:8px;font-size:12px;">Close</button>
                <button type="button" class="btn-primary btn-sm" id="uploadSubmitBtn" onclick="submitBulkUpload()" disabled
                    style="padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;color:#fff;">
                    <i class="fas fa-upload" style="margin-right:4px;"></i> Upload & Extract
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Research Call Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
        <div class="modal-content">
            <form method="POST" action="" id="editForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="_method" id="editMethod" value="PUT">
                <input type="hidden" name="program_title" id="edit_program_title" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Deadlines</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Progress Report Deadline</label>
                                <input type="date" name="prog_rpt_deadline" id="edit_prog_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Prog. Report 2 Deadline</label>
                                <input type="date" name="prog_rpt2_deadline" id="edit_prog_rpt2_deadline" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Final Report Deadline</label>
                                <input type="date" name="final_rpt_deadline" id="edit_final_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-2">
                                <label class="form-label" style="font-size:12px;">Description</label>
                                <textarea name="description" id="editDescription" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary btn-sm" id="editSubmitBtn"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Research Call Modal --}}
<div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:1200px;">
        <div class="modal-content">
            <form method="POST" action="{{ route('programs.store') }}" id="programModalForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalLabel">
                        <i class="fas fa-sync-alt me-2"></i>
                        <span id="programModalTitleText">New Research Call</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="programModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="programModalRecordId" value="">

                    {{-- Upload notice (compact) --}}
                    <div class="fluent-alert info d-none py-1 mb-2 text-center" id="programModalFileNotice" style="font-size:12px;">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <span>Excel file <strong>required</strong> on create. Proposals ZIP optional.</span>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_grant_id" class="form-label" style="font-size:12px;">Grant <span class="text-danger">*</span></label>
                                <select name="grant_id" id="programModal_grant_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Grant --</option>
                                    @foreach($grants as $grant)
                                        <option value="{{ $grant->id }}" data-grant-code="{{ $grant->grant_code }}">{{ $grant->grant_code }} - {{ $grant->grant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_cycle_id" class="form-label" style="font-size:12px;">Cycle</label>
                                <select name="cycle_id" id="programModal_cycle_id" class="form-select form-select-sm">
                                    <option value="">-- None --</option>
                                    @foreach($cycleConfigs as $cc)
                                        <option value="{{ $cc->id }}" data-cycle-title="{{ $cc->title }}">{{ $cc->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_program_title" class="form-label" style="font-size:12px;">Research Call Title <span class="text-danger">*</span></label>
                                <input type="text" name="program_title" id="programModal_program_title" class="form-control form-control-sm" required placeholder="Auto-generated from Grant + Cycle" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2" id="programModal_file_upload_group">
                                <label for="programModal_excel" class="form-label" style="font-size:12px;">
                                    Excel File (.xlsx, .xls, .csv) <span class="text-danger" id="programModalExcelRequired">*</span>
                                </label>
                                <input type="file" name="excel" id="programModal_excel" class="form-control form-control-sm" accept=".xlsx,.xls,.csv">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_prog_rpt_deadline" class="form-label" style="font-size:12px;">Progress Report Deadline</label>
                                <input type="date" name="prog_rpt_deadline" id="programModal_prog_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_prog_rpt2_deadline" class="form-label" style="font-size:12px;">Prog. Report 2 Deadline</label>
                                <input type="date" name="prog_rpt2_deadline" id="programModal_prog_rpt2_deadline" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_final_rpt_deadline" class="form-label" style="font-size:12px;">Final Report Deadline</label>
                                <input type="date" name="final_rpt_deadline" id="programModal_final_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_proposals_zip" class="form-label" style="font-size:12px;">Proposals ZIP (optional)</label>
                                <input type="file" name="proposals_zip" id="programModal_proposals_zip" class="form-control form-control-sm" accept=".zip">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_description" class="form-label" style="font-size:12px;">Description</label>
                                <textarea name="description" id="programModal_description" class="form-control form-control-sm" rows="2" placeholder="Optional description..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <p class="mb-0" style="font-size:11px;color:var(--color-ink-400);">
                                <i class="fas fa-info-circle"></i> Status is computed from deadlines — inactive when deadlines have passed.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="programModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="programModalBtnText">Create Research Call</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.tooltip .tooltip-inner {
    background: #fff;
    color: var(--color-ink-700);
    border: 1px solid var(--color-ink-200);
    font-size: 11.5px;
    font-weight: 400;
    padding: 8px 12px;
    border-radius: 6px;
    box-shadow: var(--fluent-depth-8);
    max-width: 260px;
}
.tooltip.bs-tooltip-top .tooltip-arrow::before { border-top-color: var(--color-ink-200); }
.tooltip.bs-tooltip-bottom .tooltip-arrow::before { border-bottom-color: var(--color-ink-200); }
.dropdown-item:hover { background: var(--color-sand-50, #faf7f0); }
.dropdown-item:active { background: var(--color-brand-50, #f8e8ee); }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    @if($programs->count() > 0)
    var table = $('#programsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [8, 9] },
            { searchable: false, targets: [8, 9] }
        ],
        drawCallback: function() {
            // Reinitialize Bootstrap 5 dropdowns after DataTable redraw
            var dropdownToggleList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            dropdownToggleList.map(function(el) {
                if (!el._dropdown) {
                    el._dropdown = new bootstrap.Dropdown(el);
                }
            });
        }
    });

    // Connect custom search input to DataTables search
    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Status filter — custom DataTable filter on column index 7 (Status)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var statusFilter = $('#statusFilter').val();
        if (!statusFilter) return true; // no filter

        var statusCell = $(table.cell(dataIndex, 7).node()).text().trim();
        if (statusFilter === 'active' && statusCell.indexOf('Active') !== -1) return true;
        if (statusFilter === 'inactive' && statusCell.indexOf('Inactive') !== -1) return true;
        return false;
    });

    // Bind status filter dropdown
    var statusSelect = $('#statusFilter');
    statusSelect.on('change', function() {
        table.draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip({html: true});

    // Auto-generate program_title from Grant code + Cycle title
    function autoGenerateProgramTitle() {
        var grantSelect = $('#programModal_grant_id');
        var cycleSelect = $('#programModal_cycle_id');
        var titleInput = $('#programModal_program_title');

        var grantCode = grantSelect.find('option:selected').data('grant-code') || '';
        var cycleTitle = cycleSelect.find('option:selected').data('cycle-title') || '';

        if (grantCode && cycleTitle) {
            titleInput.val(grantCode + ' - ' + cycleTitle);
        } else if (grantCode) {
            titleInput.val(grantCode);
        } else if (cycleTitle) {
            titleInput.val(cycleTitle);
        } else {
            titleInput.val('');
        }
    }

    $('#programModal_grant_id').on('change', autoGenerateProgramTitle);
    $('#programModal_cycle_id').on('change', autoGenerateProgramTitle);

    // Open create modal
    $(document).on('click', '[data-modal-create="programModal"]', function() {
        $('#programModalForm')[0].reset();
        $('#programModalMethod').val('POST');
        $('#programModalRecordId').val('');
        $('#programModalTitleText').text('New Research Call');
        $('#programModalBtnText').text('Create Research Call');
        $('#programModalForm').attr('action', '{{ route('programs.store') }}');
        $('#programModal_file_upload_group').show();
        $('#programModalFileNotice').removeClass('d-none');
        $('#programModalExcelRequired').show();
        $('#programModal_excel').prop('required', true);
        $('#programModal_program_title').prop('readonly', true).val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        var modal = new bootstrap.Modal(document.getElementById('programModal'));
        modal.show();
        autoGenerateProgramTitle();
    });

    // Open edit modal from dropdown
    $(document).on('click', '[data-modal-edit]', function(e) {
        e.preventDefault();
        var data = $(this).data();
        $('#editMethod').val('PUT');
        $('#editRecordId').val(data.fieldId || '');
        $('#editForm').attr('action', '/programs/' + data.fieldId);
        $('#edit_prog_rpt_deadline').val(data.fieldProgRptDeadline || '');
        $('#edit_prog_rpt2_deadline').val(data.fieldProgRpt2Deadline || '');
        $('#edit_final_rpt_deadline').val(data.fieldFinalRptDeadline || '');
        $('#editDescription').val(data.fieldDescription || '');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        var modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    });

    // AJAX submit for edit form
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#editSubmitBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                showToast('success', resp.message || 'Research call updated successfully.');
                setTimeout(function() { location.reload(); }, 1200);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"]');
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                        }
                    });
                } else {
                    showToast('error', xhr.responseJSON?.message || 'An error occurred.');
                }
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
            }
        });
    });

    // AJAX submit for create form
    $('#programModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#programModalSubmitBtn');
        var method = $('#programModalMethod').val();
        var url = form.attr('action');

        // Ensure program_title is auto-populated before submit
        autoGenerateProgramTitle();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        var formData = new FormData(form[0]);

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(resp) {
                bootstrap.Modal.getInstance(document.getElementById('programModal')).hide();
                showImportResultModal(resp);
            },
            error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Research Call</span>');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"]');
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                        }
                    });
                } else {
                    showToast('error', xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            }
        });
    });
});

function closeModalAndReload(modalId) {
    var modalEl = document.getElementById(modalId);
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        else modalEl.classList.remove('show');
    }
    location.reload();
}

function hideModal(modalId) {
    var modalEl = document.getElementById(modalId);
    if (modalEl) {
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

    // Upload ZIP button from dropdown
    $(document).on('click', '.upload-zip-btn', function() {
        var programId = $(this).data('program-id');
        openBulkUploadModalForProgram(programId);
    });

function toggleActionMenu(btn) {
    var menu = btn.nextElementSibling;
    var wasOpen = menu.style.display === 'block';
    closeAllMenus();
    if (!wasOpen) {
        // Position the menu below the button
        var rect = btn.getBoundingClientRect();
        menu.style.left = (rect.right - 200) + 'px';
        menu.style.top = (rect.bottom + 2) + 'px';
        menu.style.display = 'block';
    }
}

function closeAllMenus() {
    document.querySelectorAll('.action-menu').forEach(function(m) { m.style.display = 'none'; });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        closeAllMenus();
    }
});

function toggleVisibility(programId) {
    $.ajax({
        url: '{{ route("programs.toggle", "PLACEHOLDER") }}'.replace('PLACEHOLDER', programId),
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        dataType: 'json',
        success: function(resp) {
            showToast('success', resp.message);
            setTimeout(function() { location.reload(); }, 1000);
        },
        error: function(xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to toggle visibility.');
        }
    });
}

function showDeleteModal(id, title, count) {
    deleteProgramId = id;
    $('#deleteProgramTitle').text(title);
    $('#deleteProjectCount').text(count);
    $('#deleteConfirmInput').val('');
    $('#deleteConfirmBtn').prop('disabled', true);
    $('#deleteError').hide();
    var modal = new bootstrap.Modal(document.getElementById('deleteProgramModal'));
    modal.show();
}

var singleFile = null;
var singleProjectId = null;

function openSingleUploadModalForProgram(programId) {
    singleFile = null;
    singleProjectId = programId;
    $('#singleUploadProjectId').text('ID: ' + programId);
    $('#singlePdfInput').val('');
    $('#singleFilePreview').hide();
    $('#singleUploadProgress').hide();
    $('#singleUploadResult').hide();
    $('#singleUploadBtn').prop('disabled', true);
    var modal = new bootstrap.Modal(document.getElementById('singleUploadModal'));
    modal.show();
}

function handleSingleFileDrop(event) {
    event.preventDefault();
    handleSingleFileSelect(event.dataTransfer.files);
}

function handleSingleFileSelect(files) {
    if (files.length === 0) return;
    var file = files[0];
    if (file.type !== 'application/pdf') {
        showToast('error', 'Please select a PDF file.');
        return;
    }
    singleFile = file;
    $('#singleFileName').text(file.name);
    $('#singleFileSize').text((file.size / 1024).toFixed(1) + ' KB');
    $('#singleFilePreview').css('display', 'flex').show();
    $('#singleUploadBtn').prop('disabled', false);
}

function removeSingleFile() {
    singleFile = null;
    $('#singlePdfInput').val('');
    $('#singleFilePreview').hide();
    $('#singleUploadBtn').prop('disabled', true);
}

function submitSingleUpload() {
    if (!singleFile || !singleProjectId) return;

    var btn = $('#singleUploadBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    $('#singleUploadProgress').show();
    $('#singleUploadResult').hide();

    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('pdf', singleFile);

    $.ajax({
        url: '/programs/' + singleProjectId + '/upload-proposal',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var pct = Math.round((e.loaded / e.total) * 100);
                    $('#singleProgressBar').css('width', pct + '%');
                }
            });
            return xhr;
        },
        success: function(resp) {
            $('#singleProgressBar').css('width', '100%');
            $('#singleUploadResult').html(
                '<div style="background:#d1fae5;border:1px solid #a8e6b8;border-radius:8px;padding:10px 14px;font-size:13px;color:#065f46;">'
                + '<i class="fas fa-check-circle" style="margin-right:6px;"></i>'
                + 'Proposal uploaded successfully.'
                + '</div>'
            ).show();
            btn.html('<i class="fas fa-check"></i> Done');
            setTimeout(function() { location.reload(); }, 1500);
        },
        error: function(xhr) {
            $('#singleUploadResult').html(
                '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#991b1b;">'
                + '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>'
                + (xhr.responseJSON?.error || 'Upload failed.')
                + '</div>'
            ).show();
            btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
        }
    });
}

var deleteProgramId = null;
var uploadedArchiveFile = null;

function openBulkUploadModalForProgram(programId) {
    window._importProgramId = programId;
    uploadedArchiveFile = null;
    $('#uploadFileList').hide();
    $('#archiveFileName').text('');
    $('#archiveFileSize').text('');
    $('#uploadProgress').hide();
    $('#uploadResult').hide();
    $('#uploadSubmitBtn').prop('disabled', true);
    $('#archiveFileInput').val('');
    $('#bulkUploadModal').modal('show');
}

function handleArchiveDrop(event) {
    event.preventDefault();
    handleArchiveFile(event.dataTransfer.files);
}

function handleArchiveFile(files) {
    if (files.length === 0) return;
    var file = files[0];
    var ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    if (!['.zip', '.rar'].includes(ext)) {
        showToast('error', 'Please select a ZIP or RAR file.');
        return;
    }
    uploadedArchiveFile = file;
    $('#archiveFileName').text(file.name);
    $('#archiveFileSize').text((file.size / 1024 / 1024).toFixed(2) + ' MB');
    $('#uploadFileList').show();
    $('#uploadSubmitBtn').prop('disabled', false);
}

function removeArchiveFile() {
    uploadedArchiveFile = null;
    $('#uploadFileList').hide();
    $('#archiveFileInput').val('');
    $('#uploadSubmitBtn').prop('disabled', true);
}

function submitBulkUpload() {
    if (!uploadedArchiveFile) return;
    var programId = window._importProgramId;
    if (!programId) { showToast('error', 'Program ID not found.'); return; }

    var btn = $('#uploadSubmitBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    $('#uploadProgress').show();
    $('#uploadResult').hide();

    var formData = new FormData();
    formData.append('program_id', programId);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('archive', uploadedArchiveFile);

    $.ajax({
        url: '{{ route("programs.upload-proposals") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var pct = Math.round((e.loaded / e.total) * 100);
                    $('#uploadProgressBar').css('width', pct + '%');
                    $('#uploadProgressText').text('Uploading... ' + pct + '%');
                }
            });
            return xhr;
        },
        success: function(resp) {
            $('#uploadProgressBar').css('width', '100%');
            $('#uploadProgressText').text('Upload complete!');
            var resultHtml = '<div style="background:#d1fae5;border:1px solid #a8e6b8;border-radius:8px;padding:12px 16px;font-size:13px;color:#065f46;">'
                + '<i class="fas fa-check-circle" style="margin-right:6px;"></i>'
                + '<strong>' + (resp.matched || 0) + '</strong> proposal(s) matched and uploaded.'
                + (resp.skippedExisting ? ' <strong>' + resp.skippedExisting + '</strong> already existed (skipped).' : '')
                + (resp.unmatched && resp.unmatched.length > 0
                    ? '<div style="margin-top:8px;font-size:11px;color:#991b1b;"><strong>Unmatched (' + resp.unmatched.length + '):</strong><br>' + resp.unmatched.slice(0, 10).join(', ') + (resp.unmatched.length > 10 ? '...' : '') + '</div>'
                    : '')
                + '</div>';
            $('#uploadResult').html(resultHtml).show();
            btn.html('<i class="fas fa-check"></i> Done');
        },
        error: function(xhr) {
            $('#uploadResult').html('<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;font-size:13px;color:#991b1b;">'
                + '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>'
                + (xhr.responseJSON?.error || 'Upload failed.')
                + '</div>').show();
            btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload & Extract');
        }
    });
}

function showImportResultModal(resp) {
    var totalInExcel = resp.totalInExcel || 0;
    var imported = resp.importCount || 0;
    var missingCount = resp.missingPdfCount || 0;
    var errors = resp.importErrors || [];
    var missingPdfs = resp.projectsWithoutPdf || [];

    // Store program ID for bulk upload
    window._importProgramId = resp.program?.id || null;

    // Set program name
    $('#importResultProgramName').text(resp.program?.program_title || '');

    // Set statistics
    $('#statTotalInExcel').text(totalInExcel);
    $('#statImported').text(imported);
    $('#statMissingPdf').text(missingCount);

    // Color the missing card based on count
    if (missingCount === 0) {
        $('#statMissingCard').css('background', 'linear-gradient(135deg,#d1fae5,#a7f3d0)');
        $('#statMissingCard div:first-child').css('color', '#059669');
        $('#statMissingCard div:last-child').css('color', '#047857');
        $('#statMissingCard div:last-child').text('ALL MATCHED');
    } else {
        $('#statMissingCard').css('background', 'linear-gradient(135deg,#fee2e2,#fecaca)');
        $('#statMissingCard div:first-child').css('color', '#dc2626');
        $('#statMissingCard div:last-child').css('color', '#b91c1c');
        $('#statMissingCard div:last-child').text('MISSING PDF');
    }

    // Show missing PDF list
    if (missingCount > 0) {
        var listHtml = '';
        missingPdfs.forEach(function(p) {
            listHtml += '<div style="display:flex;align-items:center;gap:8px;padding:6px 12px;border-bottom:1px solid #fde68a;font-size:12px;">'
                + '<i class="fas fa-file-pdf" style="color:#dc2626;font-size:12px;"></i>'
                + '<span style="font-weight:600;color:#92400e;">' + (p.old_project_id || '') + '</span>'
                + '<span style="color:#a16207;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (p.title || '') + '</span>'
                + '</div>';
        });
        $('#missingPdfList').html(listHtml);
        $('#missingPdfSection').show();
    } else {
        $('#missingPdfSection').hide();
    }

    // Show errors
    if (errors.length > 0) {
        var errHtml = '';
        errors.forEach(function(e) {
            errHtml += '<div style="padding:6px 12px;border-bottom:1px solid #fecaca;font-size:11px;color:#991b1b;">'
                + '<i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>' + e
                + '</div>';
        });
        $('#importErrorsList').html(errHtml);
        $('#importErrorsSection').show();
    } else {
        $('#importErrorsSection').hide();
    }

    // Set icon color
    if (missingCount > 0 || errors.length > 0) {
        $('#importResultIcon').css('background', 'linear-gradient(135deg,#fef3c7,#fde68a)');
        $('#importResultIcon i').css('color', '#f59e0b');
    } else {
        $('#importResultIcon').css('background', 'linear-gradient(135deg,#d1fae5,#a7f3d0)');
        $('#importResultIcon i').css('color', '#10b981');
    }

    $('#importResultModal').modal('show');
}

var uploadedArchiveFile = null;

function openBulkUploadModal() {
    uploadedArchiveFile = null;
    $('#uploadFileList').hide();
    $('#archiveFileName').text('');
    $('#archiveFileSize').text('');
    $('#uploadProgress').hide();
    $('#uploadResult').hide();
    $('#uploadSubmitBtn').prop('disabled', true);
    $('#archiveFileInput').val('');
    $('#bulkUploadModal').modal('show');
}

function handleArchiveDrop(event) {
    event.preventDefault();
    var files = event.dataTransfer.files;
    handleArchiveFile(files);
}

function handleArchiveFile(files) {
    if (files.length === 0) return;
    var file = files[0];

    // Validate file type
    var validTypes = ['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/vnd.rar'];
    var validExtensions = ['.zip', '.rar'];
    var ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

    if (!validTypes.includes(file.type) && !validExtensions.includes(ext)) {
        showToast('error', 'Please select a ZIP or RAR file.');
        return;
    }

    uploadedArchiveFile = file;
    $('#archiveFileName').text(file.name);
    $('#archiveFileSize').text((file.size / 1024 / 1024).toFixed(2) + ' MB');
    $('#uploadFileList').show();
    $('#uploadSubmitBtn').prop('disabled', false);
}

function removeArchiveFile() {
    uploadedArchiveFile = null;
    $('#uploadFileList').hide();
    $('#archiveFileInput').val('');
    $('#uploadSubmitBtn').prop('disabled', true);
}

function submitBulkUpload() {
    if (!uploadedArchiveFile) return;

    var programId = window._importProgramId;
    if (!programId) {
        showToast('error', 'Program ID not found.');
        return;
    }

    var btn = $('#uploadSubmitBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    $('#uploadProgress').show();
    $('#uploadResult').hide();

    var formData = new FormData();
    formData.append('program_id', programId);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('archive', uploadedArchiveFile);

    $.ajax({
        url: '{{ route("programs.upload-proposals") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var pct = Math.round((e.loaded / e.total) * 100);
                    $('#uploadProgressBar').css('width', pct + '%');
                    $('#uploadProgressText').text('Uploading... ' + pct + '%');
                }
            });
            return xhr;
        },
        success: function(resp) {
            $('#uploadProgressBar').css('width', '100%');
            $('#uploadProgressText').text('Upload complete!');
            var resultHtml = '<div style="background:#d1fae5;border:1px solid #a8e6b8;border-radius:8px;padding:12px 16px;font-size:13px;color:#065f46;">'
                + '<i class="fas fa-check-circle" style="margin-right:6px;"></i>'
                + '<strong>' + (resp.matched || 0) + '</strong> proposal(s) matched and uploaded successfully.'
                + (resp.unmatched && resp.unmatched.length > 0
                    ? '<div style="margin-top:8px;font-size:11px;color:#991b1b;"><strong>Unmatched files (' + resp.unmatched.length + '):</strong><br>' + resp.unmatched.slice(0, 10).join(', ') + (resp.unmatched.length > 10 ? '...' : '') + '</div>'
                    : '')
                + '</div>';
            $('#uploadResult').html(resultHtml).show();
            btn.html('<i class="fas fa-check"></i> Done');
        },
        error: function(xhr) {
            $('#uploadResult').html('<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;font-size:13px;color:#991b1b;">'
                + '<i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>'
                + (xhr.responseJSON?.error || 'Upload failed. Please try again.')
                + '</div>').show();
            btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload & Extract');
        }
    });
}

$(document).on('click', '.delete-program-btn', function() {
    deleteProgramId = $(this).data('program-id');
    var title = $(this).data('program-title');
    var projectCount = $(this).data('project-count');
    
    $('#deleteProgramTitle').text(title);
    $('#deleteProjectCount').text(projectCount);
    $('#deleteConfirmInput').val('');
    $('#deleteConfirmBtn').prop('disabled', true);
    $('#deleteError').hide();
    $('#deleteProgramModal').modal('show');
});

function confirmDeleteProgram() {
    if (!deleteProgramId) return;
    
    var btn = $('#deleteConfirmBtn');
    var errorDiv = $('#deleteError');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
    errorDiv.hide();
    
    $.ajax({
        url: '{{ route('programs.destroy', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', deleteProgramId),
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'DELETE'
        },
        dataType: 'json',
        success: function(resp) {
            bootstrap.Modal.getInstance(document.getElementById('deleteProgramModal')).hide();
            showToast('success', resp.message || 'Research call deleted successfully.');
            setTimeout(function() { location.reload(); }, 1200);
        },
        error: function(xhr) {
            errorDiv.text(xhr.responseJSON?.message || 'Failed to delete research call.').show();
            btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
        }
    });
}

</script>
@endpush

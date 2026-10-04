@extends('layouts.app')

@section('title', 'Profile Settings - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-user-gear"></i> Profile Settings</h1>
        <p>Manage your account details and preferences.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('home') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-id-card"></i> Account Information</h2>
    </div>
    <div class="panel-body">
        <form id="profileForm" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                {{-- Left Column — Readonly (admin-managed) --}}
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">
                        <i class="fas fa-lock" style="color:var(--brand-500);margin-right:4px;"></i> Account Information
                        <span style="text-transform:none;letter-spacing:0;font-weight:400;color:var(--ink-400);margin-left:6px;">(managed by administrator)</span>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" readonly
                               style="background:var(--sand-50); cursor:not-allowed;">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" readonly
                               style="background:var(--sand-50); cursor:not-allowed;">
                    </div>

                    <div class="form-group">
                        <label for="qu_id" class="form-label">QU ID</label>
                        <input type="email" name="qu_id" id="qu_id" class="form-control" value="{{ old('qu_id', $user->qu_id) }}" readonly
                               placeholder="e.g. jdoe@qu.edu.qa"
                               style="background:var(--sand-50); cursor:not-allowed;">
                        <small style="color:var(--color-ink-400);font-size:11px;">Your QU ID is your university email address.</small>
                    </div>
                </div>

                {{-- Right Column — Editable --}}
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">
                        <i class="fas fa-pen" style="color:var(--brand-500);margin-right:4px;"></i> Preferences
                    </div>

                    <div class="form-group">
                        <label for="nationality_id" class="form-label">Nationality</label>
                        <select name="nationality_id" id="nationality_id" class="form-select">
                            <option value="">-- Select Nationality --</option>
                            @foreach($nationalities as $nat)
                                <option value="{{ $nat->id }}" {{ old('nationality_id', $user->nationality_id) == $nat->id ? 'selected' : '' }}>{{ $nat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="faculty" id="faculty"
                                {{ old('faculty', $user->faculty) ? 'checked' : '' }} value="1">
                            <label class="form-check-label" for="faculty">Faculty Member</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <span class="pill {{ $user->is_active ? 'success' : 'inactive' }}" style="font-size:12px;">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--ink-100);">
                <a href="{{ route('home') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn-primary" id="profileSubmitBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#profileSubmitBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                showToast('success', resp.message || 'Profile updated successfully.');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
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
</script>
@endpush

@push('scripts')
<script>
function toggleProjectMenu(btn) {
    var menu = btn.nextElementSibling;
    var wasOpen = menu.style.display === 'block';
    closeProjectMenus();
    if (!wasOpen) {
        var rect = btn.getBoundingClientRect();
        menu.style.left = (rect.right - 180) + 'px';
        menu.style.top = (rect.bottom + 2) + 'px';
        menu.style.display = 'block';
    }
}

function closeProjectMenus() {
    document.querySelectorAll('.action-menu').forEach(function(m) { m.style.display = 'none'; });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        closeProjectMenus();
    }
});

var singleFile = null;
var singleProjectId = null;

function openSingleUploadModal(projectId, projectTitle) {
    singleFile = null;
    singleProjectId = projectId;
    $('#singleUploadProjectId').text(projectTitle);
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
</script>
@endpush

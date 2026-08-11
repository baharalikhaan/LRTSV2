@extends('layouts.app')
@section('title', 'Reviewer Grading - RTS')
@section('content')

<style>
.rg-info-bar{background:#fff;border:1px solid var(--ink-100,#eeedf0);border-radius:var(--fluent-radius-md,6px);box-shadow:var(--fluent-depth-2);padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.rg-info-left{display:flex;align-items:center;gap:20px;flex:1;min-width:0}
.rg-info-stat{text-align:center;padding:0 16px;border-right:1px solid var(--ink-100,#eeedf0)}
.rg-info-stat:last-child{border-right:none}
.rg-info-stat .rg-stat-val{font-weight:700;font-size:16px;color:var(--brand-500,#8d1b3d)}
.rg-info-stat .rg-stat-label{font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400,#8b8592);font-weight:500}
.rg-info-name{font-weight:700;font-size:14px;color:var(--ink-800,#241f2a)}
.rg-info-email{font-size:11px;color:var(--ink-400,#8b8592)}
.rg-main{display:grid;grid-template-columns:1fr 300px;gap:16px;align-items:start}
.rg-left{min-width:0}
.rg-right{position:sticky;top:20px}
.rg-card{background:#fff;border:1px solid var(--ink-100,#eeedf0);border-radius:var(--fluent-radius-md,6px);box-shadow:var(--fluent-depth-2);margin-bottom:16px;overflow:hidden}
.rg-card-header{padding:10px 16px;font-size:12px;font-weight:600;color:var(--ink-700,#38333e);background:var(--sand-50,#faf7f0);border-bottom:1px solid var(--ink-100,#eeedf0);display:flex;align-items:center;gap:6px}
.rg-card-body{padding:16px}
.rg-selector-row{display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap}
.rg-selector-left{flex:1;min-width:200px}
.rg-selector-right{display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.rg-selector-stat{font-size:11px;color:var(--ink-500,#675f6e)}
.rg-selector-stat strong{color:var(--brand-500,#8d1b3d)}
.rg-select{width:100%;padding:7px 10px;border:1px solid var(--ink-200,#d8d6dc);border-radius:var(--fluent-radius-sm,4px);font-size:12px;font-family:inherit;color:var(--ink-700,#38333e);background:#fff;transition:border-color .15s}
.rg-select:focus{outline:none;border-color:var(--brand-500,#8d1b3d);box-shadow:0 0 0 2px rgba(141,27,61,.1)}
.rg-project-card{border:1px solid var(--ink-100,#eeedf0);border-radius:var(--fluent-radius-sm,4px);margin-bottom:10px;overflow:hidden;transition:box-shadow .15s;display:flex}
.rg-project-card:last-child{margin-bottom:0}
.rg-project-card:hover{box-shadow:var(--fluent-depth-4)}
.rg-project-id{background:var(--brand-500,#8d1b3d);color:#fff;font-weight:700;font-size:12px;text-align:center;padding:10px 12px;writing-mode:vertical-lr;text-orientation:mixed;letter-spacing:.05em;min-width:36px;display:flex;align-items:center;justify-content:center}
.rg-project-body{padding:12px 14px;flex:1;min-width:0}
.rg-badge{display:inline-block;padding:2px 8px;border-radius:3px;font-size:10px;font-weight:600}
.rg-badge-pending{background:#fff8e1;color:#f57f17;border:1px solid #ffe082}
.rg-badge-graded{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7}
.rg-gtable{width:100%;border-collapse:collapse;font-size:11px;margin-top:8px}
.rg-gtable th{background:var(--sand-50,#faf7f0);color:var(--ink-600,#4c4553);font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.03em;padding:5px 8px;border:.5px solid var(--ink-100,#eeedf0)}
.rg-gtable td{padding:5px 8px;border:.5px solid var(--ink-100,#eeedf0);text-align:center;vertical-align:middle}
.rg-g-val{font-weight:700;color:var(--brand-500,#8d1b3d)}
.rg-g-comment{font-size:10px;color:var(--ink-400,#8b8592);font-style:italic}
.rg-rl{background:var(--brand-500,#8d1b3d);color:#fff;font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.03em}
.rg-rl-alt{background:var(--sand-500,#ab8140);color:#fff;font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.03em}
.rg-rating-card{background:var(--sand-50,#faf7f0)}
.rg-rating-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;gap:8px}
.rg-rating-row:last-of-type{margin-bottom:0}
.rg-rating-label{font-size:11px;font-weight:600;color:var(--ink-600,#4c4553);white-space:nowrap;min-width:110px;text-transform:uppercase;letter-spacing:.03em}
.rg-star-rating{display:inline-flex;flex-direction:row-reverse;gap:1px}
.rg-star-rating input{display:none}
.rg-star-rating label{cursor:pointer;font-size:16px;color:var(--ink-200,#d8d6dc);transition:color .1s;line-height:1;padding:0 1px}
.rg-star-rating label::before{content:"\2605"}
.rg-star-rating input:checked~label,.rg-star-rating label:hover,.rg-star-rating label:hover~label{color:var(--brand-500,#8d1b3d)}
.rg-rating-val{font-size:12px;font-weight:700;color:var(--brand-500,#8d1b3d);min-width:20px;text-align:center}
.rg-rating-msg{font-size:11px;color:var(--ink-400,#8b8592);display:block;margin-top:4px}
.rg-rating-msg.rg-ok{color:var(--success,#1f8a5f)}
.rg-btn-save{display:block;width:100%;padding:8px 16px;background:var(--brand-500,#8d1b3d);color:#fff;border:none;border-radius:var(--fluent-radius-sm,4px);font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;transition:all .15s;box-shadow:var(--fluent-depth-2);margin-top:14px}
.rg-btn-save:hover{background:var(--brand-600,#7a1636);box-shadow:var(--fluent-depth-4)}
.rg-btn-save:disabled{opacity:.5;cursor:not-allowed}
.rg-btn-detail{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;background:var(--brand-500,#8d1b3d);color:#fff;border:none;border-radius:var(--fluent-radius-sm,4px);font-size:12px;font-weight:600;font-family:inherit;text-decoration:none;transition:all .15s;box-shadow:var(--fluent-depth-2)}
.rg-btn-detail:hover{background:var(--brand-600,#7a1636);color:#fff;box-shadow:var(--fluent-depth-4)}
.rg-empty{text-align:center;padding:40px 20px;color:var(--ink-400,#8b8592)}
.rg-empty i{font-size:36px;color:var(--ink-200,#d8d6dc);margin-bottom:10px;display:block}
.rg-empty .rg-et{font-size:14px;font-weight:500;color:var(--ink-500,#675f6e);margin-bottom:2px}
.rg-empty .rg-es{font-size:11px;color:var(--ink-300,#b4b0ba)}
@media(max-width:992px){.rg-main{grid-template-columns:1fr}.rg-right{position:static}.rg-selector-row{flex-direction:column}}
</style>

<div>
    <div class="rg-info-bar">
        <div class="rg-info-left">
            <div><div class="rg-info-name">{{ $user->name }}</div><div class="rg-info-email">{{ $user->email }}</div></div>
            <div class="rg-info-stat"><div class="rg-stat-val">{{ $totalProjects }}</div><div class="rg-stat-label">Projects</div></div>
            <div class="rg-info-stat"><div class="rg-stat-val" id="totalPrograms">{{ count($programs) }}</div><div class="rg-stat-label">Research Calls</div></div>
        </div>
        <a href="{{ route('reviewer-grading.detail', ['u_id' => $user->id]) }}" class="rg-btn-detail"><i class="fas fa-chart-line"></i> View Details</a>
    </div>

    <div class="rg-main">
        <div class="rg-left">
            <div class="rg-card">
                <div class="rg-card-header"><i class="fas fa-layer-group"></i> Select Research Call</div>
                <div class="rg-card-body">
                    <div class="rg-selector-row">
                        <div class="rg-selector-left">
                            <select class="rg-select" id="programDropdown">
                                <option value="">— Select a Research Call —</option>
                                @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->program_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="rg-selector-right">
                            <div class="rg-selector-stat"><strong>Selected:</strong> <span id="programTitle">—</span></div>
                            <div class="rg-selector-stat"><strong>Projects:</strong> <span id="programProjectCount">0</span></div>
                            <div class="rg-selector-stat"><strong>Reviewed:</strong> <span id="statReviewed">0</span></div>
                            <div class="rg-selector-stat"><strong>Pending:</strong> <span id="statPending">0</span></div>
                            <div class="rg-selector-stat"><strong>Avg:</strong> <span id="statAvgRating">—</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rg-card">
                <div class="rg-card-header"><i class="fas fa-diagram-project"></i> Projects in Research Call</div>
                <div class="rg-card-body" id="projectsContainer">
                    @include('admin.partials.reviewer-projects-empty')
                </div>
            </div>
        </div>
        <div class="rg-right">
            <div class="rg-card rg-rating-card">
                <div class="rg-card-header"><i class="fas fa-star"></i> Rate Reviewer <span id="avgRatingLabel" style="font-weight:400;font-size:10px;opacity:.7;margin-left:4px;"></span></div>
                <div class="rg-card-body">
                    <form method="POST" action="{{ route('reviewer-grading.save-ratings') }}" id="ratingForm">
                        @csrf
                        <input type="hidden" name="reviewer" value="{{ $user->id }}">
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                        <input type="hidden" id="program_id" name="program_id" value="">
                        @foreach(['conflict'=>'Conflict','responsiveness'=>'Responsiveness','comprehensiveness'=>'Comprehensiveness','no_reviewers'=>'No. of Reviews','behaviour'=>'Behaviour'] as $k=>$l)
                        <div class="rg-rating-row">
                            <label class="rg-rating-label">{{ $l }}</label>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <div class="rg-star-rating" data-for="{{ $k }}">
                                    @for($i=5;$i>=1;$i--)
                                    <input type="radio" id="{{ $k }}-{{ $i }}" name="{{ $k }}" value="{{ $i }}">
                                    <label for="{{ $k }}-{{ $i }}"></label>
                                    @endfor
                                </div>
                                <span class="rg-rating-val" id="{{ $k }}-val">0</span>
                            </div>
                        </div>
                        @endforeach
                        <hr style="border-color:var(--ink-200,#d8d6dc);margin:14px 0;">
                        <span class="rg-rating-msg" id="ratingStatus"></span>
                        <span class="rg-rating-msg rg-ok" id="ratingAvgDisplay"></span>
                        <span class="rg-rating-msg" id="flashMsg">
                            @if(session('successrating')){!! session('successrating') !!}@php session()->forget('successrating') @endphp@endif
                        </span>
                        <button type="submit" class="rg-btn-save" id="saveRatingBtn" disabled><i class="fas fa-save"></i> Save Ratings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    function updateRatingDisplay(f){var v=$('input[name="'+f+'"]:checked').val()||0;$('#'+f+'-val').text(v)}
    function updateAvgLabel(){var s=0,c=0;['conflict','responsiveness','comprehensiveness','no_reviewers','behaviour'].forEach(function(f){var v=parseInt($('input[name="'+f+'"]:checked').val()||0);s+=v;if(v>0)c++});var a=c>0?(s/5).toFixed(1):'0.0';$('#avgRatingLabel').text('— Avg: '+a+' / 5');return a}
    $('.rg-star-rating input[type="radio"]').on('change',function(){updateRatingDisplay($(this).attr('name'));updateAvgLabel()});
    ['conflict','responsiveness','comprehensiveness','no_reviewers','behaviour'].forEach(function(f){updateRatingDisplay(f)});
    updateAvgLabel();

    $('#programDropdown').change(function(){
        var pid=$(this).val();$('#program_id').val(pid);var uid='{{ $user->id }}';
        if(!pid){$('#projectsContainer').html('<div class="rg-empty"><i class="fas fa-folder-open"></i><div class="rg-et">Select a Research Call</div><div class="rg-es">Choose a research call above to see the reviewer\'s assigned projects</div></div>');$('#programTitle').text('—');$('#programProjectCount').text('0');$('#saveRatingBtn').prop('disabled',true);return}
        $('#programTitle').text($('#programDropdown option:selected').text());
        $.ajax({url:'{{ route("ajaxListreviewerGrading") }}',type:'GET',data:{program_id:pid,user_id:uid},success:function(r){
            var p=r.projects||[],rt=r.ratings;$('#programProjectCount').text(p.length);
            var rc=0,pc=0;$.each(p,function(i,x){if(x.gradeA!==null)rc++;else pc++});
            $('#statReviewed').text(rc);$('#statPending').text(pc);
            if(p.length===0){$('#projectsContainer').html('<div class="rg-empty"><i class="fas fa-inbox"></i><div class="rg-et">No Projects Found</div><div class="rg-es">This reviewer has no projects assigned in the selected program</div></div>')}
            else{var h='';$.each(p,function(i,x){
                var g=x.gradeA!==null,pr=x.progressGrade!==null&&x.progressGrade!==undefined;
                h+='<div class="rg-project-card">';
                h+='<div class="rg-project-id">'+(x.old_project_id||x.id)+'</div>';
                h+='<div class="rg-project-body">';
                if(!g){var dl=new Date(x.deadline);dl.setTime(dl.getTime()+14*864e5);
                    h+='<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">';
                    h+='<span class="rg-badge rg-badge-pending"><i class="fas fa-clock"></i> Not Yet Reviewed</span>';
                    h+='<span style="font-size:10px;color:var(--ink-400,#8b8592);">Deadline: '+dl.toLocaleDateString()+'</span></div>';
                    h+='<div style="font-size:11px;color:var(--ink-500,#675f6e);">Proposal Status: '+(x.proposalstatus||'Accepted')+'</div>'}
                else{
                    h+='<table class="rg-gtable"><thead><tr>';
                    h+='<th style="width:80px;"></th><th>Achievements</th><th>Publications</th><th>Student Involvement</th><th>Project Impact / Budget</th>';
                    h+='</tr></thead><tbody>';
                    h+='<tr><td class="rg-rl">Final Report</td>';
                    h+='<td><span class="rg-g-val">'+(x.gradeA??'-')+'</span><br><span class="rg-g-comment">'+(x.commentA??'')+'</span></td>';
                    h+='<td><span class="rg-g-val">'+(x.gradeB??'-')+'</span><br><span class="rg-g-comment">'+(x.commentB??'')+'</span></td>';
                    h+='<td><span class="rg-g-val">'+(x.gradeD??'-')+'</span><br><span class="rg-g-comment">'+(x.commentD??'')+'</span></td>';
                    h+='<td><span class="rg-g-val">'+(x.gradeC??'-')+'</span><br><span class="rg-g-comment">'+(x.commentC??'')+'</span></td></tr>';
                    if(pr){h+='<tr><td class="rg-rl-alt">Progress Report</td>';
                    h+='<td><span class="rg-g-val">'+(x.achievementsRating??'-')+'</span><br><span class="rg-g-comment">'+(x.achievementsComments??'-')+'</span></td>';
                    h+='<td><span class="rg-g-val">'+(x.publicationsRating??'-')+'</span><br><span class="rg-g-comment">'+(x.publicationsComments??'-')+'</span></td>';
                    h+='<td><span class="rg-g-val">'+(x.studentsRating??'-')+'</span><br><span class="rg-g-comment">'+(x.studentsComments??'-')+'</span></td>';
                    h+='<td><span class="rg-g-val">'+(x.budgetRating??'-')+'</span><br><span class="rg-g-comment">'+(x.budgetComments??'-')+'</span></td></tr>'}
                    h+='</tbody></table>'}
                h+='</div></div>'});
                $('#projectsContainer').html(h)}
            if(rt){setRatingValues(rt);$('#ratingStatus').text('Rating already set for this research call').css('color','var(--brand-500,#8d1b3d)');
                var av=((parseInt(rt.conflict||0)+parseInt(rt.responsiveness||0)+parseInt(rt.comprehensiveness||0)+parseInt(rt.no_reviewers||0)+parseInt(rt.behaviour||0))/5).toFixed(1);
                $('#ratingAvgDisplay').html('Average Rating: <strong>'+av+'</strong> / 5');$('#statAvgRating').text(av);updateAvgLabel();$('#flashMsg').html('');$('#saveRatingBtn').prop('disabled',false)}
            else{resetRatingValues();$('#ratingStatus').text('');$('#ratingAvgDisplay').text('');$('#statAvgRating').text('—');$('#avgRatingLabel').text('');$('#flashMsg').html('');$('#saveRatingBtn').prop('disabled',false)}
        },error:function(){$('#projectsContainer').html('<div class="rg-empty"><i class="fas fa-exclamation-triangle" style="color:var(--danger,#b3261e);"></i><div class="rg-et">Error loading projects</div><div class="rg-es">Please try again</div></div>')}});
    });
    function setRatingValues(rt){['conflict','responsiveness','comprehensiveness','no_reviewers','behaviour'].forEach(function(f){var v=rt[f]||0;$('input[name="'+f+'"][value="'+v+'"]').prop('checked',true);$('#'+f+'-val').text(v)})}
    function resetRatingValues(){['conflict','responsiveness','comprehensiveness','no_reviewers','behaviour'].forEach(function(f){$('input[name="'+f+'"]').prop('checked',false);$('#'+f+'-val').text('0')})}
    $('#ratingForm').on('submit',function(e){if(!$('#program_id').val()){e.preventDefault();alert('Please select a research call first.')}});
});
</script>
@endpush

<form method="POST" action="{{ route('progressGrade')}}">
  @csrf
  <input type="text" name="p_id" value={{$p_id}} hidden>
  <label><b>Analysis</b></label><br>
  <textarea id="analysis" name="analysis" rows="4" cols="40">{{$progressDraft->analysis}}</textarea><br><br>
  <label><b>Summative Comments and Remarks</b></label><br>
  <textarea id="remarks" name="comments" rows="4" cols="40">{{$progressDraft->comments}}</textarea><br><br>
  <label><b>Recommendation</b></label><br>
  <textarea id="recommendation" name="recommendation" rows="4" cols="40">{{$progressDraft->recommendation}}</textarea><br><br>
  <button type="submit" name="draft" value="draft">
    Save As Draft
  </button>
  &nbsp;
  <button type="submit" class="btn btn-primary" name="publish" value="publish" align="center">
    {{ __('Submit') }}
  </button>

</form>
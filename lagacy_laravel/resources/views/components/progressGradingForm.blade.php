<style>
    ol {
        list-style-type: none;
        /* Remove default bullet */
    }

    ol li::before {
        font-weight: bold;
        /* Make bullet bold */
        display: inline-block;
        width: 1em;
        /* Adjust spacing if needed */
        margin-left: -1em;
        /* Adjust spacing if needed */
    }
</style>

<form method="POST" action="{{ route('progressGrade') }}">
    @csrf
    <input type="text" name="p_id" value={{ $p_id }} hidden>
    <input type="text" name="report_type" value="{{ $reportType ?? 'Progress' }}" hidden>

    <input type="text" name="analysis" value="0" hidden>
    <input type="text" name="recommendation" value="0" hidden>
    <input type="text" name="comments" value="0" hidden>


    <p style="color:teal; font-size:12">
        Kindly evaluate the progress report based on the following criteria on a scale of 1 to 5, where 1 indicates the
        highest level of dissatisfaction and 5 indicates the highest level of satisfaction.
    </p>

    <label><b>1. Progress Toward Achieving Outcomes:</b></label><br>

    <ol type="a">
        <li><b>a.</b> Degree of progress made towards realizing the proposed outcomes in the project.</li>
        <li><b>b.</b> Does the project demonstrate advancement towards producing a prototype, patent, or open-source
        <li>
            software?


             <select id="achievementsRating" name="achievementsRating" autocomplete="off">
                 <option value="1" {{ old('achievementsRating', $progressComments->achievementsRating ?? '') == '1' ? 'selected' : '' }}>1</option>
                 <option value="2" {{ old('achievementsRating', $progressComments->achievementsRating ?? '') == '2' ? 'selected' : '' }}>2</option>
                 <option value="3" {{ old('achievementsRating', $progressComments->achievementsRating ?? '') == '3' ? 'selected' : '' }}>3</option>
                 <option value="4" {{ old('achievementsRating', $progressComments->achievementsRating ?? '') == '4' ? 'selected' : '' }}>4</option>
                 <option value="5" {{ old('achievementsRating', $progressComments->achievementsRating ?? '') == '5' ? 'selected' : '' }}>5</option>
             </select>
            <br>
            <textarea id="achievementsComments" name="achievementsComments" rows="4" cols="40"
                placeholder="comments upto 500 characters (optional)">{{ old('achievementsComments', $progressComments->achievementsComments ?? '') }}</textarea>
            @if ($errors->has('achievementsComments'))
                <span class="text-danger">{{ $errors->first('achievementsComments') }}</span>
            @endif
            <br><br>
    </ol>

    <label><b>2. Progress in Publications:</b></label>
    <ol type="a">
        <li><b>a.</b> Progress in generating publications in high-ranked journals since the start of the project. </li>


         <select id="publicationsRating" name="publicationsRating" autocomplete="off">
             <option value="1" {{ old('publicationsRating', $progressComments->publicationsRating ?? '') == '1' ? 'selected' : '' }}>1</option>
             <option value="2" {{ old('publicationsRating', $progressComments->publicationsRating ?? '') == '2' ? 'selected' : '' }}>2</option>
             <option value="3" {{ old('publicationsRating', $progressComments->publicationsRating ?? '') == '3' ? 'selected' : '' }}>3</option>
             <option value="4" {{ old('publicationsRating', $progressComments->publicationsRating ?? '') == '4' ? 'selected' : '' }}>4</option>
             <option value="5" {{ old('publicationsRating', $progressComments->publicationsRating ?? '') == '5' ? 'selected' : '' }}>5</option>
         </select>
        <br>
        <textarea id="publicationsComments" name="publicationsComments" rows="4" cols="40"
            placeholder="comments upto 500 characters (optional)">{{ old('publicationsComments', $progressComments != null ? $progressComments->publicationsComments : '') }}</textarea>
        @if ($errors->has('publicationsComments'))
            <span class="text-danger">{{ $errors->first('publicationsComments') }}</span>
        @endif
        <br><br>

    </ol>



    <label><b>3. Engagement in Student Involvement and Capacity Building:</b></label><br>

    <ol type="a">
        <li><strong> a. </strong>Level of engagement of students and other project members in the ongoing project
            activities.</li>
        <br>
         <select id="studentsRating" name="studentsRating" autocomplete="off">
             <option value="1" {{ old('studentsRating', $progressComments->studentsRating ?? '') == '1' ? 'selected' : '' }}>1</option>
             <option value="2" {{ old('studentsRating', $progressComments->studentsRating ?? '') == '2' ? 'selected' : '' }}>2</option>
             <option value="3" {{ old('studentsRating', $progressComments->studentsRating ?? '') == '3' ? 'selected' : '' }}>3</option>
             <option value="4" {{ old('studentsRating', $progressComments->studentsRating ?? '') == '4' ? 'selected' : '' }}>4</option>
             <option value="5" {{ old('studentsRating', $progressComments->studentsRating ?? '') == '5' ? 'selected' : '' }}>5</option>
         </select>


        <br>

        <textarea id="studentsComments" name="studentsComments" rows="4" cols="40"
            placeholder="comments upto 500 characters (optional)">{{ old('studentsComments', $progressComments != null ? $progressComments->studentsComments : '') }}</textarea>
        @if ($errors->has('studentsComments'))
            <span class="text-danger">{{ $errors->first('studentsComments') }}</span>
        @endif
        <br><br>
    </ol>


    <label><b>4. Please verify if the necessary ethical approvals have been included with the progress
            report.</b></label><br>

    <ol type="a">
        <br>
        <div class="container d-flex justify-content-center align-items-center">

            @if ($progressComments)
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-toggle {{ $progressComments->ethical == 1 ? 'active' : '' }}">
                        <input type="radio" name="ethical" value="1" autocomplete="off"
                            @if ($progressComments->ethical == 1) checked @endif> YES
                    </label>
                    <label class="btn btn-toggle {{ $progressComments->ethical == -1 ? 'active' : '' }}">
                        <input type="radio" name="ethical" value="-1" autocomplete="off"
                            @if ($progressComments->ethical == -1) checked @endif> N/A
                    </label>
                    <label class="btn btn-toggle {{ $progressComments->ethical == 0 ? 'active' : '' }}">
                        <input type="radio" name="ethical" value="0" autocomplete="off"
                            @if ($progressComments->ethical == 0) checked @endif> NO
                    </label>
                </div>
            @else
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-toggle ">
                        <input type="radio" name="ethical" value="1" autocomplete="off"> YES
                    </label>
                    <label class="btn btn-toggle">
                        <input type="radio" name="ethical" value="-1" autocomplete="off"> N/A
                    </label>
                    <label class="btn btn-toggle ">
                        <input type="radio" name="ethical" value="0" autocomplete="off"> NO
                    </label>
                </div>
            @endif
        </div>
        <br>
    </ol>

    <label><b>5. Budget Utilization</b></label><br>

    <ol type="a">
        <li><strong> a. </strong>  How adequate do you find the project’s budget utilization?</li>
        <li><strong> b. </strong>The project budget should be structured to ensure that at least <strong> 60% </strong> is utilized within the first year.</li>
        <br>
         <select id="budgetRating" name="budgetRating" autocomplete="off">
             <option value="1" {{ old('budgetRating', $progressComments->budgetRating ?? '') == '1' ? 'selected' : '' }}>1</option>
             <option value="2" {{ old('budgetRating', $progressComments->budgetRating ?? '') == '2' ? 'selected' : '' }}>2</option>
             <option value="3" {{ old('budgetRating', $progressComments->budgetRating ?? '') == '3' ? 'selected' : '' }}>3</option>
             <option value="4" {{ old('budgetRating', $progressComments->budgetRating ?? '') == '4' ? 'selected' : '' }}>4</option>
             <option value="5" {{ old('budgetRating', $progressComments->budgetRating ?? '') == '5' ? 'selected' : '' }}>5</option>
         </select>
        <br>

        <textarea id="budgetComments" name="budgetComments" rows="4" cols="40"
            placeholder="comments upto 500 characters (optional)">{{ old('budgetComments', $progressComments != null ? $progressComments->budgetComments : '') }}</textarea>
        @if ($errors->has('budgetComments'))
            <span class="text-danger">{{ $errors->first('budgetComments') }}</span>
        @endif

    </ol>


    <label><b>6. Recommendation for Continuation:</b></label><br>
    <ol type="a">
        <li><strong> Accept: </strong> The progress report demonstrates sufficient progress and potential for
            continuation of the project.</li>
        <li><strong>Reject: </strong>The progress report does not meet expectations for continuation of the project at
            this stage.</li><br>

        <div class="vc-toggle-container" style="align:center;">
            <label class="vc-switch">
                <input type="checkbox" class="vc-switch-input" id="isAccepted" name="isAccepted"
                    {{ $progressComments != null ? ($progressComments->isAccepted == 1 ? 'checked' : '') : '' }} />
                <span data-on="Accepted" data-off="Rejected" class="vc-switch-label"></span>
                <span class="vc-handle"></span>
            </label>
        </div>
    </ol>

    {{-- <b>5. </b> Does this project requires ethical approval?
    @if ($commitments)
        <b> {{ $commitments->ethical = 0 ? 'No' : 'Yes' }} </b>
    @endif
    <br> --}}
    <br>
    <div class="btn-group" role="group" aria-label="Basic button group">
        <button type="submit" class="btn btn-warning" role="group" name="draft" value="draft">
            Save As Draft
        </button>
        &nbsp;
        <button type="submit" class="btn btn-primary" role="group" name="publish" value="publish">
            {{ __('Submit') }}
        </button>
    </div>
</form>

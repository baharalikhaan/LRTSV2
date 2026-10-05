<div>

    <h3 class="text-center"><b>Final Report</b></h3>


    <i>
        <p class="text-to-open-modal" data-toggle="modal"
            data-target="#helpfinal" align="center"> Help
            regarding filling the
            form </p>
    </i>

    <form method="POST" action="{{ route('finalGrades') }}">
        @csrf
        <input type="text" name="p_id"
            value="{{ $p_id }}" hidden>

        <label class="col-form-label"><b>1.
                Achievements</b></label><br>
        <p>Degree of realization of the proposed outcomes in the
            project. Does the
            project produce a Prototype, Patent, Open Source
            Software, etc.? If a
            Prototype is achieved, state its TRL level (or SRL for
            society
            readiness)</p>

        <table class="table">
            @foreach ($contributions as $contribution)
                <tr>
                    <td>
                        <input type="checkbox" name="option1"
                            value="{{ $contribution['score'] }}"
                            checked>
                    </td>
                    <td>{{ $typeMappings[$contribution['type']] ?? $contribution['type'] }}
                    </td>
                    <td>{{ $contribution['detail'] }}<br></td>
                </tr>
            @endforeach
        </table>

        <label>a. Score</label>
        <input type="text" id="gradeA" name='gradeA'
            size="2" readonly>

        <br>
        <label>b. Comment</label><br>
        <textarea id="commentA" name="commentA" rows="4" cols="40"></textarea><br><br>

        <label class="col-form-label"><b>2.
                Publications</b></label><br>

        <p>Number of Q1/Q2 publications in ranked journals. Number
            of Q1
            publications in highly ranked journals. Number and
            quality of Books,
            Chapters, etc</p>

        <p style="color:red; font-size:12px; font-style:italic">
            (Kindly visit each
            research artifact and ensure that the LPI has
            acknowledged the QU
            funding in the artifact. If not acknowledged, then
            uncheck the artifact
            from the list.)</p>

        <table id="myTable" class="table">
            <tr>
                <th></th>
                <th>Identifier</th>
                <th>Type</th>
                <th>Publish date</th>
                <th>Link</th>
            </tr>

            @foreach ($outcomes as $outcome)
                <tr>
                    <td>
                        <input type="checkbox" name="option2"
                            value="{{ $outcome['score'] }}"
                            checked>
                    </td>

                    <td class="open-apimodal" data-toggle="modal"
                        data-mydata="{{ $outcome['identifier'] }}">
                        {{ $outcome['identifier'] }}</td>
                    <td>{{ $outcome['type'] }}</td>
                    <td> {{ \Carbon\Carbon::parse($outcome['publication_date'])->format('d-m-Y') }}
                    </td>
                    {{-- <td><a href={{ $outcome['url'] }}> link </a></td> --}}
                    <td><a href="{{ $outcome['url'] }}"
                            target="_blank">link</a>
                    </td>


                </tr>
            @endforeach
        </table>
        <br>
        <label>a. Score</label>
        <input id="gradeB" type="text" name='gradeB'
            size="2" readonly>
        <br>
        <label>b. Comment</label><br>
        <textarea id="commentB" name="commentB" rows="4" cols="40"></textarea><br><br>

        <b>3. Student and young researchers involements</b><br><br>
        <input type="radio" id="Yes" value="Yes"
            name="yesno" onclick="show();" checked>
        <label for="Yes">Yes</label><br>
        <input type="radio" id="No" value="No"
            name="yesno" onclick="hide();">
        <label for="No">No</label><br>

        <div id="YR">
            <p>Level of engagement of graduate students in the
                activities of the
                project. Training of undergraduate students. Such
                as involvement of
                RAs and GAs in the project</p>
            <!-- <label class="col-form-label"><b>3. Young Researcher Supervision</b></label><br> -->
            <table class="table">
                <th></th>
                <th>Student ID</th>
                <th>Level</th>
                <th>Days</th>
                @foreach ($students as $student)
                    <tr>
                        <td>
                            <input type="checkbox" name="option3"
                                value="{{ $student['score'] }}"
                                checked>
                        </td>

                        <td class="open-apimodal2"
                            data-toggle="modal"
                            data-mydata="{{ $student['std_id'] }}">
                            {{ $student['std_id'] }}</td>


                        <td>{{ $typeMappings[$student['type']] ?? $student['type'] }}
                        </td>
                        <td>{{ $student['days'] }}</td>
                        <td><br></td>
                    </tr>
                @endforeach
            </table>
            <br>
            <label>a. Score</label>
            <input id="gradeD" type="text" name='gradeD'
                size="2" readonly><br>
            <label>b. Comment</label><br>
            <textarea id="commentD" name="commentD" rows="4" cols="40"></textarea><br><br>
        </div>

        <label class="col-form-label"><b>4. Project
                Impact</b></label><br>
        <p>Has the project provided concise KPIs for the proposed
            outcomes? The
            value of the reported outcomes (e.g., KPIs) in
            comparison to what was
            suggested in the proposal on
            industry/society/government, etc. The
            potential to benefit society or advance desired
            economical (e.g.,
            technology transfer) and societal outcomes (e.g.
            capacity building of
            students and researchers, change in policy). The level
            of engagement
            with end-users. Extent to which end-users locally and
            internationally
            may realistically benefit from the outcomes. The
            relevance of the
            project to partners’ development with respect to
            industrial
            development, socio-economic, health and environmental
            aspects and the
            ability to address end-user needs, as well as the
            potential to create
            positive international scientific visibility for the
            partners (if any).
        </p>

        <label>a. Score</label>
        <input id="gradeC" type="text" name='gradeC'
            value=0 size="2">
        <br>
        <label>b. Comment</label><br>
        <textarea id="commentC" name="commentC" rows="4" cols="40"></textarea>
        <br><br>



        <button type="submit" name="draft" value="draft"
            class="btn btn-secondary">
            Save As Draft
        </button>
        <button type="submit" class="btn btn-primary"
            name="publish" value="publish">
            {{ __('Submit') }}
        </button>

    </form>
</div>

<style>
    table,
    td,
    th {
        border-bottom: 1px solid #ddd;
        text-align: left;
        font-size: 14;
    }
</style>

<div id="progress_form">

    {{-- <b color='teal'>1. Progress Toward Achieving Outcomes:</b><br>
    <p><b> Rating:</b> {{ $progressComments->rt1 }}</p>
    <p><b> Comments:</b> {{ $progressComments->achievementsComments }}</p>


    <b>2. Progress in Publications:</b><br>
    <p><b> Rating:</b> {{ $progressComments->rt2 }}</p>
    <p><b> Comments:</b> {{ $progressComments->publicationsComments }}</p>


    <b>3. Engagement in Student Involvement and Capacity Building:</b><br>
    <p><b> Rating:</b> {{ $progressComments->rt3 }}</p>
    <p><b> Comments:</b> {{ $progressComments->studentsComments }}</p>

    <b> 4. Recommendation for Continuation:</b><br>
    <i>{{ $progressComments->acceptance }}</i>
 --}}


    <table class="table table-striped">
        <thead>
            <tr>
                <th>Progress Report Date:</th>
                <th colspan="2">
                    {{ $progressComments->progress_report_date ? date('d-m-Y', strtotime($progressComments->progress_report_date)) : '---' }}
                </th>

            </tr>

            <tr>
                <th>Progress Grading Date:</th>
                <th colspan="2">
                    {{ $progressComments->updated_at ? date('d-m-Y', strtotime($progressComments->updated_at)) : '---' }}
                </th>

            </tr>

            <tr>
                <th>Aspect</th>
                <th>Rating</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>


            <tr>
                <td>1. Progress Toward Achieving Outcomes:</td>
                <td
                    style="color: {{ $progressComments->rt1 === 'Satisfied' ? 'green' : ($progressComments->rt1 === 'Very Satisfied' ? 'green' : ($progressComments->rt1 === 'Unsure' ? 'orange' : 'red')) }}">
                    {{ $progressComments->rt1 }}</td>
                <td>{{ $progressComments->achievementsComments }}</td>
            </tr>
            <tr>
                <td>2. Progress in Publications:</td>
                <td
                    style="color: {{ $progressComments->rt2 === 'Satisfied' ? 'green' : ($progressComments->rt2 === 'Very Satisfied' ? 'green' : ($progressComments->rt2 === 'Unsure' ? 'orange' : 'red')) }}">
                    {{ $progressComments->rt2 }}</td>
                <td>{{ $progressComments->publicationsComments }}</td>
            </tr>
            <tr>
                <td>3. Engagement in Student Involvement and Capacity Building:</td>
                <td
                    style="color: {{ $progressComments->rt3 === 'Satisfied' ? 'green' : ($progressComments->rt3 === 'Very Satisfied' ? 'green' : ($progressComments->rt3 === 'Unsure' ? 'orange' : 'red')) }}">
                    {{ $progressComments->rt3 }}</td>
                <td>{{ $progressComments->studentsComments }}</td>
            </tr>
            <tr>
                <td>4. Recommendation for Continuation:</td>
                <td colspan="2" style="color: {{ $progressComments->acceptance === 'Accepted' ? 'green' : 'red' }}">
                    {{ $progressComments->acceptance }}</td>
            </tr>


            <tr>
                <td>5. Budget Utilization:</td>
                <td
                    style="color: {{ $progressComments->rt4 === 'Satisfied' ? 'green' : ($progressComments->rt4 === 'Very Satisfied' ? 'green' : ($progressComments->rt4 === 'Unsure' ? 'orange' : 'red')) }}">
                    {{ $progressComments->rt4 }}</td>
                <td>{{ $progressComments->budgetComments }}</td>
            </tr>

        </tbody>
    </table>



</div>

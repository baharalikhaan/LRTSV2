<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


</head>

<style>
    table,
    td,
    th {
        border-bottom: 1px solid #ddd;
        text-align: left;
        font-size: 14;

    }
</style>
<div id="final_form">



    <table class="table table-striped">

        <thead>

            <tr>
                <th>Final Report Date:</th>
                <th colspan="2">
                    {{ $finalGrades->final_report_date ? date('d-m-Y', strtotime($finalGrades->final_report_date)) : '---' }}
                </th>

            </tr>

            <tr>
                <th>Final Grading Date:</th>
                <th colspan="2">
                    {{ $finalGrades->updated_at ? date('d-m-Y', strtotime($finalGrades->updated_at)) : '---' }}
                </th>

            </tr>



   @if($finalGrades->grant_type=="regular")




            <tr>
                <th>Crtiteria</th>
                <th>Comments</th>
                <th>Grades</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Results and Outcomes</td>
                <td>{{ $finalGrades->commentA }}</td>
                <td>{{ $finalGrades->gradeA }}</td>

            </tr>
            <tr>
                <td>Publications</td>
                <td>{{ $finalGrades->commentB }}</td>
                <td>{{ $finalGrades->gradeB }}</td>
            </tr>
            <tr>
                <td>Young Researcher Supervision</td>
                <td>{{ $finalGrades->commentD }}</td>
                <td>{{ $finalGrades->gradeD }}</td>
            </tr>
            <tr>
                <td>Project</td>
                <td>{{ $finalGrades->commentC }}</td>
                <td>{{ $finalGrades->gradeC }}</td>
            </tr>
        </tbody>

        @else
        <tr>
            <th>Status:</th>

            <td><b>{{ $finalGrades->isaccepted ==="1"?"Accepted":"Rejected"}}</b></td>

        </tr>
        @endif
    </table>


</div>

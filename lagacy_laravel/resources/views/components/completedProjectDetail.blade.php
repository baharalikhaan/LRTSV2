<div class="outcomes">
        <table id="outcomes" align="left" style="font-size:14;padding:2%">
            <thead style="color:teal;font-weight:bold;padding:3%">
                <tr>
                    <th>Identifier</th>
                    <th>Type</th>
                    <th>Verified By</th>
                    <th>Not Verified By</th>
                    <th>Score</th>
                    <th>Reviewers Acceptance</th>
                </tr>
            </thead>
            <tbody style="padding:2%;border-left:1px solid #ddd;">
                @foreach ($outcomes as $identifier => $outcome_ids)
                @foreach ($outcome_ids as $outcome_id => $users)
                <tr>
                    <td>{{$identifier}}</td>
                    <td>{{$users['type']}}</td>
                    <td><?php echo implode(', ', $users['verified']) ?></td>
                    <td><?php echo implode(', ', $users['not_verified']) ?></td>
                    <td>{{$users['score']}}</td>
                    <td>{{$users['verifcation_by_reviewer']}}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
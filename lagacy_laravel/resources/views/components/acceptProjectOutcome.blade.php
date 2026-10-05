<form method="POST" action="{{route('publish')}}">
    @csrf
    <input type="text" hidden name="p_id" value={{$project->id}} />
    <div class="outcomes">
        <table id="outcomes" align="left" style="font-size:14;padding:2%">
            <thead style="color:teal;font-weight:bold;padding:3%">
                <tr>
                    <th>Identifier</th>
                    <th>Outcome ID</th>
                    <th>Type</th>
                    <th>Verified By</th>
                    <th>Not verified by</th>
                    <th>Score</th>
                    <th>Accept</th>
                    <th>Reject</th>
                </tr>
            </thead>
            <tbody style="padding:2%;border-left:1px solid #ddd;">
                @foreach ($outcomes as $identifier => $outcome_ids)
                @foreach ($outcome_ids as $outcome_id => $users)
                <tr>
                    <td>{{$identifier}}</td>
                    <td>{{$users['outcome_id']}}</td>
                    <td>{{$users['type']}}</td>
                    <td><?php echo implode(', ', $users['verified']) ?></td>
                    <td><?php echo implode(', ', $users['not_verified']) ?></td>
                    <td>{{$users['score']}}</td>
                    <td><input type="radio" name={{$users['outcome_id']}} value="verified" checked>
                    </td>
                    <td><input type="radio" name={{$users['outcome_id']}} value="not-verified">
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <button type="submit">
            Submit
        </button>
    </div>
</form>
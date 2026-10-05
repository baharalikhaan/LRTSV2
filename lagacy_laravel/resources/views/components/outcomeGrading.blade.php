<style>
  #results {
  position: fixed;
  bottom: 0;
  right: 0;
  width: 20%;
  height:50%;
  border: 2px solid teal;
  text-align: left;
  padding: 1%;
  overflow-x: hidden;
  overflow-y: auto;
  background-color: #fdfbf6;
}
table, tr, th,
    td
     {
        border-bottom: 1px solid #ddd;
        font-size: 14;
    }
</style>


      <input type="text" style="display:none" name="p_id" value={{$p_id}}>
      <table id="position">
        <colgroup>
                <col span="1" style="width: 5%;">
                <col span="1" style="width: 15%;">
                <col span="1" style="width: 50%;">
                <col span="1" style="width: 10%;">
                <col span="1" style="width: 10%;">
        </colgroup>
        <thead style="text-align:center;color:teal;font-weight:bold;" >
          <tr>
            <th scope="col" rowspan="2" style="text-align:center">Type</th>
            <th scope="col" rowspan="2" style="text-align:center">Identifier</th>
            <th scope="col" rowspan="2" style="text-align:center">Title</th>
            <th scope="col" rowspan="2" style="text-align:center">Date</th>
            <th scope="col" rowspan="2" style="text-align:center">Venue</th>
          </tr>s
        </thead>
        <tbody style="text-align:left">
          @foreach($outcomes as $outcome)
          <tr>
            <td style="display:none;"> {{$outcome['id']}} </td>
            <td> {{$outcome['type']}} </td>
            <td> {{$outcome['identifier']}} </td>
            <td><a href={{$outcome['url']}}> {{$outcome['title']}} </a></td>
            <td> {{$outcome['publication_date']}} </td>
            <td> {{$outcome['venue']}} </td>
          </tr>
          @endforeach
        </tbody>
      </table>


<div id="results">

</div>

<script>
    var cells = document.querySelectorAll("#position td");
    for (var i = 0; i < cells.length; i++) {
        cells[i].addEventListener("click", function() {
            var doi = this.innerHTML;
            var url = "https://api.crossref.org/works/" + doi;
            fetch(url)
                .then(response => response.json())
                .then(res => {
                    var results = $('#results');
                    results.empty();
                    console.log(res, res.message);
                    var title = res.message.title[0];
                    var doi = res.message.DOI;
                    var journal = res.message['container-title'][0];
                    var pubDate = res.message.created['date-time'];
                    var journal = res.message.publisher;
                    var type = res.message.type;
                    var authors = res.message.author;

                    //var results = $('#results');
                    results.append('<p><strong>Title:</strong> ' + title + '</p>');
                    results.append('<p><strong>DOI:</strong> ' + doi + '</p>');
                    results.append('<p><strong>Published In:</strong> ' + journal + '</p>');
                    results.append('<p><strong>Publish Date:</strong> ' + pubDate + '</p>');
                    results.append('<p><strong>Type:</strong> ' + type + '</p>');
                    results.append('<p><strong>Authors:</strong> </p>');
                    results.append('<ul>');
                    $.each(authors, function(i, author) {
                        results.append('<li>' + author.given + ' ' + author.family + '</li>');
                    });
                    results.append('</ul>');


                })
                .catch(error => {
                    var results = $('#results');
                    results.empty();
                    //results.append('<p><strong>Error:</strong> ' + error + '</p>');
                   // console.log(error);
                });
        });
    }
</script>
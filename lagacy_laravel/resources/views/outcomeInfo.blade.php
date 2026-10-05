<!DOCTYPE html>
<html>

<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"] {
            padding: 5px;
            font-size: 16px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 5px 10px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #3e8e41;
        }

        #results {
            margin-top: 20px;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        li {
            margin-bottom: 5px;
        }
    </style>
    <script>
        function searchCrossRef() {
            var doi = document.getElementById("query").value;
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
                    console.log(error);
                });
        }
    </script>
</head>

<body>
    <label for="query">Enter title or DOI:</label>
    <input id="query" type="text" placeholder="Enter title or DOI">
    <button onclick="searchCrossRef()">Search</button>

    <div id="results">
        this is result div
    </div>
</body>

</html>
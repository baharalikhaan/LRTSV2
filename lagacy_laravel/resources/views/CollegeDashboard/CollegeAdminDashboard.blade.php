<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cycles</title>

    <style>
        .heading {
            position: absolute;
            top: -15;
            left: 35;
            background-color: teal;
            color: white;
            padding: 6px;
            border-radius: 15px 15px 15px 15px;
        }

        .btn-teal {
            color: #fff;
            background-color: #008080;
            /* Teal color */
            border-color: #008080;
            /* Teal color */
        }

        .btn-teal:hover {
            color: #fff;
            background-color: #005959;
            /* Darker Teal color on hover */
            border-color: #005959;
            /* Darker Teal color on hover */
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">


</head>

<body class="body">
    @include('components.projectSideBar')
    @include('components.navbar')


    <div class="row" style="margin: 20; padding-left:40">
        <div class="col-md-12" style="margin-top: 10;">
            <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                <div style=" margin: 40px;">


                    <div class="container-fluid mt-4">
                        <!-- Dropdown and Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>
                                <select id="parameterSelect" class="form-select" style="width: auto;" onchange="fetchData()">
                                    <option value="all">App Parameters</option>
                                    <option value="q1">Q1 Articles</option>
                                    <option value="q2">Q2 Articles</option>
                                    <option value="q3">Q3 Articles</option>
                                    <option value="q4">Q4 Articles</option>
                                    <option value="conference">Conferences</option>
                                    <option value="BookPublish">Book Published</option>
                                    <option value="EditBook">Edited Books</option>
                                    <option value="BookChapter">Book Chapters</option>
                                    <option value="IP">IP</option>
                                    <option value="GrantedPatents">Granted Patents</option>
                                    <option value="OpenSW">Open Source Softwares</option>
                                    <option value="SUp">Startups</option>
                                    <option value="master">Undergrade Students</option>
                                    <option value="UG">Masters Students</option>
                                    <option value="PhD">PhD Students</option>
                                </select>
                            </div>
                        </div>

                        <!-- Content Row: Table and Chart -->
                        <div class="row">
                            <!-- Table Section -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>College / Institute</th>
                                                <th>Commitments</th>
                                                <th>Outcomes</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dataTableBody">
                                            <!-- Data dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Chart Section -->
                            <div class="col-md-6">
                                <div class="card w-100">
                                    <div class="card-body">

                                        <canvas id="comparisonChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="heading">
                        Commitments vc Outcomes (College-Wise)
                    </div>
                </div>
            </div>
        </div>
    </div>





    <div class="row" style="margin: 20; padding-left:40">
        <div class="col-md-12" style="margin-top: 10;">
            <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                <div style=" margin: 40px;">


                    <div class="container-fluid mt-4">
                        <!-- Dropdown and Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>
                                <select id="parameterSelect2" class="form-select" style="width: auto;" onchange="fetchData2()">
                                    <option value="all">App Parameters</option>
                                    <option value="q1">Q1 Articles</option>
                                    <option value="q2">Q2 Articles</option>
                                    <option value="q3">Q3 Articles</option>
                                    <option value="q4">Q4 Articles</option>
                                    <option value="conference">Conferences</option>
                                    <option value="BookPublish">Book Published</option>
                                    <option value="EditBook">Edited Books</option>
                                    <option value="BookChapter">Book Chapters</option>
                                    <option value="IP">IP</option>
                                    <option value="GrantedPatents">Granted Patents</option>
                                    <option value="OpenSW">Open Source Softwares</option>
                                    <option value="SUp">Startups</option>
                                    <option value="master">Undergrade Students</option>
                                    <option value="UG">Masters Students</option>
                                    <option value="PhD">PhD Students</option>
                                </select>
                            </div>
                        </div>

                        <!-- Content Row: Table and Chart -->
                        <div class="row">
                            <!-- Table Section -->
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered w-100">
                                        <thead>
                                            <tr>
                                                <th>Pillar</th>
                                                <th>Commitments</th>
                                                <th>Outcomes</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dataTableBody2">
                                            <!-- Data dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Chart Section -->
                            <div class="col-md-6">
                                <div class="card w-100">
                                    <div class="card-body">

                                        <canvas id="comparisonChart2"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="heading">
                        Commitments vc Outcomes (Pillar-Wise)
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>



<script>
    let chartInstance;


    function fetchData() {
        const parameter = document.getElementById('parameterSelect').value;
        fetch(`/fetchdatacollege?parameter=${encodeURIComponent(parameter)}`)
            .then(response => response.json())
            .then(data => {
                updateChart(data);
                updateTable(data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }



    function updateChart(data) {
        const labels = data.map(item => `${item.tag} - ${item.tagtitle}`);
        const commitments = data.map(item => sumValues(item.commitments));
        const outcomes = data.map(item => sumValues(item.outcomes));

        const ctx = document.getElementById('comparisonChart').getContext('2d');
        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Commitments',
                        data: commitments,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    },
                    {
                        label: 'Outcomes',
                        data: outcomes,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        });
    }

    function updateTable(data) {
        const tableBody = document.getElementById('dataTableBody');
        tableBody.innerHTML = '';

        data.forEach(item => {
            const commitments = sumValues(item.commitments);
            const outcomes = sumValues(item.outcomes);
            const percentage = commitments ? ((outcomes / commitments) * 100).toFixed(2) : 0;

            tableBody.innerHTML += `
                <tr>
                    <td>${item.tag} - ${item.tagtitle}</td>
                    <td>${commitments}</td>
                    <td>${outcomes}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                                <div style="width: ${percentage}%; height: 100%; background: #4caf50;"></div>
                            </div>
                            <span>${percentage}%</span>
                        </div>
                    </td>
                </tr>`;
        });
    }


    let chartInstance2;
    function fetchData2() {
        const parameter = document.getElementById('parameterSelect2').value;
        fetch(`/fetchdatapillar?parameter=${encodeURIComponent(parameter)}`)
            .then(response => response.json())
            .then(data => {
                updateChart2(data);
                updateTable2(data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateChart2(data) {
        const labels = data.map(item => `${item.subpillar}`);
        const commitments = data.map(item => sumValues(item.commitments));
        const outcomes = data.map(item => sumValues(item.outcomes));

        const ctx = document.getElementById('comparisonChart2').getContext('2d');
        if (chartInstance2) chartInstance2.destroy();

        chartInstance2 = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Commitments',
                        data: commitments,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)'
                    },
                    {
                        label: 'Outcomes',
                        data: outcomes,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        });
    }

    function updateTable2(data) {
        const tableBody = document.getElementById('dataTableBody2');
        tableBody.innerHTML = '';

        data.forEach(item => {
            const commitments = sumValues(item.commitments);
            const outcomes = sumValues(item.outcomes);
            const percentage = commitments ? ((outcomes / commitments) * 100).toFixed(2) : 0;

            tableBody.innerHTML += `
                <tr>
                    <td>${item.pillar} - ${item.subpillar}</td>
                    <td>${commitments}</td>
                    <td>${outcomes}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                                <div style="width: ${percentage}%; height: 100%; background: #4caf50;"></div>
                            </div>
                            <span>${percentage}%</span>
                        </div>
                    </td>
                </tr>`;
        });
    }


    function sumValues(item) {
        if (!item) return 0;
        return Object.values(item).reduce((sum, val) => sum + (parseFloat(val) || 0), 0);
    }
    document.addEventListener('DOMContentLoaded', fetchData2);
    document.addEventListener('DOMContentLoaded', fetchData);

</script>

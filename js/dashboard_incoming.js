let allData = []

function populateDashboard(data) {
    const { fromDate, toDate, caseType, referredBy } = getFilters();

    const processed = data.filter(d => {
        if (d.status !== 'Approved') return false;

        const referralDate = d.date_time.substring(0, 10);
        if (fromDate && referralDate < fromDate) return false;
        if (toDate && referralDate > toDate) return false;
        if (caseType && d.type !== caseType) return false;
        if (referredBy && d.referred_by !== referredBy) return false;

        return true;
    });

    console.log(processed);

    const totalProcessed = processed.length;

    const toSeconds = (start, end) => {
        if (!start || !end) return null;
        const s = new Date(start);
        const e = new Date(end);
        return (e - s) / 1000; // seconds
    };

    const receptionTimes = [];     // date_time → reception_time
    const approvalTimes = [];      // reception_time → approved_time

    processed.forEach(d => {
        const recTime = toSeconds(d.date_time, d.reception_time);
        const approvalTime = toSeconds(d.reception_time, d.approved_time);

        if (recTime != null) receptionTimes.push(recTime);
        if (approvalTime != null) approvalTimes.push(approvalTime);
    });

    console.log(approvalTimes);

    const avg = arr => arr.length ? (arr.reduce((a, b) => a + b, 0) / arr.length) : 0;
    const min = arr => arr.length ? Math.min(...arr) : 0;
    const max = arr => arr.length ? Math.max(...arr) : 0;
    console.log(approvalTimes)
    $('#total-processed-refer').text(totalProcessed);
    $('#average-reception-id').text(formatTimeToMMSS(avg(receptionTimes)));
    $('#average-approve-id').text(formatTimeToMMSS(avg(approvalTimes)));
    $('#fastest-id').text(formatTimeToMMSS(min(approvalTimes)));
    $('#slowest-id').text(formatTimeToMMSS(max(approvalTimes)));

    render3DPieChart(aggregate(processed, 'pat_class'), 'myChart-1');
    render3DPieChart(aggregate(processed, 'type'), 'myChart-2');
    render3DPieChart(aggregate(processed, 'referred_by'), 'myChart-3');

    const aggregated = buildRHUStats(processed);
    console.log(aggregated);
    const dataSet = buildDataSet(aggregated);
    console.log(dataSet);
    renderRHUSummaryTable(dataSet);

    const icdArray = buildICDAggregate(processed);
    renderICDTable(icdArray);
    renderICDChart(icdArray);
}


function dashboard_data_onLoad() {
    const fromDate = $('#from-date-inp').val();  // may be empty
    const toDate = $('#to-date-inp').val();      // may be empty

    $.ajax({
        url: '../SDN/fetch_incoming_dashboard_data.php',
        method: 'POST',
        dataType: 'json',
        data: { fromDate, toDate },  // <-- send dates to PHP
        success: function(response) {
            console.log(response)
            allData = response;
            populateDashboard(allData);
        }
    });
}


function getFilters() {
    const fromDate = $('#from-date-inp').val();
    const toDate = $('#to-date-inp').val();
    const caseType = $('.filter-type-btn.active').data('type'); // we'll set .active
    const referredBy = $('#refer-to-select').val();

    return { fromDate, toDate, caseType, referredBy };
}

function renderICDChart(icdArray) {
    const top10 = icdArray.slice(0, 10);

    Highcharts.chart('icd-bar-chart', {
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Top 10 ICD Diagnoses'
        },
        xAxis: {
            categories: top10.map(item => item.codeTitle),
            title: { text: null }
        },
        yAxis: {
            min: 0,
            title: { text: 'Count', align: 'high' }
        },
        tooltip: {
            valueSuffix: ' cases'
        },
        series: [{
            name: 'Cases',
            data: top10.map(item => item.count)
        }]
    });
}

function renderICDDashboard(data) {
    console.log(data)
    const icdCounts = {};

    data.forEach(d => {
        const code = d.icd_diagnosis || 'Unknown';
        icdCounts[code] = (icdCounts[code] || 0) + 1;
    });

    const icdArray = Object.entries(icdCounts)
        .map(([code, count]) => ({ code, count }))
        .sort((a,b) => b.count - a.count);

    const top10 = icdArray.slice(0, 10).reverse(); // reverse for horizontal bar

    const chartData = top10.map(item => ({
        name: item.code,
        y: item.count
    }));

    Highcharts.chart('icd-bar-chart', {
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Top 10 ICD Diagnosis Codes'
        },
        xAxis: {
            type: 'category',
            title: { text: 'ICD Code' }
        },
        yAxis: {
            title: { text: 'Number of Referrals' }
        },
        series: [{
            name: 'Referrals',
            data: chartData
        }]
    });

    renderICDTable(icdArray);
}

function renderICDTable(icdArray) {
    const tableData = (icdArray || []).map(item => [item.codeTitle, item.count]);

    if ($.fn.DataTable.isDataTable('#icd-dataTable')) {
        $('#icd-dataTable').DataTable().clear().rows.add(tableData).draw();
    } else {
        $('#icd-dataTable').DataTable({
            data: tableData,
            columns: [
                { title: "ICD Code & Title" },
                { title: "Count" }
            ],
            // paging: false,
            // searching: false,
            // ordering: false,
            // info: false
        });
    }
}

function buildICDAggregate(data) {
    const map = {};

    data.forEach(d => {
        if (d.status !== 'Approved') return;

        const key = `${d.icd_diagnosis} - ${d.icd_diagnosis_title}`;

        if (!map[key]) {
            map[key] = 0;
        }
        map[key]++;
    });

    // convert to array
    const icdArray = Object.entries(map).map(([codeTitle, count]) => ({
        codeTitle,
        count
    }));

    // sort descending
    icdArray.sort((a, b) => b.count - a.count);

    return icdArray;
}


function buildRHUStats(data) {
    const result = {};

    data.forEach(d => {
        const facility = d.referred_by || 'Unknown';
        const type = d.type || 'Unknown';
        const level = d.pat_class || 'Unknown';

        if (!result[facility]) {
            result[facility] = {};
        }

        if (!result[facility][type]) {
            result[facility][type] = { Primary: 0, Secondary: 0, Tertiary: 0 };
        }

        if (result[facility][type][level] !== undefined) {
            result[facility][type][level]++;
        }
    });

    return result;
}

function buildDataSet(aggregated) {
    const dataSet = [];

    for (const [facility, types] of Object.entries(aggregated)) {
        let total = 0;
        const row = [`<span>${facility}</span>`];

        const sections = ['ER', 'OB', 'PCR', 'Toxicology', 'Cancer', 'OPD', 'NBSCC'];
        const classes = ['Primary', 'Secondary', 'Tertiary'];

        sections.forEach(section => {
            classes.forEach(cls => {
                const count = types[section]?.[cls] || 0;
                row.push(`<span>${count}</span>`);
                total += count;
            });
        });

        row.push(`<span>${total}</span>`);

        // Save the total as a number property for sorting
        dataSet.push({ row, total });
    }

    // sort by total descending
    dataSet.sort((a, b) => b.total - a.total);

    // return only the rows
    return dataSet.map(item => item.row);
}


function renderRHUSummaryTable(dataSet) {
    if ($.fn.DataTable.isDataTable('#referrals-summary-table')) {
        $('#referrals-summary-table').DataTable().destroy();
        $('#referrals-summary-table tbody').empty();
    }

    $('#referrals-summary-table').DataTable({
        destroy: true,
        data: dataSet,
        columns: [
            { title: "Referring Health Facility" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Primary" }, { title: "Secondary" }, { title: "Tertiary" },
            { title: "Total" }
        ],
        // scrollX: true,
        paging: false,
        // searching: false,
        ordering: false,
        info: false,
    });
}

function formatTimeToMMSS(seconds) {
    if (!seconds || isNaN(seconds)) return '00:00';

    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);

    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

function aggregate(data, key) {
    const counts = {};
    data.forEach(d => {
        const k = d[key] || 'Unknown';
        counts[k] = (counts[k] || 0) + 1;
    });
    const total = Object.values(counts).reduce((a,b)=>a+b,0);
    return Object.entries(counts).map(([name, count]) => ({
        name,
        y: parseFloat(((count / total) * 100).toFixed(1))
    }));
}

function render3DPieChart(data, containerId) {
    Highcharts.chart(containerId, {
        chart: {
            type: 'pie',
            borderRadius: "10px",
            padding: "0",
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            },
        },
        title: { text: null }, // explicitly,
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 40,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}: {point.y}%',
                    style: { color: '#2e1d18' }
                }
            }
        },
        series: [{
            name: 'Cases',
            colorByPoint: true,
            data: data
        }]
    });
}



$(document).ready(function(){
    dashboard_data_onLoad()

    $('#from-date-inp, #to-date-inp, #refer-to-select').on('change', () => {
        populateDashboard(allData); // allData = the full response from server
    });

    $('.filter-type-btn').on('click', function () {
        $('.filter-type-btn').removeClass('active');
        $(this).addClass('active');
        populateDashboard(allData);
    });

    $('#from-date-inp, #to-date-inp').on('change', () => {
        dashboard_data_onLoad();
    });

    // header js
    $('#nav-account-div').on('click' , function(event){
        event.preventDefault();
        if($("#nav-drop-account-div").css("display") === "none"){
        $("#nav-drop-account-div").css("display", "flex")
        }else{
            $("#nav-drop-account-div").css("display", "none")
        }
    })

    $('#nav-drop-account-div').on('mouseleave', function() {
        $("#nav-drop-account-div").css("display" , "none")
    });

    $('#side-bar-mobile-btn').on('click' , function(event){
        document.querySelector('#side-bar-div').classList.toggle('hidden');
    })

    $('#logout-btn').on('click' , function(event){
        event.preventDefault(); 
        $('#myModal-prompt #modal-title-incoming').text('Confirmation')
        document.querySelector('#myModal-prompt #modal-icon').className = "fa-solid fa-circle-exclamation"
        $('#myModal-prompt #ok-modal-btn-incoming').text('No')

        $('#myModal-prompt #yes-modal-btn-incoming').text('Yes');
        $("#myModal-prompt #yes-modal-btn-incoming").css("display", "flex")

        // Are you sure you want to logout?
        $('#myModal-prompt #modal-body').text('Are you sure you want to logout?');
    })

    $('#yes-modal-btn-incoming').on('click' , function(event){
        document.querySelector('#nav-drop-account-div').classList.toggle('hidden');

        $.ajax({
            url: '../SDN/logout.php',
            method: "POST",
            success: function(response) {
                // response = JSON.parse(response);
                // window.location.href = "http://192.168.42.222:8035/index.php" 
                // window.location.href = "http://10.10.90.14:8079/index.php" 
                window.location.href = "https://sdnplus.bataanghmc.net/" 
            }
        });
    })

    $('#dashboard-incoming-btn').on('click' , function(event){
        event.preventDefault();
        window.location.href = "../SDN/dashboard_incoming.php";
    })

    $('#dashboard-outgoing-btn').on('click' , function(event){
        event.preventDefault();
        window.location.href = "../SDN/dashboard_outgoing.php";
    })

    $('#setting-btn').on('click' , function(event){
        event.preventDefault();
        window.location.href = "../SDN/setting.php";
    })

    $('#sdn-title-h1').on('click' , function(event){
        event.preventDefault();
        window.location.href = "../SDN/Home.php";
    })
})
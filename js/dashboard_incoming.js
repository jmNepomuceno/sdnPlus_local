let mobile_responsive = false; 
if (window.matchMedia("(max-width: 480px)").matches) {
    document.querySelector('.header-div .side-bar-title #sdn-title-h1').style.display = "none"
    document.querySelector('.header-div .side-bar-title').style.width = "50px"
    //250px
    mobile_responsive = true; 
} else {
    // The screen width is more than 480px
}


let current_page = ""
$(document).ready(function(){
  const myModal = new bootstrap.Modal(document.getElementById('myModal-dashboardIncoming'));
  const myModalLogOut = new bootstrap.Modal(document.getElementById('myModal-prompt'));
  
  if(number_of_referrals === 0){
    myModal.show()
    // myModalLogOut.show()
  }

  $('#total-processed-refer').text($('#total-processed-refer-inp').val())

  const playAudio = () =>{
    let audio = document.getElementById("notif-sound")
    audio.muted = false;
    audio.play().catch(function(error){
        'Error playing audio: ' , error
    }) 
  }

  const stopSound = () =>{
    let audio = document.getElementById("notif-sound")
    audio.pause;
    audio.muted = true;
    audio.currentTime = 0;
  }

  function renderPieChart(chart, dataArray) {
    let xValues = [];
    for (let i = 0; i < dataArray.length; i++) {
        switch (chart) {
            case "case_type": xValues.push(dataArray[i]['type']); break;
            case "rhu": xValues.push(dataArray[i]['referred_by']); break;
            case "case_category": xValues.push(dataArray[i]['pat_class']); break;
        }
    }
    xValues.sort();

    var counts = {};

    xValues.forEach(function(item) {
        counts[item] = (counts[item] || 0) + 1;
    });

    var uniqueArray = Object.keys(counts);
    var duplicatesCount = uniqueArray.map(function(item) {
        return counts[item];
    });

    xValues = uniqueArray;
    const yValues = duplicatesCount;
    const barColors = [
        "#b91d47",
        "#00aba9",
        "#2b5797",
        "#e8c3b9",
        "#1e7145"
    ];

    let what_chart = "";
    switch (chart) {
        case "case_type": what_chart = "myChart-2"; break;
        case "rhu": what_chart = "myChart-3"; break;
        case "case_category": what_chart = "myChart-1"; break;
    }

    new Chart(document.getElementById(what_chart), {
        type: "pie",
        data: {
            labels: xValues,
            datasets: [{
                backgroundColor: barColors,
                data: yValues,
                label: "Data"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    align: 'center',
                    labels: {
                        font: {
                            size: 13, // Set the font size for legend labels
                            weight: 'bold', // Make legend labels bold
                            border: 'red'
                        },
                        boxWidth: 15,
                        padding: 20
                    }
                },
                tooltip: {
                    bodyFont: {
                        size: 12, // Set the font size for tooltips
                        weight: 'bold' // Make tooltips font bold
                    }
                },
                datalabels: {
                    color: '#000',
                    font: {
                        weight: 'bold', // Make data labels bold
                        size: 14 // Font size for data labels
                    }
                }
            },
            layout: {
                padding: {
                    left: 0,
                    right: 0,
                    top: 0,
                    bottom: 0
                }
            }
        }
    });
  }

renderPieChart("rhu" , dataReferFrom)
renderPieChart("case_type" , dataPatType)
renderPieChart("case_category" , dataPatClass)


$('#notif-sub-div').on('click' , function(event){
    if($('#notif-span').val() === 0){
        $('#notif-circle').addClass('hidden')
        document.getElementById("notif-sound").pause();
        document.getElementById("notif-sound").currentTime = 0;
    }else{
        window.location.href = "http://192.168.42.222:8035/main.php?loadContent=php/incoming_form.php"

        // window.location.pathname = "/newpage.html";
        current_page = "incoming_page"
        $('#current-page-input').val(current_page)
        $('#notif-sub-div').addClass('hidden')
    }
})

  $('#history-log-btn').on('click' , function(event){
    event.preventDefault();
    window.location.href = "../SDN/history_log.php";
  })

  const loadContent = (url) => {
    $.ajax({
        url:url,
        success: function(response){
            $('#container').html(response);
        }
    })
  }

  
  function fetchMySQLData() {
    $.ajax({
        url: '../SDN/fetch_interval.php',
        method: "POST",
        data : {
            from_where : 'bell'
        },
        dataType: "JSON",
        success: function(response) {
            // response = JSON.parse(response);  
            $('#notif-span').text(response[0].length);
            if(response[0].length > 9){
                $('#notif-span').css('font-size' , '0.65rem');
            }

            if (parseInt(response[0].length) >= 1) {
                if(current_page === 'incoming_page'){
                    stopSound()
                }else{
                    playAudio();
                }
                timer_running = true;
                // $('#notif-circle').removeClass('hidden');
                $('#notif-circle').css('display' , 'block');
                
                let type_counter = []
                for(let i = 0; i < response[0].length; i++){

                    if(!type_counter.includes(response[0][i]['type'])){
                        type_counter.push(response[0][i]['type'])
                    }
                }

                
                document.getElementById('notif-sub-div').innerHTML = '';
                for(let i = 0; i < type_counter.length; i++){
                    let type_var  = type_counter[i]
                    let type_counts  = 0

                    for(let j = 0; j < response[0].length; j++){
                        if(type_counter[i] ===  response[0][j]['type']){
                            type_counts += 1
                        }
                    }

                    if(i % 2 === 0){ 
                        document.getElementById('notif-sub-div').innerHTML += '\
                        <div>\
                            <h4 class="font-bold text-lg">' + type_counts + '</h4>\
                            <h4 class="font-bold text-lg">' + type_var + '</h4>\
                        </div>\
                    ';
                    }else{
                        document.getElementById('notif-sub-div').innerHTML += '\
                        <div>\
                            <h4 class="font-bold text-lg">' + type_counts + '</h4>\
                            <h4 class="font-bold text-lg">' + type_var + '</h4>\
                        </div>\
                    ';
                    }
                }

            } else {
                // $('#notif-circle').addClass('hidden');
                $('#notif-circle').css('display' , 'none');
                stopSound()
            }
            
            fetch_timer = setTimeout(fetchMySQLData, 10000);
        }
    });
}   

  fetchMySQLData(); 

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

    let currentDate = new Date();

    let year = currentDate.getFullYear();
    let month = currentDate.getMonth() + 1; // Adding 1 to get the month in the human-readable format
    let day = currentDate.getDate();

    let hours = currentDate.getHours();
    let minutes = currentDate.getMinutes();
    let seconds = currentDate.getSeconds();

    let final_date = year + "/" + month + "/" + day + " " + hours + ":" + minutes + ":" + seconds

    $.ajax({
        url: '../SDN/save_process_time.php',
        data : {  
            what: 'save',
            date : final_date,
            sub_what: 'logout'
        },
        method: "POST",
        success: function(response) {
            // response = JSON.parse(response);  
            // window.location.href = "http://192.168.42.222:8035/index.php" 
            // window.location.href = "http://10.10.90.14:8079/index.php" 
            window.location.href = "https://sdnplus.bataanghmc.net/"
        }
    });
})

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

  let notif_sub_div_open = true
    $('#notif-div').on('click' , function(event){

        if(!notif_sub_div_open){
            document.getElementById('notif-sub-div').style.display = 'none'
            notif_sub_div_open = true
        }else{
            notif_sub_div_open = false
            document.getElementById('notif-sub-div').style.display = 'flex'
        }
    })

    $('#notif-sub-div').on('click' , function(event){
        if(parseInt($('#notif-span').text() === 0)){
            $('#notif-circle').addClass('hidden')
            document.getElementById("notif-sound").pause();
            document.getElementById("notif-sound").currentTime = 0;
        }else{
            $('#notif-sub-div').addClass('hidden')
            loadContent('../SDN/incoming_form2.php')
            current_page = "incoming_page"
            $('#current-page-input').val(current_page)
        }

        document.getElementById('notif-sub-div').style.display = 'none'
    })

    $('#notif-sub-div').on('mouseleave' , function(event){
        $('#notif-sub-div').css('display' , 'none')
        notif_sub_div_open = true
    })

  $('#admin-module-btn').on('click' , function(event){
      event.preventDefault();
      window.location.href = "../SDN/admin.php";
      
      // // 
      // let currentDate = new Date();

      // let year = currentDate.getFullYear();
      // let month = currentDate.getMonth() + 1; // Adding 1 to get the month in the human-readable format
      // let day = currentDate.getDate();

      // let hours = currentDate.getHours();
      // let minutes = currentDate.getMinutes();
      // let seconds = currentDate.getSeconds();

      // let final_date = year + "/" + month + "/" + day + " " + hours + ":" + minutes + ":" + seconds

      // $.ajax({
      //     url: '../SDN/save_process_time.php',
      //     data : {
      //         what: 'save',
      //         date : final_date,
      //         sub_what: 'history_log'
      //     },
      //     method: "POST",
      //     success: function(response) {
      //         window.location.href = "../SDN/admin.php";
      //     }
      // });
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

  $('#incoming-sub-div-id').on('click' , function(event){
    event.preventDefault();
    window.location.href = "../main.php";
  })

  $('#filter-date-btn').on('click' , function(event){
    event.preventDefault();

    const data = {
      from_date : $('#from-date-inp').val(),
      to_date : $('#to-date-inp').val(),
      where : 'incoming'
    }

    
    $.ajax({
      url: '../SDN/filter_date_incoming.php',
      method: "POST",
      data : data,
      dataType : 'json',
      success: function(response) { 

        $('#total-processed-refer').text(response.totalReferrals)
        $('#average-reception-id').text(response.averageDuration_reception)
        $('#average-sdn-approve-id').text(response.average_sdn_average)
        $('#average-interdept-approve-id').text(response.averageTime_interdept)
        $('#average-approve-id').text(response.averageDuration_approval)
        $('#average-total-id').text(response.averageDuration_total)
        $('#fastest-id').text(response.fastest_response_final)
        $('#slowest-id').text(response.slowest_response_final)
      }
    });


    // populate table
    $.ajax({
      url: '../SDN/filter_date_table_incoming.php',
      method: "POST",
      data : data,
      success: function(response) {
        document.getElementById('tbody-class').innerHTML = response
      }
    });

    $.ajax({
      url: '../SDN/filter_chart_incoming.php',
      method: "POST",
      data : data,
      success: function(response) {
        response = JSON.parse(response);

        const referredByObj = [];
        const patClassObj = [];
        const typeObj = [];

        response.forEach(item => {
          // Check each item for its key and push an object containing both the key and value into the corresponding array
          if ('referred_by' in item) {
            referredByObj.push({ referred_by: item.referred_by });
          } else if ('pat_class' in item) {
            patClassObj.push({ pat_class: item.pat_class });
          } else if ('type' in item) {
            typeObj.push({ type: item.type });
          }
        });

        for(let i = 1; i <= 3; i++){
          document.getElementById('main-graph-sub-div-' + i).removeChild(document.getElementById('myChart-'+ i))
          let canva = document.createElement('canvas')
          canva.id = 'myChart-'+ i
          document.getElementById('main-graph-sub-div-'+ i).appendChild(canva)
        }

        for(let i = 0; i < 3 ; i++){

        }

        renderPieChart("rhu" , referredByObj)
        renderPieChart("case_type" , typeObj)
        renderPieChart("case_category" , patClassObj)
      }
    });

  })

  // Get the timer element
let recep_time = document.getElementById('average-reception-id').textContent;
let approve_time = document.getElementById('average-approve-id').textContent;
// let total_time = document.getElementById('average-total-id').textContent
let fastest_time = document.getElementById('fastest-id').textContent;
let slowest_time = document.getElementById('slowest-id').textContent;
let initial_load = true;
// Get the initial time in seconds
var initialTime = getTimeInSeconds('00:00:01');

// Set the initial time
setTimer(initialTime);

var intervalId = setInterval(function() {
    initialTime += 10;
    let reception_status = setTimer(initialTime, "reception");
    let approve_status = setTimer(initialTime, "approve");
    let total_status = setTimer(initialTime, "total");
    let fastest_status = setTimer(initialTime, "fastest");
    let slowest_status = setTimer(initialTime, "slowest");

    if(reception_status && approve_status && total_status && fastest_status && slowest_status){
      clearInterval(intervalId);
    }
}, 10);

// Function to convert HH:MM:SS format to seconds
function getTimeInSeconds(timeString) {
    var timeArray = timeString.split(':');
    return parseInt(timeArray[0]) * 3600 + parseInt(timeArray[1]) * 60 + parseInt(timeArray[2]);
}

// Function to set the timer display
function setTimer(seconds, elem) {
    let real_time;

    switch(elem) {
        case 'reception': real_time = getTimeInSeconds(recep_time); break;
        case 'approve': real_time = getTimeInSeconds(approve_time); break;
        // case 'total': real_time = getTimeInSeconds(total_time); break;
        case 'fastest': real_time = getTimeInSeconds(fastest_time); break;
        case 'slowest': real_time = getTimeInSeconds(slowest_time); break;
    }

    // If the time is met, stop the interval
    if (real_time >= seconds) {
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var remainingSeconds = seconds % 60;

        // Format the time as HH:MM:SS
        var formattedTime = pad(hours) + ':' + pad(minutes) + ':' + pad(remainingSeconds);

        // Update the timer element content
        switch(elem) {
            case 'reception': document.getElementById('average-reception-id').textContent = formattedTime; break;
            case 'approve': document.getElementById('average-approve-id').textContent = formattedTime; break;
            case 'total': document.getElementById('average-total-id').textContent = formattedTime; break;
            case 'fastest': document.getElementById('fastest-id').textContent = formattedTime; break;
            case 'slowest': document.getElementById('slowest-id').textContent = formattedTime; break;
        }
    } else {
        return true
    }
}

// Function to pad single digits with leading zeros
function pad(num) {
    return num.toString().padStart(2, '0');
}

  // // Function to pad single-digit numbers with a leading zero
  // function pad(number) {
  //     return (number < 10) ? '0' + number : number;
  // }
  $('#navbar-icon').on('click' , (event) =>{
    event.preventDefault();
    window.location.href = "../SDN/Home.php";
  })

    $(document).off('input', '.icd-inputs').on('input', '.icd-inputs', function(event) {
      const input_name = $(this).attr('name')
      const index = $('.icd-inputs').index(this);

      for(let i = 0; i < $('.icd-inputs').length; i++){
          if(i != index){
              $('.icd-inputs').eq(i).val("")
          }
      }

      $.ajax({
          url: '../SDN/icd_query.php',
          method: "POST", 
          data:{
              column : input_name,
              search_keyword : $(this).val()
          },
          dataType : 'json',
          success: function(response){
              // Reference to the <select> element you want to update
              let selectElement = $('#icd-select'); // Replace with the actual <select> element ID

              // Clear the existing options (except for a placeholder, if any)
              selectElement.empty();
              // selectElement.append('<option value="">Select an option</option>'); // Optional placeholder

              // Populate the select element with the new data
              response.forEach(item => {
                  selectElement.append('<option value="' + item.icd10_code + '">' + item.icd10_code + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + item.icd10_title + '</option>');
              });+

              // Optionally trigger the dropdown to be visible if needed (some frameworks need this)
              selectElement.show();
          }
      })
  })

  $('#icd-select').on('change', function() {
      const selectedValue = $(this).val(); 
      const selectedText = $(this).find('option:selected').text();


       $.ajax({
          url: '../SDN/icd_dashboard.php',
          method: "POST", 
          data:{
              icd_code : selectedValue
          },
          dataType : 'json',
          success: function(response){

              $('#total-icd-label').text(response.count)
          }
      })
  });
})


let mobile_responsive = false; 
if (window.matchMedia("(max-width: 480px)").matches) {
    document.querySelector('.header-div .side-bar-title #sdn-title-h1').style.display = "none"
    document.querySelector('.header-div .side-bar-title').style.width = "50px"
    //250px
    mobile_responsive = true; 
} 


$(document).ready(function(){
  let myModal = new bootstrap.Modal(document.getElementById('myModal-prompt'));
  let myModal_hospitalAndUsers = new bootstrap.Modal(document.getElementById('myModal-hospitalAndUsers'));
  let myModal_userAccess = new bootstrap.Modal(document.getElementById('myModal-user-access'));
  
  // myModal_userAccess.show()
  
  const stopSound = () =>{
    let audio = document.getElementById("notif-sound")
    audio.pause;
    audio.muted = true;
    audio.currentTime = 0;
  }

  let intervalHistoryLog;
  let inactivityTimer;
  let userIsActive = true;
  let current_page = ""

  function handleUserActivity() {
      userIsActive = true;
      // Additional code to handle user activity if needed
      clearInterval(intervalHistoryLog)

  }

  function handleUserInactivity() {
      userIsActive = false;
      // Additional code to handle user inactivity if needed
      // intervalHistoryLog = setInterval(fetchHistoryLog, 10000);
  }

  // Attach event listeners
  document.addEventListener('mousemove', handleUserActivity);

  // Set up a timer to check user inactivity periodically
  const inactivityInterval = 10000; // Execute every 5 seconds (adjust as needed)

  function startInactivityTimer() {
      inactivityTimer = setInterval(() => {
          if (!userIsActive) {
              handleUserInactivity();
          }
          userIsActive = false; // Reset userIsActive after each check
      }, inactivityInterval);
  }

  function resetInactivityTimer() {
      clearInterval(inactivityTimer);

      startInactivityTimer();
  }
  
  // Start the inactivity timer when the page loads
  startInactivityTimer();
  
  //----------------------------------------------------------------------------

    $('#total-processed-refer').text($('#total-processed-refer-inp').val())
  const playAudio = () =>{
    let audio = document.getElementById("notif-sound")
    audio.muted = false;
    audio.play().catch(function(error){
        'Error playing audio: ' , error
    })
  }

  $('#history-select').change(function() {
    var selectedValue = $(this).val();

    if(selectedValue === 'login'){
      selectedValue = 'user_login'
    }else if(selectedValue === 'incoming'){
      selectedValue = 'pat_refer'
    }else if(selectedValue === 'register'){
      selectedValue = 'pat_form'
    }else if(selectedValue === 'outgoing'){
      selectedValue = 'pat_defer'
    }else{
      selectedValue = 'all'
    }

    $.ajax({
      url: '../php/history_filter.php',
      method: "POST",
      data : {
        option : selectedValue
      },
      success: function(response) {
          let historyDiv = document.querySelector('.history-container')

          if (historyDiv) {
              while (historyDiv.firstChild) {
                  historyDiv.removeChild(historyDiv.firstChild);
              }
          }

          document.querySelector('.history-container').innerHTML = response
      }
    });
  });

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
    event.preventDefault(); // Prevent the default behavior (navigating to the link)

    $('#modal-title-main').text('Warning')
    // $('#modal-body').text('Are you sure you want to logout?')
    $('#ok-modal-btn-main').text('No')

    $('#yes-modal-btn-main').text('Yes');
    $('#yes-modal-btn-main').removeClass('hidden')

    $('#myModal-main').modal('show');
  })
  
  $('#yes-modal-btn-main').on('click' , function(event){
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
        url: '../php/save_process_time.php',
        data : {  
            what: 'save',
            date : final_date,
            sub_what: 'history_log'
        },
        method: "POST",
        success: function(response) {
            // response = JSON.parse(response);  
            // window.location.href = "http://192.168.42.222:8035/index.php" 
            window.location.href = "http://10.10.90.14:8079/index.php" 
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
  })

  $('#dashboard-incoming-btn').on('click' , function(event){
    event.preventDefault();
    window.location.href = "../SDN/dashboard_incoming.php";
  })

  $('#dashboard-outgoing-btn').on('click' , function(event){
      event.preventDefault();
      window.location.href = "../SDN/dashboard_outgoing.php";
  })

  $('#sdn-title-h1').on('click' , function(event){
    event.preventDefault();
    window.location.href = "../SDN/Home.php";
  })

  $('#incoming-sub-div-id').on('click' , function(event){
    event.preventDefault();
    window.location.href = "../main.php";
  })

  $('#history-log-btn').on('click' , function(event){
      event.preventDefault();
      window.location.href = "../SDN/history_log.php";
  })

  $('#setting-btn').on('click' , function(event){
    event.preventDefault();
    window.location.href = "../SDN/setting.php";
  })

let toggle_accordion_obj = {}
let global_breakdown_index = 0
let global_classification_divs_index = 0;
let single_classification_clicked = ""
for(let i = 0; i < document.querySelectorAll('.table-tr').length; i++){
    toggle_accordion_obj[i] = true
}

function attachSeeMoreBtn() {
  const expand_elements = document.querySelectorAll('.see-more-btn');
  expand_elements.forEach(function(element, index) {
      element.addEventListener('click', function() {
          global_breakdown_index = index;
      });
  });
}

function attachInfoBtn() {
  const edit_info_elements = document.querySelectorAll('.edit-info-btn');
    edit_info_elements.forEach(function(element, index) {
      element.addEventListener('click', function() {
          global_breakdown_index = index;
      });
  });
}

function attachClassifications() {
  $('#populate-patclass-div').on('click', '.classification-sub-div', function() {
    global_classification_divs_index = $(this).index();
});
}

attachClassifications()
attachSeeMoreBtn();
attachInfoBtn();

$('#add-classification-lbl').on('click' , function(event){
    $.ajax({
      url: '../SDN/populate_pat_class.php',
      method: "POST",
      success: function(response) {
          // response = JSON.parse(response); 
          document.getElementById('populate-patclass-div').innerHTML = ''
          document.getElementById('populate-patclass-div').innerHTML = response
      }
  });
})

$(document).on('click', '.classification-sub-div', function(event){
  // set the input fields to unclickabl
  $('#delete-classification-btn').css('opacity' , '1')
  $('#delete-classification-btn').css('pointer-events' , 'auto')

  single_classification_clicked = $('.classification-sub-div').eq(global_classification_divs_index).text()
})

// add new classification
$('#add-classification-btn').on('click' , function(event){
  $.ajax({
      url: '../SDN/add_classification.php',
      data : {  
          classification : $('#add-classification-input').val(),
          what : 'add'
      },
      method: "POST",
      success: function(response) {
          // response = JSON.parse(response); 
          $('#add-classification-icon').removeClass('hidden')
          $('#add-classification-input').addClass('hidden')

          $('#modal-body-incoming-success').text('Added Successfully')
          $('#myModal-success').modal('show');
      }
  });
})


$(document).on('click', '#add-classification-icon', function(event){
  // $('#add-classification-icon').addClass('hidden')
  // $('#add-classification-input').removeClass('hidden')

  $('#add-classification-btn').css('opacity' , '1')
  $('#add-classification-btn').css('pointer-events' , 'auto')


  // Get the elements
  const dynamicWidthDiv = document.getElementById('dynamic-width-div');
  const inputField = document.getElementById('add-classification-input');

  // Listen for input events on the input field
  inputField.addEventListener('input', function(event) {
      if (event.inputType === 'deleteContentBackward') {
        dynamicWidthDiv.style.width = inputField.scrollWidth - 8 + 'px';
        inputField.style.width = inputField.scrollWidth - 8 + 'px';
      }else{
        dynamicWidthDiv.style.width = inputField.scrollWidth + 'px';
        inputField.style.width = inputField.scrollWidth + 'px';
      }
  });
})

$(document).on('click', '#delete-classification-btn', function(event){
  $.ajax({
    url: '../SDN/add_classification.php',
    method: "POST",
    data : {
      classification : single_classification_clicked,
      what : 'delete'
    },
    success: function(response) {
        $('#modal-body-incoming-success').text('Deleted Successfully')
        $('#myModal-success').modal('show');
      }
  });
})


$(document).on('click', '.see-more-btn', function(event){
    for(let i = 0; i < document.querySelectorAll('.table-tr').length; i++){
      if(global_breakdown_index != i){
        toggle_accordion_obj[i] = true

        document.querySelectorAll('.breakdown-div')[i].style.display = 'none'
        document.querySelectorAll('.table-tr')[i].style.height = "50px"
        document.querySelectorAll('.breakdown-div')[i].style.display = 'none'
        document.querySelectorAll('.number_users')[i].style.display = 'flex'

        $('.see-more-btn').eq(i).css('top' , '20px')
        $('.see-more-btn').eq(i).css('right' , '20px')

        if($('.see-more-btn').eq(i).hasClass('fa-square-caret-down')){
          $('.see-more-btn').eq(i).removeClass('fa-square-caret-down')
          $('.see-more-btn').eq(i).addClass('fa-square-caret-up')
        }else{
          $('.see-more-btn').eq(i).addClass('fa-square-caret-down')
          $('.see-more-btn').eq(i).removeClass('fa-square-caret-up')
        }
      }
    }


    if(toggle_accordion_obj[global_breakdown_index]){
        $('#hospital-user-td').css('width' , '600px')
        document.querySelectorAll('.table-tr')[global_breakdown_index].style.height = "350px"
        document.querySelectorAll('.breakdown-div')[global_breakdown_index].style.display = 'flex'
        document.querySelectorAll('.number_users')[global_breakdown_index].style.display = 'none'

        $('.see-more-btn').eq(global_breakdown_index).css('top' , '180px')
        $('.see-more-btn').eq(global_breakdown_index).css('right' , '10px')

        toggle_accordion_obj[global_breakdown_index] = false
    }else{
        $('#hospital-user-td').css('width' , '200px')
        document.querySelectorAll('.table-tr')[global_breakdown_index].style.height = "50px"
        document.querySelectorAll('.breakdown-div')[global_breakdown_index].style.display = 'none'
        document.querySelectorAll('.number_users')[global_breakdown_index].style.display = 'flex'

        $('.see-more-btn').eq(global_breakdown_index).css('top' , '20px')
        $('.see-more-btn').eq(global_breakdown_index).css('right' , '20px')

        toggle_accordion_obj[global_breakdown_index] = true
    }

    if($('.see-more-btn').eq(global_breakdown_index).hasClass('fa-square-caret-down')){
      $('.see-more-btn').eq(global_breakdown_index).removeClass('fa-square-caret-down')
      $('.see-more-btn').eq(global_breakdown_index).addClass('fa-square-caret-up')
    }else{
      $('.see-more-btn').eq(global_breakdown_index).addClass('fa-square-caret-down')
      $('.see-more-btn').eq(global_breakdown_index).removeClass('fa-square-caret-up')
    }
    
})

let prev_info_arr = []
$(document).on('click', '.edit-info-btn', function(event){
  if($('.edit-info-btn').eq(global_breakdown_index).text() === 'Edit'){
    prev_info_arr = []
    for(let i = global_breakdown_index * 5; i <= (global_breakdown_index * 5) + 4; i++){
      prev_info_arr.push( $('.edit-users-info').eq(i).val())
      $('.edit-users-info').eq(i).css('border-bottom' , '1px solid #198754')
      $('.edit-users-info').eq(i).css('pointer-events' , 'auto')
    }

    $('.cancel-info-btn').eq(global_breakdown_index).css('display' , 'block')
    $('.edit-info-btn').eq(global_breakdown_index).text('Save')
    $('.edit-info-btn').eq(global_breakdown_index).css('background' , '#198754')
    
    for(let i = 0; i < $('.edit-info-btn').length; i++){
      if(i !== global_breakdown_index){
        $('.edit-info-btn').eq(i).css('pointer-events' , 'none')
        $('.edit-info-btn').eq(i).css('opacity' , '0.3')
      }
    }
  }else if($('.edit-info-btn').eq(global_breakdown_index).text() === 'Save'){
    let temp = [];
    for(let i = global_breakdown_index * 5; i <= (global_breakdown_index * 5) + 4; i++){
      temp.push( $('.edit-users-info').eq(i).val())
    }

    const data = {
      prev_last_name : prev_info_arr[0],
      prev_first_name : prev_info_arr[1],
      prev_middle_name : prev_info_arr[2],
      prev_username : prev_info_arr[3],
      prev_password : prev_info_arr[4],

      last_name : temp[0],
      first_name : temp[1],
      middle_name : temp[2],
      username : temp[3],
      password : temp[4],
      hospital_code : parseInt($('.hcode-edit-info').eq(global_breakdown_index).val())
    }

    
    $.ajax({
      url: '../SDN/edit_user_acc.php',
      method: "POST",
      data : data,
      dataType: 'json',
      success: function(response) {
          
          myModal_hospitalAndUsers.show()

          // set the input fields to unclickable
          for(let i = 0; i <= (global_breakdown_index * 5) + 4; i++){
            $('.edit-users-info').eq(i).css('pointer-events' , 'none')
            $('.edit-users-info').eq(i).css('border-bottom' , 'none')
          }

          $('.cancel-info-btn').eq(global_breakdown_index).css('display' , 'none')
          $('.edit-info-btn').eq(global_breakdown_index).css('background' , '#0d6efd')

          $('.edit-info-btn').eq(global_breakdown_index).text('Edit')

          for(let i = 0; i < $('.edit-info-btn').length; i++){
            $('.edit-info-btn').eq(i).css('pointer-events' , 'auto')    
            $('.edit-info-btn').eq(i).css('opacity' , '1')

          }

          $('#yes-modal-btn-incoming').css('display', 'none');
          myModal.show()
        }
    });
  }
})

// sort-up-btn
$('.sort-up-btn').on('click' , function(event){
  let index = parseInt(event.target.id.match(/\d+/)[0]);

  $('.sort-up-btn').eq(index).removeClass('opacity-30')
  $('.sort-up-btn').eq(index).removeClass('hover:opacity-100')
  $('.sort-up-btn').eq(index).addClass('opacity-100')

  $('.sort-down-btn').eq(index).addClass('opacity-30')
  $('.sort-down-btn').eq(index).addClass('hover:opacity-100')
  $('.sort-down-btn').eq(index).removeClass('opacity-100')

  var div = document.querySelector(".table-body");
  while (div.firstChild) {
      div.removeChild(div.firstChild);
  }

  let temp = ""
  switch(event.target.id){
    case "sort-up-btn-id-0": temp = "hospital_name_ASC"; break;
    case "sort-up-btn-id-1": temp = "hospital_code_ASC"; break;
    case "sort-up-btn-id-2": temp = "hospital_isVerified_ASC"; break;
  }


  $.ajax({
    url: '../SDN/fetch_admin_search_table.php',
    method: "POST",
    data : {
      temp : temp
    },
    success: function(response) {
        div.innerHTML += response
        attachSeeMoreBtn();
        attachInfoBtn();
      }
  });
})

$('.sort-down-btn').on('click' , function(event){
  let index = parseInt(event.target.id.match(/\d+/)[0]);

  $('.sort-down-btn').eq(index).removeClass('opacity-30')
  $('.sort-down-btn').eq(index).removeClass('hover:opacity-100')
  $('.sort-down-btn').eq(index).addClass('opacity-100')

  $('.sort-up-btn').eq(index).addClass('opacity-30')
  $('.sort-up-btn').eq(index).addClass('hover:opacity-100')
  $('.sort-up-btn').eq(index).removeClass('opacity-100')

  var div = document.querySelector(".table-body");
  while (div.firstChild) {
      div.removeChild(div.firstChild);
  }

  let temp = ""
  switch(event.target.id){
    case "sort-down-btn-id-0": temp = "hospital_name_DESC"; break;
    case "sort-down-btn-id-1": temp = "hospital_code_DESC"; break;
    case "sort-down-btn-id-2": temp = "hospital_isVerified_DESC"; break;
  }
  $.ajax({
    url: '../SDN/fetch_admin_search_table.php',
    method: "POST",
    data : {
      temp : temp
    },
    success: function(response) {
        div.innerHTML += response
        attachSeeMoreBtn();
        attachInfoBtn();
      }
  });
})

$(document).on('click', '.cancel-info-btn', function(event){
  // set the input fields to unclickable
  for(let i = 0; i <= (global_breakdown_index * 5) + 4; i++){
    $('.edit-users-info').eq(i).css('pointer-events' , 'none')
    $('.edit-users-info').eq(i).css('border-bottom' , 'none')
  }

  $('.cancel-info-btn').eq(global_breakdown_index).css('display' , 'none')
  $('.edit-info-btn').eq(global_breakdown_index).css('background' , '#0d6efd')
  $('.edit-info-btn').eq(global_breakdown_index).text('Edit')

  let j = 0;
  for(let i = global_breakdown_index * 5; i <= (global_breakdown_index * 5) + 4; i++){
    $('.edit-users-info').eq(i).val(prev_info_arr[j])
    j += 1
  }

  for(let i = 0; i < $('.edit-info-btn').length; i++){
    $('.edit-info-btn').eq(i).css('pointer-events' , 'auto')
    $('.edit-info-btn').eq(i).css('opacity' , '1')
  }
})

$('#logout-btn').on('click' , function(event){
  event.preventDefault(); 
  $('#myModal-prompt #modal-title-incoming').text('Warning')
  $('#myModal-prompt #ok-modal-btn-incoming').text('No')

  $('#myModal-prompt #yes-modal-btn-incoming').text('Yes');
  $("#myModal-prompt #yes-modal-btn-incoming").css("display", "flex")

  // Are you sure you want to logout?
  $('#myModal-prompt #modal-body-incoming').text('Are you sure you want to logout?');
})

$('#yes-modal-btn-incoming').on('click' , function(event){
  document.querySelector('#nav-drop-account-div').classList.toggle('hidden');

  let currentDate = new Date();

  let year = currentDate.getFullYear();
  let month = currentDate.getMonth() + 1;
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

// add-user-btn
let selectedRole_add;
$('.role-checkbox').on('change', function () {
  $('.role-checkbox').not(this).prop('checked', false);
  selectedRole_add = $('.role-checkbox:checked').val();
});

$(document).on('click', '#add-user-btn', function(event){
  $.ajax({
      url: '../SDN/add_user_doctor.php',
      data : {
          firstname: $('#user-fname').val(),
          middlename: $('#user-mname').val(),
          lastname: $('#user-lname').val(),
          username : $('#user-name').val(),
          password: $('#user-pw').val(),
          role: selectedRole_add,
          action : "add"
      },                        
      method: "POST",
      success: function(response) {
    
        $('#yes-modal-btn-incoming').css('display', 'none');
        $('#ok-modal-btn-incoming').css('margin-right', '0px');
        myModal.show()
        document.querySelector('#user-access-table tbody').innerHTML = ""
        document.querySelector('#user-access-table tbody').innerHTML = response

        for(let i = 0; i < $('.inputs-class').length; i++){
          $('.inputs-class').eq(i).val('')
          $('.role-checkbox').eq(i).prop('checked', false)
        }
      }
  });
})

let selectedRole_edit;
let global_index_edit_access;
let permissions = {}
let old_data = {}

$(document).on('change', '.role-checkbox-add', function () {
  const $currentRow = $(this).closest('tr');
  $currentRow.find('.role-checkbox-add').not(this).prop('checked', false);
  selectedRole_edit = $currentRow.find('.role-checkbox-add:checked').val();

});


$(document).on('change', '.permission-checkbox', function () {
  const checkboxes = document.querySelectorAll('.permission-checkbox'); 
  let interval = global_index_edit_access * 7;
  for (let i = interval; i < interval + 7; i++) {
      const checkbox = checkboxes[i]; // Get the current checkbox
      const key = checkbox.value; // Use the value attribute as the key
      const isChecked = checkbox.checked; // Determine if the checkbox is checked
      permissions[key] = isChecked; // Assign the result to the permissions object
  }
});


$(document).on('click', '.access-action-btn', function(event){     
  // selectedRole_edit = $('.role-checkbox-add:checked').val();

  const index = $('.access-action-btn').index(this)
  global_index_edit_access = index

  let interval = index * 4;
  let interval_role = index * 2
  if($('.access-action-btn').eq($('.access-action-btn').index(this)).text() === 'EDIT'){
    for(let i = 0; i <= $('.access-details .access-details-inputs').length; i++){
      $('.access-details .access-details-inputs').eq(i).css('font-weight' , '300')
      $('#user-access-table tbody tr .access-details .access-details-inputs').eq(i).css('background' , 'transparent')
      $('#user-access-table tbody tr .access-details .access-details-inputs').eq(i).css('pointer-events' , 'none')
    }
    
    for(let i = 0; i < $('.access-action-btn').length; i++){
      $('.access-access-td').eq(i).css('pointer-events' , 'none')
      $('.access-access-td').eq(i).css('opacity' , '0.4')

      $('.access-role-td').eq(i).css('pointer-events' , 'none')
      $('.access-role-td').eq(i).css('opacity' , '0.4')

      $('.access-action-btn').eq(i).text('EDIT')
      $('.access-action-btn').eq(i).css('opacity' , '0.4')
    }

    old_data['firstname_old'] = $('.access-details-inputs').eq(interval).val()
    old_data['lastname_old'] = $('.access-details-inputs').eq(interval + 1).val()
    old_data['username_old']  = $('.access-details-inputs').eq(interval + 2).val()
    old_data['password_old'] = $('.access-details-inputs').eq(interval + 3).val()
    
    $('.access-action-btn').eq(index).text('SAVE')
    $('.access-access-td').eq($('.access-action-btn').index(this)).css('pointer-events' , 'auto')
    $('.access-access-td').eq($('.access-action-btn').index(this)).css('opacity' , '1')

    $('.access-role-td').eq($('.access-action-btn').index(this)).css('pointer-events' , 'auto')
    $('.access-role-td').eq($('.access-action-btn').index(this)).css('opacity' , '1')

    $('.access-action-btn').eq($('.access-action-btn').index(this)).css('pointer-events' , 'auto')
    $('.access-action-btn').eq($('.access-action-btn').index(this)).css('opacity' , '1')
    
    for(let i = interval; i <= interval + 3; i++){
      if(index % 2 === 0){
        $('#user-access-table tbody tr .access-details .access-details-inputs').eq(i).css('background' , 'white')
      }else{
        $('#user-access-table tbody tr .access-details .access-details-inputs').eq(i).css('background' , '#e2e7e9')
      }

      $('.access-details .access-details-inputs').eq(i).css('font-weight' , '900')
      $('#user-access-table tbody tr .access-details .access-details-inputs').eq(i).css('pointer-events' , 'auto')
    }

  }else{
    // Find the row where the button was clicked
    const $currentRow = $(this).closest('tr');

    // Get the checked checkboxes within this row
    const checkedCheckboxes = $currentRow.find('.role-checkbox-add:checked');

    // Extract values of the checked checkboxes
    const checkedValues = checkedCheckboxes.map(function () {
        return $(this).val();
    }).get();


    old_data['firstname'] = $('.access-details-inputs').eq(interval).val()
    old_data['lastname'] = $('.access-details-inputs').eq(interval + 1).val()
    old_data['username'] = $('.access-details-inputs').eq(interval + 2).val() 
    old_data['password'] = $('.access-details-inputs').eq(interval + 3).val() 
    old_data['role'] = selectedRole_edit ? selectedRole_edit : checkedValues[0]
    old_data['action'] = "edit"
    
    
    if(JSON.stringify(permissions) === "{}"){
      const checkboxes = document.querySelectorAll('.permission-checkbox'); 
      let interval = global_index_edit_access * 7;
      for (let i = interval; i < interval + checkboxes.length; i++) {
          const checkbox = checkboxes[i]; // Get the current checkbox
          const key = checkbox.value; // Use the value attribute as the key
          const isChecked = checkbox.checked; // Determine if the checkbox is checked
          permissions[key] = isChecked; // Assign the result to the permissions object
      }
     
    }
    
    old_data.permissions = JSON.stringify(permissions);
    $.ajax({
      url: '../SDN/add_user_doctor.php',
      data : old_data,                        
      method: "POST",
      success: function(response) {
        $('#yes-modal-btn-incoming').css('display', 'none');
        $('#ok-modal-btn-incoming').css('margin-right', '0px');
        myModal.show()
        document.querySelector('#user-access-table tbody').innerHTML = ""
        document.querySelector('#user-access-table tbody').innerHTML = response
      }
    });
  }
})

$(document).on('click', '#delete-user-btn', function(event){
  const index = $('.access-action-btn').index(this)
  let interval = index * 4;

  $.ajax({
    url: '../SDN/add_user_doctor.php',
    data : {
      firstname: $('.access-details-inputs').eq(interval).val(),
      lastname: $('.access-details-inputs').eq(interval + 1).val(),
      username: $('.access-details-inputs').eq(interval + 2).val(),
      password: $('.access-details-inputs').eq(interval + 3).val(),
      role: null,
      action: "delete"  
    },                        
    method: "POST",
    success: function(response) {
      $('#yes-modal-btn-incoming').css('display', 'none');
      $('#ok-modal-btn-incoming').css('margin-right', '0px');
      myModal.show()
      
      document.querySelector('#user-access-table tbody').innerHTML = ""
      document.querySelector('#user-access-table tbody').innerHTML = response
    }
  })
})

  $('#navbar-icon').on('click' , (event) =>{
    event.preventDefault();
    window.location.href = "../SDN/Home.php";
  })

  $('#ok-modal-btn-incoming').on('click' , (event) =>{
    myModal.hide()
  })

  $('#yes-modal-btn-incoming').on('click' , (event) =>{
    myModal.hide()
  })

  $('#myModal-hospitalAndUsers').off('click', '.send-cipher').on('click', '.send-cipher', function(event) {
    let index = $(this).index('.send-cipher')
    let id = $(this).attr('id')

    // console.log({
    //   id,
    //   cipher: $('.cipher-input').eq(index).val(),
    //  })

    $.ajax({
      url: '../SDN/send_cipher.php',
      data : {
       id,
       cipher: $('.cipher-input').eq(index).val(),
      },                        
      method: "POST",
      success: function(response) {
        console.log(response)
        if(response === "success"){
          $('#modal-body-incoming-success').text('Cipher Sent Successfully')
          $('#myModal-success').modal('show');

          // Clear the input field after sending the cipher
          $('.cipher-input').eq(index).val('')
        }
      }
    })
  })

})

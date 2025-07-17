$(document).ready(function(){
    let global_ajax_response = null;

    setTimeout(() => {
        $('#incoming-middle-name-search').val("");
    }, 100); // 100ms delay is usually enough
    
    $('#myDataTable').DataTable({
        "bSort": false,
        "paging": true, 
        "pageLength": 6, 
        "lengthMenu": [ [6, 10, 25, 50, -1], [6, 10, 25, 50, "All"] ],
    });

    var dataTable = $('#myDataTable').DataTable();
    $('#myDataTable thead th').removeClass('sorting sorting_asc sorting_desc');
    dataTable.search('').draw(); 

    for(let i = 0; i < $('.side-bar-navs-class').length; i++){
        $('.side-bar-navs-class').css('opacity' , '0.3')
        $('.side-bar-navs-class').css('border-top' , 'none')
        $('.side-bar-navs-class').css('border-bottom' , 'none')
    }

    $('#outgoing-sub-div-id').css('opacity' , '1')
    $('#outgoing-sub-div-id').css('border-top' , '2px solid #3e515b')
    $('#outgoing-sub-div-id').css('border-bottom' , '2px solid #3e515b')

    let inactivityInterval = 10000; 

    const myModal = new bootstrap.Modal(document.getElementById('pendingModal'));
    const defaultMyModal = new bootstrap.Modal(document.getElementById('myModal-incoming'));
    // myModal.show()

    let global_index = 0, global_paging = 1, global_timer = "", global_breakdown_index = 0;
    let final_time_total = ""
    let next_referral_index_table;
    let length_curr_table = document.querySelectorAll('.hpercode').length;
    let toggle_accordion_obj = {}
    let type_approval = true // true = immediate approval // false = interdepartamental approval
    let from_yes_btn = ""
    let startTime;
    let elapsedTime = 0;
    let running = false;
    let requestId;
    let lastLoggedSecond = 0;
    let ok_btn_modal_origin = ""
    
    for(let i = 0; i < length_curr_table; i++){
        toggle_accordion_obj[i] = true
    }
    
    // activity/inactivity user
    let inactivityTimer;
    let running_timer_interval = "", running_timer_interval_update;
    let userIsActive = true;

    let sensitive_case_btn_index = ""

    function changePatientModalContent(){
        $('#pat-status-form').text('Approved')
        $('#approval-form').css('display' , 'none')
        $('#approval-details').css('display' , 'block')

        $('#update-stat-select').css('display' , 'block')
    }

    function handleUserActivity() {
        userIsActive = true;
    }

    function handleUserInactivity() {
        userIsActive = false;
        $.ajax({
            url: '../SDN/fetch_interval.php',
            method: "POST",
            data : {
                from_where : 'outgoing'
            }, 
            success: function(response) {

                dataTable.clear();
                dataTable.rows.add($(response)).draw();

                length_curr_table = $('.tr-incoming').length
                for(let i = 0; i < length_curr_table; i++){
                    toggle_accordion_obj[i] = true
                }
                const expand_elements = document.querySelectorAll('.accordion-btn');
                    expand_elements.forEach(function(element, index) {
                    element.addEventListener('click', function() {
                        global_breakdown_index = index;
                    });
                }); 
            }
        });
    }

    document.addEventListener('mousemove', handleUserActivity);

    function startInactivityTimer() {
        clearInterval(inactivityTimer);
        inactivityTimer = setInterval(() => {
            if (!userIsActive) {
                handleUserInactivity();
            }
            userIsActive = false;
            
        }, inactivityInterval);
    } 

    startInactivityTimer();

    const ajax_method = (index, event) => {
        global_index = index

        if(global_paging > 1){
            index -= 6
        }

        const data = {
            hpercode: document.querySelectorAll('.hpercode')[index].value,
            from:'outgoing',
            datatable_index : global_index,
            dateReferral : $('.date-referral').eq(index).val()

        }
        $.ajax({
            url: '../SDN/process_pending.php',
            method: "POST", 
            data:data,
            dataType:'JSON',
            success: function(response){
                console.log(response);
                global_ajax_response = response.responseData;

                document.querySelector('.left-div').innerHTML = ''
                document.querySelector('.right-div').innerHTML = ''

                document.querySelector('.left-div').innerHTML += response.left_html;
                document.querySelector('.right-div').innerHTML += response.right_html;

                let temp_arr_x = [
                    'Approved', 'Discharged' , 'Cancelled' , 'Arrived' , 'Checked' , 'Admitted' , 'For follow' , 'Deferred', 'Referred'
                ]

                // if(document.querySelectorAll('.pat-status-incoming')[index].textContent == 'Pending'){
                //     $('#select-response-status').css('pointer-events' , 'none')
                //     $('#select-response-status').css('opacity' , '0.3')
                // }

                $('#select-response-status').css('pointer-events' , 'none')
                $('#select-response-status').css('opacity' , '0.3')

                $('#right-sub-div-d-outgoing').css('display' , 'block')
                // $('#right-sub-div-b-1').css('height' , '100%')
                // console.log(temp_arr_x, document.querySelectorAll('.pat-status-incoming')[index].textContent)
                // if(temp_arr_x.includes(document.querySelectorAll('.pat-status-incoming')[index].textContent)){
                //     $('#right-sub-div-b').css('display' , 'none')
                //     $('#right-sub-div-d').css('display' , 'block')
                //     $('.appr-det-sub-div:nth-child(2)').css('display' , 'none')
                // }

                if(document.querySelectorAll('.pat-status-incoming')[index].textContent == 'Deferred'){
                    $('#print-modal-btn-incoming').css('display' , 'none')
                }else{
                    $('#print-modal-btn-incoming').css('display' , 'block')
                }

                myModal.show();

            }
        })
    }

    // const pencil_elements = document.querySelectorAll('.pencil-btn');
    //     pencil_elements.forEach(function(element, index) {
    //     element.addEventListener('click', function() {       
    //         ajax_method(index)
    //     });
    // });

    $('#print-modal-btn-incoming').off('click');

    $('#print-modal-btn-incoming').on('click', function () {
        const dataArray = global_ajax_response;
        dataArray[1].statusFinal = dataArray[1].status;
        delete dataArray[1].status;
        if (!Array.isArray(dataArray) || dataArray.length === 0) {
            console.error('🚫 No data to send');
            return;
        }
    
        const mergedData = Object.assign({}, ...dataArray);
    
        // Define popup size and position
        const popupWidth = 850;
        const popupHeight = 1000;
        const left = (screen.width / 2) - (popupWidth / 2);
        const top = (screen.height / 2) - (popupHeight / 2);
    
        // Open popup window with specs
        const popup = window.open('', 'printWindow', `width=${popupWidth},height=${popupHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`);
    
        if (!popup) {
            alert('⚠️ Pop-up was blocked. Please allow pop-ups for this site.');
            return;
        }
    
        // Create and submit form to the opened window
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../sdn.php';
        form.target = 'printWindow'; // name must match window.open()
        
        for (const key in mergedData) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = mergedData[key];
            form.appendChild(input);
        }
    
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });


    dataTable.on('click', '.pencil-btn', function () {
        var row = $(this).closest('tr');
        var rowIndex = dataTable.row(row).index();
        ajax_method(rowIndex);
    });

    // const cancel_ref_elements = document.querySelectorAll('.referral-cancel-btns');
    // cancel_ref_elements.forEach(function(element, index) {
    //     element.addEventListener('click', function() {       
    //         global_index = index
    //         $('#modal-title-incoming').text('Confirmation')
    //         $('#modal-body-incoming').text('Are you sure you want to cancel this referral?')
    //         $('#ok-modal-btn-incoming').text('No')

    //         defaultMyModal.show()
    //     });
    // });

    dataTable.on('click', '.referral-cancel-btns', function () {
        var row = $(this).closest('tr');
        var rowIndex = dataTable.row(row).index();
        global_index = rowIndex
        $('#modal-title-incoming').text('Confirmation')
        $('#modal-body-incoming').text('Are you sure you want to cancel this referral?')
        $('#ok-modal-btn-incoming').text('No')

        defaultMyModal.show()
    });

    // $('#myModal-incoming').off('click', '#yes-modal-btn-incoming').on('click', '#yes-modal-btn-incoming', function(event){
        
    //  })


    // $.ajax({
    //     url: '../SDN/session_navigation.php',
    //     method: "POST", 
    //     data : {
    //         nav_path : ""
    //     },
    //     success: function(response){
    //         if(response === '"true"'){
    //             $(document).on('saveTimeSession', saveTimeSession);
    //         }
    //     }
    // })

    // search incoming patients
    $('#incoming-search-btn').off('click', '#incoming-search-btn').on('click' , function(event){        
        $('#incoming-clear-search-btn').css('opacity' , '1')
        $('#incoming-clear-search-btn').css('pointer-events' , 'auto')

        let valid_search = false;
        let elements = [$('#incoming-referral-no-search').val(), $('#incoming-last-name-search').val(), $('#incoming-first-name-search').val(),
        $('#incoming-middle-name-search').val(), $('#incoming-type-select').val(),  $('#incoming-agency-select').val(), $('#incoming-status-select').val()]

        for(let i = 0; i < elements.length; i++){
            if(elements[i] !== "" && i != elements.length - 1){
                valid_search = true
            }
            if(elements[i] !== 'default' && i == elements.length - 1){
                valid_search = true
            }
        }

        if(valid_search){
            // find all status that is, sent already on the interdept or On-Process
            let hpercode_arr = []
            for(let i = 0; i < document.querySelectorAll('.pat-status-incoming').length; i++){
                let pat_stat = document.querySelectorAll('.pat-status-incoming')

                const str = pat_stat[i].textContent.trim(); // Trim to remove leading and trailing whitespace
                if (str && typeof str === 'string') {
                    const hasTwoSpaces = str.match(/^[^\s]*\s[^\s]*\s[^\s]*$/);; // Check if the string contains two consecutive spaces
                    if (hasTwoSpaces) {
                        hpercode_arr.push(document.querySelectorAll('.hpercode')[i].value)
                    } 
                }

                if(pat_stat[i].textContent === 'On-Process'){
                    hpercode_arr.push(document.querySelectorAll('.hpercode')[i].value)
                }

                if(pat_stat[i].textContent === 'Pending'){
                    hpercode_arr.push(document.querySelectorAll('.hpercode')[i].value)
                }
            }


            let data = {
                hpercode_arr : hpercode_arr,
                ref_no : $('#incoming-referral-no-search').val(),
                last_name : $('#incoming-last-name-search').val(),
                first_name : $('#incoming-first-name-search').val(),
                middle_name : $('#incoming-middle-name-search').val(),
                case_type : $('#incoming-type-select').val(),
                agency : $('#incoming-agency-select').val(),
                status : $('#incoming-status-select').val(),
                where : 'search',
                where_type : 'outgoing'
            }

            $.ajax({
                url: '../SDN/incoming_search.php',
                method: "POST", 
                data:data,
                // dataType:'JSON',
                success: function(response){

                    dataTable.clear();
                    dataTable.rows.add($(response)).draw();

                    length_curr_table = $('.tr-incoming').length
                    for(let i = 0; i < length_curr_table; i++){
                        toggle_accordion_obj[i] = true
                    }

                    const expand_elements = document.querySelectorAll('.accordion-btn');
                    expand_elements.forEach(function(element, index) {
                        element.addEventListener('click', function() {
                            global_breakdown_index = index;
                        });
                    });

                    inactivityInterval = 300000
                    startInactivityTimer()

                    
                }
            }) 
        }else{
            defaultMyModal.show()
        }

    })

    $('#incoming-clear-search-btn').off('click', '#incoming-clear-search-btn').on('click' , () =>{
        $.ajax({
            url: '../SDN/incoming_search.php',
            method: "POST", 
            data:{
                'where' : "clear"
            },
            success: function(response){

                dataTable.clear();
                dataTable.rows.add($(response)).draw();

                length_curr_table = $('.tr-incoming').length
                for(let i = 0; i < length_curr_table; i++){
                    toggle_accordion_obj[i] = true
                }

                $('#incoming-referral-no-search').val("")
                $('#incoming-last-name-search').val("")
                $('#incoming-first-name-search').val("")
                $('#incoming-middle-name-search').val("")
                $('#incoming-type-select').val("")
                $('#incoming-agency-select').val("")
                $('#incoming-status-select').val("default")

                const expand_elements = document.querySelectorAll('.accordion-btn');
                expand_elements.forEach(function(element, index) {
                    element.addEventListener('click', function() {
                        global_breakdown_index = index;
                    });
                });

                inactivityInterval = 10000
                startInactivityTimer()

            }
        }) 
    })

    dataTable.on('page.dt', function () {
        // clearInterval(running_timer_interval)

        var currentPageIndex = dataTable.page();
        var currentPageNumber = currentPageIndex + 1;

        global_paging = currentPageNumber
    });

    $(document).off('click', '#inter-dept-referral-btn').on('click' , '#inter-dept-referral-btn' , function(event){
        $('.interdept-div').css('display' , 'block')
        document.querySelector('.interdept-div').scrollIntoView({ behavior: 'smooth' });
    })

    $(document).off('click', '#int-dept-btn-forward').on('click' , '#int-dept-btn-forward' , function(event){
        $('#modal-title-incoming').text('Successed')
        document.querySelector('#modal-icon').className = 'fa-solid fa-circle-check'
        $('#modal-body-incoming').text('Successfully Forwarded')
        $('#ok-modal-btn-incoming').text('Close')
        $('#yes-modal-btn-incoming').css('display' , 'none')
        defaultMyModal.show()
        $('.interdept-div-v2').css('display' , 'flex')

        let data = {
            dept : $('#inter-depts-select').val(),
            hpercode : document.querySelectorAll('.hpercode')[global_index].value,
            pause_time : global_timer,
            approve_details : $('#eraa').val(),
            case_category : $('#approve-classification-select').val(),
        }

        $.ajax({
            url: '../SDN/incoming_interdept.php',
            method: "POST", 
            data:data,
            success: function(response){
                response = JSON.parse(response);   

                $('.interdept-div').css('display','none')
                $('#cancel-btn').css('display','block')
                $('.approval-main-content').css('display','none')

                runTimer().stop()
                runTimer().reset()
                // clearInterval(running_timer_interval)
                
                document.querySelectorAll('.pat-status-incoming')[global_index].textContent = 'Pending - ' + $('#inter-depts-select').val().toUpperCase();

                // enable the second request on the table while waiting for the current request that is on interdepartment already
                // document.querySelectorAll('.tr-incoming').
                myModal.hide()

                // reset the value of approval details
                const selectElement = document.getElementById('approve-classification-select');
                selectElement.value = '';
                selectElement.value = selectElement.options[0].value;
                $('#eraa').val("")

                enabledNextReferral()
            }
        })
    })

    $(document).off('click', '#imme-approval-btn').on('click', '#imme-approval-btn', function(event){
       defaultMyModal.show()
       $('#modal-body-incoming').text('Are you sure you want to approve this?')
       $('#modal-title-incoming').text('Confimation')
       $('#ok-modal-btn-incoming').text('No')
       $('#yes-modal-btn-incoming').css('display', 'block')
       type_approval = true
    })

    $('#yes-modal-btn-incoming').off('click', '#yes-modal-btn-incoming').on('click' , function(event){
        alert('Contact Bataan General Hospital and Medical Center for the approval of cancellation.')
        // const data = {
        //     hpercode: document.querySelectorAll('.hpercode')[global_index].value,
        //     datatable_index : global_index
        // }
        // $.ajax({
        //     url: '../SDN/cancel_referral_req.php',
        //     method: "POST", 
        //     data:data,
        //     // dataType:'JSON',
        //     success: function(response){

        //         dataTable.clear();
        //         dataTable.rows.add($(response)).draw();

                
                
        //     }
        // })
     })

     $(document).off('click', '.accordion-btn').on('click' , '.accordion-btn' , function(event){
        var accordion_index = $('.accordion-btn').index(this);

        var idString = event.target.id;
        // Use regular expression to extract the number

        if(toggle_accordion_obj[accordion_index]){
            document.querySelectorAll('.tr-incoming #dt-turnaround .breakdown-div')[accordion_index].style.display = "flex"
            toggle_accordion_obj[accordion_index] = false

            // fa-solid fa-plus
            $('.accordion-btn').eq(accordion_index).removeClass('fa-plus')
            $('.accordion-btn').eq(accordion_index).addClass('fa-minus')
        }else{
            document.querySelectorAll('.tr-incoming #dt-turnaround .breakdown-div')[accordion_index].style.display = "none"
            toggle_accordion_obj[accordion_index] = true

            $('.accordion-btn').eq(accordion_index).addClass('fa-plus')
            $('.accordion-btn').eq(accordion_index).removeClass('fa-minus')
        }
    })

    $(document).off('click', '#pre-emp-text').on('click' , '.pre-emp-text' , function(event){
        var originalString = event.target.textContent;
        // Using substring
        var stringWithoutPlus = originalString.substring(2);

        // Or using slice
        // var stringWithoutPlus = originalString.slice(2);
        $('#eraa').val($('#eraa').val() + " " + stringWithoutPlus  + " ")


        if ($('#approve-classification-select').val() !== '') {
            $('#imme-approval-btn').css('opacity' , '1')
            $('#imme-approval-btn').css('pointer-events' , 'auto')

            $('#inter-dept-referral-btn').css('opacity' , '1')
            $('#inter-dept-referral-btn').css('pointer-events' , 'auto')
        }
    })

    $(document).on('change' , '#inter-depts-select' , function(event){
        // Check if an option is selected
        if ($(this).val() !== '') {
            // Apply CSS changes when an option is selected
            $('#int-dept-btn-forward').css('opacity', '1');
            $('#int-dept-btn-forward').css('pointer-events', 'auto');
        } else {
            // Optionally, you can reset CSS when no option is selected
            $('#int-dept-btn-forward').css('opacity', '0.3');
            $('#int-dept-btn-forward').css('pointer-events', 'none');
        }
    });

    $(document).on('change' , '#approve-classification-select' , function(event){
        if ($(this).val() !== '' && $('#eraa').val().length > 1) {
            $('#imme-approval-btn').css('opacity' , '1')
            $('#imme-approval-btn').css('pointer-events' , 'auto')

            $('#inter-dept-referral-btn').css('opacity' , '1')
            $('#inter-dept-referral-btn').css('pointer-events' , 'auto')
        }else{
            
        }
    });

    $('#eraa').on('input', function(event) {
        if ($('#approve-classification-select').val() !== '' && $('#eraa').val().length > 20) {
            $('#imme-approval-btn').css('opacity' , '1')
            $('#imme-approval-btn').css('pointer-events' , 'auto')

            $('#inter-dept-referral-btn').css('opacity' , '1')
            $('#inter-dept-referral-btn').css('pointer-events' , 'auto')
        }else{
            
        }
    });

    $('#eraa').on('keydown', function(event) {
        if (event.keyCode === 8 && $('#eraa').val().length < 20) {
            $('#imme-approval-btn').css('opacity' , '0.3')
            $('#imme-approval-btn').css('pointer-events' , 'none')

            $('#inter-dept-referral-btn').css('opacity' , '0.3')
            $('#inter-dept-referral-btn').css('pointer-events' , 'none')
        }
    });
 
    $(document).off('click', '#cancel-btn').on('click' , '#cancel-btn' , function(event){
        defaultMyModal.show()
        $('#modal-title-incoming').text('Confirmation')
        $('#modal-body-incoming').text('Are you sure you want to cancel this referral?')
        $('#ok-modal-btn-incoming').text('No')
        clearInterval(running_timer_interval_update)
    });

    
    $(document).on('change' , '#select-response-status' , function(event){
        var selectedValue = $(this).val();
        
        $('#right-sub-div-c').css('display' , 'flex')
        document.getElementById('right-sub-div-c').scrollIntoView({ behavior: 'smooth' });

        if (selectedValue === 'Approved') {
            $('#imme-approval-btn').css('display' , 'flex')
            $('#inter-dept-referral-btn').css('display', 'none')

            $('.interdept-div').css('display' , 'none')

        } 
        else if (selectedValue === 'Interdepartamental') {
            $('#imme-approval-btn').css('display' , 'none')
            $('#inter-dept-referral-btn').css('display', 'flex')

            // $('#approval-form').css('display' , 'none')

            $('.interdept-div').css('display' , 'none')
            // $('.interdept-div').css('margin-top' , '2%')
        }
    })
    
    $(document).off('click', '#final-approve-btn').on('click' , '#final-approve-btn' , function(event){
        const data = {
            global_single_hpercode : document.querySelectorAll('.hpercode')[global_index].value,
            timer : final_time_total,
            approve_details : $('#eraa').val(), 
            case_category : $('#approve-classification-select').val(),
            action : "Approve",
            type_approval : "false"
        }

        runTimer().stop()
        $.ajax({
            url: '../SDN/approved_pending.php',
            method: "POST",
            data : data,
            success: function(response){
                
                document.querySelectorAll('.pat-status-incoming')[global_index].textContent = 'Approved';
                myModal.hide()
                
                dataTable.clear();
                dataTable.rows.add($(response)).draw();

                // find the on-process
                let yawa;
                for(let i = 0; i < document.querySelectorAll('.pat-status-incoming').length; i++){
                    if(document.querySelectorAll('.pat-status-incoming')[i].textContent === 'On-Process'){
                        yawa = i;
                        break;
                    }
                }                
                
                // runTimer().stop()
                if(yawa >= 0){
                    runTimer(yawa).start()
                }else{
                    runTimer().reset()
                }

                length_curr_table = $('.tr-incoming').length
                for(let i = 0; i < length_curr_table; i++){
                    toggle_accordion_obj[i] = true
                }
                
                // const pencil_elements = document.querySelectorAll('.pencil-btn');
                // pencil_elements.forEach(function(element, index) {
                //     element.addEventListener('click', function() {
                //         ajax_method(index)
                //     });
                // });

                enabledNextReferral()
                // if()
            }
         })
    });


    // sensitive case
    
    $(document).off('click', '.sensitive-case-btn').on('click', '.sensitive-case-btn', function(event){
        //reset the the buttons in modal after the previous transaction
        $('#ok-modal-btn-incoming').text('OK')
        $('#yes-modal-btn-incoming').css('display', 'none') 

        $('#modal-title-incoming').text('Verification')
        $('#modal-body-incoming').text('')

        sensitive_case_btn_index = $('.sensitive-case-btn').index(this);

        let sensitive_btn = document.createElement('input')
        sensitive_btn.id = 'sensitive-pw'
        sensitive_btn.type = 'password'
        sensitive_btn.placeholder = 'Input Password'

        $('#modal-body-incoming').append(sensitive_btn)

        defaultMyModal.show()
    })

    $('#ok-modal-btn-incoming').off('click', '#ok-modal-btn-incoming').on('click' , function(event){
        if($('#ok-modal-btn-incoming').text() === 'Close'){
        }
        else if($('#ok-modal-btn-incoming').text() === 'OK' && ok_btn_modal_origin == "sensitive-case-btn"){
            let mcc_passwords_validity = false
            let input_pw = $('#sensitive-pw').val().toString()
            for (var key in mcc_passwords) {
                if (mcc_passwords.hasOwnProperty(key)) {
                    if(mcc_passwords[key] === input_pw){
                        mcc_passwords_validity = true;
                    }
                }
            }
            
            if (mcc_passwords_validity) {
                let sensitive_hpercode = document.querySelectorAll('.sensitive-hpercode')


               $.ajax({
                    url: '../SDN/fetch_sensitive_names.php',
                    method: "POST",
                    data : {
                        hpercode : sensitive_hpercode[sensitive_case_btn_index].value // sensitive_case_btn_index = should always be = 0
                    },
                    dataType:'JSON',
                    success: function(response){
                        let fullNameLabel = $('<label>')
                            .addClass('pat-full-name-lbl')
                            .text(`${response.patlast}, ${response.patfirst} ${response.patmiddle}`);
                        fullNameLabel.hide(); 

                        $('.sensitive-lock-icon').eq(sensitive_case_btn_index)
                            .css('color', 'lightgreen')
                            .removeClass('fa-solid fa-lock')
                            .addClass('fa-solid fa-lock-open');
                    
                        $('.pencil-btn').eq(sensitive_case_btn_index)
                            .css('pointer-events', 'auto')
                            .css('opacity', '1');
                        
                        $('.sensitive-case-btn').eq(sensitive_case_btn_index).fadeOut(2000, function() {
                            $('.pat-full-name-div').eq(sensitive_case_btn_index).append(fullNameLabel);
                            fullNameLabel.show(); 
                        });
                    }
                })
            } else {
                // Change color to red
                // $('.sensitive-lock-icon').eq(sensitive_btn_index).css('color', 'red');
            
                // // Fade back to normal color after 2 seconds
                // setTimeout(function() {
                //     $('.sensitive-lock-icon').eq(sensitive_btn_index).css('color', ''); // Reset to original color
                // }, 2000);
            }
        }
    })

    $(document).on('change', '#update-stat-select', function(event){
        var selectedValue = $(this).val();
        if (selectedValue) {
            $('#update-stat-check-btn').css('opacity' , '1')
            $('#update-stat-check-btn').css('pointer-events' , 'auto')
        } else {
            $('#update-stat-check-btn').css('opacity' , '0.3')
            $('#update-stat-check-btn').css('pointer-events' , 'none')
        }
    });
    
    $(document).off('click', '#update-stat-check-btn').on('click', '#update-stat-check-btn', function(event){
        const  selectedValue = $('#update-stat-select').val();
        let data = {
            hpercode : document.querySelectorAll('.hpercode')[global_index].value,
            newStatus : selectedValue
        }
        $.ajax({
            url: '../SDN/update_referral_status.php',
            method: "POST",
            data : data,
            success: function(response){
                myModal.hide()
                
                $('#pat-status-form').text(data.newStatus)
                $('#modal-body-incoming').text('Successfully Updated')
                defaultMyModal.show()
                $('#save-update').hide(); 
                $('#update-stat-select').prop('selectedIndex', 0);
            }
         })
    });

    
    // Attach a cleanup event to stop the timer when this content is replaced
    function cleanupContent() {
        if (inactivityTimer !== null) {
            clearInterval(inactivityTimer);
            inactivityTimer = null;
        }
    }

    // Listen for an event indicating content unload
    window.addEventListener("unloadContent", cleanupContent);

    // Optional: Clean up when the window unloads (to be extra safe)
    window.addEventListener("beforeunload", cleanupContent);
})
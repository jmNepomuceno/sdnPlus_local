if (typeof fetchIntervalId === 'undefined') {
    var fetchIntervalId = null;
}

$(document).ready(function(){
    let global_ajax_response = null;
    // let last_modified_arr = []
    // for(let elem of jsonData){
    //     last_modified_arr.push({last_modified : elem.last_modified})
    // }
    // console.log(last_modified_arr)

    // function pollServer() {
    //     $.ajax({
    //         url: '../../SDN/poll/comet.php',
    //         type: 'POST',
    //         data: { last_modified_arr },
    //         dataType: 'json',
    //         success: function(data) {
    //             console.log(data)
    //             if (!data.hasOwnProperty('same') && !data.hasOwnProperty('status_comet')) {
    //                 last_modified_arr = data.map(item => ({ last_modified: item.last_modified }));
    //                 console.log('Data updated:', data);
    //             }
    //             pollServer(); // Continue polling
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error:', error);
    //             setTimeout(pollServer, 5000); // Retry after a delay in case of error
    //         }
    //     });
    // }

    // pollServer() 


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

    $('#incoming-sub-div-id').css('opacity' , '1')
    $('#incoming-sub-div-id').css('border-top' , '2px solid #3e515b')
    $('#incoming-sub-div-id').css('border-bottom' , '2px solid #3e515b')

    // try and error
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

    let startTime;
    let elapsedTime = 0;
    let running = false;
    let requestId;
    let lastLoggedSecond = 0;
    let rejection_data = {}
    let refer_again_bool = false

    let ok_btn_modal_origin = ""
    let encapsule = [];

    for(let i = 0; i < length_curr_table; i++){
        toggle_accordion_obj[i] = true
    }


    // activity/inactivity user
    let inactivityTimer;
    let running_timer_interval = "", running_timer_interval_update;
    let userIsActive = true;

    let sensitive_case_btn_index = ""

    // reusable functions
    function updateInterdeptFunc(){
        let data = {
            hpercode : document.querySelectorAll('.hpercode')[global_index].value
        }
        // console.log(data)
        $.ajax({
            url: '../SDN/fetch_update_interdept.php',
            method: "POST", 
            data:data,
            dataType: "JSON",
            success: function(response){
                // console.log(response)
                clearInterval(running_timer_interval_update)

                if(response[0]['status_interdept'] === "Pending"){
                    $('#span-dept').text(response[1].department.toUpperCase() + " | ")
                    $('#span-status').text(response[0].status_interdept + " | ")
                    $('#span-time').text("00:00:00")

                    $('#v2-update-stat').text(`Last update: ${response[0]['currentDateTime']}`)
                }

                if(response[0]['status_interdept'] === "On-Process"){
                    let timeString = response[1].curr_time;
                    if(timeString){
                        running_timer_interval_update = setInterval(function() {
                            let totalSeconds = timeString;
                            let hours = Math.floor(totalSeconds / 3600);
                            let minutes = Math.floor((totalSeconds % 3600) / 60);
                            let seconds = (totalSeconds % 60).toFixed(0);

                            const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                            
                            $('#v2-update-stat').text(`Last update: ${response[0]['currentDateTime']}`)

                            // <label for="" id="v2-stat"> <span id="span-dept">Surgery</span> - <span id="span-status">Pending</span> - <span id="span-time">00:00:00</span></span></label>
                            $('#span-dept').text(response[1].department.charAt(0).toUpperCase() + response[1].department.slice(1) + " | ") 
                            $('#span-status').text(response[0].status_interdept + " | ") 
                            $('#span-time').text(formattedTime)
                            
                            // here
                            $('.interdept-div').css('display','none')
                            $('#cancel-btn').css('display','block')
                            $('.approval-main-content').css('display','none')
                            clearInterval(running_timer_interval)

                            // check if the status of the thingy is approve or deferred
                            // $.ajax({
                            //     url: '../SDN/fetch_update_interdept.php',
                            //     method: "POST", 
                            //     data:data,
                            //     success: function(response){
                                    
                                    
                            //         // document.querySelectorAll('.pat-status-incoming')[global_index].textContent = 'Pending - ' + $('#inter-depts-select').val().toUpperCase();;
                            //     }
                            // })
                            timeString =  parseInt(timeString) + 1
                        }, 1000); 
                    }
                }
                else if(response[0]['status_interdept'] === "Approved" || response[0]['status_interdept'] === "Rejected"){
                    // console.log(response)
                    $('#v2-update-stat').text(`Last update: ${response[1]['final_progress_date']}`)

                    // <label for="" id="v2-stat"> <span id="span-dept">Surgery</span> - <span id="span-status">Pending</span> - <span id="span-time">00:00:00</span></span></label>
                    $('#span-dept').text(response[1].department.charAt(0).toUpperCase() + response[1].department.slice(1) + " | ") 
                    $('#span-status').text(response[0].status_interdept + " | ") 
                    $('#span-time').text(response[1]['final_progress_time'])
                    // console.log(response[0]['sent_interdept_time'] ,  response[1]['final_progress_time'])
                    
                    const [hours1, minutes1, seconds1] = response[0]['sent_interdept_time'].split(':').map(Number);
                    const [hours2, minutes2, seconds2] = response[1]['final_progress_time'].split(':').map(Number);
                    
                    // Create Date objects in UTC with the provided hours, minutes, and seconds
                    const date1 = new Date(Date.UTC(1970, 0, 1, hours1, minutes1, seconds1));
                    const date2 = new Date(Date.UTC(1970, 0, 1, hours2, minutes2, seconds2));
                    
                    const totalMilliseconds = date1.getTime() + date2.getTime();
                    
                    // Create a new Date object in UTC with the total milliseconds
                    const newDate = new Date(totalMilliseconds);
                    
                    // Format the result in UTC time "HH:mm:ss"
                    const result = `${String(newDate.getUTCHours()).padStart(2, '0')}:${String(newDate.getUTCMinutes()).padStart(2, '0')}:${String(newDate.getUTCSeconds()).padStart(2, '0')}`;
                    
                    // console.log(result);
                    final_time_total = result

                    if(response[0]['status_interdept'] === "Approved"){ 
                        $('#final-approve-btn').css('display','block')
                        $('#refer-again-btn').css('display','none')
                    }else{
                        $('#final-approve-btn').css('display','none')
                        $('#refer-again-btn').css('display','block')
                        $('#reject-details-btn').css('display','block')

                        rejection_data = {
                            department : response[1]['department'],                                                                      
                            hpercode : response[1]['hpercode'],                                                                      
                            rejected_by : response[1]['rejected_by'],                                                                      
                            rejected_time : response[1]['curr_time'],                                                                      
                            rejected_date : response[1]['final_progress_date'],
                            action : "Add"
                        }
                        // console.log(rejection_data)
                    }
                }      
            }
        })
    }

    // for interdepartamental module. Whenever the first current referral is already pending on interdept, the next referral will be availabe to process.
    function enabledNextReferral(){ // for interdept
        // check the status of the referrals to get the index of the next referral to be enable
        let hasTwoSpaces;
        for(let i = 0; i < document.querySelectorAll('.pat-status-incoming').length; i++){
            const str = document.querySelectorAll('.pat-status-incoming')[i].textContent.trim(); // Trim to remove leading and trailing whitespace

            if (str && typeof str === 'string') {
                hasTwoSpaces = str.match(/^[^\s]*\s[^\s]*\s[^\s]*$/);; // Check if the string contains two consecutive spaces
                if (hasTwoSpaces) {
                    next_referral_index_table = i;

                    if(next_referral_index_table >= 0 && next_referral_index_table + 1 < document.querySelectorAll('.tr-incoming').length){
                        document.querySelectorAll('.tr-incoming')[next_referral_index_table + 1].style.opacity = "1"
                        document.querySelectorAll('.tr-incoming')[next_referral_index_table + 1].style.pointerEvents = "auto"
                    }
                } 
            }
        }
    }

    function enabledNextReferral_normal(){
        let next_referral_index_table;
        for(let i = 0; i < document.querySelectorAll('.pat-status-incoming').length; i++){
            if(document.querySelectorAll('.pat-status-incoming')[i].textContent === 'On-Process'){
                // console.log(document.querySelectorAll('.pat-status-incoming')[i].textContent)
                next_referral_index_table = i;
            }
        }

        if(next_referral_index_table >= 0 && next_referral_index_table + 1 < document.querySelectorAll('.tr-incoming').length){
            document.querySelectorAll('.tr-incoming')[next_referral_index_table + 1].style.opacity = "1"
            document.querySelectorAll('.tr-incoming')[next_referral_index_table + 1].style.pointerEvents = "auto"
        }
    }

    enabledNextReferral()
    enabledNextReferral_normal()

    function changePatientModalContent(){
        $('#pat-status-form').text('Approved')
        $('#approval-form').css('display' , 'none')
        $('#approval-details').css('display' , 'block')

        $('#update-stat-select').css('display' , 'block')
    }


    function move() {
        $('.loading-overlay').css('display' , 'flex')
        let i = 0;

        if (i == 0) {
            i = 1;
            let elem = document.getElementById("myBar");
            let width = 1;
            let id = setInterval(frame, 60);
            function frame() {
            if (width >= 100) {
                clearInterval(id);
                i = 0;
                elem.style.width = "1px"
                $('.loading-overlay').css('display' , 'none')
            } else {
                width += 3;
                elem.style.width = width + "%";
            }
            }
        }
    }

    function handleUserInactivity() {
        userIsActive = false;
        console.log('here')
        $.ajax({
            url: '../SDN/fetch_interval.php',
            method: "POST",
            data : {
                from_where : 'incoming'
            }, 
            success: function(response) {
                // console.log('fetched incoming referral---------------------------------------------------')

                dataTable.clear();
                dataTable.rows.add($(response)).draw();

                length_curr_table = $('.tr-incoming').length
                for(let i = 0; i < length_curr_table; i++){
                    toggle_accordion_obj[i] = true
                }

                const expand_elements = document.querySelectorAll('.accordion-btn');
                    expand_elements.forEach(function(element, index) {
                    element.addEventListener('click', function() {
                        // console.log(index)
                        global_breakdown_index = index;
                    });
                });

                enabledNextReferral_normal()

                // $.ajax({
                //     url: '../SDN/fetch_try.php',
                //     method: "POST",
                //     dataType : 'JSON',
                //     success: function(response){
                //         // console.log(response)
                        
                //         if(response.prev_inc != response.curr_inc){
                //             // console.log('here')
                //             move()
                //         }

                //         if(response.running_index.length >= 1){
                //             running_bool_var = true;
                //         }
                //         // console.log(encapsule)
                //         for (let i = 0; i < encapsule.length; i++) {
                //             // encapsule[response.running_index[i]].stop();
                //             if (encapsule[i] && typeof encapsule[i].stop === 'function') {
                //                 encapsule[i].stop();
                //             }
                //         }

                //         encapsule = []

                //         for (let i = 0; i < response.data.length; i++) {
                //             encapsule[response.data[i]] = runTimer(parseInt(response.data[i]))
                //         }

                //         for (let i = 0; i < response.data.length; i++) {
                //             elapsedTime = (parseFloat(response.running_timer[i]) || 0) * 1000;
                //             // requestAnimationFrame(() => encapsule[i].start(elapsedTime));
                //             encapsule[response.data[i]].start(elapsedTime)
                //         }
                //     }
                // })
            },
            error: function(xhr, status, error) {
                console.error("Error: " + error);
            }
        });
    }

    function startContinuousFetch() {
        if (fetchIntervalId === null) {
            handleUserInactivity()
            fetchIntervalId = setInterval(handleUserInactivity, inactivityInterval); // every 1s
            console.log("Started continuous fetch.");
        }
    }

    function stopContinuousFetch() {
        if (fetchIntervalId !== null) {
            clearInterval(fetchIntervalId);
            fetchIntervalId = null;
            console.log("Stopped continuous fetch.");
        }
    }

    startContinuousFetch();

    // $(document).on("visibilitychange", function() {
    //     if (document.hidden) {
    //         startInactivityTimer();
    //     }
    // });



    const ajax_method = (index, event) => {
        // console.log(341, index)
        // console.log("ajax_method...")
        global_index = index

        if(global_paging > 1 && global_paging <= 2){
            index -= 6 
        }

        if(global_paging > 2){
            index -= 6 * (global_paging - 1); // Adjust index for paging
        }

        const data = {
            hpercode: document.querySelectorAll('.hpercode')[index].value,
            from:'incoming',
            datatable_index : global_index
        }

        $.ajax({
            url: '../SDN/process_pending.php',
            method: "POST", 
            data:data,
            dataType:'JSON',
            success: function(response){

                $.ajax({
                    url: '../SDN/check_record_lock.php',
                    method: "POST", 
                    data:{
                        hpercode : document.querySelectorAll('.hpercode')[index].value,
                        from : 'incoming',
                    },
                    dataType: "JSON",
                    success: function(response_data){
                        // console.log(response_data)
                        // if(response_data.whoLocked){
                        if(response_data.whoLocked && document.querySelectorAll('.pat-status-incoming')[index].textContent == 'Pending'){
                            $('#modal-title-incoming').text('Warning')
                            document.querySelector('#modal-icon').className = 'fa-solid fa-circle-exclamation'
                            $('#modal-body-incoming').text('This referral is currently accessed by ' + response_data.whoLocked + '.')
                            $('#ok-modal-btn-incoming').text('Close')
                            $('#ok-modal-btn-incoming').css('margin-right' , '0')
                            $('#yes-modal-btn-incoming').css('display' , 'none')
                            defaultMyModal.show()

                            document.querySelectorAll('.pat-status-incoming')[index].textContent = "On-Process"
                        }
                        else{
                            global_ajax_response = response.responseData;
                            console.log(global_ajax_response);

                            document.querySelector('.left-div').innerHTML = ''
                            document.querySelector('.right-div').innerHTML = ''

                            document.querySelector('.left-div').innerHTML += response.left_html;
                            document.querySelector('.right-div').innerHTML += response.right_html;

                            let temp_arr_x = [
                                'Approved', 'Discharged' , 'Cancelled' , 'Arrived' , 'Checked' , 'Admitted' , 'For follow' , 'Referred', 'Deferred'
                            ]

                            if (document.querySelectorAll('.pat-status-incoming')[index].textContent === 'Pending') {
                                // console.log(393, encapsule);
                            
                                if (!encapsule[index]) {
                                    encapsule[index] = runTimer(index); // Create and store a new timer
                                }
                            
                                encapsule[index].start(); // Start the timer
                            
                                const data = {
                                    hpercode: document.querySelectorAll('.hpercode')[index].value,
                                    from: 'incoming'
                                };
                            
                                $.ajax({
                                    url: '../SDN/pendingToOnProcess.php',
                                    method: "POST",
                                    data: data,
                                    success: function (response) {
                                        // console.log(410, response);
                                        enabledNextReferral_normal();
                                    }
                                });
                            
                                $('#update-stat-select').css('display', 'none');
                            }
                            
                            else if(temp_arr_x.includes(document.querySelectorAll('.pat-status-incoming')[index].textContent)){
                                // console.log('286')
                                let data = {
                                    hpercode : document.querySelectorAll('.hpercode')[index].value,
                                }
                                // console.log(data)

                                $.ajax({
                                    url: '../SDN/fetch_approve_details.php',
                                    method: "POST", 
                                    data:data,
                                    dataType: 'JSON',
                                    success: function(response){
                                        // console.log(response)
                                        // response[0].pat_class
                                        // $('#approve-classification-select-details').val(response[0].pat_class)
                                        // $('#eraa-details').val(response[0].approval_details)
                                        // right-sub-div-b
                                        // $('#right-sub-div-b').css('display' , 'none')
                                        $('#right-sub-div-d').css('display' , 'block')
                                        
                                    }
                                })

                                changePatientModalContent()
                            }


                            // checking if the patient is already referred interdepartamentally
                            // console.log(data)

                            myModal.show();
                        }
                    }
                })
                
                // $.ajax({
                //     url: '../SDN/check_interdept_refer.php',
                //     method: "POST", 
                //     data:data,
                //     dataType: "JSON",
                //     success: function(response){
                //         console.log(322)
                //         console.log(response)
                //         console.log(typeof response.status_interdept)

                //         if(response.status_interdept && response.status != "Approved"){
                //             // $('#approval-form').css('display','none')
                //             // $('.interdept-div-v2').css('display','flex')
                //             // $('#cancel-btn').css('display','block')

                //             $('#right-sub-div-e').css('display' , 'block')
                //             updateInterdeptFunc()

                //             $('#seen-by-lbl span').text((response.referring_seenBy) ? response.referring_seenBy : "Not seen yet")
                //             $('#seen-date-lbl span').text((response.referring_seenTime) ? response.referring_seenTime : "Not seen yet")
                            
                //             if (document.querySelectorAll('.pat-status-incoming')[global_index].textContent.includes("Approve")) {
                //                 $('#final-approve-btn').css('display','block')
                //             }
                //         }else{
                //             $('#approval-form').css('display','flex')
                //             $('.approval-main-content').css('display','block')
                //             $('.interdept-div-v2').css('display','none')
                //             $('#cancel-btn').css('display','none')
                //         }
                //     }
                // })


                // check if the item is locked or not
                

            }
        })
    }

    // const pencil_elements = document.querySelectorAll('.pencil-btn');
    //     pencil_elements.forEach(function(element, index) {
    //     element.addEventListener('click', function() {       
    //         ajax_method(index)
    //     });
    // });

    dataTable.on('click', '.pencil-btn', function () {
        // console.log('den');
        var row = $(this).closest('tr');
        var rowIndex = dataTable.row(row).index();
        // console.log(rowIndex)
        ajax_method(rowIndex);
    });

    // -800

    function loadStateFromSession(current_dataTable_index) {
        // console.log(running_timer_var , running_bool_var , running_startTime_var , running_hpercode_var, running_index_var)
        // upon logout

        if(post_value_reload === 'true'){
            // console.log('366')
            $.ajax({
                url: '../SDN/save_process_time.php',
                method: "POST",
                data : {what: 'continue'},
                dataType : 'JSON',
                success: function(response){
                //    console.log(response, encapsule)

                    for (let i = 0; i < response.length; i++) {
                        encapsule[i] = runTimer(i)
                    }

                    // for (let i = 0; i < response.length; i++) {
                    //     elapsedTime = (parseFloat(response[i].progress_timer) || 0) * 1000;
                    //     encapsule[i].start(elapsedTime, i);
                    // }

                    async function startTimers(response) {
                        for (let i = 0; i < response.length; i++) {
                            elapsedTime = (parseFloat(response[i].progress_timer) || 0) * 1000;
                            encapsule[i].start(elapsedTime, i);
                            
                            // Wait for the next animation frame before starting the next timer
                            await new Promise(requestAnimationFrame);
                        }
                    }
                    
                    startTimers(response);
                }
            })
        }
        
        else{
            // console.log('390')
            $.ajax({
                url: '../SDN/fetch_try.php',
                method: "POST",
                dataType : 'JSON',
                success: function(response){
                    // console.log(response)
                    
                    if(response.prev_inc != response.curr_inc){
                        // console.log('here')
                        move()
                    }

                    if(response.running_index.length >= 1){
                        running_bool_var = true;
                    }
                    // console.log(encapsule)
                    for (let i = 0; i < encapsule.length; i++) {
                        // encapsule[response.running_index[i]].stop();
                        if (encapsule[i] && typeof encapsule[i].stop === 'function') {
                            encapsule[i].stop();
                        }
                    }

                    encapsule = []

                    for (let i = 0; i < response.data.length; i++) {
                        encapsule[response.data[i]] = runTimer(parseInt(response.data[i]))
                    }

                    for (let i = 0; i < response.data.length; i++) {
                        elapsedTime = (parseFloat(response.running_timer[i]) || 0) * 1000;
                        // requestAnimationFrame(() => encapsule[i].start(elapsedTime));
                        encapsule[response.data[i]].start(elapsedTime)
                    }
                }
            })
        }
    }

    // on load
    loadStateFromSession()

    function runTimer(index) {
        let running = false;
        let elapsedTime = 0;
        let startTime = 0;
        let requestId = null;
        let lastLoggedSecond = 0;
        let abortController = new AbortController(); // For aborting AJAX requests
    
        // console.log("runTimer..." , index);
    
        function formatTime(milliseconds) {
            const totalSeconds = Math.floor(milliseconds / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
    
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
    
        function updateTimer() {
            if (!running) return; 
    
            const now = performance.now();
            elapsedTime = now - startTime;
            global_timer = formatTime(elapsedTime);
            
            // let went_here = false;
            if (document.querySelectorAll('.pat-status-incoming').length > 0) {
                // if(index > document.querySelectorAll('.stopwatch').length - 1){
                //     index -= 1
                //     went_here = true
                // }

                if (global_paging === 1) {
                    document.querySelectorAll('.stopwatch')[index].textContent = formatTime(elapsedTime);
                    // console.log(elapsedTime)
                    if(elapsedTime >= 900000){
                        document.querySelectorAll('.stopwatch')[index].style.color = 'red';
                    }else{
                        document.querySelectorAll('.stopwatch')[index].style.color = 'green';
                    }
                    document.querySelectorAll('.pat-status-incoming')[index].textContent = 'On-Process';
                    current_dataTable_index = index;
                }
                
                const currentSecond = Math.floor(elapsedTime / 1000);
                if (currentSecond > lastLoggedSecond) {
                    // console.log("update Timer...")
                    lastLoggedSecond = currentSecond;

                    // console.log("every second" , running_timer_var)
    
                    abortController.abort();
                    abortController = new AbortController(); 
                    
                    // if(went_here){
                    //     index += 1
                    // }

                    // console.log("check_ref_length - ", index , " - " , document.querySelectorAll('.pat-status-incoming').length, encapsule)

                    // Perform AJAX request
                    $.ajax({
                        url: '../SDN/fetch_onProcess.php',
                        method: "POST",
                        data: {
                            timer: elapsedTime / 1000,
                            running_bool: running,
                            startTime: running ? performance.now() : startTime,
                            hpercode: document.querySelectorAll('.hpercode')[index].value,
                            index: index,
                            check_ref_length : document.querySelectorAll('.pat-status-incoming').length
                        },
                        dataType: "JSON",
                        signal: abortController.signal, // Attach the abort signal
                        success: function(response) {
                            // console.log(654, index, response)
                            running_timer_var = response.timer;
                            running_startTime_var = response.startTime;
                            running_hpercode_var = response.hpercode;
                            running_index_var = response.index;

                            // if(response.changed_inc_total === "true"){
                            //     if(index >= 1){
                            //         index = response.index[index - 1]
                            //     }else{
                            //         index = response.index[index]
                            //     }
                            //     console.log(666, index)

                            //     // if(running_index_var.length >= 1){
                            //     //     running_bool_var = true;
                            //     // }
                            //     // console.log(encapsule, encapsule.length)
                            //     // // for(let i = index + 1; i < encapsule.length; i++){
                            //     // //     console.log(1245, "STOPPPPPPPPPPP", index,encapsule.length)
                            //     // //     encapsule[i].stop()
                            //     // // }
                            //     // for (let i = index + 1; i < running_index_var.length; i++) {
                            //     //     encapsule[running_index_var[i]].stop();
                            //     // }
            
                            //     // encapsule = []
            
                            //     // for (let i = 0; i < running_timer_var.length; i++) {
                            //     //     console.log(1251)
                            //     //     encapsule[running_index_var[i]] = runTimer(parseInt(running_index_var[i]))
                            //     // }

                            //     // for (let i = 0; i < running_index_var.length; i++) {
                            //     //     elapsedTime = (parseFloat(running_timer_var[i]) || 0) * 1000;
                            //     //     // requestAnimationFrame(() => encapsule[i].start(elapsedTime));
                            //     //     encapsule[running_index_var[i]].start(elapsedTime)
                            //     // }

                            //     // if(document.querySelectorAll('.pat-status-incoming')[index + 1].textContent == "")

                            //     if(document.querySelectorAll('.pat-status-incoming').length > 1){
                            //         console.log(728, index)
                            //         document.querySelectorAll('.stopwatch')[index + 1].textContent = "00:00:00"
                            //         document.querySelectorAll('.stopwatch')[index + 1].style.color = "black"
                            //         document.querySelectorAll('.pat-status-incoming')[index + 1].textContent = "Pending"
                            //     }else{
                            //         document.querySelectorAll('.stopwatch')[index].textContent = "00:00:00"
                            //         document.querySelectorAll('.stopwatch')[index].style.color = "black"
                            //         document.querySelectorAll('.pat-status-incoming')[index].textContent = "Pending"
                            //     }

                            // }

                            // console.log("updated after fetch session", running_timer_var, running_index_var , running_startTime_var, running_hpercode_var)
                        },
                        error: function(err) {
                            if (err.name === 'AbortError') {
                                // console.log('AJAX request aborted');
                            }
                        }
                    });
                }
                requestId = requestAnimationFrame(() => updateTimer('self'));
            }
            
            
        }
    
        function start(new_elapsedTime = 0, index) {
            if (running) return;
    
            if (new_elapsedTime != 0) {
                elapsedTime = new_elapsedTime;
            }
    
            running = true;
            startTime = performance.now() - elapsedTime;
            requestId = requestAnimationFrame(() => updateTimer('start'));
            // console.log("start function..." , index);
        }

        function stop() {
            // console.log("stop function....", index);
            running = false;
            cancelAnimationFrame(requestId); // Cancel the next animation frame
            abortController.abort(); // Cancel any pending AJAX requests

            // Stop and reset encapsule entry
            if (encapsule[index]) {
                delete encapsule[index]; // Remove from encapsule
            }
        }
    
        function reset() {
            running = false;
            elapsedTime = 0;
            lastLoggedSecond = 0;
            cancelAnimationFrame(requestId);
            abortController.abort();
        }
    
        // Expose control functions
        return { start, stop, reset, updateTimer };
    }
    

    function saveTimeSession(){
        // look only for the status that is On-Process
        let curr_index = 0;
        let interdept_status
        // check if theres a pending status currently on the interdepartamental referral
        for(let i = 0; i < status_interdept_arr.length; i++){
            if(status_interdept_arr[i].status_interdept != null){
                // console.log('here')
                interdept_status = i
            }
        }

        // console.log(status_interdept_arr)

        if(interdept_status != null || interdept_status != "" || interdept_status != undefined){
            curr_index = interdept_status + 1
        }else{
            for(let i = 0; i < document.querySelectorAll('.pat-status-incoming').length; i++){
                if(document.querySelectorAll('.pat-status-incoming')[i].textContent === "On-Process"){
                    curr_index = i;
                }
            }
        }

        // console.log(533, curr_index)
        
        $.ajax({
            url: '../SDN/fetch_onProcess.php',
            method: "POST", 
            data:{
                // timer: document.querySelectorAll('.stopwatch')[curr_index].textContent,
                timer : elapsedTime / 1000,
                running_bool : running,
                startTime : running ? performance.now() : startTime,
                hpercode: document.querySelectorAll('.hpercode')[curr_index].value,
                index: curr_index, // questionable,
            },
            success: function(response){
                // console.log(response)
                
            }
        })

    }
        
    window.addEventListener('beforeunload', function(event) {
        // event.preventDefault(); 
        saveTimeSession()
        // event.returnValue = 'Are you sure you want to leave?'; 
    });

    $.ajax({
        url: '../SDN/session_navigation.php',
        method: "POST", 
        data : {
            nav_path : ""
        },
        success: function(response){
            if(response === '"true"'){
                $(document).on('saveTimeSession', saveTimeSession);
            }
        }
    })

    // search incoming patients
    $('#incoming-search-btn').off('click', '#incoming-search-btn').on('click' , function(event){        
        
        let valid_search = false;
        let elements = [
            $('#incoming-referral-no-search').val(), 
            $('#incoming-last-name-search').val(), 
            $('#incoming-first-name-search').val(),
            $('#incoming-middle-name-search').val(), 
            $('#incoming-type-select').val(),  
            $('#incoming-agency-select').val(),  
            $('#incoming-status-select').val(), 
            $('#incoming-startDate-search').val(), 
            $('#incoming-endDate-search').val(),
            $('#incoming-tat-select').val(), 
            $('#incoming-sensitive-select').val()
        ]

        for (let val of elements) {
            if (val && val !== 'default') {
                valid_search = true;
                break; // No need to continue looping if any field is valid
            }
        }

        if(valid_search){
            $('.loader').show(); 
            $('#incoming-clear-search-btn').css('opacity' , '1')
            $('#incoming-clear-search-btn').css('pointer-events' , 'auto')

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
                sensitive : $('#incoming-sensitive-select').val(),
                startDate : $('#incoming-startDate-search').val(),
                endDate : $('#incoming-endDate-search').val(),
                // startDate : "2025-05-15",
                // endDate : "2025-05-16",
                tat : $('#incoming-tat-select').val(),
                status : $('#incoming-status-select').val(),
                where : 'search',
                where_type : 'incoming'
            }

            console.log(data)

            $.ajax({
                url: '../SDN/incoming_search.php',
                method: "POST", 
                data:data,
                // dataType:'JSON',
                success: function(response){
                    // console.log(response)

                    dataTable.clear();
                    dataTable.rows.add($(response)).draw();

                    length_curr_table = $('.tr-incoming').length
                    for(let i = 0; i < length_curr_table; i++){
                        toggle_accordion_obj[i] = true
                    }

                    const expand_elements = document.querySelectorAll('.accordion-btn');
                    expand_elements.forEach(function(element, index) {
                        element.addEventListener('click', function() {
                            // console.log(index)
                            global_breakdown_index = index;
                        });
                    });

                    // inactivityInterval = 300000
                    // startInactivityTimer();
                    stopContinuousFetch()
                },
                complete: function () {
                  $('.loader').hide(); // Hide loader whether success or error
                }

            }) 
        }else{
            $('#modal-body-incoming').text('Invalid Search')
            $('#ok-modal-btn-incoming').text('Close')
            $('#ok-modal-btn-incoming').css('margin-right' , '0')
            $('#yes-modal-btn-incoming').css('display' , 'none')
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
                // inactivityInterval = 10000
                // startInactivityTimer();
                handleUserInactivity()
                startContinuousFetch()
                $('#incoming-clear-search-btn').css('opacity' , '0.3')
                $('#incoming-clear-search-btn').css('pointer-events' , 'none')

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
                        // console.log(index)
                        global_breakdown_index = index;
                    });
                });

                enabledNextReferral()

                
            }
        }) 
    })

    dataTable.on('page.dt', function () {
        // clearInterval(running_timer_interval)
        // console.log(716)
        var currentPageIndex = dataTable.page();
        var currentPageNumber = currentPageIndex + 1;

        global_paging = currentPageNumber
    });

    function parseTimeToMilliseconds(timeString) {
        const [hours, minutes, seconds] = timeString.split(":");
        // console.log(hours, minutes, seconds)
        const totalMilliseconds = ((parseInt(hours, 10) * 60 + parseInt(minutes, 10)) * 60 + parseInt(seconds, 10)) * 1000;
        return totalMilliseconds;
        //5000
    }


    $(document).off('click', '#inter-dept-referral-btn').on('click' , '#inter-dept-referral-btn' , function(event){
        $('.interdept-div').css('display' , 'block')
        document.querySelector('.interdept-div').scrollIntoView({ behavior: 'smooth' });
    })

    $(document).off('click', '#int-dept-btn-forward').on('click' , '#int-dept-btn-forward' , function(event){
        $('#modal-title-incoming').text('Successed')
        document.querySelector('#modal-icon').className = 'fa-solid fa-circle-check'
        $('#modal-body-incoming').text('Successfully Forwarded')
        $('#ok-modal-btn-incoming').text('Close')
        $('#ok-modal-btn-incoming').css('margin-right' , '0')
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

        // check first if the current interdept is already rejected from the other department and ready for referral again
        if(refer_again_bool){
            rejection_data['action'] = "Check"
            // console.log(846)
            // console.log(rejection_data)

            $.ajax({
                url: '../SDN/reject_interdept.php',
                method: "POST", 
                data:rejection_data,
                dataType : "json",
                success: function(response){ 
                    // console.log(response)
                    response_value = response
                    
                    if(response){
                        let timeString = response['sent_interdept_time'];
                        let additionalSeconds = response['rejected_time']

                        // Split the time string into hours, minutes, and seconds
                        let parts = timeString.split(':');
                        let hours = parseInt(parts[0], 10) * 3600; // Convert hours to seconds
                        let minutes = parseInt(parts[1], 10) * 60; // Convert minutes to seconds
                        let seconds = parseFloat(parts[2]);        // Keep seconds as is

                        // Calculate total seconds (use Math.floor to round down to whole seconds)
                        let totalSeconds = Math.floor(hours + minutes + seconds + parseFloat(additionalSeconds));

                        // Extract hours, minutes, and seconds from totalSeconds
                        let newHours = Math.floor(totalSeconds / 3600);
                        let newMinutes = Math.floor((totalSeconds % 3600) / 60);
                        let newSeconds = totalSeconds % 60;

                        // Format the result back to HH:MM:SS format (without milliseconds)
                        let result = [
                            newHours.toString().padStart(2, '0'),
                            newMinutes.toString().padStart(2, '0'),
                            newSeconds.toString().padStart(2, '0')
                        ].join(':');

                        data['pause_time'] = result
                        $.ajax({
                            url: '../SDN/incoming_interdept.php',
                            method: "POST", 
                            data:data,
                            dataType: "JSON",
                            success: function(response){
                                // console.log(response)

                                $('.interdept-div').css('display','none')
                                $('#cancel-btn').css('display','block')
                                $('.approval-main-content').css('display','none')

                                // runTimer().stop()
                                // runTimer().reset()
                                // clearInterval(running_timer_interval)
                                
                                // console.log($('#inter-depts-select').val().toUpperCase())
                                document.querySelectorAll('.pat-status-incoming')[global_index].textContent = 'Pending - ' + $('#inter-depts-select').val().toUpperCase();

                                // enable the second request on the table while waiting for the current request that is on interdepartment already
                                // document.querySelectorAll('.tr-incoming').
                                myModal.hide()

                                // reset the value of approval details
                                const selectElement = document.getElementById('approve-classification-select');
                                selectElement.value = '';
                                selectElement.value = selectElement.options[0].value;
                                $('#eraa').val("")

                                // enabledNextReferral()
                                refer_again_bool = false
                            }
                        })

                    }
                }
            })

            
        }
        else{
            // console.log(923)
            // console.log(data)
            $.ajax({
                url: '../SDN/incoming_interdept.php',
                method: "POST", 
                data:data,
                dataType: "JSON",
                success: function(response){
                    // console.log(response)
    
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
        }
        
    })

    $('#pendingModal').off('click', '#imme-approval-btn').on('click', '#imme-approval-btn', function(event){
        defaultMyModal.show()
        $('#modal-body-incoming').text('Are you sure you want to approve this?')
        $('#modal-title-incoming').text('Confimation')
        $('#ok-modal-btn-incoming').text('No')
        $('#yes-modal-btn-incoming').css('display', 'block')
        type_approval = true
    })

    $('#pendingModal').off('click', '#imme-defer-btn').on('click', '#imme-defer-btn', function(event){
        defaultMyModal.show()
        // console.log('here')
        $('#modal-body-incoming').text('Are you sure you want to defer this?')
        $('#modal-title-incoming').text('Confimation')
        $('#ok-modal-btn-incoming').text('No')
        $('#yes-modal-btn-incoming').css('display', 'block')
        type_approval = true
     })

    // imme-defer-btn

    $('#myModal-incoming').off('click', '#yes-modal-btn-incoming').on('click', '#yes-modal-btn-incoming', function(event){
        function formatTime(milliseconds) {
            const totalSeconds = Math.floor(milliseconds);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        let data = {}

        if($('#imme-approval-btn').css('display') === 'flex'){
            data = {
                global_single_hpercode : document.querySelectorAll('.hpercode')[global_index].value,
                timer : formatTime(parseFloat(running_timer_var[global_index])),
                approve_details : $('#eraa').val(),
                case_category : $('#approve-classification-select').val(),
                action : 'Approve', // approve or deferr
                type_approval : type_approval,
                processed_by: $('#processed-by-span').text()
            }
        }else{
            data = {
                global_single_hpercode : document.querySelectorAll('.hpercode')[global_index].value,
                timer : formatTime(parseFloat(running_timer_var[global_index])),
                approve_details : $('#eraa').val(),
                case_category : "",
                action : 'Defer', // approve or deferr
                type_approval : type_approval,
                processed_by: $('#processed-by-span').text()
            }
        }

        if(refer_again_bool){
            rejection_data['action'] = "Check"
            // console.log(1010)
            // console.log(rejection_data)
            // console.log(1016, current_dataTable_index)

            $.ajax({
                url: '../SDN/reject_interdept.php',
                method: "POST", 
                data:rejection_data,
                dataType : "json",
                success: function(response){ 
                    // console.log(response)
                    response_value = response
                    
                    if(response){
                        let timeString = response['sent_interdept_time'];
                        let additionalSeconds = response['rejected_time']

                        // Split the time string into hours, minutes, and seconds
                        let parts = timeString.split(':');
                        let hours = parseInt(parts[0], 10) * 3600; // Convert hours to seconds
                        let minutes = parseInt(parts[1], 10) * 60; // Convert minutes to seconds
                        let seconds = parseFloat(parts[2]);        // Keep seconds as is

                        // Calculate total seconds (use Math.floor to round down to whole seconds)
                        let totalSeconds = Math.floor(hours + minutes + seconds + parseFloat(additionalSeconds));

                        // Extract hours, minutes, and seconds from totalSeconds
                        let newHours = Math.floor(totalSeconds / 3600);
                        let newMinutes = Math.floor((totalSeconds % 3600) / 60);
                        let newSeconds = totalSeconds % 60;

                        // Format the result back to HH:MM:SS format (without milliseconds)
                        let result = [
                            newHours.toString().padStart(2, '0'),
                            newMinutes.toString().padStart(2, '0'),
                            newSeconds.toString().padStart(2, '0')
                        ].join(':');

                        data['pause_time'] = result
                        data['referred_again'] = 'true'
                        // console.log(data)


                        $.ajax({
                            url: '../SDN/approved_pending.php',
                            method: "POST",   
                            data : data,
                            // dataType:'JSON',
                            success: function(response){
                                // console.log(response)
                
                                // clearInterval(running_timer_interval)
                                // runTimer().stop()
                                document.querySelectorAll('.pat-status-incoming')[global_index].textContent = 'Approved';
                                myModal.hide()
                                
                                dataTable.clear();
                                dataTable.rows.add($(response)).draw();
                                
                                length_curr_table = $('.tr-incoming').length
                                for(let i = 0; i < length_curr_table; i++){
                                    toggle_accordion_obj[i] = true
                                }
                
                                // reset the prev value of the eraa and the select element
                                const selectElement = document.getElementById('approve-classification-select');
                                selectElement.value = '';
                                selectElement.value = selectElement.options[0].value;
                                $('#eraa').val("")
                
                                //disabled again the interdepartamental buttons and immediate referral button
                                $('#imme-approval-btn').css('opacity' , '0.6')
                                $('#imme-approval-btn').css('pointer-events' , 'none')
                
                                $('#inter-dept-referral-btn').css('opacity' , '0.6')
                                $('#inter-dept-referral-btn').css('pointer-events' , 'none')
                
                                const expand_elements = document.querySelectorAll('.accordion-btn');
                                    expand_elements.forEach(function(element, index) {
                                    element.addEventListener('click', function() {
                                        // console.log(index)
                                        global_breakdown_index = index;
                                    });
                                });
                
                                // reset timer variables
                                elapsedTime = 0;
                                running = false;
                                lastLoggedSecond = 0;
                
                                // enabledNextReferral()
                                // console.log(running)
                                if(running){
                                    runTimer().stop()
                                    // console.log(1103, current_dataTable_index)
                                    current_dataTable_index = parseInt(current_dataTable_index) - 1
                                    runTimer(current_dataTable_index).updateTimer
                                    refer_again_bool = false
                                }
                                
                            }
                        })
                    }
                }
            })

            // refer_again_bool = false
        }
        else{
            // console.log(1260, global_index, running_timer_var, document.querySelectorAll('.hpercode')[global_index].value)
 
            data['index'] = global_index
            data['hpercode'] = document.querySelectorAll('.hpercode')[global_index].value

            // console.log(data)
            $.ajax({
                url: '../SDN/approved_pending.php',
                method: "POST",   
                data : data,
                dataType:'JSON',
                success: function(response){

                    if(response.running_timer_var.length > 0){
                        running_timer_var = response.running_timer_var
                        running_startTime_var = response.running_startTime_var
                        running_hpercode_var = response.running_hpercode_var
                        running_index_var = response.running_index_var
                    }else{
                        running_timer_var = []
                        running_startTime_var = []
                        running_hpercode_var = []
                        running_index_var = []
                        running_bool_var = false
                    }
                
                    // console.log("updated after timerSession")
                    // console.log(running_timer_var)
                    // console.log(running_startTime_var)
                    // console.log(running_hpercode_var)
                    // console.log(running_index_var)

                    // console.log('dendendendenden')

                    // if(running_index_var.length >= 1){
                    //     running_bool_var = true;
                    // }
                    
                    // encapsule = encapsule.filter(item => item !== "" && item !== null && item !== undefined);
                    // console.log(encapsule)

                    // for(let i = 0; i < running_index_var.length; i++){
                    //     console.log(1245, "STOPPPPPPPPPPP")
                    //     encapsule[running_index_var[i]].stop()
                    // }

                    for (let i = 0; i < encapsule.length; i++) {
                        if (encapsule[i] && typeof encapsule[i].stop === 'function') {
                            encapsule[i].stop();
                        }
                    }

                    encapsule = []
                    // console.log(encapsule)

                    for (let i = 0; i < running_timer_var.length; i++) {
                        // console.log(1251)
                        encapsule[running_index_var[i]] = runTimer(parseInt(running_index_var[i]))
                    }
                    // console.log("pat_ref_cancel" , encapsule , running_index_var)

                    // for (let i = 0; i < running_index_var.length; i++) {
                    //     (function(index) {
                    //         elapsedTime = (parseFloat(running_timer_var[index]) || 0) * 1000;
                    //         requestAnimationFrame(() => encapsule[index].start(elapsedTime));
                    //         // encapsule[i].start(elapsedTime)
                    //     })(i);
                    // }

                    for (let i = 0; i < running_index_var.length; i++) {
                        elapsedTime = (parseFloat(running_timer_var[i]) || 0) * 1000;
                        // requestAnimationFrame(() => encapsule[i].start(elapsedTime));
                        encapsule[running_index_var[i]].start(elapsedTime)
                    }

                    document.querySelectorAll('.pat-status-incoming')[global_index].textContent = 'Approved';
                    myModal.hide()

                    dataTable.clear();
                    dataTable.rows.add($(response.table_html)).draw();
                    
                    length_curr_table = $('.tr-incoming').length
                    for(let i = 0; i < length_curr_table; i++){
                        toggle_accordion_obj[i] = true
                    }
    
                    // reset the prev value of the eraa and the select element
                    const selectElement = document.getElementById('approve-classification-select');
                    selectElement.value = '';
                    selectElement.value = selectElement.options[0].value;
                    $('#eraa').val("")
    
                    //disabled again the interdepartamental buttons and immediate referral button
                    $('#imme-approval-btn').css('opacity' , '0.6')
                    $('#imme-approval-btn').css('pointer-events' , 'none')
    
                    $('#inter-dept-referral-btn').css('opacity' , '0.6')
                    $('#inter-dept-referral-btn').css('pointer-events' , 'none')
    
                    const expand_elements = document.querySelectorAll('.accordion-btn');
                        expand_elements.forEach(function(element, index) {
                        element.addEventListener('click', function() {
                            // console.log(index)
                            global_breakdown_index = index;
                        });
                    });
    
                    // // reset timer variables
                    // //check if needed to reset the variables, especially on the mothefucker running variable
                    // elapsedTime = 0;
                    // running = false;
                    // lastLoggedSecond = 0;
    
                    // enabledNextReferral()

                    // check also on some instances on the thingy
                    // enabledNextReferral_normal

                    // console.log("updated after approved", running_timer_var , running_timer_var.length , running_bool_var , running_startTime_var , running_hpercode_var, running_index_var)
                }
            })

        }
     })

     $(document).off('click', '.accordion-btn').on('click' , '.accordion-btn' , function(event){
        var accordion_index = $('.accordion-btn').index(this);
        // console.log(accordion_index)

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

    $(document).off('click', '.pre-emp-text').one('click', '.pre-emp-text', function() {
        // console.log('asdfasdfasdafasdfad')
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
    

    $(document).on('input' , '#eraa' , function(event) {
        if ($('#approve-classification-select').val() !== '' && $('#eraa').val().length > 20) {
            $('#imme-approval-btn').css('opacity' , '1')
            $('#imme-approval-btn').css('pointer-events' , 'auto')

            $('#inter-dept-referral-btn').css('opacity' , '1')
            $('#inter-dept-referral-btn').css('pointer-events' , 'auto')
        }

        if($('#imme-approval-btn').css('display') === 'none' && $('#eraa').val().length > 10){
            $('#imme-defer-btn').css('opacity' , '1')
            $('#imme-defer-btn').css('pointer-events' , 'auto')
        }
    });

    // $(document).on('keydown' , '#eraa' , function(event){
    //     console.log(1024)
    //     if (event.keyCode === 8 && $('#eraa').val().length < 20) {
    //         $('#imme-approval-btn').css('opacity' , '0.3')
    //         $('#imme-approval-btn').css('pointer-events' , 'none')

    //         $('#inter-dept-referral-btn').css('opacity' , '0.3')
    //         $('#inter-dept-referral-btn').css('pointer-events' , 'none')
    //     }
    // });
 
    $('#cancel-btn').off('click', '#cancel-btn').on('click', function(event) {
        defaultMyModal.show()
        $('#modal-title-incoming').text('Confirmation')
        $('#modal-body-incoming').text('Are you sure you want to cancel this referral?')
        clearInterval(running_timer_interval_update)
    });

    $(document).off('click', '#proceed-ref-res').on('click' , '#proceed-ref-res' , function(event){
        document.getElementById('right-sub-div-b-1').scrollIntoView({ behavior: 'smooth' });
        // right-sub-div-b-1
    })
    
    $('#pendingModal').off('change', '#select-response-status').on('change', '#select-response-status', function(event) {
        var selectedValue = $(this).val();
        // console.log(selectedValue)
        
        $('#right-sub-div-c').css('display' , 'flex')
        document.getElementById('right-sub-div-c').scrollIntoView({ behavior: 'smooth' });

        $('#approval-form').css('display','flex')
        if (selectedValue === 'Approved') {
            $('#approval-form').css('height','650px')

            $('#approval-title-div').text("Approval Form")
            $('#case-cate-title').css('display' , 'flex')
            $('#approve-classification-select').css('display' , 'flex')
            $('#pre-text').css('display' , 'flex')


            $('#imme-approval-btn').css('display' , 'flex')
            $('#inter-dept-referral-btn').css('display', 'none')
            $('#imme-defer-btn').css('display', 'none')

            $('.interdept-div').css('display' , 'none')
 
        } 
        else if (selectedValue === 'Interdepartamental') {
            $('#approval-form').css('height','650px')

            $('#approval-title-div').text("Approval Form")
            $('#case-cate-title').css('display' , 'flex')
            $('#approve-classification-select').css('display' , 'flex')
            $('#pre-text').css('display' , 'flex')

            $('#imme-approval-btn').css('display' , 'none')
            $('#inter-dept-referral-btn').css('display', 'flex')
            $('#imme-defer-btn').css('display', 'none')

            // $('#approval-form').css('display' , 'none')

            $('.interdept-div').css('display' , 'none')
            // $('.interdept-div').css('margin-top' , '2%')
        }
        else if (selectedValue === 'Deferred') {
            // console.log('fcxsa')
            $('#approval-form').css('height','400px')
            //toggle
            $('#approval-title-div').text("Deferral Form")
            $('#case-cate-title').css('display' , 'none')
            $('#approve-classification-select').css('display' , 'none')
            $('#pre-text').css('display' , 'none')
            

            $('#imme-defer-btn').css('display' , 'flex')
            $('#imme-approval-btn').css('display' , 'none')
            $('#inter-dept-referral-btn').css('display', 'none')

            $('.interdept-div').css('display' , 'none')

        }
    })
    
    $(document).off('click', '#final-approve-btn').on('click' , '#final-approve-btn' , function(event){
        // console.log('dendendendendenden')
        const data = {
            global_single_hpercode : document.querySelectorAll('.hpercode')[global_index].value,
            timer : final_time_total,
            approve_details : $('#eraa').val(), 
            case_category : $('#approve-classification-select').val(),
            action : "Approve",
            type_approval : "false",
            interdept_approval : "true"
        }

        let now = new Date();
        let year = now.getFullYear();
        let month = (now.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-based
        let day = now.getDate().toString().padStart(2, '0');

        let hours = now.getHours().toString().padStart(2, '0');
        let minutes = now.getMinutes().toString().padStart(2, '0');
        let seconds = now.getSeconds().toString().padStart(2, '0');

        data['final_approve_date_time'] = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

        // console.log(data);

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
                    // console.log(1021, i , document.querySelectorAll('.pat-status-incoming')[i].textContent, document.querySelectorAll('.pat-status-incoming')[i].textContent === 'On-Process')
                    if(document.querySelectorAll('.pat-status-incoming')[i].textContent === 'On-Process'){
                        yawa = i;
                        break;
                    }
                }                
                
                // runTimer().stop()
                // console.log(1029, yawa)
                if(yawa >= 0){
                    // console.log(1031 , 'here')
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
                //         console.log('den')
                //         ajax_method(index)
                //     });
                // });


                // dataTable.on('click', '.pencil-btn', function () {
                //     console.log('den');
                //     var row = $(this).closest('tr');
                //     var rowIndex = dataTable.row(row).index();
                //     ajax_method(rowIndex);
                // });

                enabledNextReferral()
                // if()
            }
        })

        
    });


    // sensitive case
    
    $('.incoming-container').off('click', '.sensitive-case-btn').on('click', '.sensitive-case-btn', function(event){
        //reset the the buttons in modal after the previous 
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
        ok_btn_modal_origin = "sensitive-case-btn"
    })


    $('#ok-modal-btn-incoming').off('click', '#ok-modal-btn-incoming').on('click' , function(event){
        if($('#ok-modal-btn-incoming').text() === 'Close'){
            // console.log('done interdept referral shared')
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
            // console.log(mcc_passwords_validity)
            if (mcc_passwords_validity) {
                let sensitive_hpercode = document.querySelectorAll('.sensitive-hpercode')

            //    console.log(sensitive_case_btn_index)
            //    console.log(sensitive_hpercode)

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
                // console.log(sensitive_btn_index)
                // $('.sensitive-lock-icon').eq(sensitive_btn_index).css('color', 'red');
            
                // // Fade back to normal color after 2 seconds
                // setTimeout(function() {
                //     $('.sensitive-lock-icon').eq(sensitive_btn_index).css('color', ''); // Reset to original color
                // }, 2000);
            }
        }
        else if($('#ok-modal-btn-incoming').text() === 'OK' && ok_btn_modal_origin == "update-stat-check-btn"){
            // console.log(1412)
        }else if($('#ok-modal-btn-incoming').text() === 'OK' && ok_btn_modal_origin == "select-icd-diagnosis"){
            defaultMyModal.hide()
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
    
    // $('#pendingModal').off('change', '#select-response-status').on('change', '#select-response-status', function(event) {
    $('#pendingModal').off('click', '#update-stat-check-btn').on('click', '#update-stat-check-btn', function(event) {
    // $(document).on('click', '#update-stat-check-btn', function(event){
        const  selectedValue = $('#update-stat-select').val();
        let data = {
            hpercode : document.querySelectorAll('.hpercode')[global_index].value,
            newStatus : selectedValue
        }
        // console.log(data)
        
        $.ajax({
            url: '../SDN/update_referral_status.php',
            method: "POST",
            data : data,
            success: function(response){
                // console.log(response)
                myModal.hide()
                
                $('#pat-status-form').text(data.newStatus)
                $('#modal-title-incoming').text("Successful")
                $('#modal-body-incoming').text('Successfully Updated')

                $('#ok-modal-btn-incoming').text('OK')
                $('#yes-modal-btn-incoming').css('display', 'none') 
            
                defaultMyModal.show()
                $('#save-update').hide(); 
                $('#update-stat-select').prop('selectedIndex', 0);

                ok_btn_modal_origin = "update-stat-check-btn"
            }
         })
    });

    $('#pendingModal').off('click', '#refer-again-btn').on('click', '#refer-again-btn', function(event) {
        // close yung interdept status
        $('#right-sub-div-e').css('display' , 'none')
        $('#select-response-status').css('pointer-events' , 'auto')
        $('#select-response-status').css('background' , 'transparent')
        $('#select-response-status option:first').text('Select');

        // enable yung status choices ulit
        // get the time spent ng unang dept, i add sa current main timer.
        rejection_data['action'] = "Add"
        // console.log(rejection_data)

        $.ajax({
            url: '../SDN/reject_interdept.php',
            method: "POST", 
            data:rejection_data,
            // dataType : 'json', 
            success: function(response){
                refer_again_bool = true;
                // console.log(response)
            }
        })
    })

    $('#pendingModal').off('click', '#reject-details-btn').on('click', '#reject-details-btn', function(event) {
        $('#right-sub-div-e').css('height',  '350px')
        $('#rejection-details-text').css('display' , 'flex')
    })

    $('#pendingModal').off('click', '#icd-select').on('click', '#icd-select', function(event) {
        // console.log(1608)
    })

    $('#pendingModal').off('input', '.icd-inputs').on('input', '.icd-inputs', function(event) {
        const input_name = $(this).attr('name')
        const index = $('.icd-inputs').index(this);
        // console.log(input_name, $(this).val() )

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
                // console.log(response)
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

    // Rebind event after table is redrawn
    $(document).on('click', '.toggle-contact-btn', function () {
        var $btn = $(this);
        var $row = $btn.closest('tr');

        $row.find('.contact-extra').slideToggle(200);

        // Optionally change button text
        if ($btn.text() === "More Details") {
            $btn.text("Less Details");
        } else {
            $btn.text("More Details");
        }
    });


    // Attach a cleanup event to stop the timer when this content is replaced
    function cleanupContent() {
        if (inactivityTimer !== null) {
            clearInterval(inactivityTimer);
            inactivityTimer = null;
            // console.log("Content-specific timer stopped.");
        }
    }

    // Listen for an event indicating content unload
    window.addEventListener("unloadContent", cleanupContent);

    // Optional: Clean up when the window unloads (to be extra safe)
    window.addEventListener("beforeunload", cleanupContent);
})
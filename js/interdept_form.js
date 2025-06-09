$(document).ready(function(){
    $('#myDataTable').DataTable({
        "bSort": false,
        "paging": true, 
        "pageLength": 6, 
        "lengthMenu": [ [6, 10, 25, 50, -1], [6, 10, 25, 50, "All"] ],
    });

    var dataTable = $('#myDataTable').DataTable();
    $('#myDataTable thead th').removeClass('sorting sorting_asc sorting_desc');
    dataTable.search('').draw(); 

    let global_index = 0, global_paging = 1, global_timer = "", global_breakdown_index = 0;
    const myModal = new bootstrap.Modal(document.getElementById('pendingModal'));
    const defaultMyModal = new bootstrap.Modal(document.getElementById('myModal-incoming'));
    // myModal.show()

    let userIsActive = true;

    function handleUserActivity() {
        userIsActive = true;
    }

    function handleUserInactivity() {
        userIsActive = false;
        $.ajax({
            url: '../php/fetch_interval.php',
            method: "POST",
            data : {
                from_where : 'incoming_interdept'
            },
            success: function(response) {

                // dataTable.clear();
                // dataTable.rows.add($(response)).draw();

                const pencil_elements = document.querySelectorAll('.pencil-btn');
                    pencil_elements.forEach(function(element, index) {
                    element.addEventListener('click', function() {
                        ajax_method(index)
                    });
                });
            }
        });
    }

    document.addEventListener('mousemove', handleUserActivity);

    const inactivityInterval = 1000; 

    function startInactivityTimer() {
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
        const data = {
            hpercode: document.querySelectorAll('.hpercode')[index].value,
            from:'incoming'
        }
        $.ajax({
            url: '../php/process_pending.php',
            method: "POST", 
            data:data,
            success: function(response){
                document.querySelector('.ul-div').innerHTML = ''
                document.querySelector('.ul-div').innerHTML += response
                if(document.querySelectorAll('.pat-status-incoming')[index].textContent == 'Pending'){
                    runTimer(index, 0, 0, 0) // secs, minutes, hours
                    let data = {
                        hpercode : document.querySelectorAll('.hpercode')[index].value,
                        from : 'interdept'
                    }
                    $.ajax({
                        url: '../SDN/pendingToOnProcess.php',
                        method: "POST", 
                        data:data
                    })
                }

                // checking if the patient is already referred interdepartamentally
                // $.ajax({
                //     url: '../SDN/check_interdept_refer.php',
                //     method: "POST", 
                //     data:data,
                //     success: function(response){
                //         if(response === '1'){
                //             $('#approval-form').css('display','none')
                //             $('.interdept-div-v2').css('display','flex')
                //             $('#cancel-btn').css('display','block')
                //         }
                //     }
                // })

                myModal.show();
            }
        })
    }

    const pencil_elements = document.querySelectorAll('.pencil-btn');
        pencil_elements.forEach(function(element, index) {
        element.addEventListener('click', function() {

            ajax_method(index)

            // lobal_index = index

            // possible redundant
            // const data = {
            //     hpercode: document.querySelectorAll('.hpercode')[index].value,
            //     from: 'incoming'
            // }
            // $.ajax({
            //     url: '../php/process_pending.php',
            //     method: "POST", 
            //     data:data,
            //     success: function(response){
            //         document.querySelector('.ul-div').innerHTML = ''
            //         document.querySelector('.ul-div').innerHTML += response
                    
            //         // if(document.querySelectorAll('.pat-status-incoming')[index].textContent == 'Pending'){
            //         //     runTimer(index, 0, 0, 0) // secs, minutes, hours
            //         // }
            //         myModal.show();

            //     }
            // })
        });
    });

    if($('#running-timer-input').val() !== "" && $('#running-timer-input').val() !== "00:00:00"){
        const parts = $('#running-timer-input').val().split(':');
        // Extract hours, minutes, and seconds
        let hours = 0;
        let minutes = 0;
        let seconds = 0;
        
        if (parts.length === 3) {
            hours = parseInt(parts[0], 10);
            minutes = parseInt(parts[1], 10);
            seconds = parseInt(parts[2], 10);
        } else if (parts.length === 2) {
            minutes = parseInt(parts[0], 10);
            seconds = parseInt(parts[1], 10);
        } else if (parts.length === 1) {
            seconds = parseInt(parts[0], 10);
        }
        runTimer(0, seconds, minutes, hours)
    }

    // when refresh
    window.addEventListener('beforeunload', function(event) {
        $.ajax({
            url: '../php/fetch_onProcess.php',
            method: "POST", 
            data:{
                timer: document.querySelectorAll('.stopwatch')[0].textContent,
                hpercode: document.querySelectorAll('.hpercode')[0].value
            },
            dataType: "JSON",
            success: function(response){

                document.querySelector('.referral-details').innerHTML += response
                runTimer(index)
                myModal.show();
            }
        })
    });

    function pad(num) {
        return (num < 10 ? '0' : '') + num;
    }

    let interval_db = 0;
    function runTimer(index, sec, min, hrs){
        let seconds = sec;
        let minutes = min;
        let hours = hrs;

        running_timer_interval = setInterval(function() {
            seconds++;

            if (seconds === 60) {
                seconds = 0;
                minutes++;
            }

            if (minutes === 60) {
                minutes = 0;
                hours++;
            }

            const formattedTime = pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
            global_timer = formattedTime
            // Display the time in the HTML element
            if(global_paging === 1){
                document.querySelectorAll('.stopwatch')[index].textContent = formattedTime;
                document.querySelectorAll('.pat-status-incoming')[index].textContent = 'On-Process';
            }

        
            if(interval_db === 5){
                $.ajax({
                    url: '../SDN/session_timer.php',
                    method: "POST", 
                    data:{
                        formattedTime: formattedTime,
                        hpercode: document.querySelectorAll('.hpercode')[0].value,
                        from:'interdept'
                    },
                    success: function(response){
                        // response = JSON.parse(response);   
                    }
                })
                interval_db = 0;
            }else{
                $.ajax({
                    url: '../SDN/session_timer.php',
                    method: "POST", 
                    data:{
                        formattedTime: formattedTime,
                        hpercode: document.querySelectorAll('.hpercode')[0].value,
                        from:'incoming'
                    },
                    success: function(response){
                        // response = JSON.parse(response);   
                    }
                })
            }
            
            interval_db += 1;
        }, 1000); 
    }

    $('.pre-emp-text').on('click' , function(event){
        var originalString = event.target.textContent;
        // Using substring
        var stringWithoutPlus = originalString.substring(2);

        // Or using slice
        // var stringWithoutPlus = originalString.slice(2);
        $('#eraa').val($('#eraa').val() + " " + stringWithoutPlus  + " ")
    })

    // inter-approval-btn
    $('#inter-approval-btn').on('click' , function(event){
        defaultMyModal.show()
    })

    // yes-modal-btn-incoming
    $('#yes-modal-btn-incoming').on('click' , function(event){
        clearInterval(running_timer_interval)
        let data = {
            hpercode: document.querySelectorAll('.hpercode')[0].value,
            final_time : global_timer
        }
        $.ajax({
            url: '../SDN/approve_pending_interdept.php',
            method: "POST", 
            data:data,
            success: function(response){
                // response = JSON.parse(response);   
            }
        })
    })

})
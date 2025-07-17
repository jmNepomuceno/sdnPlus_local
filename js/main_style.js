let mobile_responsive = false; 
if (window.matchMedia("(max-width: 480px)").matches) {
    document.querySelector('#main-div .header-div .side-bar-title #sdn-title-h1').style.display = "none"
    document.querySelector('#main-div .header-div .side-bar-title').style.width = "50px"
    //250px

    document.querySelector('#main-div .aside-main-div #side-bar-div').style.display = "none"
    mobile_responsive = true; 
}



let idleTime = 0;
let timeoutSeconds = 0;
let idleInterval = null;
let modalShown = false;

function resetIdleTime() {
    idleTime = 0;
    // console.log("Idle time reset");
}

function startIdleTimer() {
    if (idleInterval !== null) {
        clearInterval(idleInterval); // in case already running
    }

    idleInterval = setInterval(() => {
        idleTime++;
        console.log(idleTime);

        if (idleTime >= timeoutSeconds && !modalShown) {
            const reauthModal = new bootstrap.Modal(document.getElementById('reauthModal'));
            reauthModal.show();
            modalShown = true;
        }
    }, 1000);

    // console.log("Idle timer started");
}

function stopIdleTimer() {
    if (idleInterval !== null) {
        clearInterval(idleInterval);
        idleInterval = null;
        // console.log("Idle timer stopped");
    }
}

$(document).ready(function() {
    let user_role = window.user_role || ''; // Example: get from global or set default
    // console.log("User role:", user_role);

    if (user_role === 'rhu_account') {
        timeoutSeconds = 1800; // 30 minutes
    } else if (user_role === 'doctor_admin') {
        timeoutSeconds = 3600; // 1 hour
    } else if (user_role === 'admin') {
        timeoutSeconds = 2; // 1 hour
    } 

    

    // Attach activity listeners to reset idle timer
    document.addEventListener('mousemove', resetIdleTime);
    document.addEventListener('keypress', resetIdleTime);

    // Start the idle timer
    // startIdleTimer();
    if(user_role === "rhu_account"){
        document.getElementById('nav-drop-account-div').style.height = "300px"
    }

    // window height
    const screenHeight = window.innerHeight;
    // window width
    const screenWidth = window.innerWidth;

    // load the 4 web pages
    // 727 1536

    const loadContent = (url) => {
        // Notify the currently loaded content to clean up
        const unloadEvent = new Event("unloadContent");
        window.dispatchEvent(unloadEvent);

        let nav_path = false;
        if (url.includes('incoming_form2')) {
            nav_path = true
        } else {
            nav_path = false
        }

        $.ajax({
            url: '../SDN/session_navigation.php',
            method: "POST",
            data : {
                nav_path : nav_path
            },
            success: function(response) {
            }
        });

        $.ajax({
            url:url, 
            success: function(response){
                $('#container').html(response);
            }
        })
    }

    for(let i = 0; i < $('.side-bar-navs-class').length; i++){
        $('.side-bar-navs-class').css('opacity' , '0.3')
        $('.side-bar-navs-class').css('border-top' , 'none')
        $('.side-bar-navs-class').css('border-bottom' , 'none')
    }


    const myModal_main = new bootstrap.Modal(document.getElementById('myModal-main'));
    const tutorialModal = new bootstrap.Modal(document.getElementById('tutorialModal'));
    const traverseModal = new bootstrap.Modal(document.getElementById('myModal-traverse'));
    const creditModal = new bootstrap.Modal(document.getElementById('creditModal'));
    const updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
    const concernModal = new bootstrap.Modal(document.getElementById('concernModal'));
    const surveyModal = new bootstrap.Modal(document.getElementById('surveyModal'));
    const mssSettingModal = new bootstrap.Modal(document.getElementById('mss-setting-modal'));
    
    const carousel = document.getElementById('tutorial-carousel');

    if(running_bool === "true" || running_bool === true){
        loadContent('../SDN/incoming_form2.php')
    }else{
        loadContent('../SDN/default_view2.php')
    }

    // Function to parse query parameters from URL  
    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] === variable) {
                return pair[1];
            }
        }
        return null;
    }

    // Check if the loadContent parameter exists in the URL
    var loadContentParam = getQueryVariable('loadContent');

    if (loadContentParam) {
        loadContent(loadContentParam);
    }else{
        loadContent('../SDN/default_view2.php')
    }

    jQuery.noConflict();
    let current_page = ""
    let fetch_timer = 0
    
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

    let prevReferralCount = 0;
    let soundTimeout = null;

    function fetchMySQLData() {
        $.ajax({
            url: '../SDN/fetch_interval.php',
            method: "POST",
            data: {
                from_where: 'bell'
            },
            dataType: "JSON",
            success: function(response) {
                const currentCount = response[0].length;
                const processingCount = parseInt(response[1].count);

                $('#notif-span').text(currentCount);
                $('#notif-circle').css('display', currentCount >= 1 ? 'block' : 'none');
                $('#notif-span').css('font-size', currentCount > 9 ? '0.65rem' : '');

                // Group and count by type
                let type_counter = [];
                response[0].forEach(ref => {
                    if (!type_counter.includes(ref.type)) {
                        type_counter.push(ref.type);
                    }
                });

                let notifHTML = '';
                type_counter.forEach((type_var, i) => {
                    let type_counts = response[0].filter(r => r.type === type_var).length;
                    notifHTML += `
                        <div>
                            <h4 class="font-bold text-lg">${type_counts}</h4>
                            <h4 class="font-bold text-lg">${type_var}</h4>
                        </div>`;
                });
                $('#notif-sub-div').html(notifHTML);

                // Logic for sound
                if (currentCount >= 1 && processingCount === 0) {
                    playAudio(); // Play continuously if no one is processing
                    clearTimeout(soundTimeout);
                } else if (
                    currentCount > prevReferralCount &&  // new referral arrived
                    processingCount >= 1                 // while someone is processing
                ) {
                    playAudio();
                    clearTimeout(soundTimeout); // clear previous timeout if exists
                    soundTimeout = setTimeout(() => {
                        stopSound();
                    }, 10000); // stop after 5 seconds
                } else {
                    stopSound(); // fallback stop if none of the above applies
                }

                // Update the previous count
                prevReferralCount = currentCount;
            }
        });
    }  


    function startInactivityTimer() {
        // clearInterval(inactivityTimer);
        inactivityTimer = setInterval(() => {
            fetchMySQLData()
        }, 10000);
    } 

    fetchMySQLData(); 
    startInactivityTimer();

    // let pollingInterval = 1000; // Start with 1 second
    // const maxInterval = 60000; // Cap at 60 seconds
    // let lastData = null; // Cache the last fetched data
    
    // async function pollServer() {
    //     try {
    //         const response = await fetch('../SDN/fetch_interval.php', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json'
    //             },
    //             body: JSON.stringify({ from_where: 'bell' })
    //         });
    
    //         if (response.ok) {
    //             const data = await response.json();
    
    //             // Check for changes before processing
    //             if (JSON.stringify(data) !== JSON.stringify(lastData)) {
    //                 processUpdates(data); // Call a function to handle updates
    //                 lastData = data; // Update the cached data
    //                 pollingInterval = 1000; // Reset interval on changes
    //             } else {
    //                 pollingInterval = Math.min(pollingInterval * 2, maxInterval); // Exponential backoff when no updates
    //             }
    //         } else {
    //             console.error("Server error:", response.status);
    //             pollingInterval = Math.min(pollingInterval * 2, maxInterval); // Exponential backoff on error
    //         }
    //     } catch (error) {
    //         console.error("Network error:", error);
    //         pollingInterval = Math.min(pollingInterval * 2, maxInterval); // Exponential backoff on failure
    //     } finally {
    //         setTimeout(pollServer, pollingInterval); // Schedule the next poll
    //     }
    // }
    
    // function processUpdates(data) {
    
    //     const notifSpan = document.getElementById('notif-span');
    //     const notifSubDiv = document.getElementById('notif-sub-div');
    //     const notifCircle = document.getElementById('notif-circle');
    
    //     notifSpan.textContent = data[0].length;
    
    //     if (data[0].length > 9) {
    //         notifSpan.style.fontSize = '0.65rem';
    //     }
    
    //     if (data[0].length >= 1) {
    //         notifCircle.style.display = 'block';
    
    //         // Count types
    //         let type_counter = [];
    //         for (let i = 0; i < data[0].length; i++) {
    //             if (!type_counter.includes(data[0][i]['type'])) {
    //                 type_counter.push(data[0][i]['type']);
    //             }
    //         }
    
    //         // Clear and update notification sub-div
    //         notifSubDiv.innerHTML = '';
    //         for (let i = 0; i < type_counter.length; i++) {
    //             let type_var = type_counter[i];
    //             let type_counts = 0;
    
    //             for (let j = 0; j < data[0].length; j++) {
    //                 if (type_counter[i] === data[0][j]['type']) {
    //                     type_counts += 1;
    //                 }
    //             }
    
    //             notifSubDiv.innerHTML += `
    //                 <div>
    //                     <h4 class="font-bold text-lg">${type_counts}</h4>
    //                     <h4 class="font-bold text-lg">${type_var}</h4>
    //                 </div>
    //             `;
    //         }
    
    //         // Play or stop sound based on counts
    //         if (data[0].length >= 1 && data[1].count == 0) {
    //             playAudio();
    //         }
    //         if (data[0].length >= 1 && data[1].count >= 1) {
    //             stopSound();
    //         }
    //     } else {
    //         notifCircle.style.display = 'none';
    //         stopSound();
    //     }
    // }
    
    // Start polling
    // pollServer();
    
    let side_bar_btn_counter = 0
    $('#side-bar-mobile-btn').on('click' , function(event){
        document.querySelector('#side-bar-div').classList.toggle('hidden');

        if(side_bar_btn_counter === 0){
            document.querySelector('#side-bar-mobile-btn').className = 'side-bar-mobile-btn w-[50%] ml-2 h-[10px] absolute flex flex-row justify-start items-center cursor-pointer transition duration-700 ease-in-out'
            side_bar_btn_counter = 1;
            $('#sdn-title-h1').addClass('hidden')
        }else{
            document.querySelector('#side-bar-mobile-btn').className = 'side-bar-mobile-btn w-[50%] ml-2 h-full flex flex-row justify-start items-center cursor-pointer delay-150'
            $('#sdn-title-h1').removeClass('hidden')
            side_bar_btn_counter = 0;
        }
    })

    
    $('#logout-btn').on('click' , function(event){
        event.preventDefault(); 
        $('#modal-title-main').text('Warning')
        $('#ok-modal-btn-main').text('No')

        $('#yes-modal-btn-main').text('Yes');
        $("#yes-modal-btn-main").css("display", "flex")

    })

    $('#yes-modal-btn-main').on('click' , function(event){
        document.querySelector('#nav-drop-account-div').classList.toggle('hidden');

        let currentDate = new Date();

        let year = currentDate.getFullYear();
        let month = String(currentDate.getMonth() + 1).padStart(2, '0');
        let day = String(currentDate.getDate()).padStart(2, '0');

        let hours = String(currentDate.getHours()).padStart(2, '0');
        let minutes = String(currentDate.getMinutes()).padStart(2, '0');
        let seconds = String(currentDate.getSeconds()).padStart(2, '0');

        let final_date = year + "/" + month + "/" + day + " " + hours + ":" + minutes + ":" + seconds;
        
        // $.ajax({
        //     url: '../SDN/save_process_time.php',
        //     data : {
        //         what: 'save',
        //         date : final_date,
        //         sub_what: 'logout'
        //     },                        
        //     method: "POST",
        //     success: function(response) {
        //         // response = JSON.parse(response);
        //         // window.location.href = "http://192.168.42.222:8035/index.php" 
        //         // window.location.href = "http://10.10.90.14:8079/index.php" 
        //         window.location.href = "https://sdnplus.bataanghmc.net/" 
        //     }
        // });
        
        $.ajax({
            url: '../SDN/logout.php',
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

    $('#ok-modal-btn-main').on('click' , function(event){
    })

    $('#nav-account-div').on('click' , function(event){
        event.preventDefault();
        if($("#nav-drop-account-div").css("display") === "none"){
            $.ajax({
                url: '../SDN/nav_bar_check.php',
                method: "POST",
                success: function(response) {
                    if(parseInt(response) >= 1){
                        traverseModal.show()
                    }else{
                        $("#nav-drop-account-div").css("display", "flex")
                    }
                }
            });
        }else{
            $("#nav-drop-account-div").css("display", "none")
        }
    })

    $('#nav-drop-account-div').on('mouseleave', function() {
        $("#nav-drop-account-div").css("display" , "none")
    });

    //welcome modal
    $('#closeModal').on('click' , function(event){
        $('#myModal').addClass('hidden')
        $('#main-div').css('filter', 'blur(0)');
        $('#modal-div').addClass('hidden')

        document.getElementById("notif-sound").play()
    })

    if(parseInt($('#notif-circle').text()) > 0){
        // document.getElementById("notif-sound").play()

        // setTimeout(function() {
        //     document.getElementById("notif-sound").play()
        // }, 2000); // Delay in milliseconds (2 seconds in this example)
    }

    $('#sdn-title-h1').on('click' , function(event){
        event.preventDefault();
        $.ajax({
            url: '../SDN/nav_bar_check.php',
            method: "POST",
            success: function(response) {
                if(parseInt(response) >= 1){
                    traverseModal.show()
                }else{
                    loadContent('../SDN/default_view2.php')

                }
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

    $('#history-log-btn').on('click' , function(event){
        event.preventDefault();

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
                sub_what: 'history_log'
            },
            method: "POST",
            success: function(response) {
                window.location.href = "../SDN/history_log.php";
            }
        });
    })

    $('#admin-module-btn').on('click' , function(event){
        event.preventDefault();
        // 
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
                sub_what: 'history_log'
            },
            method: "POST",
            success: function(response) {
                window.location.href = "../SDN/admin.php";
            }
        });
    })

    $('#setting-btn').on('click' , function(event){
        window.location.href = "../SDN/setting.php";
    })

    $('#help-btn').on('click' , function(event){
        window.open("../assets/user_guide/hcpn_user_guide.pdf", "_blank");
    })

    $('#credit-btn').on('click' , function(event){
        creditModal.show()
    })

    $('#update-div').on('click' , function(event){
        updateModal.show()
    })

     $('#concern-icon-div').on('click' , function(event){
        concernModal.show()
    })

    $('#setting-mss-btn').on('click' , function(event){
        mssSettingModal.show()
    })

    $('#survey-icon-div').on('click' , function(event){
        surveyModal.show()
    })

    $('#feedback-btn').click(function(event) {
        event.preventDefault();
    })

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

    // mikas
    // MIKAS3255

    $('#outgoing-sub-div-id').on('click' , function(event){
        event.preventDefault();
        $.ajax({
            url: '../SDN/nav_bar_check.php',
            method: "POST",
            success: function(response) {
                if(parseInt(response) >= 1){
                    traverseModal.show()
                }else{
                    loadContent('../SDN/outgoing_form2.php')
                }
            }
        });
    })

    $('#incoming-sub-div-id').on('click' , function(event){
        event.preventDefault();
        loadContent('../SDN/incoming_form2.php')
    })

     $('#census-sub-side-bar').on('click' , function(event){
        event.preventDefault();
        loadContent('../SDN/census.php')
    })

    $('#patient-reg-form-sub-side-bar').on('click' , function(event){
        event.preventDefault();
        $(document).trigger('saveTimeSession');

        $.ajax({
            url: '../SDN/nav_bar_check.php',
            method: "POST",
            success: function(response) {
                if(parseInt(response) >= 1){
                    traverseModal.show()
                }else{
                    loadContent('../SDN/patient_register_form2.php')
                }
            }
        });
    })

    $('#interdept-sub-div-id').on('click' , function(event){
        event.preventDefault();
        // loadContent('../SDN/interdept_form.php')
    })

    $('#bucasPending-sub-div-id').on('click' , function(event){
        event.preventDefault();
        loadContent('../SDN/bucas_queue.php')
    })


    $('#bucasHistory-sub-div-id').on('click' , function(event){
        event.preventDefault();
        loadContent('../SDN/bucas_history.php')
    })
    

    $(window).on('load' , function(event){
        event.preventDefault();
        current_page = "default_page"
        $('#current-page-input').val(current_page)

        
        // loadContent('php/default_view.php')
        // loadContent('php/patient_register_form.php')
        // loadContent('php/opd_referral_form.php')
    })

    $(window).on('beforeunload', function() {
        localStorage.setItem('scrollPosition', $(window).scrollTop());
    });

    $('#navbar-icon').on('click' , function(event){
        let side_bar_div_width;

        if(screenHeight > 800){
            side_bar_div_width = 250;
        }else{
            side_bar_div_width = 200;
        }

        let width = $("#side-bar-div").width()

        if(!mobile_responsive){
            if(width === 0){
                $('#side-bar-div').css("width", "250px")
                $('#main-side-bar-1-subdiv').css("display", "flex")
                $('#main-side-bar-2-subdiv').css("display", "flex")

                // $('#main-div .aside-main-div #container').css("width", "87%")

                $('.side-bar-navs-class').css('display' , 'flex')
                $('#bgh-name').css('display' , 'block')
                
                $('#license-div').css('width' , '87%')

            }else{
                $('#side-bar-div').css("width", "0")

                $('#main-side-bar-1-subdiv').css("display", "none")
                $('#main-side-bar-2-subdiv').css("display", "none")

                $('#main-div .aside-main-div #container').css("width", "100%")

                $('.side-bar-navs-class').css('display' , 'none')
                $('#bgh-name').css('display' , 'none')
                

                $('#license-div').css('width' , '100%')
            }
        }else{
            if(document.querySelector('#main-div .aside-main-div #side-bar-div').style.display =="none"){
                document.querySelector('#main-div .aside-main-div #side-bar-div').style.display = "flex"
            }else{
                document.querySelector('#main-div .aside-main-div #side-bar-div').style.display = "none"

            }
        }

        // let  width = ((($("#side-bar-div").width() / $("#side-bar-div").parent().width()) * 100).toFixed(1)) + "%";

        // if(width === "13.5%"){
        //     $('#side-bar-div').css("width", "0")
        //     $('#bgh-name').text('')
        //     $('#main-side-bar-1-subdiv').css("display", "none")
        //     $('#main-side-bar-2-subdiv').css("display", "none")

        //     $('#main-div .aside-main-div #container').css("width", "100%")
        // }else{
        //     $('#side-bar-div').css("width", "13.5%")
        //     $('#bgh-name').text('Bataan General Hospital and Medical Center')
        //     $('#main-side-bar-1-subdiv').css("display", "flex")
        //     $('#main-side-bar-2-subdiv').css("display", "flex")

        //     $('#main-div .aside-main-div #container').css("width", "86.5%")
        // }
    })

    carousel.addEventListener('slide.bs.carousel', function(event) {
        if (event.to === 2) {
            $('#pat-mod').css('background' , '#6c757d')
            $('#ref-mod').css('background' , '#0d6efd')

            $('#pat-mod').css('opacity' , '0.3')
            $('#ref-mod').css('opacity' , '1')
        }else if(event.to === 0){
            $('#pat-mod').css('background' , '#0d6efd')
            $('#ref-mod').css('background' , '#6c757d')

            $('#pat-mod').css('opacity' , '1')
            $('#ref-mod').css('opacity' , '0.3')
        }
    });

    $('#reauth-submit').on('click', function() {
        const password = $('#reauth-password').val();
        $.post('../reauth.php', { password }, function(response) {
            console.log(response);
            if (response.status === 'success') {
                idleTime = 0;
                modalShown = false; // allow modal to show again next time
                location.reload();
            } else {
                alert('Invalid password');
            }
        }, 'json');
    });
})

/*
    incoming_referrals: referral_id = REF000010 / reference_num = R3-BTN-BALANGA-BGHMC-2024-06-11 / hpercode = PAT000012
    bucas_referral: bucasID = BUCAS-20240307-00034 / caseNo = 2024-000002
*/
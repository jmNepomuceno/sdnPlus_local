$(document).ready(function() {
    // me code
    for(let i = 0; i < $('.side-bar-navs-class').length; i++){
        $('.side-bar-navs-class').css('opacity' , '0.3')
        $('.side-bar-navs-class').css('border-top' , 'none')
        $('.side-bar-navs-class').css('border-bottom' , 'none')
    }

    $('#bucasPending-sub-div-id').css('opacity' , '1')
    $('#bucasPending-sub-div-id').css('border-top' , '2px solid #3e515b')
    $('#bucasPending-sub-div-id').css('border-bottom' , '2px solid #3e515b')

    $('.view-link').click(function() {
        var bucasID = $(this).data('bucas-id');
        var formData = { bucasID_parameter: bucasID };
        
        $.ajax({
            type: 'POST',
            url: '../SDN/bucas_referral.php',
            data: formData,
            success: function(response) {
                $('.modal-body-bucas').html(response); 
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    // $('#searchBtn').click(function() {
    //     $('#bucasBackdrop').modal('hide');
    // });
    
});

document.getElementById('submit-referral-btn').addEventListener("click", function() {
    var sdnPatientID = document.getElementById("sdnBucasID").value;
    var sdnCaseNo = document.getElementById("sdnCaseNo").value;
    var sdnStatusInput = document.getElementById("sdnStatus").value;
    var sdnProcessDT = document.getElementById("sdnProcessDT").value;
    var statusDefer = document.getElementById("statusDefer").value;    
    var sdnUserLog = document.getElementById("sdnUserID").value;

    if (sdnStatusInput.trim() === "") {
        alert("Please enter a value for the Response Status.");
        return;
    }

    var formData = {
        sdnPatientID: sdnPatientID,
        sdnCaseNo: sdnCaseNo,
        sdnStatusInput: sdnStatusInput,
        sdnProcessDT: sdnProcessDT,
        statusDefer: statusDefer,
        sdnUserLog: sdnUserLog
    }

    // sdnCaseNo: "2024-000002"
    // sdnPatientID: "BUCAS-20240307-00034"
    // sdnProcessDT: "01/23/2025 11:32 AM"
    // sdnStatusInput: "accepted"
    // sdnUserLog: "admin"
    // statusDefer: ""

    async function postData() {
        try {
            let response = await new Promise((resolve, reject) => {
                $.ajax({
                    url: '../SDN/fetch_lastHpercode.php',
                    type: 'POST',
                    dataType : 'JSON',
                    success: (data) => resolve(data),
                    error: (xhr, status, error) => reject(error),
                });
            });
            // console.log(response)
            formData['hpercode'] = "PAT000053" //response[0].hpercode
            formData['approve_details'] = "Thank you for your referral."
            formData['case_category'] = "Tertiary"
            formData['processed_by'] = response[1] // doctor full name
            formData['timer'] = "00:00:00"
            formData['type_approval'] = true
        } catch (error) {
            console.error('Error posting data:', error);
        }
    }

    postData(); // Call the async function

    // console.log(formData)

    // $.ajax({
    //     type: 'POST',
    //     url: '../SDN/referral_response.php',
    //     data: formData,
    //     success: function (response) {
    //         var _response = JSON.parse(response);
    //         if (_response.success == true) {
    //             alert(_response.message);
    //             // close the modal progmatically
    //             document.getElementById('bucasBackdrop').style.display = "none";
    //             var modalBackdrops = document.querySelectorAll('.modal-backdrop');
    //             modalBackdrops.forEach(function(backdrop) {
    //                 backdrop.remove();
    //             });
    //             document.body.classList.remove('modal-open');
    //             window.location.reload();
    //         } else {
    //             alert(_response.message);
    //         }
    //     },
    //     error: function(xhr, status, error) {
    //         console.error('Error inserting data:', error);
    //     }
    // });

    $.ajax({
        url: '../SDN/bucas_approved.php',
        method: "POST",   
        data : formData,
        dataType:'JSON',
        success: function(response){
            // console.log(response)
        },
        error: function(xhr, status, error) {
            console.error('Error inserting data:', error);
        }
    })



}); 

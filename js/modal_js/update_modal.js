$(document).ready(function(){
    $('#feedback-btn').off('click', '#feedback-btn').on('click' , function(event){  
        event.preventDefault();

        // feedback-textarea
        $.ajax({
            type: 'POST',
            url: '../../SDN/insert_feedback.php',
            data: {
                feedback: $('#feedback-textarea').val()
            },
            success: function(response) {
                console.log(response)
                // Handle success response
                $('#feedback-textarea').val(''); // Clear the textarea
                $('#feedback-btn').text('Feedback Sent!').prop('disabled', true);
                $('#feedback-btn').removeClass('btn-primary').addClass('btn-success');

                setTimeout(function() {
                    $('#feedback-btn').text('Submit Concern').prop('disabled', false);
                    $('#feedback-btn').removeClass('btn-success').addClass('btn-primary');
                }, 3000); // Reset button text after 3 seconds

                document.getElementById('pending-concerns-list').innerHTML = ""
                document.getElementById('pending-concerns-list').innerHTML = response
                                document.getElementById('pending-concerns-list').innerHTML += `
                        <li> Easier viewing and copying of the Referral Form</li>
                        <li> Unstable or delayed updates for new incoming referrals</li>
                        <li> Ability to cancel referrals even after they have been approved</li>`;
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error('Error sending feedback:', error);

                $('#feedback-btn').text('Error! Try Again').prop('disabled', true);
                setTimeout(function() {
                    $('#feedback-btn').text('Submit Concern').prop('disabled', false);
                    $('#feedback-btn').removeClass('btn-success').addClass('btn-primary');

                }, 3000); // Reset button text after 3 seconds
            }
        });
    })

})
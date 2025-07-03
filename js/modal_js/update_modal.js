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
                // Handle success response
                $('#feedback-textarea').val(''); // Clear the textarea
                $('#feedback-btn').text('Feedback Sent!').prop('disabled', true);
                $('#feedback-btn').removeClass('btn-primary').addClass('btn-success');

                setTimeout(function() {
                    $('#feedback-btn').text('Submit Concern').prop('disabled', false);
                    $('#feedback-btn').removeClass('btn-success').addClass('btn-primary');
                }, 3000); // Reset button text after 3 seconds
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
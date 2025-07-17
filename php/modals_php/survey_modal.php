<div class="modal fade" id="surveyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modal-title" class="modal-title" id="exampleModalLabel">📢 Announcement & Survey</h5>
                <button type="button" data-bs-dismiss="modal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div id="modal-body" class="modal-body">
                <h4 id="survey-title">Session Time Lock Out</h4>

                <p id="survey-purpose">
                    <strong>Purpose:</strong>  
                    To prevent problems that happen when the system is left open and unused for too long, which can sometimes cause errors or data loss.
                </p>

                <p id="survey-details">
                    This update introduces a timer that keeps track of whether you’re actively using the system.  
                    <br><br>
                    If you don’t touch your mouse or keyboard for a certain amount of time, the system will temporarily lock and ask you to type your password again before continuing.  
                    <br><br>
                    <strong>Important:</strong> As long as you keep moving your mouse, clicking, or typing, the timer resets — so you won’t be locked while you’re working.  
                    <br><br>
                    <p id="survey-affected">
                        <strong>Who will be affected?</strong><br>
                        All users who leave the system inactive for longer than the allowed time — currently proposed as <strong>4 hours for RHU users</strong> and <strong>2 hours for Doctor Admins</strong>.<br>
                        <em>Please note: These time limits are still subject to change, depending on the feedback and final approval of the SDN doctors.</em>
                    </p>
                    <br><br>
                    We value everyone’s experience and opinion — so before we fully implement this feature, we’d like to know:  
                    👉 Do you think this update is helpful and worth being implemented?  
                    👉 Please let us know your feedback by clicking one of the response buttons below.
                </p>

                <p id="survey-goal">
                    <strong>Goal:</strong>  
                    To keep your account secure and reduce system errors from inactive sessions — while still letting you continue your work smoothly if you return shortly.
                </p>


                <hr>

                <div class="text-center">
                    <p>Do you agree with this update?</p>
                    <button class="btn btn-success me-2 survey-response-btn" data-response="approve">
                        👍 Approve (<span id="approve-count">0</span>)
                    </button>
                    <button class="btn btn-danger survey-response-btn" data-response="disapprove">
                        👎 Disapprove (<span id="disapprove-count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
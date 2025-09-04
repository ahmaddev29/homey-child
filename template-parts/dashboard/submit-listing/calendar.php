<?php
global $homey_local;
?>
<div class="form-step" data-step="calendar">
    <!--step information-->
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h3 class="title">Calendar <span style="color:#c31b1b; font-size:14px">(This Calendar manages this specific listing only.)</span></h3>
            </div><!-- block-left -->
        </div>
        <div class="block-body">
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <div class="show-calendar">
                        <div id="calendar"></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <div class="show-calendar-data">
                        <div id="repeater-container">
                            <!-- Instances will be added here -->
                        </div>
                        <button type="button" id="add-new-instance" class="btn btn-primary">Add New</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="block">
        <div class="block-title">
            <div class="block-left">
                <h2 class="title">FAQs for Hosts: Managing Your Booking Calendar</h2>
            </div>
        </div>
        <div class="block-body">
            <div class="panel-group featured-faq featured-flex" id="accordion">
                <div class="first-acc-column">
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse1">
                            <strong class="panel-title">
                                How does the calendar work by default?
                                <span class="fa-solid fa-arrow-down"></span>
                            </strong>
                        </div>
                        <div id="collapse1" class="panel-collapse collapse in">
                            <div class="panel-body">By default, every date and time slot for all your services (Amenity, Sleeping Accommodation, and Guided Service) is set to Available. The calendar will only show blocked time slots or preparation times once you set them.</div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse2">
                            <strong class="panel-title">
                                How do I block a specific date or time?
                                <span class="fa-solid fa-arrow-down"></span>
                            </strong>
                        </div>
                        <div id="collapse2" class="panel-collapse collapse">
                            <div class="panel-body">
                                <ul>
                                    <li> Navigate to the date on the calendar. </li>

                                    <li>Click on the time slot you want to block.</li>

                                    <li>It will appear in selected time slots section.</li>

                                    <li>Select "Blocked" from the options for Amenity, Sleeping Accommodation or Guided Service.</li>

                                    <li>The calendar will update in real-time, and that slot will now be marked as blocked and unavailable for guests to book.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse3">
                            <strong class="panel-title">
                                How do I make a previously blocked slot available again?
                                <span class="fa-solid fa-arrow-down"></span>
                            </strong>
                        </div>
                        <div id="collapse3" class="panel-collapse collapse">
                            <div class="panel-body">Simply click on the blocked time slot, click "Change", and then select "Available". The slot will instantly revert to being open for bookings.</div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse4">
                                <strong class="panel-title">
                                    What is "Preparation Time" and how do I set it?
                                    <span class="fa-solid fa-arrow-down"></span>
                                </strong>
                            </div>
                            <div id="collapse4" class="panel-collapse collapse">
                                <div class="panel-body">Preparation time is a buffer period you need between bookings (e.g., to clean an amenity or reset a room). This field (How much time do you need in between bookings?) is used to set the preperation time during listing process.</div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="first-acc-column">
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse5">
                            <strong class="panel-title">
                                A guest just made a booking. What happens to my calendar?
                                <span class="fa-solid fa-arrow-down"></span>
                            </strong>
                        </div>
                        <div id="collapse5" class="panel-collapse collapse">
                            <div class="panel-body">Your calendar updates in real-time and shows the status of booking.</div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse6">
                            <strong class="panel-title">
                                How can I create different availability settings for different times on the same day? (e.g., available in the morning, blocked in the evening)?
                                <span class="fa-solid fa-arrow-down"></span>
                            </strong>
                        </div>
                        <div id="collapse6" class="panel-collapse collapse">
                            <div class="panel-body">
                                This is where the <b>"Add New"</b> button is used.
                                <ul>
                                    <li>Click on the first time slot you want to configure (e.g., 9:00 AM - 12:00 PM).</li>

                                    <li>Set its status to Available or Blocked.</li>

                                    <li>Click the "Add New" button. This allows you to define a completely separate set of times without affecting your first selection.</li>

                                    <li>Click on a new time slot (e.g., 1:00 PM - 5:00 PM) and set its status.</li>

                                    <li>You can repeat this to create multiple, custom time blocks on a single day.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#collapse7">
                            <strong class="panel-title">
                                Can I set a different status for each of my services (Amenity, Sleeping, Guided) on the same date?
                                <span class="fa-solid fa-arrow-down"></span>
                            </strong>
                        </div>
                        <div id="collapse7" class="panel-collapse collapse">
                            <div class="panel-body">Yes, absolutely. The status for each service is independent. You could have your Amenity available, your Sleeping Accommodation blocked, and your Guided Service available all on the same day and time.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .show-calendar-data {
        padding: 50px 0px 50px 10px;
    }

    .show-calendar-data .active-instance {
        border: 1px solid #D1954C;
        ;
        padding: 20px;
        border-radius: 20px;
    }

    .show-calendar-data h3 {
        margin: 0 0 10px;
    }

    .show-calendar-data .btn-outline {
        background: transparent;
        border-radius: 10px !important;
        border: 1px solid #262626;
        display: block;
    }

    .show-calendar-data .btn-outline:hover {
        background: #D1954C;
        border-radius: 10px !important;
        border: 1px solid #D1954C;
        color: #fff;
    }

    .time-slots-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .date-time-slots {
        margin-bottom: 10px;
    }

    .date-container,
    .time-slot {
        display: flex;
        align-items: center;
        flex-direction: column;
        align-content: center;
        justify-content: center;
        padding: 7px 20px 7px 20px;
        background: #f2f2f2;
        border-radius: 5px;
        flex: 1;
    }

    .month {
        text-transform: uppercase;
        font-size: 12px;
        font-weight: 500;
    }

    .day {
        font-size: 18px;
        font-weight: bold;
    }

    .clear-dates {
        display: none;
        cursor: pointer;
        color: #262626;
        font-size: 12px;
    }

    .clear-dates-text {
        text-decoration: underline;
    }

    .clear-dates-icon {
        margin-right: 5px;
    }

    .cal-options {
        display: flex;
        align-items: center;
    }

    .amenity-availability-options,
    .sleeping-availability-options,
    .gservice-availability-options {
        margin-top: 10px;
        display: none;
    }

    .amenity-availability-options input[type="radio"],
    .sleeping-availability-options input[type="radio"],
    .gservice-availability-options input[type="radio"] {
        margin: 0px;
        width: 30px;
    }

    .delete-instance {
        margin-top: 20px;
    }

    #add-new-instance {
        float: right;
    }

    .show-calendar-data .instance {
        padding-bottom: 20px;
        margin-bottom: 15px;
    }

    .events-list {
        margin-top: 10px;
    }

    .event-item {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 10px;
        background-color: #f9f9f9;
    }

    .event-item strong {
        color: #333;
    }

    .fc-event-time {
        display: none;
    }
</style>
<?php

/**
 * Template Name: Featured Listing Calendar
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
}

get_header();

$listing_id = isset($_GET['listing_id']) ? $_GET['listing_id'] : null;
$homey_featured_time = get_post_meta($listing_id, 'homey_featured_time', true);
$homey_queued_featured_time = get_post_meta($listing_id, 'homey_queued_featured_time', true);
$start_time = 0;
if (isset($homey_featured_time)) {
    $start_time = $homey_featured_time;
} else {
    $start_time = $homey_queued_featured_time;
}

$homey_featured_expiry = get_post_meta($listing_id, 'homey_featured_expiry', true);
$homey_featured_queued_expiry = get_post_meta($listing_id, 'homey_featured_queued_expiry', true);
$end_time = 0;
if (isset($homey_featured_expiry)) {
    $end_time = $homey_featured_expiry;
} else {
    $end_time = $homey_featured_queued_expiry;
}
?>


<section id="body-area">

    <div class="dashboard-page-title">
        <h1><?php echo esc_html__(the_title('', '', false), 'homey'); ?></h1>
    </div><!-- .dashboard-page-title -->

    <?php get_template_part('template-parts/dashboard/side-menu'); ?>

    <div class="user-dashboard-right dashboard-without-sidebar">
        <div class="dashboard-content-area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="dashboard-area">
                            <div class="block">
                                <div id="featured-calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('featured-calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: [{
                //title: 'Featured Listing',
                start: '<?php echo date('Y-m-d', $start_time); ?>',
                end: '<?php echo date('Y-m-d', $end_time); ?>'
            }],
            eventDidMount: function(info) {
                // Add custom class to highlight the event cells
                var firstDay = info.event.start;
                var lastDay = info.event.end;

                // Find all the cells between the start and end dates
                var allDayCells = document.querySelectorAll('.fc-daygrid-day');

                allDayCells.forEach(function(cell) {
                    var cellDate = new Date(cell.getAttribute('data-date'));
                    var cellDateOnly = new Date(cellDate.getFullYear(), cellDate.getMonth(), cellDate.getDate());
                    var firstDayOnly = new Date(firstDay.getFullYear(), firstDay.getMonth(), firstDay.getDate());
                    var lastDayOnly = new Date(lastDay.getFullYear(), lastDay.getMonth(), lastDay.getDate());

                    if (cellDateOnly >= firstDayOnly && cellDateOnly <= lastDayOnly) {
                        cell.classList.add('highlight-cell');

                        // Check if the elements already exist before appending
                        if (!cell.querySelector('.highlight-content')) {
                            var innerDivHighlight = document.createElement('div');
                            innerDivHighlight.classList.add('highlight-content');
                            cell.appendChild(innerDivHighlight);
                        }
                    }

                    if (cellDateOnly.getTime() === firstDayOnly.getTime()) {
                        if (!cell.querySelector('.additional-content')) {
                            var innerDivStart = document.createElement('div');
                            innerDivStart.classList.add('additional-content');
                            innerDivStart.textContent = 'Start';
                            cell.appendChild(innerDivStart);
                        }
                    }

                    if (cellDateOnly.getTime() === lastDayOnly.getTime()) {
                        if (!cell.querySelector('.additional-content')) {
                            var innerDivEnd = document.createElement('div');
                            innerDivEnd.classList.add('additional-content');
                            innerDivEnd.textContent = 'End';
                            cell.appendChild(innerDivEnd);
                        }
                    }
                });
            },
            datesSet: function() {
                var allDayCells = document.querySelectorAll('.fc-daygrid-day');
                allDayCells.forEach(function(cell) {
                    if (cell.querySelector('.highlight-content')) {
                        cell.classList.add('highlight-cell');
                    } else {
                        cell.classList.remove('highlight-cell');
                    }
                });
            }
        });

        calendar.render();
    });
</script>

<?php get_footer(); ?>
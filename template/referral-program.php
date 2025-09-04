<?php

/**
 * Template Name: Referral Program
 */
if (!is_user_logged_in() || homey_is_renter()) {
    wp_redirect(home_url('/'));
}

get_header();


$current_user_id = get_current_user_id();
$total_referral_credits = get_user_meta($current_user_id, 'referral_credit', true);
$used_referral_credits = get_user_meta($current_user_id, 'used_referral_credit', true);
$credit_amount = get_option('referral_credit_amount', 50);

// Count the number of users registered using the host's referral link
$args = array(
    'meta_query' => array(
        array(
            'key' => 'referrer_user_id',
            'value' => $current_user_id,
            'compare' => '='
        )
    )
);

$user_query = new WP_User_Query($args);
$referred_users_count = $user_query->get_total();

// Display 0 if no users are registered yet
if ($referred_users_count === 0) {
    $referred_users_count = 0;
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
                            <div class="wallet-box-wrap">
                                <div class="row">
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="wallet-box">
                                            <p class="block-big-text"><?php echo esc_html($referred_users_count); ?></p>
                                            <h3>Total Referred Users</h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="wallet-box">
                                            <p class="block-big-text">
                                                $<?php echo esc_html($total_referral_credits ? $total_referral_credits : 0); ?>
                                            </p>
                                            <h3>Total Referral Credit</h3>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="wallet-box">
                                            <p class="block-big-text">
                                                $<?php echo esc_html($used_referral_credits ? $used_referral_credits : 0); ?>
                                            </p>
                                            <h3>Used Referral Credit</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">All Referrals</h2>
                                    </div>
                                </div>
                                <?php echo do_shortcode('[referrals_table]'); ?>
                            </div>

                            <div class="block make-simple">
                                <div class="title">Making it Simple</div>
                                <div class="steps">
                                    <div class="step">
                                        <div class="step-number">1</div>
                                        <div class="step-title">Refer a New Host</div>
                                        <div class="step-description">Send your referral link to a potential host. They can use it to create their listing.</div>
                                    </div>
                                    <div class="step">
                                        <div class="step-number">2</div>
                                        <div class="step-title">Check for Updates</div>
                                        <div class="step-description">You will be notified via email about the progress of your refereed Host.</div>
                                    </div>
                                    <div class="step">
                                        <div class="step-number">3</div>
                                        <div class="step-title">Get Paid</div>
                                        <div class="step-description">Earn your $50 credit after the requirements of a being new referred host are met.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="block referral-criteria">
                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <div class="title">Criteria for Becoming a New Host</br>through our Referral Program</div>
                                        <div class="steps">
                                            <div class="step">
                                                <div class="step-number">1</div>
                                                <div class="step-content">
                                                    <div class="step-title">Sign Up</div>
                                                    <div class="step-description">The new host must use an active referral program link/code when signing up.</div>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number">2</div>
                                                <div class="step-content">
                                                    <div class="step-title">Get Verified</div>
                                                    <div class="step-description">The new host must become a verified host. Requirements include: Complete the sign up process, verified email, activate their stripe account, and list their Backyard Amenity.</div>
                                                </div>
                                            </div>
                                            <div class="step">
                                                <div class="step-number">3</div>
                                                <div class="step-content">
                                                    <div class="step-title">Stay Active</div>
                                                    <div class="step-description">The new host must have an active account along with an active listing for 30 days in order to receive your $50 incentive.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-12">
                                        <img fetchpriority="high" decoding="async" width="500" height="554" src="https://backyardlease.flywheelsites.com/wp-content/uploads/2024/08/Group-26021.png" class="attachment-full size-full wp-image-6950" alt="" srcset="https://backyardlease.flywheelsites.com/wp-content/uploads/2024/08/Group-26021.png 500w, https://backyardlease.flywheelsites.com/wp-content/uploads/2024/08/Group-26021-271x300.png 271w, https://backyardlease.flywheelsites.com/wp-content/uploads/2024/08/Group-26021-451x500.png 451w" sizes="(max-width: 500px) 100vw, 500px">
                                    </div>
                                </div>
                            </div>

                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">Earn $<?php echo $credit_amount ?> for every new Host you
                                            refer.</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <strong>Invite someone with an incredible backyard experience and earn
                                        $<?php echo $credit_amount ?> when they list their amenity and are a verified
                                        host.</strong></br></br>
                                    <?php echo do_shortcode('[referral_code]'); ?>
                                </div>
                            </div>

                            <div class="block">
                                <div class="block-title">
                                    <div class="block-left">
                                        <h2 class="title">FAQs</h2>
                                    </div>
                                </div>
                                <div class="block-body">
                                    <div class="panel-group featured-faq featured-flex" id="accordion">
                                        <div class="first-acc-column">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse1">
                                                    <strong class="panel-title">
                                                        How do I get paid?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse1" class="panel-collapse collapse in">
                                                    <div class="panel-body">The referral program gives, you, as the
                                                        referring host $<?php echo $credit_amount ?> that will be credited
                                                        to your account. Once the referred host has signed up, is verified,
                                                        and has an active account for at least 30 days, you will see
                                                        $<?php echo $credit_amount ?> added to your account and those funds
                                                        are deducted from booking fees. Instead of paying Backyard Lease its
                                                        service fee, you get to keep it!</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse2">
                                                    <strong class="panel-title">
                                                        How many referrals can I have?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse2" class="panel-collapse collapse">
                                                    <div class="panel-body">Unlimited. The more hosts you refer the more
                                                        money you can make!</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse3">
                                                    <strong class="panel-title">
                                                        Who can I refer?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse3" class="panel-collapse collapse">
                                                    <div class="panel-body">Backyard Lease is just about for any homeowner
                                                        with a backyard experience who is willing to share and make money
                                                        hosting their backyard adventure! Just remember, experiences can
                                                        range from pools to basketball courts, fishing ponds to lakeside
                                                        water sports, photography to open land, and so much more.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse10">
                                                    <strong class="panel-title">
                                                        How can I share referral link?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse10" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        You can share your referral link on various social media platforms
                                                        by clicking the provided social media icons. Alternatively, you can
                                                        email the link to a host by clicking the email icon. You can also
                                                        copy your referral link and send it directly to any host.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse11">
                                                    <strong class="panel-title">
                                                        What the referral link will do?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse11" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        The referral link directs the referred user to the host signup page.
                                                        If the user registers as a host, the link will be used to track the
                                                        signup event and track the referral host.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="first-acc-column">
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse4">
                                                    <strong class="panel-title">
                                                        What host is not eligible?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse4" class="panel-collapse collapse">
                                                    <div class="panel-body">Includes, but not limited to. Tenants/renters
                                                        who do not have consent from the homeowner to host. Anyone who has
                                                        previously received an invitation or has already published a
                                                        listing. People who live in certain ineligible locations.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse5">
                                                    <strong class="panel-title">
                                                        When does the referral expire?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse5" class="panel-collapse collapse">
                                                    <div class="panel-body">As long as the referred host does not sign up
                                                        after the invitation was sent without using the link/code or was
                                                        previously signed up prior to receiving the invitation the referral
                                                        link is still active and ready for use. However, if you decide to
                                                        delete your account you will be given a new referral link or code.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse6">
                                                    <strong class="panel-title">
                                                        Does the credit expire?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse6" class="panel-collapse collapse">
                                                    <div class="panel-body">No. The credit does not expire and you can track
                                                        it on your referral program page. However, if you decide to delete
                                                        your account and then reactivate your account at a later time the
                                                        credit will no longer be available.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse7">
                                                    <strong class="panel-title">
                                                        When is the credit deducted?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse7" class="panel-collapse collapse">
                                                    <div class="panel-body">Funds are deducted from booking fees that
                                                        Backyard Lease would receive once a guest has booked your listing.
                                                        So, instead of Backyard Lease getting paid, you do!</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse8">
                                                    <strong class="panel-title">
                                                        How do I track and manage my referrals?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse8" class="panel-collapse collapse">
                                                    <div class="panel-body">You can track your referrals on the Referral
                                                        Program page.</div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading" data-toggle="collapse" data-parent="#accordion"
                                                    data-target="#collapse9">
                                                    <strong class="panel-title">
                                                        How will I know if my referral worked?
                                                        <span class="fa-duotone fa-solid fa-arrow-down"></span>
                                                    </strong>
                                                </div>
                                                <div id="collapse9" class="panel-collapse collapse">
                                                    <div class="panel-body">You will be updated via email when a new host
                                                        has signed up through your link. You will also be notified via email
                                                        after the host has meet their verified 30 day requirement for being
                                                        a host and you are entitled to your $<?php echo $credit_amount ?>
                                                        credit.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<style>
    .make-simple,
    .referral-criteria {
        padding: 40px;
    }

    .make-simple .title {
        font-size: 2em;
        margin-bottom: 40px;
        font-weight: bold;
        font-family: din-2014-narrow, sans-serif;
        text-align: center;
    }

    .referral-criteria .title {
        font-size: 2em;
        margin-bottom: 40px;
        font-weight: bold;
        font-family: din-2014-narrow, sans-serif;
    }

    .make-simple .steps {
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
    }

    .referral-criteria .steps {
        padding-right: 50px;
    }

    .make-simple .step {
        background-color: #F7F8F9;
        border-radius: 10px;
        padding: 30px 20px 20px 20px;
        text-align: center;
        flex: 1;
    }

    .referral-criteria .step {
        display: flex;
        align-items: start;
        gap: 20px;
        margin-bottom: 30px;
    }

    .make-simple .step-number {
        font-size: 40px;
        color: #D1954C;
        margin-bottom: 20px;
        font-weight: bold;
        font-family: din-2014-narrow, sans-serif !important;
    }

    .referral-criteria .step-number {
        font-size: 40px;
        color: #D1954C;
        font-weight: bold;
        font-family: din-2014-narrow, sans-serif !important;
        position: relative;
        top: 6px;
    }

    .make-simple .step-title {
        font-size: 22px;
        color: #333;
        margin-bottom: 10px;
        font-family: din-2014-narrow, sans-serif !important;
        font-weight: bold;
    }

    .referral-criteria .step-title {
        font-size: 22px;
        color: #333;
        font-family: din-2014-narrow, sans-serif !important;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .make-simple .step-description {
        font-size: 18px;
        font-family: din-2014-narrow, sans-serif !important;
        font-weight: 300;
    }

    .referral-criteria .step-description {
        font-size: 18px;
        font-family: din-2014-narrow, sans-serif !important;
        font-weight: 300;
    }

    .referral-criteria img {
        height: 370px;
        object-fit: cover;
        border-radius: 20px;
    }

    .completion-steps {
        list-style: none;
        padding: 0;
    }

    .completion-steps li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .completion-steps .step-icon {
        width: 24px;
        height: 22px;
    }

    .completion-steps .step-description {
        flex-grow: 1;
        padding: 0 10px;
        margin: 0;
    }
</style>

<?php get_footer(); ?>
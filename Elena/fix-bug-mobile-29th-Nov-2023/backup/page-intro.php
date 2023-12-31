<?php
/*
 * Template Name: Intro Page mt
 */ 
?>
<!DOCTYPE html>

<html class="no-js"> <!--<![endif]-->

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
					 More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">
    <!-- 	<meta name="viewport" content="width=device-width, initial-scale=1"  /> -->
    <meta name="description" content="<?php echo get_bloginfo('description') ?>" />
    <meta name="keywords" content="Job, Jobs, company, employer, employee" />
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <title><?php
            /*
			 * Print the <title> tag based on what is being viewed.
			 */
            global $page, $paged, $current_user, $user_ID;

            wp_title('|', true, 'right');

            // Add the blog description for the home/front page.
            $site_description = get_bloginfo('description', 'display');

            ?></title>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <?php
    $general_opts    = new ET_GeneralOptions();
    $favicon    = $general_opts->get_favicon();
    $website_logo    = $general_opts->get_website_logo();
    if ($favicon) {
    ?>
        <link rel="shortcut icon" href="<?php echo $favicon[0]; ?>" />
    <?php } ?>
    <!-- enqueue json library for ie 7 or below -->
    <?php wp_head(); ?>
</head>
<?php
if (!is_user_logged_in()) {
    $job_link   =  et_get_page_link('post-a-job');
    $job_title  =  __("POST A JOB", ET_DOMAIN);

    $resume_link  = et_get_page_link('jobseeker-signup');
    $resume_title = __("CREATE A RESUME", ET_DOMAIN);
} else {
    global $current_user;
    $roles  =   $current_user->roles;
    $role   =   array_pop($roles);
    if ($role != 'jobseeker') {

        $job_link   =  et_get_page_link('post-a-job');
        $job_title  =  __("POST A JOB", ET_DOMAIN);

        $resume_link  = get_post_type_archive_link('resume');
        $resume_title = __("SEARCH A RESUME", ET_DOMAIN);
    } else {

        $job_link   =  get_post_type_archive_link('job');
        $job_title  =  __("SEARCH A JOB", ET_DOMAIN);

        $resume_link  = et_get_page_link('jobseeker-signup');
        $resume_title = __("REVIEW YOUR RESUME", ET_DOMAIN);
    }
} ?>

<body data-page="main" <?php body_class(); ?>>
    <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->
    <!-- Preloading -->
    <div class="mask-color">
        <div id="preview-area">
            <div class="logo-image-preloading"><img src="<?php echo TEMPLATEURL; ?>/img/preloading-logo.png" alt="EngineTheme"></div>
            <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
        </div>
        <div class="page-main"></div>
        <div class="page-left"></div>
        <div class="page-right"></div>
    </div>
    <!-- Preloading / End -->
    <!-- Header -->
    <header data-size="small">
        <div class="container">
            <div class="logo">
                <a href="<?php echo home_url() ?>" class="logo">
                    <img src="<?php echo $website_logo[0]; ?>" alt="<?php echo $general_opts->get_site_title();  ?>" />
                </a>
            </div>

            <div class="btn-group">
                <a href="<?php echo $job_link ?>" class="btn btn-red"><?php echo $job_title; ?></a>
                <?php if (function_exists('et_is_resume_menu')) { ?>
                    <span class="or">or</span>
                    <a href="<?php echo $resume_link; ?>" class="btn btn-blue"><?php echo $resume_title; ?></a>
                <?php } ?>
            </div>
        </div>
    </header>
    <!-- Header / End -->
    <!-- Slider -->
    <div class="slider-wrapper2" style="background-color:#0d321a;">
        <div class="container" style="height:80px;background-color:#0d321a;">
            <div class="clearfix" style="height:30px;"></div>
            <div class="logo">
                <a href="<?php echo home_url() ?>" class="logo">
                    <img src="<?php echo $website_logo[0]; ?>" alt="<?php echo $general_opts->get_site_title();  ?>" />
                </a>
            </div>
            <nav class="menu">
                <?php
                je_header_menu();
                ?>
            </nav>


        </div>
    </div>
    <!-- Slider / End -->
    <!-- Page content -->
    <?php
    if (have_posts()) {
        the_post();
        global $post;
        if ($post->post_content != '') {
            the_content();
        } else {
    ?>
            <div class="wrapper">
                <div class="container">
                    <div class="wrapper-timeline">
                        <h2 class="title-top">Bussiness Feature</h2>
                        <div class="box-wrapper">
                            <span class="line"></span>
                            <div class="content">
                                <span class="icon " data-icon="O">
                                    <h2 class="title">
                                </span>Job Management</h2>
                                <p>Sort your listings by location,
                                    job type and category.</p>
                            </div>
                        </div>
                        <div class="box-wrapper">
                            <span class="line"></span>
                            <div class="content">
                                <span class="icon " data-icon="e"></span>
                                <h2 class="title">Ajax Job Filters</h2>
                                <p>Refine your search criteria without reloading the page.</p>
                            </div>
                        </div>
                        <div class="box-wrapper">
                            <span class="line"></span>
                            <div class="content">
                                <span class="icon " data-icon="'"></span>
                                <h2 class="title"> Apply Online</h2>
                                <p>Upload a CV and send applications directly from the site.</p>
                            </div>
                        </div>
                        <div class="box-wrapper">
                            <span class="line"></span>
                            <div class="content">
                                <span class="icon" data-icon="M">
                                    <h2 class="title">Email Templates</h2>
                                    <p>Send automated messages to advertisers and jobseekers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        }
    }
    ?>

    <!-- Page content / End -->

    <script>
        jQuery(document).ready(function($) {
            $('.mask-color').fadeOut(1700);
            var height = $('.slider-wrapper').height();
            $(window).scroll(function() {
                var st = $(this).scrollTop();
                if (st > height) {

                    $('header').stop().css({
                        'display': 'block'
                    });

                } else {

                    $('header').stop().css({
                        'display': 'none'
                    });

                }
            });

        });
    </script>

    <?php
    get_footer();

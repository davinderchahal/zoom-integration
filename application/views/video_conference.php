<?php if ($signature != '' && $meeting_id != '') { ?>

    <style>
        /* To hide */
        #zmmtg-root {
            display: none;
            position: unset;
        }

        .mini-layout-body {
            margin: 0 !important;
            margin-top: 10px !important;
            margin-left: 24% !important;
        }

        .mini-layout-body-title {
            margin: 0 !important;
        }

        .meeting-app {
            width: 100% !important;
        }

        #wc-loading {
            width: 100% !important;
            height: 100% !important;
        }
    </style>


    <div class="row">
        <div class="col-xl-12 col-12">
            <div class="ReactModal__Body--open">
                <!-- added on import -->
                <div id="zmmtg-root"></div>
                <div id="aria-notify-area"></div>

                <!-- added on meeting init -->
                <div class="ReactModalPortal"></div>
                <div class="ReactModalPortal"></div>
                <div class="ReactModalPortal"></div>
                <div class="ReactModalPortal"></div>
                <div class="global-pop-up-box"></div>
                <div class="sharer-controlbar-container sharer-controlbar-container--hidden"></div>
            </div>

        </div>
    </div>
    <!-- Dependencies for client view and component view -->
    <script src="https://source.zoom.us/2.13.0/lib/vendor/react.min.js"></script>
    <script src="https://source.zoom.us/2.13.0/lib/vendor/react-dom.min.js"></script>
    <script src="https://source.zoom.us/2.13.0/lib/vendor/redux.min.js"></script>
    <script src="https://source.zoom.us/2.13.0/lib/vendor/redux-thunk.min.js"></script>
    <script src="https://source.zoom.us/2.13.0/lib/vendor/lodash.min.js"></script>

    <!-- CDN for client view -->
    <script src="https://source.zoom.us/zoom-meeting-2.13.0.min.js"></script>
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/2.13.0/css/bootstrap.css" />
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/2.13.0/css/react-select.css" />

    <script>
        console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

        // it's option if you want to change the WebSDK dependency link resources. setZoomJSLib must be run at first
        ZoomMtg.setZoomJSLib("https://source.zoom.us/2.13.0/lib", "/av"); // CDN version defaul

        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareJssdk();
        const zoomMeetingSDK = document.getElementById('zmmtg-root');

        // To hide
        zoomMeetingSDK.style.display = 'none';

        // To show
        zoomMeetingSDK.style.display = 'block';
        ZoomMtg.init({
            leaveUrl: 'https://your-domain.com',
            //webEndpoint: meetingConfig.webEndpoint,
            //disableCORP: !window.crossOriginIsolated, // default true
            // disablePreview: false, // default false
            externalLinkPage: 'https://your-domain.com',
            success: function() {
                ZoomMtg.i18n.load('en-US');
                ZoomMtg.i18n.reload('en-US');
                ZoomMtg.join({
                    sdkKey: '<?php echo $client_id; ?>',
                    signature: '<?php echo $signature; ?>', // role in SDK signature needs to be 1
                    meetingNumber: '<?php echo $meeting_id; ?>',
                    passWord: '<?php echo $meeting_password; ?>',
                    userName: '<?php echo $user_name; ?>',
                    userEmail: '<?php echo $user_email; ?>',
                    success: function(res) {
                        console.log("join meeting success");
                        console.log("get attendeelist");
                        ZoomMtg.getAttendeeslist({});
                        ZoomMtg.getCurrentUser({
                            success: function(res) {
                                console.log("success getCurrentUser", res.result.currentUser);
                            },
                        });
                    },
                    error: function(res) {
                        console.log(res);
                    },
                });
            },
            error: function(res) {
                console.log(res);
            },
        });
    </script>
<?php } else { ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <div class="container">
        <?php if ($host == 1) { ?>
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo site_url('zoom/create_room'); ?>" method="post">
                        <div class="row">
                            <div class="col-xl-6 col-3 form-group">
                                <input type="text" name="title" value="<?php echo set_value('title'); ?>" class="form-control ps-15 bg-transparent" placeholder="Title" required>
                                <?php echo form_error('title', '<span class="error invalid-feedback" style="display:block">', '</span>') ?>
                            </div>
                            <div class="col-xl-6 col-2 form-group">
                                <input type="text" name="duration" value="<?php echo set_value('duration'); ?>" class="form-control ps-15 bg-transparent" placeholder="Duration In Minute" required>
                                <?php echo form_error('duration', '<span class="error invalid-feedback" style="display:block">', '</span>') ?>
                            </div>
                            <div class="col-xl-6 col-2 form-group">
                                <button type="submit" class="btn btn-danger mt-10">Start Meeting</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } else { ?>
            <div class="row">
                <div class="col-xl-12 col-12">
                    <div class="alert alert-info">We do not currently have any active live meeting</div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php  } ?>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    jQuery.fn.bstooltip = jQuery.fn.tooltip;

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        onOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
</script>
<?php $successMsg = $this->session->userdata('form-success');
$this->session->unset_userdata('form-success');
$failMsg = $this->session->userdata('form-fail');
$this->session->unset_userdata('form-fail');

if ($successMsg != '' || $failMsg != '') {
    $msg = ($successMsg) ? $successMsg : $failMsg;
    $msgClass = ($successMsg) ? 'success' : 'error'; ?>
    <script>
        $.toast({
            heading: '',
            text: '<?php echo $msg; ?>',
            position: 'top-right',
            loaderBg: '#ff6849',
            icon: '<?php echo $msgClass; ?>',
            hideAfter: 3500,
        });
    </script>
<?php } ?>
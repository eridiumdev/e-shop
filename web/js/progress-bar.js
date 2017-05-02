var $progress = $('.progress');
var $progressBar = $('.progress-bar');
var $alert = $('.alert');

setTimeout(function() {
    $progressBar.css('width', '10%');
    setTimeout(function() {
        $progressBar.css('width', '30%');
        setTimeout(function() {
            $progressBar.css('width', '100%');
            setTimeout(function() {
                $progress.css('display', 'none');
                $alert.css('display', 'block');
            }, 500); // WAIT 5 milliseconds
        }, 2000); // WAIT 2 seconds
    }, 1000); // WAIT 1 seconds
}, 1000); // WAIT 1 second


// HTML
<!-- Progress bar -->
<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
</div>
<div class="alert alert-success" role="alert">Loading completed!</div>

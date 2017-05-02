$("#delivery").change(function (){
    $("#deliveryDesc").html(delivery);
    $("#deliveryDesc").fadeOut();
    $("#deliveryDesc").fadeIn();
});

$("#payment").change(function (){
    $("#paymentDesc").html(payment);
    $("#paymentDesc").fadeOut();
    $("#paymentDesc").fadeIn();
});

// Processing payment
$('#confirmOrderBtn').on('click', function(e) {
	$('#paymentModal').modal();
    $('#paymentModal').preventDefault();
});

$('#confirm_button').on('click', function(e) {
    $('#paymentPassword').hide();
    $('#paymentProcessing').fadeIn('slow');
    $('#modal_footer').hide();

    setTimeout(function() {
        $('#paymentProcessing').hide();
        $('#paymentSuccess').fadeIn('slow');
        $('#confirmOrderBtn').remove();
        $('#successBtn').show();
        setTimeout(function() {$('#paymentModal').modal('hide');}, 1500);
    }, 3000);
});

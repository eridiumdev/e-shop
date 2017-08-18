$("#delivery").change(function (){
    var $deliveryId = $("#delivery").val();
    var $deliveryDescription = $("#deliveryDesc_" + $deliveryId).val();
    $("#deliveryDesc").hide();
    $("#deliveryDesc").fadeIn();
    $("#deliveryDesc").html($deliveryDescription)
});

$("#payment").change(function (){
    var $paymentId = $("#payment").val();
    var $paymentDescription = $("#paymentDesc_" + $paymentId).val();
    $("#paymentDesc").hide();
    $("#paymentDesc").fadeIn();
    $("#paymentDesc").html($paymentDescription)
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
    }, 2300);
});

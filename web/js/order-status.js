// Update order status on select change
$(".statusUpdater").change(function (){
    var $orderId = $(this).parent().find('input').val();
    $("#statusUpdateOrderId").val($orderId);
    $('#statusUpdateForm').attr('action', '/admin/orders/change-status');
    $('#statusUpdateForm').trigger('submit');
});

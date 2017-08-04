$('input[type="number"]').on('input', function(){
    var $qty = $(this).val();
    var $priceDiv = $(this).parent().next('div');
    var $price = $priceDiv.find('input[type="hidden"]').val();
    var $subTotalSpan = $priceDiv.find('span')
    var $subTotal = ($price * $qty);
    $subTotalSpan.html($subTotal);

    var $total = 0.0;
    $('.subtotal').each(function(){
        var $val = $(this).html();
        var $floatVal = parseFloat($val.replace(",", "."));
        $total = $total + $floatVal;
    });
    $('#total').html($total);
});

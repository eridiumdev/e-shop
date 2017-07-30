$('#price').on('input', function(){
    var $price = $('#price').val();
    var $discount = $('#discount').val();
    var $discountedPrice = ($price * (1.00 - $discount)).toFixed(2);
    $("#discountedPrice").val($discountedPrice);
});

$('#discount').on('input', function(){
    var $price = $('#price').val();
    var $discount = $('#discount').val();
    var $discountedPrice = ($price * (1.00 - $discount)).toFixed(2);
    $("#discountedPrice").val($discountedPrice);
});

$('#discountedPrice').on('input', function(){
    var $price = $('#price').val();
    var $discountedPrice = $("#discountedPrice").val();
    var $discount = (1 - ($discountedPrice / $price)).toFixed(2);
    $("#discount").val($discount);
});

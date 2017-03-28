$(function() {
    $(document).scrollTop($("#drugPics").offset().top);
});

function showPage(n) {
    $('.links').removeClass('active');
    $('#link' + n).addClass('active');

    $('.hidable').hide();
    $('#page' + n).fadeIn();
}

function showDrugPage(n) {
    $('.drugLinks').removeClass('active');
    $('#drugLink' + n).addClass('active');

    $('.drugHidable').hide();
    $('#drugPage' + n).fadeIn();
}

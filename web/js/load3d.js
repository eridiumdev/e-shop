$("#show3d").click(function() {
    $(this).remove();
    $('#loading').show();
    $("#window3d").load("/window3d/window3d.html");
});

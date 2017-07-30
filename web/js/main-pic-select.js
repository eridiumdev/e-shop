$(".mainPic-thumbnail").click(function() {
    var $selected = $(this);
    var $name = $selected.siblings('p').text();

    $(".mainPic-thumbnail").not(this).removeClass('outline');
    $selected.addClass('outline');

    $('#selectMainPic').modal()
	.one('click', '#confirm_button', function(e) {
		$('#mainPic').val($name);
        $("#mainPicUploader").val('');
	});
});

$(".mainPic-thumbnail").dblclick(function() {
    var $selected = $(this);
    var $name = $selected.siblings('p').text();

    $(".mainPic-thumbnail").not(this).removeClass('outline');
    $selected.addClass('outline');

    $('#selectMainPic').modal('hide');
    $('#mainPic').val($name);
    $("#mainPicUploader").val('');
});

$("#mainPicUploader").change(function (){
   var fileName = $(this).val();
   var normalized = fileName.replace(/\\/g, '/');
   var split = normalized.split('/');
   $("#mainPic").val(split[split.length - 1]);

   $(".mainPic-thumbnail").removeClass('outline');
});

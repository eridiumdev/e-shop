$(".thumbnail").click(function() {
    var $selected = $(this);
    var $name = $selected.siblings('p').text();

    $(".thumbnail").not(this).css("outline", "none");
    $selected.css("outline", "3px solid #2c3e50");

    $('#select_pic').modal()
	.one('click', '#confirm_button', function(e) {
		$('#picture').val($name);
        $("input:file").val('');
	});
});

$(".thumbnail").dblclick(function() {
    var $selected = $(this);
    var $name = $selected.siblings('p').text();

    $(".thumbnail").not(this).css("outline", "none");
    $selected.css("outline", "3px solid #2a9fd6");

    $('#select_pic').modal('hide');
    $('#picture').val($name);
    $("input:file").val('');
});

$("input:file").change(function (){
   var fileName = $(this).val();
   var normalized = fileName.replace(/\\/g, '/');
   var split = normalized.split('/');
   $("#picture").val(split[split.length - 1]);
});

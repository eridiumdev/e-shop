$(".pic-thumbnail").click(function() {
    var $selected = $(this);
    var $name = $selected.siblings('p').text();
    var $input = $selected.siblings('input');

    if ($selected.hasClass('outline')) {
        $selected.removeClass('outline');
        $input.val('');
    } else {
        $selected.addClass('outline');
        $input.val($name);
    }
});

$("#confirm_button_multi").click(function() {
    var values = $("input[name='selectedPics[]']").map(function(){
        var value = $(this).val();
        if (value != '') {
            return value;
        }
    }).get();
    var count = values.length;
    if (count == 1) {
      $("#pics").val(values);
    } else {
      $("#pics").val(count + " pictures selected");
    }
    $("#picUploader").val('');
});

$("#picUploader").change(function (){
    $("input[name='pics']").map(function(){
        $(this).val('');
    });
    var count = document.getElementById('picUploader').files.length;
    if (count == 1) {
        var fileName = $(this).val();
        var normalized = fileName.replace(/\\/g, '/');
        var split = normalized.split('/');
        $("#pics").val(split[split.length - 1]);
    } else {
        $("#pics").val(count + " pictures to upload");
    }

});

$("#category").change(function (){
   var $categoryId = $(this).val();
   $('.category-specs').hide();
   $('.category-specs').find('.category-spec').prop('disabled', true);
   $('#categorySpecs-' + $categoryId).fadeIn('slow');
   $('#categorySpecs-' + $categoryId).find('.category-spec').prop('disabled', false);
});

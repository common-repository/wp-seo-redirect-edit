jQuery(document).ready(function($){
   $("#seor_redirects .inside span").click(function(){
       let oldSlug = $(this).parent().text();
       let input = $("<input type='hidden' name='seor_remove[]'>");
       input.val(oldSlug);
       input.insertAfter($(this).parent());
       $(this).parent().remove();
   });
});
jQuery(document).ready(function($) {
    $("#projectList").change(function() {
             $("#projectDetails").hide();
        if ($(this).val() === ''){
            return;
        }
   
        $("#projectName,#projectDesc,#projectCreator,#projectTodo").html('');
        
      
        var data = {
            action: "project_details",
            project_id: $(this).val(),
            account_id:bc_account
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            response = $.parseJSON(response);
            $("#projectName").html(response.name);
             $("#projectDesc").html(response.description);
             $("#projectCreator").html("<br><img src='" + response.creator.avatar_url + "'> <span>Created By " + response.creator.name + "</span><br>");
             $.each(response.todos,function(index,todo){
                 $("#projectTodo").append("<li>" + todo.name + "</li>");
             });
             $("#projectDetails").fadeIn();
        });
    });
});
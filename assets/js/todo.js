jQuery(document).ready(function($) {
    $("#projectList").change(function() {
        $("#projectDetails").hide();
        if ($(this).val() === '') {
            return;
        }

        $("#projectName,#projectDesc,#projectCreator,#projectTodo").html('');


        var data = {
            action: "project_details",
            project_id: $(this).val(),
            account_id: bc_account
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            response = $.parseJSON(response);
            $("#projectName").html(response.name);
            $("#projectDesc").html(response.description);
            $("#projectCreator").html("<br><img src='" + response.creator.avatar_url + "'> <span>Created By " + response.creator.name + "</span><br>");
            $.each(response.todos, function(index, todo) {
                $("#projectTodo").append("<li>" + todo.name + "</li>");
            });
            $("#projectDetails").fadeIn();
        });
    });

    $("#add_bc_todo").click(function(e) {
        e.preventDefault();
        var data = {
            action: "todo_box"
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            $("#todo_list").append(response);
        });
    });

    $("#todo_list").sortable();


    $("a.remove_todo").live("click",function(e) {
        e.preventDefault();
        if (confirm("Are you sure you want to remove this todo item?")) {
            $(this).parent().remove();
        }
    });

});

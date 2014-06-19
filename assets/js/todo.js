jQuery(document).ready(function($) {
    $("#projectList").val('');
    $("#postList").val('');
    $("#todoInfo,#todoSelected").hide();


    $("#projectList").change(function() {
        $("#todoInfo").hide();
        $("#projectDetails").hide();
        if ($(this).val() === '') {
            return;
        }

        $("#projectName,#projectDesc,#projectCreator,#projectTodo").html('');

        $("#todoInfo").show();
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


    $("#postList").change(function() {
        $("#theTodoList").html('');
        $("#todoSelected").hide();
        if ($(this).val() === '') {
            return;
        }

        var data = {
            action: "post_list",
            post_id: $(this).val()
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            response = $.parseJSON(response);
            $("#todoName").val(response.post_title);
            $("#todoDesc").val(response.post_content);
            $.each(response.todolist, function(index, value) {
                $("#theTodoList").append("<li>" + value + "</li>");
            });
            $("#todoSelected").show();
        });

    });

    $("#performAssign").click(function(e) {
        e.preventDefault();
        var data = {
            action: "assign_todo",
            project_id: $("#projectList").val(),
            account_id: bc_account,
            todo_name: $("#todoName").val(),
            todo_description: $("#todoDesc").val(),
            post_id: $("#postList").val()
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            $("#todoResults").html(response);

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
    if ($("#todo_list").length > 0) {
        $("#todo_list").sortable();
    }

    $("a.remove_todo").live("click", function(e) {
        e.preventDefault();
        if (confirm("Are you sure you want to remove this todo item?")) {
            $(this).parent().remove();
        }
    });



});

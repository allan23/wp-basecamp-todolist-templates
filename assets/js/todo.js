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
        loadProject();
    });

    $(".expandTodo").live("click", function(e) {
        e.preventDefault();
        var data = {
            action: "expand_todo",
            url: $(this).attr("data-url")
        }
        var mylist = $(this).parent().children("ol");
        mylist.html('');
        var mydesc = $(this).parent().children(".tdesc");
        mydesc.html('');
        $.post(ajax_object.ajax_url, data, function(response) {
            response = $.parseJSON(response);
            mydesc.html(response.description);
            $.each(response.todos.remaining, function(index, todo) {
                mylist.append("<li>" + todo.content + "</li>");
            });
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
        $("#projectDetails").hide();
        $("#ajaxLoading").show();
        var data = {
            action: "assign_todo",
            project_id: $("#projectList").val(),
            account_id: bc_account,
            todo_name: $("#todoName").val(),
            todo_description: $("#todoDesc").val(),
            post_id: $("#postList").val()
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            //$("#todoResults").html(response);
            loadProject();
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

    $("#refreshProject").click(function(e) {
        e.preventDefault();
        loadProject(true);
    });
    function loadProject(hardRefresh) {

        $("#projectDetails").hide();
        $("#ajaxLoading").show();
        $("#projectTodo").html('');
        var data = {
            action: "project_details",
            project_id: $("#projectList").val(),
            account_id: bc_account
        }
        if (hardRefresh === true) {
            data.hardRefresh = true;
        }
        $.post(ajax_object.ajax_url, data, function(response) {
            response = $.parseJSON(response);
            $("#projectName").html(response.name);
            $("#projectDesc").html(response.description);
            $("#projectCreator").html("<br><img src='" + response.creator.avatar_url + "'> <span>Created By " + response.creator.name + "</span><br>");
            if (response.todos.length === 0) {
                $("#projectTodo").append("<li><em>There are no todo lists for this project.</em></li>");
            } else {
                $.each(response.todos, function(index, todo) {
                    $("#projectTodo").append("<li><strong>" + todo.name + "</strong> <a href='#' class='expandTodo' data-url='" + todo.url + "'>[Expand]</a><br><em class='tdesc'></em><ol></ol></li>");
                });
            }
            $("#ajaxLoading").hide();
            $("#projectDetails").fadeIn();

        });

    }
});

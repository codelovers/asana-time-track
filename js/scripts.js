$(function(){
    var apiKeyCookie = $.cookie('asana-api-key');
    var apiKeyForm = $('#api-key-form');
    var apiKeyFormSubmit = apiKeyForm.find('button[type=submit]');
    var apiKeyEditDelete = apiKeyForm.find('#api-key-edit, #api-key-delete, #api-key-refresh');
    var apiKeyInput = apiKeyForm.find('#api-key');
    var workspaceContainer = apiKeyForm.siblings('#workspace-container');
    var apiKeyImg = apiKeyForm.find('.ajax_img');
    var workspaceLoader = $('.container').children('.ajax_img');
    $('#start-modal').modal();
    
    if(apiKeyCookie != null) {
        apiKeyFormSubmit.hide();
        apiKeyEditDelete.show();
        apiKeyInput.attr('disabled', '').val(apiKeyCookie);
        workspaceContainer.fadeIn();
        apiKeyImg.fadeIn();
        workspacesAjaxCall();
    }
    
    $('#start-send').on('click', function(){
        if($('#api-key-form')[0].checkValidity()){
            $.cookie('asana-api-key', apiKeyInput.val(), { expires: 7 });
            apiKeyInput.attr('disabled', '');
            apiKeyImg.fadeIn();
            apiKeyFormSubmit.fadeOut();
            workspacesAjaxCall();
            apiKeyEditDelete.show();
        }
        return false;
    });
    
    function workspacesAjaxCall() {
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val(),
          success: function( result ) {
            workspaceContainer.html(result).fadeIn();
            apiKeyImg.fadeOut();
            },
          error : function( msg ) {
            workspaceContainer.html(msg.responseText).fadeIn();
            apiKeyImg.fadeOut();
            }
        });
    }
    
    $('#api-key-delete').on('click', function(){
        $.cookie('asana-api-key', null);
        apiKeyFormSubmit.show();
        apiKeyEditDelete.hide();
        workspaceContainer.hide();
        apiKeyInput.removeAttr('disabled').val('');
    });
    
    $('#api-key-edit').on('click', function(){
        apiKeyFormSubmit.show();
        apiKeyEditDelete.hide();
        workspaceContainer.hide();
        apiKeyInput.removeAttr('disabled').val(apiKeyCookie);
        return false;
    });
    
    $('#api-key-refresh').on('click', function(){
        workspaceContainer.hide();
        apiKeyImg.fadeIn();
        workspacesAjaxCall();
        return false;
    });
    
    $('#workspace-container').on('click', '#workspace-container > .workspace', function(){
        var caption = $(this).find('h3').text();
        var workspaceId = $(this).data('workspace-id');
        $('#start-modal').modal('hide');
        $('.workspace_caption').show().attr('data-workspace-id', workspaceId).html(caption);
        projectsAjaxCall();
        workspaceLoader.show();    });
    
    function projectsAjaxCall() {
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val()+"&workspaceId=" + $('.workspace_caption').data('workspace-id'),
          success: function( result ) {
              $('#track-table').show().children('.nav-tabs').html(result);
              var projectId = $('#track-table').children('.nav-tabs').children('.active').data('project-id');
              tasksAjaxCall(projectId);
            },
          error : function( msg ) {
              $('.container').html(msg.responseText).fadeIn();
              workspaceLoader.fadeOut();
            }
        });
    }
    
    $('.nav-tabs').on('click', '.nav-tabs > li:not(.active)', function(){        var projectId = $(this).data('project-id');
        $('#tab1').hide();
        workspaceLoader.show();
        tasksAjaxCall(projectId);    });
    
    function tasksAjaxCall(projectId) {
        $.ajax({
          type: "GET",
          url: "request.php",
          data: "apiKey=" + apiKeyInput.val() + "&workspaceId=" + $('.workspace_caption').data('workspace-id') + "&projectId=" + projectId,
          success: function( result ) {
              $('#tab1').show().find('tbody').html(result);
              workspaceLoader.fadeOut();
            },
          error : function( msg ) {
              $('.container').append(msg.responseText).fadeIn();
              workspaceLoader.fadeOut();
            }
        });
    }
});
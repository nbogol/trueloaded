<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--=== Page Content ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">

                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>       
                {if {$messages|@count} > 0}
                    {foreach $messages as $message}
                        <div class="alert fade in {$message['messageType']}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message['message']}</span>
                        </div>               
                    {/foreach}
                {/if}
                <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable dataTable sortable-grid table-properties" data_ajax="{$app->urlManager->createUrl('collections/list')}">
                    <thead>
                        <tr>
                            {foreach $app->controller->view->collectionTable as $tableItem}
                                <th{if $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                        </tr>
                    </thead>
                </table>            

            </div>
        </div>
    </div>

    <!--===Actions ===-->
    <div class="row right_column" id="collections_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="collections_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->
</div>

<script type="text/javascript">
var global = '{$cID}';

function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
}

function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}

function resetStatement(item_id) {
    if (item_id > 0) global = item_id;

    $("#collections_management").hide();
    switchOnCollapse('collections_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
}

var first = true;
function onClickEvent(obj, table) {
    $('#row_id').val(table.find(obj).index());
    $("#collections_management").hide();
    $('#collections_management_data .scroll_col').html('');
    var collections_id = $(obj).find('input.cell_identify').val();
    if (global > 0) collections_id = global;

    $.post("collections/statusactions", { 'collections_id' : collections_id }, function(data, status) {
        if (status == "success") {
            $('#collections_management_data .scroll_col').html(data);
            $("#collections_management").show();
        } else {
            alert("Request error.");
        }
    },"html");

    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + collections_id + ']').parents('tr').addClass('selected');
    global = '';
    url = window.location.href;
    if (url.indexOf('cID=') > 0) {
      url = url.replace(/cID=\d+/g, 'cID=' + collections_id);
    } else {
      url += '?cID=' + collections_id;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({}, '', url);
    }
}

function onUnclickEvent(obj, table) {
    $("#collections_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(table).DataTable().draw(false);
}

function collectionEdit(id) {
    $("#collections_management").hide();
    $.get("collections/edit", { 'collections_id' : id }, function(data, status) {
        if (status == "success") {
            $('#collections_management_data .scroll_col').html(data);
            $("#collections_management").show();
            switchOffCollapse('collections_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function collectionSave(id) {
    $.post("collections/save?collections_id="+id, $('form[name=collection]').serialize(), function(data, status) {
        if (status == "success") {
            //$('#collections_management_data').html(data);
            //$("#collections_management").show();
            $('.alert #message_plce').html('');
            $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
            resetStatement(id);
            switchOffCollapse('collections_list_collapse');
        } else {
            alert("Request error.");
        }
    },"json");
    return false;    
}

function collectionDeleteConfirm(id) {
    $.post("{$app->urlManager->createUrl('collections/confirmdelete')}", { 'collections_id': id }, function (data, status) {
        if (status == "success") {
            $('#collections_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function collectionDelete() {
    if (confirm('Are you sure?')) {
        $.post("{$app->urlManager->createUrl('collections/delete')}", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                if (data == 'reset') {
                    resetStatement();
                } else {
                    $('#collections_management_data .scroll_col').html(data);
                    $("#collections_management").show();
                }
                switchOnCollapse('collections_list_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
    }
    return false;
}

$(document).ready(function(){
    $( ".datatable tbody" ).sortable({
        axis: 'y',
        update: function( event, ui ) {
            $.post("{Yii::$app->urlManager->createUrl('collections/sort-order')}", $(this).sortable('serialize'), function(data, status){
                if (status == "success") {
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        },
        handle: ".handle"
    }).disableSelection();
});

$.fn.image_uploads = function(options){
  var option = jQuery.extend({
    overflow: false,
    box_class: false
  },options);

  return this.each(function() {
    var _this = $(this);
    if (_this.data('value')) {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">Drop files here or<br><span class="btn">Upload</span></div>\
      <div class="upload-file dz-clickable dz-started"><div class="dz-details dz-processing dz-success dz-image-preview"><img data-dz-thumbnail src="{$smarty.const.DIR_WS_CATALOG_IMAGES}' + _this.data('value') + '" /><div class="dz-filename"><span data-dz-name="">' + _this.data('value') + '</span></div><div class="upload-remove"></div></div></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
      $('.upload-remove', _this).click(function(){
        $('.upload-file', _this).html('');
        _this.removeAttr('data-value');
        $('input[name="' + _this.data('name') + '"]').val('del');
      })
    } else {
      _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template">Drop files here or<br><span class="btn">Upload</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="' + _this.data('name') + '"/></div>\
    </div>');
    }

    $('.upload-file', _this).dropzone({
      url: "{Yii::$app->urlManager->createUrl('upload')}",
      sending:  function(e, data) {
        $('.upload-hidden input[type="hidden"]', _this).val(e.name);
        $('.upload-remove', _this).on('click', function(){
          $('.dz-details', _this).remove()
          $('.upload-hidden input[type="hidden"]', _this).val('del');
        })
      },
      previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="dz-filename"><span data-dz-name=""></span></div><div class="upload-remove"></div></div>',
      dataType: 'json',
      drop: function(){
        $('.upload-file', _this).html('');
      }
    });
  })
};
</script>
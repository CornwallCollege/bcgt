/* 
 * Contains all of the init functions
 */


M.mod_bcgtcg = {};


var tmpDate = null;
var observationMaxPoints = 3;
var useThisStudentIDForPopUp = null;

var critNameSwitchThis = null;


M.mod_bcgtcg.cginiteditqual = function(Y) {
    
    var subType = Y.one('#qualSubtype');

    
    
};


M.mod_bcgtcg.initactgrid = function(Y, qualID, grid, courseID, groupingID, cmID, cols ) 
{

    $(document).ready(function() {
        
        draw_grid('CGActGridTable', '', grid, 'activity', cols);
        
//        var height = $('#CGActGridTable').height() + 50;
//        var maxHeight = 600;
//
//        if (height < maxHeight){
//            height = height + 100;
//        } else {
//            height = maxHeight;
//        }
//
//        $('#CGActGridTable').tinytbl({
//            'body': {
//                'useclass': null,
//                'autofocus':false
//            },
//            'head': {
//                'useclass':null
//            },
////            'cols':{
////                'frozen': 2
////            },
//            'rows':{
//                'frozen': 3
//            },
//            'rtl':0,
//            'width': 'auto',
//            'height': ''+($('#CGActGridTable').height() + 50)+''
//        });
//        
//        $('#loading').hide();
//        applyTT();
        
        if (grid === 'se' || grid === 'ae'){
            $('#toggleCommentsButton').removeAttr('disabled');
        } else {
            $('#toggleCommentsButton').attr('disabled', 'disabled');
        }
        
        
        
        
               
        $('#viewsimple').unbind('click');
        $('#viewsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('s');
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID="+qualID+"&cmID="+cmID+"&g="+grid;
            
            $.post(url, function(data){
                draw_grid('CGActGridTable', data, grid, 'activity', cols);
            });
            
        });
        
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('a');
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID="+qualID+"&cmID="+cmID+"&g="+grid;
            
            $.post(url, function(data){
                draw_grid('CGActGridTable', data, grid, 'activity', cols);
            });
            
        });
        
        $('#editsimple').unbind('click');
        $('#editsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('se');
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID="+qualID+"&cmID="+cmID+"&g="+grid;
            
            $.post(url, function(data){
                draw_grid('CGActGridTable', data, grid, 'activity', cols);
            });
            
        });
        
        $('#editadvanced').unbind('click');
        $('#editadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('ae');
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID="+qualID+"&cmID="+cmID+"&g="+grid;
            
            $.post(url, function(data){
                draw_grid('CGActGridTable', data, grid, 'activity', cols);
            });
            
        });       
               
               
               
               
        /*
        $.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
        {
            if ( sNewSource !== undefined && sNewSource !== null ) {
                oSettings.sAjaxSource = sNewSource;
            }
            // Server-side processing should just call fnDraw
            if ( oSettings.oFeatures.bServerSide ) {
                this.fnDraw();
                //return;
            }
            this.oApi._fnProcessingDisplay( oSettings, true );
            var that = this;
            var iStart = oSettings._iDisplayStart;
            var aData = [];

            this.oApi._fnServerParams( oSettings, aData );
            oSettings.fnServerData.call( oSettings.oInstance, oSettings.sAjaxSource, aData, function(json) {
                // Clear the old information from the table
                that.oApi._fnClearTable( oSettings );
                // Got the data - add it to the table
                var aData =  (oSettings.sAjaxDataProp !== "") ?
                    that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;

                var dataLength = aData.length;
                for ( var i=0 ; i<dataLength ; i++ )
                {
                    that.oApi._fnAddData( oSettings, aData[i] );
                }
                oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
                that.fnDraw();
                if ( bStandingRedraw === true )
                {
                    oSettings._iDisplayStart = iStart;
                    that.oApi._fnCalculateEnd( oSettings );
                    that.fnDraw( false );
                }
                that.oApi._fnProcessingDisplay( oSettings, false );
                // Callback user function - for event handlers etc
                if ( typeof fnCallback == 'function' && fnCallback !== null )
                {
                    fnCallback( oSettings );
                }

            }, oSettings );
        };
        draw_CG_act_table(qualID, grid, courseID, groupingID, columnsLocked, configColumnWidth, cmID);
        
        
        var pageNumbers = $('.unitgridpage');
        pageNumbers.each(function(pageNumber){
        $(this).on('click',function(e){
            e.preventDefault();
            //get the page number:
            
            $('.unitgridpage').removeClass('active');
            
            var page = $(this).attr('page');
            Y.one('#pageInput').set('value',page);
            var checked = '';
            if(Y.one('#showlate'))
            {
                checked = Y.one('#showlate').get('checked');
                if(checked)
                {
                    checked = 'L';
                }
            }
            var grid = Y.one('#grid').get('value');
            redraw_CG_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
            
            $(this).addClass('active');
            
        });
    } );
    */
    
    });
    
    /*
    var viewsimple = Y.one('#viewsimple');
    if (viewsimple != null)
    {
        viewsimple.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 's');
            var checked = '';
            if(Y.one('#showlate'))
            {
                checked = Y.one('#showlate').get('checked');
                if(checked)
                {
                    checked = 'L';
                }
            }
            var page = 0;
            if(Y.one('#pageInput'))
            {
                page = Y.one('#pageInput').get('value');
            }
            redraw_CG_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
        });
    }
    
    
    var editsimple = Y.one('#editsimple');
    if (editsimple != null){
        editsimple.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 'se');
            var page = 0;
            if(Y.one('#pageInput'))
            {
                page = Y.one('#pageInput').get('value');
            }
            redraw_CG_act_table(qualID, 'se', false, courseID, page, groupingID, cmID);
        });
    }
    
    
    var editadvanced = Y.one('#editadvanced');
    if (editadvanced != null){
        editadvanced.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 'ae');
            var page = 0;
            if(Y.one('#pageInput'))
            {
                page = Y.one('#pageInput').get('value');
            }
            redraw_CG_act_table(qualID, 'ae', false, courseID, page, groupingID, cmID);
        });
    }
   
    var viewadvanced = Y.one('#viewadvanced');
    if (viewadvanced != null){
        viewadvanced.detach();
        viewadvanced.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 'a');
            var page = 0;
            if(Y.one('#pageInput'))
            {
                page = Y.one('#pageInput').get('value');
            }
            redraw_CG_act_table(qualID, 'a', false, courseID, page, groupingID, cmID);
        });
    }
    
    
    var viewLate = Y.one('#showlate');
    if(viewLate)
    {
        viewLate.detach();
        viewLate.on('click', function(e){
            var checked = viewLate.get('checked');
            if(checked)
            {
                checked = 'L';
            }
            var page = 0;
            if(Y.one('#pageInput'))
            {
                page = Y.one('#pageInput').get('value');
            }
            redraw_CG_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
       });     
    }
        
    // buttons
    $(function() {
      var loc = window.location.href;     
      if(/g=se/.test(loc)) {
        $('#editsimple').addClass('gridbuttonswitchON');
      }
      else if(/g=s/.test(loc)) {
        $('#viewsimple').addClass('gridbuttonswitchON');
      }
      else {}
    });
    
    $(".gridbuttonswitch").click(function(){
    $(".gridbuttonswitchON").removeClass("gridbuttonswitchON");
     $(this).addClass("gridbuttonswitchON");
    });
    */
        
}



var draw_CG_act_table = function(qualID, grid, courseID, groupingID, columnsLocked, configColumnWidth, cmID) { 
    var oTable = $('#BTECActGrid').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
//        "iDisplayStart": 0,
//        "iDisplayLength": 15,
        "sScrollX": "100%",
        "sScrollY": "800px",
        "bScrollCollapse": true,
        "bPaginate": false,
        "bSort":false,
        "bInfo":false,
        "bFilter":false,
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID,
        "fnDrawCallback": function () {
            if ( typeof oTable != 'undefined' ) {
                applyActTT(true);
                setTimeout("applyActTT(true);", 2000); 
            }
        }
    } );
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": columnsLocked,
                    "iLeftWidth": configColumnWidth 
                } );
    //applyUnitTT();
    
}

var recalculate_cols_act = function() {
    $(":input").attr("disabled",true);
    var oDataTable = $('#BTECActGrid').dataTable();
    if(typeof oDataTable != 'undefined'  )
    {
        oDataTable.fnAdjustColumnSizing(false);
        applyActTT();
        setTimeout("applyActTT();", 2000);
        setTimeout("applyActTT();", 3000);
        setTimeout("applyActTT();", 4000);
    }
    setTimeout("$(':input').attr('disabled',false);", 2000);
    setTimeout("$(':input').attr('disabled',false);", 3000);
}

var redraw_CG_act_table = function(qualID, grid, flag, courseID, page, groupingID, cmID) {
    $(":input").attr("disabled",true);
    var lock = false;
    if(grid == 'se' || grid == 'ae')
    {
        lock = true;
    }
    var oDataTable = $('#BTECActGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&lock="+lock+"&cmID="+cmID+"&grID="+groupingID;
    //var oSettings = oDataTable.fnSettings();
    oDataTable.fnReloadAjax(newUrl, recalculate_cols_act);
//    applyUnitTT(false);
    //applyUnitTT();
}


M.mod_bcgtcg.cginiteditunit = function(Y) {
        
    var qualPath = Y.one('#unitPathway');
        if (qualPath !== null){
            qualPath.on('change', function(e) {
            Y.one('#editUnitForm').submit();
        });
    }
    
    var qualPathType = Y.one('#unitPathwayType');
        if (qualPathType !== null){
            qualPathType.on('change', function(e) {
            Y.one('#editUnitForm').submit();
        });
    }
    
    var addNewCrit = Y.one('#addNewCrit');
        if (addNewCrit != null){
        addNewCrit.on('click', function(e){
            addNewCriterion();
            e.preventDefault();
        });
    }
    
    var addHBVRQTask = Y.one('#addNewHBVRQTask');
    if (addHBVRQTask != null){
        addHBVRQTask.detach();
        addHBVRQTask.on('click', function(e){
            addNewHBVRQTask();
            e.preventDefault();
        });
    }
    
    
    var addHBNVQTask = Y.one('#addNewHBNVQTask');
    if (addHBNVQTask != null){
        addHBNVQTask.detach();
        addHBNVQTask.on('click', function(e){
            addNewHBNVQTask();
            e.preventDefault();
        });
    }
    
    
    var addHBNVQSignOffSheet = Y.one('#addNewHBNVQSignOffSheet');
    if (addHBNVQSignOffSheet != null){
        addHBNVQSignOffSheet.detach();
        addHBNVQSignOffSheet.on('click', function(e){
            addNewHBNVQSignOffSheet();
            e.preventDefault();
        });
    }
    
    
    
    
    
    // Validate form before we submit it
    var frm = Y.one('#editUnitForm');
    if (frm !== null){
        frm.on('submit', function(e){
            
            var errors = '';
            
            // Unit type must be set
            var unitType = Y.one('#unitTypeFamily');
            unitType = unitType.get('value');
            if (unitType === '' || unitType < 1){
                errors += 'Unit Type must be set<br>';
            }
            
            // Pathway must be set
            var pathway = Y.one('#unitPathway');
            pathway = pathway.get('value');
            if (pathway === '' || pathway < 1){
                errors += 'Pathway must be set<br>';
            }
            
            // Pathway type must be set
            var pathwayType = Y.one('#unitPathwayType');
            pathwayType = pathwayType.get('value');
            if (pathwayType === '' || pathwayType < 1){
                errors += 'Pathway Type must be set<br>';
            }
            
            // Unique ID must be set
            var code = Y.one('#unique');
            code = code.get('value');
            if (code === '' || code < 1){
                errors += 'Unit Code must be set<br>';
            }
            
            // Name must be set
            var name = Y.one('#name');
            name = name.get('value');
            if (name === '' || name < 1){
                errors += 'Unit Name must be set<br>';
            }
            
            
            if (errors !== ''){
                var errorDiv = Y.one('#outputErrors');
                errorDiv.setContent(errors);
                e.preventDefault();
                return false;
            } 
            
            // Otherwise just do the default & submit it
            
            
        });
    }
    
    applyTT();
    
    
};

function applyActTT(enableInputs)
{
    var selects = Y.one('#selects').get('value');
    var cmID = -1;
    if(selects == "yes")
    {
        
        if (Y.one("#activityChange") != null){
            var index3 = Y.one("#activityChange").get('selectedIndex');
            cmID = Y.one("#activityChange").get("options").item(index3).getAttribute('value');
        }
        
    }
    else
    {
        if(Y.one('#cmID'))
        {
            cmID = Y.one('#cmID').get('value');
        }
    }
    
    // Criteria checkboxes    
    $('.criteriaCheck').unbind('click');
    $('.criteriaCheck').bind('click', function(e){
        
        var value = +$(this).is(':checked');
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: criteriaID,
            unitID: unitID,
            qualID: qualID,
            method: 'check'
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_value.php', '', params, function(d){
            
            updateActivityGrid(d, cell, unitID, studentID);
            
        });
        
    });
        
    
    // Select Criteria
    $('.criteriaValueSelect').unbind('change');
    $('.criteriaValueSelect').bind('change', function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: criteriaID,
            unitID: unitID,
            qualID: qualID,
            method: 'select'
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_value.php', '', params, function(d){
            
            updateActivityGrid(d, cell, unitID, studentID);
            
        });
        
    });
    
    
    
    $('.criteriaValueDate').datepicker( {
        dateFormat: "dd-mm-yy",
        onSelect: function(date){
            
            var critID = $(this).attr('criteriaid');
            var unitID = $(this).attr('unitid');
            var qualID = $(this).attr('qualid');
            var studentID = $(this).attr('studentid');
            var grid = $(this).attr('grid');

            var params = {
                grid: grid,
                value: date,
                studentID: studentID,
                criteriaID: critID,
                unitID: unitID,
                qualID: qualID,
                method: 'date'
            };
            
            var cell = $($(this).parents('td')[0]);
        
            runPost('update_student_value.php', '', params, function(d){

                updateActivityGrid(d, cell, unitID, studentID);

            });
            
        }
    } );
    
    
    
    
    
    
    
//    // Tick Criteria
//    var criteriaChecks = Y.all('.criteriaCheck');
//    if(criteriaChecks)
//    {
//        criteriaChecks.each(function(check){
//            check.detach();
//            check.on('click', function(e){
//                
//                //grey everything out first
//                Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea, #studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').setAttribute('disabled', true);
//                Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").setAttribute('disabled', true);
//                
//                var critID = check.getAttribute('criteriaid');
//                var unitID = check.getAttribute('unitid');
//                var qualID = check.getAttribute('qualid');
//                var studentID = check.getAttribute('studentid');
//                var value = +check.get('checked');
//                var grid = check.getAttribute('grid');
//                                
//                var data = {
//                    method: 'post',
//                    data: {
//                        grid: grid,
//                        method: 'check',
//                        qualID: qualID,
//                        studentID: studentID,
//                        unitID: unitID,
//                        criteriaID: critID,
//                        value: value
//                    },
//                    dataType: 'json',
//                    on: {
//                        success: update_act_grid
//                    }
//                }
//                
//                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/update_student_value.php";
//                Y.io(url, data);
//                
//                $(":input").attr("disabled",false);
//                
//          });  
//        });
//    }
//        
//    
//    // Select Criteria
//    var criteriaSelects = Y.all('.criteriaValueSelect');
//    if(criteriaSelects)
//    {
//        criteriaSelects.each(function(sel){
//            sel.detach();
//            sel.on('change', function(e){
//                
//                //grey everything out first
//                Y.all('#studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').setAttribute('disabled', true);
//                Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").setAttribute('disabled', true);
//                
//                var critID = sel.getAttribute('criteriaid');
//                var unitID = sel.getAttribute('unitid');
//                var qualID = sel.getAttribute('qualid');
//                var studentID = sel.getAttribute('studentid');
//                var value = +sel.get('value');
//                var grid = sel.getAttribute('grid');
//                                
//                var data = {
//                    method: 'post',
//                    data: {
//                        grid: grid,
//                        method: 'select',
//                        qualID: qualID,
//                        studentID: studentID,
//                        unitID: unitID,
//                        criteriaID: critID,
//                        value: value
//                    },
//                    dataType: 'json',
//                    on: {
//                        success: update_act_grid
//                    }
//                }
//                
//                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/update_student_value.php";
//                Y.io(url, data);
//                
//                
//                
//          });  
//          
//        });
//        
//    }
//    
//    
//    $(":input").attr("disabled",false);
//    applyTT();
}

function update_act_grid(id, o){
    applyActTT(true);
}

M.mod_bcgtcg.inithbvrqstudgrid = function(Y, qualID, studentID, grid){
    
    $(document).ready( function(){
        
        $('#viewsimple').unbind('click');
        $('#viewsimple').bind('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/student_grid.php?sID='+studentID+'&qID='+qualID+'&g=s';
        });
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/student_grid.php?sID='+studentID+'&qID='+qualID+'&g=a';
        });
        
        $('#editsimple').unbind('click');
        $('#editsimple').bind('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/student_grid.php?sID='+studentID+'&qID='+qualID+'&g=se';
        });
        
        $('#editadvanced').unbind('click');
        $('#editadvanced').bind('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/student_grid.php?sID='+studentID+'&qID='+qualID+'&g=ae';
        });
        
        applyTT();
        applyStudentTT();
        
    } );
    
};

function runPost(url, action, params, callBack){
    
    loading();
    $.post(M.cfg.wwwroot + '/blocks/bcgt/plugins/bcgtcg/ajax/'+url, {action: action, params: params}, function(d){
        callBack(d);
        loading(false);
    }).fail(function(){
        alert('Error: Could not update!');
        loading(false);
    });
    
}

function update(action, params)
{
        
    Y.all('#studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').setAttribute('disabled', true);
    Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea').setAttribute('disabled', true);
    Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").setAttribute('disabled', true);
        
    $.post(M.cfg.wwwroot + '/blocks/bcgt/plugins/bcgtcg/ajax/update.php', {action: action, params: params}, function(d){
        eval(d);
        Y.all('#studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').removeAttribute('disabled');
        Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea').removeAttribute('disabled');
        Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").removeAttribute('disabled');
    });
    
}


function toggleAddComments()
{
    
    var button = $('#toggleCommentsButton');
    
    if (button.attr('disabled') === 'disabled'){
        return false;
    }
    
    if (button.hasClass('active')){
        button.removeClass('active');
    } else {
        button.addClass('active');
    }
    
    $('.criteriaTDContent').toggle();
    $('.hiddenCriteriaCommentButton').toggle();
    
}


function draw_grid(id, data, view, grid, freezeCols){
            
    // Destroy current tinytbl, if exists
    $('.ui-tinytbl').each( function(){
        
        var role = $(this).attr('role');
        if (role === id){
            $('#'+id).tinytbl('destroy');
        }
        
    } );
    
    // Replace table data
    if (data !== ''){
        $('#'+id+ ' tbody').html(data);
    }
    
    
    // if editing, increase the size of the headers
    if (view === 'se' || view ==='ae'){
        
        $('.criteriaName').css('width', '100px');
        $('.criteriaName').css('min-width', '100px');
        $('.criteriaName').css('max-width', '100px');
        
        $('.criteriaCell').css('width', '100px');
        $('.criteriaCell').css('min-width', '100px');
        $('.criteriaCell').css('max-width', '100px');
        
        $('#toggleCommentsButton').removeAttr('disabled');
                
        $('.unitCommentCell').css('width', '65px');
        $('.unitCommentCell').css('min-width', '65px');
        $('.unitCommentCell').css('max-width', '65px');
        
    } else {
        
        $('.criteriaName').css('width', '40px');
        $('.criteriaName').css('min-width', '40px');
        $('.criteriaName').css('max-width', '40px');
        
        $('.criteriaCell').css('width', '40px');
        $('.criteriaCell').css('min-width', '40px');
        $('.criteriaCell').css('max-width', '40px');
        
        $('.unitCommentCell').css('width', '40px');
        $('.unitCommentCell').css('min-width', '40px');
        $('.unitCommentCell').css('max-width', '40px');
        
        $('#toggleCommentsButton').attr('disabled', 'disabled');
        
    }
        
    var activityUnits = $('.activityUnitName');
    $.each(activityUnits, function(){
        
        var unitID = $(this).attr('unitID');
        var cnt = $('.criterionUnit_'+unitID).length;
        var cWidth = $($('.criterionUnit_'+unitID)[0]).outerWidth();
        var newWidth = cnt * cWidth;
        $(this).css('width', newWidth + 'px');
        $(this).css('min-width', newWidth + 'px');
        $(this).css('max-width', newWidth + 'px');
        $(this).css('padding-left', '0px');
        $(this).css('padding-right', '0px');
        
    });
    
    
    
    
    
    $('#toggleCommentsButton').removeClass('active');
        
    
    // Draw tinytbl again
    var windowHeight = window.innerHeight;
    var height = $('#'+id).height();
    var maxHeight = 800;
    var minHeight = 400;
    
    if (height < minHeight){
        height = 400;
    }
    else if (height > maxHeight) {
        height = maxHeight;
    }
    
    // If too big for window, now make smaller
    if (height >= windowHeight)
    {
        height = windowHeight - 80;
    }
    
    var freezeRows = 1;
            
    $('#'+id).tinytbl({
            'body': {
                'useclass': null,
                'autofocus':false
            },
            'head': {
                'useclass':null
            },
            'cols': {
                'frozen': freezeCols
            },
            'rows': {
                'frozen': freezeRows
            },
            'rtl':0,
            'width': 'auto',
            'height': ''+height+''
        });

    // Width fix
    $('#tinytbl-1').width( $('#tinytbl-1').width() + 20);
    
    $('#loading').hide();
    applyTT();
    
    if (grid === 'student'){
        applyStudentTT();
    } else if (grid === 'unit'){
        applyUnitTT();
    } else if (grid === 'activity'){
        applyActTT();
    } else if(grid === 'class'){
        applyClassTT();
    }
    
    
}


M.mod_bcgtcg.initstudentgrid = function(Y, qualID, studentID, grid, cols) {
    var qualID;
    var studentID;
    var grid;
    var cols;
        
    $(document).ready( function(){
        
        draw_grid('CGStudentGridTable', '', grid, 'student', cols);
        
//        var height = $('#CGStudentGridTable').height() + 50;
//        var maxHeight = 600;
//
//        if (height < maxHeight){
//            height = height + 100;
//        } else {
//            height = maxHeight;
//        }
//
//
//        $('#CGStudentGridTable').tinytbl({
//            'body': {
//                'useclass': null,
//                'autofocus':false
//            },
//            'head': {
//                'useclass':null
//            },
//            'cols': {
//                'frozen': 4
//            },
//            'rows': {
//                'frozen': 1
//            },
//            'rtl':0,
//            'width': 'auto',
//            'height': ''+height+''
//        });


//        $('#loading').hide();
//        applyTT();
        
        if (grid === 'se' || grid === 'ae'){
            $('#toggleCommentsButton').removeAttr('disabled');
        } else {
            $('#toggleCommentsButton').attr('disabled', 'disabled');
        }
        
        
        $('#viewsimple').unbind('click');
        $('#viewsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('s');
    
            // Get data
            var grid = $('#grid').val();

            if ( $('#changeUnitGroup').val() !== undefined ){
                var uGroup = encodeURIComponent( $('#changeUnitGroup').val() );
            } else {
                var uGroup = 0;
            }
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('CGStudentGridTable', data, grid, 'student', cols);
            });
            
            
        });
        
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('a');
    
            // Get data
            var grid = $('#grid').val();
            
            if ( $('#changeUnitGroup').val() !== undefined ){
                var uGroup = encodeURIComponent( $('#changeUnitGroup').val() );
            } else {
                var uGroup = 0;
            }
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('CGStudentGridTable', data, grid, 'student', cols);
            });
            
            
        });
        
        $('#editsimple').unbind('click');
        $('#editsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('se');
    
            // Get data
            var grid = $('#grid').val();
            
            if ( $('#changeUnitGroup').val() !== undefined ){
                var uGroup = encodeURIComponent( $('#changeUnitGroup').val() );
            } else {
                var uGroup = 0;
            }
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('CGStudentGridTable', data, grid, 'student', cols);
            });
            
            
        });
        
        $('#editadvanced').unbind('click');
        $('#editadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('ae');
    
            // Get data
            var grid = $('#grid').val();
            
            if ( $('#changeUnitGroup').val() !== undefined ){
                var uGroup = encodeURIComponent( $('#changeUnitGroup').val() );
            } else {
                var uGroup = 0;
            }
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('CGStudentGridTable', data, grid, 'student', cols);
            });
            
            
        });
        
        
        // CHange unit group 
        $('#changeUnitGroup').unbind('change');
        $('#changeUnitGroup').change( function(){

            $('#loading').show();
                
            // Get data
            var grid = $('#grid').val();
            var uGroup = encodeURIComponent( $(this).val() );
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('CGStudentGridTable', data, grid, 'student', cols);
            });

        } );
        
        
        var doResize;
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                var g = $('#grid').val();
                draw_grid('CGStudentGridTable', '', g, 'student', cols);
            }, 100 );
        } );
        
                
        
    } );
    
    
    
    
//    $(document).ready(function() {
//        var selects = Y.one('#selects');
//        if(selects != null && selects.get('value') == "yes")
//        {
//            var select = Y.one("#qualChange");
//            if(select)
//            {
//                var index = Y.one("#qualChange").get('selectedIndex');
//                qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');  
//            }
//            else
//            {
//                qualID = Y.one('#qID').get('value');    
//            }
//            var index2 = Y.one("#studentChange").get('selectedIndex');
//            studentID = Y.one("#studentChange").get("options").item(index2).getAttribute('value');
//        }
//        else
//        {
//            studentID = Y.one('#sID').get('value');
//            qualID = Y.one('#qID').get('value');   
//        }
//        $.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
//        {
//            if ( sNewSource !== undefined && sNewSource !== null ) {
//                oSettings.sAjaxSource = sNewSource;
//            }
//            // Server-side processing should just call fnDraw
//            if ( oSettings.oFeatures.bServerSide ) {
//                this.fnDraw();
//                //return;
//            }
//            this.oApi._fnProcessingDisplay( oSettings, true );
//            var that = this;
//            var iStart = oSettings._iDisplayStart;
//            var aData = [];
//
//            this.oApi._fnServerParams( oSettings, aData );
//            oSettings.fnServerData.call( oSettings.oInstance, oSettings.sAjaxSource, aData, function(json) {
//                /* Clear the old information from the table */
//                that.oApi._fnClearTable( oSettings );
//                /* Got the data - add it to the table */
//                var aData =  (oSettings.sAjaxDataProp !== "") ?
//                    that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;
//
//                for ( var i=0 ; i<aData.length ; i++ )
//                {
//                    that.oApi._fnAddData( oSettings, aData[i] );
//                }
//                oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
//                that.fnDraw();
//                if ( bStandingRedraw === true )
//                {
//                    oSettings._iDisplayStart = iStart;
//                    that.oApi._fnCalculateEnd( oSettings );
//                    that.fnDraw( false );
//                }
//                that.oApi._fnProcessingDisplay( oSettings, false );
//                /* Callback user function - for event handlers etc */
//                if ( typeof fnCallback == 'function' && fnCallback !== null )
//                {
//                    fnCallback( oSettings );
//                }
//
//            }, oSettings );
//        };
//        
//        draw_CG_student_table(qualID, studentID, grid);
//    } );
//    
//    var viewsimple = Y.one('#viewsimple');
//    if (viewsimple != null)
//    {
//        viewsimple.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 's');
//            var checked = '';
//            if(Y.one('#showlate'))
//            {
//                checked = Y.one('#showlate').get('checked');
//                if(checked)
//                {
//                    checked = 'L';
//                }
//            }
//            redraw_CG_student_table(qualID, studentID, 's', checked);
//        });
//    }
//    
//    var editsimple = Y.one('#editsimple');
//    if (editsimple != null)
//    {
//        editsimple.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'se');
//            redraw_CG_student_table(qualID, studentID, 'se');
//        });
//    }
//    
//    var editadvanced = Y.one('#editadvanced');
//    if (editadvanced != null)
//    {
//        editadvanced.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'ae');
//            redraw_CG_student_table(qualID, studentID, 'ae');
//        });
//    }
//    
//    var viewadvanced = Y.one('#viewadvanced');
//    if (viewadvanced != null)
//    {
//        viewadvanced.detach();
//        viewadvanced.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'a');
//            redraw_CG_student_table(qualID, studentID, 'a');
//        });
//    }
//    
//    
//        
//    // buttons
//    $(function() {
//      var loc = window.location.href;     
//      if(/g=se/.test(loc)) {
//        if ($('#editsimple') != null){  
//            $('#editsimple').addClass('gridbuttonswitchON');
//        }
//      }
//      else if(/g=s/.test(loc)) {
//        if ($('#viewsimple') != null){
//            $('#viewsimple').addClass('gridbuttonswitchON');
//        }
//      }
//      
//    });
//    
//    if ($(".gridbuttonswitch") != null){
//        $(".gridbuttonswitch").click(function(){
//        $(".gridbuttonswitchON").removeClass("gridbuttonswitchON");
//         $(this).addClass("gridbuttonswitchON");
//        });
//    }
    
}

M.mod_bcgtcg.inithbvrqunitgrid = function(Y, qualID, unitID, page){
    
    $(document).ready( function(){
        
        if (page === undefined || page === false || page === ''){
            page = 1;
        }
        
        var viewsimple = Y.one('#viewsimple');
        viewsimple.on('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/unit_grid.php?uID='+unitID+'&qID='+qualID+'&g=s&page='+page;
        });
        
        var viewadv = Y.one('#viewadvanced');
        viewadv.on('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/unit_grid.php?uID='+unitID+'&qID='+qualID+'&g=a&page='+page;
        });
        
        var editsimple = Y.one('#editsimple');
        editsimple.on('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/unit_grid.php?uID='+unitID+'&qID='+qualID+'&g=se&page='+page;
        });
        
        var editadv = Y.one('#editadvanced');
        editadv.on('click', function(e){
            e.preventDefault();
            window.location = M.cfg.wwwroot + '/blocks/bcgt/grids/unit_grid.php?uID='+unitID+'&qID='+qualID+'&g=ae&page='+page;
        });
        
        applyTT();
        applyUnitTT();
        
    } );
    
};


M.mod_bcgtcg.initunitgrid = function(Y, qualID, unitID, grid, cols) {

    var qualID;
    var unitID;
    var grid;
    
    $(document).ready( function(){
        
        draw_grid('CGUnitGridTable', '', grid, 'unit', cols);
        
        if (grid === 'se' || grid === 'ae'){
            $('#toggleCommentsButton').removeAttr('disabled');
        } else {
            $('#toggleCommentsButton').attr('disabled', 'disabled');
        }
        
        var regGrpID = $('#reggrpid').val();
        if (regGrpID === undefined){
            regGrpID = -1;
        }
        
        
        $('#viewsimple').unbind('click');
        $('#viewsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('s');
    
            // Get data
            var grid = $('#grid').val();
            var page = $('#pageInput').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('CGUnitGridTable', data, grid, 'unit', cols);
            });
            
            
        });
        
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('a');
    
            // Get data
            var grid = $('#grid').val();
            var page = $('#pageInput').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('CGUnitGridTable', data, grid, 'unit', cols);
            });
            
            
        });
        
        $('#editsimple').unbind('click');
        $('#editsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('se');
    
            // Get data
            var grid = $('#grid').val();
            var page = $('#pageInput').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('CGUnitGridTable', data, grid, 'unit', cols);
            });
            
            
        });
        
        $('#editadvanced').unbind('click');
        $('#editadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('ae');
    
            // Get data
            var grid = $('#grid').val();
            var page = $('#pageInput').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('CGUnitGridTable', data, grid, 'unit', cols);
            });
            
            
        });
        
        
        $('.unitgridpage').unbind('click');
        $('.unitgridpage').bind('click', function(e){
            
            $('#loading').show();
            
            e.preventDefault();
            $('.unitgridpage').removeClass('active');
            var page = $(this).attr('page');
            $('#pageInput').val(page);
            $(this).addClass('active');
            
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('CGUnitGridTable', data, grid, 'unit', cols);
            });
            
            
        });
        
        
        var doResize;
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                var g = $('#grid').val();
                draw_grid('CGUnitGridTable', '', g, 'unit', cols);
            }, 100 );
        } );
        
        
                
        
    } );




/*
    var qualID;
    var unitID;
    var courseID;
    $(document).ready(function() {
        var selects = Y.one('#selects').get('value');
        if(selects == "yes")
        {
            var index = Y.one("#qualChange").get('selectedIndex');
            qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');
            var index2 = Y.one("#unitChange").get('selectedIndex');
            unitID = Y.one("#unitChange").get("options").item(index2).getAttribute('value');
        }
        else
        {
            unitID = Y.one('#uID').get('value');
            qualID = Y.one('#qID').get('value');   
        }
        courseID = Y.one('#cID').get('value');
        $.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
        {
            if ( sNewSource !== undefined && sNewSource !== null ) {
                oSettings.sAjaxSource = sNewSource;
            }
            // Server-side processing should just call fnDraw
            if ( oSettings.oFeatures.bServerSide ) {
                this.fnDraw();
                //return;
            }
            this.oApi._fnProcessingDisplay( oSettings, true );
            var that = this;
            var iStart = oSettings._iDisplayStart;
            var aData = [];

            this.oApi._fnServerParams( oSettings, aData );
            oSettings.fnServerData.call( oSettings.oInstance, oSettings.sAjaxSource, aData, function(json) {
                // Clear the old information from the table 
                that.oApi._fnClearTable( oSettings );
                // Got the data - add it to the table 
                var aData =  (oSettings.sAjaxDataProp !== "") ?
                    that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;

                for ( var i=0 ; i<aData.length ; i++ )
                {
                    that.oApi._fnAddData( oSettings, aData[i] );
                }
                oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
                that.fnDraw();
                if ( bStandingRedraw === true )
                {
                    oSettings._iDisplayStart = iStart;
                    that.oApi._fnCalculateEnd( oSettings );
                    that.fnDraw( false );
                }
                that.oApi._fnProcessingDisplay( oSettings, false );
                // Callback user function - for event handlers etc 
                if ( typeof fnCallback == 'function' && fnCallback !== null )
                {
                    fnCallback( oSettings );
                }

            }, oSettings );
        };
        
        draw_CG_unit_table(qualID, unitID, 's', courseID, columnsLocked, configColumnWidth);
    } );
    
    var viewsimple = Y.one('#viewsimple');
    viewsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 's');
        var checked = '';
        if(Y.one('#showlate'))
        {
            checked = Y.one('#showlate').get('checked');
            if(checked)
            {
                checked = 'L';
            }
        }
        redraw_CG_unit_table(qualID, unitID, 's', checked, courseID);
    });
    
    var editsimple = Y.one('#editsimple');
    editsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 'se');
        redraw_CG_unit_table(qualID, unitID, 'se', '', courseID);
    });
    
    var editadvanced = Y.one('#editadvanced');
    editadvanced.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 'ae');
        redraw_CG_unit_table(qualID, unitID, 'ae', '', courseID);
    });
    
    var viewadvanced = Y.one('#viewadvanced');
    viewadvanced.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 'a');
        redraw_CG_unit_table(qualID, unitID, 'a', '', courseID);
    }); 
    
    
    // buttons
    $(function() {
      var loc = window.location.href;     
      if(/g=se/.test(loc)) {
        $('#editsimple').addClass('gridbuttonswitchON');
      }
      else if(/g=s/.test(loc)) {
        $('#viewsimple').addClass('gridbuttonswitchON');
      }
      else {}
    });
    
    $(".gridbuttonswitch").click(function(){
    $(".gridbuttonswitchON").removeClass("gridbuttonswitchON");
     $(this).addClass("gridbuttonswitchON");
    });
    
    
    */
    
}



M.mod_bcgtcg.initstudunits = function(Y) {
    
    $(document).ready( function () {
        var tables = $('.cgStudentsUnitsTable');
        var count = tables.length;
        var tablesArray = [];
        for(var i=1;i<=count;i++)
        {
            tablesArray[i] = $('#cgStudentUnits'+i).dataTable( {
                "sScrollX": "100%",
                "sScrollY": "400px",
                "bScrollCollapse": true,
                "bPaginate": false,
                "bSortClasses": false
            });
                
            new FixedColumns( tablesArray[i], {
                "iLeftColumns": 4,
                "iLeftWidth": 250 
           }); 
        }
    });
    
    
    
    $(document).ready( function(){
        
        $('td.nameCol').on('click', function(){

            if (selectedSetID > 0)
            {
                var sID = $(this).attr('sID');
                var qID = $(this).attr('qID');
                var units = unitSets[selectedSetID];
                var unitArray = units.split(',');
                
                // FIrst untick all for this student
                $('.chq'+qID+'s'+sID).prop('checked', false);
                
                // Then loop through units in set and tick
                for (var i = 0; i < unitArray.length; i++)
                {
                    $('#chs'+sID+'q'+qID+'u'+unitArray[i]).prop('checked', true);
                }

            }

        }); 
        
    } );
         
    
         
    var unitCopy = Y.all('.unitsColumn');
    unitCopy.each(function(unit){
        unit.on('click', function(e){
            e.preventDefault();
            //id is in the form of qIDuID
            var id = unit.get('id');
            var toggle = true;
            var className = unit.getAttribute('class');
            var toggleOn = className.indexOf('tOn');
            var toggleOff = className.indexOf('tOff');
            if(toggleOn == -1 && toggleOff == -1)
            {
                className = className + 'tOn'; 
            }
            else
            {
                //knock off the tOn or tOff and put the other back
                //then swicth what we are doing checking or unchecking
                if(toggleOn != -1)
                {
                    className = className.substring(0,className.indexOf('tOn'));
                    toggle = false;
                    className = className + 'tOff';
                }
                else
                {
                    className = className.substring(0,className.indexOf('tOff'));
                    className = className + 'tOn';
                }
            }
            var checkboxes = Y.all('.ch'+id);
            checkboxes.each(function(input){
                if(!toggle)
                {
                    input.set('checked', '');
                }
                else
                {
                    input.set('checked', 'checked'); 
                } 
            });
            unit.setAttribute('class', className);
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    }); 
    
    var studentCopy = Y.all('.studentRow');
    studentCopy.each(function(student){
        student.on('click', function(e){
            e.preventDefault();
            var id = student.get('id');
            var toggle = true;
            var className = student.getAttribute('class');
            var toggleOn = className.indexOf('tOn');
            var toggleOff = className.indexOf('tOff');
            if(toggleOn == -1 && toggleOff == -1)
            {
                className = className + 'tOn'; 
            }
            else
            {
                //knock off the tOn or tOff and put the other back
                //then swicth what we are doing checking or unchecking
                if(toggleOn != -1)
                {
                    className = className.substring(0,className.indexOf('tOn'));
                    toggle = false;
                    className = className + 'tOff';
                }
                else
                {
                    className = className.substring(0,className.indexOf('tOff'));
                    className = className + 'tOn';
                }
            }
            var checkboxes = Y.all('.ch'+id);
            checkboxes.each(function(input){
                if(!toggle)
                {
                    input.set('checked', '');
                }
                else
                {
                    input.set('checked', 'checked'); 
                } 
            });
            student.setAttribute('class', className);
        }); 
    });
    
    var studentCopyAll = Y.all('.studentAll');
    studentCopyAll.each(function(student){
       student.on('click',function(e){
          e.preventDefault();
          var id = student.get('id');
          //this will get all of the checkboxes for the student
          var checkboxes = Y.all('.'+id);
          checkboxes.each(function(input){
              var idName = input.getAttribute('id');
              //comes down in the form of 'chs{STUDENTID}q{QUALID}u{UNITID}'
              var q = idName.indexOf('q');
              var unitID = idName.substring(q);
              var checked = input.get('checked');
              var unitChecks = Y.all('.ch'+unitID);
              unitChecks.each(function(check){
                  check.set('checked', checked);
              });
          });
       }); 
    });
    
    var none = Y.all('.none');
    none.each(function(input){
        input.on('click', function(e){
            e.preventDefault();
            var id = input.get('id');
            //id is none123
            var q = id.indexOf('e');
            var qID = id.substring(q + 1);
            var checkboxes = Y.all('.eSU'+qID);
            checkboxes.each(function(check){
               check.set('checked', ''); 
            });
      });  
    })

    var all = Y.all('.all');
    all.each(function(input){
        input.on('click', function(e){
            e.preventDefault();
            var id = input.get('id');
            //id is none123
            var q = id.indexOf('l');
            var qID = id.substring(q + 2);
            var checkboxes = Y.all('.eSU'+qID);
            checkboxes.each(function(check){
               check.set('checked', 'checked'); 
            });
      });   
    })
    
}

M.mod_bcgtcg.initsinglestudunits = function(Y) {
    $(document).ready( function () {
        var tables = $('.singleStudentUnits');
        var count = tables.length;
        var tablesArray = [];
        for(var i=1;i<=count;i++)
        {
            tablesArray[i] = $('#singleStudentUnits'+i).dataTable( {
                "sScrollX": "100%",
                "bScrollCollapse": true,
                "bPaginate": false,
                "bFilter": false,
                "bSort": false,
                "bInfo":false
            });
                
            new FixedColumns( tablesArray[i], {
                "iLeftColumns": 2,
                "iLeftWidth": 60 
           }); 
        }
    });
    
    var studentCopy = Y.all('.studentRow');
    studentCopy.each(function(student){
        student.on('click', function(e){
            e.preventDefault();
            var id = student.get('id');
            //this is in the form of
            //chs'.$this->studentID.'q'.$this->id
            //so chs2321q2133
            var toggle = true;
            var className = student.getAttribute('class');
            var toggleOn = className.indexOf('tOn');
            var toggleOff = className.indexOf('tOff');
            if(toggleOn == -1 && toggleOff == -1)
            {
                className = className + 'tOn'; 
            }
            else
            {
                //knock off the tOn or tOff and put the other back
                //then swicth what we are doing checking or unchecking
                if(toggleOn != -1)
                {
                    className = className.substring(0,className.indexOf('tOn'));
                    toggle = false;
                    className = className + 'tOff';
                }
                else
                {
                    className = className.substring(0,className.indexOf('tOff'));
                    className = className + 'tOn';
                }
            }
            //get all of them that have the class of chsIDqID and 
            var checkboxes = Y.all('.ch'+id);
            checkboxes.each(function(input){
                if(!toggle)
                {
                    input.set('checked', '');
                }
                else
                {
                    input.set('checked', 'checked'); 
                } 
            });
            student.setAttribute('class', className);
        }); 
    });
}




var redraw_CG_unit_table = function(qualID, unitID, grid, flag, courseID) {
    var oDataTable = $('#CGUnitGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&f="+flag+"&cID="+courseID;
    //var oSettings = oDataTable.fnSettings();
    oDataTable.fnReloadAjax(newUrl, recalculate_cols_units);
    applyTT();
    applyUnitTT();
    //applyUnitTT();
        //setTimeout("recalculate_cols();", 1000)
}


var draw_CG_unit_table = function(qualID, unitID, grid, courseID, columnsLocked, configColumnWidth) { 
    var oTable = $('#CGUnitGrid').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
        "sScrollX": "100%",
        "sScrollY": "800px",
        "bScrollCollapse": true,
        "bPaginate": false,
        "bSort":false,
        "bInfo":false,
        "bFilter":false,
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&cID="+courseID,
        "fnDrawCallback": function () {
            if ( typeof oTable != 'undefined' ) {
                applyTT();
                setTimeout("applyTT();applyUnitTT();", 2000); 
            }
        }
    } );
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": columnsLocked,
                    "iLeftWidth": configColumnWidth 
                } );
    //applyUnitTT();
    
}


var draw_CG_student_table = function(qualID, studentID, grid) { 
                
    var oTable = $('#CGStudentGrid').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
        "sScrollX": "100%",
        "sScrollY": "550px",
        "bScrollCollapse": true,
        "bPaginate": false,
        "bSort":false,
        "bInfo":false,
        "bFilter":false,
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid,
        "fnDrawCallback": function () {
            if ( typeof oTable != 'undefined' ) {
                //applyStudentTT();
                setTimeout("applyTT();applyStudentTT();", 2000); 
            }
        }
    } );
    
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": 3,
                    "iLeftWidth": 280 
                } );
    //applyStudentTT();
    
}

var redraw_CG_student_table = function(qualID, studentID, grid, flag) {
    var oDataTable = $('#CGStudentGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&f="+flag;
    //var oSettings = oDataTable.fnSettings();
        oDataTable.fnReloadAjax(newUrl, recalculate_cols);
        //applyStudentTT();
            //setTimeout("recalculate_cols();", 1000)
            
    // Do qualification comment - We're doing this here cos it changes based on edit/
    $('#qualComment').html('');
    var params = {action: 'getQualComment', params: {studentID: studentID, qualID: qualID, mode: grid} };
    $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/ajax/update_student_comments.php', params, function(data){
        $('#qualComment').html(data);
    });
    
        
            
}


var recalculate_cols = function() {
    var oDataTable = $('#CGStudentGrid').dataTable();
    if(typeof oDataTable != 'undefined'  )
    {
        oDataTable.fnAdjustColumnSizing();
        //applyStudentTT();
    }
    
}

var recalculate_cols_units = function() {
    var oDataTable = $('#CGUnitGrid').dataTable();
    if(typeof oDataTable != 'undefined'  )
    {
        oDataTable.fnAdjustColumnSizing();
//        applyTT();
    }
    
}




function applyTT()
{    
    
    var today = new Date();
    
//    // Set class for background yellow on comments
//    $('.tooltipContent').parents('td').addClass('criteriaComments');
//    $('.tooltipContent').parents('td').attr('title', 'title');
//    
//    //Gets the Unit details
//    $('.uNToolTip').each( function(){
//        
//        // Check is already bound
//        var aria = $(this).attr('aria-describedby');
//        
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//            
//            $(this).tooltip( {
//                content: function(){
//
//                    var uID = $(this).attr('unitID');
//                    var sID = $(this).attr('studentID');
//                    var html = $('div#unitTooltipContent_'+uID+'_'+sID).html();
//                    return html;
//                }
//            } );
//            
//        }
//        
//    } );
    
     
//    //    Gets the criteria comments
//    $('.stuValue').each( function(){
//        
//        // Check is already bound
//        var aria = $(this).attr('aria-describedby');
//        
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//            
//            $(this).tooltip( {
//                content: function(){
//
//                    var cID = $(this).attr('criteriaID');
//                    var sID = $(this).attr('studentID');
//                    var html = $('div#criteriaTooltipContent_'+cID+'_'+sID).html();
//                    return html;
//                }
//            }  );
//            
//        }
//        
//    } );


    $('.criteriaValueNonEdit').unbind('click');
    $('.criteriaValueNonEdit').bind('click', function(){
        
        var t = $(this);
        
        // Check if it already has data brought back from ajax
        var html = t.children('.criteriaContent').html();
        if (html !== undefined && html !== ''){
            
            t.children('.criteriaContent').dialog({
                resizable: false,
                close: function(ev, ui){
                    $(this).dialog('destroy');
                }
            });
            
        } 
        
                
    });
    
          
$('.uNToolTipInfo').unbind('click');
$('.uNToolTipInfo').bind('click', function(){

    var t = $(this);

    // Check if it already has data brought back from ajax
    var html = t.siblings('.unitInfoContent').html();
    if (html !== ''){

        t.siblings('.unitInfoContent').dialog({
            resizable: false,
            close: function(ev, ui){
                $(this).dialog('destroy');
            }
        });

    } 

});

    
    
    $('.overallTask').each( function(){
        
        // Check is already bound
        var aria = $(this).attr('aria-describedby');
        
        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
            
            $(this).tooltip( {
                content: function(){
                    var cID = $(this).attr('criteriaID');
                    var sID = $(this).attr('studentID');
                    var html = $('div#overallTaskTooltipContent_'+cID+'_'+sID).html();
                    return html;
                }
            });
            
        }
        
    } );
    
    
    
//    $('.rangeValue').each( function(){
//        
//        // Check is already bound
//        var aria = $(this).attr('aria-describedby');
//        
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//            
//            $(this).tooltip( {
//                content: function(){
//
//                    var rID = $(this).attr('rangeID');
//                    var uID = $(this).attr('unitID');
//                    var sID = $(this).attr('studentID');
//                    var html = $('div#rangeTooltipContent_'+rID+'_'+uID+'_'+sID).html();
//                    return html;
//                }
//            }  );
//            
//        }
//        
//    } );
    
        
    
        
    $('.signOffTD').each( function(){
        
        // Check is already bound
        var aria = $(this).attr('aria-describedby');
        
        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
            
            $(this).tooltip( {
                content: function(){

                    var tt = $(this).find('div.signoffTooltip');
                    var html = $(tt).html();
                    return html;

                }
            } );
            
        }
        
    } );
    
    
    

    
    
    $('.bcgt_comments_dialog').each( function(indx, item){
        
        if (!$(item).hasClass('ui-dialog-content')){
        
            var cellID = $(item).attr('id');
            var studentID = $(item).attr('studentID');
            var critID = $(item).attr('critID');
            var qualID = $(item).attr('qualID');
            var unitID = $(item).attr('unitID');
            var grid = $(item).attr('grid');
            var imgID = $(item).attr('imgID');
            
            $(item).dialog({

                autoOpen: false,
                resizable: false,
                show: {
                    effect: "fade",
                    duration: 500
                },
                hide: {
                    effect: "fade",
                    duration: 500
                },
                buttons: {
                    "Save": function(){
                        
                        var comments = $(item).find('.dialogCommentText').val();
                        comments = encodeURIComponent(comments);
                        var params = {action: 'criteriaComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments, imgID: imgID} };
                        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/ajax/update_student_comments.php', params, function(data){
                            eval(data);
                        });
                    },
                    Cancel: function(){
                        $(this).dialog("close");
                    }
                }

            });
        
        }
        
    } );
    
    
    $('.bcgt_unit_comments_dialog').each( function(indx, item){
        
        if (!$(item).hasClass('ui-dialog-content')){
        
            var cellID = $(item).attr('id');
            var studentID = $(item).attr('studentID');
            var qualID = $(item).attr('qualID');
            var unitID = $(item).attr('unitID');
            var grid = $(item).attr('grid');
            var imgID = $(item).attr('imgID');
            
            $(item).dialog({

                autoOpen: false,
                resizable: false,
                show: {
                    effect: "fade",
                    duration: 500
                },
                hide: {
                    effect: "fade",
                    duration: 500
                },
                buttons: {
                    "Save": function(){
                        
                        var comments = $(item).find('.dialogCommentText').val();
                        comments = encodeURIComponent(comments);
                        var params = {action: 'unitComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments, imgID: imgID} };
                        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/ajax/update_student_comments.php', params, function(data){
                            eval(data);
                        });
                    },
                    Cancel: function(){
                        $(this).dialog("close");
                    }
                }

            });
        
        }
        
    } );
    
    
    
    
    
    
    $('.addComments').unbind('click');
    $('.addComments').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        var criteriaID = idAttr.split('_')[2];
        var studentID = idAttr.split('_')[6];
        var qualID = idAttr.split('_')[8];
                
        $('#dialog_'+studentID+'_'+criteriaID+'_'+qualID).dialog("open");
        
    } );
    
    $('.editComments').unbind('click');
    $('.editComments').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        var criteriaID = idAttr.split('_')[2];
        var studentID = idAttr.split('_')[6];
        var qualID = idAttr.split('_')[8];
                
        $('#dialog_'+studentID+'_'+criteriaID+'_'+qualID).dialog("open");
        
    } );
    
    
    // Unit Comments
    $('.addCommentsUnit').unbind('click');
    $('.addCommentsUnit').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        var unitID = idAttr.split('_')[2];
        var studentID = idAttr.split('_')[4];
        var qualID = idAttr.split('_')[6];
                
        $('#dialog_S'+studentID+'_U'+unitID+'_Q'+qualID).dialog("open");
        
    } );
    
    
    $('.editCommentsUnit').unbind('click');
    $('.editCommentsUnit').unbind('mouseover');
    $('.editCommentsUnit').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        var unitID = idAttr.split('_')[2];
        var studentID = idAttr.split('_')[4];
        var qualID = idAttr.split('_')[6];
                
        $('#dialog_S'+studentID+'_U'+unitID+'_Q'+qualID).dialog("open");
        
    } );
    
    
    
    
    
    
    
//    // Tooltip to show comments if there are some
//    $('.showCommentsUnit').tooltip({
//        delay: 0, 
//        track: true,
//        showURL: false,
//        position: 'center right',
//        relative: true,
//        bodyHandler: function() {
//            var text = $(this).siblings('.tooltipContent').text();
//            return text;
//        }
//    });


    $('.viewSomething').unbind('click');
    $('.viewSomething').bind('click', function(){
        
        var t = $(this);
        
        // Check if it already has data brought back from ajax
        var html = t.siblings('.thatSomething').html();
        if (html !== ''){
            
            t.siblings('.thatSomething').dialog({
                resizable: false,
                close: function(ev, ui){
                    $(this).dialog('destroy');
                }
            });
            
        } 
        
                
    });

    
    $('#saveQualComment').unbind('click');
    $('#saveQualComment').bind('click', function(){
                
        Y.all('#studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').setAttribute('disabled', true);
                
        var comments = encodeURIComponent( $('#qualComment textarea').val() );
        
        var params = {action: 'qualComment', params: {studentID: $(this).attr('studentid'), qualID: $(this).attr('qualid'), comment: comments, element: 'qualComment'} };
        
        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/ajax/update_student_comments.php', params, function(data){
            eval(data);
            Y.all('#studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').removeAttribute('disabled');
        });
        
    });
    
    
    
    // Set border of red for criteria that were late
    $('.wasLate').parents('td').css('border', '3px solid red');
    
    
    $('.bcgtDatePicker').datepicker( {
        dateFormat: "dd-mm-yy"
    } );
    
    
    $('.datePickerCriteria').datepicker({
        dateFormat: 'dd-mm-yy', 
        maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
        onClose: function(d, i){
            d = $.trim(d);
            if(tmpDate === d){
                tmpDate = null;
                return false;
            }   
            
            var qualID = $(this).attr('qualID');
            var criteriaID = $(this).attr('criteriaID');
            var unitID = $(this).attr('unitID');
            var studentID = $(this).attr('studentID');
            var grid = $(this).attr('grid');
            var setAchieved = false;
            if ($(this).attr('setAchieved') !== undefined){
                setAchieved = true;
            }
            
            var params = {
                qualID: qualID,
                criteriaID: criteriaID,
                studentID: studentID,
                unitID: unitID,
                date: d,
                mode: $('#grid').val(),
                grid: grid,
                setAchieved: setAchieved
            };    
            update('updateCriteriaAwardDate', params);    

        }
    });
    
    $('.datePickerCriteriaTarget').datepicker({
        dateFormat: 'dd-mm-yy', 
        onClose: function(d, i){
            d = $.trim(d);
            if(tmpDate === d){
                tmpDate = null;
                return false;
            }   
            
            var qualID = $(this).attr('qualID');
            var criteriaID = $(this).attr('criteriaID');
            var unitID = $(this).attr('unitID');
            var studentID = $(this).attr('studentID');
            var grid = $(this).attr('grid');
            
            var params = {
                qualID: qualID,
                criteriaID: criteriaID,
                studentID: studentID,
                unitID: unitID,
                date: d,
                mode: $('#grid').val(),
                grid: grid
            };    
            update('updateCriteriaTargetDate', params);    

        }
    });
    
    $('.datePickerIV').datepicker({
        dateFormat: 'dd-mm-yy', 
        maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
        onClose: function(d, i){
            d = $.trim(d);
            if(tmpDate === d){
                tmpDate = null;
                return false;
            }   
            
            var qualID = $(this).attr('qualID');
            var unitID = $(this).attr('unitID');
            var studentID = $(this).attr('studentID');
            var attribute = $(this).attr('name');
            var grid = $(this).attr('grid');
            
            var params = {
                qualID: qualID,
                studentID: studentID,
                unitID: unitID,
                attribute: attribute,
                value: d,
                mode: $('#grid').val(),
                grid: grid
            };    
            
            update('updateUnitAttribute', params);    

        }
    });
    
    $('.datePickerRange').datepicker({
        dateFormat: 'dd-mm-yy', 
        maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
        onClose: function(d, i){
            d = $.trim(d);
            if(tmpDate === d){
                tmpDate = null;
                return false;
            }   
            
            var qualID = $(this).attr('qualID');
            var studentID = $(this).attr('studentID');
            var rangeID = $(this).attr('rangeID');
            
            var params = {
                qualID: qualID,
                rangeID: rangeID,
                studentID: studentID,
                date: d
            };    

            update('updateRangeAwardDate', params);

        }
    });
    
    
    $('.datePickerRangeTarget').datepicker({
        dateFormat: 'dd-mm-yy', 
        onClose: function(d, i){
            d = $.trim(d);
            if(tmpDate === d){
                tmpDate = null;
                return false;
            }   
            
            var qualID = $(this).attr('qualID');
            var studentID = $(this).attr('studentID');
            var rangeID = $(this).attr('rangeID');
            
            var params = {
                qualID: qualID,
                rangeID: rangeID,
                studentID: studentID,
                date: d
            };    

            update('updateRangeTargetDate', params);

        }
    });
    
    
    $('.datePickerOutcomeObservation').datepicker({
        dateFormat: 'dd-mm-yy', 
        onClose: function(d, i){
            d = $.trim(d);
            if(tmpDate === d){
                tmpDate = null;
                return false;
            }   

            var qualID = $(this).attr('qualID');
            var criteriaID = $(this).attr('criteriaID');
            var unitID = $(this).attr('unitID');
            var studentID = $(this).attr('studentID');
            var observationNum = $(this).attr('observationNum');
            var grid = $(this).attr('grid');

            var params = {
                criteriaID: criteriaID,
                unitID: unitID,
                studentID: studentID,
                qualID: qualID,
                observationNum: observationNum,
                grid: grid,
                date: d
            }

            update('updateOutcomeObservationDate', params);

        }
    });
    
    
    
    $('.updateUnitAttribute').unbind('blur');
    $('.updateUnitAttribute').bind('blur', function(){
        
        var qualID = $(this).attr('qualID');
        var unitID = $(this).attr('unitID');
        var studentID = $(this).attr('studentID');
        var attribute = $(this).attr('name');
        var grid = $(this).attr('grid');
        var value = $(this).val();
        var mode = $('#grid').val();
        
        // If checkbox
        if ($(this).attr('type') == 'checkbox'){
            if ($(this).is(':checked')) value = 1;
            else value = 0;
        }

        var params = {
            qualID: qualID,
            studentID: studentID,
            unitID: unitID,
            attribute: attribute,
            value: value,
            mode: mode,
            grid: grid
        };   
                
        update('updateUnitAttribute', params);    
        
    });
    
    $('.updateRangeAward').unbind('change');
    $('.updateRangeAward').bind('change', function(){
        
        var qualID = $(this).attr('qualID');
        var unitID = $(this).attr('unitID');
        var studentID = $(this).attr('studentID');
        var rangeID = $(this).attr('rangeID');
        var grid = $(this).attr('grid');
        var value = $(this).val();
        var mode = $('#grid').val();
        
        var params = {
            qualID: qualID,
            studentID: studentID,
            unitID: unitID,
            rangeID: rangeID,
            value: value,
            mode: mode,
            grid: grid
        };   
        
        update("updateRangeAward", params);
        
    });
    
    $('.datePicker, .datePickerCriteria, .datePickerRange, .datePickerRangeTarget, .datePickerIV, .datePickerOutcomeObservation').click( function(){
        tmpDate = $(this).val();
    });
    
    $( "#genericPopup" ).draggable(
        {
            handle: "#genericContent"
        }
    );
        
    
    //$('#genericPopup').resizable();
    
    // Set class for background yellow on comments
    $('.editComments, .editCommentsUnit, .tooltipContent').parents('td').addClass('criteriaComments');
    $('.editComments, .editCommentsUnit, .tooltipContent').parents('td').attr('title', '');
    
    
    $('.criteriaComments').each( function(){
        
        var aria = $(this).attr('aria-describedby');
        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){

            $(this).tooltip( {
                content: function(){

                    var tt = $(this).find('div.tooltipContent');
                    var html = $(tt).html();
                    return html;
                }
            });

        }
        
    } );
    
    // if IE, change button links to have onclick, because as we all know, IE is shit and plays by its own rules
    if (navigator.appName === 'Microsoft Internet Explorer')
    {
        $('a input.btn').click(function(){
            window.location = $(this).closest('a').attr('href');
        });
    }
    
    $(document).on('click', 'body', function(){
        $('.ui-tooltip').fadeOut();
    });
    
    $('#loading').hide();
        
}

function loading(end){
    
    if (end === false){
        $('#loading').hide();
    } else {
        $('#loading').show();
    }
    
}


function applyClassTT(){
    
    // Select Criteria
    $('.unitAward').unbind('change');
    $('.unitAward').bind('change', function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        
        var params = {
            grid: 'student',
            value: value,
            sID: studentID,
            uID: unitID,
            qID: qualID
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_unit_award.php', '', params, function(d){
            
            updateClassGrid(d, cell, qualID, studentID, unitID);
            
        });
        
    });
    
}



function applyUnitTT(){
    
    
    // Criteria checkboxes    
    $('.criteriaCheck').unbind('click');
    $('.criteriaCheck').bind('click', function(e){
        
        var value = +$(this).is(':checked');
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: criteriaID,
            unitID: unitID,
            qualID: qualID,
            method: 'check'
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_value.php', '', params, function(d){
            
            updateUnitGrid(d, cell, qualID, studentID, unitID);
            
        });
        
    });
        
    
    // Select Criteria
    $('.criteriaValueSelect').unbind('change');
    $('.criteriaValueSelect').bind('change', function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: criteriaID,
            unitID: unitID,
            qualID: qualID,
            method: 'select'
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_value.php', '', params, function(d){
            
            updateUnitGrid(d, cell, qualID, studentID, unitID);
            
        });
        
    });
    
    
    
    $('.criteriaValueDate').datepicker( {
        dateFormat: "dd-mm-yy",
        onSelect: function(date){
            
            var critID = $(this).attr('criteriaid');
            var unitID = $(this).attr('unitid');
            var qualID = $(this).attr('qualid');
            var studentID = $(this).attr('studentid');
            var grid = $(this).attr('grid');

            var params = {
                grid: grid,
                value: date,
                studentID: studentID,
                criteriaID: critID,
                unitID: unitID,
                qualID: qualID,
                method: 'date'
            };
            
            var cell = $($(this).parents('td')[0]);
            runPost('update_student_value.php', '', params, function(d){

                updateUnitGrid(d, cell, qualID, studentID, unitID);

            });
            
        }
    } );
    
    
    
     // User defined values - TEXT criteria
    $('.criteriaUserDefinedValue').unbind();
    $('.criteriaUserDefinedValue').bind('blur', function(){
        
        var value = $(this).val();
        var critID = $(this).attr('criteriaid');
        var unitID = $(this).attr('unitid');
        var qualID = $(this).attr('qualid');
        var studentID = $(this).attr('studentid');
        var grid = $(this).attr('grid');

        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: critID,
            unitID: unitID,
            qualID: qualID,
            method: 'text'
        };

        var cell = $($(this).parents('td')[0]);

        runPost('update_student_value.php', '', params, function(d){

            updateUnitGrid(d, cell, qualID, studentID, unitID);

        });
        
    });
    
    
    // Select Criteria
    $('.unitAward').unbind('change');
    $('.unitAward').bind('change', function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        
        var params = {
            grid: 'student',
            value: value,
            sID: studentID,
            uID: unitID,
            qID: qualID
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_unit_award.php', '', params, function(d){
            
            updateUnitGrid(d, cell, qualID, studentID, unitID);
            
        });
        
    });
    
    
    
    
//    // Tick Criteria
//    var criteriaChecks = Y.all('.criteriaCheck');
//    if(criteriaChecks)
//    {
//        criteriaChecks.each(function(check){
//            check.detach();
//            check.on('click', function(e){
//                
//                //grey everything out first
//                Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea, #studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').setAttribute('disabled', true);
//                Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").setAttribute('disabled', true);
//                
//                var critID = check.getAttribute('criteriaid');
//                var unitID = check.getAttribute('unitid');
//                var qualID = check.getAttribute('qualid');
//                var studentID = check.getAttribute('studentid');
//                var value = +check.get('checked');
//                var grid = check.getAttribute('grid');
//                                
//                var data = {
//                    method: 'post',
//                    data: {
//                        grid: grid,
//                        method: 'check',
//                        qualID: qualID,
//                        studentID: studentID,
//                        unitID: unitID,
//                        criteriaID: critID,
//                        value: value
//                    },
//                    dataType: 'json',
//                    on: {
//                        success: update_unit_grid
//                    }
//                }
//                
//                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/update_student_value.php";
//                Y.io(url, data);
//                
//                $(":input").attr("disabled",false);
//                
//          });  
//        });
//    }
//        
//    
//    // Select Criteria
//    var criteriaSelects = Y.all('.criteriaValueSelect');
//    if(criteriaSelects)
//    {
//        criteriaSelects.each(function(sel){
//            sel.detach();
//            sel.on('change', function(e){
//                
//                //grey everything out first
//                Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea').setAttribute('disabled', true);
//                Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").setAttribute('disabled', true);
//                
//                var critID = sel.getAttribute('criteriaid');
//                var unitID = sel.getAttribute('unitid');
//                var qualID = sel.getAttribute('qualid');
//                var studentID = sel.getAttribute('studentid');
//                var value = +sel.get('value');
//                var grid = sel.getAttribute('grid');
//                                
//                var data = {
//                    method: 'post',
//                    data: {
//                        grid: grid,
//                        method: 'select',
//                        qualID: qualID,
//                        studentID: studentID,
//                        unitID: unitID,
//                        criteriaID: critID,
//                        value: value
//                    },
//                    dataType: 'json',
//                    on: {
//                        success: update_unit_grid
//                    }
//                }
//                
//                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/update_student_value.php";
//                Y.io(url, data);
//                
//                
//                
//          });  
//          
//        });
//        
//    }
//    
//    
//    $('.criteriaValueDate').datepicker( {
//        dateFormat: "dd-mm-yy",
//        onSelect: function(date){
//            
//            //grey everything out first
//            Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea').setAttribute('disabled', true);
//            Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").setAttribute('disabled', true);
//
//            var critID = $(this).attr('criteriaid');
//            var unitID = $(this).attr('unitid');
//            var qualID = $(this).attr('qualid');
//            var studentID = $(this).attr('studentid');
//            var grid = $(this).attr('grid');
//
//            var data = {
//                method: 'post',
//                data: {
//                    grid: grid,
//                    method: 'date',
//                    qualID: qualID,
//                    studentID: studentID,
//                    unitID: unitID,
//                    criteriaID: critID,
//                    value: date
//                },
//                dataType: 'json',
//                on: {
//                    success: update_unit_grid
//                }
//            }
//
//            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/update_student_value.php";
//            Y.io(url, data);
//
//            $(":input").attr("disabled",false);
//            
//        }
//    } );
//    
//    
//    
//    
//    var unitAward = Y.all('.unitAward');
//    if(unitAward)
//    {
//        unitAward.each(function(award){
//            award.detach();
//            award.on('change', function(e){
//                //grey everything out first
//                Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea').setAttribute('disabled', true);
//                //get the id which will be the unitid
//                var idString = award.get('id');
//                
//                var unitID = award.getAttribute('unitid');
//                var studentID = award.getAttribute('studentid');
//                var qualID = award.getAttribute('qualid');
//                
//                var index = award.get('selectedIndex');
//                var value = award.get("options").item(index).getAttribute('value');
//                
//                var data = {
//                    method: 'POST',
//                    data: {
//                        'qID' : qualID, 
//                        'sID' : studentID, 
//                        'uID' : unitID,
//                        'value' : value,
//                        'grid' : 'unit'
//                    },
//                    on: {
//                        success: update_unit_grid
//                    }
//                }
//                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/update_student_unit_award.php";
//                Y.io(url, data);
//            });
//        });
//    }
        
        
        
    $('.setStudent').unbind('click');
    $('.setStudent').bind('click', function(){
        
        $('.setStudent').removeClass('selectedStudent');
        
        var studentID = $(this).attr('studentID');
        $(this).addClass('selectedStudent');
        
        useThisStudentIDForPopUp = studentID;
        
    });
        
    
}




function updateStudentGrid(data, cell, unitID, studentID){
    
    var d = $.parseJSON(data);
                
    // Highlight criteria cell
    $(cell).effect('highlight', {color: '#ccff66'}, 3000);

    // If a unit award is present, highlight that cell as well (actually do it anyway, in case it's changing to -1)
    //if ( $(cell).attr('id').indexOf('unitAward') === -1 ){
        //$('#unitAwardCell_'+unitID+'_'+studentID).effect('highlight', {color: '#ccff66'}, 3000);
    //}

    // Also, set the new award
    if (d.unitaward !== undefined && d.unitaward.awardid !== undefined){
        if ( $('#unitAwardEdit_'+unitID+'_'+studentID).length > 0 ){
            $('#unitAwardEdit_'+unitID+'_'+studentID).val( d.unitaward.awardid );
        } else if( $('#unitAwardAdv_'+unitID+'_'+studentID).length > 0 ){
            $('#unitAwardAdv_'+unitID+'_'+studentID).text( d.unitaward.awardvalue );
        }
    }

    // Percentange
    if (d.percentage !== undefined && d.percentage.percent !== undefined){
        //$('#U'+unitID+'S'+studentID+'PercentParent').parent().parent().effect('highlight', {color: '#ccff66'}, 3000);
        $('#U'+unitID+'S'+studentID+'PercentText').text(d.percentage.percent + '%');
        $('#U'+unitID+'S'+studentID+'PercentParent').attr('title', d.percentage.percent + '% Complete');
        $('#U'+unitID+'S'+studentID+'PercentComplete').css('width', d.percentage.percent + '%');
    }


    // Qual award
    $('.qualAward').text( d.qualaward.awardvalue );
    //$('.qualAward').parent().effect('highlight', {color: '#ccff66'}, 3000);
    
}



function updateUnitGrid(data, cell, qualID, studentID, unitID){
    
    var d = $.parseJSON(data);
            
            
            
    // Highlight criteria cell
    $(cell).effect('highlight', {color: '#ccff66'}, 3000);


    // If a unit award is present, highlight that cell as well (actually do it anyway, in case it's changing to -1)
    //if ( $(cell).attr('id').indexOf('unitAward') === -1 ){
        //$('#unitAwardCell_'+studentID+'_'+qualID).effect('highlight', {color: '#ccff66'}, 3000);
    //}

    // Also, set the new award
    if (d.unitaward !== undefined && d.unitaward.awardid !== undefined){
        if ( $('#unitAwardEdit_'+studentID+'_'+qualID).length > 0 ){
            $('#unitAwardEdit_'+studentID+'_'+qualID).val( d.unitaward.awardid );
        } else if( $('#unitAwardAdv_'+studentID+'_'+qualID).length > 0 ){
            $('#unitAwardAdv_'+studentID+'_'+qualID).text( d.unitaward.awardvalue );
        }
    }

    // Percentange
    if (d.percentage !== undefined && d.percentage.percent !== undefined){
        //$('#U'+unitID+'S'+studentID+'PercentParent').parent().parent().effect('highlight', {color: '#ccff66'}, 3000);
        $('#U'+unitID+'S'+studentID+'PercentText').text(d.percentage.percent + '%');
        $('#U'+unitID+'S'+studentID+'PercentParent').attr('title', d.percentage.percent + '% Complete');
        $('#U'+unitID+'S'+studentID+'PercentComplete').css('width', d.percentage.percent + '%');
    }


    // Qual award
    $('.qualAward_'+studentID).text( d.qualaward.awardvalue );
    //$('.qualAward_'+studentID).parent().effect('highlight', {color: '#ccff66'}, 3000);
    
}




function updateActivityGrid(data, cell, unitID, studentID){
                            
    // Highlight criteria cell
    $(cell).effect('highlight', {color: '#ccff66'}, 3000);

}


function updateClassGrid(data, cell, unitID, studentID){
                            
    // Highlight criteria cell
    $(cell).effect('highlight', {color: '#ccff66'}, 3000);

}






function applyStudentTT(){
    
            
    // Criteria checkboxes    
    $('.criteriaCheck').unbind('click');
    $('.criteriaCheck').bind('click', function(e){
        
        var value = +$(this).is(':checked');
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: criteriaID,
            unitID: unitID,
            qualID: qualID,
            method: 'check'
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_value.php', '', params, function(d){
            
            updateStudentGrid(d, cell, unitID, studentID);
            
        });
        
    });
        
    
    // Select Criteria
    $('.criteriaValueSelect').unbind('change');
    $('.criteriaValueSelect').bind('change', function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: criteriaID,
            unitID: unitID,
            qualID: qualID,
            method: 'select'
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_value.php', '', params, function(d){
            
            updateStudentGrid(d, cell, unitID, studentID);
            
        });
        
    });
    
    
    
    $('.criteriaValueDate').datepicker( {
        dateFormat: "dd-mm-yy",
        onSelect: function(date){
            
            var critID = $(this).attr('criteriaid');
            var unitID = $(this).attr('unitid');
            var qualID = $(this).attr('qualid');
            var studentID = $(this).attr('studentid');
            var grid = $(this).attr('grid');

            var params = {
                grid: grid,
                value: date,
                studentID: studentID,
                criteriaID: critID,
                unitID: unitID,
                qualID: qualID,
                method: 'date'
            };
            
            var cell = $($(this).parents('td')[0]);
        
            runPost('update_student_value.php', '', params, function(d){

                updateStudentGrid(d, cell, unitID, studentID);

            });
            
        }
    } );
    
    
    // User defined values - TEXT criteria
    $('.criteriaUserDefinedValue').unbind();
    $('.criteriaUserDefinedValue').bind('blur', function(){
        
        var value = $(this).val();
        var critID = $(this).attr('criteriaid');
        var unitID = $(this).attr('unitid');
        var qualID = $(this).attr('qualid');
        var studentID = $(this).attr('studentid');
        var grid = $(this).attr('grid');

        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            criteriaID: critID,
            unitID: unitID,
            qualID: qualID,
            method: 'text'
        };

        var cell = $($(this).parents('td')[0]);

        runPost('update_student_value.php', '', params, function(d){

            updateStudentGrid(d, cell, unitID, studentID);

        });
        
        
    });
    
    
    // Select Criteria
    $('.unitAward').unbind('change');
    $('.unitAward').bind('change', function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        
        var params = {
            grid: 'student',
            value: value,
            sID: studentID,
            uID: unitID,
            qID: qualID
        };
        
        var cell = $($(this).parents('td')[0]);
        
        runPost('update_student_unit_award.php', '', params, function(d){
            
            updateStudentGrid(d, cell, unitID, studentID);
            
        });
        
    });
    
    
    
}





var cmt = "";
var unitID = -1;
var critID = -1;
var cellID = "";
var qualID = -1;
var studentID = -1;
var grid = "";

cmt = {

    setup: function(q, u, c, s, id, g){
        unitID = u;
        critID = c;
        cellID = id;
        studentID = s;
        qualID = q;
        grid = g;
    },

    create : function(div, un, fn, unit, crit, text){ /* Create the popup with the textarea to add a comment */

        /* First assign the values to the spans */
        $("#commentBoxUsername").html(un);
        $("#commentBoxFullname").html(fn);
        $("#commentBoxUnit").html(unit);
        $("#commentBoxCriteria").html(crit);
        
        if (text !== undefined){
            /* Strip crap from body if only just added */
            var regex = /<br\s*[\/]?>/gi;
            text = text.replace(regex, "\n");

            $("#commentText").val(text);
        }

        
        css_popup(div); /* Call function from cssPopup.js */

    },

    cancel : function(){ /* Cancel editing/submitting a comment - basically just close the popup */

        /* Reset values */
        $("#commentBoxUsername").html("");
        $("#commentBoxFullname").html("");
        $("#commentBoxUnit").html("");
        $("#commentBoxCriteria").html("");
        $("#commentText").val("");

        /* Close Divs */
        $("#bcgtblanket").css("display", "none");
        $("#popUpDiv").css("display", "none");

    },

    reset : function(){
        unitID = -1;
        critID = -1;
        cellID = "";
        studentID = -1;
        qualID = -1;
    }

};




function css_popup(windowname) {
	blanket_size(windowname);
	$('#bcgtblanket').toggle();
    $('#'+windowname).toggle();
    window_pos(windowname);
}

function blanket_size() {
	if (typeof window.innerWidth != 'undefined') {
		viewportheight = window.innerHeight;
	} else {
		viewportheight = document.documentElement.clientHeight;
	}
	if ((viewportheight > document.body.parentNode.scrollHeight) && (viewportheight > document.body.parentNode.clientHeight)) {
		blanket_height = viewportheight;
	} else {
		if (document.body.parentNode.clientHeight > document.body.parentNode.scrollHeight) {
			blanket_height = document.body.parentNode.clientHeight;
		} else {
			blanket_height = document.body.parentNode.scrollHeight;
		}
	}
	var blanket = document.getElementById('bcgtblanket');
	blanket.style.height = blanket_height + 'px';

}

function window_pos(popUpDivVar) {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerHeight;
	} else {
		viewportwidth = document.documentElement.clientHeight;
	}
	if ((viewportwidth > document.body.parentNode.scrollWidth) && (viewportwidth > document.body.parentNode.clientWidth)) {
		window_width = viewportwidth;
	} else {
		if (document.body.parentNode.clientWidth > document.body.parentNode.scrollWidth) {
			window_width = document.body.parentNode.clientWidth;
		} else {
			window_width = document.body.parentNode.scrollWidth;
		}
	}
        
	var popUpDiv = document.getElementById(popUpDivVar);
        var left = ( $(window).width()  - $('#'+popUpDivVar).width()  ) / 2;
        var t =    ( $(window).height() - $('#'+popUpDivVar).height() ) / 2;
        
	popUpDiv.style.left = left + 'px';
        popUpDiv.style.top = t + 'px';
}



function updateCommentCell(id, comment, imgID)
{
    
    comment = comment.replace(/\\n/g, "<br>");
    comment = comment.replace(/\+/g, " ");
    comment = $.trim(comment);
        
    var button = $('#'+imgID);
    
    // Empty comment, so set button to "add comment" style
    if(comment == "")
    {
            
        button.attr("class", "addComments");
        button.attr("title", "Click here to add comments");
        button.attr("alt", "Click here to add comments");     
        
        var img = button.attr('src').replace('comment_edit', 'comment_add');
        button.attr("src", img);

        // Remove the tooltip div
        button.siblings('.tooltipContent').remove();

    }
    
    // Else must be a comment so we changed button to "edit comment" style
    else
    {
        
        button.attr("class", "editComments");
        button.attr("title", "Click here to edit comments");
        button.attr("alt", "Click here to edit comments");
        
        var img = button.attr('src').replace('comment_add', 'comment_edit');
        button.attr("src", img);

        // Remove the tooltip div
        button.siblings('.tooltipContent').remove();
        
        comment = decodeURIComponent(comment);

        // Add the tooltip div
        $('#'+id).find('.dialogCommentText').val(comment);

    }

    applyTT();
    $('#'+id).dialog('close');
    
}


function updateUnitCommentCell(id, comment, imgID)
{
    
    comment = comment.replace(/\\n/g, "<br>");
    comment = comment.replace(/\+/g, " ");
    comment = $.trim(comment);
        
    var button = $('#'+imgID);
    
    // Empty comment, so set button to "add comment" style
    if(comment == "")
    {
            
        button.attr("class", "addCommentsUnit");
        button.attr("title", "Click here to add comments");
        button.attr("alt", "Click here to add comments");     
        
        var img = button.attr('src').replace('edit.png', 'add.png');
        button.attr("src", img);

        // Remove the tooltip div
        button.siblings('.tooltipContent').remove();

    }
    
    // Else must be a comment so we changed button to "edit comment" style
    else
    {
        button.attr("class", "editCommentsUnit");
        button.attr("title", "Click here to edit comments");
        button.attr("alt", "Click here to edit comments");
        
        var img = button.attr('src').replace('add.png', 'edit.png');
        button.attr("src", img);

        // Remove the tooltip div
        button.siblings('.tooltipContent').remove();
        
        comment = decodeURIComponent(comment);

        // Add the tooltip div
        $('#'+id).find('.dialogCommentText').val(comment);

    }

    applyTT();
    $('#'+id).dialog('close');
    
}





function update_student_grid(id, o){
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    
            
            //alert(JSON.stringify(json));
    //renabled everything
//    if(json.success)
//    {
//        $(":input").attr("disabled",false);    
//    }

    Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea, #studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').removeAttribute('disabled');
    Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").removeAttribute('disabled');
    
    if(json.qualaward != null)
    {
        if($('.qualAward'))
        {
            $('.qualAward').text(""+json.qualaward.awardvalue+"");
        }
        
    }
    if(json.minqualaward != null)
    {
        if($('#minAward'))
        {
            $('#minAward').text(""+json.minqualaward.awardvalue+"");
        }
    }
    if(json.maxqualaward != null)
    {
        if($('#maxAward'))
        {
            $('#maxAward').text(""+json.maxqualaward.awardvalue+"");
        }
    }
    if(json.unitaward != null)
    {
        var uAward = Y.one('#unitAwardEdit_'+json.unitaward.unitid+'_'+json.unitaward.studentid);
        if(uAward)
        {
            var options = uAward.get("options");
            options.each(function(option){
                if(option.getAttribute('value') == json.unitaward.awardid)
                {
                    option.set('selected', 'selected'); 
                }
            });   
        }
        else
        {
            var uAward = Y.one('#unitAwardAdv_'+json.unitaward.unitid+'_'+json.unitaward.studentid);
            if(uAward)
            {
                uAward.set('innerHTML',json.unitaward.awardvalue);
            }
        }
        
        var uPoints = Y.one("#unitPoints_"+json.unitaward.unitid);
        if (uPoints !== null)
        {
            uPoints.set('innerHTML', json.unitaward.points);
        }
        
        //then we need to change its selected value.
    }
    
    if (json.percentage != null)
    {
            
        var percentText = Y.one('#U'+json.percentage.unitid+'S'+json.percentage.studentid+'PercentText');
        var percentDiv = Y.one('#U'+json.percentage.unitid+'S'+json.percentage.studentid+'PercentParent');
        
        if (percentText)
        {
            percentText.set('innerHTML', json.percentage.percent + '%');
        }
        
        if (percentDiv){
            percentDiv.setAttribute('title', json.percentage.percent + '% Complete');
        }
        
        document.getElementById('U'+json.percentage.unitid+'S'+json.percentage.studentid+'PercentComplete').style.width = json.percentage.percent + '%';
            
    }
    
    //now renable everything
    applyTT();
    applyStudentTT();
    //update the unit award
    //update the qual award
    //update the ticks
    
}






function update_unit_grid(id, o){
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
        
            //alert(JSON.stringify(json));
    //renabled everything
//    if(json.success)
//    {
//        $(":input").attr("disabled",false);    
//    }

    Y.all('#unitGridDiv select, #unitGridDiv input, #unitGridDiv textarea, #studentGridDiv select, #studentGridDiv input, #studentGridDiv textarea').removeAttribute('disabled');
    Y.all("#popUpContent select, #popUpContent input, #popUpContent textarea").removeAttribute('disabled');
    
    if(json.qualaward != null)
    {
        if($('#qualAward_'+json.studentid))
        {
            $('#qualAward_'+json.studentid).text(""+json.qualaward.awardvalue+"");
        }
        
    }
    
    
    if(json.unitaward != null)
    {
        var uAward = Y.one('#uAw_'+json.unitaward.studentid);
        if(uAward)
        {
            var options = uAward.get("options");
            options.each(function(option){
                if(option.getAttribute('value') == json.unitaward.awardid)
                {
                    option.set('selected', 'selected'); 
                }
            });   
        }
        else
        {
            var uAward = Y.one('#unitAwardAdv_'+json.unitaward.studentid);
            if(uAward)
            {
                uAward.set('innerHTML',json.unitaward.awardvalue);
            }
        }
       
        
        //then we need to change its selected value.
    }
    
    if (json.percentage != null)
    {
            
        var percentText = Y.one('#U'+json.percentage.unitid+'S'+json.percentage.studentid+'PercentText');
        var percentDiv = Y.one('#U'+json.percentage.unitid+'S'+json.percentage.studentid+'PercentParent');
        
        if (percentText)
        {
            percentText.set('innerHTML', json.percentage.percent + '%');
        }
        
        if (percentDiv){
            percentDiv.setAttribute('title', json.percentage.percent + '% Complete');
        }
        
        document.getElementById('U'+json.percentage.unitid+'S'+json.percentage.studentid+'PercentComplete').style.width = json.percentage.percent + '%';
            
    }
    
    //now renable everything
    applyTT();
    applyUnitTT();
    //update the unit award
    //update the qual award
    //update the ticks
    
}





function refreshParentCriteriaLists()
{
    
    var names = new Array();
    $('.critNameInput').each( function(){
        
        names.push( $(this).val() );
        
    } );
    
    // Loop through the select menus
    $('.parent_criteria_select').each( function(){
        
        var current = $(this).val();
        $(this).html('');
        
        var select = $(this);
        
        $(this).append('<option value="">N/A</option>');
        
        $.each(names, function(i, e){
            
            $(select).append('<option value="'+e+'">'+e+'</option>');
            
        });
        
        if (names.indexOf(current) > -1){
            $(this).val(current);
        }
        
    } );
    
}


function applyCritNameBlurFocus()
{
    
    var critNames = $('.critNameInput');
    if (critNames.length > 0){
        
        var parSelects = $('.parent_criteria_select');
        if (parSelects.length > 0){
            
            $('.critNameInput').off('focus');
            $('.critNameInput').on('focus', function(){
                
                critNameSwitchThis = $(this).val();
                
            });
            
            $('.critNameInput').off('blur');
            $('.critNameInput').on('blur', function(){
                
                var critval = $(this).val();
                critval = critval.replace(/[^0-9a-z- \. \/]/ig, '');
                $(this).val(critval);
                                
                $.each(parSelects, function(){
                    
                    var options = $(this).children();
                    $.each(options, function(i, o){
                        
                        if ($(o).val() == critNameSwitchThis){
                            $(o).val( critval );
                            $(o).text( critval );
                        }
                        
                    });
                    
                    
                });
                
                critNameSwitchThis = null;
                
            });
            
        }
        
    }
    
}



/**
 * Dynamically add a new criterion to the table
 * @returns {undefined}
 */
function addNewCriterion()
{

    numOfCriterion++;
    dynamicNumOfCriterion++;

    var d = dynamicNumOfCriterion;

    var parentDiv = $('#criteriaHolder');
    var newSection = '';
    
    newSection += '<tr id="criterionRow_'+d+'">';
        newSection += '<td><input type="hidden" name="criterionIDs['+d+']" value="-1" /><input type="text" placeholder="Name" name="criterionNames['+d+']" value="C'+numOfCriterion+'" class="critNameInput" id="critName_'+d+'" /></td>';
        newSection += '<td><textarea placeholder="Criteria Details" name="criterionDetails['+d+']" id="criterionDetails'+d+'" class="critDetailsTextArea"></textarea></td>';
        newSection += '<td><input title="Weighting" type="text" class="w40" name="criterionWeights['+d+']" value="1.00" /></td>';
        newSection += '<td class="align-l"><input type="radio" name="criterionGradings['+d+']" value="PMD" checked /> Pass, Merit, Distinction<br><input type="radio" name="criterionGradings['+d+']" value="PCD" /> Pass, Credit, Distinction<br><input type="radio" name="criterionGradings['+d+']" value="P" /> Pass Only<br><input type="radio" name="criterionGradings['+d+']" value="DATE" /> Date<br><input type="radio" name="criterionGradings['+d+']" value="TEXT" /> Free Text</td>';
        newSection += '<td><input type="text" class="w40" name="criterionOrders['+d+']" value="'+numOfCriterion+'" /></td>';
        newSection += '<td><select class="parent_criteria_select" name="criterionParents['+d+']"><option value=""></option></select></td>';
        newSection += '<td><a href="#" onclick="removeCriterionTable('+d+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" /></a></td>';
    newSection += '</tr>';

    parentDiv.append( newSection );
    refreshParentCriteriaLists();
    applyCritNameBlurFocus();

}

function addNewHBNVQTask()
{
    
    numOfTasks++;
    dynamicNumOfTasks++;

    var d = dynamicNumOfTasks;
    var parentDiv = $('#criteriaHolder');
    var newSection = '';
    
    newSection += '<tr class="taskRow_'+d+'">';
        newSection += '<td><input type="hidden" name="taskIDs['+d+']" value="-1" /><input type="text" placeholder="Name" name="taskNames['+d+']" value="T'+numOfTasks+'" class="critNameInput" id="taskName_'+d+'" /></td>';
        newSection += '<td><textarea style="width:100%;" placeholder="Task Details" name="taskDetails['+d+']" id="taskDetails'+d+'" class="critDetailsTextArea"></textarea></td>';
        newSection += '<td><input type="text" readonly="true" name="taskTargetDates['+d+']" class="bcgtDatePicker" /> </td>';
        newSection += '<td><input type="text" class="w40" name="taskOrders['+d+']" value="'+numOfTasks+'" /></td>';
        newSection += '<td><a href="#" onclick="removeTaskTable('+d+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" /></a></td>';
    newSection += '</tr>';
    
    // Outcome & Descriptive criteria row
    newSection += '<tr class="taskRow_'+d+'">';
        newSection += '<td colspan="5" class="cgHBNVQOutcomeCriteriaCell">';
        
            newSection += '<table id="Task_'+d+'_OutcomeTable" class="criteriaOutcomeTable">';
            
                newSection += '<tr><th><a style="vertical-align:top;" href="#" onclick="addNewHBNVQOutcome('+d+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" /></a> Outcome</th><th>Descriptive Criteria</th></tr>';
                
            newSection += '</table>';
        
        newSection += '</td>';
    newSection += '</tr>';
    
    
    // Sub criteria row - E3, E4 criteria
    newSection += '<tr class="subCriteriaRow taskRow_'+d+'">';
        newSection += '<td colspan="5" class="cgHBNVQSubCriteriaCell">';
        
            newSection += '<table id="Task_'+d+'_SubCriteriaTable" class="criteriaSubCriteriaTable">';
            
                newSection += '<tr><th colspan="3"><a style="vertical-align:top;" href="#" onclick="addNewHBNVQSubCriteria('+d+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" /></a> Sub Criteria</th></tr>';
                
            newSection += '</table>';
        
        newSection += '</td>';
    newSection += '</tr>';
    
    newSection += '<tr class="sepRow"><td>&nbsp;</td></tr>';

    
    
    parentDiv.append( newSection );
    
    $('.bcgtDatePicker').datepicker( {
        dateFormat: "dd-mm-yy"
    } );
    
}

function addNewHBNVQOutcome(pid)
{
    
    overallNumOutcomes++;
    
    if (arrayOfOutcomes[pid] == undefined){
        arrayOfOutcomes[pid] = new Array();
    }
    
    var num = overallNumOutcomes;
    
    // Add to array
    arrayOfOutcomes[pid].push(num);
    
     // We want to call them O1, O2, O3, etc... so use the count of the array
    var oID = arrayOfOutcomes[pid].length;
    
    var newSection = '';
    
    newSection += '<tr id="outcomeRow_'+num+'">';
    
        // Outcome cell
        newSection += '<td style="width:40%;">';
            newSection += '<table>';
            
                newSection += '<tr>';
                    newSection += '<td>Name</td>';
                    newSection += '<td><input type="hidden" name="outcomesIDs['+pid+']['+num+']" value="-1" /><input type="text" name="outcomeNames['+pid+']['+num+']" value="Outcome '+oID+'" class="rangeInput" /></td>';
                newSection += '</tr>';
                
                newSection += '<tr>';
                    newSection += '<td>Details</td>';
                    newSection += '<td><textarea name="outcomeDetails['+pid+']['+num+']"></textarea></td>';
                newSection += '</tr>';
                
                newSection += '<tr>';
                    newSection += '<td>Target Date</td>';
                    newSection += '<td><input type="text" class="bcgtDatePicker" name="outcomeDates['+pid+']['+num+']" /></td>';
                newSection += '</tr>';
                
                newSection += '<tr>';
                    newSection += '<td>No. Observations</td>';
                    newSection += '<td><input type="number" name="outcomeNumOfObservations['+pid+']['+num+']" min="1" max="'+MAX_OBSERVATIONS_ON_OUTCOME+'" style="width:30px;" value="1" onblur="checkCCNum(this);return false;" /> <small class="output" style="color:red;"></small></td>';
                newSection += '</tr>';
                
                newSection += '<tr>';
                    newSection += '<td>Descriptive Criteria</td>';
                    newSection += '<td><a href="#" onclick="addNewHBNVQDescCriteria('+pid+', '+num+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" alt="add" /></a></td>';
                newSection += '</tr>';
                
            newSection += '</table>';
        newSection += '</td>';
        
        
        // Criteria cell
        newSection += '<td>';
            newSection += '<table id="taskCriteriaTable_'+pid+'_'+num+'">';
            
                
            
            newSection += '</table>';
        newSection += '</td>';
    
    newSection += '</tr>';
    
    $('#Task_'+pid+'_OutcomeTable').append(newSection);
    
    $('.bcgtDatePicker').datepicker( {
        dateFormat: "dd-mm-yy"
    } );
    
}

function addNewHBNVQSubCriteria(pid)
{
    
    overallNumSubCriteria++;
    
    if(arrayOfSubCriteria[pid] == undefined){
        arrayOfSubCriteria[pid] = new Array();
    }
    
    var num = overallNumSubCriteria;
    
    arrayOfSubCriteria[pid].push(num);
    
    var cID = String.fromCharCode( 96 + arrayOfSubCriteria[pid].length );
    
    var newSection = '';
    newSection += '<tr id="subCriteriaRow_'+pid+'_'+num+'">';
        newSection += '<td><input type="hidden" name="subCritIDs['+pid+']['+num+']" value="-1" /><input type="text" name="subCritNames['+pid+']['+num+']" value="'+cID+'" class="critNameInput" /></td>';
        newSection += '<td><input type="text" placeholder="Details..." style="width:350px;" name="subCritDetails['+pid+']['+num+']" /></td>';
        newSection += '<td><input type="checkbox" name="subCritMarkable['+pid+']['+num+']" value="1" /></td>';
    newSection += '</tr>';
    
    $('#Task_'+pid+'_SubCriteriaTable').append(newSection);
    
}


function addNewHBNVQDescCriteria(pid, oid)
{
    
    overallNumDescCrit++;
    
    if(arrayOfDescCriteria[pid] == undefined){
        arrayOfDescCriteria[pid] = new Array();
    }

    if(arrayOfDescCriteria[pid][oid] == undefined){
        arrayOfDescCriteria[pid][oid] = new Array();
    }
    
    var num = overallNumDescCrit;
    
    // Add that unique ID to the array for this task/subtask
    arrayOfDescCriteria[pid][oid].push(num);
    
     // We want to call them a, b, c, etc... so use the count of the array
    var cID = String.fromCharCode( 96 + arrayOfDescCriteria[pid][oid].length );
        
    var newSection = '';
    newSection += '<tr id="outcomeCriteriaRow_'+oid+'_'+num+'">';
        newSection += '<td><input type="hidden" name="descCritIDs['+pid+']['+oid+']['+num+']" value="-1" /><input type="text" name="descCritNames['+pid+']['+oid+']['+num+']" value="'+cID+'" class="critNameInput" /></td>';
        newSection += '<td><input type="text" placeholder="Details..." style="width:350px;" name="descCritDetails['+pid+']['+oid+']['+num+']" /></td>';
    newSection += '</tr>';
    
    $('#taskCriteriaTable_'+pid+'_'+oid).append(newSection);
    
    
}

function addNewHBVRQTask()
{
    
    numOfTasks++;
    dynamicNumOfTasks++;

    var d = dynamicNumOfTasks;

    var parentDiv = $('#criteriaHolder');
    var newSection = '';
    
    newSection += '<tr class="taskRow_'+d+'">';
        newSection += '<td><input type="hidden" name="taskIDs['+d+']" value="-1" /><input type="text" placeholder="Name" name="taskNames['+d+']" value="T'+numOfTasks+'" class="critNameInput" id="taskName_'+d+'" /></td>';
        newSection += '<td><select onchange="changeCriterionTypeVRQ(this.value, '+d+');return false;" name="taskTypes['+d+']"><option value="Summative">Summative</option><option value="Formative">Formative</option><option value="L1">Level 1 - PMD</option></select>';
        newSection += '<td><textarea style="width:100%;" placeholder="Task Details" name="taskDetails['+d+']" id="taskDetails'+d+'" class="critDetailsTextArea"></textarea></td>';
        newSection += '<td><input type="text" readonly="true" name="taskTargetDates['+d+']" class="bcgtDatePicker" /> </td>';
        newSection += '<td><input type="text" class="w40" name="taskOrders['+d+']" value="'+numOfTasks+'" /></td>';
        newSection += '<td><a href="#" onclick="removeTaskTable('+d+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" /></a></td>';
    newSection += '</tr>';
    
    // Observation row
    newSection += '<tr class="taskRow_'+d+'">';
        newSection += '<td colspan="6">';
            newSection += loadHBVRQSummativeBox(d);
        newSection += '</td>';
    newSection += '</tr>';
    
    
    parentDiv.append( newSection );
    
    $('.bcgtDatePicker').datepicker( {
        dateFormat: "dd-mm-yy"
    } );
    
}

function loadHBVRQSummativeBox(d){
    
    var newSection = '';
    newSection += '<table id="Task_'+d+'_ObservationsTable" class="criteriaObservationTable">';
            
        newSection += '<tr id="buttonRow_'+d+'">';
            newSection += '<td></td>';
            newSection += '<td><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" title="Add new criteria" alt="Add new criteria" onclick="addNewHBVRQCriteria('+d+');" /></td>';
            newSection += '<td><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" title="Add new observation" alt="Add new observation" onclick="addNewHBVRQObservation('+d+');" /></td>';
        newSection += '</tr>';

        newSection += '<tr id="observationRow_'+d+'">';
            newSection += '<td></td>';
            newSection += '<td>Criteria</td>';
            newSection += '<td>Observations</td>';
        newSection += '</tr>';

        newSection += '<tr id="conversionChartRow_'+d+'">';
            newSection += '<td></td>';
            newSection += '<td></td>';
            newSection += '<td>Conversion Chart</td>';
        newSection += '</tr>';

    newSection += '</table>';
    
    return newSection;
    
}

function loadHBVRQFormativeBox(d){
    
    var newSection = '';
    
    newSection += '<table id="Task_'+d+'_FormativeTable" class="criteriaObservationTable">';
    
        newSection += '<tr>';
            newSection += '<th><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" title="Add new formative criteria" alt="Add new formative criteria" onclick="addHBVRQFormativeCriteria('+d+');" /></th>';
            newSection += '<th>Name</th>';
            newSection += '<th>Description</th>';
            newSection += '<th></th>';
        newSection += '</tr>';
                   
    newSection += '</table>';
    
    return newSection;
    
}

function addHBVRQFormativeCriteria(d){
    
    var n = numOfFormativeCriteria;
    
    var newSection = '';
    
    newSection += '<tr id="formativeCriteriaRow_'+d+'_'+n+'">';
        newSection += '<td></td>';
        newSection += '<td><input type="hidden" name="formativeCriteriaIDs['+d+']['+n+']" value="-1" /><input type="text" name="formativeCriteriaNames['+d+']['+n+']" placeholder="Name" /></td>';
        newSection += '<td><input type="text" name="formativeCriteriaDescs['+d+']['+n+']" placeholder="Details" class="long" /></td>';
        newSection += '<td><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" title="Remove formative criteria" alt="Remove formative criteria" onclick="removeHBVRQFormativeCriteria('+d+', '+n+');" /></td>';
    newSection += '</tr>';
        
    $('#Task_'+d+'_FormativeTable').append( newSection );
    
    numOfFormativeCriteria++;
    
}

function removeHBVRQFormativeCriteria(d, n){
    $('#formativeCriteriaRow_'+d+'_'+n).remove();
}

function addNewHBNVQSignOffSheet()
{
    
    dynamicSignOffID++;
    numOfSignOffSheets++;
    
    arrayOfSheetRanges[dynamicSignOffID] = new Array();
    
    var newSection = '';
    
    newSection += '<tr>';
        newSection += '<td><input type="hidden" name="sheetIDs['+dynamicSignOffID+']" value="-1" /><input type="text" name="sheetNames['+dynamicSignOffID+']" value="'+numOfSignOffSheets+'. Sign-Off Sheet" /></td>';
        newSection += '<td><input type="number" min="1" max="'+MAX_OBSERVATIONS_ON_OUTCOME+'" name="sheetNumObs['+dynamicSignOffID+']" style="width:30px;" /></td>';
        newSection += '<td id="signoffSheetRangeCell_'+dynamicSignOffID+'"><a href="#" onclick="addNewSignOffRange('+numOfSignOffSheets+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/plus.png" alt="add" /></a> <table id="signoffSheetRangeHolder_'+numOfSignOffSheets+'" class="signoffSheetRangeTable"></table></td>';
    newSection += '</tr>';
    
    $('#signoffSheetHolder').append(newSection);
    
}

function addNewSignOffRange(pid)
{
    
    numOfSheetRanges++;
    var num = numOfSheetRanges;
    
    arrayOfSheetRanges[pid].push(num);
    
    var rID = arrayOfSheetRanges[pid].length;
    
    var newSection = '';
    newSection += '<tr>';
        newSection += '<td>Range Name:</td>';
        newSection += '<td><input type="hidden" name="rangeIDs['+pid+']['+num+']" value="-1" /><input type="text" name="rangeNames['+pid+']['+num+']" value="'+rID+'. Range" /></td>';
    newSection += '</tr>';
    
    
    $('#signoffSheetRangeHolder_'+pid).append(newSection);
    
}



/**
 * Add a new criteria to a task
 * @param {type} pid
 * @returns {undefined}
 */
function addNewHBVRQCriteria(pid)
{

    // Add to overall
    overallNumCRCriteria++;

    if(arrayOfCRCriteria[pid] == undefined){
        arrayOfCRCriteria[pid] = new Array();
    }

    var num = overallNumCRCriteria;

    // Add that unique ID to the array for this task/subtask
    arrayOfCRCriteria[pid].push(num);

    // We want to call them C1, C2, C3, etc... so use the count of the array
    var critID = arrayOfCRCriteria[pid].length;

    var newRow = '';
    newRow += '<tr id="taskCriteriaRow_'+pid+'_'+num+'"><td class="blank_cell_left small_cell"><a href="#" onclick="deleteHBVRQCriteria('+pid+', '+num+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/close.png" class="small" /></a></td><td><input type="hidden" name="taskCritIDs['+pid+']['+num+']" value="-1" /><input type="text" name="taskCritNames['+pid+']['+num+']" value="'+critID+'. Criteria" title="" class="observationCritInput hoverTitle" onkeyup="reloadHoverTitles();" /></td><td></td></tr>';

    $('#Task_'+pid+'_ObservationsTable').append( newRow );    

    // If there are any range rows already added, loop through and add additional TDs for them
    if( arrayOfCRObservation[pid] != undefined )
    {

        $.each(arrayOfCRObservation[pid], function(ind, val){
            var newCell = '<td class="C'+num+' Ob'+val+' c">'+buildObservationOptions(pid, num, val)+'</td>';
            $('#taskCriteriaRow_'+pid+'_'+num+'').append(newCell);
        });
    }

}



/**
 * Adding a new observation
 * @param {type} pid
 * @returns {undefined}
 */
function addNewHBVRQObservation(pid)
{

    // Add to overall
    overallNumCRObservation++;

    if(arrayOfCRObservation[pid] == undefined){
        arrayOfCRObservation[pid] = new Array();
    }

    var num = overallNumCRObservation;

    // Add to array
    arrayOfCRObservation[pid].push(num);

    // We want to call them O1, O2, O3, etc... so use the count of the array
    var obID = arrayOfCRObservation[pid].length;

    var newCol = '';
    newCol += '<td class="Rng'+num+'"><input type="hidden" name="taskObservationIDs['+pid+']['+num+']" value="-1" /><input type="text" name="taskObservationNames['+pid+']['+num+']" value="'+obID+'. Observation" class="rangeInput hoverTitle" onkeyup="reloadHoverTitles();" /></td>';

    // Add to range row
    $('#observationRow_'+pid).append(newCol);

    // Add delete button to criteria header row
    var deleteCol = '<td class="c noBorder Rng'+num+'"><a href="#" onclick="deleteHBRVQObservation('+pid+', '+num+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/close.png" class="small" /></a></td>';
    $('#buttonRow_'+pid).append(deleteCol);

    // Add conversion chart for observation
    var ccCol = '<td class="c Ob'+num+'"><table class="smalltext all_c"><tr class="b"><td>Grade</td><td title="Minimum marks required for this grade">Marks</td></tr><tr><td>Pass</td><td><input id="observationCC_P_'+num+'" type="text" class="tinyInput" name="taskObservationCC['+pid+']['+num+'][P]" onblur="checkCCNum(this);" /></td></tr><tr><td>Merit</td><td><input id="observationCC_M_'+num+'" type="text" class="tinyInput" name="taskObservationCC['+pid+']['+num+'][M]" onblur="checkCCNum(this);" /></td></tr><tr><td>Distinction</td><td><input id="observationCC_D_'+num+'" type="text" class="tinyInput" name="taskObservationCC['+pid+']['+num+'][D]" onblur="checkCCNum(this);" /></td></tr></table><small class="output" style="color:red;"></small><br><small>Target Date:</small><br><input type="text" name="taskObservationTargetDates['+pid+']['+num+']" value="" class="bcgtDatePicker" /></td>';
    $('#conversionChartRow_'+pid).append(ccCol);

    // If there are any criteria rows already added, loop through and add additional TDs for them
    if( arrayOfCRCriteria[pid] != undefined )
    {

        $.each(arrayOfCRCriteria[pid], function(ind, val){
            var newCell = '<td class="C'+val+' Ob'+num+' c">'+buildObservationOptions(pid, val, num)+'</td>';
            $('#taskCriteriaRow_'+pid+'_'+val+'').append(newCell);
        });

    }

    $('.bcgtDatePicker').datepicker( {
        dateFormat: "dd-mm-yy"
    } );

}



function buildObservationOptions(pid, cid, rid)
{
    var output = '';
    output += '<select class="tinySelect" name="taskCriteriaObservationPoints['+pid+'][C'+cid+'|O'+rid+']" title="Please select the maximum number of points the student can achieve for this criteria on this observation, between 0-'+observationMaxPoints+'">';
        for(var i = 0; i <= observationMaxPoints; i++)
        {
            var sel = (i === 1) ? "selected" : "";
            output += '<option value="'+i+'" '+sel+'>'+i+'</option>';
        }
    output += '</select>';
    return output;
}



function checkCCNum(input)
{
    // Check value is whole number > 0
    var val = $(input).val();
    if( !( parseInt(val, 10) == val && val > 0 ) ){
        $(input).parents('table').next('.output').text("Not a valid number");
        $(input).css('border', '2px solid red');
    }
    else
    {
        $(input).parents('table').next('.output').text("");
        $(input).css('border', 'none');
    }
}


function reloadHoverTitles()
{
    $('.hoverTitle').each( function(i){
        $(this).attr('title', $(this).val());
    });
}

/**
 * Remove a dynamically created criterion table
 * @param {type} id
 * @returns {undefined}
 */
function removeCriterionTable(id)
{
    numOfCriterion--;
    $('#criterionRow_'+id).remove();
}

/**
 * Remove a dynamically created task table
 * @param {type} id
 * @returns {undefined}
 */
function removeTaskTable(id)
{
    numOfTasks--;
    $('.taskRow_'+id).remove();
}


function deleteHBVRQCriteria(pid, cid)
{
    $('#taskCriteriaRow_'+pid+'_'+cid).remove();
    // Remove from array
    arrayOfCRCriteria[pid] = $.grep(arrayOfCRCriteria[pid], function(val, ind){
        return (val < cid || val > cid);
    });
}

function deleteHBRVQObservation(pid, rid)
{
    $('.Ob'+rid).remove();
    // Remove from array
    arrayOfCRObservation[pid] = $.grep(arrayOfCRObservation[pid], function(val, ind){
        return (val < rid || val > rid);
    });
}

function toggleOverallTasks(name)
{
    $('.taskClass_'+name+', .taskHidden_'+name).toggle();
    if ( $('.taskClass_'+name).css('display') == 'none' ){
        $('.toggleTD_'+name).attr('colspan', '1');
    }
    else
    {
        var colspan = $('.toggleTD_'+name).attr('defaultcolspan');
        $('.toggleTD_'+name).attr('colspan', colspan);
    }
}

function loadObservationPopup(id, studentID, qualID, grid)
{
    
    if (useThisStudentIDForPopUp != null){
        studentID = useThisStudentIDForPopUp;
    }
    
    var params = {
        rangeID: id,
        studentID: studentID,
        qualID: qualID,
        grid: grid
    }
    
    callPopUp("observation", params);
    
}

function loadOutcome(id, qualID, unitID, studentID, grid)
{
    var params = {
        criteriaID: id,
        unitID: unitID,
        studentID: studentID,
        qualID: qualID,
        grid: grid
    }
    
    callPopUp("outcome", params);
}

function loadSubCriteria(id, qualID, unitID, studentID, grid)
{
    
    var params = {
        criteriaID: id,
        unitID: unitID,
        studentID: studentID,
        qualID: qualID,
        grid: grid
    }
    
    callPopUp("sub_criteria", params);
        
}

function loadSignOffSheets(studentID, unitID, qualID, sheetID)
{
    var params = {
        studentID: studentID,
        unitID: unitID,
        sheetID: sheetID,
        qualID: qualID
    }
    
    callPopUp("signoff", params);
}

function callPopUp(type, params)
{
    $('#loading').show();
    $.post(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/ajax/popUpScript.php', {type: type, params: params}, function(data){
        eval(data);
        applyTT();
    });
}


function updateRangeCriteria(value, studentID, qualID, criteriaID, rangeID, unitID, grid)
{
    var params = {
        studentID: studentID,
        qualID: qualID,
        criteriaID: criteriaID,
        rangeID: rangeID,
        unitID: unitID,
        value: value,
        grid: grid
    }   
    update('updateRangeCriteriaAward', params);    
}


var popup = "";
popup = {

    open : function(){
        css_popup("genericPopup");
    },

    close : function(){
        $("#bcgtblanket").css("display", "none");
        $("#genericPopup").css("display", "none");      
    },

    set_title : function(t){
        $('#popUpTitle').text(t);
    },

    set_sub_title : function(t){
        $('#popUpSubTitle').html(t);
    },

    set_content : function(c){
        $('#popUpContent').html(c);
    }

};

function updateSignOffRangeObservation(studentID, qualID, unitID, sheetID, rangeID, observationNum, input)
{
      var params = {
            studentID: studentID,
            qualID: qualID,
            unitID: unitID,
            sheetID: sheetID,
            rangeID: rangeID,
            observationNum: observationNum,
            value: ($(input).is(':checked')) ? 1 : 0
        }
        
        update('updateSignOffRangeObservation', params);  
}

// Attribute not setting
function updateUserSetting(obj, studentID, qualID)
{

    var setting = obj.name;
    var val;

    if( $(obj).attr('type') == 'checkbox' )
    {
        if(obj.checked == true) val = 1;        
        else val = 0;
    }
    else
    {
        val = obj.value;
    }

    updateUserQualAttribute(setting, val, studentID, qualID);


}

function updateQualAttribute(obj, qualID)
{
    var attribute = obj.name;
    var val;
    if( $(obj).attr('type') == 'checkbox' )
    {
        if(obj.checked == true) val = 1;        
        else val = 0;
    }
    else
    {
        val = obj.value;
    }

    var params = {
        attribute: attribute,
        value: val,
        qualID: qualID
    }

    update('updateQualAttribute', params);

}

function updateUserQualAttribute(attribute, value, studentID, qualID)
{

    var params = {
        attribute: attribute,
        value: value,
        studentID: studentID,
        qualID: qualID
    }

    update('updateQualAttribute', params);

}

function fadeInOut(id)
{
    $('#'+id).fadeIn('slow');
    setTimeout("$('#"+id+"').fadeOut('slow');", 3000);
}

function changeCriterionTypeVRQ(type, id){
    
    if (type == 'Formative'){
        
        $('.taskRow_'+id+':last td').html( loadHBVRQFormativeBox(id) );
        
    } else if (type == 'Summative') {
        
        $('.taskRow_'+id+':last td').html( loadHBVRQSummativeBox(id) );
        
    } else {
        
        $('.taskRow_'+id+':last td').html( '' );
        
    }
    
}


function saveHBVRQFormative(studentID, qualID, unitID, critID){
    
    var el = $('#details_studentID_'+studentID+'_qualID_'+qualID+'_critID_'+critID);
    
    var params = {
        studentID: studentID,
        qualID: qualID,
        unitID: unitID,
        criteriaID: critID,
        value: $(el).val()
    }

    update('updateFormativeDetails', params);
    
}



M.mod_bcgtcg.cgaddactivity = function(Y) {
    //on change of uID
    var unit = Y.one('#uID');
    unit.on('change', function(e){
        Y.one('#addActivity').submit();
    } ); 
    //onchange of aID
    var activity = Y.one('#aID');
    activity.on('change', function(e){
        Y.one('#addActivity').submit();
    } ); 
}

M.mod_bcgtcg.cgmodactivity = function(Y) {
    //on change of uID
    var unitAdd = $('#bcgtAddUnitCG');
    if(unitAdd)
    {
           unitAdd.on("click", function(e){
                e.preventDefault();
                //go and load up the new unit selection. 
                //need the unitid
                //the courseid
                //coursemoduleid
                var courseID = unitAdd.attr('course');
                var courseModuleID = unitAdd.attr('cmid');
                var unitID = $('#nUIDCG').find(":selected").val();
                var span = $('#bcgtloadingcg');
                if(span)
                {
                    span.html('<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
                }
                var data = {
                      method: 'POST',
                      data: {
                          'cID' : courseID, 
                          'uID' : unitID, 
                          'cmID' : courseModuleID
                      },
                      on: {
                          success: update_mod_page
                      }
                  }
                  var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_mod_selection.php";
                  var request = Y.io(url, data);
           }); 
    }
    
    apply_mod_tt();
}

function update_mod_page(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    if(json.retval != null)
    {
        var div = $('#bcgtMODAddUnitSelectionCG');
        if(div)
        {
            div.append(json.retval);
        }
        var unitID = json.unit;
        //set selected index to blank.
        $("select#nUIDCG").prop('selectedIndex', 0);
        //need to disable the option in the drop down. 
        $("select#nUIDCG option[value='"+ unitID + "']").attr('disabled', true ); 
        //need to add the id to the hidden list of units we have selected.
        var unitsSelected = $('#bcgtunitsselectedcg').val() + "_" + unitID;
        $('#bcgtunitsselectedcg').val(unitsSelected)
    }
    var span = $('#bcgtloadingcg');
    if(span)
    {
        span.html('');
    }
    apply_mod_tt();
}

function apply_mod_tt()
{
    //need to listen for deletes etc. 
    var remUnits = $('.remUnit');
    if(remUnits)
    {
        $(remUnits).each(function(index){
            $(this).on('click', function(e){
                e.preventDefault();
                var unitID = $(this).attr('unit');
                var div = $('#bcgtunitMod_'+unitID);
                if(div)
                {
                    $(div).remove();
                }
                
                //also need to add back to drop down.
                //no lets re-enable it. 
                $("select#nUIDCG option[value='"+ unitID + "']").attr('disabled', false); 
                
                //need to remove the id from the hidden list of units we have selected.
                var unitsSelected = $('#bcgtunitsselected').val();
                unitsSelected.replace("_"+unitID,"");
                $('#bcgtunitsselected').val(unitsSelected);
            });
        });   
    }
}

M.mod_bcgtcg.initactivitiescheck = function(Y) {
    $(document).ready(function() {
	var $dialogBoxContent = $('<div id="dialogBoxContent"></div>')
		.dialog({
			autoOpen: false,
			title: 'Gradebook Details',
            modal: true,
            dialogClass: 'bcgtdialogmodcheck'    
		});

	$('.criteriamod').on('click', function(e) {
        //reset the html to the loading symbol. 
        $dialogBoxContent.html('<img class="modalload" src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
		$dialogBoxContent.dialog('open');
		// prevent the default action, e.g., following a link
		e.preventDefault();
        var courseID = $(this).attr('course');
        var unitID = -1;
        var criteriaID = $(this).attr('crit');
        var qualID = $(this).attr('qual');
        var modType = $(this).attr('mod');
        var userID = -1;
        var cmID = -1;
        //this will now go and get the data based onn the attributes through ajax.
        var data = {
            method: 'POST',
            data: {
                'cID' : courseID, 
                'uID' : unitID, 
                'cmID' : cmID,
                'sID' : userID,
                'mod' : modType,
                'qID' : qualID,
                'criteriaID' : criteriaID
            },
            on: {
                success: display_modal
            }
        }
//        alert(JSON.stringify(data));
        var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_mod_details.php";
        var request = Y.io(url, data);
        
	});
});

    //listen for the red x on click:
    var noCriterias = $('.bcgtcritnoass');
    if(noCriterias)
    {
        noCriterias.on('click', function(e){
           var courseID = $(this).attr('course');
           var unitID = $(this).attr('uID');
           var familyID = $(this).attr('fID');
           
           //go to add activity
           var link = M.cfg.wwwroot+'/blocks/bcgt/forms/add_activity.php?'+
                        'page=addact&uID='+unitID+'&cID='+courseID+
                        '&fID='+familyID+'';
           window.location.href = link;
        });
    }
}

function display_modal(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    var div = $('#dialogBoxContent');
    if(div)
    {
        div.html('');    
    }
    if(json.retval != null && div)
    { 
        div.html(json.retval); 
    }
    apply_mod_link_TT();
}

function apply_mod_link_TT()
{
    //listen out for the modal button clicks. 
    
    //close the diaplog
    
    //go to the corect destination
    
    //activity tracker
    //or activity
    
    var actTracking = $('input.acttracking');
    if(actTracking)
    {
        actTracking.on('click', function(e){
        e.preventDefault();
           var courseID = $(this).attr('course');
           var qualID = $(this).attr('qID');
           var cmID = $(this).attr('cmID');
           var groupingID = $(this).attr('grID');
           
           //go to activity grid. 
           var link = M.cfg.wwwroot+'/blocks/bcgt/grids/act_grid.php?cID='+
                        courseID+'&cmID='+cmID+'&grID='+groupingID+'&qID='+qualID;
           window.location.href = link;
        });
    }
    
    var act = $('input.act');
    if(act)
    {
        act.on('click', function(e){
            e.preventDefault();
           var cmID = $(this).attr('cmid');
           var mod = $(this).attr('mod');
           //go to add activity
           //go to add activity
           var link = M.cfg.wwwroot+'/mod/'+mod+'/view.php?'+
                        'id='+cmID+'';
           window.location.href = link;
        });
    }
}





M.mod_bcgtcg.initclassgrid = function(Y, qualID, grid, columnsLocked) {

    var qualID;
    var grid;
    var columnsLocked;
    
    
    $(document).ready( function(){
        
        draw_grid('CGClassGridTable', '', grid, 'class', columnsLocked);
        
        $('#viewsimple').unbind('click');
        $('#viewsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('s');
            
            var page = $('#pageInput').val();
            var courseID = $('#scID').val();
            var groupID = $('#grID').val();
            var flag = '';
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&grID="+groupID;            
            
            $.post(url, function(data){
                draw_grid('CGClassGridTable', data, grid, 'class', columnsLocked);
            });
            
            
        });
        
        
        $('#editsimple').unbind('click');
        $('#editsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('se');
            
            var page = $('#pageInput').val();
            var courseID = $('#scID').val();
            var groupID = $('#grID').val();
            var flag = '';
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&grID="+groupID;            
            
            $.post(url, function(data){
                draw_grid('CGClassGridTable', data, grid, 'class', columnsLocked);
            });
            
            
        });
        
        
        $('.classgridpage').unbind('click');
        $('.classgridpage').bind('click', function(e){
            
            $('#loading').show();
            
            e.preventDefault();
            $('.classgridpage').removeClass('active');
            var page = $(this).attr('page');
            $('#pageInput').val(page);
            
            var flag = '';
            var courseID = $('#scID').val();
            var groupID = $('#grID').val();
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtcg/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&grID="+groupID;            
            
            $.post(url, function(data){
                draw_grid('CGClassGridTable', data, grid, 'class', columnsLocked);
            });
            
            $(this).addClass('active');
            
        });
        
           
        
        var doResize;
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                var g = $('#grid').val();
                draw_grid('CGClassGridTable', '', g, 'class', columnsLocked);
            }, 100 );
        } );
        
        
    } );
    
    
}
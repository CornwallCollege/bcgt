/* 
 * Contains all of the init functions
 */
M.mod_bcgtbespoke = {};


M.mod_bcgtbespoke.bespokeiniteditqual = function(Y) {
    
    var subType = Y.one('#qualSubtype');
    if (subType != null){
        subType.on('change', function(){
            var val = subType.get('value');
            
            if (val != -2){
                Y.one('#custom-sub-type').hide();
            }
            
            // Create custom subtype name
            if (val == -2){
                Y.one('#custom-sub-type').show();
            }
        });
    }
    
    
};

M.mod_bcgtbespoke.bespokeiniteditunit = function(Y) {
    
    var addNewCrit = Y.one('#addNewCrit');
    addNewCrit.on('click', function(e){
        addNewCriterion();
        e.preventDefault();
    });
    
    apply_stuff();
    
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
    
    
//    // if editing, increase the size of the headers
//    if (view === 'se' || view ==='ae'){
//        
//        $('.criteriaName').css('width', '100px');
//        $('.criteriaName').css('min-width', '100px');
//        $('.criteriaName').css('max-width', '100px');
//        
//        $('.criteriaCell').css('width', '100px');
//        $('.criteriaCell').css('min-width', '100px');
//        $('.criteriaCell').css('max-width', '100px');
//        
//        $('#toggleCommentsButton').removeAttr('disabled');
//                
//        $('.unitCommentCell').css('width', '65px');
//        $('.unitCommentCell').css('min-width', '65px');
//        $('.unitCommentCell').css('max-width', '65px');
//        
//    } else {
//        
//        $('.criteriaName').css('width', '40px');
//        $('.criteriaName').css('min-width', '40px');
//        $('.criteriaName').css('max-width', '40px');
//        
//        $('.criteriaCell').css('width', '40px');
//        $('.criteriaCell').css('min-width', '40px');
//        $('.criteriaCell').css('max-width', '40px');
//        
//        $('.unitCommentCell').css('width', '40px');
//        $('.unitCommentCell').css('min-width', '40px');
//        $('.unitCommentCell').css('max-width', '40px');
//        
//        $('#toggleCommentsButton').attr('disabled', 'disabled');
//        
//    }
//        
//    var activityUnits = $('.activityUnitName');
//    $.each(activityUnits, function(){
//        
//        var unitID = $(this).attr('unitID');
//        var cnt = $('.criterionUnit_'+unitID).length;
//        var cWidth = $($('.criterionUnit_'+unitID)[0]).outerWidth();
//        var newWidth = cnt * cWidth;
//        $(this).css('width', newWidth + 'px');
//        $(this).css('min-width', newWidth + 'px');
//        $(this).css('max-width', newWidth + 'px');
//        $(this).css('padding-left', '0px');
//        $(this).css('padding-right', '0px');
//        
//    });
//    
    
    
    
    
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
    apply_grid_stuff();
    
}


M.mod_bcgtbespoke.initstudentgrid = function(Y, qualID, studentID, grid, freezeCols) {
        
    $(document).ready(function(){
        
        draw_grid('bespokeStudentGridTable', '', grid, 'student', freezeCols);    
        
        var doResize;
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                draw_grid('bespokeStudentGridTable', '', grid, 'student', freezeCols);    
            }, 100 );
        } );
        
    });
    
}


M.mod_bcgtbespoke.initunitgrid = function(Y, qualID, unitID, grid, freezeCols) {
        
    $(document).ready(function(){
        
        draw_grid('bespokeUnitGridTable', '', grid, 'unit', freezeCols); 
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
            draw_grid('bespokeUnitGridTable', '', grid, 'unit', freezeCols); 
            }, 100 );
        } );
        
    });
    
}













function toggleAddComments()
{
    
    var button = $('#toggleCommentsButton');
    
    if (button.hasClass('active')){
        button.removeClass('active');
    } else {
        button.addClass('active');
    }
    
    $('.criteriaTDContent').toggle();
    $('.hiddenCriteriaCommentButton').toggle();
    
}

function removeCriterionTable(id)
{

    numOfCriterion--;
    $('#criterionRow_'+id).remove();
    $('.subTableParent_'+id).remove();
    arrayOfSubCriterion[id] = undefined;

}

function removeSubCriterionTable(id, pid)
{
    $('#subCriterionTable_'+pid+'_'+id).remove();
    arrayOfSubCriterion[pid] = $.grep(arrayOfSubCriterion[pid], function(val){
        return (val < id || val > id);
    });
    shiftSubNamesDown(pid);
}

function shiftSubNamesDown(pid)
{
    var i = 1;
    $('.SC_'+pid).each( function(){
        var cName = $(this).text();
        var pName = cName.split(".")[0];
        cName = pName + '.' + i;
        $(this).text(cName);
        i++;
    } );

    var i = 1;
    $('.subName_'+pid).each( function(){
        var cName = $(this).val();
        var pName = cName.split(".")[0];
        cName = pName + '.' + i;
        $(this).val(cName);
        i++;
    } );


}

function shiftSubCriteriaDown(parentID, removedNum)
{
    var count = numOfSubCriterion[parentID];
    var start = parseInt(removedNum) + 1;
    var end = count + 1;

    for(var i = start; i <= end; i++)
    {

        // Shift the sub criteria down by 1

        // Name in the html table > span
        var newName = $('#criteriaNameSpan_Num_'+parentID).html() + '.' + (i-1);
        $('#subCriteriaNameSpan_Num_'+parentID+'_'+i).html(newName);

        // Value in the html hidden input
        $('#hiddenSubNames_'+parentID+'_'+i).val(newName);

        // Now actually fire of an AJAX request to change it in the DB
        // As up till now it's just been on the client side
        var actualID = $('#hiddenSubIDs_'+parentID+'_'+i).val();
        updateCriteriaName(actualID, newName);                                

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

    var parentDiv = $('table#criteriaHolder');
    var newSection = '';
    
    newSection += '<tr id="criterionRow_'+d+'">';
        newSection += '<td><input type="hidden" name="criterionIDs['+d+']" value="-1" /><input type="text" placeholder="Name" name="criterionNames['+d+']" value="C'+numOfCriterion+'" class="critNameInput" id="critName_'+d+'" /></td>';
        newSection += '<td><textarea placeholder="Criteria Details" name="criterionDetails['+d+']" id="criterionDetails'+d+'" class="critDetailsTextArea"></textarea></td>';
        newSection += '<td><input title="Weighting" type="text" class="w40" name="criterionWeights['+d+']" value="1.00" /></td>';
        newSection += '<td class="align-l">-</td>';
        newSection += '<td><select class="parent_criteria_select" name="criterionParents['+d+']"><option value=""></option></select></td>';
        newSection += '<td><select class="criterionGrading" name="criterionGradingStructure['+d+']">'+critGradings+'</select> &nbsp;&nbsp; <small><a href="#" title="Copy to all Criteria" class="copyGradingCriteria"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/images/copy.png" /></a></small></td>';
        newSection += '<td><a href="#" onclick="removeCriterionTable('+d+');return false;"><img src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtcg/pix/redX.png" /></a></td>';
    newSection += '</tr>';

    parentDiv.append( newSection );
    refreshParentCriteriaLists();
    applyCritNameBlurFocus();
    apply_stuff();

}

function loadCriteriaGradingStructureList()
{
    
    
    
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
        
        $(this).append('<option value=""></option>');
        
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

//http://www.youtube.com/watch?v=5_DbNhtN7sw
            
Object.size = function(obj)
{
    var size = 0, key;
    for(key in obj) if (obj.hasOwnProperty(key)) size++;
    return size;
}            
       
       
function update(action, params){
    
    loading();
    $.post(M.cfg.wwwroot + '/blocks/bcgt/plugins/bcgtbespoke/ajax/update.php', {action: action, params: params}, function(d){
        eval(d);
        loading(false);
    }).fail(function(){
        alert('Error: Could not update grid!');
        loading(false);
    });
    
}
       

function apply_grid_stuff(){
    
    $('.criteriaSelect').unbind('change');
    $('.criteriaSelect').change(function(e){
        
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
            qualID: qualID
        };
        
        
        update('update_criteria_value', params);
        
    });
    
    
    
    $('.unitAwardSelect').unbind('change');
    $('.unitAwardSelect').change(function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var unitID = $(this).attr('unitID');
        var qualID = $(this).attr('qualID');
        var grid = $(this).attr('grid');
        
        var params = {
            grid: grid,
            value: value,
            studentID: studentID,
            unitID: unitID,
            qualID: qualID
        };
        
        update('update_unit_award', params);
        
    });
    
    
    $('.qualAwardSelect').unbind('change');
    $('.qualAwardSelect').change( function(e){
        
        var value = $(this).val();
        var studentID = $(this).attr('studentID');
        var qualID = $(this).attr('qualID');
        
        var params = {
            value: value,
            studentID: studentID,
            qualID: qualID
        };
        
        update('update_qual_award', params);
        
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
                        var params = {action: 'add_criteria_comment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments, imgID: imgID} };
                         $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbespoke/ajax/update.php', params, function(data){
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
                        var params = {action: 'add_unit_comment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments, imgID: imgID} };
                        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbespoke/ajax/update.php', params, function(data){
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
    
    
    
    
    
//    
//    $('.addComments').unbind('click');
//    $('.addComments').bind('click', function(){
//             
//        var idAttr = $(this).attr('id');
//        
//        var criteriaID = $(this).attr("criteriaid");
//        var unitID = $(this).attr("unitid");
//        var studentID = $(this).attr("studentid");
//        var qualID = $(this).attr("qualid");
//
//        var username = $(this).attr("username");
//        var name = $(this).attr("fullname");
//        var unitName = $(this).attr("unitname");
//        var critName = $(this).attr("critname");
//        
//        var grid = $(this).attr("grid");
//        
//        cmt.setup(qualID, unitID, criteriaID, studentID, idAttr, grid);
//        cmt.create("popUpDiv", username, name, unitName, critName);
//                
//        
//    } );
//    
//    $('.editComments').unbind('click');
//    $('.editComments').unbind('mouseover');
//    $('.editComments').bind('click', function(){
//                        
//        var idAttr = $(this).attr('id');
//        
//        var criteriaID = $(this).attr("criteriaid");
//        var unitID = $(this).attr("unitid");
//        var studentID = $(this).attr("studentid");
//        var qualID = $(this).attr("qualid");
//
//        var username = $(this).attr("username");
//        var name = $(this).attr("fullname");
//        var unitName = $(this).attr("unitname");
//        var critName = $(this).attr("critname");
//        
//        var grid = $(this).attr("grid");
//        
//        var text = $(this).siblings('.tooltipContent').html();
//        
//        cmt.setup(qualID, unitID, criteriaID, studentID, idAttr, grid);
//        cmt.create("popUpDiv", username, name, unitName, critName, text);
//                
//        
//    } );
//    
//    
//    // Unit Comments
//    $('.addUnitComments').unbind('click');
//    $('.addUnitComments').bind('click', function(){
//        
//        var idAttr = $(this).attr("id");
//        
//        var unitID = idAttr.split('_')[2];
//        var studentID = idAttr.split('_')[4];
//        var qualID = idAttr.split('_')[6];
//
//        var username = $(this).attr("username");
//        var name = $(this).attr("fullname");
//        var unitName = $(this).attr("unitname");
//        var critName = $(this).attr("critname");
//        
//        var grid = $(this).attr("grid");
//        
//        cmt.setup(qualID, unitID, -1, studentID, idAttr, grid);
//        cmt.create("popUpDiv", username, name, unitName, critName);
//                
//        
//    } );
//    
//    
//    $('.editUnitComments').unbind('click');
//    $('.editUnitComments').unbind('mouseover');
//    $('.editUnitComments').bind('click', function(){
//        
//        var idAttr = $(this).attr("id");
//        
//        var unitID = idAttr.split('_')[2];
//        var studentID = idAttr.split('_')[4];
//        var qualID = idAttr.split('_')[6];
//
//        var username = $(this).attr("username");
//        var name = $(this).attr("fullname");
//        var unitName = $(this).attr("unitname");
//        var critName = $(this).attr("critname");
//        
//        var grid = $(this).attr("grid");
//        var text = $(this).siblings('.tooltipContent').html();
//        
//        cmt.setup(qualID, unitID, -1, studentID, idAttr, grid);
//        cmt.create("popUpDiv", username, name, unitName, critName, text);
//                
//        
//    } );
//    
//    
//    $('#commentClose a, #cancelComment').unbind('click');
//    $('#commentClose a, #cancelComment').bind('click', function(){
//        cmt.reset();
//        cmt.cancel();
//    });
//    
//    $('#saveComment').unbind('click');
//    $('#saveComment').bind('click', function(){
//        
//        var comments = encodeURIComponent( $('#commentText').val() );
//        
//        // Criteria Comment
//        if (critID > 0){
//            var params = {action: 'add_criteria_comment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments} };
//        }
//        
//        // Unit Comment
//        else if(critID < 0 && unitID > 0){
//            var params = {action: 'add_unit_comment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments} };
//        }
//        
//        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbespoke/ajax/update.php', params, function(data){
//            eval(data);
//        });
//        
//                
//    });
//    
//    $('#deleteComment').unbind('click');
//    $('#deleteComment').bind('click', function(){
//        
//        $('#commentText').val('');
//        var comments = '';
//        
//         // Criteria Comment
//        if (critID > 0){
//            var params = {action: 'add_criteria_comment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments} };
//        }
//        
//        // Unit Comment
//        else if(critID < 0 && unitID > 0){
//            var params = {action: 'add_unit_comment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments} };
//        }
//        
//        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbespoke/ajax/update.php', params, function(data){
//            eval(data);
//        });
//        
//        
//    });

    
    
    // Unit tooltip
    $('.unitName, .studentUnit').tooltip( {
        
        content: function(){
            
            // Get rid of any open ones
            $('.ui-tooltip').remove();
            
            return $(this).find('div.unitDetailsTooltip').html();
            
        }
        
    });
    
    // Gets the criteria tooltip
    $('.val').tooltip( {
        
        content: function(){
            
            // Get rid of any open ones
            $('.ui-tooltip').remove();
            
            return $(this).find('div.criteriaValueTooltip').html();
            
        }
        
    });
    
    
//     // Set class for background yellow on comments
//    $('.editComments, .editCommentsUnit, .tooltipContent').parents('td').addClass('criteriaComments');
//    $('.editComments, .editCommentsUnit, .tooltipContent').parents('td').attr('title', '');
    // if IE, change button links to have onclick, because as we all know, IE is shit and plays by its own rules
    if (navigator.appName === 'Microsoft Internet Explorer')
    {
        $('a input.btn').click(function(){
            window.location = $(this).closest('a').attr('href');
        });
    }
    
    
    
    
        
    
}

function loading(end){
    
    if (end === false){
        $('#loading').hide();
    } else {
        $('#loading').show();
    }
    
}
       

function apply_stuff(){
    
    $('.deleteCriteria').unbind('click');
    $('.deleteCriteria').bind('click', function(e){
        var l = $(this).attr('level');
        var n = $(this).attr('num');
        removeCriterionTable(l, n);
        e.preventDefault();
    });
    
    $('.addSubCriteria').unbind('click');
    $('.addSubCriteria').bind('click', function(e){
        var p = $(this).attr('parent');
        var n = $(this).attr('num');
        addNewCriterion(p, n);
        e.preventDefault();
    });
    
    $('.copyGradingCriteria').unbind('click');
    $('.copyGradingCriteria').bind('click', function(e){
        var val = $(this).parent().siblings('select').val();
        $('.criterionGrading').val(val);
        e.preventDefault();
    });
        
}




//
//
//var cmt = "";
//var unitID = -1;
//var critID = -1;
//var cellID = "";
//var qualID = -1;
//var studentID = -1;
//var grid = "";
//
//cmt = {
//
//    setup: function(q, u, c, s, id, g){
//        unitID = u;
//        critID = c;
//        cellID = id;
//        studentID = s;
//        qualID = q;
//        grid = g;
//    },
//
//    create : function(div, un, fn, unit, crit, text){ /* Create the popup with the textarea to add a comment */
//
//        /* First assign the values to the spans */
//        $("#commentBoxUsername").html(un);
//        $("#commentBoxFullname").html(fn);
//        $("#commentBoxUnit").html(unit);
//        $("#commentBoxCriteria").html(crit);
//        
//        if (text !== undefined){
//            /* Strip crap from body if only just added */
//            var regex = /<br\s*[\/]?>/gi;
//            text = text.replace(regex, "\n");
//
//            $("#commentText").val(text);
//        }
//
//        
//        css_popup(div); /* Call function from cssPopup.js */
//
//    },
//
//    cancel : function(){ /* Cancel editing/submitting a comment - basically just close the popup */
//
//        /* Reset values */
//        $("#commentBoxUsername").html("");
//        $("#commentBoxFullname").html("");
//        $("#commentBoxUnit").html("");
//        $("#commentBoxCriteria").html("");
//        $("#commentText").val("");
//
//        /* Close Divs */
//        $("#bcgtblanket").css("display", "none");
//        $("#popUpDiv").css("display", "none");
//
//    },
//
//    reset : function(){
//        unitID = -1;
//        critID = -1;
//        cellID = "";
//        studentID = -1;
//        qualID = -1;
//    }
//
//};
//



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

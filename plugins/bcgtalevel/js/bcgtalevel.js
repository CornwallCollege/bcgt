M.mod_bcgtalevel = {};

M.mod_bcgtalevel.aleveliniteditqual = function(Y) {  
    Y.one('#save').set('disabled', 'disabled');
    var alevelSubType = Y.one('#qualSubtype');
    alevelSubType.on('change', function(e){
        var cID = Y.one("#cID").get('value');
        var typeID = -1;
        var qualID = Y.one('#qID').get('value');
        var index = Y.one("#qualFamilySelect").get('selectedIndex');
        var familyID = Y.one("#qualFamilySelect").get("options").item(index).getAttribute('value');
        var index2 = Y.one("#qualSubtype").get('selectedIndex');
        var subTypeID = Y.one("#qualSubtype").get("options").item(index2).getAttribute('value');
        var levelID = Y.one("#qualLevel").get('value');
        self.location='edit_qual.php?fID='+familyID+'&tID='+typeID+'&qID='+qualID+'&level='+levelID+'&subtype='+subTypeID+'&cID='+cID;
    });
    
//    var addUnit = Y.one('#addUnit');
//    addUnit.on('click', function(e){
//        e.preventDefault();
//        //increment the number of units
//        var noUnits = Y.one('#noUnits');
//        var number = noUnits.get('value');
//        var newRowCount = parseInt(number) + 1;
//        noUnits.set('value', newRowCount);
//        //add a new row to the table
//        $('#aleveleUnitsTable tr:last').after('<tr><td><input type=\"text\" name=\"unitName'+newRowCount+'\" value=\"Unit'+newRowCount+'\"/></td><td><input type=\"text\" name=\"unitUMS'+newRowCount+'\"/></td><td><input type=\"button\" value=\"X\" class=\"removeUnit\" name=\"removeUnit\"/></td><td><input type=\"hidden\" value=\"_'+newRowCount+'\" name=\"unitID'+newRowCount+'\"/></td></tr>');
//        
//        add_units_ass(newRowCount);
//        applyTT();
//    });
    
//    $('.addAss').click(function(e){
//        e.preventDefault();
//        var newValue = $('#noAss').val();
//        var newRowCount = parseInt(newValue) + 1;
//        $('#alevelAssTable tr:last').after('<tr><td><input type=\"text\" name=\"assName'+newRowCount+'\" value=\"Ass'+newRowCount+'\"/></td><td><input type=\"text\" class=\"bcgt_datepicker\" name=\"assDate'+newRowCount+'\"/></td><td><select id=\"assUnit'+newRowCount+'\" class=\"assUnit\" name=\"assUnit'+newRowCount+'\"><option value=\"-1\">No Unit</option></select></td><td><input type=\"button\" value=\"X\" class=\"removeAss\" name=\"removeAss\"/></td><td><input type=\"hidden\" value=\"_'+newRowCount+'\" name=\"assID'+newRowCount+'\"/></td></tr>');
//        $('#noAss').val(newRowCount);
//        applyTT();
//        add_select_options(newRowCount);
//    });	
    
    
    $('.addWeight').click(function(e){
        e.preventDefault();
        var newValue = $('#noWeights').val();
        var newRowCount = parseInt(newValue) + 1;
        $('#alevelWeightTable tr:last').after('<tr><td><input type=\"text\" name=\"weightNo'+newRowCount+'\" value=\"'+newRowCount+'\"/></td><td><input type=\"text\" name=\"weightPec'+newRowCount+'\"/></td><td><input type=\"text\" name=\"weightCoef'+newRowCount+'\"/></td><td><input type=\"checkbox\" class=\"weightingCoef\" name=\"targetCoef\"/></td><td><input type=\"button\" value=\"X\" class=\"removeAss\" name=\"removeWeighting\"/></td><td><input type=\"hidden\" value=\"_'+newRowCount+'\" name=\"weightID'+newRowCount+'\"/></td></tr>');
        $('#noWeights').val(newRowCount);
        applyTT();
    });	
    applyTT();
    
    var name = Y.one('#qualName');
    $('#qualName').unbind('keypress');
    name.on('keypress', function(e){
        check_alevel_edit_qual_valid();
    });
    
    check_alevel_edit_qual_valid();
};

function check_alevel_edit_qual_valid()
{
    //get the level and subtype and name
    var subtypeIndex = Y.one('#qualSubtype').get('selectedIndex');
    var subtype = Y.one("#qualSubtype").get("options").item(subtypeIndex).getAttribute('value');
    var name = Y.one('#qualName').get('value');
    if(name != '' && subtype != -1)
    {
        Y.one('#save').set('disabled', '');
    }
}

M.mod_bcgtalevel.aleveliniteditunit = function(Y) {   
    
    $('.addAss').click(function(e){
        e.preventDefault();
        var newValue = $('#noAss').val();
        var newRowCount = parseInt(newValue) + 1;
        $('#alevelAssTable tr:last').after('<tr><td><input type=\"text\" name=\"assName'+newRowCount+'\" value=\"Ass'+newRowCount+'\"/></td><td><input type=\"text\" name=\"assDetails'+newRowCount+'\"/></td><td><input type=\"button\" value=\"X\" class=\"removeAss\" name=\"removeAss\"/></td><td><input type=\"hidden\" value=\"'+newRowCount+'\" name=\"assID'+newRowCount+'\"/></td></tr>');
        $('#noAss').val(newRowCount);
        applyTT();
    });
    
    applyTT();
};

function applyTT()
{
    // Destroy datepickers and recreate them
    $(document).ready( function () {
        $('.bcgt_datepicker').datepicker( {dateFormat: 'dd-mm-yy', changeMonth: true, changeYear: true} );
    });
    
    $('.removeUnit').unbind('click');
    $('.removeUnit').click(function(e){
        e.preventDefault();
        //remove the current row
        var unitName = $(this).closest('tr').children('td:first').children('input').val();
        $(this).parents('tr').remove();
        //also change the number of the units
        var noUnits = Y.one('#noUnits');
        var number = noUnits.get('value');
        var newRowCount = (parseInt(number)) - 1;
        noUnits.set('value', newRowCount);
        
        var number = noUnits.get('value');
        remove_units_ass(unitName)
    });

    $('.removeAss').unbind('click');
    $('.removeAss').click(function(e){
        e.preventDefault();
        //remove the current row
        $(this).parents('tr').remove();
        //also change the number of the assessments
        var newValue = $('#noAss').val();
        var newRowCount = parseInt(newValue) - 1;
        $('#noAss').val(newRowCount);
    });

    $('.unitName').change(function(){
        //we want to change the drop downs on the ass
        //get the id
        var id = $(this).attr('id');
        var number = id.substring(8);
        var name = $(this).val();
        alter_options(number, name);
    });
    
    $('.removeWeighting').unbind('click');
    $('.removeWeighting').click(function(e){
        e.preventDefault();
        //remove the current row
        $(this).parents('tr').remove();
        //also change the number of the assessments
        var newValue = $('#noWeights').val();
        var newRowCount = parseInt(newValue) - 1;
        $('#noWeights').val(newRowCount);
    });
    
    $('.weightingCoef').unbind('click');
    $('.weightingCoef').click(function(e){
        //uncheck all others
        var name = $(this).attr('name');
        $('.weightingCoef').each(function(){
            if(($(this).attr('name')) != name)
            {
                $(this).attr('checked',false);
            }
        });
    });
    
    
}	

function add_select_options(newRowCount)
{
    //get all of the unit names
    //get the select
    //add all of the unit names as options
    var selector = $('#assUnit'+newRowCount);
    var unitNamesInputs = $("input.unitName[type=text]");
    var noUnitNames = unitNamesInputs.length;
    for(var i=0;i<noUnitNames;i++)
    {
        //the values is:
        var name = unitNamesInputs[i].value;
        //we need to get the id. the name of the input box contains the ID
        var nameOfInput = $(unitNamesInputs[i]).attr("name");
        var id = nameOfInput.substr(8);				
        //add the options
        selector.add(new Option(name,id));
    }
}

function add_units_ass(newRowCount)
{
    var allSelects = $('#alevelAssTable select');
    var noSelects = allSelects.length;
    for(var i=0;i<noSelects;i++)
    {
        allSelects[i].add(new Option('Unit'+newRowCount, newRowCount));
    }
}

function remove_units_ass(unitName)
{
    var allSelects = $('#alevelAssTable select');
    var noSelects = allSelects.length;

    for(var i=0;i<noSelects;i++)
    {
        var select = allSelects[i];
        var options = allSelects[i].options;
        for(var j=0;j<options.length;j++)
        {
            if(options[j].text == unitName)
            {
                select.remove(j);
                //limit to one
                break;
            }
        }			
    }
}

function alter_options(number, name)
{
    var allSelects = $('#alevelAssTable select');
    var noSelects = allSelects.length;

    for(var i=0;i<noSelects;i++)
    {
        var options = allSelects[i].options;
        for(var j=0;j<options.length;j++)
        {
            if(options[j].value == number)
            {
                options[j].text = name;
            }
        }		
    }
}

M.mod_bcgtalevel.initstudentgrid = function(Y, qualID, studentID, grid) {

    $(document).ready(function() {
        var selects = Y.one('#selects').get('value');
        if(selects == "yes")
        {
            var index2 = Y.one("#studentChange").get('selectedIndex');
            studentID = Y.one("#studentChange").get("options").item(index2).getAttribute('value');
        }
        else
        {
            studentID = Y.one('#sID').get('value'); 
        }
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
                /* Clear the old information from the table */
                that.oApi._fnClearTable( oSettings );
                /* Got the data - add it to the table */
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
                /* Callback user function - for event handlers etc */
                if ( typeof fnCallback == 'function' && fnCallback !== null )
                {
                    fnCallback( oSettings );
                }

            }, oSettings );
        };
        
        draw_ALEVEL_student_table(studentID, grid);

        apply_alps_stu_grid_calls(Y);


    } );
    
    var viewsimple = Y.one('#viewsimple');
    viewsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 's');
        var cID =  Y.one('#cID').get('value');
        self.location='student_grid.php?qID='+qualID+'&sID='+studentID+'&g=s&cID='+cID;
//        redraw_ALEVEL_student_table(qualID, studentID, 's');
    });
    
    var editsimple = Y.one('#editsimple');
    editsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 'se');
        var cID =  Y.one('#cID').get('value');
        self.location='student_grid.php?qID='+qualID+'&sID='+studentID+'&g=se&cID='+cID;
//        redraw_ALEVEL_student_table(qualID, studentID, 'se');
    });
}

var draw_ALEVEL_student_table = function(studentID, grid) { 
    
//    $(document).ready( function () {
//        var tables = $('.alevelStudentsGridTables');
//        var count = tables.length;
//        var tablesArray = [];
//        for(var i=0;i<count;i++)
//        {
//            var id = $(tables[i]).attr('id');
////            var qualID = id.substring(id.indexOf('Q'));
//            tablesArray[i] = $('#'+id).dataTable( {
//                "bProcessing": true,
//                "bServerSide": true,
//                "sScrollX": "100%",
//                "sScrollY": "550px",
//                "bScrollCollapse": true,
//                "bPaginate": false,
//                "bSort":false,
//                "bInfo":false,
//                "bFilter":false,
////                "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid,
////                "fnDrawCallback": function () {
////                    if ( typeof oTable != 'undefined' ) {
////        //                applyStudentTT();
////        //                setTimeout("applyGridTT();", 2000); 
////                    }
////                }
//            });
//                
////            new FixedColumns( tablesArray[i], {
////                "iLeftColumns": 3,
////                "iLeftWidth": 260 
////           }); 
//        }
//    });
//    
////    var oTable = $('#alevelStudentGrid').dataTable( {
////        "bProcessing": true,
////        "bServerSide": true,
////        "sScrollX": "100%",
////        "sScrollY": "550px",
////        "bScrollCollapse": true,
////        "bPaginate": false,
////        "bSort":false,
////        "bInfo":false,
////        "bFilter":false,
////        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid,
////        "fnDrawCallback": function () {
////            if ( typeof oTable != 'undefined' ) {
//////                applyStudentTT();
//////                setTimeout("applyGridTT();", 2000); 
////            }
////        }
////    } );
////    
////    var fCol = new FixedColumns( oTable, {
////                    "iLeftColumns": 3,
////                    "iLeftWidth": 280 
////                } );
    apply_student_grid_TT(studentID);
}

var redraw_ALEVEL_student_table = function(qualID, studentID, grid) {
    var oDataTable = $('#alevelStudentGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid;
    //var oSettings = oDataTable.fnSettings();
        oDataTable.fnReloadAjax(newUrl);
        //applyStudentTT();
            //setTimeout("recalculate_cols();", 1000)
            
//    // Do qualification comment
//    $('#qualComment').html('');
//            
//    var params = {action: 'getQualComment', params: {studentID: studentID, qualID: qualID, mode: grid} };
//    $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
//        $('#qualComment').html(data);
//    });
    
        
            
}

function apply_student_grid_TT(studentID)
{
    $('.alevelFormalAssessments').unbind('change');
    $('.alevelFormalAssessments').change(function(e){
        //get the value
        var name = $(this).attr('name');
        var criteriaID = name.split('_')[1];
        var qualID = name.split('_')[3];
        var valueID = $(this).val();
//        alert(valueID);
        if(studentID == -1)
        {
            studentID = name.split('_')[5];
        }
        
        var data = {
            method: 'POST',
            data: {
                'qID' : qualID, 
                'sID' : studentID, 
                'cID' : criteriaID,
                'value' : valueID, 
                'vtype' : 'value', 
                'uservalue' : '-1',
                'grid' : 'student'
            },
            on: {
                success: update_student_grid
            }
        }
//        alert('qID='+qualID+
//                'sID='+studentID+ 
//                'cID='+criteriaID+
//                'value='+valueID+ 
//                'vtype='+'value'+ 
//                'uservalue='+'-1'+
//                'grid='+'student');
        var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/update_student_value.php";
        var request = Y.io(url, data);
    });
    
    $('.alevelCetas').unbind('change');
    $('.alevelCetas').change(function(e){
        //get the value
        var name = $(this).attr('name');
        var criteriaID = name.split('_')[1];
        var qualID = name.split('_')[3];
        var valueID = $(this).val();
        
        if(studentID == -1)
        {
            studentID = name.split('_')[5];
        }
        var data = {
            method: 'POST',
            data: {
                'qID' : qualID, 
                'sID' : studentID, 
                'cID' : criteriaID,
                'value' : valueID, 
                'vtype' : 'targetgrade', 
                'uservalue' : '-1',
                'grid' : 'student'
            },
            on: {
                success: update_student_grid
            }
        }
        var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/update_student_value.php";
        var request = Y.io(url, data);
    });
    
    $('.alevelAdditionalInput').unbind('change');
    $('.alevelAdditionalInput').change(function(e){
        //get the value
        var name = $(this).attr('name');
        var criteriaID = name.split('_')[1];
        var qualID = name.split('_')[3];
        var value = $(this).val();
        if(studentID == -1)
        {
            studentID = name.split('_')[5];
        }
        var data = {
            method: 'POST',
            data: {
                'qID' : qualID, 
                'sID' : studentID, 
                'cID' : criteriaID,
                'value' : '-1', 
                'vtype' : 'userdefined', 
                'uservalue' : value,
                'grid' : 'student'
            },
            on: {
                success: update_student_grid
            }
        }
        var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/update_student_value.php";
        var request = Y.io(url, data);
    });
    
    $('.alevelTG').unbind('change');
    $('.alevelTG').change(function(e){
        //get the value
        var name = $(this).attr('name');
        var studentID = name.split('_')[1];
        var qualID = name.split('_')[3];
        var value = $(this).val();
        var data = {
            method: 'POST',
            data: {
                'qID' : qualID, 
                'sID' : studentID, 
                'value' : value, 
                'vtype' : 'targetgrade'
            },
            on: {
                success: update_student_grid
            }
        }
        alert('qID='+qualID+' sID='+studentID+' value='+value);
        var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtalevel/ajax/update_student_target_grade.php";
        var request = Y.io(url, data);
    });
    
    
    
    
}

function update_student_grid(id, o){
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
            //alert(JSON.stringify(json));
            
    //applyStudentTT();
    //update the unit award
    //update the qual award
    //update the ticks
    
}

M.mod_bcgtalevel.initclassgrid = function(Y, qualID) {
    var viewsimple = Y.one('#viewsimple');
    viewsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 's');
        self.location='class_grid.php?qID='+qualID+'&g=s';
    });
    
    var editsimple = Y.one('#editsimple');
    editsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 'se');
        self.location='class_grid.php?qID='+qualID+'&g=se';
    });
    
    $(document).ready(function() {
       
       apply_alps_class_grid_calls(Y);
       
    });
    
    apply_student_grid_TT(-1);
}


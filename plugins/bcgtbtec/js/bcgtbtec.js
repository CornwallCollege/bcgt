M.mod_bcgtbtec = {};

var changeType = false;
var change = false;
var globalQualID;
var globalActivityID;
var tmpDate = null;

M.mod_bcgtbtec.bteciniteditqual = function(Y) {
    
//    Y.one('#save').set('disabled', 'disabled');
    
    var btecLevel = Y.one('#qualLevel');
    if(btecLevel)
    {
        btecLevel.on('change', function(e){
               changeType = true; 
               reload_BTEC_edit_qual_form();
            } ); 
    }
    
    
    var btecType = Y.one('#qualType');
    if(btecType)
    {
        btecType.on('change', function(e){
               changeType = true; 
               reload_BTEC_edit_qual_form();
            } ); 
    }
    
    
    var btecSubType = Y.one('#qualSubtype');
    if(btecSubType)
    {
        btecSubType.on('change', function(e){
            changeType = true;
            reload_BTEC_edit_qual_form();    
        });
    }
    
//    var name = Y.one('#qualName');
//    $('#qualName').unbind('keypress');
//    name.on('keypress', function(e){
//        check_btec_edit_qual_valid();
//    });
//    
//    check_btec_edit_qual_valid();
};

function check_btec_edit_qual_valid()
{
    //get the level and subtype and name
    var levelIndex = Y.one('#qualLevel').get('selectedIndex');
    var level = Y.one("#qualLevel").get("options").item(levelIndex).getAttribute('value');
    var subtypeIndex = Y.one('#qualSubtype').get('selectedIndex');
    var subtype = Y.one("#qualSubtype").get("options").item(subtypeIndex).getAttribute('value');
    var name = Y.one('#qualName').get('value');
    if(name != '' && level != -1 && subtype != -1)
    {
        Y.one('#save').set('disabled', '');
    }
}

M.mod_bcgtbtec.bteciniteditunit = function(Y) {
    var btecLevel = Y.one('#level');
    btecLevel.on('change', function(e){
        var credits = Y.one('#credits');
        if(credits)
        {
            credits.set('value', '0');
        }
        var typeID = $('#spec').val();
        if(typeID)
        {
            $('#spec').val(typeID);
        }
        Y.one('#editUnitForm').submit();
        //need to reset the typeID;
    } );   
    
    var btecSubType = Y.one('#subtype');
    if(btecSubType)
    {
        btecSubType.on('change', function(e){
        var typeID = $('#spec').val();
        if(typeID)
        {
            $('#spec').val(typeID);
        }
        Y.one('#editUnitForm').submit();
        });
    }
    
    var btecSpec = Y.one('#spec');
    if(btecSpec)
    {
        btecSpec.on('change', function(e){
            var typeID = $('#spec').val();
            if(typeID)
            {
                $('#typeID').val(typeID);
            }
            Y.one('#editUnitForm').submit();
        });
    }
    
    var unitSubType = $('#unitSubType');
    if(unitSubType)
    {
        unitSubType.on('change', function(e){
            var typeID = $('#spec').val();
            if(typeID)
            {
                $('#typeID').val(typeID);
            }
            Y.one('#editUnitForm').submit();
        });
    }
    
};

M.mod_bcgtbtec.bteciniteditunitcriteria = function(Y) {
    var pass = Y.one('#noPass');
    if(pass)
    {
        pass.on('change', function(e){
                Y.one('#editUnitForm').submit();
            } );
    }
       
    var merit = Y.one('#noMerit');
    if(merit)
    {
        merit.on('change', function(e){
                Y.one('#editUnitForm').submit();
            });
    }
   
    var diss = Y.one('#noDiss');
    if(diss)
    {
        diss.on('change', function(e){
                Y.one('#editUnitForm').submit();
            });
    }
    
    var l1 = Y.one('#noL1');
    if(l1)
    {
        l1.on('change', function(e){
                Y.one('#editUnitForm').submit();
            } );
    }
    
    
    apply_edit_unit_TT();
}

function apply_edit_unit_TT()
{
    var subCriteria = $('#subCriteria').val();
    $('#pCopyMerit').unbind("click");
    $('#pCopyMerit').click(function(e){
        e.preventDefault();
        //need to set the correct number of total credits;
        //for each criteria:   
        //copy details
        //cipy all subcriteria
        var noCriteria = $('#noPass').val();
        noCriteria = parseInt(noCriteria);
        $('#noMerit').val(noCriteria);
        //clear old column
        $('#btecCritM tbody tr').remove();
        for(var i=1;i<=noCriteria;i++)
        {
            //for every P we want to create a new row.
            var criteriaName = 'M'+i;
            var details = $('#details_'+'P'+i).val();
            if(!details)
            {
                details = '';    
            }
            var newMainRow = get_criteria_main_row(criteriaName, details, 'P'+i, subCriteria);
            $('#btecCritM').append(newMainRow);
        }
        apply_edit_unit_TT();
    });
    
    $('#mCopyDiss').unbind("click");
    $('#mCopyDiss').click(function(e){
        e.preventDefault();
        //need to set the correct number of total credits;
        //for each criteria:   
        //copy details
        //cipy all subcriteria
        var noCriteria = $('#noMerit').val();
        $('#noDiss').val(noCriteria);
        //clear old column
        $('#btecCritD tbody tr').remove();
        for(var i=1;i<=noCriteria;i++)
        {
            //for every P we want to create a new row.
            var criteriaName = 'D'+i;
            var details = $('#details_'+'M'+i).val();
            if(!details)
            {
                details = '';    
            }
            var newMainRow = get_criteria_main_row(criteriaName, details, 'M'+i, subCriteria);
            $('#btecCritD').append(newMainRow);
        }
        apply_edit_unit_TT();
    });
    
    $('.subCriteriaDetails').each(function(index){
        //each time the details are typed in, add one below.
        $(this).unbind("keydown");
        $(this).on("keydown", function(e){
            var unicode=e.keyCode? e.keyCode : e.charCode;
            if(unicode != 9)
            {
                var name = $(this).attr('name');
                var criteriaName = name.split('_')[2];
                var subCriteriaNumber = name.split('_')[3];
                subCriteriaNumber = parseInt(subCriteriaNumber);
                var table = $('table #'+criteriaName+'_sub');
                var rowCount = $('table #'+criteriaName+'_sub tr').length;
                if(rowCount == subCriteriaNumber)
                {
                    //then we are indeed at the last row
                    //so add a new row. 
                    //total number of subCriterias
                    var noSubCriteria = $('#noSubCrit'+criteriaName).val();
                    var number = parseInt(noSubCriteria);
                    var newNoSub = number+1;
                    var newSubCriteriaName = criteriaName+'.'+newNoSub;
                    //build the row up
                    var newRow = get_new_sub_criteria_row(newSubCriteriaName, criteriaName, newNoSub, '');
                    //apend it
                    table.append(newRow);
                    //increment the number of subs
                    change_number_subs(newNoSub, criteriaName);
                    apply_edit_unit_TT();
                } 
                else
                {
                    console.log('rowCount='+rowCount+' and subCritNo='+subCriteriaNumber)
                }
            }
        });
    });
        
    $('.actionImageDel').each(function(index){
        $(this).unbind("click");
        $(this).on("click", function(e){
            var id = $(this).attr('id');
            //id is citeriaName_no;
            var criteriaName = id.split('_')[1];
            var subCriteriaNumber = id.split('_')[2];
            var number = parseInt(subCriteriaNumber);
            //remove the actual row. 
            change_all_sub_numbers(criteriaName, number, false);
            
            $(this).closest("tr").remove();
            //decrement the no of subCriterias
            change_number_subs(-1, criteriaName, false);
            //we need to change all of the numbers of all of the following rows 
        });
    });
      
      
      
    $('.actionImageAddB').each(function(index){
        $(this).unbind("click");
        $(this).on("click", function(e){
            var id = $(this).attr('id');
            //id is citeriaName_no;
            var criteriaName = id.split('_')[1];
            var subCriteriaNumber = id.split('_')[2];
            var number = parseInt(subCriteriaNumber);
            var newNumber = number+1;
            var newSubCriteriaName = criteriaName+"."+newNumber;
            
            change_all_sub_numbers(criteriaName, number, true);
            
            var newRow = get_new_sub_criteria_row(newSubCriteriaName, criteriaName, newNumber, '');
            $('#'+criteriaName+'_sub').prepend(newRow);
            
            change_number_subs(-1, criteriaName, true);
            
            apply_edit_unit_TT();
        });
    });  
      
    $('.actionImageAdd').each(function(index){
        $(this).unbind("click");
        $(this).on("click", function(e){
            var id = $(this).attr('id');
            //id is citeriaName_no;
            var criteriaName = id.split('_')[1];
            var subCriteriaNumber = id.split('_')[2];
            var number = parseInt(subCriteriaNumber);
            var newNumber = number+1;
            var newSubCriteriaName = criteriaName+"."+newNumber;
            
            change_all_sub_numbers(criteriaName, number, true);
            
            var newRow = get_new_sub_criteria_row(newSubCriteriaName, criteriaName, newNumber, '');
            $(this).closest("tr").after(newRow);
            
            change_number_subs(-1, criteriaName, true);
            
            apply_edit_unit_TT();
        });
    });  
}

function get_criteria_main_row(criteriaName, text, oldCriteriaName, subCriteria)
{
    //we need a cell for the name, details, add button
    var retval = '<tr><td>'+criteriaName+'</td>';
    retval += '<td><textarea cols="20" rows="3" name="details_'+criteriaName+'" ';
    retval += 'id="details_'+criteriaName+'">'+text+'</textarea></td>';
    if(subCriteria)
    {
        retval += '<td><img class="actionImageAddB" id="sA_'+criteriaName+'_0"';
        retval += 'alt="Insert New Below" title="Insert New Below" ';
        retval += 'src="'+M.cfg.wwwroot;
        retval += '/blocks/bcgt/plugins/bcgtbtec/pix/greenPlus.png"></td></tr>';
        retval += get_sub_criteria_rows(criteriaName, oldCriteriaName);
    }
    
    return retval;
}

function get_sub_criteria_rows(criteriaName, oldCriteriaName)
{
    //build the table
    //get the number of rows
    var retval = '';
    var noSubCriteria = $('#noSubCrit'+oldCriteriaName).val();
    var noSubCriteria = parseInt(noSubCriteria);
    //create table
    retval += '<td></td>';
    retval += '<td><table class="subCriteria" id="'+criteriaName+'_sub" ';
    retval += 'align="center"><tbody>';
    for(var i=1;i<=noSubCriteria;i++)
    {
        retval += '<tr class="subcriteria"><td>';
        retval += criteriaName+'.'+i+'</td><td><textarea class="';
        retval += 'subCriteriaDetails subCriteriaDetailsLast" id="ta_'+criteriaName+'_';
        retval += i+'" cols="15" rows="3" name="sub_details_'+criteriaName;
        retval += '_'+i+'">';
        var text = $('#ta_'+oldCriteriaName+'_'+i).val(); 
        if(text)
        {
            retval += text;
        }
        retval += '</textarea></td><td><img class="actionImageAdd" ';
        retval += 'id="sA_'+criteriaName+'_'+i+'" alt="Insert New Below" ';
        retval += 'title="Insert New Below"';
        retval += 'src="'+M.cfg.wwwroot;
        retval += '/blocks/bcgt/plugins/bcgtbtec/pix/greenPlus.png">';
        retval += '<img class="actionImageDel" id="sD_'+criteriaName+'_'+i+'"';
        retval += 'title="Delete This Row" alt="Delete This Row"'; 
        retval += 'src="'+M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/pix/';
        retval += 'redX.png"></td></tr>';
    }
    retval += '</tbody></table></td>';
    retval += '<tr class="rowDivider">';
    retval += '<td><input type="hidden" id="noSubCrit'+criteriaName+'" name="noSubCrit'+criteriaName+'" value="'+noSubCriteria+'"/></td>';
    retval += '</tr>';
    
    return retval;
}

function change_all_sub_numbers(criteriaName, actionIndex, increment)
{
    //this change occurs before a new row gets added or removed
    //if we are incrementing
        //then after this number increment all numbers by one
    //else decrement all numbers by one after
    var tableRows = $('table #'+criteriaName+'_sub tr');
    $(tableRows).each(function(index1){
        if(actionIndex <= index1)
        {
            //then we are after the row that we are adding or deleting.
            //the text inside the first cell
            $(this).children('td').each(function(index2){
                if(index2 == 0)
                {
                    var cellHTML = $(this).html();
                    var subCriteriaNumber = cellHTML.split('.')[1];
                    subCriteriaNumber = parseInt(subCriteriaNumber);
                    if(increment)
                    {
                        subCriteriaNumber++;
                    }
                    else
                    {
                        subCriteriaNumber--;
                    }
                    $(this).html(criteriaName+'.'+subCriteriaNumber);
                }
                else if(index2 == 1)
                {
                    //second column name and id change
                    var cellName = $(this).children(':first').attr('name');
                    var subCriteriaNumber = cellName.split('_')[3];
                    subCriteriaNumber = parseInt(subCriteriaNumber);
                    if(increment)
                    {
                        subCriteriaNumber++;
                    }
                    else
                    {
                        subCriteriaNumber--;
                    }
                    $(this).children(':first').attr('name', 'sub_details_'+criteriaName+'_'+subCriteriaNumber);
                
                    $(this).children(':first').attr('id', 'ta_'+criteriaName+'_'+subCriteriaNumber);
                }
                else if(index2 == 2)
                {
                    //third column ids of two images
                    $(this).children('img').each(function(index3){
                        var id = $(this).attr('id');
                        var subCriteriaNumber = id.split('_')[2];
                        subCriteriaNumber = parseInt(subCriteriaNumber);
                        if(increment)
                        {
                            subCriteriaNumber++;
                        }
                        else
                        {
                            subCriteriaNumber--;
                        }
                        $(this).attr('id', 's_'+criteriaName+'_'+subCriteriaNumber);
                    });
                }
                
                
            });
 
        }
    });
}

function get_new_sub_criteria_row(subCriteriaName, criteriaName, newNumber, text)
{
    var retval = '<tr><td>'+subCriteriaName+'</td>';
    retval += '<td>'+create_cell2(criteriaName+'_'+newNumber, criteriaName, newNumber, text)+'</td>';
    retval += '<td>'+create_cell3(criteriaName, newNumber)+'</td></tr>';
    return retval;
}

function create_cell2(subCritName, criteriaName, newNumber, text)
{
    var retval = '<textarea class=\'subCriteriaDetails\' id=\'ta_'+subCritName+'\' name=\'sub_details_';
    retval += criteriaName+'_'+newNumber+'\' cols=\'15\' rows=\'3\'>';
    retval += text+'</textarea>';
    return retval;
}

function change_number_subs(newValue, criteriaName, increment)
{
    if(newValue == -1)
    {
        var noSubCriteria = $('#noSubCrit'+criteriaName).val();
        var number = parseInt(noSubCriteria);
        if(increment)
        {
            newValue = number+1;
        }
        else
        {
            newValue = number-1;
        }
    }
    $('#noSubCrit'+criteriaName).attr('value', newValue);
}

function create_cell3(criteriaName, newNumber)
{
    var retval = '<img class=\'actionImageAdd\' id="sA_'+criteriaName+'_'+newNumber+'" alt=\'Insert New Below\' ';
    retval += 'title=\'Insert New Below\' src=\''+M.cfg.wwwroot;
    retval += '/blocks/bcgt/plugins/bcgtbtec/pix/greenPlus.png\'/>';
    retval += '<img class=\'actionImageDel\' id=\'sD_'+criteriaName+'_'+newNumber+'\' title=\'Delete This Row\'';
    retval += ' alt=\'Delete This Row\' src=\''+M.cfg.wwwroot;
    retval += '/blocks/bcgt/plugins/bcgtbtec/pix/redX.png\'/>';
    return retval;
}

var reload_BTEC_edit_qual_form = function() {
    if(changeType){
        var typeID = -1;
    }
    else{
        var typeID = Y.one('#tID').get('value');
    }
    var courseID = Y.one("#cID").get('value');
    var qualID = Y.one('#qID').get('value');
    var index = Y.one("#qualFamilySelect").get('selectedIndex');
    var familyID = Y.one("#qualFamilySelect").get("options").item(index).getAttribute('value');
    var index2 = Y.one("#qualSubtype").get('selectedIndex');
    var subTypeID = Y.one("#qualSubtype").get("options").item(index2).getAttribute('value');
    var index3 = Y.one("#qualLevel").get('selectedIndex');
    var levelID = Y.one("#qualLevel").get("options").item(index3).getAttribute('value');
    var index4 = Y.one("#qualType").get('selectedIndex');
    var spec = Y.one("#qualType").get("options").item(index4).getAttribute('value');
    if(spec != -1)
    {
        typeID = spec;
    }
    
    self.location='edit_qual.php?fID='+familyID+'&tID='+typeID+'&qID='+qualID+'&level='+levelID+'&subtype='+subTypeID+'&spec='+spec+'&cID='+courseID;
};

M.mod_bcgtbtec.initstudunits = function(Y) {
    
    $(document).ready( function () {
        var tables = $('.btecStudentsUnitsTable');
        var count = tables.length;
        var tablesArray = [];
        for(var i=1;i<=count;i++)
        {
            tablesArray[i] = $('#btecStudentUnits'+i).dataTable( {
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
        
        $('td.nameCol').on('mouseover', function(){
            if (selectedSetID > 0)
            {
                $(this).addClass('bcgt_highlighted');
            }
        });
        
        $('td.nameCol').on('mouseout', function(){
            $(this).removeClass('bcgt_highlighted');
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

M.mod_bcgtbtec.initsinglestudunits = function(Y) {
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

M.mod_bcgtbtec.initactivityqualgrid = function(Y, qualID, activityID, grid, 
columnsLocked, configColumnWidth, courseID) {
    
    $(document).ready(function() {
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
                /* Callback user function - for event handlers etc */
                if ( typeof fnCallback == 'function' && fnCallback !== null )
                {
                    fnCallback( oSettings );
                }

            }, oSettings );
        };
        draw_btec_qual_activity_table(qualID, activityID, grid, columnsLocked, configColumnWidth, courseID);
    });
        
        var pageNumbers = $('.unitgridpage');
        pageNumbers.each(function(pageNumber){
        $(this).on('click',function(e){
            e.preventDefault();
            //get the page number:
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
            redraw_BTEC_activity_table(qualID, activityID, grid, checked, courseID, page);
        });
        } );
    
    
    var viewsimple = Y.one('#viewsimple');
    viewsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid'+qualID).set('value', 's');
        show_late(true);
        var checked = '';
        if(Y.one('#showlate'))
        {
            checked = Y.one('#showlate').get('checked');
            if(checked)
            {
                checked = 'L';
            }
        }
        var page = Y.one('#pageInput').get('value');
        redraw_BTEC_activity_table(qualID, activityID, 's', checked, courseID, page);
    });
    
    var editsimple = Y.one('#editsimple');
    editsimple.on('click', function(e){
        e.preventDefault();
        show_late(false);
        Y.one('#grid'+qualID).set('value', 'se');
        var page = Y.one('#pageInput').get('value');
        redraw_BTEC_activity_table(qualID, activityID, 'se', '', courseID, page);
    });
    
    var editadvanced = Y.one('#editadvanced');
    editadvanced.on('click', function(e){
        e.preventDefault();
        show_late(false);
        Y.one('#grid'+qualID).set('value', 'ae');
        var page = Y.one('#pageInput').get('value');
        redraw_BTEC_activity_table(qualID, activityID, 'ae', '', courseID, page);
    });
    
    var viewadvanced = Y.one('#viewadvanced');
    viewadvanced.on('click', function(e){
        e.preventDefault();
        show_late(false);
        Y.one('#grid'+qualID).set('value', 'a');
        var page = Y.one('#pageInput').get('value');
        redraw_BTEC_activity_table(qualID, activityID, 'a', '', courseID, page);
    });
}

var draw_btec_qual_activity_table = function(qualID, activityID, grid, columnsLocked, configColumnWidth) { 
    var oTable = $('#btecActivityGrid'+qualID).dataTable( {
        "bProcessing": true,
        "bServerSide": true,
        "sScrollX": "100%",
        "sScrollY": "550px",
        "bScrollCollapse": true,
        "bPaginate": false,
        "bSort":false,
        "bInfo":false,
        "bFilter":false,
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_qual_activity_grid.php?qID="+qualID+"&aID="+activityID+"&g="+grid,
        "fnDrawCallback": function (o) {
            if ( typeof oTable != 'undefined' ) {
                applyActivityTT(qualID, activityID);
                setTimeout("applyActivityTT();", 2000); 
            }
        }
    } );
    
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": 2,
                    "iLeftWidth": "150px" 
                } );
    
}

M.mod_bcgtbtec.initstudentgrid = function(Y, qualID, studentID, grid, order, cols) {

//    var qualID;
//    var studentID;
//    var order = 'spec';
//    if($('#order'))
//    {
//        order = $('#order').attr('value');
//    }
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
//                var dataLength = aData.length;
//                for ( var i=0 ; i<dataLength ; i++ )
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
//        draw_BTEC_student_table(qualID, studentID, grid, order);
//    } );
//    
//    var refreshpredgrade = Y.one('.refreshpredgrade');
//    if(refreshpredgrade)
//    {
//        refreshpredgrade.on('click', function(e){
//            e.preventDefault();
//            var data = {
//                method: 'POST',
//                data: {
//                    'qID' : qualID,
//                    'sID': studentID
//                },
//                dataType: 'json',
//                on: {
//                    success: refresh_pred_grades
//                }
//            }
//            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/refresh_pred_grades.php";
//            var request = Y.io(url, data);
//        });
//    }
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
//            redraw_BTEC_student_table(qualID, studentID, 's', checked, order);
//        });
//    }
//    
//    
//    var editsimple = Y.one('#editsimple');
//    if (editsimple != null){
//        editsimple.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'se');
//            redraw_BTEC_student_table(qualID, studentID, 'se', false, order);
//        });
//    }
//    
//    
//    var editadvanced = Y.one('#editadvanced');
//    if (editadvanced != null){
//        editadvanced.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'ae');
//            redraw_BTEC_student_table(qualID, studentID, 'ae', false, order);
//        });
//    }
//   
//    var viewadvanced = Y.one('#viewadvanced');
//    if (viewadvanced != null){
//        viewadvanced.detach();
//        viewadvanced.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'a');
//            redraw_BTEC_student_table(qualID, studentID, 'a', false, order);
//        });
//    }
//    
//    
//    var viewLate = Y.one('#showlate');
//    if(viewLate)
//    {
//        viewLate.detach();
//        viewLate.on('click', function(e){
//            var checked = viewLate.get('checked');
//            if(checked)
//            {
//                checked = 'L';
//            }
//            redraw_BTEC_student_table(qualID, studentID, 's', checked, order);
//       });     
//    }
//    
//    var orderChange = $('#order');
//    if(orderChange)
//    {
//        orderChange.on('change',function(e){
////            $(":input").attr("disabled",true);
//            var form = $('#studentGridForm');
//            if(form)
//            {
////                document.unitGridForm.submit();
//                $(form).submit();
//            }
//            
//        });
//    }
//    
//    // buttons
//    $(function() {
//      var loc = window.location.href;     
//      if(/g=se/.test(loc)) {
//        $('#editsimple').addClass('gridbuttonswitchON');
//      }
//      else if(/g=s/.test(loc)) {
//        $('#viewsimple').addClass('gridbuttonswitchON');
//      }
//      else {}
//    });
//    
//    $(".gridbuttonswitch").click(function(){
//    $(".gridbuttonswitchON").removeClass("gridbuttonswitchON");
//     $(this).addClass("gridbuttonswitchON");
//    });


    var qualID;
    var studentID;
    var order;
    var cols;
        
    $(document).ready( function(){
        
        draw_grid('BTECStudentGridTable', '', grid, 'student', order, cols);
                
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
            
            var flag = '';
            if ( $('#showlate').is(':checked') === true ){
                flag = 'L';
            }
                
            // Get data
            var grid = $('#grid').val();
            
            if ( $('#changeUnitGroup').val() !== undefined ){
                var uGroup = encodeURIComponent( $('#changeUnitGroup').val() );
            } else {
                var uGroup = 0;
            }
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&f="+flag+"&order="+order+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('BTECStudentGridTable', data, grid, 'student', order, cols);
            });
            
            
        });
        
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('a');
            
            var flag = '';
            if ( $('#showlate').is(':checked') === true ){
                flag = 'L';
            }
                
            // Get data
            var grid = $('#grid').val();
           
            if ( $('#changeUnitGroup').val() !== undefined ){
                var uGroup = encodeURIComponent( $('#changeUnitGroup').val() );
            } else {
                var uGroup = 0;
            }
           
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&f="+flag+"&order="+order+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('BTECStudentGridTable', data, grid, 'student', order, cols);
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
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&order="+order+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('BTECStudentGridTable', data, grid, 'student', order, cols);
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
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&order="+order+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('BTECStudentGridTable', data, grid, 'student', order, cols);
            });
            
        });
        
        // CHange unit group 
        $('#changeUnitGroup').unbind('change');
        $('#changeUnitGroup').change( function(){

            $('#loading').show();
                
            // Get data
            var grid = $('#grid').val();
            var uGroup = encodeURIComponent( $(this).val() );
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&order="+order+"&uGroup="+uGroup;
            
            $.post(url, function(data){
                draw_grid('BTECStudentGridTable', data, grid, 'student', order, cols);
            });

        } );
        
        
        // Refresh predicted grade
        if ($('.refreshpredgrade').length > 0)
        {
            
            $('.refreshpredgrade').unbind('click');
            $('.refreshpredgrade').bind('click', function(e){
                
                e.preventDefault();
                
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/refresh_pred_grades.php?qID="+qualID+"&sID="+studentID;
                $.post(url, function(data){
                    
                    var jData = JSON.parse(data);                   
                    refresh_pred_grades(0, jData);
                    
                });
                
            });
            
        }
               
        
        
        
        var doResize;
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                var g = $('#grid').val();
                draw_grid('BTECStudentGridTable', '', g, 'student', order, cols);
            }, 100 );
        } );
        
                
        
    } );

}



function draw_grid(id, data, view, grid, order, freezeCols){
                                                       
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
    
    
    // Do the widths of the frozen stuff first, otherwise it'll fuck up
    var w = '40px';
    $('#unitCommentTH').css('width', w);
    $('#unitCommentTH').css('min-width', w);
    $('#unitCommentTH').css('max-width', w);     

    $('.unitCommentTD').css('width', w);
    $('.unitCommentTD').css('min-width', w);
    $('.unitCommentTD').css('max-width', w); 
    
    $('.assignmentTD').css('width', '10px');
    $('.assignmentTD').css('min-width', '10px');
    $('.assignmentTD').css('max-width', '10px');
        
    $('.ivColumn').css('width', '85px');
    $('.ivColumn').css('min-width', '85px');
    $('.ivColumn').css('max-width', '85px');
    
    
    // Draw tinytbl
    var windowHeight = window.innerHeight;
    var height = $('#'+id).height();
    var maxHeight = 700;
    var minHeight = 300;
    
    if (height < minHeight){
        height = minHeight;
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
    
    if (grid === 'se'){
        $('#tinytbl-1').width( $('#tinytbl-1').width() + 5);
    }
    
    // APply widths
    
    if (view === 'se'){
        
        $('.criteriaName').css('width', '60px');
        $('.criteriaName').css('min-width', '60px');
        $('.criteriaName').css('max-width', '60px');
        
        $('.criteriaCell').css('width', '60px');
        $('.criteriaCell').css('min-width', '60px');
        $('.criteriaCell').css('max-width', '60px');
        
        
    } else if(view === 'ae'){
        
        $('.criteriaName').css('width', '100px');
        $('.criteriaName').css('min-width', '100px');
        $('.criteriaName').css('max-width', '100px');
        
        $('.criteriaCell').css('width', '100px');
        $('.criteriaCell').css('min-width', '100px');
        $('.criteriaCell').css('max-width', '100px');
        
        
    } else {
        
        var w = '40px';
                
        $('.criteriaName').css('width', w);
        $('.criteriaName').css('min-width', w);
        $('.criteriaName').css('max-width', w);
        
        $('.criteriaCell').css('width', w);
        $('.criteriaCell').css('min-width', w);
        $('.criteriaCell').css('max-width', w);
        
        $('.ivColumn').css('width', '85px');
        $('.ivColumn').css('min-width', '85px');
        $('.ivColumn').css('max-width', '85px');
        
//        $('#unitCommentTH').css('width', w);
//        $('#unitCommentTH').css('min-width', w);
//        $('#unitCommentTH').css('max-width', w);     
//        
//        $('.unitCommentTD').css('width', w);
//        $('.unitCommentTD').css('min-width', w);
//        $('.unitCommentTD').css('max-width', w);  
                
    }
    
    var activity = $('.activityName');
    $.each(activity, function(){
        
        var actID = $(this).attr('activityid');
        var critWidth = $($('.criteriaName')[0]).innerWidth();
        var newWidth = $('.criteriaName_'+actID).length * critWidth;
        newWidth = newWidth - 8;
        $(this).css('width', newWidth+'px');
        $(this).css('min-width', newWidth+'px');
        
    });
        
                
    $('#toggleCommentsButton').removeClass('active');
        
    
    // Width fix
    var widthFix = 20;
    if (order === 'act'){
        widthFix = 40; // WTF is going on with this bloody thing
    } 
            
    // Fix for order by activity, reapply the widths, doesn't seem to like it first time
    if (order === 'act'){
        
        var w = '60px';
        var uW = '40px';
        
        if (view === 'se'){
            w = '60px';
        } else if (view === 'ae'){
            w = '100px';
        }
        
        $('.assignmentTD').css('width', '10px');
        $('.assignmentTD').css('min-width', '10px');
        $('.assignmentTD').css('max-width', '10px');
                
        $('.criteriaName').css('width', w);
        $('.criteriaName').css('min-width', w);
        $('.criteriaName').css('max-width', w);
        
        $('.criteriaCell').css('width', w);
        $('.criteriaCell').css('min-width', w);
        $('.criteriaCell').css('max-width', w);
        
        $('#unitCommentTH').css('width', uW);
        $('#unitCommentTH').css('min-width', uW);
        $('#unitCommentTH').css('max-width', uW);  
        
        $('.unitCommentTD').css('width', uW);
        $('.unitCommentTD').css('min-width', uW);
        $('.unitCommentTD').css('max-width', uW);  
        
        var activity = $('.activityName');
        $.each(activity, function(){

            var actID = $(this).attr('activityid');
            var critWidth = $($('.criteriaName')[0]).innerWidth();
            var newWidth = $('.criteriaName_'+actID).length * critWidth;
            newWidth = newWidth - 8;
            $(this).css('width', newWidth+'px');
            $(this).css('min-width', newWidth+'px');

        });
        
        
    } else {
                
        var activity = $('.activityName');
        $.each(activity, function(){

            var actID = $(this).attr('activityid');
            var critWidth = $($('.criteriaName')[0]).innerWidth();
            var newWidth = $('.criteriaName_'+actID).length * critWidth;
            newWidth = newWidth - 8;
            $(this).css('width', newWidth+'px');
            $(this).css('min-width', newWidth+'px');

        });
        
    }
    
    $('#tinytbl-1').width( $('#tinytbl-1').width() + widthFix);
    
    // If advanced edit on unit grid, show mass value
    if (view === 'ae' && id === 'BTECUnitGridTable'){
        $('#mass_value').show();
    } else {
        $('#mass_value').hide();
    }
    
    $('#loading').hide();
    applyTT();
    
    if (grid === 'student'){
        applyStudentTT();
    } else if (grid === 'unit'){
        applyUnitTT();
    } else if (grid === 'activity'){
        applyActTT();
    } else if (grid === 'class'){
        applyClassTT();
    }
    
    if (order === 'act'){
        
        var lWidth = $($('.ui-tinytbl-inner.ui-widget-header')[0]).innerWidth();
        $('#fixedHead_left_col').css('width', lWidth + 'px');
        $('#fixedHead_left_col').css('min-width', lWidth + 'px');
        $('#fixedHead_left_col').css('max-width', lWidth + 'px');
        
        var width = $('.ui-tinytbl-head').width();
        width = width - widthFix;
        //width = width - 17;
        $('#BTECUnitFixedHead').css('width', width + 'px');
        $('#BTECUnitFixedHead').css('max-width', width + 'px');
        
        // Have the fixed header scroll when the grid scrolls
        $($('.ui-tinytbl-column')[1]).scroll(function(){
            var s = $(this).scrollLeft();
            $('#BTECUnitFixedHead').scrollLeft(s);
        });
    }
    
    if (view === 'se' || view === 'ae'){
        $('#toggleCommentsButton').removeAttr('disabled');
    } else {
        $('#toggleCommentsButton').attr('disabled', 'disabled');
    }
    
    
}



M.mod_bcgtbtec.initactgrid = function(Y, qualID, grid, courseID, groupingID, columnsLocked, cmID ) 
{
    
    var qualID;
    var grid;
    var courseID;
    var groupingID;
    var columnsLocked;
    var cmID;
    
    $(document).ready( function(){
        
        draw_grid('BTECActGridTable', '', grid, 'activity', '', columnsLocked);
        
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
            
            var flag = '';
            if ( $('#showlate').is(':checked') === true ){
                flag = 'L';
            }
                
            // Get data
            var grid = $('#grid').val();
            var page = $('#pageInput').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID+"&page="+page;
            
            $.post(url, function(data){
                draw_grid('BTECActGridTable', data, grid, 'activity', '', columnsLocked);
            });
            
            
        });
        
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('a');
            
            var flag = '';
            if ( $('#showlate').is(':checked') === true ){
                flag = 'L';
            }
                
            // Get data
            var grid = $('#grid').val();
            var page = $('#pageInput').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID+"&page="+page;
            
            $.post(url, function(data){
                draw_grid('BTECActGridTable', data, grid, 'activity', '', columnsLocked);
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
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID+"&page="+page;
            
            $.post(url, function(data){
                draw_grid('BTECActGridTable', data, grid, 'activity', '', columnsLocked);
            });
            
            
        });
        
        $('#editadvanced').unbind('click');
        $('#editadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('ae');
                
            // Get data
            var page = $('#pageInput').val();
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID+"&page="+page;
            
            $.post(url, function(data){
                draw_grid('BTECActGridTable', data, grid, 'activity', '', columnsLocked);
            });
            
        });        
        
        
        $('.unitgridpage').unbind('click');
        $('.unitgridpage').bind('click', function(e){
            
            $('#loading').show();
            
            e.preventDefault();
            $('.unitgridpage').removeClass('active');
            var page = $(this).attr('page');
            $('#pageInput').val(page);
            
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID+"&page="+page;
            
            $.post(url, function(data){
                draw_grid('BTECActGridTable', data, grid, 'activity', '', columnsLocked);
            });
            
            $(this).addClass('active');
            
        });
        
        
        
    } );
    
    
    
//
//    $(document).ready(function() {
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
//                var dataLength = aData.length;
//                for ( var i=0 ; i<dataLength ; i++ )
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
//        draw_BTEC_act_table(qualID, grid, courseID, groupingID, columnsLocked, configColumnWidth, cmID);
//        
//        
//        var pageNumbers = $('.unitgridpage');
//        pageNumbers.each(function(pageNumber){
//        $(this).on('click',function(e){
//            e.preventDefault();
//            //get the page number:
//            
//            $('.unitgridpage').removeClass('active');
//            
//            var page = $(this).attr('page');
//            Y.one('#pageInput').set('value',page);
//            var checked = '';
//            if(Y.one('#showlate'))
//            {
//                checked = Y.one('#showlate').get('checked');
//                if(checked)
//                {
//                    checked = 'L';
//                }
//            }
//            var grid = Y.one('#grid').get('value');
//            redraw_BTEC_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
//            
//            $(this).addClass('active');
//            
//        });
//    } );
//    });
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
//            var page = 0;
//            if(Y.one('#pageInput'))
//            {
//                page = Y.one('#pageInput').get('value');
//            }
//            redraw_BTEC_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
//        });
//    }
//    
//    
//    var editsimple = Y.one('#editsimple');
//    if (editsimple != null){
//        editsimple.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'se');
//            var page = 0;
//            if(Y.one('#pageInput'))
//            {
//                page = Y.one('#pageInput').get('value');
//            }
//            redraw_BTEC_act_table(qualID, 'se', false, courseID, page, groupingID, cmID);
//        });
//    }
//    
//    
//    var editadvanced = Y.one('#editadvanced');
//    if (editadvanced != null){
//        editadvanced.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'ae');
//            var page = 0;
//            if(Y.one('#pageInput'))
//            {
//                page = Y.one('#pageInput').get('value');
//            }
//            redraw_BTEC_act_table(qualID, 'ae', false, courseID, page, groupingID, cmID);
//        });
//    }
//   
//    var viewadvanced = Y.one('#viewadvanced');
//    if (viewadvanced != null){
//        viewadvanced.detach();
//        viewadvanced.on('click', function(e){
//            e.preventDefault();
//            Y.one('#grid').set('value', 'a');
//            var page = 0;
//            if(Y.one('#pageInput'))
//            {
//                page = Y.one('#pageInput').get('value');
//            }
//            redraw_BTEC_act_table(qualID, 'a', false, courseID, page, groupingID, cmID);
//        });
//    }
//    
//    
//    var viewLate = Y.one('#showlate');
//    if(viewLate)
//    {
//        viewLate.detach();
//        viewLate.on('click', function(e){
//            var checked = viewLate.get('checked');
//            if(checked)
//            {
//                checked = 'L';
//            }
//            var page = 0;
//            if(Y.one('#pageInput'))
//            {
//                page = Y.one('#pageInput').get('value');
//            }
//            redraw_BTEC_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
//       });     
//    }
//        
//    // buttons
//    $(function() {
//      var loc = window.location.href;     
//      if(/g=se/.test(loc)) {
//        $('#editsimple').addClass('gridbuttonswitchON');
//      }
//      else if(/g=s/.test(loc)) {
//        $('#viewsimple').addClass('gridbuttonswitchON');
//      }
//      else {}
//    });
//    
//    $(".gridbuttonswitch").click(function(){
//    $(".gridbuttonswitchON").removeClass("gridbuttonswitchON");
//     $(this).addClass("gridbuttonswitchON");
//    });
}

function refresh_pred_grades(id, json)
{

    var mingrade = json.mingrade;
    var maxgrade = json.maxgrade;
    var avggrade = json.avggrade;
    var minucas = json.minucas;
    var maxucas = json.maxucas;
    var avgucas = json.avgucas;
    
    var minAward = Y.one('#minAward');
    if(minAward)
    {
        minAward.set('innerHTML',mingrade);
    }
    var minUcas = Y.one('#minUcas');
    if(minUcas)
    {
          minUcas.set('innerHTML',minucas);  
    }
    var maxAward = Y.one('#maxAward');
    if(maxAward)
    {
        maxAward.set('innerHTML',maxgrade);
    }
    var maxUcas = Y.one('#maxUcas');
    if(maxUcas)
    {
          maxUcas.set('innerHTML',maxucas);  
    }
    var qualAward = Y.one('#qualAward');
    if(qualAward)
    {
        qualAward.set('innerHTML',avggrade);
    } 
    var avgUcas = Y.one('#avgUcas');
    if(avgUcas)
    {
          avgUcas.set('innerHTML',avgucas);  
    }
}

function show_late(show)
{
    if(Y.one('#showLateFunc'))
    {
        if(show)
        {
            Y.one('#showLateFunc').show();    
        }
        else
        {
            Y.one('#showLateFunc').hide();
        }
    }
}

M.mod_bcgtbtec.initclassgrid = function(Y, qualID, grid, columnsLocked) {

    var qualID;
    var grid;
    var columnsLocked;
    
    
    $(document).ready( function(){
        
        draw_grid('BTECClassGridTable', '', grid, 'class', '', columnsLocked);
        
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
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&grID="+groupID;            
            
            $.post(url, function(data){
                draw_grid('BTECClassGridTable', data, grid, 'class', '', columnsLocked);
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
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&grID="+groupID;            
            
            $.post(url, function(data){
                draw_grid('BTECClassGridTable', data, grid, 'class', '', columnsLocked);
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
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&grID="+groupID;            
            
            $.post(url, function(data){
                draw_grid('BTECClassGridTable', data, grid, 'class', '', columnsLocked);
            });
            
            $(this).addClass('active');
            
        });
        
           
        
        var doResize;
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                var g = $('#grid').val();
                draw_grid('BTECClassGridTable', '', g, 'class', '', columnsLocked);
            }, 100 );
        } );
        
        
    } );
    
    /*
    $(document).ready(function() {
        var selects = Y.one('#selects').get('value');
        if(selects == "yes")
        {
            if (Y.one("#qualChange") != null){
                var index = Y.one("#qualChange").get('selectedIndex');
                qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');
            }
            else
            {
                qualID = -1;
            }
        }
        else
        {
            qualID = -1;
            if(Y.one('#qID'))
            {
                qualID = Y.one('#qID').get('value');  
            }
             
        }
        //this is the course we are searching for/filtering
        courseID = Y.one('#scID').get('value');
        groupID = Y.one('#grID').get('value');
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
                //Callback user function - for event handlers etc
                if ( typeof fnCallback == 'function' && fnCallback !== null )
                {
                    fnCallback( oSettings );
                }

            }, oSettings );
        };
        
        //need to get the grid
        var grid = $('#grid');
        var gridVal = grid.val();
        draw_BTEC_class_table(qualID, gridVal, courseID, columnsLocked, configColumnWidth, groupID);
        var pageNumbers = $('.classgridpage');
        pageNumbers.each(function(pageNumber){
        $(this).on('click',function(e){
            e.preventDefault();
            //get the page number:
            
            $('.classgridpage').removeClass('active');
            
            var page = $(this).attr('page');
            Y.one('#pageInput').set('value',page);
            var checked = '';
            var grid = Y.one('#grid').get('value');
            redraw_BTEC_class_table(qualID, grid, checked, courseID, page, groupID);
            
            $(this).addClass('active');
            
        });
    });
        
    } );

    var viewsimple = Y.one('#viewsimple');
    viewsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 's');
        var page = 1;
        if(Y.one('#pageInput'))
        {
            page = Y.one('#pageInput').get('value');
        }
        redraw_BTEC_class_table(qualID, 's', '', courseID, page, groupID);
    });
    
    var editsimple = Y.one('#editsimple');
    editsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 'se');
        var page = 1;
        if(Y.one('#pageInput'))
        {
            page = Y.one('#pageInput').get('value');
        }
        redraw_BTEC_class_table(qualID, 'se', '', courseID, page, groupID);
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

M.mod_bcgtbtec.initunitgrid = function(Y, unitID, qualID, grid, order, freezeCols) {

    var qualID;
    var unitID;
    var grid;
    var order;
    
    $(document).ready( function(){
        
        draw_grid('BTECUnitGridTable', '', grid, 'unit', order, freezeCols);
        
        if (grid === 'se' || grid === 'ae'){
            $('#toggleCommentsButton').removeAttr('disabled');
        } else {
            $('#toggleCommentsButton').attr('disabled', 'disabled');
        }
        
        var courseID = $('#cID').val();
        var regGrpID = $('#reggrpid').val();
        if (regGrpID === undefined){
            regGrpID = -1;
        }
        
        $('#viewsimple').unbind('click');
        $('#viewsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('s');
            
            var page = $('#pageInput').val();
            
            var flag = '';
            if ( $('#showlate').is(':checked') === true ){
                flag = 'L';
            }
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&order="+order+"&f="+flag+"&cID="+courseID+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('BTECUnitGridTable', data, grid, 'unit', order, freezeCols);
            });
            
            
        });
        
        
        $('#viewadvanced').unbind('click');
        $('#viewadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('a');
    
            var page = $('#pageInput').val();

            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&order="+order+"&cID="+courseID+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('BTECUnitGridTable', data, grid, 'unit', order, freezeCols);
            });
            
            
        });
        
        $('#editsimple').unbind('click');
        $('#editsimple').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('se');
    
            var page = $('#pageInput').val();

            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&order="+order+"&cID="+courseID+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('BTECUnitGridTable', data, grid, 'unit', order, freezeCols);
            });
            
            
        });
        
        $('#editadvanced').unbind('click');
        $('#editadvanced').bind('click', function(){
            
            $('#loading').show();

            // Set grid hidden input
            $('#grid').val('ae');
    
            var page = $('#pageInput').val();
    
            // Get data
            var grid = $('#grid').val();
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&order="+order+"&cID="+courseID+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('BTECUnitGridTable', data, grid, 'unit', order, freezeCols);
            });
                        
            
        });
                
                
        $('#order').unbind('change');
        $('#order').bind('change', function(){
            
            var form = $('#unitGridForm');
            if(form && form.attr('name') && form.attr('name') === 'unitGridForm')
            {
                $(form).submit();
            }
            else 
            {
                var form = $('#unitGroupGridForm');
                $(form).submit();
            }
            
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
            var regGrpID = $('#reggrpid').val();
            if (regGrpID === undefined){
                regGrpID = -1;
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&page="+page+"&order="+order+"&cID="+courseID+"&regGrpID="+regGrpID;
            
            $.post(url, function(data){
                draw_grid('BTECUnitGridTable', data, grid, 'unit', order, freezeCols);
            });
            
            
        });
        
        
        $('#do_mass_value_update').bind('click', function(){
            
            var crit = $('#mass_value_crit_name').val();
            var value = $('#mass_value_id').val();
            var courseID = $('#cID').val();
            var groupID = $('#grID').val();
            
            var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/mass_update_student_value.php?qID="+qualID+"&uID="+unitID+"&crit="+crit+"&value="+value+"&cID="+courseID;
            
            $('#loading').show();
            $(":input").attr("disabled", true);
            
            $.post(url, function(data){
                eval(data);
                $(":input").attr("disabled", false);
                $('#loading').hide();
            });
            
        });
        
        
        
        
        
        var doResize;
        
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading').show();
            doResize = setTimeout( function(){
                var g = $('#grid').val();
                draw_grid('BTECUnitGridTable', '', g, 'unit', order, freezeCols);
            }, 100 );
        } );
        
                
        
    } );



/*
    var qualID;
    var unitID;
    var courseID = -1;
    var sCourseID = -1;
    var groupingID = -1;
    var order = '';
    if($('#order'))
    {
        order = $('#order').find(":selected").val();
    }
    $(document).ready(function() {
        var selects = Y.one('#selects').get('value');
        if(selects == "yes")
        {
            if (Y.one("#qualChange") != null){
                var index = Y.one("#qualChange").get('selectedIndex');
                qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');
            }
            else
            {
                qualID = -1;
            }
            
            if (Y.one("#unitChange") != null){
                var index2 = Y.one("#unitChange").get('selectedIndex');
                unitID = Y.one("#unitChange").get("options").item(index2).getAttribute('value');
            }
        }
        else
        {
            unitID = Y.one('#uID').get('value');
            qualID = -1;
            if(Y.one('#qID'))
            {
                qualID = Y.one('#qID').get('value');  
            }
             
        }
        if(Y.one('#scID'))
        {
            sCourseID = Y.one('#scID').get('value');   
        }
        if(Y.one('#cID'))
        {
            courseID = Y.one('#cID').get('value');       
        }
        if(Y.one('#grID'))
        {
            groupingID = Y.one('#grID').get('value');  
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
        
        //need to get the grid
        var grid = $('#grid');
        var gridVal = grid.val();
        draw_BTEC_unit_table(qualID, unitID, gridVal, sCourseID, columnsLocked, configColumnWidth, order, groupingID, courseID);
        
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
            redraw_BTEC_unit_table(qualID, unitID, grid, checked, sCourseID, page, order, groupingID, courseID);
            
            $(this).addClass('active');
            
        });
    });
        
    } );

    var viewsimple = Y.one('#viewsimple');
    viewsimple.on('click', function(e){
        e.preventDefault();
        Y.one('#grid').set('value', 's');
        show_late(true);
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
        redraw_BTEC_unit_table(qualID, unitID, 's', checked, sCourseID, page, order, groupingID, courseID);
    });
    
    var editsimple = Y.one('#editsimple');
    editsimple.on('click', function(e){
        e.preventDefault();
        show_late(false);
        Y.one('#grid').set('value', 'se');
        var page = 0;
        if(Y.one('#pageInput'))
        {
            page = Y.one('#pageInput').get('value');
        }
        redraw_BTEC_unit_table(qualID, unitID, 'se', '', sCourseID, page, order, groupingID,courseID);
    });
    
    var editadvanced = Y.one('#editadvanced');
    editadvanced.on('click', function(e){
        e.preventDefault();
        show_late(false);
        Y.one('#grid').set('value', 'ae');
        var page = 0;
        if(Y.one('#pageInput'))
        {
            page = Y.one('#pageInput').get('value');
        }
        redraw_BTEC_unit_table(qualID, unitID, 'ae', '', sCourseID, page, order, groupingID,courseID);
    });
    
    var viewadvanced = Y.one('#viewadvanced');
    viewadvanced.on('click', function(e){
        e.preventDefault();
        show_late(false);
        Y.one('#grid').set('value', 'a');
        var page = 0;
        if(Y.one('#pageInput'))
        {
            page = Y.one('#pageInput').get('value');
        }
        redraw_BTEC_unit_table(qualID, unitID, 'a', '', sCourseID, page, order, groupingID,courseID);
    }); 
    
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
            redraw_BTEC_unit_table(qualID, unitID, 's', checked, sCourseID, page, order, groupingID,courseID);
       });     
    }
    
    var orderChange = $('#order');
    if(orderChange)
    {
        orderChange.on('change',function(e){
//            $(":input").attr("disabled",true);
            var form = $('#unitGridForm');
            if(form && form.attr('name') && form.attr('name') == 'unitGridForm')
            {
//                document.unitGridForm.submit();
                $(form).submit();
            }
            else 
            {
//                document.unitGroupGridForm.submit();
                var form = $('#unitGroupGridForm');
                $(form).submit();
            }
            
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

var draw_BTEC_unit_table = function(qualID, unitID, grid, sCourseID, columnsLocked, configColumnWidth, order, groupingID, courseID) { 
//    alert("qID="+qualID+"&uID="+unitID+"&g="+grid+"&scID="+courseID+"&order="+order+"&grID="+groupingID);
    var oTable = $('#BTECUnitGrid').dataTable( {
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
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&scID="+sCourseID+"&order="+order+"&grID="+groupingID+"&cID="+courseID,
        "fnDrawCallback": function () {
            if ( typeof oTable != 'undefined' ) {
                applyUnitTT(true);
                setTimeout("applyTT(true);", 2000); 
            }
        }
    } );
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": columnsLocked,
                    "iLeftWidth": configColumnWidth 
                } );
    //applyUnitTT();
    
}

var draw_BTEC_act_table = function(qualID, grid, courseID, groupingID, columnsLocked, configColumnWidth, cmID) { 
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
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupingID+"&cmID="+cmID,
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

var draw_BTEC_class_table = function(qualID, grid, courseID, columnsLocked, configColumnWidth, groupID) { 
    var oTable = $('#BTECClassGrid').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
//        "iDisplayStart": 0,
//        "iDisplayLength": 15,
        "sScrollX": "100%",
        "sScrollY": "600px",
        "bScrollCollapse": true,
        "bPaginate": false,
        "bSort":false,
        "bInfo":false,
        "bFilter":false,
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&cID="+courseID+"&grID="+groupID,
        "fnDrawCallback": function () {
            if ( typeof oTable != 'undefined' ) {
                applyClassTT(true);
                setTimeout("applyTT(true);", 2000); 
            }
        }
    } );
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": columnsLocked,
                    "iLeftWidth": configColumnWidth 
                } );
    //applyUnitTT();
    
}


var draw_BTEC_student_table = function(qualID, studentID, grid, order) { 
    var oTable = $('#BTECStudentGrid').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
        "sScrollX": "100%",
        "sScrollY": "550px",
        "bScrollCollapse": true,
        "bPaginate": false,
        "bSort":false,
        "bInfo":false,
        "bFilter":false,
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&order="+order,
        "fnDrawCallback": function () {
            if ( typeof oTable != 'undefined' ) {
                applyStudentTT();
                setTimeout("applyTT();", 2000); 
            }
        }
    } );
    
    var fCol = new FixedColumns( oTable, {
                    "iLeftColumns": 3,
                    "iLeftWidth": 280 
                } );
    //applyStudentTT();
    
}

var redraw_BTEC_student_table = function(qualID, studentID, grid, flag, order) {
    $(":input").attr("disabled",true);
    var lock = false;
    if(grid == 'se' || grid == 'ae')
    {
        lock = true;
    }
    var oDataTable = $('#BTECStudentGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_student_grid.php?qID="+qualID+"&sID="+studentID+"&g="+grid+"&f="+flag+"&lock="+lock+"&order="+order;
    //var oSettings = oDataTable.fnSettings();
        oDataTable.fnReloadAjax(newUrl, recalculate_cols);
        //applyStudentTT();
            //setTimeout("recalculate_cols();", 1000)
            
    // Do qualification comment
    $('#qualComment').html('');
    var params = {action: 'getQualComment', params: {studentID: studentID, qualID: qualID, mode: grid, grid: "student"} };
    $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
        $('#qualComment').html(data);
    });
    
        
            
}
        
var redraw_BTEC_unit_table = function(qualID, unitID, grid, flag, sCourseID, page, order, groupingID, courseID) {
    $(":input").attr("disabled",true);
    var lock = false;
    if(grid == 'se' || grid == 'ae')
    {
        lock = true;
    }
    var oDataTable = $('#BTECUnitGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&f="+flag+"&scID="+sCourseID+"&page="+page+"&lock="+lock+"&order="+order+"&grID="+groupingID+"&cID="+courseID;
    //var oSettings = oDataTable.fnSettings();
    oDataTable.fnReloadAjax(newUrl, recalculate_cols_units);
//    applyUnitTT(false);
    //applyUnitTT();
}

var redraw_BTEC_act_table = function(qualID, grid, flag, courseID, page, groupingID, cmID) {
    $(":input").attr("disabled",true);
    var lock = false;
    if(grid == 'se' || grid == 'ae')
    {
        lock = true;
    }
    var oDataTable = $('#BTECActGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&lock="+lock+"&cmID="+cmID+"&grID="+groupingID;
    //var oSettings = oDataTable.fnSettings();
    oDataTable.fnReloadAjax(newUrl, recalculate_cols_act);
//    applyUnitTT(false);
    //applyUnitTT();
}

var redraw_BTEC_class_table = function(qualID, grid, flag, courseID, page, groupID) {
    $(":input").attr("disabled",true);
    var lock = false;
    if(grid == 'se' || grid == 'ae')
    {
        lock = true;
    }
    var oDataTable = $('#BTECClassGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_class_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&lock="+lock+"&grID="+groupID;
    //var oSettings = oDataTable.fnSettings();
    oDataTable.fnReloadAjax(newUrl, recalculate_cols_class);
//    applyUnitTT(false);
    //applyUnitTT();
}

var redraw_BTEC_activity_table = function(qualID, activityID, grid, courseID, page) {
    $(":input").attr("disabled",true);
    var oDataTable = $('#btecActivityGrid'+qualID).dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_qual_activity_grid.php?qID="+qualID+"&aID="+activityID+"&g="+grid+"&cID="+courseID+"&page="+page;
    oDataTable.fnReloadAjax(newUrl, recalculate_cols_activity);
    applyActivityTT();
}


var recalculate_cols = function() {
    $(":input").attr("disabled",true);
    var oDataTable = $('#BTECStudentGrid').dataTable();
    if(typeof oDataTable != 'undefined'  )
    {
        oDataTable.fnAdjustColumnSizing(false);
        applyStudentTT();
        setTimeout("applyStudentTT();", 2000);
        setTimeout("applyStudentTT();", 3000);
        setTimeout("applyStudentTT();", 4000);
    }
    setTimeout("$(':input').attr('disabled',false);", 2000);
    setTimeout("$(':input').attr('disabled',false);", 3000);
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

var recalculate_cols_activity = function() {
    var dataTables = $('.activityQualGrid');
    dataTables.each(function(table){
       var oDataTable = $(this).dataTable();
       if(typeof oDataTable != 'undefined'  )
        {
            oDataTable.fnAdjustColumnSizing();
            applyActivityTT();
        }
    });
}

var recalculate_cols_units = function() {
    $(":input").attr("disabled",true);
    var oDataTable = $('#BTECUnitGrid').dataTable();
    if(typeof oDataTable != 'undefined'  )
    {
        oDataTable.fnAdjustColumnSizing(false);
        applyUnitTT(true);
        setTimeout("applyUnitTT(true);", 2000);
        setTimeout("applyUnitTT(true);", 3000);
        setTimeout("applyUnitTT(true);", 4000);
    }
    setTimeout("$(':input').attr('disabled',false);", 2000);
    setTimeout("$(':input').attr('disabled',false);", 3000);
}

var recalculate_cols_class = function() {
    $(":input").attr("disabled",true);
    var oDataTable = $('#BTECClassGrid').dataTable();
    if(typeof oDataTable != 'undefined'  )
    {
        oDataTable.fnAdjustColumnSizing(false);
        applyClassTT(true);
        setTimeout("applyClassTT(true);", 2000);
        setTimeout("applyClassTT(true);", 3000);
        setTimeout("applyClassTT(true);", 4000);
    }
    setTimeout("$(':input').attr('disabled',false);", 2000);
    setTimeout("$(':input').attr('disabled',false);", 3000);
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
    $(":input").attr("disabled",false);
    if(json.criterialist != null)
    {
        var studentID = json.studentid;
        var qualID = json.qualid;
        var unitID = json.unitid;
        var valueID = json.valueid;
        var length = json.criterialist.length;
        var originalCriteriaID = json.originalcriteriaid;
        //cID_3036_uID_306_SID_11733_QID_278
//        $('#cID_'+originalCriteriaID+'_uID_'+unitID+'_sID_'+studentID+'_QID_'+qualID).attr("disabled", false);
//        $('#cID_'+originalCriteriaID+'_uID_'+unitID+'_sID_'+studentID+'_QID_'+qualID).parents('td').css('background-color', 'green');
        for(var i=0;i<length;i++)
        {
            //for each criteria
            //if its met then tick it
            //if its not then untick it
            var criteria = json.criterialist[i];
            if(criteria != null)
            {
               var criteriaID = criteria.id;
                var met = criteria.met;
                var check = Y.one('input[id="cID_'+criteriaID+'_uID_'+unitID+'_SID_'+studentID+'_QID_'+qualID+'"]'); 
                if(check)
                { 
                    if(met === 1 || met == true || met === 'true')
                    {
                        check.set('checked', 'checked');
                    }
                    else
                    {
                        check.set('checked', '');   
                    }     
                } 
                else
                {
                    var select = Y.one('select[id="cID_'+criteriaID+'_uID_'+unitID+'_SID_'+studentID+'_QID_'+qualID+'"]'); 
                    if(select)
                    {
                        Y.one('select[id="cID_'+criteriaID+'_uID_'+unitID+'_SID_'+studentID+'_QID_'+qualID+'"] > option[value="' + valueID + '"]').set('selected', 'selected');
                    }
                }
            }    
        }
    }
    if(json.qualaward != null)
    {
        if($('#qualAward'))
        {
            $('#qualAward').text(""+json.qualaward.awardvalue+"");
        }
        if($('#avgUcas'))
        {
            $('#avgUcas').text(""+json.qualaward.ucaspoints+"");     
        }
    }
    if(json.minqualaward != null)
    {
        if($('#minAward'))
        {
            $('#minAward').text(""+json.minqualaward.awardvalue+"");
        }
        if($('#minUcas'))
        {
            $('#minUcas').text(""+json.minqualaward.ucaspoints+"");     
        }
    }
    if(json.maxqualaward != null)
    {
        if($('#maxAward'))
        {
            $('#maxAward').text(""+json.maxqualaward.awardvalue+"");
        }
        if($('#maxUcas'))
        {
            $('#maxUcas').text(""+json.maxqualaward.ucaspoints+"");     
        }
    }
    if(json.unitaward != null)
    {
        var uAward = Y.one('#uAw_'+json.unitaward.unitid);
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
            var uAward = Y.one('#unitAwardAdv_'+json.unitaward.unitid);
            if(uAward)
            {
                uAward.set('innerHTML',json.unitaward.awardvalue);
            }
        }
        //then we need to change its selected value.
    }
    //now renable everything
    applyStudentTT();
    //update the unit award
    //update the qual award
    //update the ticks
    
}

function update_act_grid(id, o){
    applyActTT(true);
}

function update_unit_grid(id, o){
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    //need to get the sid and the cid for this
    //so can renable the input. 
    if(json.multiple && json.multiple === true)
    {
        var multiple = json.multipleArray;
        multiple.forEach(function(single){
            process_check_update(single);
        });
    }
    else
    {
        process_check_update(json);
    }
    applyUnitTT(true);
    //update the unit award
    //update the qual award
    //update the ticks
    
}

function process_check_update(json)
{
    var originalCriteriaID = json.originalcriteriaid;
    var studentID = json.studentid;
//    $('#sID_'+studentID+'_cID_'+originalCriteriaID).attr("disabled", false);
//    $('#sID_'+studentID+'_cID_'+originalCriteriaID).parents('td').css('background-color', 'green');
    $(":input").attr("disabled",false);
    if(json.criterialist != null)
    {  
        var length = json.criterialist.length;
        for(var i=0;i<length;i++)
        {
            //for each criteria
            //if its met then tick it
            //if its not then untick it
            var criteria = json.criterialist[i];
            if(criteria != null)
            {
               var criteriaID = criteria.id;
                var met = criteria.met;
                var check = Y.one('input[id="sID_'+studentID+'_cID_'+criteriaID+'"]');
                if(check)
                {
                    if(met === 1 || met || met == 'true')
                    {
                        check.set('checked', 'checked');
                    }
                    else
                    {
                        check.set('checked', '');   
                    }
                }
                else
                {
                    var select = Y.one('select[id="sID_'+studentID+'_cID_'+criteriaID+'"]'); 
                    if(select)
                    {
                        Y.one('select[id="sID_'+studentID+'_cID_'+criteriaID+'"] > option[value="' + valueID + '"]').set('selected', 'selected');
                    }
                }
            }    
        }
    }
    if(json.qualaward != null)
    {
        if($('#qualAward_'+studentID))
        {
            $('#qualAward_'+studentID).text(""+json.qualaward.awardvalue+"");
        }
    }    
    if(json.unitaward != null)
    {
        var uAward = Y.one('#uAw_'+studentID);
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
            var uAward = Y.one('#unitAwardAdv_'+studentID);
            if(uAward)
            {
                uAward.set('innerHTML',json.unitaward.awardvalue);
            }
        }
        //then we need to change its selected value.
    }
}

function applyActivityTT(qualID, activityID)
{ 
    var criteriaChecks = Y.all('.criteriaCheck');
    if(criteriaChecks)
    {
        criteriaChecks.each(function(check){
            check.detach();
            check.on('click', function(e){
                
                //grey everything out first
                //id is in the formsID_11733_cID_3030
//                check.attr("disabled", true);
//                check.css('background-color', 'green');
                $(":input").attr("disabled",true);
                //send the ajax request
                //id comes down as cID_27272_uID_21231
                var idString = check.get('id');
                var criteriaID = idString.split('_')[1];
                var unitID = idString.split('_')[3];
                var checked = check.get('checked');
                var user = Y.one('#user').get('value');
                //todo:
                //get the criteria id, who updated it and if its checked or not checked. 
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'aID' : activityID, 
                        'cID' : criteriaID,
                        'value' : checked, 
                        'vtype' : 'check', 
                        'uservalue' : '-1',
                        'user' : user,
                        'uID' : unitID,
                        'grid' : 'student'
                    },
                    dataType: 'json',
                    on: {
                        success: update_activity_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
          });  
        })
    }
    
    var criteriaSelect = Y.all('.criteriaValueSelect');
    if(criteriaSelect)
    {
        criteriaSelect.each(function(select){
            select.detach();
            select.on('change', function(e){
                //grey everything out first
//                select.attr("disabled",true);
//                select.parents('td').css('background-color', 'green');
                $(":input").attr("disabled",true);
                //get the id which will be the criteriaid
                var idString = select.get('id');
                var criteriaID = idString.split('_')[1];
                var unitID = idString.split('_')[3];
                var user = Y.one('#user').get('value'); 
                var index = select.get('selectedIndex');
                var value = select.get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'aID' : activityID, 
                        'uID' : unitID,
                        'cID' : criteriaID,
                        'value' : value,
                        'user' : user,
                        'grid' : 'student'
                    },
                    on: {
                        success: update_activity_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
            });
        });
    }
    
//    applyTT();
}


function applyStudentTT()
{
    //WHY ON EARTH AM I RE-GETTING THE BLOODY VARIABLES?
    var selects = $('#selects');
    var qualID, studentID;
    if(selects.length > 0 && selects.val() == "yes")
    {
        var select = Y.one("#qualChange");
        if(select)
        {
            var index = Y.one("#qualChange").get('selectedIndex');
            qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');  
        }
        else
        {
            qualID = Y.one("#qID").get('value');
        }
        
        var studentChange = Y.one("#studentChange");
        if (studentChange){
            var index2 = Y.one("#studentChange").get('selectedIndex');
            studentID = Y.one("#studentChange").get("options").item(index2).getAttribute('value');
        }
    }
    else
    {
        studentID = $('#sID').val();
        qualID = $('#qID').val();   
    }
    
    var criteriaChecks = $('.criteriaCheck');
    if(criteriaChecks.length > 0)
    {
        
        criteriaChecks = Y.all('.criteriaCheck');
        criteriaChecks.each(function(check){
            check.detach();
            check.on('click', function(e){
                
                //grey everything out first
                //id is in the formsID_11733_cID_3030
//                check.attr("disabled", true);
//                check.css('background-color', 'green');
                $(":input").attr("disabled",true);
                //send the ajax request
                //id comes down as cID_27272_uID_21231
                var idString = check.get('id');
                var criteriaID = idString.split('_')[1];
                var unitID = idString.split('_')[3];
                var checked = check.get('checked');
                var user = Y.one('#user').get('value');
                //todo:
                //get the criteria id, who updated it and if its checked or not checked. 
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'cID' : criteriaID,
                        'value' : checked, 
                        'vtype' : 'check', 
                        'uservalue' : '-1',
                        'user' : user,
                        'uID' : unitID,
                        'grid' : 'student'
                    },
                    dataType: 'json',
                    on: {
                        success: update_student_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
          });  
        })
    }
        
    var unitAward = $('.unitAward select, select.unitAward');
    if(unitAward.length > 0)
    {
        unitAward = Y.all('.unitAward');
        unitAward.each(function(award){
            award.detach();
            award.on('change', function(e){
                //grey everything out first
//                award.attr("disabled",true);
//                award.parents('td').css('background-color', 'green');
                $(":input").attr("disabled",true);
                //get the id which will be the unitid
                var idString = award.get('id');
                var unitID = idString.split('_')[1];
                var user = Y.one('#user').get('value'); 
                var index = award.get('selectedIndex');
                var value = award.get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'uID' : unitID,
                        'value' : value,
                        'user' : user,
                        'grid' : 'student'
                    },
                    on: {
                        success: update_student_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_unit_award.php";
                var request = Y.io(url, data);
            });
        });
    }
    
    var criteriaSelect = $('.criteriaValueSelect');
    if(criteriaSelect.length > 0)
    {
        criteriaSelect = Y.all('.criteriaValueSelect');
        criteriaSelect.each(function(select){
            select.detach();
            select.on('change', function(e){
                //grey everything out first
//                select.attr("disabled",true);
//                select.parents('td').css('background-color', 'green');
                $(":input").attr("disabled",true);
                //get the id which will be the criteriaid
                var idString = select.get('id');
                var criteriaID = idString.split('_')[1];
                var unitID = idString.split('_')[3];
                var user = Y.one('#user').get('value'); 
                var index = select.get('selectedIndex');
                var value = select.get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'uID' : unitID,
                        'cID' : criteriaID,
                        'value' : value,
                        'user' : user,
                        'grid' : 'student'
                    },
                    on: {
                        success: update_student_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
            });
        });
    }
        
    applyTT();
}

function applyClassTT()
{
    var selects = Y.one('#selects').get('value');
    var qualID;
    if(selects == "yes")
    {
        if (Y.one("#qualChange") != null){
            var index = Y.one("#qualChange").get('selectedIndex');
            qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');
        }        
    }
    else
    {
        qualID = Y.one('#qID').get('value');   
    }
    
    var unitAward = Y.all('.unitAward');
    if(unitAward)
    {
        unitAward.each(function(award){
            award.detach();
            award.on('change', function(e){
                //get the id which will be the studentid
                ////grey everything out first
                
                //id is in the formsID_11733_cID_3030
//                award.attr("disabled", true);
//                award.parents('td').css('background-color', 'red');
                $(":input").attr("disabled",true);
                var idString = award.get('id');
                var studentID = idString.split('_')[1];
                var unitID = idString.split('_')[3];
                var user = Y.one('#user').get('value'); 
                var index = award.get('selectedIndex');
                var value = award.get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'uID' : unitID,
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'value' : value,
                        'user' : user,
                        'grid' : 'class'
                    },
                    
                    on: {
                        success: update_class_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_unit_award.php";
                var request = Y.io(url, data);
            });
        });
    }
}

function update_class_grid(id, o){
    var data = o.responseText; // Response data.
//    alert(data);
    var json = Y.JSON.parse(o.responseText);
    //need to get the sid and the cid for this
    //so can renable the input. 
    var studentID = json.studentid;
    $(":input").attr("disabled",false);
    if(json.qualaward != null)
    {
        if($('#qualAward_'+studentID))
        {
            $('#qualAward_'+studentID).text(""+json.qualaward.awardvalue+"");
        }
    }
}

function applyUnitTT(enableInputs)
{
    var selects = Y.one('#selects').get('value');
    var qualID = -1, unitID = -1, cmID = -1;
    if(selects == "yes")
    {
        qualID = -1;
        unitID = -1;
        if (Y.one("#qualChange") != null){
            var index = Y.one("#qualChange").get('selectedIndex');
            qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');
        }

        if (Y.one("#unitChange") != null){
            var index2 = Y.one("#unitChange").get('selectedIndex');
            unitID = Y.one("#unitChange").get("options").item(index2).getAttribute('value');
        }
        
        if (Y.one("#activityChange") != null){
            var index3 = Y.one("#activityChange").get('selectedIndex');
            cmID = Y.one("#unitChange").get("options").item(index3).getAttribute('value');
        }
        
    }
    else
    {
        if(Y.one('#uID'))
        {
            unitID = Y.one('#uID').get('value');
        }
        if(Y.one('#qID'))
        {
            qualID = Y.one('#qID').get('value');
        }
        if(Y.one('#cmID'))
        {
            cmID = Y.one('#cmID').get('value');
        }
    }
    
    var criteriaChecks = Y.all('.criteriaCheck');
    if(criteriaChecks)
    {
        criteriaChecks.each(function(check){
            check.detach();
            check.on('click', function(e){
                //send the ajax request
                ////grey everything out first
                
                
                //only grey out this one OR change its colour????
                //id is in the formsID_11733_cID_3030
//                check.attr("disabled", true);
//                check.parents('td').css('background-color', 'red');
                $(":input").attr("disabled",true);
                if(qualID == -1)
                {
                    //then get it from the select
                    qualID = check.getAttribute('qual');
                }
                //id comes down as sID_27272_cID_21231
                var idString = check.get('id');
                var criteriaID = idString.split('_')[3];
                var studentID = idString.split('_')[1];
                var checked = check.get('checked');
                var user = Y.one('#user').get('value');
                //todo:
                //get the criteria id, who updated it and if its checked or not checked. 
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'cID' : criteriaID,
                        'value' : checked, 
                        'vtype' : 'check', 
                        'uservalue' : '-1',
                        'user' : user,
                        'uID' : unitID,
                        'grid' : 'unit'
                    },
                    on: {
                        success: update_unit_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
          });  
        })
    }
    
    var unitAward = Y.all('.unitAward');
    if(unitAward)
    {
        unitAward.each(function(award){
            award.detach();
            award.on('change', function(e){
                //get the id which will be the studentid
                ////grey everything out first
                
                //id is in the formsID_11733_cID_3030
//                award.attr("disabled", true);
//                award.parents('td').css('background-color', 'red');
                $(":input").attr("disabled",true);
                if(qualID == -1)
                {
                    //then get it from the select
                    qualID = award.getAttribute('qual');
                }
                var idString = award.get('id');
                var studentID = idString.split('_')[1];
                var user = Y.one('#user').get('value'); 
                var index = award.get('selectedIndex');
                var value = award.get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'uID' : unitID,
                        'value' : value,
                        'user' : user,
                        'grid' : 'unit'
                    },
                    on: {
                        success: update_unit_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_unit_award.php";
                var request = Y.io(url, data);
            });
        });
    }
    
    var criteriaSelect = Y.all('.criteriaValueSelect');
    if(criteriaSelect)
    {
        criteriaSelect.each(function(select){
            select.detach();
            select.on('change', function(e){
                //get the id which will be the criteriaid
                ////grey everything out first
                //id is in the formsID_11733_cID_3030
//                select.attr("disabled", true);
//                select.parents('td').css('background-color', 'red');
                $(":input").attr("disabled",true);
                if(qualID == -1)
                {
                    //then get it from the select
                    qualID = select.getAttribute('qual');
                }
                var idString = select.get('id');
                var criteriaID = idString.split('_')[3];
                var studentID = idString.split('_')[1];
                var user = Y.one('#user').get('value'); 
                var index = select.get('selectedIndex');
                var value = select.get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'uID' : unitID,
                        'cID' : criteriaID,
                        'value' : value,
                        'user' : user,
                        'grid' : 'unit'
                    },
                    on: {
                        success: update_unit_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
            });
        });
    }
//    if(enableInputs)
//    {
//        $(":input").attr("disabled",false);
//    }
//    else
//    {
//        $(":input").attr("disabled",true);
//    }
    $(":input").attr("disabled",false);
    applyTT();
}

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
    
    var criteriaChecks = Y.all('.criteriaCheck');
    if(criteriaChecks)
    {
        criteriaChecks.each(function(check){
            check.detach();
            check.on('click', function(e){
                //send the ajax request
                ////grey everything out first
                
                
                //only grey out this one OR change its colour????
                //id is in the formsID_11733_cID_3030
//                check.attr("disabled", true);
//                check.parents('td').css('background-color', 'red');
                $(":input").attr("disabled",true);
                //id comes down as sID_27272_cID_21231
                var idString = check.get('id');
                var unitID = idString.split('_')[3];
                var criteriaID = idString.split('_')[1];
                var checked = check.get('checked');
                var user = Y.one('#user').get('value');
                var qualID = check.getAttribute('qual');
                var studentID = check.getAttribute('student');
                //todo:
                //get the criteria id, who updated it and if its checked or not checked. 
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'cID' : criteriaID,
                        'value' : checked, 
                        'vtype' : 'check', 
                        'uservalue' : '-1',
                        'user' : user,
                        'uID' : unitID,
                        'grid' : 'act'
                    },
                    on: {
                        success: update_act_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
          });  
        })
    }
    
    var criteriaSelect = Y.all('.criteriaValueSelect');
    if(criteriaSelect)
    {
        criteriaSelect.each(function(select){
            select.detach();
            select.on('change', function(e){
                //get the id which will be the criteriaid
                ////grey everything out first
                //id is in the formsID_11733_cID_3030
//                select.attr("disabled", true);
//                select.parents('td').css('background-color', 'red');
                $(":input").attr("disabled",true);
                var idString = select.get('id');
                var unitID = idString.split('_')[3];
                var criteriaID = idString.split('_')[1];
                var user = Y.one('#user').get('value'); 
                var index = select.get('selectedIndex');
                var value = select.get("options").item(index).getAttribute('value');
                var qualID = select.getAttribute('qual');
                var studentID = select.getAttribute('student');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID, 
                        'sID' : studentID, 
                        'uID' : unitID,
                        'cID' : criteriaID,
                        'value' : value,
                        'user' : user,
                        'grid' : 'act'
                    },
                    on: {
                        success: update_act_grid
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_value.php";
                var request = Y.io(url, data);
            });
        });
    }
    $(":input").attr("disabled",false);
    applyTT();
}

function applyTT()
{    
    //Gets the Unit details
    
    
        
$('.uNToolTipInfo').unbind('click');
$('.uNToolTipInfo').bind('click', function(){

    var unitID = $(this).attr('unitID');
    var t = $(this);

    // Check if it already has data brought back from ajax
    var html = t.siblings('.unitInfoContent').html();
    if (html != ''){

        t.siblings('.unitInfoContent').dialog({
            resizable: false,
            close: function(ev, ui){
                $(this).dialog('destroy');
            }
        });

    } else {

        $.get(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?uID='+unitID+'&type=crit', function(data){
            t.siblings('.unitInfoContent').html(data);
            t.siblings('.unitInfoContent').dialog({
                resizable: false,
                close: function(ev, ui){
                    $(this).dialog('destroy');
                }
            });
        });

    }




});
    
    
//    $('.uNToolTipInfo').each( function(){
//        
//        var aria = $(this).attr('aria-describedby');
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//            
//            $(this).tooltip({
//                content: function(callback){
//
//                    var unitID = $(this).attr('unitID');
//
//                    $.get(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?uID='+unitID+'&type=crit', function(data){
//                        callback(data);
//                    });
//
//                }
//            }).off('mouseover')
//            .on('click', function(){
//                $(this).tooltip('open');
//                return false;
//            }).attr('title', 'Click me for more info')
//            .css('cursor', 'pointer');
//            
//        }
//        
//    } );
    
    

//
//    $('.uNToolTip').tooltip({
//        delay: 500, 
//        track: false,
//        showURL: false,
//        position: 'center right',
//        tipClass: 'bcgt_tooltip',
//        relative: true,
//        bodyHandler: function() {
//            var idAttr = $(this).attr("id");
//            //comes down as uID_21233
//            var unitID = idAttr.split('_')[1];
//            var tsTimeStamp= new Date().getTime();
//            var response = "<span id='responseU"+unitID+"'>Loading... </span>";
//            var content = "";
//            $.ajax({
//                    type: 'GET',
//                    cache:true,
//                    time: tsTimeStamp,
//                timeout:500000,
//                    url: M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?uID='+unitID+'&type=crit', 
//                    success: function(data)
//                    {
//                            $('#responseU'+unitID).html(data);
//                    }
//            });
//            return response;
//        }
//    });
     
     //
    //    Gets the criteria comments
    
    $('.criteriaValueNonEdit').unbind('click');
    $('.criteriaValueNonEdit').bind('click', function(){
        
        var qualID = $(this).attr('qualID');
        var studentID = $(this).attr('studentID');
        var criteriaID = $(this).attr('criteriaID');
        var t = $(this);
        
        // Check if it already has data brought back from ajax
        var html = t.find('.criteriaContent').html();
        if (html !== undefined && html !== ''){
            
            t.find('.criteriaContent').dialog({
                resizable: false,
                close: function(ev, ui){
                    $(this).dialog('destroy');
                }
            });
            
        } else {
            
            $.get(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?qID='+qualID+'&sID='+studentID+'&cID='+criteriaID, function(data){
                t.find('.criteriaContent').html(data);
                t.find('.criteriaContent').dialog({
                    resizable: false,
                    close: function(ev, ui){
                        $(this).dialog('destroy');
                    }
                });
            });
            
        }
                
    });
    
//    $('.stuValueNonEdit').each( function(){
//        
//        var aria = $(this).attr('aria-describedby');
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//            
//            $(this).tooltip({
//                content: function(callback){
//
//                    var idAttr = $(this).attr("id");
//                    var criteriaID = idAttr.split('_')[1];
//                    var studentID = idAttr.split('_')[5];
//                    var qualID = idAttr.split('_')[7];
//
//                    $.get(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?qID='+qualID+'&sID='+studentID+'&cID='+criteriaID, function(data){
//                        callback(data);
//                    });
//
//                }
//            }).off('mouseover')
//            .on('click', function(){
//                $(this).tooltip('open');
//                return false;
//            }).attr('title', 'Click me for more info')
//            .css('cursor', 'pointer');
//            
//        }
//        
//    } );
    
    
   
//    
//    $('.stuValue').tooltip({
//        delay: 500, 
//        track: false,
//        showURL: false,
//        position: 'center right',
//        tipClass: 'bcgt_tooltip',
//        tip: '#bcgt_tooltip',
//        relative: true,
//        bodyHandler: function() {
//            var idAttr = $(this).attr("id");
//            //comes down as stCID_3324_UID_242344
//            var criteriaID = idAttr.split('_')[1];
//            var studentID = idAttr.split('_')[5];
//            var qualID = idAttr.split('_')[7];
//            var tsTimeStamp= new Date().getTime();
//            var response = "<span id='responseS"+studentID+"'>Loading... </span>";
//            var content = "";
//            $.ajax({
//                    type: 'GET',
//                    cache:true,
//                    time: tsTimeStamp,
//                timeout:2000,
//                    url: M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?qID='+qualID+'&sID='+studentID+'&cID='+criteriaID, 
//                    success: function(data)
//                    {
//                        $($.parseHTML(data));
//                        $('#responseS'+studentID).html(data);  
//                    }
//            });
//            return response;
//        }
//    });
    
    
    
$('.studentUnitInfo').unbind('click');
$('.studentUnitInfo').bind('click', function(){

    var qualID = $(this).attr('qualID');
    var studentID = $(this).attr('studentID');
    var t = $(this);

    // Check if it already has data brought back from ajax
    var html = t.siblings('.studentUnitContent').html();
    if (html != ''){

        t.siblings('.studentUnitContent').dialog({
            resizable: false,
            close: function(ev, ui){
                $(this).dialog('destroy');
            }
        });

    } else {

        $.get( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?sID='+studentID+'&qID='+qualID+'&type=studentsunits' , function(data){
            t.siblings('.studentUnitContent').html(data);
            t.siblings('.studentUnitContent').dialog({
                close: function(ev, ui){
                    resizable: false,
                    $(this).dialog('destroy');
                }
            });
        });

    }




});

//    
//    
////    Gets the student units
//$('.studentUnitInfo').each( function(){
//    
//    var aria = $(this).attr('aria-describedby');
//    if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//    
//        $(this).tooltip({
//                content: function(callback){
//
//                    // Get rid of any open ones
//                    $('.ui-tooltip').remove();
//
//                    var qualID = $(this).attr('qualID');
//                    var studentID = $(this).attr('studentID');
//
//                    $.get( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?sID='+studentID+'&qID='+qualID+'&type=studentsunits' , function(data){
//                        callback(data);
//                    });
//
//                }
//            }).off('mouseover')
//            .on('click', function(){
//                $(this).tooltip('open');
//                return false;
//            }).attr('title', 'Click me for more info')
//            .css('cursor', 'pointer');
//        
//    }
//    
//} );
    
    
    
//    //the students units
//    //Gets the Unit details
//$('.studentUnit').tooltip({
//        content: function(callback){
//            
//            // Get rid of any open ones
//            $('.ui-tooltip').remove();
//            
//            var idAttr = $(this).attr("id");
//            var studentID = idAttr.split('_')[1];
//            var qualID = idAttr.split('_')[3];
//            
//            $.get( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?sID='+studentID+'&qID='+qualID+'&type=studentsunits' , function(data){
//                callback(data);
//            });
//            
//        }
//    });
//    
//    $('.studentUnit').tooltip({
//        delay: 500, 
//        track: false,
//        showURL: false,
//        position: 'center right',
//        tipClass: 'bcgt_tooltip',
//        relative: true,
//        bodyHandler: function() {
//            var idAttr = $(this).attr("id");
//            //comes down as uID_21233
//            var studentID = idAttr.split('_')[1];
//            var qualID = idAttr.split('_')[3];
//            var tsTimeStamp= new Date().getTime();
//            var response = "<span id='responseS"+studentID+"'>Loading... </span>";
//            var content = "";
//            $.ajax({
//                    type: 'GET',
//                    cache:true,
//                    time: tsTimeStamp,
//                timeout:2000,
//                    url: M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?sID='+studentID+'&qID='+qualID+'&type=studentsunits', 
//                    success: function(data)
//                    {
//                            $('#responseS'+studentID).html(data);
//                    }
//            });
//            return response;
//        }
//    });
    
    
    
        
    
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
                        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
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

                resizable: false,
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
                        var params = {action: 'unitComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments, imgID: imgID} };
                        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
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
    
    
    
    
    
    
    $('.bcgt_student_comments_dialog').each( function(indx, item){
        
        if (!$(item).hasClass('ui-dialog-content')){
            
            var studentID = $(item).attr('studentID');
            var qualID = $(item).attr('qualID');
            var unitID = $(item).attr('unitID');
            var gridType = $('#gridType').val();
            
            var btns = {
                    
                "Confirm": function(){

                    var comments = $('#student_response_comments_S'+studentID+'_U'+unitID+'_Q'+qualID).val();
                    var params = {action: 'confirmUnitCommentsRead', params: {studentID: studentID, qualID: qualID, unitID: unitID, studentComments: comments} };

                    $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update.php', params, function(data){
                        eval(data);
                    });

                },
                Cancel: function(){
                    $(this).dialog("close");
                }

            };
            
            // Unit grid we don't want the confirm button
            if (gridType == 'unit'){
                delete btns["Confirm"];
            }
            
            $(item).dialog({

                resizable: false,
                autoOpen: false,
                show: {
                    effect: "fade",
                    duration: 500
                },
                hide: {
                    effect: "fade",
                    duration: 500
                },
                buttons: btns

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
    
    
    // Student's Unit Comments
    $('.studentUnitComments').unbind('click');
    $('.studentUnitComments').bind('click', function(){
        
        var unitID = $(this).attr('unitID');
        var studentID = $(this).attr('studentID');
        var qualID = $(this).attr('qualID');
                
        $('#student_dialog_S'+studentID+'_U'+unitID+'_Q'+qualID).dialog("open");
                
        
    } );
    
//    
//    
//    
//    
//    
//    $('#commentClose a, #cancelComment').unbind('click');
//    $('#commentClose a, #cancelComment').bind('click', function(){
//        cmt.reset();
//        cmt.cancel();
//    });
    
//    $('#saveComment').unbind('click');
//    $('#saveComment').bind('click', function(){
//        
//        var comments = encodeURIComponent( $('#commentText').val() );
//        
//        // Criteria Comment
//        if (critID > 0){
//            var params = {action: 'criteriaComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments} };
//        }
//        
//        // Unit Comment
//        else if(critID < 0 && unitID > 0){
//            var params = {action: 'unitComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments} };
//        }
//        
//        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
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
//            var params = {action: 'criteriaComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments} };
//        }
//        
//        // Unit Comment
//        else if(critID < 0 && unitID > 0){
//            var params = {action: 'unitComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments} };
//        }
//        
//        
//        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
//            eval(data);
//        });
//        
//    });
    
//    // Tooltip to show comments if there are some
//    $('.showCommentsUnit').each( function(){
//        
//        var aria = $(this).attr('aria-describedby');
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//            
//            $(this).tooltip({
//                delay: 500, 
//                track: false,
//                showURL: false,
//                position: 'center right',
//                tipClass: 'bcgt_tooltip',
//                relative: true,
//                bodyHandler: function() {
//                    var text = $(this).siblings('.tooltipContent').text();
//                    return text;
//                }
//            });
//            
//        }
//        
//    } );

    $('.showCommentsUnit').unbind('click');
    $('.showCommentsUnit').bind('click', function(){
        
        $(this).siblings('.unitComment').dialog({
            resizable: false,
            close: function(ev, ui){
                $(this).dialog('destroy');
            }
        });
        
    });
    
    
    
//    
//    $('#saveQualComment').unbind('click');
//    $('#saveQualComment').bind('click', function(){
//                
//        var comments = encodeURIComponent( $('#qualComment textarea').val() );
//        
//        var params = {action: 'qualComment', params: {studentID: $(this).attr('studentid'), qualID: $(this).attr('qualid'), comment: comments, element: 'qualComment', grid: 'student'} };
//        
//        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
//            eval(data);
//        });
//        
//    });
    
    // Set class for background yellow on comments
    $('.editComments, .tooltipContent, .showCommentsUnit, .hasComments').each( function(){
        
        $($(this).parents('td')[0]).addClass('criteriaComments');
        
    } );
    
    
    
    $($('.stuValueNonEdit').parents('td')[0]).addClass('hand');
    
    
//    $('.criteriaComments').each( function(){
//        
//        var aria = $(this).attr('aria-describedby');
//        if (aria === undefined || (aria !== undefined && aria.search('ui-tooltip') < 0) ){
//
//            $(this).tooltip( {
//                content: function(){
//
//                    var tt = $(this).find('div.tooltipContent');
//                    var html = $(tt).html();
//                    return html;
//                }
//            });
//
//        }
//        
//    } );
    
//        
//    // Apply nice checkbox
//    $( "#showlate, #showlogs" ).button();
//    
//    $('#showlogs').bind('change', function(){
//        
//        var c = $(this).hasClass('ui-state-active');
//        if (c === true){
//            $(this).removeClass('ui-state-active');
//        } else {
//            $(this).addClass('ui-state-active');
//        }
//        
//        $('#gridLogs').toggle();
//        
//    });


    $('.bcgtDatePicker').datepicker( {
        dateFormat: "dd-mm-yy"
    } );
    
    var today = new Date();
    
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
            var el = $(this).attr('id');
            
            var params = {
                qualID: qualID,
                studentID: studentID,
                unitID: unitID,
                attribute: attribute,
                value: d,
                mode: $('#grid').val(),
                grid: grid,
                el: el
            };    
                        
            $('#studentGridDiv :input, #unitGridDiv :input').attr('disabled', 'disabled');
            
            $.post(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update.php', {action: 'updateUnitAttribute', params: params}, function(data){
               eval(data); 
               $('#studentGridDiv :input, #unitGridDiv :input').removeAttr('disabled');
            });

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
        var el = $(this).attr('id');
        
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
            grid: grid,
            el: el
        };   
        
        $('#studentGridDiv :input').attr('disabled', 'disabled');
            
        $.post(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update.php', {action: 'updateUnitAttribute', params: params}, function(data){
           eval(data); 
           $('#studentGridDiv :input').removeAttr('disabled');
        });
                        
    });
    
    
    
    
    
        
    $(document).on('click', 'body', function(){
        $('.ui-tooltip').fadeOut();
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
//        if (text != undefined){
//            /* Strip crap from body if only just added */
//            var regex = /<br\s*[\/]?>/gi;
//            text = text.replace(regex, "\n");
//
//            $("#commentText").val(text);
//        }
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

M.mod_bcgtbtec.btecaddactivity = function(Y) {
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

M.mod_bcgtbtec.btecmodactivity = function(Y) {
    //on change of uID
    var unitAdd = $('#bcgtAddUnitBtec');
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
                var unitID = $('#nUID').find(":selected").val();
                var span = $('#bcgtloadingbtec');
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
                          success: update_mod_page_btec
                      }
                  }
                  var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_mod_selection.php";
                  var request = Y.io(url, data);
           }); 
    }
    
    var qualUnitCheck = $('.qualunitcheck');
    if(qualUnitCheck)
    {
        $(qualUnitCheck).each(function(index){
            var unitID = $(this).attr('unit');
            checkModUnitQualCeck(unitID);
        });
    }
    
    apply_mod_tt_btec();
}

function update_mod_page_btec(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    if(json.retval != null)
    {
        var div = $('#bcgtMODAddUnitSelection');
        if(div)
        {
            div.append(json.retval);
        }
        var unitID = json.unit;
        //set selected index to blank.
        $("select#nUID").prop('selectedIndex', 0);
        //need to disable the option in the drop down. 
        $("select#nUID option[value='"+ unitID + "']").attr('disabled', true ); 
        //need to add the id to the hidden list of units we have selected.
        var unitsSelected = $('#bcgtunitsselected').val() + "_" + unitID;
        $('#bcgtunitsselected').val(unitsSelected);
        
        checkModUnitQualCeck(unitID);
    }

    var span = $('#bcgtloadingbtec');
    if(span)
    {
        span.html('');
    }
    apply_mod_tt_btec();
}

function apply_mod_tt_btec()
{
    //check the check boxes. 
    //if there are non checked, show a warning
    //if there are checked, hide the warning. 
    var qualUnitCheck = $('.qualunitcheck');
    if(qualUnitCheck)
    {
        $(qualUnitCheck).each(function(index){            
            $(this).unbind( "click" );
            $(this).on('click', function(e){
                var unitID = $(this).attr('unit');
                checkModUnitQualCeck(unitID);
            });
        });
    }
    
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
                $("select#nUID option[value='"+ unitID + "']").attr('disabled', false); 
                
                //need to remove the id from the hidden list of units we have selected.
                var unitsSelected = $('#bcgtunitsselected').val();
                unitsSelected.replace("_"+unitID,"");
                $('#bcgtunitsselected').val(unitsSelected);
            });
        });   
    }
}

function checkModUnitQualCeck(unitID)
{
    //so we have the unitID
    //are any of the others checked?
    var checked = false;
    var unitCheck = $('.qualunitcheck'+unitID);
    $(unitCheck).each(function(index2){
       if($(this).prop( "checked" ))
        {
            checked = true;
        }
    });
    if(checked)
    {
        $('#'+unitID+'_noqualswarn').css('visibility', 'hidden');
    }
    else
    {
        $('#'+unitID+'_noqualswarn').css('visibility', 'visible'); 
    }
}

M.mod_bcgtbtec.initactivitiescheck = function(Y) {
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



M.mod_bcgtbtec = {};

var changeType = false;
var change = false;
var globalQualID;
var globalActivityID;
M.mod_bcgtbtec.bteciniteditqual = function(Y) {
    
    Y.one('#save').set('disabled', 'disabled');
    
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
    
    var name = Y.one('#qualName');
    $('#qualName').unbind('keypress');
    name.on('keypress', function(e){
        check_btec_edit_qual_valid();
    });
    
    check_btec_edit_qual_valid();
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
    
    self.location='edit_qual.php?fID='+familyID+'&tID='+typeID+'&qID='+qualID+'&level='+levelID+'&subtype='+subTypeID+'&spec='+spec;
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

M.mod_bcgtbtec.initstudentgrid = function(Y, qualID, studentID, grid) {

    var qualID;
    var studentID;
    var order = 'spec';
    if($('#order'))
    {
        order = $('#order').attr('value');
    }
    $(document).ready(function() {
        var selects = Y.one('#selects');
        if(selects != null && selects.get('value') == "yes")
        {
            var select = Y.one("#qualChange");
            if(select)
            {
                var index = Y.one("#qualChange").get('selectedIndex');
                qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');  
            }
            else
            {
                qualID = Y.one('#qID').get('value');    
            }
            var index2 = Y.one("#studentChange").get('selectedIndex');
            studentID = Y.one("#studentChange").get("options").item(index2).getAttribute('value');
        }
        else
        {
            studentID = Y.one('#sID').get('value');
            qualID = Y.one('#qID').get('value');   
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
        
        draw_BTEC_student_table(qualID, studentID, grid, order);
    } );
    
    var refreshpredgrade = Y.one('.refreshpredgrade');
    if(refreshpredgrade)
    {
        refreshpredgrade.on('click', function(e){
            e.preventDefault();
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID': studentID
                },
                dataType: 'json',
                on: {
                    success: refresh_pred_grades
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/refresh_pred_grades.php";
            var request = Y.io(url, data);
        });
    }
    
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
            redraw_BTEC_student_table(qualID, studentID, 's', checked, order);
        });
    }
    
    
    var editsimple = Y.one('#editsimple');
    if (editsimple != null){
        editsimple.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 'se');
            redraw_BTEC_student_table(qualID, studentID, 'se', false, order);
        });
    }
    
    
    var editadvanced = Y.one('#editadvanced');
    if (editadvanced != null){
        editadvanced.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 'ae');
            redraw_BTEC_student_table(qualID, studentID, 'ae', false, order);
        });
    }
   
    var viewadvanced = Y.one('#viewadvanced');
    if (viewadvanced != null){
        viewadvanced.detach();
        viewadvanced.on('click', function(e){
            e.preventDefault();
            Y.one('#grid').set('value', 'a');
            redraw_BTEC_student_table(qualID, studentID, 'a', false, order);
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
            redraw_BTEC_student_table(qualID, studentID, 's', checked, order);
       });     
    }
    
    var orderChange = $('#order');
    if(orderChange)
    {
        orderChange.on('change',function(e){
//            $(":input").attr("disabled",true);
            var form = $('#studentGridForm');
            if(form)
            {
//                document.unitGridForm.submit();
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
}

M.mod_bcgtbtec.initactgrid = function(Y, qualID, grid, courseID, groupingID, columnsLocked, configColumnWidth, cmID ) 
{

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
        draw_BTEC_act_table(qualID, grid, courseID, groupingID, columnsLocked, configColumnWidth, cmID);
        
        
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
            redraw_BTEC_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
            
            $(this).addClass('active');
            
        });
    } );
    });
        
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
            redraw_BTEC_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
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
            redraw_BTEC_act_table(qualID, 'se', false, courseID, page, groupingID, cmID);
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
            redraw_BTEC_act_table(qualID, 'ae', false, courseID, page, groupingID, cmID);
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
            redraw_BTEC_act_table(qualID, 'a', false, courseID, page, groupingID, cmID);
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
            redraw_BTEC_act_table(qualID, 's', checked, courseID, page, groupingID, cmID);
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
}

function refresh_pred_grades(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);

    var mingrade = json.mingrade;
    var maxgrade = json.maxgrade;
    var avggrade = json.avggrade;
    
    var minAward = Y.one('#minAward');
    if(minAward)
    {
        minAward.set('innerHTML',mingrade);
    }
    var maxAward = Y.one('#maxAward');
    if(maxAward)
    {
        maxAward.set('innerHTML',maxgrade);
    }
    var qualAward = Y.one('#qualAward');
    if(qualAward)
    {
        qualAward.set('innerHTML',avggrade);
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

M.mod_bcgtbtec.initclassgrid = function(Y, qualID, columnsLocked, configColumnWidth) {

    var qualID;
    var courseID;
    var groupID;
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
}

M.mod_bcgtbtec.initunitgrid = function(Y, qualID, unitID, columnsLocked, configColumnWidth) {

    var qualID;
    var unitID;
    var courseID = -1;
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
            courseID = Y.one('#scID').get('value');   
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
        
        //need to get the grid
        var grid = $('#grid');
        var gridVal = grid.val();
        draw_BTEC_unit_table(qualID, unitID, gridVal, courseID, columnsLocked, configColumnWidth, order, groupingID);
        
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
            redraw_BTEC_unit_table(qualID, unitID, grid, checked, courseID, page, order, groupingID);
            
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
        redraw_BTEC_unit_table(qualID, unitID, 's', checked, courseID, page, order, groupingID);
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
        redraw_BTEC_unit_table(qualID, unitID, 'se', '', courseID, page, order, groupingID);
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
        redraw_BTEC_unit_table(qualID, unitID, 'ae', '', courseID, page, order, groupingID);
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
        redraw_BTEC_unit_table(qualID, unitID, 'a', '', courseID, page, order, groupingID);
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
            redraw_BTEC_unit_table(qualID, unitID, 's', checked, courseID, page, order, groupingID);
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
}

var draw_BTEC_unit_table = function(qualID, unitID, grid, courseID, columnsLocked, configColumnWidth, order, groupingID) { 
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
        "sAjaxSource": M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&scID="+courseID+"&order="+order+"&grID="+groupingID,
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
        
var redraw_BTEC_unit_table = function(qualID, unitID, grid, flag, courseID, page, order, groupingID) {
    $(":input").attr("disabled",true);
    var lock = false;
    if(grid == 'se' || grid == 'ae')
    {
        lock = true;
    }
    var oDataTable = $('#BTECUnitGrid').dataTable();
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_unit_grid.php?qID="+qualID+"&uID="+unitID+"&g="+grid+"&f="+flag+"&scID="+courseID+"&page="+page+"&lock="+lock+"&order="+order+"grID="+groupingID;
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
    var newUrl = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_act_grid.php?qID="+qualID+"&g="+grid+"&f="+flag+"&cID="+courseID+"&page="+page+"&lock="+lock+"&cmID="+cmID+"grID="+groupingID;
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
        if($('#qualAward_'.studentID))
        {
            $('#qualAward_'.studentID).text(""+json.qualaward.awardvalue+"");
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
    var selects = Y.one('#selects');
    var qualID, studentID;
    if(selects != null && selects.get('value') == "yes")
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
        var index2 = Y.one("#studentChange").get('selectedIndex');
        studentID = Y.one("#studentChange").get("options").item(index2).getAttribute('value');
    }
    else
    {
        studentID = Y.one('#sID').get('value');
        qualID = Y.one('#qID').get('value');   
    }
    
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
        
    var unitAward = Y.all('.unitAward');
    if(unitAward)
    {
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
    $('.uNToolTipInfo').tooltip({
        content: function(callback){
            
            // Get rid of any open ones
            $('.ui-tooltip').remove();
            
            var unitID = $(this).attr('unitID');
            
            $.get(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?uID='+unitID+'&type=crit', function(data){
                callback(data);
            });
            
        }
    }).off('mouseover')
    .on('click', function(){
        $(this).tooltip('open');
        return false;
    }).attr('title', 'Click me for more info')
    .css('cursor', 'pointer');

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
    $('.stuValueNonEdit').tooltip({
        content: function(callback){
            
            // Get rid of any open ones
            $('.ui-tooltip').remove();
            
            var idAttr = $(this).attr("id");
            var criteriaID = idAttr.split('_')[1];
            var studentID = idAttr.split('_')[5];
            var qualID = idAttr.split('_')[7];
            
            $.get(M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?qID='+qualID+'&sID='+studentID+'&cID='+criteriaID, function(data){
                callback(data);
            });
            
        }
    }).off('mouseover')
    .on('click', function(){
        $(this).tooltip('open');
        return false;
    }).attr('title', 'Click me for more info')
    .css('cursor', 'pointer');
   
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
    
    
    
//    Gets the student units
    $('.studentUnitInfo').tooltip({
        content: function(callback){
            
            // Get rid of any open ones
            $('.ui-tooltip').remove();
            
            var qualID = $(this).attr('qualID');
            var studentID = $(this).attr('studentID');
            
            $.get( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/get_tooltips.php?sID='+studentID+'&qID='+qualID+'&type=studentsunits' , function(data){
                callback(data);
            });
            
        }
    }).off('mouseover')
    .on('click', function(){
        $(this).tooltip('open');
        return false;
    }).attr('title', 'Click me for more info')
    .css('cursor', 'pointer');
    
    
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
    
    
    $('.criteriaComments').tooltip( {
        content: function(){
            
            // Get rid of any open ones
            $('.ui-tooltip').remove();
            
            var tt = $(this).find('div.tooltipContent');
            var html = $(tt).html();
            return html;
        }
    }  );
    
    $('.addComments').unbind('click');
    $('.addComments').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        
        var criteriaID = idAttr.split('_')[2];
        var unitID = idAttr.split('_')[4];
        var studentID = idAttr.split('_')[6];
        var qualID = idAttr.split('_')[8];

        var username = $(this).attr("username");
        var name = $(this).attr("fullname");
        var unitName = $(this).attr("unitname");
        var critName = $(this).attr("critname");
        
        var grid = $(this).attr("grid");
        
        cmt.setup(qualID, unitID, criteriaID, studentID, idAttr, grid);
        cmt.create("popUpDiv", username, name, unitName, critName);
                
        
    } );
    
    $('.editComments').unbind('click');
    $('.editComments').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        
        var criteriaID = idAttr.split('_')[2];
        var unitID = idAttr.split('_')[4];
        var studentID = idAttr.split('_')[6];
        var qualID = idAttr.split('_')[8];

        var username = $(this).attr("username");
        var name = $(this).attr("fullname");
        var unitName = $(this).attr("unitname");
        var critName = $(this).attr("critname");
        
        var grid = $(this).attr("grid");
        var text = $(this).siblings('.tooltipContent').text();
        
        cmt.setup(qualID, unitID, criteriaID, studentID, idAttr, grid);
        cmt.create("popUpDiv", username, name, unitName, critName, text);
                
        
    } );
    
    
    // Unit Comments
    $('.addCommentsUnit').unbind('click');
    $('.addCommentsUnit').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        
        var unitID = idAttr.split('_')[2];
        var studentID = idAttr.split('_')[4];
        var qualID = idAttr.split('_')[6];

        var username = $(this).attr("username");
        var name = $(this).attr("fullname");
        var unitName = $(this).attr("unitname");
        var critName = $(this).attr("critname");
        
        var grid = $(this).attr("grid");
        
        cmt.setup(qualID, unitID, -1, studentID, idAttr, grid);
        cmt.create("popUpDiv", username, name, unitName, critName);
                
        
    } );
    
    
    $('.editCommentsUnit').unbind('click');
    $('.editCommentsUnit').unbind('mouseover');
    $('.editCommentsUnit').bind('click', function(){
        
        var idAttr = $(this).attr("id");
        
        var unitID = idAttr.split('_')[2];
        var studentID = idAttr.split('_')[4];
        var qualID = idAttr.split('_')[6];

        var username = $(this).attr("username");
        var name = $(this).attr("fullname");
        var unitName = $(this).attr("unitname");
        var critName = $(this).attr("critname");
        
        var grid = $(this).attr("grid");
        var text = $(this).siblings('.tooltipContent').text();
        
        cmt.setup(qualID, unitID, -1, studentID, idAttr, grid);
        cmt.create("popUpDiv", username, name, unitName, critName, text);
                
        
    } );
    
    
    
    
    
    $('#commentClose a, #cancelComment').unbind('click');
    $('#commentClose a, #cancelComment').bind('click', function(){
        cmt.reset();
        cmt.cancel();
    });
    
    $('#saveComment').unbind('click');
    $('#saveComment').bind('click', function(){
        
        var comments = encodeURIComponent( $('#commentText').val() );
        
        // Criteria Comment
        if (critID > 0){
            var params = {action: 'criteriaComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments} };
        }
        
        // Unit Comment
        else if(critID < 0 && unitID > 0){
            var params = {action: 'unitComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments} };
        }
        
        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
            eval(data);
        });
        
                
    });
    
    $('#deleteComment').unbind('click');
    $('#deleteComment').bind('click', function(){
        
        $('#commentText').val('');
        var comments = '';
        
         // Criteria Comment
        if (critID > 0){
            var params = {action: 'criteriaComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, criteriaID: critID, grid: grid, comment: comments} };
        }
        
        // Unit Comment
        else if(critID < 0 && unitID > 0){
            var params = {action: 'unitComment', params: {element: cellID, studentID: studentID, qualID: qualID, unitID: unitID, grid: grid, comment: comments} };
        }
        
        
        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
            eval(data);
        });
        
    });
    
    // Tooltip to show comments if there are some
    $('.showCommentsUnit').tooltip({
        delay: 500, 
        track: false,
        showURL: false,
        position: 'center right',
        tipClass: 'bcgt_tooltip',
        relative: true,
        bodyHandler: function() {
            var text = $(this).siblings('.tooltipContent').text();
            return text;
        }
    });
    
    
    $('#saveQualComment').unbind('click');
    $('#saveQualComment').bind('click', function(){
                
        var comments = encodeURIComponent( $('#qualComment textarea').val() );
        
        var params = {action: 'qualComment', params: {studentID: $(this).attr('studentid'), qualID: $(this).attr('qualid'), comment: comments, element: 'qualComment', grid: 'student'} };
        
        $.post( M.cfg.wwwroot+'/blocks/bcgt/plugins/bcgtbtec/ajax/update_student_comments.php', params, function(data){
            eval(data);
        });
        
    });
    
    // Set class for background yellow on comments
    $('.tooltipContent').parents('td').addClass('criteriaComments');
    $('.tooltipContent').parents('td').attr('title', '');
    
    $(document).on('click', 'body', function(){
        $('.ui-tooltip').fadeOut();
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
        
        if (text != undefined){
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


function updateCommentCell(id, comment)
{
    
    comment = comment.replace(/\\n/g, "<br>");
    comment = $.trim(comment);
        
    var button = $('#'+id);
    
    // Empty comment, so set button to "add comment" style
    if(comment == "")
    {
            
        button.attr("class", "addComments");
        button.attr("title", "Click here to add comments");
        button.attr("alt", "Click here to add comments");     
        
        var img = button.attr('src').replace('comments.jpg', 'plus.png');
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
        
        var img = button.attr('src').replace('plus.png', 'comments.jpg');
        button.attr("src", img);

        // Remove the tooltip div
        button.siblings('.tooltipContent').remove();
        
        comment = decodeURIComponent(comment);

        // Add the tooltip div
        button.after("<div class='tooltipContent'>"+comment+"</div>");

    }

    applyTT();
    cmt.cancel();
    
}


function updateUnitCommentCell(id, comment)
{
    
    comment = comment.replace(/\\n/g, "<br>");
    comment = $.trim(comment);
        
    var button = $('#'+id);
    
    // Empty comment, so set button to "add comment" style
    if(comment == "")
    {
            
        button.attr("class", "addCommentsUnit");
        button.attr("title", "Click here to add comments");
        button.attr("alt", "Click here to add comments");     
        
        var img = button.attr('src').replace('comments.jpg', 'plus.png');
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
        
        var img = button.attr('src').replace('plus.png', 'comments.jpg');
        button.attr("src", img);

        // Remove the tooltip div
        button.siblings('.tooltipContent').remove();
        
        comment = decodeURIComponent(comment);

        // Add the tooltip div
        button.after("<div class='tooltipContent'>"+comment+"</div>");

    }

    applyTT();
    cmt.cancel();
    
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
    var unitAdd = $('#bcgtAddUnit');
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
                var span = $('#bcgtloading');
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
                  var url = M.cfg.wwwroot+"/blocks/bcgt/plugins/bcgtbtec/ajax/get_mod_selection.php";
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
        $('#bcgtunitsselected').val(unitsSelected)
    }
    var span = $('#bcgtloading');
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
                $("select#nUID option[value='"+ unitID + "']").attr('disabled', false); 
                
                //need to remove the id from the hidden list of units we have selected.
                var unitsSelected = $('#bcgtunitsselected').val();
                unitsSelected.replace("_"+unitID,"");
                $('#bcgtunitsselected').val(unitsSelected);
            });
        });   
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



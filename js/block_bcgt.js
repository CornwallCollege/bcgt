/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */

M.block_bcgt = {};
 
M.block_bcgt.init = function(Y) {
    
    //all of the initialised functions required. 
//    var searchButton = Y.one('#search');
//    searchButton.on('click', helloWorld);
//    
//    var searchText = Y.one('#searchStudent');
//    searchText.on('change', helloWorld);

    //go to course:
//    
//    var goToCourse = $('#gotocourse');
//    if(goToCourse)
//    {
//        $('#gotocourse').on('change',function(){
//           //then we want to go to that course (unless its -1)
//            var courseID = $(this).val();
//            if(courseID != -1)
//            {
//                location = '../../../course/view.php?id='+courseID;
//            }
//        });
//    }

    var goToCourse = $('#courseGo');
    if(goToCourse)
    {
       $('#courseGo').on('click',function(e){
            //then we want to go to that course (unless its -1)
            var courseID = $('#gotocourse').find(":selected").val();
            if(courseID == -1)
            {
                e.preventDefault();
                location = '#';
            }
        });       
    }
};

M.block_bcgt.initgridfaclass = function(Y) {
   $(document).ready(function() {
       apply_alps_class_grid_calls(Y);
   });
}




function apply_alps_class_grid_calls(Y)
{
    process_alps_ceta();
       
    var alpsfagrade = Y.all('.faGradeAlps');
     if(alpsfagrade)
     {
         //get the project id
         //get the qualid
         //get the userid
         alpsfagrade.each(function(cell){
             var qualID = cell.getAttribute('qual');
             var projectid = cell.getAttribute('project');
             var groupID = -1;
             if($('#grID') && $('#grID').val() && typeof($('#grID').val()) !== "undefined")
            {
                groupID = $('#grID').val();
            }
             
             var div = Y.one('#faGradeAlps_'+projectid+'_'+qualID);
             var type = 'class';
             var subtype = 'projectg';
             if(div)
             {
                 div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
             }
             var data = {
                 method: 'POST',
                 data: {
                     'qID' : qualID,
                     'sID' : -1,
                     'assID' : projectid,
                     'type' : type,
                     'subtype' : subtype,
                     'grID' : groupID,
                     'score': true
                 },
                 dataType: 'json',
                 on: {
                     success: display_alps_report
                 }
             }
             var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
             var request = Y.io(url, data);
         });
     }

     var alpsfaceta = Y.all('.faCetaAlps');
     if(alpsfaceta)
     {
         //get the project id
         //get the qualid
         //get the userid
         alpsfagrade.each(function(cell){
             var qualID = cell.getAttribute('qual');
             var projectid = cell.getAttribute('project');
             var groupID = -1;
             if($('#grID') && $('#grID').val() && typeof($('#grID').val()) !== "undefined")
            {
                groupID = $('#grID').val();
            }
             var div = Y.one('#faCetaAlps_'+projectid+'_'+qualID);
             var type = 'class';
             var subtype = 'projectc';
             if(div)
             {
                 div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
             }
             var data = {
                 method: 'POST',
                 data: {
                     'qID' : qualID,
                     'sID' : -1,
                     'assID' : projectid,
                     'type' : type,
                     'subtype' : subtype,
                     'grID' : groupID,
                     'score': true
                 },
                 dataType: 'json',
                 on: {
                     success: display_alps_report
                 }
             }
             var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
             var request = Y.io(url, data);
         });
     }

     var alpsclass = Y.all('.alpsclass');
     if(alpsclass)
     {
         //get the project id
         //get the qualid
         //get the userid
         alpsclass.each(function(cell){
             var qualID = cell.getAttribute('qual');
             var groupID = -1;
             if($('#grID') && $('#grID').val() && typeof($('#grID').val()) !== "undefined")
            {
                groupID = $('#grID').val();
            }
             var div = Y.one('#alpsclass_'+qualID);
             var type = 'class';
             var subtype = 'all';
             if(div)
             {
                 div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
             }
             var data = {
                 method: 'POST',
                 data: {
                     'qID' : qualID,
                     'sID' : -1,
                     'assID' : -1,
                     'type' : type,
                     'subtype' : subtype,
                     'grID' : groupID,
                     'score': true
                 },
                 dataType: 'json',
                 on: {
                     success: display_alps_report
                 }
             }
             var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
             var request = Y.io(url, data);
         });
     }

     var alpsGradeBook= Y.all('.gradeBookAlps');
     if(alpsGradeBook)
     {
         //get the project id
         //get the qualid
         //get the userid
         var userID = $('#studentid').val();
         alpsGradeBook.each(function(cell){
             var qualID = cell.getAttribute('qual');
             var courseID = cell.getAttribute('courseid');
             var gradebookid = cell.getAttribute('gid');
             var div = Y.one('#gbalps_'+gradebookid+'_'+courseID);
             var type = 'class';
             var subtype = 'gbook';
             if(div)
             {
                 div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
             }
             var data = {
                 method: 'POST',
                 data: {
                     'qID' : qualID,
                     'sID' : userID,
                     'assID' : gradebookid,
                     'type' : type,
                     'subtype' : subtype,
                     'courseid' : courseID,
                     'score': true
                 },
                 dataType: 'json',
                 on: {
                     success: display_alps_report
                 }
             }
             var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
             var request = Y.io(url, data);
         });
     }
}

function apply_alps_stu_grid_calls(Y)
{
    process_alps_ceta();
        
    var alpsfa = Y.all('.alpsfa');
    if(alpsfa)
    {
        alpsfa.each(function(cell){
            var qualID = cell.getAttribute('qual');
            var studentID = cell.getAttribute('user');
            var type = 'student';
            var subtype = 'fa';

            var div = Y.one('#alpsfa_'+qualID+'_'+studentID);
            if(div)
            {
                div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID' : studentID,
                    'type' : type,
                    'subtype' : subtype,
                    'score': true
                },
                dataType: 'json',
                on: {
                    success: display_alps_report
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
            var request = Y.io(url, data);
        });
    }

    var alpsall = Y.all('.alpsall');
    if(alpsall)
    {
        alpsall.each(function(cell){
            var qualID = cell.getAttribute('qual');
            var studentID = cell.getAttribute('user');
            var type = 'student';
            var subtype = 'all';

            var div = Y.one('#alpsall_'+qualID+'_'+studentID);
            if(div)
            {
                div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID' : studentID,
                    'type' : type,
                    'subtype' : subtype,
                    'score': true
                },
                dataType: 'json',
                on: {
                    success: display_alps_report
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
            var request = Y.io(url, data);
        });
    } 

    var alpsfagrade = Y.all('.faGradeAlps');
    if(alpsfagrade)
    {
        //get the project id
        //get the qualid
        //get the userid
        var userID = $('#studentid').val();
        alpsfagrade.each(function(cell){
            var qualID = cell.getAttribute('qual');
            var projectid = cell.getAttribute('project');
            var div = Y.one('#faGradeAlps_'+projectid+'_'+qualID);
            var type = 'student';
            var subtype = 'projectg';
            if(div)
            {
                div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID' : userID,
                    'assID' : projectid,
                    'type' : type,
                    'subtype' : subtype,
                    'score': true
                },
                dataType: 'json',
                on: {
                    success: display_alps_report
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
            var request = Y.io(url, data);
        });
    }

    var alpsfaceta = Y.all('.faCetaAlps');
    if(alpsfaceta)
    {
        //get the project id
        //get the qualid
        //get the userid
        var userID = $('#studentid').val();
        alpsfagrade.each(function(cell){
            var qualID = cell.getAttribute('qual');
            var projectid = cell.getAttribute('project');
            var div = Y.one('#faCetaAlps_'+projectid+'_'+qualID);
            var type = 'student';
            var subtype = 'projectc';
            if(div)
            {
                div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID' : userID,
                    'assID' : projectid,
                    'type' : type,
                    'subtype' : subtype,
                    'score': true
                },
                dataType: 'json',
                on: {
                    success: display_alps_report
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
            var request = Y.io(url, data);
        });
    }

    var alpsGradeBook= Y.all('.gradeBookAlps');
    if(alpsGradeBook)
    {
        //get the project id
        //get the qualid
        //get the userid
        var userID = $('#studentid').val();
        alpsGradeBook.each(function(cell){
            var qualID = cell.getAttribute('qual');
            var courseID = cell.getAttribute('courseid');
            var gradebookid = cell.getAttribute('gid');
            var div = Y.one('#gbalps_'+gradebookid+'_'+courseID);
            var type = 'student';
            var subtype = 'gbook';
            if(div)
            {
                div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID' : userID,
                    'assID' : gradebookid,
                    'type' : type,
                    'subtype' : subtype,
                    'courseid' : courseID,
                    'score': true
                },
                dataType: 'json',
                on: {
                    success: display_alps_report
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
            var request = Y.io(url, data);
        });
    }
}

function process_alps_ceta()
{
    //get the alps
    var alpsceta = Y.all('.alpsceta');
    if(alpsceta)
    {
        alpsceta.each(function(cell){
            var qualID = cell.getAttribute('qual');
            var studentID = cell.getAttribute('user');
            var type = 'student';
            var subtype = 'ceta';
            var div = Y.one('#alpsceta_'+qualID+'_'+studentID);
            if(div)
            {
                div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var div2 = Y.one('#alpsceta_'+qualID+'_'+studentID+'_2');
            if(div2)
            {
                div2.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
            }
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sID' : studentID,
                    'type' : type,
                    'subtype' : subtype,
                    'score': true
                },
                dataType: 'json',
                on: {
                    success: display_alps_report
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_alps_report.php";
            var request = Y.io(url, data);
        });
    }
}

function display_alps_report(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    if(json.display != null)
    {
        var qualID = json.qualid;
        var userID = json.userid;
        var assID = json.assid;
        var display = json.display;
        var type = json.type;
        var subtype = json.subtype;
        var courseID = json.courseid;
        switch(type)
        {
            case "student":
                switch(subtype)
                {
                    case"ceta":
                        set_div_content('#alpsceta_'+qualID+'_'+userID,display);
                        set_div_content('#alpsceta_'+qualID+'_'+userID+'_2',display);
                        break;
                    case"fa":
                        set_div_content('#alpsfa_'+qualID+'_'+userID,display);
                        break;
                    case"all":
                        set_div_content('#alpsall_'+qualID+'_'+userID,display);
                        break;
                    case"projectg":
                        set_div_content('#faGradeAlps_'+assID+'_'+qualID,display);
                        break;
                    case"projectc":
                        set_div_content('#faCetaAlps_'+assID+'_'+qualID,display);
                        break;
                    case"gbook":
                        set_div_content('#gbalps_'+assID+'_'+courseID,display);
                        break;
                }
                break;
            case "class":
                switch(subtype)
                {
                    case"projectg":
                        set_div_content('#faGradeAlps_'+assID+'_'+qualID,display);
                        break;
                    case"projectc":
                        set_div_content('#faCetaAlps_'+assID+'_'+qualID,display);
                        break;
                    case"all":
                        set_div_content('#alpsclass_'+qualID,display);
                        break;
                    case"gbook":
                        set_div_content('#gbalps_'+assID+'_'+courseID,display);
                        break;
                }
        }
    }
    
    //now we are going to reapply the colour coding:
    $("span.alpstemp1").closest("td").css("background-color", "#9E1616");
    $("span.alpstemp2").closest("td").css("background-color", "#B14545");
    $("span.alpstemp3").closest("td").css("background-color", "#C87D7D");
    $("span.alpstemp4").closest("td").css("background-color", "#999999");
    $("span.alpstemp5").closest("td").css("background-color", "#000000");
    $("span.alpstemp6").closest("td").css("background-color", "#999999");
    $("span.alpstemp7").closest("td").css("background-color", "#8080E6");
    $("span.alpstemp8").closest("td").css("background-color", "#0000CC");
    $("span.alpstemp9").closest("td").css("background-color", "#00008F");
    
    $("span.alpstemp1").closest("th").css("background-color", "#9E1616");
    $("span.alpstemp2").closest("th").css("background-color", "#B14545");
    $("span.alpstemp3").closest("th").css("background-color", "#C87D7D");
    $("span.alpstemp4").closest("th").css("background-color", "#999999");
    $("span.alpstemp5").closest("th").css("background-color", "#000000");
    $("span.alpstemp6").closest("th").css("background-color", "#999999");
    $("span.alpstemp7").closest("th").css("background-color", "#8080E6");
    $("span.alpstemp8").closest("th").css("background-color", "#0000CC");
    $("span.alpstemp9").closest("th").css("background-color", "#00008F");
}

function set_div_content(divID, content)
{
    var div = Y.one(divID);
    if(div)
    {
        //clear the loading gif first
        div.set('innerHTML', '');
        div.set('innerHTML', content);
    }
}

M.block_bcgt.initalpstab = function(Y) {
   
   apply_alps_reporting_tt();
}

function apply_alps_reporting_tt()
{
    //for each expand class
    //get the type
    //get the val
    var expands = $('.expand');
    if(expands)
    {
        expands.each(function(expand){
            $(this).unbind('click');
            $(this).on('click', function(e){
                //need to get the value
                //need to get the type
                var type = $(this).attr('type');
                var value = $(this).attr('val');
                var courseID = $('#cid').val();
                
                //are we showing or removing?
                var add = true;
                var loadString = '';
                var alpsrowsloaded = $('#alpsrows').val();
                if(alpsrowsloaded)
                {
                    var rowsLoaded = alpsrowsloaded.split('|');
                    if(rowsLoaded.length > 0)
                    {
                        for(var i=0;i<rowsLoaded.length;i++)
                        {
                            if(rowsLoaded[i] == type+'_'+value)
                            {
                                //then its loaded before, so we are just removing them
                                //All child elements will have an attribute of type and the value
                                remove_alps_rows(type, value);
                                //need to take this out of the string
                                add = false;
                                break;
                            }
                            else
                            {
                                //build the string up
                                loadString = loadString + rowsLoaded[i]+'|';
                            }
                                
                        }
                    }
                }
                //add the string back
                $('#alpsrows').val(loadString);
                if(add)
                {
                    var div = Y.one('#expand');
                    if(div)
                    {
                        div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
                    }

                    var data = {
                        method: 'POST',
                        data: {
                            'type' : type,
                            'val' : value,
                            'cid' : courseID
                        },
                        dataType: 'json',
                        on: {
                            success: expand_report
                        }
                    }
                    var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/expand_report.php";
                    var request = Y.io(url, data); 
                }
            }); 
        });
    }
}

function remove_alps_rows(type, value)
{
    var rows = $("tr["+type+"='"+value+"']");
    if(rows)
    {
        rows.each(function(row){

            var rem = $(this).attr('rem');
            if(rem)
            {
                //split rem on the _
                var subtype = rem.split("_")[0];
                var subvalue = rem.split("_")[1];
                //then you would look for their sub and so on
                remove_alps_rows(subtype, subvalue);
            }
            $(this).remove();
           
        });
        //these rows will be deleted
        //also need to get the rem attribute of the 
    }
}

function expand_report(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    var display = json.display;
    var type = json.type;
    var value = json.val;
    $('#e_'+type+'_'+value).after(display);
    $('#expand').html('');
    var alpsrowsloaded = $('#alpsrows').val();
    alpsrowsloaded = alpsrowsloaded + '|'+type+'_'+value;
    $('#alpsrows').val(alpsrowsloaded);
    apply_alps_reporting_tt();
}

//function display_alps_report(id, o)
//{
//    var data = o.responseText; // Response data.
//    var json = Y.JSON.parse(o.responseText);
//    if(json.display != null)
//    {
//        var qualID = json.qualid;
//        var userID = json.userid;
//        var assID = json.assid;
//        var display = json.display;
//        var type = json.type;
//        var subtype = json.subtype;
//        var courseID = json.courseid;
//        var fam = json.fam;
//        var typeID = json.typeid;
//        switch(type)
//        {
//            case "student":
//                switch(subtype)
//                {
//                    case"ceta":
//                        set_div_content('#alpsceta_'+qualID+'_'+userID,display);
//                        set_div_content('#alpsceta_'+qualID+'_'+userID+'_2',display);
//                        break;
//                    case"fa":
//                        set_div_content('#alpsfa_'+qualID+'_'+userID,display);
//                        break;
//                    case"all":
//                        set_div_content('#alpsall_'+qualID+'_'+userID,display);
//                        break;
//                    case"projectg":
//                        set_div_content('#faGradeAlps_'+assID+'_'+qualID,display);
//                        break;
//                    case"projectc":
//                        set_div_content('#faCetaAlps_'+assID+'_'+qualID,display);
//                        break;
//                    case"gbook":
//                        set_div_content('#gbalps_'+assID+'_'+courseID,display);
//                        break;
//                }
//                break;
//            case "class":
//                switch(subtype)
//                {
//                    case"projectg":
//                        set_div_content('#faGradeAlps_'+assID+'_'+qualID,display);
//                        break;
//                    case"projectc":
//                        set_div_content('#faCetaAlps_'+assID+'_'+qualID,display);
//                        break;
//                    case"all":
//                        set_div_content('#alpsclass_'+qualID,display);
//                        break;
//                    case"gbook":
//                        set_div_content('#gbalps_'+assID+'_'+courseID,display);
//                        break;
//                }
//            case "family":
//                switch(subtype)
//                {
//                    case"all":
//                        set_div_content('#famOverall_'+fam, display);
//                        break;
//                    case"fag":
//                        set_div_content('#famFAG_'+fam+'_'+assID, display);
//                        break;
//                    case"fac":
//                        set_div_content('#famFAC_'+fam+'_'+assID, display);
//                        break;
//                }
//            case "famType":
//                switch(subtype)
//                {
//                    case"all":
//                        set_div_content('#famType_'+typeID, display);
//                        break;
//                    case"fag":
//                        set_div_content('#famTFAG_'+typeID+'_'+assID, display);
//                        break;
//                    case"fac":
//                        set_div_content('#famTFAC_'+typeID+'_'+assID, display);
//                        break;
//                }
//        }
//    }
//    
//    //now we are going to reapply the colour coding:
//
//    
//    apply_alps_colour_coding();
//}

function apply_alps_colour_coding()
{
    $("span.alpstemp1").closest("td").css("background-color", "#9E1616");
    $("span.alpstemp2").closest("td").css("background-color", "#B14545");
    $("span.alpstemp3").closest("td").css("background-color", "#C87D7D");
    $("span.alpstemp4").closest("td").css("background-color", "#999999");
    $("span.alpstemp5").closest("td").css("background-color", "#000000");
    $("span.alpstemp6").closest("td").css("background-color", "#999999");
    $("span.alpstemp7").closest("td").css("background-color", "#8080E6");
    $("span.alpstemp8").closest("td").css("background-color", "#0000CC");
    $("span.alpstemp9").closest("td").css("background-color", "#00008F");
    
    $("span.alpstemp1").closest("th").css("background-color", "#9E1616");
    $("span.alpstemp2").closest("th").css("background-color", "#B14545");
    $("span.alpstemp3").closest("th").css("background-color", "#C87D7D");
    $("span.alpstemp4").closest("th").css("background-color", "#999999");
    $("span.alpstemp5").closest("th").css("background-color", "#000000");
    $("span.alpstemp6").closest("th").css("background-color", "#999999");
    $("span.alpstemp7").closest("th").css("background-color", "#8080E6");
    $("span.alpstemp8").closest("th").css("background-color", "#0000CC");
    $("span.alpstemp9").closest("th").css("background-color", "#00008F");
}

function set_div_content(divID, content)
{
    var div = Y.one(divID);
    if(div)
    {
        //clear the loading gif first
        div.set('innerHTML', '');
        div.set('innerHTML', content);
    }
}

M.block_bcgt.inittrackerstab = function(Y) {
    
    var buttons = Y.all('.simplequalreportheading');
    if(buttons)
    {
        buttons.each( function(button){
            button.on('click', function(event){  
                var qual = button.getAttribute('id');
                var quals = qual.split("_");
                var qualID = quals[1];
                var type = quals[2];
                var div = Y.one('#sqrc_'+qualID+'_'+type);
                var cID = $('#cID').val();
                if(div)
                {
                    div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
                }
                //sqrh_$qual->id
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : -1,
                        'type' : type,
                        'cID' : cID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    applyReportingTT();
    //all of the initialised functions required. 
//    var searchButton = Y.one('#search');
//    searchButton.on('click', helloWorld);
//    
//    var searchText = Y.one('#searchStudent');
//    searchText.on('change', helloWorld);
};

M.block_bcgt.initgroupstab = function(Y) 
{
    
    var buttons = Y.all('.simplegroupreportheading');
    if(buttons)
    {
        buttons.each( function(button){
            button.on('click', function(event){
                var grouping = button.getAttribute('id');
                //this is actually grouping
                var groupings = grouping.split("_");
                var groupingID = groupings[1];
                var type = groupings[2];
                var courseID = $('#cID').val();
                var div = Y.one('#sqrc_'+groupingID+'_'+type);
                if(div)
                {
                    div.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
                }
                //sqrh_$qual->id
                var data = {
                    method: 'POST',
                    data: {
                        'grID' : groupingID,
                        'qID' : -1,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    applyReportingTT();
    //all of the initialised functions required. 
//    var searchButton = Y.one('#search');
//    searchButton.on('click', helloWorld);
//    
//    var searchText = Y.one('#searchStudent');
//    searchText.on('change', helloWorld);
};

function display_simple_qual_report(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    if(json.retval != null)
    {
        var tab = json.tab;
        
        var qualID = json.qualid;
        var groupID = json.groupingid;
        var display = json.retval;
        var type = json.type;
        var idUse;
        if(qualID && qualID != -1)
        {
            idUse = qualID;
        }
        else if(groupID && groupID != -1)
        {
            idUse = groupID;
        }
        var div = Y.one('#sqrc_'+idUse+'_'+type);
        if(div)
        {
            //clear the loading gif first
            div.set('innerHTML', '');
            div.set('innerHTML', display);
        }
        //clear the loading gif on the tabs
        $('.'+idUse+'loading').html('');
        if(tab == 'co' && $('#classGrid_'+idUse+'_'+type))
        {
            var oTable = $('#classGrid_'+idUse+'_'+type).dataTable( {
                "sScrollX": "100%",
                "sScrollY": "600px",
                "bScrollCollapse": true,
                "bPaginate": false,
                "bSort":false,
                "bInfo":false,
                "bFilter":false
            });

            var fCol = new FixedColumns( oTable, {
                        "iLeftColumns": 3,
                        "iLeftWidth": 220 
                    } );
        }
    }   
    applyReportingTT();
}

function applyReportingTT()
{
    var tabs = Y.all('.tab');
    if(tabs)
    {
        tabs.each( function(tab){
            tab.detach('click');
            tab.on('click', function(event){
                var id = tab.getAttribute('id');
                //loading symbol!
                var span = Y.one('#'+id+'loading');
                if(span)
                {
                    span.set('innerHTML', '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" alt="" />');
                }
                var qualID = tab.getAttribute('qual');
                var groupID = tab.getAttribute('group');
                var actualTab = tab.getAttribute('tab');
                var tabType = tab.getAttribute('tabtype');
                var courseID = tab.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'tab' : actualTab,
                        'grID' : groupID,
                        'type' : tabType,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    
    var close = Y.all('.closereport');
    if(close)
    {
        close.each( function(close){
            close.detach('click');
            close.on('click', function(event){
                var id = close.getAttribute('id');
                var type = close.getAttribute('tabtype');
                var div = Y.one('#sqrc_'+id+'_'+type);
                if(div)
                {
                    div.set('innerHTML', '');  
                }
            });
        });
    }
    
    var edits = Y.all('.edit');
    if(edits)
    {
        edits.each(function(edit){
            edit.detach('click');
            edit.on('click', function(event){
                event.preventDefault();
                var qualID = edit.getAttribute('qual');
                var groupID = edit.getAttribute('group');
                var actualTab = edit.getAttribute('tab');
                var type = edit.getAttribute('tabtype');
                var courseID = edit.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'tab' : actualTab,
                        'grID' : groupID,
                        'edit' : true,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
            });
        });
    }
    
    var views = Y.all('.view');
    if(views)
    {
        views.each(function(view){
            view.detach('click');
            view.on('click', function(event){
                event.preventDefault();
                var qualID = view.getAttribute('qual');
                var groupID = view.getAttribute('group');
                var actualTab = view.getAttribute('tab');
                var type = view.getAttribute('tabtype');
                var courseID = view.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'tab' : actualTab,
                        'grID' : groupID,
                        'edit' : false,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);

            });
        });
    }
    
    var edittargets = Y.all('.edittarget');
    if(edittargets)
    {
        edittargets.each( function(edittarget){
            edittarget.detach('click');
            edittarget.on('change', function(event){
                var qualID = edittarget.getAttribute('qual');
                var groupID = edittarget.getAttribute('group');
                var idUse = qualID;
                if(groupID && groupID != -1)
                {
                    idUse = groupID;
                }
                var sID = edittarget.getAttribute('sid');
                var index = Y.one("#t_"+qualID+"_s_"+sID).get('selectedIndex');
                var value = Y.one("#t_"+qualID+"_s_"+sID).get("options").item(index).getAttribute('value');
                var cID = edittarget.getAttribute('cid');
                var type = edittarget.getAttribute('type');
                if (Y.one("#uf_"+idUse) !== null){
                    var index = Y.one("#uf_"+idUse).get('selectedIndex');
                    var ufilter = Y.one("#uf_"+idUse).get("options").item(index).getAttribute('value');
                } else {
                    var ufilter = '';
                }
                if (Y.one("#tf_"+idUse) !== null){
                    var index = Y.one("#tf_"+idUse).get('selectedIndex');
                    var tfilter = Y.one("#tf_"+idUse).get("options").item(index).getAttribute('value');
                } else {
                    var tfilter = '';
                }
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : groupID,
                        'ufilter' : ufilter,
                        'tfilter' : tfilter,
                        'value' : value,
                        'type' : type,
                        'sID' : sID,
                        'cID' : cID
                    },
                    dataType: 'json',
                    on: {
//                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/update_user_target.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    
    
    
    
    // This only works if we have the elbp block
    // Need to change it at some point so it can work without it
    // Will wait until someone notices and complains as I have a sore throat and coming down with a cold and can't be bothered.
    $('.update_asp_grade').on('change', function(){
        
        var studentID = $(this).attr('studentid');
        var qualID = $(this).attr('qualid');
        var value = $(this).val();
        
        var params = { 
            studentID: studentID,
            qualID: qualID,
            aspirationalgrade: value,
            ignoreTarget: true
        };
        
        var data = { action: 'save', params: params };
        
        $.post(M.cfg.wwwroot + '/blocks/bcgt/ajax/elbp_target_grades/ajax.php', data, function(d){
            $('#edit_asp_select_'+studentID+'_'+qualID).parent().effect( "highlight", {color: '#ccff66'}, 3000 );
        });
        
    });
    
    
    
    
    
    var editasps = Y.all('.editasp');
    if(editasps)
    {
        editasps.each( function(editasp){
            editasp.detach('click');
            editasp.on('change', function(event){
                var qualID = editasp.getAttribute('qual');
                var groupID = editasp.getAttribute('group');
                var idUse = qualID;
                if(groupID && groupID != -1)
                {
                    idUse = groupID;
                }
                var sID = editasp.getAttribute('sid');
                var index = Y.one("#a_"+qualID+"_s_"+sID).get('selectedIndex');
                var value = Y.one("#a_"+qualID+"_s_"+sID).get("options").item(index).getAttribute('value');
                var cID = editasp.getAttribute('cid');
                var type = editasp.getAttribute('type');
                var index = Y.one("#uf_"+idUse).get('selectedIndex');
                var ufilter = Y.one("#uf_"+idUse).get("options").item(index).getAttribute('value');
                var index = Y.one("#tf_"+idUse).get('selectedIndex');
                var tfilter = Y.one("#tf_"+idUse).get("options").item(index).getAttribute('value');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : groupID,
                        'ufilter' : ufilter,
                        'tfilter' : tfilter,
                        'value' : value,
                        'type' : type,
                        'sID' : sID,
                        'cID' : cID
                    },
                    dataType: 'json',
                    on: {
//                        success: display_simple_qual_report
                    }
                }
//                alert(JSON.stringfy(data));
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/update_user_target.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    
    var unitFilters = Y.all('.unitFilter');
    if(unitFilters)
    {
        unitFilters.each( function(unitFilter){
            unitFilter.detach('click');
            unitFilter.on('change', function(event){
                var qualID = unitFilter.getAttribute('qual');
                var groupID = unitFilter.getAttribute('group');
                var editing = Y.one("#editing").get("value");
                var idUse = qualID;
                if(groupID && groupID != -1)
                {
                    //then we are using the groupid
                    idUse = groupID;
                }
                var index = Y.one("#uf_"+idUse).get('selectedIndex');
                var ufilter = Y.one("#uf_"+idUse).get("options").item(index).getAttribute('value');
                var index = Y.one("#tf_"+idUse).get('selectedIndex');
                var tfilter = Y.one("#tf_"+idUse).get("options").item(index).getAttribute('value');
                var actualTab = unitFilter.getAttribute('tab');
                var type = unitFilter.getAttribute('tabtype');
                var courseID = unitFilter.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : groupID,
                        'ufilter' : ufilter,
                        'tfilter' : tfilter,
                        'tab' : actualTab,
                        'edit' : editing,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    
    var targetFilters = Y.all('.targetFilter');
    if(targetFilters)
    {
        targetFilters.each( function(targetFilter){
            targetFilter.on('change', function(event){
                var qualID = targetFilter.getAttribute('qual');
                var groupID = targetFilter.getAttribute('group');
                var editing = Y.one("#editing").get("value");
                var idUse = qualID;
                if(groupID && groupID != -1)
                {
                    //then we are using the groupid
                    idUse = groupID;
                }
                var index = Y.one("#uf_"+idUse).get('selectedIndex');
                var ufilter = Y.one("#uf_"+idUse).get("options").item(index).getAttribute('value');
                var index = Y.one("#tf_"+idUse).get('selectedIndex');
                var tfilter = Y.one("#tf_"+idUse).get("options").item(index).getAttribute('value');
                var tabType = targetFilter.getAttribute('tab');
                var type = targetFilter.getAttribute('tabtype');
                var courseID = targetFilter.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : groupID,
                        'ufilter' : ufilter,
                        'tfilter' : tfilter,
                        'tab' : tabType,
                        'edit' : editing,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    
    var sortheads = Y.all('.sorthead');
    if(sortheads)
    {
        sortheads.each( function(sorthead){
            sorthead.detach('click');
            sorthead.on('click', function(event){
                event.preventDefault();
                var qualID = sorthead.getAttribute('qual');
                var groupID = sorthead.getAttribute('group');
                var idUse = qualID;
                if(groupID && groupID != -1)
                {
                    //then we are using the groupid
                    idUse = groupID;
                }
                var editing = Y.one("#editing").get("value");
                var index = Y.one("#uf_"+idUse).get('selectedIndex');
                var ufilter = Y.one("#uf_"+idUse).get("options").item(index).getAttribute('value');
                var index = Y.one("#tf_"+idUse).get('selectedIndex');
                var tfilter = Y.one("#tf_"+idUse).get("options").item(index).getAttribute('value');
                var tabType = sorthead.getAttribute('tab');
                var currentSort = Y.one("#sorting").get("value");
                var thisSort = sorthead.getAttribute('sortname');
                var currentSort = currentSort + ',' + thisSort;
                var type = sorthead.getAttribute('tabtype');
                var courseID = sorthead.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : groupID,
                        'ufilter' : ufilter,
                        'tfilter' : tfilter,
                        'tab' : tabType,
                        'edit' : editing,
                        'sort' : currentSort,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }
    
    var usortheads = Y.all('.usorthead');
    if(usortheads)
    {
        usortheads.each( function(sorthead){
            sorthead.detach('click');
            sorthead.on('click', function(event){
                event.preventDefault();
                var qualID = sorthead.getAttribute('qual');
                var groupID = sorthead.getAttribute('group');
                var tabType = sorthead.getAttribute('tab');
                var currentSort = Y.one("#usorting").get("value");
                var thisSort = sorthead.getAttribute('sortname');
                var currentSort = currentSort + ',' + thisSort;
                var type = sorthead.getAttribute('tabtype');
                var courseID = sorthead.getAttribute('course');
                var data = {
                    method: 'POST',
                    data: {
                        'qID' : qualID,
                        'grID' : groupID,
                        'tab' : tabType,
                        'sort' : currentSort,
                        'type' : type,
                        'cID' : courseID
                    },
                    dataType: 'json',
                    on: {
                        success: display_simple_qual_report
                    }
                }
                var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_simple_qual_report.php";
                var request = Y.io(url, data);
                
            });
        });
    }    
}

M.block_bcgt.initeditqual = function(Y) {
//    Y.one('#save').set('disabled', 'disabled');
    
    var qualFamily = Y.one('#qualFamilySelect');
    qualFamily.on('change', reloadEditQualForm);    
    
    var qualPath = Y.one('#qualPathway');
    if (qualPath != null){
        qualPath.on('change', reloadEditQualForm);
    }
    
    
    var qualPathType = Y.one('#qualPathwayType');
    if (qualPathType != null){
        qualPathType.on('change', reloadEditQualForm);
    }
    
    var q = Y.one('#qualPathwaySubType');
    if (q != null){
        q.on('change', reloadEditQualForm);
    }
    
//    var name = Y.one('#qualName');
//    $('#qualName').unbind('keypress');
//    name.on('keypress', function(e){
//        check_edit_qual_valid();
//    })
//    check_edit_qual_valid();
};

function check_edit_qual_valid()
{
    //get the level and subtype and name
    var typeIndex = Y.one('#qualFamilySelect').get('selectedIndex');
    var type = Y.one("#qualFamilySelect").get("options").item(typeIndex).getAttribute('value');
    var name = Y.one('#qualName').get('value');
    if(name != '' && name != ' ' && type != -1)
    {
        Y.one('#save').set('disabled', '');
    }
}

M.block_bcgt.initselqual = function(Y) {
    var qualFamily = Y.one('#family');
    qualFamily.on('change', function(e) {
        Y.one('#bcgtQualSelect').submit();
    });
    
    var level = Y.one('#level');
    level.on('change', function(e) {
        Y.one('#bcgtQualSelect').submit();
    });
    
    var subtype = Y.one('#subtype');
    subtype.on('change', function(e) {
        Y.one('#bcgtQualSelect').submit();
    });
}

M.block_bcgt.initselunit = function(Y) {
    
}

M.block_bcgt.initeditunit = function(Y) {
//    Y.one('#save').set('disabled', 'disabled');
    var qualFamily = Y.one('#unitTypeFamily');
    qualFamily.on('change', function(e) {
        Y.one('#editUnitForm').submit();
    });
//    var unique = Y.one('#unique');
//    $('#unique').unbind('keypress');
//    if(unique)
//    {
//        unique.on('keypress', function(e){
//                check_edit_unit_valid();
//        })
//    }
//    
//    var name = Y.one('#name');
//    $('#name').unbind('keypress');
//    name.on('keypress', function(e){
//        check_edit_unit_valid();
//    })
//    check_edit_unit_valid();
}

function check_edit_unit_valid()
{
    //get the level and subtype and name
    var typeIndex = Y.one('#unitTypeFamily').get('selectedIndex');
    var type = Y.one("#unitTypeFamily").get("options").item(typeIndex).getAttribute('value');
    var uniqueInput = Y.one('#unique');
    if(uniqueInput)
    {
        var unique = Y.one('#unique').get('value');
    }
    var name = Y.one('#name').get('value');
    if(name != '' && ((uniqueInput && unique != '') || (!uniqueInput)) && type != -1)
    {
        Y.one('#save').set('disabled', '');
    }
}

M.block_bcgt.initqualunits = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addUnit').set('disabled', false);
        Y.one('#removeUnit').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
        var multipleSelected = checkMultipleSelects(Y.one('#addselect'));
        if(multipleSelected == 1)
        {
            //if there is only one selected then enable the edit button
            Y.one('#editUnit').set('disabled', false);
        }
        else
        {
            //else disable the edit button
            Y.one('#editUnit').set('disabled', true);
        }
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addUnit').set('disabled', true);
        Y.one('#removeUnit').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
        var multipleSelected = checkMultipleSelects(Y.one('#removeselect'));
        if(multipleSelected == 1)
        {
            //if there is only one selected then enable the edit button
            Y.one('#editUnit').set('disabled', false);
        }
        else
        {
            //else disable the edit button
            Y.one('#editUnit').set('disabled', true);
        }
    }); 
}

M.block_bcgt.initprojectquals = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', false);
        Y.one('#removeQual').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', true);
        Y.one('#removeQual').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
    
    // Destroy datepickers and recreate them
    $('.bcgt_datepicker').datepicker( {dateFormat: 'dd-mm-yy', changeMonth: true, changeYear: true} );
    
//    var addQual = Y.one('#addQual');
//    addQual.on('click', function(e) {
//        e.preventDefault();
//        //we need to get the options and append these to the
//        //list on the left
//        
//        
//    //then add each value to an array
//    });
//    
//    var removeQual = Y.one('#removeQual');
//    removeQual.on('click', function(e) {
//        e.preventDefault();
//    });
    
}

M.block_bcgt.initqualteachers = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addTeacher').set('disabled', false);
        Y.one('#removeTeacher').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addTeacher').set('disabled', true);
        Y.one('#removeTeacher').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
    
    var qualSelect = Y.one('#qualSelect');
    qualSelect.on('click', function(e) {
        Y.one('#editQualTeacher').submit();
    });
}

M.block_bcgt.initteacherquals = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', false);
        Y.one('#removeQual').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', true);
        Y.one('#removeQual').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
    
    var qualSelect = Y.one('#userID');
    qualSelect.on('click', function(e) {
        Y.one('#editUserQual').submit();
    });
}

M.block_bcgt.initqualstud = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addStu').set('disabled', false);
        Y.one('#removeStu').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addStu').set('disabled', true);
        Y.one('#removeStu').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
}

M.block_bcgt.initunitquals = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', false);
        Y.one('#removeQual').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', true);
        Y.one('#removeQual').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
    
    var unitSelect = Y.one('#unitID');
    unitSelect.on('click', function(e) {
        Y.one('#editUnitQualForm').submit();
    });
}

M.block_bcgt.initusersusers = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addUser').set('disabled', false);
        Y.one('#removeUser').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addUser').set('disabled', true);
        Y.one('#removeUser').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
    
    var unitSelect = Y.one('#userID');
    unitSelect.on('click', function(e) {
        Y.one('#editUserUsers').submit();
    });
}


M.block_bcgt.initselcourse = function(Y) {
    
}

M.block_bcgt.initcoursequals = function(Y) {
    var addSelect = Y.one('#addselect');
    addSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', false);
        Y.one('#removeQual').set('disabled', true);
        Y.one('#removeselect').get('options').set('selected',false);
    }); 
    
    var removeSelect = Y.one('#removeselect');
    removeSelect.on('click', function(e) {
        Y.one('#addQual').set('disabled', true);
        Y.one('#removeQual').set('disabled', false);
        Y.one('#addselect').get('options').set('selected',false);
    }); 
    
}

M.block_bcgt.initcoursequalsusers = function(Y) {
    $(document).ready( function () {
        var tables = $('.courseQualUserTable');
        var count = tables.length;
        var tablesArray = [];
        for(var i=1;i<=count;i++)
        {
            tablesArray[i] = $('#courseQualUserTable'+i).dataTable( {
                "sScrollX": "100%",
                "sScrollY": "800px",
                "bScrollCollapse": true,
                "bPaginate": false,
                "bSort": false,
                "bInfo":false
            });
                
            new FixedColumns( tablesArray[i], {
                "iLeftColumns": 4,
                "iLeftWidth": 260 
           }); 
        }
        var staffTable = $('#courseQualUserTableStaff');
        if(staffTable.length)
        {
            var staffDataTable = $('#courseQualUserTableStaff').dataTable( {
                "sScrollX": "100%",
                "sScrollY": "800px",
                "bScrollCollapse": true,
                "bPaginate": false,
                "bSort": false,
                "bInfo":false
            });
            
            if(staffDataTable)
            {
                new FixedColumns( staffDataTable, {
                    "iLeftColumns": 4,
                    "iLeftWidth": 260 
               });
            }
            
        }
         
        
    });
    
    
    //When the all button is clicked for the staff
    var qualStaffAll = Y.all('.qualColumnStaffAll');
    qualStaffAll.each(function(qual){
        qual.on('click', function(e){
            e.preventDefault();
            //id is in the form of qID
            var id = qual.get('id');
            var toggle = true;
            var className = qual.getAttribute('class');
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
            qual.setAttribute('class', className);
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    });
    
    //when the all buton is clicked for the students
    var qualAll = Y.all('.qualColumnAll');
    qualAll.each(function(qual){
        qual.on('click', function(e){
            e.preventDefault();
            //id is in the form of qID
            var id = qual.get('id');
            var toggle = true;
            var className = qual.getAttribute('class');
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
            qual.setAttribute('class', className);
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    });
    
    //when the summaryAll is clicked for the unlinked students
    var unlinkedQual = Y.all('.qualUnlinkedColumnCourse');
    unlinkedQual.each(function(qual){
        qual.on('click', function(e){
            e.preventDefault();
            //id is in the form of qID
            var id = qual.get('id');
            var toggle = true;
            var className = qual.getAttribute('class');
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
            qual.setAttribute('class', className);
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    });
    
    //when the summary button is clicked for staf (e.g by specific course)
    var staffQual = Y.all('.qualColumnCourseStaff');
    staffQual.each(function(qual){
        qual.on('click', function(e){
            e.preventDefault();
            //id is in the form of qID
            var id = qual.get('id');
            var toggle = true;
            var className = qual.getAttribute('class');
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
            qual.setAttribute('class', className);
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    });
    
    var qualCourse = Y.all('.qualColumnCourse');
    qualCourse.each(function(qual){
        qual.on('click', function(e){
            e.preventDefault();
            //id is in the form of qID
            var id = qual.get('id');
            var toggle = true;
            var className = qual.getAttribute('class');
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
            qual.setAttribute('class', className);
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    });
    
    var studentRow = Y.all('.studentRow');
    studentRow.each(function(student){
        student.on('click', function(e){
            e.preventDefault();
            //id is in the form of qID
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
            //get the id of it so we can get the unitid
            //then find all of the checkboxes that have a class that contains
            //uUnitID and set them to checked. 
        });
    });
}

M.block_bcgt.initgridselect = function(Y) {
    
    //if the gridselect div isnt empty
    //then lets navigate to it
    $( document ).ready(function() {
        var div = Y.one('#gridresults');
        if(div && div.get('innerHTML') != '')
        {
            $('html, body').animate({ scrollTop: $('#gridresults').offset().top }, 'slow');  
        }
    });
    
    var qual = Y.one('#qual');
    qual.on('change', function(e) {
        var index = Y.one("#qual").get('selectedIndex');
        var qualID = Y.one("#qual").get("options").item(index).getAttribute('value');
        var grid = Y.one('#grid').get('value');
        var group = Y.one('#group');
        if(group)
        {
            //reset the qual selected index
            group.set('selectedIndex',-1);
        }
        var course = Y.one('#course');
        if(course)
        {
            course.set('selectedIndex', -1);
        }
        if(qualID != -1 && (grid == 'c' || grid == 'a'))
        {
            e.preventDefault();
            var cID = Y.one("#cID").get('value');
            
            var index2 = Y.one("#course").get('selectedIndex');
            var courseID = Y.one("#course").get("options").item(index2).getAttribute('value');
            //then location will be the subject grid with the qualid passed in
            if(grid == 'c')
            {
//                location = '../grids/class_grid.php?qID='+qualID+'&cID='+cID+'&g=c';   
            }
            else
            {
//                location = '../grids/ass_grid_class.php?qID='+qualID+'&cID='+cID+'&g=a';
            }
        }
        else if(grid == 'u')
        {
//            Y.one('#gridselect').submit();
            //then we want to update the 'myUnits'
            var data = {
                method: 'POST',
                data: {
                    'qID' : qualID,
                    'sel' : 'mqual',
                    'g' : grid
                },
                dataType: 'json',
                on: {
                    success: update_grid_select
                }
            }
            var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_selects.php";
            var request = Y.io(url, data);
        }
    });
    
    var group = Y.one('#group');
    if(group)
    {
        group.on('change', function(e){
           //reset the qual selected index
        var search = Y.one('#search');
        if(search)
        {
            search.set('value', '');
        }
           Y.one('#qual').set('selectedIndex',-1);
//           Y.one('#gridselect').submit();

        });
    }
    
    var course = Y.one('#course');
    course.on('change', function(e) {
        var search = Y.one('#search');
        if(search)
        {
            search.set('value', '');
        }
        Y.one('#qual').set('selectedIndex',-1);
        var index = Y.one("#course").get('selectedIndex');
        var courseID = Y.one("#course").get("options").item(index).getAttribute('value');
          //then we want to update the 'mygroups'
          var data = {
              method: 'POST',
              data: {
                  'cID' : courseID,
                  'sel' : 'mcourse',
                  'g' : ''
              },
              dataType: 'json',
              on: {
                  success: update_grid_select
              }
          }
          var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_selects.php";
          var request = Y.io(url, data);
//        Y.one('#gridselect').submit();
    });
    
    var acourse = Y.one('#acourse');
    if(acourse)
    {
        acourse.on('change', function(e) {
        
        Y.one('#aqual').set('selectedIndex',-1);
        Y.one('#qual').set('selectedIndex',-1);
        Y.one('#course').set('selectedIndex',-1);
//        Y.one('#gridselect').submit();
//then we want to update the 'mygroups'
            var index = Y.one("#acourse").get('selectedIndex');
            var courseID = Y.one("#acourse").get("options").item(index).getAttribute('value');
            var data = {
              method: 'POST',
              data: {
                  'cID' : courseID,
                  'sel' : 'acourse',
                  'g' : ''
              },
              dataType: 'json',
              on: {
                  success: update_grid_select
              }
          }
          var url = M.cfg.wwwroot+"/blocks/bcgt/ajax/get_selects.php";
          var request = Y.io(url, data);
        });
    }
    
    var aqual = Y.one('#aqual');
    if(aqual)
    {
        aqual.on('change', function(e) {
                var agroup = Y.one('#agroup');
                if(agroup)
                {
                    //reset the qual selected index
                    agroup.set('selectedIndex',-1);
                }
                var acourse = Y.one('#acourse');
                if(acourse)
                {
                    acourse.set('selectedIndex', -1);
                }
                Y.one('#qual').set('selectedIndex',-1);
                var index = Y.one("#aqual").get('selectedIndex');
                var qualID = Y.one("#aqual").get("options").item(index).getAttribute('value');
                var grid = Y.one('#grid').get('value');
                if(qualID != -1 && (grid == 'c' || grid == 'a'))
                {
                    e.preventDefault();
                    var cID = Y.one("#cID").get('value');
                    //then location will be the subject grid with the qualid passed in
                    if(grid == 'c')
                    {
//                         location = '../grids/class_grid.php?qID='+qualID+'&cID='+cID+'&g=c';   
                    }
                    else
                    {
//                        location = '../grids/ass_grid_class.php?qID='+qualID+'&cID='+cID+'&g=a';
                    }
                }
                else
                {
//                    Y.one('#gridselect').submit();
                }
            });
    }
    
    var agroup = Y.one('#agroup');
    if(agroup)
    {
        agroup.on('change', function(e){
           //reset the qual selected index
           Y.one('#aqual').set('selectedIndex',-1);
//           Y.one('#gridselect').submit();
        });
    }
    
    
    var student = Y.one('#studentID');
    if(student)
    {
        student.on('change', function(e) {
//        Y.one('#gridselect').submit();
        });
    }
    
    var unit = Y.one('#unitID');
    if(unit)
    {
        unit.on('change', function(e) {
//        Y.one('#gridselect').submit();
        });
    }
    
    var assessment = Y.one('#assID');
    if(assessment)
    {
        assessment.on('change', function(e) {
//        Y.one('#gridselect').submit();
        });
    }
    
    var projectQuals = Y.all('.projectQualSelect');
    if(projectQuals)
    {
        var cID = Y.one("#cID").get('value');
        projectQuals.each(function(project){
            project.on('change', function (e){
                //need to get the projectID
                //and the qualid
                //the qual id is the value selected
                //the projectID is the id of the select
                var index = project.get('selectedIndex');
                var qualID = project.get("options").item(index).getAttribute('value');
                
                var projectID = project.get("id");
                location = '../grids/ass.php?qID='+qualID+'&cID='+cID+'&g=a&pID='+projectID;
                
            });
        });
    }
        
    
    
}

function update_grid_select(id, o)
{
    var data = o.responseText; // Response data.
    var json = Y.JSON.parse(o.responseText);
    var select = json.select;
    if(select == 'mqual')
    {
        var option = build_selects('-1', 'Please select one ...');
        //then we need to change the selects in the
        $("#unitID").empty().append(option);
        var units = json.units;
        var length = (json.units).length;
        for(var i=0;i<=length;i++)
        {
            if(typeof units[i] != 'undefined' && typeof units[i]["id"] != 'undefined' 
                && typeof units[i]["uniqueid"] != 'undefined' && typeof units[i]["name"] != 'undefined')
            {
                  var option = build_selects(units[i]["id"], units[i]["uniqueid"]+' : '+units[i]["name"]);
                $("#unitID").append(option);  
            }
        }
    }
    else if(select == 'mcourse')
    {
       var option = build_selects('-1', 'Please select one ...');
        //then we need to change the selects in the
        $("#group").empty().append(option);
        var groups = json.groups;
        var length = (json.groups).length;
        for(var i=0;i<=length;i++)
        {
            if(typeof groups[i] != 'undefined' && typeof groups[i]["id"] != 'undefined' 
                && typeof groups[i]["name"] != 'undefined')
            {
                  var option = build_selects(groups[i]["id"], groups[i]["name"]);
                $("#group").append(option);  
            }
        } 
    }
    else if(select == 'acourse')
    {
       var option = build_selects('-1', 'Please select one ...');
        //then we need to change the selects in the
        $("#agroup").empty().append(option);
        var groups = json.groups;
        var length = (json.groups).length;
        for(var i=0;i<=length;i++)
        {
            if(typeof groups[i] != 'undefined' && typeof groups[i]["id"] != 'undefined' 
                && typeof groups[i]["name"] != 'undefined' && typeof groups[i]["shortname"] != 'undefined')
            {
                  var option = build_selects(groups[i]["id"], groups[i]["name"]+ ' - ' + groups[i]["shortname"]);
                $("#agroup").append(option);  
            }
        } 
    }
}

function build_selects(id, option)
{
    return $('<option></option>').attr("value", id).text(option);
}

M.block_bcgt.initgridstu = function(Y) {
    var student = Y.one('#studentChange');
    if(student)
    {
        student.on('change', function(e) {
            Y.one('#studentGridForm').submit();
        });
    }

    var qual = Y.one('#qualChange');
    if(qual)
    {
        qual.on('change', function(e) {
            var index = Y.one("#qualChange").get('selectedIndex');
            var qualID = Y.one("#qualChange").get("options").item(index).getAttribute('value');
            location = '../forms/grid_select.php?qID='+qualID;
        });
    }
    
    var tabs = $('.ordertab');
    if(tabs)
    {
           $('.ordertab').on('click',function(e){
              e.preventDefault();
              //submit the form
              //get the value
              var order = $(this).attr('order');
              $('#order').attr('value', order);
              $('#studentGridForm').submit();
           }); 
    }
    
}

M.block_bcgt.initimport = function(Y) {
    var qual = Y.one('#famquals');
    if(qual)
    {
        qual.on('change', function(e) {
        Y.one('#importform').submit();
        });
    }
}


M.block_bcgt.initgridunit = function(Y) {
    var student = Y.one('#unitChange');
    if (student != null){
        student.on('change', function(e) {
            Y.one('#unitGridForm').submit();
        });
    }
    
    var qual = Y.one('#qualChange');
    if (qual != null){
        qual.on('change', function(e) {
            Y.one('#unitGridForm').submit();
        });
    }
}

M.block_bcgt.initgridact = function(Y) {
    
    var qual = Y.one('#activityChange');
    if (qual != null){
        qual.on('change', function(e) {
            Y.one('#actGridForm').submit();
        });
    }
}

M.block_bcgt.initgridgroupunit = function(Y) {
    var unit = Y.one('#unitChange');
    if (unit != null){
        unit.on('change', function(e) {
            Y.one('#unitGroupGridForm').submit();
        });
    }
    
    var qual = Y.one('#qualChange');
    if (qual != null){
        qual.on('change', function(e) {
            Y.one('#unitGroupGridForm').submit();
        });
    }
}

M.block_bcgt.initmygrid = function(Y) {

}

M.block_bcgt.initgridclass = function(Y) {
    var qual = Y.one('#qualChange');
    var g = $('#grid').val();
    if (qual != null){
        qual.on('change', function(e) {
            Y.one('#classGridForm').submit();
        });
    }
}

M.block_bcgt.initactivities = function(Y) {

}

M.block_bcgt.addactivities = function(Y) {

}

M.block_bcgt.inittargetqualsettings = function(Y){
      $(".contentCollapse").hide();
	  //toggle the componenet with class msg_body
	  $(".headingCollapse").click(function()
	  {
        $(this).next(".contentCollapse").slideToggle(500);
	  });
      
      $(".delete").click(function(){
          $(this).preventDefault();
      })
}

M.block_bcgt.inittransferunits = function(Y){
    
    var hover = $('.CRITHOVER');
    $(hover).each( function(){
        
        $(this).hover( function(){
            
            var cc = $(this).attr('cc');
            var hcc = 'TO_'+cc;
            
            $(this).css('background-color', 'lime');
            $('.'+hcc).css('background-color', 'red');
            
        });
        
    } );
    
    
    var hover = $('.CRITHOVER');
    $(hover).each( function(){
        
        $(this).on('mouseout', function(){
            
            var cc = $(this).attr('cc');
            var hcc = 'TO_'+cc;
            
            $(this).css('background-color', '#fff');
            $('.'+hcc).css('background-color', '#fff');
            
        });
        
    } );
    
    
    
    
    
    
    var hover = $('.CRITHOVERTO');
    $(hover).each( function(){
        
        $(this).hover( function(){
            
            var cc = $(this).attr('cc');
            var hcc = 'FROM_'+cc;
            
            $(this).css('background-color', 'red');
            $('.'+hcc).css('background-color', 'lime');
            
        });
        
    } );
    
    var hover = $('.CRITHOVERTO');
    $(hover).each( function(){
        
        $(this).on('mouseout', function(){
            
            var cc = $(this).attr('cc');
            var hcc = 'FROM_'+cc;
            
            $(this).css('background-color', '#fff');
            $('.'+hcc).css('background-color', '#fff');
            
        });
        
    } );
    
    
//    var hover = Y.all('.TRANSFERHOVER');
//    if (hover != null){
//        
//        hover.each( function(e){
//            
//            e.on('mouseover', function(j){
//
//                // FInd out what unit it is
//                var unitID = e.getAttribute('unitid');
//                var studentID = e.getAttribute('studentid');
//                if (unitID != null && studentID != null)
//                {
//                    if (Y.one('.UNIT'+unitID+'STUD'+studentID).hasClass('marked') == false){
//                        Y.one('.UNIT'+unitID+'STUD'+studentID).setStyle('backgroundColor', 'orange');
//                    }
//                }
//
//            });
//            
//            e.on('mouseout', function(j){
//
//                // FInd out what unit it is
//                var unitID = e.getAttribute('unitid');
//                var studentID = e.getAttribute('studentid');
//                if (unitID != null && studentID != null)
//                {
//                    if (Y.one('.UNIT'+unitID+'STUD'+studentID).hasClass('marked') == false){
//                        Y.one('.UNIT'+unitID+'STUD'+studentID).setStyle('backgroundColor', 'inherit');
//                    }
//                }
//
//            });
//            
//            
//        } );
//        
//        
//    }
//    
//    var checkbox = Y.all('.transferUnit');
//    checkbox.each( function(e){
//        
//        e.on('click', function(j){
//        
//            var checked = e.get('checked');
//        
//            // FInd out what unit it is
//            var unitID = e.getAttribute('unitid');
//            if (unitID != null)
//            {
//                if (checked)
//                {
//                    Y.all('.FROMUNIT'+unitID).setStyle('backgroundColor', 'lime');
//                    Y.all('.UNIT'+unitID).setStyle('backgroundColor', 'red');
//                    Y.all('.UNIT'+unitID).addClass('marked');
//                }
//                else
//                {
//                    Y.all('.FROMUNIT'+unitID).setStyle('backgroundColor', 'inherit');
//                    Y.all('.UNIT'+unitID).setStyle('backgroundColor', 'inherit');
//                    Y.all('.UNIT'+unitID).removeClass('marked');
//                }
//            }
//        
//        });
//        
//        
//        
//    } );
    
    
}

M.block_bcgt.initassessmenttracker = function(Y, studentID, courseID, qualID){
                    
    $(document).ready( function(){
                
        // Filter the results and draw the calendar again
        $('#filter_calendar').unbind('click');
        $('#filter_calendar').bind('click', function(){
                        
            // Filters
            var viewType = $('input[name="viewtype"]:checked').val();
            var year = $('#yearfield').val();
            // Only get the courseID from the form if it's there, otherwise use the one in the url
            if ($('#coursefield').length > 0){
                courseID = $('#coursefield').val();
            }
            var modLinks = $('input[name="critact"]:checked').val();
            var modTypes = $('#modulesfield').val();
            
            $('#assessment_tracker_content').html( '<img src="'+M.cfg.wwwroot+'/blocks/bcgt/pix/ajax-loader.gif" />' );
            
            var params = {
                studentID: studentID,
                courseID: courseID,
                qualID: qualID,
                year: year,
                modLinks: modLinks,
                modTypes: modTypes,
                viewType: viewType
            };
            
             $.post(M.cfg.wwwroot + '/blocks/bcgt/ajax/load_assessment_tracker.php', params, function(data){
                
                $('#assessment_tracker_content').html(data);
                draw_assessment_tracker(studentID);
                
            });
            
            
        });
        
        
        
        
        
        
        
        
        // Redraw on window resize
        var doResize;
        $(window).resize( function(){
            clearTimeout(doResize);
            $('#loading2').show();
            doResize = setTimeout( function(){
                draw_assessment_tracker(studentID);
            }, 100 );
        } );
        
        
        // draw the calendar
        draw_assessment_tracker(studentID);
        
        
    } );
    
}

function bind_mod_items(studentID){
    
    // Click on a mod_item
    $('.mod_item').unbind('click');
    $('.mod_item').bind('click', function(){

        var mod = $(this).attr('moduleType');
        var id = $(this).attr('moduleID');
        var partID = $(this).attr('partID');

        $('#assessment_tracker_info').hide();
        $('#loading').show();

        var showVals = $('input[name="showvalues"]:checked').val();

        var params = { id: id, mod: mod, studentID: studentID, showVals: showVals };
        if (partID !== undefined)
        {
            params.partID = partID;
        }

        $.post(M.cfg.wwwroot + '/blocks/bcgt/ajax/load_mod_info.php', params, function(data){

            var json = Y.JSON.parse(data);

            $('#assessment_tracker_info_title').text( json.title );
            $('#assessment_tracker_info_content').html( json.content );
            $('#loading').hide();
            $('#assessment_tracker_info').show();

        });

    });
    
    $('.mod_item').addClass('hand');
    
    
    
    // Click on a mod_item
    $('.mod_head').unbind('click');
    $('.mod_head').bind('click', function(){

        var mod = $(this).attr('moduleType');
        var id = $(this).attr('moduleID');

        $('#assessment_tracker_info').hide();
        $('#loading').show();

        var params = { id: id, mod: mod, studentID: studentID };

        $.post(M.cfg.wwwroot + '/blocks/bcgt/ajax/load_mod_info.php', params, function(data){

            var json = Y.JSON.parse(data);

            $('#assessment_tracker_info_title').text( json.title );
            $('#assessment_tracker_info_content').html( json.content );
            $('#loading').hide();
            $('#assessment_tracker_info').show();

        });

    });
    
  
}

function draw_assessment_tracker(studentID){
        
    if( $('#tinytbl-1').length > 0 ){
        $('#assessment_tracker_table').tinytbl('destroy');
    }
    
    $('#assessment_tracker_info').hide();
        
    var height = '800px';
    if ($('#elbp_assessment_calendar_content').length > 0){
        height = '600px';
    }
    
    // Apply tinytbl
    $('#assessment_tracker_table').tinytbl({
        'body': {
            'useclass': null,
            'autofocus': false
        },
        'head': {
            'useclass': null
        },
        'cols': {
            'frozen': 1
        },
        'rows': {
            'frozen': 1
        },
        'rtl':0,
        'width': 'auto',
        'height': ''+height+''
    });

    // Apply width fix
    $('#tinytbl-1').width( $('#tinytbl-1').width() + 2 );
    
    // Apply pointer css to mod items
    bind_mod_items(studentID);

    // Hide the loading gif
    $('#loading2').hide();
    
}

M.block_bcgt.initcorereports = function(Y, url1, url2, frozenColumns, frozenColumnsWidth, applyDataTables){
    $('document').ready(function () {
        $('#exportsub').unbind('click');
        $('#exportsub').bind('click', function(){
            $('#corereportrun').attr('action', url1);
            $('#corereportrun').attr('target', '_blank');
        });
        
        $('#runsub').unbind('click');
        $('#runsub').bind('click', function(){
            $('#corereportrun').attr('action', url2);
            $('#corereportrun').attr('target', '');
        });
        
//        $('#optionsContent').css('display','none');
//        $('#optionsHeader').unbind('click');
//        $('#optionsHeader').bind('click', function(){
//            $( "#optionsContent" ).slideToggle( "slow", function() {
//            // Animation complete.
//          });
//        });  
        
        if($('#results') && applyDataTables)
        {
            var oTable;            
            oTable = $('#resultsTable').dataTable( {
                "sScrollX": "100%",
                "sScrollY": "700px",
                "bScrollCollapse": true,
                "bPaginate": false,
                "bSort":false,
                "bInfo":false,
                "bFilter":false,
                "bAutoWidth": false
            } );

            var fCol = new FixedColumns( oTable, {
                            "iLeftColumns": frozenColumns,
                            "iLeftWidth": frozenColumnsWidth 
                        } );  
        }
        
    });
}

function saveUnitGroupsForm(frm){
    
    $('.unitGroup select option').each( function(){
        $(this).attr('selected', 'selected');
    } );
    
    $('#'+frm).submit();
    
}

function addUnitGroup(){
    
    var unitGroups = $('.unitGroup').length;
    unitGroups++;
        
    var newGroup = "";
    newGroup += "<div class='unitGroup' id='unitGroup_"+unitGroups+"'>";

        newGroup += "<p><input type='text' name='unitGroupNames["+unitGroups+"]' placeholder='Group name' /></p>";
        newGroup += "<div>";
            newGroup += "<select id='unitGroupUnits_"+unitGroups+"' name='unitGroupUnits["+unitGroups+"][]' multiple='multiple'>";

            newGroup += "</select>";
            newGroup += "<br>";
            newGroup += "<p><input type='button' onclick='addUnitsToGroup("+unitGroups+");return false;' value='Add Selected Units' />&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' onclick='removeUnitsFromGroup("+unitGroups+");return false;' value='Remove Selected Units' /></p>";
            newGroup += "<p><input type='button' onclick='removeUnitGroup("+unitGroups+");return false;' value='Remove Group' /></p>";
        newGroup += "</div>";

    newGroup += "</div>";

    $('#unitGroups').append(newGroup);
    
}

function removeUnitGroup(id){
    
    // Remove all units from this group
    var options = $('#unitGroupUnits_'+id+' option');
    
    $(options).each( function(){
        
        $('#unitlist').append( $(this) );
        
    } );
    
    $('#unitGroup_'+id).remove();
    
}

function addUnitsToGroup(id){
    
    var selected = $('#unitlist option:selected');
    
    $(selected).each( function(){
        
        $('#unitGroupUnits_'+id).append( $(this) );
        
    } );
    
}

function removeUnitsFromGroup(id){
    
    var selected = $('#unitGroupUnits_'+id+' option:selected');
    
    $(selected).each( function(){
        
        $('#unitlist').append( $(this) );
        
    } );
    
    
}

M.block_bcgt.initarchive = function(Y)
{
    var type = $('#archivetype');
    if(type)
    {
        $('#archivetype').bind('change', function(){
            $('#archive_data_form').submit();
        })
    }
}


/*
 * Moodle Gradetracker V1.0 – This code is copyright of Bedford College and is 
 * supplied for evaluation purposes only. The code may not be used for any 
 * purpose without permission from The Learning Technologies Team, 
 * Bedford College:  moodlegrades@bedford.ac.uk
 * 
 * Author mchaney@bedford.ac.uk
 */


// A function that gives hello world feedback:
var helloWorld = function(e) {
    alert('HELLO WORLD');
}

var reloadEditQualForm = function(changeType) {
        
    var courseID = Y.one('#cID').get('value');
    var typeID = Y.one('#tID').get('value');
    var qualID = Y.one('#qID').get('value');
    var index = Y.one("#qualFamilySelect").get('selectedIndex');
    var familyID = Y.one("#qualFamilySelect").get("options").item(index).getAttribute('value');
    
    if (Y.one('#qualPathway') != null){
        var index = Y.one("#qualPathway").get('selectedIndex');
        var pathway = Y.one('#qualPathway').get("options").item(index).getAttribute('value');
    } else {
        var pathway = -1;
    }
    
    if (Y.one('#qualPathwayType') != null){
        var index = Y.one("#qualPathwayType").get('selectedIndex');
        var pathwayType = Y.one('#qualPathwayType').get("options").item(index).getAttribute('value');
    } else {
        var pathwayType = -1;
    }
    
    //qualPathwaySubType
    if (Y.one('#qualPathwaySubType') != null){
        var index = Y.one("#qualPathwaySubType").get('selectedIndex');
        var pathwaySubType = Y.one('#qualPathwaySubType').get("options").item(index).getAttribute('value');
    } else {
        var pathwaySubType = -1;
    }
    
    self.location='edit_qual.php?fID='+familyID+'&tID='+typeID+'&qID='+qualID+'&pathway='+pathway+'&pathwaytype='+pathwayType+'&subtype='+pathwaySubType+'&cID='+courseID;
}

var checkMultipleSelects = function(select){
    var count= 0;
    var options = select.get('options');
    options.each(function(o){
        if(o.get('selected') === true)
            count++;
    });
    return count;
}


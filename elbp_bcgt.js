function loadTracker(id, el){

    // Load a display type
    var params = { type: 'tracker', studentID: ELBP.studentID, courseID: ELBP.courseID, id: id }
    ELBP.ajax("elbp_bcgt", "load_display_type", params, function(d){
        $('#elbp_bcgt_content').html(d);
        ELBP.set_view_link(el);
    }, function(d){
        $('#elbp_bcgt_content').html('<img src="'+www+'blocks/elbp/pix/loader.gif" alt="" />');
    });

}

function loadTrackerPopup(id){
            
    ELBP.load_expanded('elbp_bcgt', function(){
        var el = $('#qual'+id+'_tab');
        loadTracker(id, el);
    });

}
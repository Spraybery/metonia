document.addEventListener('DOMContentLoaded', function() {

    $('.date-pick').datepicker();

    // Auto-wrap regular tables for responsiveness
    // Exclude DataTables which are handled by their own scripts or .datatable-scroll wrapper
    $('table.table').each(function() {
        var $table = $(this);
        // Check if not already wrapped and not a special datatable that handles itself
        if (!$table.parent().hasClass('table-responsive') && 
            !$table.parent().hasClass('datatable-scroll') && 
            !$table.parent().hasClass('datatable-scroll-wrap')) {
            
            $table.wrap('<div class="table-responsive"></div>');
        }
    });

});


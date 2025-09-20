// View button functionality
$(document).on('click', '.viewBtn', function() {
    $('#viewName').val($(this).data('name'));
    $('#viewGender').val($(this).data('gender'));
    $('#viewPhone').val($(this).data('phone'));
    $('#viewEmail').val($(this).data('email'));
    $('#viewBirthday').val($(this).data('birthday'));
    $('#viewAddress').val($(this).data('address'));
    $('#viewCreated').val($(this).data('created'));
    new bootstrap.Modal(document.getElementById('viewGuardModal')).show();
});

// Delete button functionality via AJAX
$(document).on('click', '.deleteBtn', function() {
    if (!confirm('Are you sure you want to delete this guard?')) return;
    var guardId = $(this).data('id');
    $.ajax({
        url: '', // current page handles deletion
        method: 'POST',
        data: { id: guardId },
        success: function(response) {
            if (response.trim() === 'success') {
                $('#row-' + guardId).remove();
                alert('Guard deleted successfully!');
            } else {
                alert('Error deleting guard!');
            }
        }
    });
});

// Search functionality across all columns except Action
$('#searchInput').on('input', function() {
    var filter = $(this).val().toLowerCase(); // get search input
    $('#guardsTableBody tr').each(function() {
        var match = false;
        $(this).find('td').not(':last').each(function() { // ignore last column (Action)
            if ($(this).text().toLowerCase().indexOf(filter) > -1) {
                match = true;
                return false; // stop checking other cells
            }
        });
        $(this).toggle(match); // show row if match, hide if not
    });

    // Show or hide the clear button
    $('#clearSearchBtn').toggle($(this).val() !== '');
});

// Clear search button functionality
$('#clearSearchBtn').click(function() {
    $('#searchInput').val('');          // clear input
    $('#guardsTableBody tr').show();    // show all rows
    $(this).hide();                      // hide clear button
});
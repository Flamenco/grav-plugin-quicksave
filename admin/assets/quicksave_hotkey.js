$(window).on('keydown', function (event) {
    var key = String.fromCharCode(event.which).toLowerCase();
    if ((event.ctrlKey || event.metaKey) && event.shiftKey && key === 'a') {
        event.preventDefault();
        const form = $('#saveajax');
        if (form) {
            form.submit()
        }
    }
});
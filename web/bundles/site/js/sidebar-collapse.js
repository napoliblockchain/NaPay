$("#pushmenu_collapsed").click(function () {
    $.ajax({
        url: yiiOptions.changeCollapsed,
        success: function () {
            console.log('riavvio...');
            location.reload(true);
        }
    });
});

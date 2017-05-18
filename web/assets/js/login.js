$(function () {
    $("#login-form").on("submit", function (e) {
        e.preventDefault();
        $.post("", $(this).serialize(), function (response) {
            console.log(response);
            if (response.success) {
                console.log(response);
                window.location.href = response.redirect;
            }
        });
    });
});
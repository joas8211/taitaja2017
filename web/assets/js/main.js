$(function () {
    $(".menu-icon").click(function () {
        if ($(".menu").height() > 0) {
            $(".menu").removeClass("open");
        } else {
            $(".menu").addClass("open");
        }
    });
});
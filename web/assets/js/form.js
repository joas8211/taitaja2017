$(function () {
    $("#form").on("submit", function (e) {
        e.preventDefault();
        var inputs = {};
        $(".sport-value").each(function () {
            inputs[$(this).data("id")] = $(this).val();
        });
        var query = $(this).serialize()+"&inputs="+encodeURIComponent(JSON.stringify(inputs));
        $.post("", query, function (data) {
            if (data.success) {
                window.location.href = data.redirect;
            }
        });
    });

    $(".sport-input.minutes, .sport-input.seconds").change(function () {
        var minutes = $(this).parent().parent().find(".minutes").val() | 0;
        var seconds = $(this).parent().parent().find(".seconds").val() | 0;
        var total = minutes * 60 + seconds;
        console.log(minutes);
        console.log(minutes * 60);
        console.log(seconds);
        console.log(total)
        $(this).parent().siblings(".sport-value").val(total);
    });

    $(".time-value").each(function () {
        if ($(this).val() > 0) {
            var minutes = Math.floor($(this).val() / 60);
            var seconds = $(this).val() - minutes * 60;
            $(this).parent().parent().find(".minutes").val(minutes);
            $(this).parent().parent().find(".seconds").val(seconds);
        }
    });
});
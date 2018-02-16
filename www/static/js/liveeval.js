function HookLiveEval() {
    var data = $(".hidden-score").parent();

    var process = function(index) {
        data.eq(index).find(".processing").show();
        data.eq(index).find(".lie-score").hide();

        setTimeout(function() {
            data.eq(index).find(".processing").hide();
            data.eq(index).find(".hidden-score").show();
            if (index >= 0)
              process(index - 1);
        }, Math.random() * 900 + 350);
    }

    $("#liveeval").on("click", function() {
        process(data.length - 1);
    });
}

$(document).ready(HookLiveEval);


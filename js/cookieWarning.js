function createCookie(a, b, c) {
    if (c) {
        var d = new Date;
        d.setTime(d.getTime() + c * 24 * 60 * 60 * 1e3);
        var e = "; expires=" + d.toGMTString()
    } else var e = "";
    document.cookie = a + "=" + b + e + "; path=/"
}
function readCookie(a) {
    var b = a + "=";
    var c = document.cookie.split(";");
    for (var d = 0; d < c.length; d++) {
        var e = c[d];
        while (e.charAt(0) == " ") e = e.substring(1, e.length);
        if (e.indexOf(b) == 0) return e.substring(b.length, e.length)
    }
    return null
}
$(document).ready(function () {
    var privacyUrl = "http://www.monipal.com/privacy/";
    var closeText = "[ok]";
    var cookieStatus = readCookie("cookieStatus");
    if (cookieStatus != 1) {
        var cookieBoxStyle = {
            position: "absolute",
            right: "10px",
            bottom: "10px",
            width: "200",
            "-webkit-border-radius": "10px",
            "-moz-border-radius": "10px",
            "border-radius": "10px",
            border: "1px solid #ccc",
            padding: "10px",
            "font-family": "Arial",
            "font-size": "12px",
            "background-color": "#efefef",
            display: "none"
        };
        $(".cookieWarningBox").css(cookieBoxStyle);
        $(".cookieWarningBox").html("This website uses 'cookies' to give you the best, most relevant experience. Using this website means you’re happy with this. You can find out more about the cookies used by clicking this <a href=" + privacyUrl + '>link</a> (or by clicking the cookie link at the bottom of any page). <a style="float:right;" href="#">' + closeText + "</a>");

        $(".cookieWarningBox").fadeIn(300);
        setTimeout(function(){
            $(".cookieWarningBox").fadeOut(300);
            createCookie("cookieStatus", 1, 10);
        }, 30000);

        $(".cookieWarningBox a").click(function () {
            $(".cookieWarningBox").fadeOut(300);
            createCookie("cookieStatus", 1, 10)
        })
    }
})

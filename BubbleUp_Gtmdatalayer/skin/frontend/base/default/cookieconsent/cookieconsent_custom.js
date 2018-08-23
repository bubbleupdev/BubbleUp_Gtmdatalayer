

function loadOptimizeTag(gtm_id){
    (function (a, s, y, n, c, h, i, d, e) {
        s.className += ' ' + y;
        h.start = 1 * new Date;
        h.end = i = function () {
            s.className = s.className.replace(RegExp(' ?' + y), '')
        };
        (a[n] = a[n] || []).hide = h;
        setTimeout(function () {
            i();
            h.end = null
        }, c);
        h.timeout = c;
    })(window, document.documentElement, 'async-hide', 'dataLayer', 4000,
        { gtm_id : true });
};
function loadTagManager(gtm_id){
    (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start':
                new Date().getTime(), event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', gtm_id);
};

function scrolling(el,trigger,activity){
    el.addEventListener("scroll", function(){
        var scrolled = el.scrollTop;
        if(!scrolled){
            scrolled = el.scrollY;
        }
        if(scrolled >= trigger && !activity && trigger > 0 ){
            activity = 1;
            createCookie("cookieconsent_status","allow",365);
            enableTracking();
            var banner = document.getElementsByClassName("cc-window");
            var i;
            for (i = 0; i < banner.length; i++) {
                banner[i].style.display = "none";
            }
        };
    });
};

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 *1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else {
        var expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}


function clearCookie( name, domain, path ) {
    if( getCookie( name ) ) {
        document.cookie = name + "=" +
            ((path) ? ";path="+path:"")+
            ((domain)?";domain="+domain:"") +
            ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
    }
}

!(function a(o, s, c) {
    function p(t, e) {
        if (!s[t]) {
            if (!o[t]) {
                var n = "function" == typeof require && require;
                if (!e && n) return n(t, !0);
                if (u) return u(t, !0);
                var i = new Error("Cannot find module '" + t + "'");
                throw ((i.code = "MODULE_NOT_FOUND"), i);
            }
            var r = (s[t] = { exports: {} });
            o[t][0].call(
                r.exports,
                function (e) {
                    return p(o[t][1][e] || e);
                },
                r,
                r.exports,
                a,
                o,
                s,
                c
            );
        }
        return s[t].exports;
    }
    for (
        var u = "function" == typeof require && require, e = 0;
        e < c.length;
        e++
    )
        p(c[e]);
    return p;
})(
    {
        1: [
            function (e, t, n) {
                "use strict";
                function i(e, t) {
                    for (var n = 0; n < e.length; n++) t(e[n], n);
                }
                function a(e, t, n, i) {
                    var r = t.querySelector(".column-id");
                    if (r && parseInt(r.textContent)) {
                        (n.id = parseInt(r.textContent)),
                            (n.shared_network = !!t.className.match(
                                /\bshared-network-snippet\b/
                            )),
                            (n.network = n.shared_network || p);
                        var a =
                            "action=update_code_snippet&_ajax_nonce=" +
                            c +
                            "&field=" +
                            e +
                            "&snippet=" +
                            JSON.stringify(n),
                            o = new XMLHttpRequest();
                        o.open("POST", ajaxurl, !0),
                            o.setRequestHeader(
                                "Content-Type",
                                "application/x-www-form-urlencoded; charset=UTF-8"
                            ),
                            (o.onload = function () {
                                o.status < 200 ||
                                400 <= o.status ||
                                (console.log(o.responseText),
                                void 0 !== i && i(JSON.parse(o.responseText)));
                            }),
                            o.send(a);
                    }
                }
                function r() {
                    a("priority", this.parentElement.parentElement, {
                        priority: this.value
                    });
                }
                function o(e, t) {
                    var n = parseInt(e.textContent.replace(/\((\d+)\)/, "$1"));
                    t ? n++ : n--, (e.textContent = "(" + n.toString() + ")");
                }
                function s(e) {
                    var i = this.parentElement.parentElement,
                        t = i.className.match(/\b(?:in)?active-snippet\b/);
                    if (t) {
                        e.preventDefault();
                        var r = "inactive-snippet" === t[0];
                        a("active", i, { active: r }, function (e) {
                            var t = i.querySelector(".snippet-activation-switch");
                            if (e.success) {
                                i.className = r
                                    ? i.className.replace(
                                        /\binactive-snippet\b/,
                                        "active-snippet"
                                    )
                                    : i.className.replace(
                                        /\bactive-snippet\b/,
                                        "inactive-snippet"
                                    );
                                var n = document.querySelector(".subsubsub");
                                o(n.querySelector(".active .count"), r),
                                    o(n.querySelector(".inactive .count"), r),
                                    (t.title = r ? u.deactivate : u.activate);
                            } else (i.className += " erroneous-snippet"), (t.title = u.activation_error);
                        });
                    }
                }
                var c, p, u;
                (c = document.getElementById("code_snippets_ajax_nonce").value),
                    (p =
                        "-network" ===
                        pagenow.substring(pagenow.length - "-network".length)),
                    (u = window.code_snippets_manage_i18n),
                    i(document.getElementsByClassName("snippet-priority"), function (
                        e,
                        t
                    ) {
                        e.addEventListener("input", r), (e.disabled = !1);
                    }),
                    i(
                        document.getElementsByClassName("snippet-activation-switch"),
                        function (e, t) {
                            e.addEventListener("click", s);
                        }
                    );
            },
            {}
        ]
    },
    {},
    [1]
);

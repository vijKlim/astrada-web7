import React from 'react'
import { render } from 'react-dom'
import numbro from 'numbro'

require('./plugins.js');

// @see http://symfony.com/doc/3.4/frontend/encore/legacy-apps.html
const $ = require('jquery')
global.$ = global.jQuery = $

require('jquery-match-height/dist/jquery.matchHeight-min');
require('scrollax');
require('scrolltofixed');
require('ion-rangeslider');


import '../../../assets/styles/site/main.scss'

// require('bootstrap-sass')


import '../i18n'
import { setTimezone, getCurrencySymbol } from '../i18n'

import AddressAutosuggest from '../widgets/AddressAutosuggest'

import 'select2';

global.ClipboardJS = require('clipboard')

// polyfill for `startsWith` not implemented in IE11
if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position) {
        position = position || 0
        return this.indexOf(searchString, position) === position
    }
}

// @see https://developer.mozilla.org/fr/docs/Web/API/Element/closest#Polyfill
if (!Element.prototype.matches)
    Element.prototype.matches = Element.prototype.msMatchesSelector ||
        Element.prototype.webkitMatchesSelector

if (!Element.prototype.closest)
    Element.prototype.closest = function(s) {
        var el = this
        if (!document.documentElement.contains(el)) return null
        do {
            if (el.matches(s)) return el
            el = el.parentElement || el.parentNode
        } while (el !== null && el.nodeType == 1)

        return null
    }

Number.prototype.formatMoney = function() {

    return numbro(this).format({
        ...numbro.languageData().formats.fullWithTwoDecimals,
        currencySymbol: getCurrencySymbol(),
    })
}

// Initialize Matomo
window._paq = [];


function initTheme()
{
    $(".loader-wrap").fadeOut(300, function () {
        $("#main").animate({
            opacity: "1"
        }, 600);
    });

    // Header ------------------
    $(".more-filter-option").on("click", function () {
        $(".hidden-listing-filter").slideToggle(500);
        $(this).find("span").toggleClass("mfilopact");
    });
    var headSearch = $(".header-search"),
        ssbut = $(".show-search-button"),
        wlwrp = $(".header-modal"),
        wllink = $(".show-header-modal"),
        mainheader = $(".main-header");
    function showSearch() {
        headSearch.addClass("vis-head-search").removeClass("vis-search");
        ssbut.find("span").text("Close");
        ssbut.find("i").addClass("vis-head-search-close");
        mainheader.addClass("vis-searchdec");
        hideWishlist();
    }
    function hideSearch() {
        headSearch.removeClass("vis-head-search").addClass("vis-search");
        ssbut.find("span").text("Search");
        ssbut.find("i").removeClass("vis-head-search-close");
        mainheader.removeClass("vis-searchdec");
    }
    ssbut.on("click", function () {
        if ($(".header-search").hasClass("vis-search")) showSearch();
        else hideSearch();
    });
    $(".header-search_close").on("click", function () {
        hideSearch();
    });
    function showWishlist() {
        wlwrp.fadeIn(1).addClass("vis-wishlist").removeClass("novis_wishlist")
        hideSearch();
        wllink.addClass("scwllink");
    }
    function hideWishlist() {
        wlwrp.fadeOut(1).removeClass("vis-wishlist").addClass("novis_wishlist");
        wllink.removeClass("scwllink");
    }
    wllink.on("click", function () {
        if (wlwrp.hasClass("novis_wishlist")) showWishlist();
        else hideWishlist();
    });
    $(".close-header-modal").on("click", function () {
        hideWishlist();
    });
    var wlitle = $(".novis_wishlist .widget-posts li").length;
    $(".header-modal-top h4 strong , .cart-btn span.cart-counter").text(wlitle);
    $(".act-hiddenpanel").on("click", function () {
        $(this).toggleClass("active-hidden-opt-btn").find("span").text($(this).find("span").text() === 'Close options' ? 'More options' : 'Close options');
        $(".hidden-listing-filter").slideToggle(400);
    });
    // filter show -----------------
    var shf = $(".shsb_btn"),
        ahimcocn = $(".anim_clw"),
        mapover = $(".map-overlay , .close_sbfilters");
    function showhiddenfilters() {
        shf.removeClass("shsb_btn_act");
        ahimcocn.addClass("hidsb_act");
        mapover.fadeIn(200);
    }
    function hidehiddenfilters() {
        shf.addClass("shsb_btn_act");
        ahimcocn.removeClass("hidsb_act");
        mapover.fadeOut(200);
    }
    shf.on("click", function () {
        if ($(this).hasClass("shsb_btn_act")) showhiddenfilters();
        else hidehiddenfilters();
    });
    mapover.on("click", function () {
        hidehiddenfilters();
    });
    // niceselect -----------------
    $(".url_btn").on("click", function (e) {
        e.preventDefault();
    });
    $('.chosen-select').niceSelect();
// rangeslider -----------------
    $(".range-slider").ionRangeSlider({
        type: "double",
        keyboard: true
    });
    $(".rate-range").ionRangeSlider({
        type: "single",
        hide_min_max: true,
    });
    $(".price-range").ionRangeSlider({
        type: "double",
        onFinish: function (data) {
            console.dir(data);
            $('#price_range_min').val(data.from);
            $('#price_range_max').val(data.to);
        }
    });

    //   scroll to------------------
    $(".custom-scroll-link").on("click", function () {
        var a = 90 + $(".scroll-nav-wrapper").height();
        if (location.pathname.replace(/^\//, "") === this.pathname.replace(/^\//, "") || location.hostname === this.hostname) {
            var b = $(this.hash);
            b = b.length ? b : $("[name=" + this.hash.slice(1) + "]");
            if (b.length) {
                $("html,body").animate({
                    scrollTop: b.offset().top - a
                }, {
                    queue: false,
                    duration: 1200,
                    easing: "easeInOutExpo"
                });
                return false;
            }
        }
    });

    //  listing height -----------------
    $(".dasboard-menu-btn").on("click", function () {
        $(".dasboard-menu-wrap").slideToggle(500);
    });
    $(".list-single-facts .inline-facts-wrap").matchHeight({});
    $(".listing-item").matchHeight({});
    $(".article-masonry").matchHeight({});
    $(".grid-opt li span").on("click", function () {
        $(".listing-item").matchHeight({
            remove: true
        });
        setTimeout(function () {
            $(".listing-item").matchHeight();
        }, 50);
        $(".grid-opt li span").removeClass("act-grid-opt");
        $(this).addClass("act-grid-opt");
        if ($(this).hasClass("two-col-grid")) {
            $(".listing-item").removeClass("has_one_column");
            $(".listing-item").addClass("has_two_column");
        } else if ($(this).hasClass("one-col-grid")) {
            $(".listing-item").addClass("has_one_column");
        } else {
            $(".listing-item").removeClass("has_one_column").removeClass("has_two_column");
        }
    });

    //   tabs------------------
    $(".tabs-menu a").on("click", function (a) {
        a.preventDefault();
        $(this).parent().addClass("current");
        $(this).parent().siblings().removeClass("current");
        var b = $(this).attr("href");
        $(this).parents(".tabs-act").find(".tab-content").not(b).css("display", "none");
        $(b).fadeIn();
    });

    $(".change_bg a").on("click", function () {
        var bgt = $(this).data("bgtab");
        $(".bg_tabs").css("background-image", "url(" + bgt + ")");
    });


    // Styles ------------------
    function csselem() {
        $(".height-emulator").css({
            height: $(".fixed-footer").outerHeight(true)
        });
        $(".slideshow-container .swiper-slide").css({
            height: $(".slideshow-container").outerHeight(true)
        });
        $(".slider-container .slider-item").css({
            height: $(".slider-container").outerHeight(true)
        });
        $(".map-container.column-map").css({
            height: $(window).outerHeight(true) - 80 + "px"
        });
        $(".hidden-search-column-container").css({
            height: $(window).outerHeight(true) - 70 + "px"
        });
    }
    csselem();
    //fix map overlay
    setTimeout(function () {

        window.dispatchEvent(new Event('resize'));
    }, 300)
    // Mob Menu------------------
    $(".nav-button-wrap").on("click", function () {
        $(".main-menu").toggleClass("vismobmenu");
        $(this).toggleClass("vismobmenu_btn");
    });
    function mobMenuInit() {
        var ww = $(window).width();
        if (ww < 1054) {
            $(".menusb").remove();
            $(".main-menu").removeClass("nav-holder");
            $(".main-menu nav").clone().addClass("menusb").appendTo(".main-menu");
            $(".menusb").menu();
            $(".map-container.fw-map.big_map.hid-mob-map").css({
                height: $(window).outerHeight(true) - 110 + "px"
            });
        } else {
            $(".menusb").remove();
            $(".main-menu").addClass("nav-holder");
            $(".map-container.fw-map.big_map.hid-mob-map").css({
                height: 550 + "px"
            });
        }
    }
    mobMenuInit();
    var $window = $(window);
    $window.on("resize", function () {
        csselem();
        mobMenuInit();
        if ($(window).width() > 1064) {
            $(".lws_mobile , .dasboard-menu-wrap").addClass("vishidelem");
            $(".map-container.fw-map.big_map.hid-mob-map").css({
                "right": "0"
            });
            $(".map-container.column-map.hid-mob-map").css({
                "right": "0"
            });
        } else {
            $(".lws_mobile , .dasboard-menu-wrap").removeClass("vishidelem");
            $(".map-container.fw-map.big_map.hid-mob-map").css({
                "right": "-100%"
            });
            $(".map-container.column-map.hid-mob-map").css({
                "right": "-100%"
            });
        }
    });

    $(".scroll-nav-wrapper").scrollToFixed({
        minWidth: 768,
        zIndex: 1112,
        marginTop: 80,
        removeOffsets: true,
        limit: function () {
            var a = $(".limit-box").offset().top - $(".scroll-nav-wrapper").outerHeight(true) - 50;
            return a;
        }
    });

    // scroll animation ------------------
    $(".scroll-init  ul ").singlePageNav({
        filter: ":not(.external)",
        updateHash: false,
        offset: 160,
        threshold: 150,
        speed: 1200,
        currentClass: "act-scrlink"
    });
    $(".rate-item-bg").each(function () {
        $(this).find(".rate-item-line").css({
            width: $(this).attr("data-percent")
        });
    });
    $(window).on("scroll", function (a) {
        if ($(this).scrollTop() > 150) {
            $(".to-top").fadeIn(500);

            $(".clbtg").fadeIn(500);
        } else {
            $(".to-top").fadeOut(500);
            $(".clbtg").fadeOut(500);
        }
    });
    //   scroll to------------------
    $(".custom-scroll-link").on("click", function () {
        var a = 90 + $(".scroll-nav-wrapper").height();
        if (location.pathname.replace(/^\//, "") === this.pathname.replace(/^\//, "") || location.hostname === this.hostname) {
            var b = $(this.hash);
            b = b.length ? b : $("[name=" + this.hash.slice(1) + "]");
            if (b.length) {
                $("html,body").animate({
                    scrollTop: b.offset().top - a
                }, {
                    queue: false,
                    duration: 1200,
                    easing: "easeInOutExpo"
                });
                return false;
            }
        }
    });

    $(".to-top , .to-top_footer").on("click", function (a) {
        a.preventDefault();
        $("html, body").animate({
            scrollTop: 0
        }, 800);
        return false;
    });
}

//   Parallax ------------------
function initparallax() {
    var a = {
        Android: function () {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function () {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function () {
            return a.Android() || a.BlackBerry() || a.iOS() || a.Opera() || a.Windows();
        }
    };
    var trueMobile = a.any();

    if (null === trueMobile) {
        var b = new Scrollax();
        b.reload();
        b.init();
    }
    if (trueMobile) $(".bgvid , .background-vimeo , .background-youtube-wrapper ").remove();
}

window.addEventListener('scroll', () => {
    if(window.scrollY > 100){
        $(".main-header").addClass('sticky');
    }
    if(window.scrollY <= 100){
        $(".main-header").removeClass('sticky');
    }
}, false);

/* Top cart */
document.addEventListener('DOMContentLoaded', function() {

    initTheme();
    initparallax();

    // Set global timezone used in Moment.js
    const timezone = document.querySelector('body').dataset.timezone
    setTimezone(timezone)

    // const cartTopElement = document.querySelector('#cart-top')
    // if (cartTopElement) {
    //   render(<CartTop url={ cartTopElement.dataset.url } href={ cartTopElement.dataset.href } />, cartTopElement)
    // }

    const inputs = document.querySelectorAll('[data-widget="address-input"]')
    if (inputs.length > 0) {

        const addressElements = {
            latitude: '$1ddress_latitude',
            longitude: '$1ddress_longitude',
            postalCode: '$1ddress_postalCode',
            addressLocality: '$1ddress_addressLocality',
        }

        inputs.forEach(el => {

            // Try to build an address object
            let address = {
                streetAddress: el.value
            }
            for (const addressProp in addressElements) {
                const addressEl = document.getElementById(
                    el.getAttribute('id').replace(/([aA])ddress_streetAddress/, addressElements[addressProp])
                )
                if (addressEl) {
                    address = {
                        ...address,
                        [addressProp]: addressEl.value
                    }
                }
            }

            address = {
                ...address,
                geo: {
                    latitude: address.latitude,
                    longitude: address.longitude,
                }
            }

            new AddressAutosuggest(
                el.closest('.form-group'),
                {
                    required: el.required,
                    address,
                    inputProps: {
                        id: el.getAttribute('id'),
                        name: el.getAttribute('name'),
                    },
                    onAddressSelected: (text, address) => {
                        for (const addressProp in addressElements) {
                            const addressEl = document.getElementById(
                                el.getAttribute('id').replace(/([aA])ddress_streetAddress/, addressElements[addressProp])
                            )
                            if (addressEl) {
                                addressEl.value = address[addressProp]
                            }
                        }
                    }
                }
            )
        })
    }

})

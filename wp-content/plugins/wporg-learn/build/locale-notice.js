!function(e,o,i){"use strict";const n=e.WPOrgLearnLocaleNotice||{},c=o.extend(n,{$notice:o(),init:function(){c.$notice=o(".wporg-learn-locale-notice"),c.$notice.on("click",".wporg-learn-locale-notice-dismiss",(function(e){e.preventDefault(),c.dismissNotice()}))},dismissNotice:function(){c.$notice.fadeTo(100,0,(function(){c.$notice.slideUp(100,(function(){c.$notice.remove()}))})),i.set("wporg-learn-locale-notice-dismissed",!0,c.cookie.expires,c.cookie.cpath,c.cookie.domain,c.cookie.secure)}});o(document).ready((function(){c.init()}))}(window,jQuery,wpCookies);
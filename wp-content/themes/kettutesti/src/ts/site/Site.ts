import {l} from "../debug/debug";
import {Layouts} from "../../../layouts/layouts";


import {SkipToContent} from "./SkipToContent";
import {Header} from "../../../templates/header/Header";
import {AnchorLink} from "./modules/AnchorLink";

export class Site {

    private readonly SELECTOR_USER_LOGGED_IN: string = 'logged-in';

    private layouts: Layouts;
    private header: Header;

    private isUserLoggedIn: boolean;


    private readonly SELECTOR_CONTENT: string = "#content";
    private readonly SKIP_TO_CONTENT_LINKTEXT: string = 'Siirry pääsisältöön';
    private readonly SELECTOR_HEADER: 'header.header';

    private skipToContent: SkipToContent;

    constructor() {
        l('site init');

        this.isUserLoggedIn = $('body').hasClass(this.SELECTOR_USER_LOGGED_IN);

        //this.header = new Header();
        this.layouts = new Layouts();


        this.skipToContent = new SkipToContent(this.SELECTOR_CONTENT, this.SKIP_TO_CONTENT_LINKTEXT, this.SELECTOR_HEADER);

        $('a[href*="#"]').not('.no-scroll').each(function (index, element) {

            new AnchorLink($(element));

        });

        const hash = window.location.hash;
        // to top right away
        if (hash) {
            scroll(0, 0);

            // void some browsers issue
            setTimeout(function () {
                scroll(0, 0);
            }, 1);
        }

        if (hash.length > 2 && $(hash) && $(hash).length) {
            const targetTop = $(hash).offset().top;
            $('html, body').delay(1).animate({scrollTop: targetTop});

        }

        //let iOS = ['iPad', 'iPhone', 'iPod'].indexOf(navigator.platform) >= 0;
        if (this.iOS()) {
            $('body').addClass('ios');
        }
    }

    private iOS() {
        return [
                'iPad Simulator',
                'iPhone Simulator',
                'iPod Simulator',
                'iPad',
                'iPhone',
                'iPod'
            ].includes(navigator.platform)
            // iPad on iOS 13 detection
            || (navigator.userAgent.includes("Mac") && "ontouchend" in document);
    }
}
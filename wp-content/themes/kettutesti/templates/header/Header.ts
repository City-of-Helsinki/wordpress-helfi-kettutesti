//import {Login} from "../login/Login";
import {l} from "../../src/ts/debug/debug";
import Easings = JQuery.Easings;
import ResizeEvent = JQuery.ResizeEvent;

export class Header {

    private readonly SELECTOR_NAV: string = '.header__page-menu';
    private readonly SELECTOR_NAV_TOGGLE: string = '.header__togglebutton';
    private readonly SELECTOR_SUB_NAV: string = '.header__page-menu-children-list';
    private readonly SELECTOR_SUB_NAV_TOGGLE: string = '.header__page-menu-children-list-toggle';

    private readonly SELECTOR_NAV_LINK = ".header__page-menu-link";


    private readonly SCROLL_AMOUNT_MIN: number = 50; //when STATE_NAV_HIDDEN is applied
    private readonly SCROLL_AMOUNT_MAX: number = 300; //when STATE_SCROLLED is applied

    private readonly STATE_NAV_HIDDEN: string = 'hidden';
    private readonly STATE_SCROLLED: string = 'header--scrolled';
    private readonly STATE_ARIA_NAV_OPEN: string = 'true';
    private readonly STATE_ARIA_NAV_CLOSED: string = 'false';
    private readonly STATE_SUB_NAV_OPEN: string = 'open';

    private readonly NAV_BUTTON_ACTIVE_CLASS: string = 'is-active';
    private readonly NAV_OPEN_BODY_CLASS: string = 'nav_open';


    private $header: JQuery;
    private $navigation: JQuery;
    private $navButton: JQuery;

    private $navLinks: JQuery;

    private $backgroundElements: JQuery;

    private isNavOpen: boolean = false;
    private lastScrollPosition: number;


    constructor() {
        this.$header = $('header.header');
        this.$navButton = this.$header.find(this.SELECTOR_NAV_TOGGLE);
        this.$navigation = this.$header.find(this.SELECTOR_NAV);

        this.$navButton.on('click', (e) => this.onNavButtonClick(e));

        this.$navigation.find(this.SELECTOR_SUB_NAV_TOGGLE).on('click', (e) => this.onSubnavToggle(e));
        this.$navLinks = this.$header.find(this.SELECTOR_NAV_LINK);
        this.$navLinks.on('click', (e) => this.onNavLinkClick(e));


        $(window).on('resize', () => this.onWindowResize());
        $(window).on("scroll", (e) => this.onWindowScroll(e));
        this.onWindowResize();
    }

    private onNavLinkClick(e: JQuery.ClickEvent) {
        if (this.isNavOpen) {
            this.closeNav();

        }
    }

    private onNavButtonClick(e: JQuery.ClickEvent) {
        if (this.isNavOpen) {
            this.closeNav();
        } else {
            this.openNav();
        }

        return false;
    }

    private closeNav() {
        this.isNavOpen = false;
        this.$navButton.removeClass(this.NAV_BUTTON_ACTIVE_CLASS);
        this.$navButton.attr('aria-pressed', this.STATE_ARIA_NAV_CLOSED);

        this.$navigation.fadeOut(100);

        this.$backgroundElements.each((index, elem) => this.enableOriginalTabindex(elem));
        this.enableOriginalTabindex($('body .skip-to-content')[0]);

        $('body').removeClass(this.NAV_OPEN_BODY_CLASS);
    }

    private openNav() {
        this.isNavOpen = true;


        this.$backgroundElements = $('body > main, body > footer').find('a[href], area[href], input, select, textarea, button, iframe, object, embed, *[tabindex], *[contenteditable]')
            .not('[tabindex=-1], [disabled], :hidden');

        this.$backgroundElements.each((index, elem) => this.disableFocusTemporarily(elem));
        this.disableFocusTemporarily($('body .skip-to-content')[0]);

        this.$navButton.addClass(this.NAV_BUTTON_ACTIVE_CLASS);
        $('body').addClass(this.NAV_OPEN_BODY_CLASS);
        this.$navigation.css("display", "flex").hide().fadeIn('100ms');
        this.$navButton.attr('aria-pressed', this.STATE_ARIA_NAV_OPEN);

        this.$navigation.find(this.SELECTOR_SUB_NAV).stop().css("display", "none");
        this.$navigation.find(this.SELECTOR_SUB_NAV_TOGGLE).removeClass(this.STATE_SUB_NAV_OPEN);
    }

    private onSubnavToggle(e: JQuery.ClickEvent) {
        let $target: JQuery = $(e.currentTarget);

        if ($target.hasClass(this.STATE_SUB_NAV_OPEN)) {
            $target.removeClass(this.STATE_SUB_NAV_OPEN);
            $target.next().stop().slideUp(200);

        } else {
            $target.addClass(this.STATE_SUB_NAV_OPEN);
            $target.next().stop().slideDown(250);
        }

        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
    }

    private onWindowResize() {
        if ($(window).width() >= 1280) {
            this.disableSubnavToggleFocus();
            this.isNavOpen = false;
            this.$navButton.removeClass(this.NAV_BUTTON_ACTIVE_CLASS);
            this.$navButton.attr('aria-pressed', this.STATE_ARIA_NAV_CLOSED);
            this.$navigation.css("display", "");
            this.$navigation.find(this.SELECTOR_SUB_NAV).stop().css('display', '');
            this.$navigation.find(this.SELECTOR_SUB_NAV_TOGGLE).removeClass(this.STATE_SUB_NAV_OPEN);
            $('body').removeClass(this.NAV_OPEN_BODY_CLASS);
        } else {
            this.enableSubnavToggleFocus();
        }
    }

    private enableSubnavToggleFocus() {
        this.$navigation.find(this.SELECTOR_SUB_NAV_TOGGLE).removeAttr('tabindex');
    }

    private disableSubnavToggleFocus() {
        this.$navigation.find(this.SELECTOR_SUB_NAV_TOGGLE).attr('tabindex', '-1');
    }

    private onWindowScroll(e: JQuery.ScrollEvent) {

        if (this.isNavOpen) {
            return;
        }

        const newY = $(window).scrollTop();

        if (newY > this.lastScrollPosition && newY > this.SCROLL_AMOUNT_MIN) {
            this.$header.addClass(this.STATE_NAV_HIDDEN);
        } else {
            this.$header.removeClass(this.STATE_NAV_HIDDEN);
        }

        if (newY > this.SCROLL_AMOUNT_MAX) {
            if (!this.$header.hasClass(this.STATE_SCROLLED)) {
                this.$header.addClass(this.STATE_SCROLLED);
            }
        } else {
            if (this.$header.hasClass(this.STATE_SCROLLED)) {
                this.$header.removeClass(this.STATE_SCROLLED);
            }
        }

        this.lastScrollPosition = newY;
    }

    private disableFocusTemporarily(elem) {
        const $elem: JQuery = $(elem);

        const tabindex: any = $elem.attr('tabindex');

        // For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
        if (typeof tabindex !== typeof undefined && (tabindex !== false)) {
            $elem.data('original-tabindex', tabindex);
            //l('original tabindex stored');
        } else {
            $elem.data('original-tabindex', false);
            //l('elem has no tabindex');
        }

        $elem.attr('tabindex', '-1');
    }

    private enableOriginalTabindex(elem) {
        const $elem: JQuery = $(elem);

        const originalTabindex = $elem.data('original-tabindex');

        if (originalTabindex !== false) {
            $elem.attr('tabindex', originalTabindex);

        } else {
            $elem.removeAttr('tabindex');

        }
    }

}
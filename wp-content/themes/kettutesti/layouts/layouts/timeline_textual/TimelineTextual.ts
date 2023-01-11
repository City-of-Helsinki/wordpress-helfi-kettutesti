import {Layout} from "../../../src/ts/layouts/Layout";
import Swiper from "swiper";

export class TimelineTextual extends Layout {

    private swiper: Swiper;
    private swiperOptions: object = {
        slideClass: 'swiper-slide',
        slideActiveClass: 'active',
        slideDuplicateActiveClass: '--duplicate-active',
        slideVisibleClass: '--visible',
        slideDuplicateClass: '--duplicate',
        slideNextClass: '--next',
        slideDuplicateNextClass: '--duplicate-next',
        slidePrevClass: '--prev',
        slideDuplicatePrevClass: '--duplicate-prev',

        spaceBetween: 0,
        watchOverflow: true,
        simulateTouch: true,
        slidesPerView: 'auto',
        centeredSlides: false,
        freeMode: true,
        freeModeSticky: false,
        grabCursor: true,


        scrollbar: {
            el: '.swiper-scrollbar',
            draggable: true,
        },

    };

    constructor($elem: JQuery) {
        super($elem);
        let swiper = this.$layout.find('.swiper-container').get(0);
        this.swiper = new Swiper(swiper, this.swiperOptions);
        console.log('TimelineTextual');
    }

}


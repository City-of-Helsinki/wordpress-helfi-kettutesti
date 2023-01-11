import {Layout} from "../../../src/ts/layouts/Layout";
import Swiper from "swiper";

export class Timeline extends Layout {

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
        slidesOffsetBefore: 0,
        slidesOffsetAfter: 0,

        scrollbar: {
            el: '.swiper-scrollbar',
            draggable: true,
        },

        /*pagination: {
            el: '.pagination',
            type: 'bullets',
            clickable: true,
            bulletClass: 'bullet',
            bulletActiveClass: 'active',
            modifierClass: '',
            currentClass: 'current'
        },
        loop: false,*/

        /*breakpoints: {

            768: {
                spaceBetween: 24,
                slidesOffsetBefore: 24,
                slidesOffsetAfter: 24,
            },

            1224: {
                spaceBetween: 24,
                slidesOffsetBefore: 0,
                slidesOffsetAfter: 0,
            }
        }*/
    };

    constructor($elem: JQuery) {
        super($elem);
        let swiper = this.$layout.find('.swiper-container').get(0);
        this.swiper = new Swiper(swiper, this.swiperOptions);
    }
}


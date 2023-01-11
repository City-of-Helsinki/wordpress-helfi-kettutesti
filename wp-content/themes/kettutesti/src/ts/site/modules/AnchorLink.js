"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.AnchorLink = void 0;
class AnchorLink {
    constructor($link) {
        this.$link = $link;
        var hash = this.$link.attr('href').substring(this.$link.attr('href').indexOf('#'));
        this.target = hash;
        var $elem = $('body').find(hash);
        if ($elem && $elem.length) {
            console.log(hash + ' found');
            this.$link.on('click', (e) => this.onAnchorClick(e));
        }
    }
    onAnchorClick(e) {
        let $target;
        if (this.$link.data('target') === 'self') {
            $target = this.$link;
        }
        else {
            $target = $(this.target);
        }
        console.log(jQuery.easing);
        if ($target && $target.length > 0) {
            e.preventDefault();
            $('html, body').stop().animate({
                'scrollTop': $target.offset().top
            }, 1000, 'easeOutCubic');
            return false;
        }
    }
}
exports.AnchorLink = AnchorLink;
//# sourceMappingURL=AnchorLink.js.map
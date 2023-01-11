export class AnchorLink {

    private $link: JQuery;
    private target: string;

    constructor($link: JQuery) {
        this.$link = $link;
        var hash = this.$link.attr('href').substring(this.$link.attr('href').indexOf('#'));
        this.target = hash;

        var $elem = $('body').find(hash);

        if ($elem && $elem.length) {
            this.$link.on('click', (e) => this.onAnchorClick(e));
        }
    }

    private onAnchorClick(e: JQuery.ClickEvent) {

        let $target: JQuery;

        if (this.$link.data('target') === 'self') {
            $target = this.$link;
        } else {
            $target = $(this.target);
        }

        if ($target && $target.length > 0) {
            $('html, body').stop().animate({
                'scrollTop': $target.offset().top
            }, 1000, 'easeOutCubic');
        }

    }
}
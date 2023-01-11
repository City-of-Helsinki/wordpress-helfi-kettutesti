import {l} from "../debug/debug";

export class Overlay {

    private readonly SELECTOR_OVERLAY: string = '.overlay';
    private readonly SELECTOR_OVERLAY_CLOSE: string = '.overlay__close';

    protected $overlay: JQuery;
    protected $overlayCloseButton: JQuery;

    constructor(selector_overlay: string = '') {
        l('overlay');
        if (selector_overlay !== '') {
            this.$overlay = $(this.SELECTOR_OVERLAY);
        } else {
            this.$overlay = $(selector_overlay);
        }
        this.$overlayCloseButton = $(this.SELECTOR_OVERLAY_CLOSE);
        this.$overlayCloseButton.on('click', (e) => this.closeOverlay(e));

    }

    protected closeOverlay(e: JQuery.ClickEvent) {
        this.$overlay.hide();
        return false;
    }

    protected showOverlay() {
        this.$overlay.show();
    }

}
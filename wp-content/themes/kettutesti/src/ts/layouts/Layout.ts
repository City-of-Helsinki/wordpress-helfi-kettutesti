export class Layout {

    protected $layout: JQuery;

    constructor($element: JQuery) {
        this.$layout = $element;
        //console.log('Layout constructed : ' + this.constructor.name);
    }

    public name(): string {
        return this.constructor.name;
    }

}
import {Layout} from "../../../src/ts/layouts/Layout";

export class Article extends Layout {

	private layouts:Array<Layout>;

	constructor($elem: JQuery) {
		super($elem);
	}

}


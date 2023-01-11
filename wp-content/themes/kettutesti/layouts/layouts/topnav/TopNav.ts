import {Layout} from "../../../src/ts/layouts/Layout";

export class TopNav extends Layout {

	private layouts:Array<Layout>;
	// private finlink:HTMLElement;
	// private svenlink:HTMLElement;
	// private activelink:String;

	constructor($elem: JQuery) {
		super($elem);
		//front-end solution
		
		// this.finlink= document.querySelector(".suomi");
		// this.svenlink= document.querySelector(".svenska");

		// if(window.location.href.includes("/sv")) {
		// 	this.svenlink.style.display="none";
		// 	this.finlink.style.display="block";
		// } else {
		// 	this.finlink.style.display="none"
		// 	this.svenlink.style.display="block";
		// }


		}

}


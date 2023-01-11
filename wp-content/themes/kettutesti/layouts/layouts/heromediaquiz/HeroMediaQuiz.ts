import {Layout} from "../../../src/ts/layouts/Layout";
export class HeroMediaQuiz extends Layout {
	private layouts:Array<Layout>;
	private title: HTMLElement;
	private myData: any;
	private questionCount: any;
	private countDisplay:HTMLElement;
	private optionCont: HTMLElement;
	private prevButton: HTMLButtonElement;
	private nextButton: HTMLButtonElement;
	private resultButton: HTMLButtonElement;

	constructor($elem: JQuery) {
		super($elem);
		this.myData= window.myData1;
		this.questionCount=0; 
		this.title= document.querySelector("#question");
			
		this.optionCont= document.querySelector("#optionCont");

		let output="";
		
		this.myData.forEach((item,indexData) => {
			let options=item.quiz.options.map((item,index)=> {
				return `<div class="radio-btn-group">
    						<div class="flex items-center">
        						<input type="radio" 
								name="buttonoption${indexData}" 
								id="q_${indexData}_a_${index}" 
								data-info="q_${indexData}_a_${index}" 
								value="" 
								class="mr-[35px]" style="transform: scale(3);accent-color: orangered;"
								aria-required="true"
								>
        						<label for="q_${indexData}_a_${index}" aria-required="true" class="col-span-2 text-base sm:text-lg md:text-lg lg:text-2xl xl:text-3xl my-1 md:my-2 lg:my-2 xl:my-3">${item.optionname}</label>
    						</div>	
						</div>`
			}).join(`<br>`);

			output += `
			<div class="question-list hidden" id="q-${indexData}">
			<fieldset>
			<legend><h1 class="text-red-500 font-bold xm:text-2xl sm:text-2xl md:text-3xl lg:text-5xl xl:text-7xl my-4 md:my-6 lg:my-6 xl:my-8">${item.quiz.question}</h1></legend>
				${options}
			</div>
			</fieldset>
			`				
		});
			
		this.optionCont.innerHTML=output;

		this.nextButton= document.querySelector("#next");
		this.prevButton=document.querySelector("#previous");
		this.resultButton=document.querySelector("#result");
		this.countDisplay=document.querySelector("#qcount");

		let questionToDisplay= document.querySelector(`#q-0`);
		let inputs= questionToDisplay.querySelectorAll("input");
		
		this.inputClick(inputs,this.nextButton);

			questionToDisplay.classList.remove("hidden");
			this.countDisplay.innerHTML=`${window.quiztag} 1/6`
			this.prevButton.style.display="none";
			this.nextButton.style.display="block";
			this.resultButton.style.display="none";

			this.prevButton.addEventListener("click",()=> {

				if (window.scrollY) {
					window.scroll(0, 0);  // reset the scroll position to the top left of the document.
				  }
				this.questionCount--;
				history.replaceState({id:`${this.questionCount}`}, "", `?${window.quiztag}=${this.questionCount+1}`);
				document.title= `Kysymys: ${this.myData[this.questionCount].quiz.question}`;
				this.countDisplay.focus();

				let questionToDisplay=document.querySelector(`#q-${this.questionCount}`);
				let questionTohide= document.querySelector(`#q-${this.questionCount+1}`);

				  this.checkItem(questionToDisplay);

				questionTohide.classList.add("hidden");
				questionToDisplay.classList.remove("hidden");
				if(this.questionCount<=0) {
					this.prevButton.style.display="none";
				}
				this.nextButton.style.display="block";
				this.resultButton.style.display="none";
				this.countDisplay.innerHTML=`${window.quiztag} ${this.questionCount+1}/6`;
			})

			this.nextButton.addEventListener("click", ()=>{
				if (window.scrollY) {
					window.scroll(0, 0);  // reset the scroll position to the top left of the document.
				  }
				this.questionCount++;

				history.replaceState({id:`${this.questionCount+1}`}, "", `?${window.quiztag}=${this.questionCount+1}`);
				document.title= `Kysymys: ${this.myData[this.questionCount].quiz.question}`;
				this.countDisplay.focus();

				let questionToDisplay= document.querySelector(`#q-${this.questionCount}`);
				let questionTohide= document.querySelector(`#q-${this.questionCount-1}`);
				let inputs= questionToDisplay.querySelectorAll("input");

				this.inputClick(inputs,this.nextButton);
				this.checkItem(questionToDisplay);		

				questionToDisplay.classList.remove("hidden");
				questionTohide.classList.add("hidden");
				if(this.questionCount>0) {
					this.prevButton.style.display="block";
				} else {
					this.prevButton.style.display="none";
				}
				if(this.questionCount>=5) {
					this.nextButton.style.display="none";
					this.resultButton.style.display="block";
				} 
				this.countDisplay.innerHTML=`${window.quiztag} ${this.questionCount+1}/6`;
			})	

	

			let finalquestionToDisplay= document.querySelector(`#q-${this.myData.length-1}`);
			let finalinputs= finalquestionToDisplay.querySelectorAll("input");

			this.inputClick(finalinputs,this.resultButton);

			this.resultButton.addEventListener("click",()=> {	

				let radioButtons= document.querySelectorAll('input[type="radio"]:checked');
				let kokki:Array<Number>= [];
				let retki:Array<Number>= [];
				let taitava:Array<Number>= [];
				let keksija:Array<Number>= [];
				let taitelija:Array<Number>= [];
				let tarina:Array<Number>= [];
				let viisas:Array<Number>= [];

				radioButtons.forEach(item=> {
					/* @ts-ignore */
					let questionNumeber=item.dataset.info.split("_")[1];
					/* @ts-ignore */
					let answerNumeber=item.dataset.info.split("_")[3];
					let answer=this.myData[parseInt(questionNumeber)].quiz.options[parseInt(answerNumeber)];
					kokki.push(answer.kokki);
					retki.push(answer.retki);
					taitava.push(answer.taitava);
					keksija.push(answer.keksijä);
					taitelija.push(answer.taitelija);
					tarina.push(answer.tarina);
					viisas.push(answer.viisas);
					
				})
				let resultsArr=[
				{"name":"kokki","score":this.CalculateTotal(kokki)},
				{"name":"retki","score":this.CalculateTotal(retki)},
				{"name":"taitava","score":this.CalculateTotal(taitava)},
				{"name":"keksija","score":this.CalculateTotal(keksija)},
				{"name":"taitelija","score":this.CalculateTotal(taitelija)},
				{"name":"tarina","score":this.CalculateTotal(tarina)},
				{"name":"viisas","score":this.CalculateTotal(viisas)},] 
				
				let topScore= Math.max.apply(Math, resultsArr.map(function(o) { return o.score; }))
				let finalResult= resultsArr.filter(item=> item.score===topScore);
				/* @ts-ignore */
				localStorage.setItem("result",JSON.stringify(finalResult[0]));

				this.questionCount=0;
				radioButtons.forEach(item=>{
					/* @ts-ignore */
					item.checked= false;
				})
				/* @ts-ignore */
				window.location=`${window.resultlink1}?kettu=${finalResult[0].name}`
			})

	}

	private CalculateTotal(ListOfScores) {
		return ListOfScores.reduce((previousValue:Number,currentValue:Number)=>{
			/* @ts-ignore */
			return parseInt(previousValue)+parseInt(currentValue);
		},0)
	}		

	private checkItem(question) {
		let inputs= question.querySelectorAll("input");
		inputs.forEach((item)=> {
			if(item.checked) {
				this.nextButton.disabled=false;
				this.nextButton.classList.remove("disabled");	
			}
		})

	}

	private inputClick(inputs,button) {
		button.disabled=true;
		button.classList.add("disabled");
	
		inputs.forEach((item)=> {
			item.addEventListener("click", ()=> {
				button.disabled=false;
				button.classList.remove("disabled");
			})
		})	
	}


}


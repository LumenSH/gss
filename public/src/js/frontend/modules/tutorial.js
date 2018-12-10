class Tutorial {
    constructor() {
        this.step = 1;
        this.tutorialButton = document.querySelector("[data-tutorial]");
        if(this.tutorialButton) {
            this.tutorialButton.addEventListener("click", $.proxy(this.onPageNext, this));
        }
    }

    onPageNext (event) {
        document.getElementById('slide' + this.step).classList.add("hidden");
        let nextSlide = document.getElementById('slide' + (this.step + 1).toString());
        if (nextSlide) {
            event.preventDefault();
            nextSlide.classList.remove("hidden");
            nextSlide.classList.add("fadeInRight");
            nextSlide.classList.add("animated");
            this.step++;
        }
    }
}

export {Tutorial}
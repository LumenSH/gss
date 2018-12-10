class Support {
    constructor() {
        this.supportType = document.getElementById('support_ticket_typ');
        this.supportTypeDiv = $('#support_ticket_gameserver').parent();

        if (this.supportTypeDiv) {
            this.supportTypeDiv.addClass('hidden');
        }

        if (this.supportType) {
            this.supportType.addEventListener("change", $.proxy(this.onChangeSupportTyp, this));
            this.onChangeSupportTyp();
        }
    }

    onChangeSupportTyp () {
        if (parseInt(this.supportType.value) === 1) {
            this.supportTypeDiv.removeClass('hidden');
            this.supportTypeDiv.addClass('fadeIn').addClass('animated');
        } else {
            this.supportTypeDiv.addClass('hidden');
            this.supportTypeDiv.removeClass('fadeIn').addClass('animated');
        }
    }
}

export {Support}
class Forum {
    constructor() {
        this.answerTrigger = $('#answertrigger');
        this.answerBox = $('#answerbox');

        if (this.answerTrigger) {
            this.answerTrigger.on('click', $.proxy(this.openAnswerBox, this));
        }


        this.initForumToggles();
    }

    openAnswerBox(event) {
        event.preventDefault();
        this.answerTrigger.addClass('hidden');
        this.answerBox.addClass('fadeIn').addClass('animated').removeClass('hidden');
        window.scroll(0, window.scrollY + 400)
    }

    initForumToggles() {
        $('.panel-body[id]').each(function () {
            let element = $(this),
                id = element.attr('id');
            element.on('hidden.bs.collapse', function (e) {
                element.parent().find('.forum-board-trigger').removeClass('ion-minus-round').addClass('ion-plus-round');
                Cookies.set('forum.' + id, true, { expires: 365 });
            });
            element.on('show.bs.collapse', function (e) {
                element.parent().find('.forum-board-trigger').addClass('ion-minus-round').removeClass('ion-plus-round');
                Cookies.remove('forum.' + id);
            });
        });

        if($('.act_thread').length) {
            $('.single-post').each(function () {
                $(this).find('.col-sm-10:first .post-content').css('min-height', $(this).find('.col-sm-2:first').height());
            })
        }
    }
}

export {Forum}
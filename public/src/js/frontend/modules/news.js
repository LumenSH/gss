class News {
    constructor() {
        $('[data-like]').on('click', $.proxy(this.onDataLikeClick, this));
        $('[data-focus-comment]').on('click', News.focusComment);
        $('[data-comment-anwer]').on('click', this.toggleCommentAnswer);
    }
    onDataLikeClick (event) {
        let target = $(event.target),
            liked = target.data('liked'),
            section = target.data('section'),
            id = target.data('id');

        if ($('.is--loggedin').length === 0) {
            gsAlert('error', 'Like', GS.Language.general.pleaseLogin);
            return;
        }

        $.post(GS.Config.baseUrl + 'index/like', {
            id: id,
            section: section,
            like: (!liked == true) ? 1 : 0
        }, function (response) {
            target.parent().find('[data-like-badge]').html(response);
        });

        target.data('liked', !liked);

        if (liked) {
            target.removeClass('txt-red').removeClass('ion-ios-heart').addClass('txt-gray').addClass('ion-ios-heart-outline');
        } else {
            target.removeClass('txt-gray').removeClass('ion-ios-heart-outline').addClass('txt-red').addClass('ion-ios-heart');
        }
    }
    static focusComment () {
        $('[name="comment"]:first').focus();
    }
    toggleCommentAnswer (event) {
        var data = $(this).data();
        event.preventDefault();

        $('#comment_field_' + data.id).fadeIn(400, function () {
            $('#comment_field_' + data.id + ' [name="comment"]').focus().val('@' + data.poster + ": ");
        });
    }
}

export {News}
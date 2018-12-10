class OAuth {
    constructor() {
        var $modalOAuth = $('#modalOAuth');
        if ($modalOAuth.length) {
            $modalOAuth.modal();
            console.log($modalOAuth.find('button'));
            $modalOAuth.find('button').on('click', function (event) {
                event.preventDefault();
                var form = $(this).closest('form').serializeObject();

                $.post(GS.Config.baseUrl + 'oauth/last', {name: form.username}, function (data) {
                    if (data.success === false && data.message === 'toIndex' || data.success === true) {
                        window.location.href = GS.Config.baseUrl;
                    } else if (data.success === false) {
                        gsAlert(data.success ? 'success' : 'error', "Register", data.message);
                    }
                }, 'json');

                return false;
            });
        }
    }
}

export {OAuth}
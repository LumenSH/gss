import {Helper} from './helper';

class Login {
    constructor() {
        this.isAjaxRunning = false;
        this.credentialsSupported = typeof navigator.credentials !== 'undefined' && window.location.protocol.indexOf('https') !== -1;

        let loginButtons = document.querySelectorAll("[data-target='#modalLogin']"),
            i;
        for (i = 0; i < loginButtons.length; i++) {
            if (loginButtons[i]) {
                loginButtons[i].addEventListener('click', () => {
                    navigator.credentials.get({
                        "password": true,
                        "unmediated": false
                    }).then(function (cred) {
                        if (typeof cred !== 'undefined') {
                            fetch(GS.Config.baseUrl + 'user/login', {
                                method: 'POST',
                                credentials: cred
                            }).then((response) => {
                                response.json().then(function (json) {
                                    this.processLogin(json, null);
                                });
                            });
                        }
                    });
                });
            }
        }

        $('#modalLogin .btn-success').on('click', $.proxy(this.onLogin, this));
    }

    onLogin(event) {
        let $form = $(event.target).closest('form'),
            form = $form.serializeObject();

        if (form._username.length === 0 || form._password.length === 0) {
            gsAlert("error", "Login", "Bitte fülle alle Felder aus");
            return false;
        }

        if (this.isAjaxRunning) {
            return false;
        }

        this.isAjaxRunning = true;

        Helper.request(GS.Config.baseUrl + 'user/login', form).then(function (response) {
            return response.json()
        }).then((json) => {
            this.processLogin(json, form);
        }).catch((ex) => {
            console.log('parsing failed', ex)
        });

        return false;
    }

    processLogin(data, form) {
        this.isAjaxRunning = false;

        if (data.type !== 'undefined' && data.type === 'GSS\\Component\\Exception\\Security\\AccountLockedException') {
            $('#modalLogin').modal('hide');
            GS.Base.addModal('Inhibition', data.message);
        } else {
            if (data.success === true) {
                if (this.credentialsSupported && form) {
                    let cred = new PasswordCredential({
                        id: form._username,
                        password: form._password,
                        name: data._username,
                        iconURL: data.avatar
                    });

                    try {
                        navigator.credentials.store(cred).then(() => {
                            window.location.reload();
                        });
                    } catch (err) {
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            } else {
                gsAlert('error', "Login", data.message);
            }
        }
    }
}

export {Login}
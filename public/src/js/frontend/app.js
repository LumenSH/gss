import {Forum} from './modules/forum'
import {Gp} from './modules/gp'
import {Gameserver} from './modules/gameserver'
import {Login} from './modules/login'
import {Tutorial} from './modules/tutorial'
import {News} from './modules/news'
import {Menu} from './modules/menu'
import {OAuth} from './modules/oauth'
import {User} from './modules/user'
import {Support} from './modules/support'

window.GS = {
    Config: gs3Config,
    Language: window['gsLang' + gs3Config.language.toUpperCase()]
};

ace.config.set('basePath', '/src/js/ace');

class Base {
    constructor() {
        this.defaults = {
            modal: '<div class="modal" id="modal-%id%">\n\t<div class="modal-dialog">\n\t\t<div class="modal-content">\n\t\t\t<div class="modal-header">\n\t\t\t\t<h4 class="man">%headline%</h4>\n\t\t\t</div>\n\t\t\t<div class="modal-body">\n\t\t\t\t%content%\n\t\t\t</div>\n\t\t\t<div class="modal-footer">\n\t\t\t\t<button class="btn btn-primary" data-dismiss="modal">%btnText%</button>\n\t\t\t</div>\n\t</div>\n</div>'
        };
        this.initBaseElements();
        this.initNotifications();

        $('[data-custom-dropdown]').on('click', function (event) {
            event.preventDefault();
            $(this).parent().toggleClass('open');
        });

        $('[name="language"]').on('change', function () {
            window.location.href = GS.Config.baseUrl + 'index/changeLanguage/' + $('[name="language"] option:selected').attr('value') + '?redirect=' + window.location.href;
        });
    }

    initBaseElements() {
        $('[data-ckeditor=true]').each(function (index, ele) {
            var editorConfig = {
                textarea: $(ele),
                locale: 'en-US',
                toolbar: [
                    'title',
                    'bold',
                    'italic',
                    'underline',
                    'strikethrough',
                    'fontScale',
                    'color',
                    'ol',
                    'ul',
                    'blockquote',
                    'code',
                    'table',
                    'link',
                    'image',
                    'hr',
                    'indent',
                    'outdent',
                    'alignment',
                    'emoji'
                ],
                emoji: {
                    imagePath: '/src/img/emoji/'
                }
            };
            if (typeof GS.Config.Mention != 'undefined') {
                editorConfig.mention = GS.Config.Mention;
            }
            new Simditor(editorConfig);
        });
        $('[data-tooltip=true]').tooltip();
        $('[data-disabled]').on('click', function (event) {
            event.preventDefault();
        });

        new Clipboard('.btn-copy');

        if ($('.changelog').length) {
            $('.changelog:first .panel-body').toggle();
            $('.changelog').each(function(){
                let me = $(this);
                me.children('.panel-heading').css('cursor', 'pointer').on('click', function() {
                    me.children('.panel-body').toggle();
                });
            });
        }
    }

    reloadRecaptcha() {
        grecaptcha.reset();
    }

    addModal(headline, content, timer) {
        timer = timer || 0;
        var id = Date.now();
        document.getElementsByTagName("body")[0].insertAdjacentHTML("beforeend", this.defaults.modal.replace('%id%', id).replace('%content%', content).replace('%headline%', headline).replace('%btnText%', GS.Language.close));
        $('#modal-' + id.toString()).modal({
            backdrop: 'static',
            keyboard: false
        });

        if (timer > 0) {
            var btn = $('#modal-' + id.toString() + " .modal-footer .btn");
            btn.attr('disabled', 'disabled');
            var text = btn.html();
            btn.html(text + " (" + timer.toString() + ")");

            var showTimer = window.setInterval(function () {
                timer--;
                btn.html(text + " (" + timer.toString() + ")");

                if (timer == 0) {
                    btn.removeAttr('disabled');
                    btn.html(text);
                    clearInterval(showTimer);
                }
            }, 1000);

        }
    }

    initCrop() {
        window.setTimeout(function () {
            $('[data-drop-image] > img').cropper({
                movable: false,
                rotatable: false,
                scalable: false,
                zoomable: false,
                aspectRatio: 1,
                crop: function (e) {
                    GS.cropX = Math.round(e.x);
                    GS.cropY = Math.round(e.y);
                    GS.cropHeight = Math.round(e.height);
                    GS.cropWidth = Math.round(e.width);
                }
            });
        }, 200);
    }

    initNotifications() {
        var me = this;
        this.notifications = document.querySelector('[name="notifications"]');

        if (this.notifications) {
            var pushButton = document.querySelector('[for="notifications"]');
            pushButton.addEventListener("click", function () {
                me.notifications.checked = !me.notifications.checked;

                if (me.notifications.checked) {
                    me.subscribe();
                }
            });

            if (OneSignal.isPushNotificationsSupported()) {
                me.notifications.disabled = false;

                OneSignal.isPushNotificationsEnabled().then(function (success) {
                    if (success) {
                        me.notifications.checked = true;
                    }
                });
            }
        }
    }

    subscribe() {
        OneSignal.registerForPushNotifications();
        OneSignal.sendTags(GS.Config.notification);
        this.notifications.checked = true;
    }
}

window.gsAlert = function(type, title, message) {
    toastr[type](message, title);
};

$(function () {
    window.GS.Base = new Base();
    if (typeof window.fetch === 'undefined') {
        if (window.location.href.indexOf('unsupportedbrowsers') === -1) {
            window.location.href = GS.Config.baseUrl + 'unsupportedbrowsers';
        }
    }

    new Forum();
    new Gameserver();
    new Gp();
    new Login();
    new Menu();
    new News();
    new OAuth();
    new Tutorial();
    new User();
    new Support();

    window.onDocumentReady();
});
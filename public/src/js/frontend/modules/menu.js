class Menu {
    constructor() {
        this.defaults = {
            opened: false,
                toggleButton: '[data-menu=true]',
                toggleClass: 'hidden-menu',
                toggleSubMenu: '.has-submenu',
                menuClass: '.desktop-menu'
        };
        let me = this;

        this.toggleButton = document.querySelectorAll("[data-menu=true]");
        this.toggleSubmenu = document.querySelectorAll(".has-submenu");
        this.menuDiv = document.querySelector(".desktop-menu");
        this.body = document.getElementsByTagName("body")[0];

        for(let i = 0; i < this.toggleButton.length; i++) {
            this.toggleButton[i].addEventListener("click", this.toggleMenu());
        }

        for(let i = 0; i < this.toggleSubmenu.length; i++) {
            this.toggleSubmenu[i].addEventListener("click", this.openSubMenu);
        }

        let menuLeft = $('#menu-left');

        if (menuLeft.length > 0) {
            let elementHeight = 0;
            $('#menu-left > *').each(function (index, ele) {
                elementHeight += $(ele).height()
            });

            if (elementHeight > menuLeft.height()) {
                menuLeft.find('.scroller').slimScroll({
                    height: menuLeft.height(),
                    width: 260
                });
            }
        }

        $('#notifications-content').slimScroll({
            height: menuLeft.height(),
            width: 260
        });
        $('#notifications-scroller ul, .notifications-scroller').slimScroll({
            height: 300
        });

        $('.icon-list').find('.icon-single').on('click', this.onMenuTabChange);

        document.addEventListener('touchstart', handleTouchStart, false);
        document.addEventListener('touchmove', handleTouchMove, false);

        let xDown = null;
        let yDown = null;

        function handleTouchStart(evt) {
            xDown = evt.touches[0].clientX;
            yDown = evt.touches[0].clientY;
        }

        function handleTouchMove(evt) {
            if ( ! xDown || ! yDown ) {
                return;
            }

            let xUp = evt.touches[0].clientX;
            let yUp = evt.touches[0].clientY;

            let xDiff = xDown - xUp;
            let yDiff = yDown - yUp;

            if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) {/*most significant*/
                if ( xDiff > 0 ) {
                    me.defaults.opened = true;
                    me.toggleMenu()();
                } else {
                    me.defaults.opened = false;
                    me.toggleMenu()();
                }
            }

            /* reset values */
            xDown = null;
            yDown = null;
        }
    }
    toggleMenu () {
        var me = this;

        return function () {
            me.menuDiv.style.transform = "none";
            if(me.defaults.opened) {
                me.menuDiv.classList.remove("fadeInLeft");
                me.menuDiv.classList.add("fadeOutLeft");
                me.menuDiv.classList.add("animated");
            } else {
                me.menuDiv.classList.remove("fadeOutLeft");
                me.menuDiv.classList.add("fadeInLeft");
                me.menuDiv.classList.add("animated");
            }
            me.defaults.opened = !me.defaults.opened;
        }
    }
    openSubMenu () {
        if ($(this).hasClass('open')) {
            $(this).removeClass('open');
            $(this).find('.ion-chevron-down').removeClass('ion-chevron-down').addClass('ion-chevron-left');
            $(this).find('ul').slideUp(100);
        } else {
            $(this).addClass('open');
            $(this).find('.ion-chevron-left').removeClass('ion-chevron-left').addClass('ion-chevron-down');
            $(this).find('ul').slideDown(100);
        }
    }
    onMenuTabChange (event) {
        $('.icon-list').find('.icon-single.active').removeClass('active');
        $(this).addClass('active');

        $('.menu-tab').hide();
        $($(this).attr('href')).show();

        event.preventDefault();
    }
}

export {Menu}
class User {
    constructor() {
        if (document.getElementById("avatarUpload") !== null) {
            document.getElementById('avatarUpload').addEventListener('change', this.handleFileSelect, false);
            $('[data-save-avatar]').on('click', this.onSaveAvatar);
        }
    }
    handleFileSelect (evt) {
        let files = evt.target.files;

        for (let i = 0, f; f = files[i]; i++) {

            // Only process image files.
            if (!f.type.match('image.*')) {
                continue;
            }
            $('[data-drop-image]').html('');

            let reader = new FileReader();

            reader.onload = (function (theFile) {
                return function (e) {
                    $('#avatarUploadModal').modal();
                    $('[data-drop-image]').append('<img onload="GS.Base.initCrop()" class="img-responsive" src="' + e.target.result + '">');
                };
            })(f);

            reader.readAsDataURL(f);
        }
    }
    onSaveAvatar () {
        let image = $('[data-drop-image] > img');
        $(this).attr('disabled', 'disabled');

        $.post(GS.Config.baseUrl + 'user/saveAvatar', {
            img: image.attr('src'),
            options: {
                x: GS.cropX,
                y: GS.cropY,
                height: GS.cropHeight,
                width: GS.cropWidth
            },
            scale: image.cropper("getImageData")
        }, function () {
            window.location.reload();
        });
    }
}

export {User}
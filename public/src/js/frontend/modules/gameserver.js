class Gameserver {
    constructor() {
        this.defaults = {
            currentStatus: "",
            currentDatabase: 0,
            currentFTP: 0,
            configEditorTemplate: '<form><textarea rows="40" cols="20" class="form-control" id="config-editor">%code%</textarea><button data-configname="%configname%" class="btn btn-primary pull-right mts">Speichern</button></form>'
        };

        if ($('body.ctl_server').length === 0) {
            return;
        }

        this.addEvents();

        if (GS.Config.state !== "0") {
            window.taskChecker = window.setInterval(this.taskCheck, 3000);
        }
    }

    addEvents () {
        if (typeof gs3Config.hostname === 'undefined') {
            return;
        }
        this.serverLog = $('#serverLog');

        this.connection = io(gs3Config.hostname);
        this.connection.emit('login', {
            key: gs3Config.token
        });

        this.connection.on('log', (data) => {
            this.serverLog.append(data.message);
            this.serverLog.scrollTop(this.serverLog[0].scrollHeight);
        });

        this.connection.on('status', (data) => {
            $('#gsOnline').hide();
            $('#gsOffline').hide();

            $((data.status === true ? '#gsOnline' : '#gsOffline')).show();
        });

        this.connection.emit('status', {});

        $('[data-start-server]').on('click', () => {
            this.connection.emit('start');
        });

        $('[data-stop-server]').on('click', () => {
            this.connection.emit('stop');
        });

        $('#serverConsole').keyup(this.onServerConsoleKeyPressed());

        $('a[data-configname]').on('click', this.onServerConfigButtonPressed());
        $('input[name="accountType"]').on('change', Gameserver.onSelectFTPAccountSwitch);

        $('.database-edit').on('click', this.onDatabaseEditButtonPressed());
        $('#editDatabase .btn-primary').on('click', this.onDatabaseSave());

        $('.ftp-edit').on('click', this.onFTPEditButtonPressed());
        $('#editFTP .btn-primary').on('click', this.onFTPSave());
        $('#editDomain .btn-primary').on('click', this.onDomainSave());
        $('#modalRename .btn-primary').on('click', this.onServerNameSave());

        $('#modalUpgrade select[name="verlaegern"]').on('change', this.onModalVerlaegernChange());

        $('[data-right-add]').on('click', $.proxy(this.onGameserverRightAdd, this));
        $('[data-right-edit]').on('click', this.onGameserverRightEdit);
        $('[data-right-save]').on('click', this.onGameserverRightSave);
    }

    onServerConsoleKeyPressed () {
        var me = this;

        return function (e) {
            if (e.keyCode === 13) {
                me.connection.emit('command', {
                    message: $(this).val()
                });
                $(this).val('');
            }
        };
    }

    onServerConfigButtonPressed () {
        var me = this;

        return function () {
            var configName = $(this).data('configname');
            var mode = $(this).data('mode');
            $.post(GS.Config.baseUrl + 'server/api', {
                action: 'getConfig',
                gsID: GS.Config.gsID,
                config: configName
            }, function (response) {
                $('#config').html('');
                $('#config').html(me.defaults.configEditorTemplate.replace('%code%', response.data).replace('%configname%', configName));

                ace.config.set('basePath', '/src/js/ace');

                me.codeEditor = ace.edit('config-editor', {
                    mode: "ace/mode/" + mode,
                    selectionStyle: "text"
                });

                me.codeEditor.setOptions({
                    maxLines: Infinity
                });

                me.codeEditor.getSession().setUseWorker(false);

                $('button[data-configname]').on('click', me.onSaveConfig());
            }, 'json');
        }
    }

    static onSelectFTPAccountSwitch () {
        if ($('input[name="accountType"]:checked').val() == 0) {
            $('#accountPath').fadeIn();
        } else {
            $('#accountPath').fadeOut();
        }
    }

    onDatabaseEditButtonPressed () {
        var me = this;

        return function () {
            if ($(this).data('id')) {
                me.defaults.currentDatabase = $(this).data('id');
                $.post(GS.Config.baseUrl + 'server/api', {
                    action: 'getDatabaseInfo',
                    gsID: GS.Config.gsID,
                    dbID: $(this).data('id')
                }, function (response) {
                    if (response.result == 'success') {
                        $.each(response.data, function (key, value) {
                            $('#editDatabase [name="' + key + '"]').val(value);
                        });

                        $('#editDatabase').modal();
                    }
                }, 'json')
            }
        }
    }

    onDatabaseSave () {
        var me = this;

        return function () {
            $.post(GS.Config.baseUrl + 'server/api', {
                action: 'saveDatabase',
                gsID: GS.Config.gsID,
                dbID: me.defaults.currentDatabase,
                form: $('#editDatabase form').serializeObject()
            }, function (response) {
                if (response.result === 'success') {
                    $('#editDatabase').modal('hide');
                    window.location.reload();
                }
            }, 'json');
        }
    }

    onFTPEditButtonPressed () {
        var me = this;

        return function () {
            if ($(this).data('id')) {
                me.defaults.currentFTP = $(this).data('id');
                $.post(GS.Config.baseUrl + 'server/api', {
                    action: 'getFTPInfo',
                    gsID: GS.Config.gsID,
                    ftpID: $(this).data('id')
                }, function (response) {
                    if (response.result === 'success') {
                        $.each(response.data, function (key, value) {
                            $('#editFTP [name="' + key + '"]').val(value);
                        });

                        $('#editFTP').modal();
                    }
                }, 'json')
            }
        }
    }

    onFTPSave () {
        var me = this;

        return function () {
            $.post(GS.Config.baseUrl + 'server/api', {
                action: 'saveFTP',
                gsID: GS.Config.gsID,
                ftpID: me.defaults.currentFTP,
                form: $('#editFTP form').serializeObject()
            }, function (response) {
                if (response.result === 'success') {
                    $('#editFTP').modal('hide');
                    window.location.reload();
                }
            }, 'json');
        }
    }

    onDomainSave () {
        return function () {
            $.post($('#editDomain form').attr('action'), $('#editDomain form').serializeObject(), function (response) {
                gsAlert(response.success ? 'success' : 'error', 'Server', response.message);

                if (response.success) {
                    $('#editDomain').modal('hide');
                }
            }, 'json');
        }
    }

    onServerNameSave () {
        return function () {
            $.post($('#modalRename form').attr('action'), $('#modalRename form').serializeObject(), function (response) {
                gsAlert(response.success ? 'success' : 'error', 'Server', response.message);

                if (response.success) {
                    $('#modalRename').modal('hide');
                }
            }, 'json');
        }
    }

    onSaveConfig () {
        var me = this;

        return function () {
            $.post(GS.Config.baseUrl + 'server/api', {
                action: 'saveConfig',
                gsID: GS.Config.gsID,
                config: $(this).data('configname'),
                configValue: me.codeEditor.getSession().getValue()
            }, function (response) {
                if (response.result === 'success') {
                    $('#config').html('');
                    $('a[href="#verwaltung"]').click();
                }
                gsAlert(response.result, 'Config-Editor', response.message);
            }, 'json');

            return false;
        }
    }

    onModalVerlaegernChange () {
        return function () {
            var value = parseInt($(this).find('option:selected').attr('value'));
            var gpUser = parseInt($('[data-gpcount]').data('gp'));
            var gp = parseInt($('[data-servercoast]').data('currentcoast'));
            var calculated = 0;

            switch (value) {
                case 1:
                    calculated = Math.ceil((gp / 4) * 1.1);
                    break;
                case 2:
                    calculated = Math.ceil((gp / 2) * 1.05);
                    break;
                case 3:
                    calculated = gp;
                    break;
                case 4:
                    calculated = Math.ceil(gp * 2 * 0.98);
                    break;
                case 5:
                    calculated = Math.ceil(gp * 3 * 0.95);
                    break;
            }

            $('[data-servercoast]').html(calculated.toString() + ' GP');
            $('[data-serversumme]').html((gpUser - calculated).toString() + ' GP');
        }
    }

    onGameserverRightAdd () {
        $('#modalRights input').val('').prop('checked', false);

        $('#modalRights').modal();
    }

    onGameserverRightEdit () {
        var config = $(this).data('right-edit');
        var rights = JSON.parse(config.Rights);
        var keys = Object.keys(rights);

        $('#modalRights [name="user"]').val(config.Username);

        keys.forEach(function (item) {
            $('#modalRights [name="' + item + '"]').prop('checked', true);
        });

        $('#modalRights').modal();

        return false;
    }

    onGameserverRightSave () {
        var form = $('#modalRights').find('form').serializeObject(),
            length = Object.keys(form).length;

        if (form.user.length < 4) {
            gsAlert('error', 'Gameserver', 'Bitte gebe einen Usernamen an');
        } else if (length === 1) {
            gsAlert('error', 'Gameserver', 'Bitte vergebe mindestens ein Recht an den Benutzer');
        } else {
            $.post(GS.Config.baseUrl + 'server/api', {
                action: 'addRight',
                gsID: GS.Config.gsID,
                data: form
            }, function (response) {
                gsAlert(response.result, 'Server', response.message);

                if (response.result === 'success') {
                    window.location.reload();
                }
            }, 'json');
        }

        return false;
    }

    taskCheck () {
        $.post(GS.Config.baseUrl + 'server/api', {
            action: 'getTaskInfo',
            gsID: GS.Config.gsID
        }, function (response) {
            if (response.state !== GS.Config.state) {
                window.location.reload();
            }
        }, 'json');
    }
}

export {Gameserver}
class Gp {
    constructor() {
        this.defaults = {
            animating: false,
            sidebarState: false,
            scrollBox: {
                in: {
                    offset: 1,
                    end: true
                },
                out: {
                    offset: 1,
                    end: true
                },
                all: {
                    offset: 1,
                    end: true
                }
            },
            template: '<li>\n    <div class="row">\n        <div class="col-xs-2">\n            <div class="label label-sm label-info"><i class="icon ion-star"></i></div>\n        </div>\n        <div class="col-xs-7"><p>%name%<br><span class="txt-green">%points%</span> GP</p></div>\n        <div class="col-xs-3"><p>%date%</p></div>\n    </div>\n</li>'
        };

        if (typeof GS.Config.gpstats === 'undefined') {
            return;
        }

        $.plot($("#gpChart"), GS.Config.gpstats, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                    label: {
                        show: true,
                        radius: 2 / 3,
                        formatter: (label, series) => {
                            return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">' + label + '<br/>' + Math.round(series.percent) + '%</div>';
                        },
                        threshold: 0.1
                    }
                }
            },
            legend: {
                show: false
            }
        });

        this.createScrollBox('gp-all');
        this.createScrollBox('gp-in');
        this.createScrollBox('gp-out');

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $(e.target).attr("href").replace('#', '');

            $('#gp-' + target).scrollbox('update');
        });
    }

    createScrollBox(name) {
        let short = name.replace('gp-', ''),
            $container = $('#' + name);

        setTimeout(() => {
            this.defaults.scrollBox[short].end = false;
        }, 250);

        $container
            .on('reachbottom.scrollbox', () => {
                if (this.defaults.scrollBox[short].end) {
                    return;
                }

                $.post(GS.Config.baseUrl + 'gp/ajax', {offset: this.defaults.scrollBox[short].offset, type: short}, (response) => {
                    if (response.data.length === 0) {
                        this.defaults.scrollBox[short].end = true;
                        return;
                    }

                    response.data.forEach((item) => {
                        let splits = item.timestamp.toString().split(' ')[0].split('-');
                        $container.find('ul').append(this.defaults.template.replace('%points%', item.value).replace('%name%', item.name).replace('%date%', splits[2] + '.' + splits[1] + "." + splits[0]).replace("txt-green", (item.status == 'out' ? 'txt-red' : 'txt-green')));
                    });

                    $container.scrollbox('update');
                }, 'json');
                this.defaults.scrollBox[short].offset++;
            })
            .scrollbox({
                distanceToReach: {
                    y: 400
                },
                startAt: {
                    y: 'top'
                }
            });
    }
}

export {Gp}